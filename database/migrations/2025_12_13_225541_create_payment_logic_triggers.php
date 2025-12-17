<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Trigger when Order Total Amount changes (to recalc pending amount)
        DB::unprepared("
            CREATE TRIGGER `trigger_order_recalc_pending_on_update` BEFORE UPDATE ON `orders` FOR EACH ROW BEGIN
                DECLARE total_pagado DECIMAL(10,2);
                
                -- Only if total_amount changed
                IF NEW.total_amount != OLD.total_amount THEN
                    -- Calculate total paid
                    SELECT COALESCE(SUM(payment_amount), 0) 
                    INTO total_pagado
                    FROM payments 
                    WHERE order_id = NEW.id 
                    AND transaction_status = 'completado';
                    
                    -- Set new pending amount
                    SET NEW.pending_amount = NEW.total_amount - total_pagado;
                    
                    -- Determine new status
                    IF NEW.pending_amount <= 0 THEN
                        SET NEW.payment_status = 'liquidado';
                    ELSEIF NEW.pending_amount < NEW.total_amount THEN
                        SET NEW.payment_status = 'abonado';
                    ELSE
                        SET NEW.payment_status = 'pendiente';
                    END IF;
                END IF;
            END
        ");

        // 2. Trigger AFTER INSERT on Payments
        DB::unprepared("
            CREATE TRIGGER `trigger_update_order_on_payment_insert` AFTER INSERT ON `payments` FOR EACH ROW BEGIN
                -- Update the order's pending amount and status
                -- We only care if the payment is 'completado' OR if we just want to re-verify everything.
                -- Ideally, even if not completed, pending amount doesn't change, but no harm checking.
                -- Actually, if inserted as 'pendiente', sum won't include it. 
                
                UPDATE `orders` 
                SET `pending_amount` = `total_amount` - (
                    SELECT COALESCE(SUM(`payment_amount`), 0) 
                    FROM `payments` 
                    WHERE `order_id` = NEW.`order_id`
                    AND `transaction_status` = 'completado'
                )
                WHERE `id` = NEW.`order_id`;
                
                -- Then update status based on the NEW pending_amount (we can't reference it easily in one Update in MySQL depending on version, so safer to do logic)
                -- Actually we can do it in one UPDATE using expressions
                UPDATE `orders`
                SET `payment_status` = (
                    CASE 
                        WHEN `pending_amount` <= 0 THEN 'liquidado'
                        WHEN `pending_amount` < `total_amount` THEN 'abonado'
                        ELSE 'pendiente'
                    END
                )
                WHERE `id` = NEW.`order_id`;
            END
        ");

        // 3. Trigger AFTER UPDATE on Payments
        DB::unprepared("
            CREATE TRIGGER `trigger_update_order_on_payment_update` AFTER UPDATE ON `payments` FOR EACH ROW BEGIN
                -- Recalculate if amount, status, or order_id changed
                IF OLD.payment_amount != NEW.payment_amount OR OLD.transaction_status != NEW.transaction_status OR OLD.order_id != NEW.order_id THEN
                    
                    -- If order_id changed (very rare), update OLD order too
                    IF OLD.order_id != NEW.order_id THEN
                         UPDATE `orders` 
                         SET `pending_amount` = `total_amount` - (
                            SELECT COALESCE(SUM(`payment_amount`), 0) FROM `payments` WHERE `order_id` = OLD.`order_id` AND `transaction_status` = 'completado'
                         ) WHERE `id` = OLD.`order_id`;
                         
                         UPDATE `orders`
                         SET `payment_status` = CASE 
                            WHEN `pending_amount` <= 0 THEN 'liquidado'
                            WHEN `pending_amount` < `total_amount` THEN 'abonado'
                            ELSE 'pendiente'
                         END WHERE `id` = OLD.`order_id`;
                    END IF;

                    -- Update NEW order
                    UPDATE `orders` 
                     SET `pending_amount` = `total_amount` - (
                        SELECT COALESCE(SUM(`payment_amount`), 0) FROM `payments` WHERE `order_id` = NEW.`order_id` AND `transaction_status` = 'completado'
                     ) WHERE `id` = NEW.`order_id`;
                     
                     UPDATE `orders`
                     SET `payment_status` = CASE 
                        WHEN `pending_amount` <= 0 THEN 'liquidado'
                        WHEN `pending_amount` < `total_amount` THEN 'abonado'
                        ELSE 'pendiente'
                     END WHERE `id` = NEW.`order_id`;
                END IF;
            END
        ");

        // 4. Trigger AFTER DELETE on Payments
        DB::unprepared("
            CREATE TRIGGER `trigger_update_order_on_payment_delete` AFTER DELETE ON `payments` FOR EACH ROW BEGIN
                -- Update the order logic
                UPDATE `orders` 
                SET `pending_amount` = `total_amount` - (
                    SELECT COALESCE(SUM(`payment_amount`), 0) 
                    FROM `payments` 
                    WHERE `order_id` = OLD.`order_id`
                    AND `transaction_status` = 'completado'
                )
                WHERE `id` = OLD.`order_id`;
                
                UPDATE `orders`
                SET `payment_status` = (
                    CASE 
                        WHEN `pending_amount` <= 0 THEN 'liquidado'
                        WHEN `pending_amount` < `total_amount` THEN 'abonado'
                        ELSE 'pendiente'
                    END
                )
                WHERE `id` = OLD.`order_id`;
            END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP TRIGGER IF EXISTS `trigger_update_order_on_payment_delete`");
        DB::unprepared("DROP TRIGGER IF EXISTS `trigger_update_order_on_payment_update`");
        DB::unprepared("DROP TRIGGER IF EXISTS `trigger_update_order_on_payment_insert`");
        DB::unprepared("DROP TRIGGER IF EXISTS `trigger_order_recalc_pending_on_update`");
    }
};
