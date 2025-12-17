<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Views
        DB::statement("DROP VIEW IF EXISTS `view_available_products`");
        DB::statement("
            CREATE OR REPLACE VIEW `view_available_products` AS 
            SELECT `p`.`id` AS `product_id`, `p`.`name` AS `product_name`, `p`.`description` AS `description`, `p`.`current_unit_price` AS `current_unit_price`, `p`.`measurement_unit` AS `measurement_unit`, `p`.`pieces_per_box` AS `pieces_per_box`, `p`.`inventory_status` AS `inventory_status`, `p`.`product_status` AS `product_status`, `p`.`updated_act` AS `last_updated`, `u`.`name` AS `updated_by`, if((`p`.`measurement_unit` = 'caja'),concat('Caja de ',`p`.`pieces_per_box`,' piezas'),'Venta por pieza') AS `packaging_type`, if((`p`.`measurement_unit` = 'caja'),round((`p`.`current_unit_price` / `p`.`pieces_per_box`),2),`p`.`current_unit_price`) AS `price_per_piece`, if(((`p`.`inventory_status` = 'disponible') and (`p`.`product_status` = 'activo')),'Disponible para venta',if((`p`.`inventory_status` = 'agotado'),'Agotado temporalmente',if((`p`.`product_status` = 'inactivo'),'Producto descontinuado','Estado desconocido'))) AS `availability_status` 
            FROM (`products` `p` left join `users` `u` on((`p`.`updated_by_user_id` = `u`.`id`))) 
            WHERE (`p`.`product_status` = 'activo')
        ");

        DB::statement("DROP VIEW IF EXISTS `view_monthly_sales`");
        DB::statement("
            CREATE OR REPLACE VIEW `view_monthly_sales` AS 
            SELECT `mc`.`id` AS `closure_id`, `mc`.`month` AS `month`, `mc`.`year` AS `year`, `mc`.`closure_status` AS `closure_status`, `mc`.`opening_date` AS `opening_date`, `mc`.`closing_date` AS `closing_date`, count(distinct `o`.`id`) AS `total_orders`, count(distinct `o`.`user_id`) AS `total_customers`, coalesce(sum(`o`.`total_amount`),0) AS `total_sales`, coalesce(sum((case when (`o`.`order_status` = 'entregado') then `o`.`total_amount` else 0 end)),0) AS `delivered_sales`, coalesce(sum((case when (`o`.`payment_status` = 'liquidado') then `o`.`total_amount` else 0 end)),0) AS `paid_sales`, coalesce(sum(`od`.`quantity`),0) AS `total_items`, sum((case when (`od`.`quantity_type` = 'caja') then `od`.`quantity` else 0 end)) AS `total_boxes`, sum((case when (`od`.`quantity_type` = 'pieza') then `od`.`quantity` else 0 end)) AS `total_pieces`, count(distinct `od`.`product_id`) AS `unique_products_sold`, coalesce(sum(`p`.`payment_amount`),0) AS `total_collected`, (case when (`mc`.`closure_status` = 'abierto') then 'Período activo' when (`mc`.`closure_status` = 'cerrado') then 'Período cerrado' when (`mc`.`closure_status` = 'procesado') then 'Procesado contablemente' else 'Estado desconocido' end) AS `closure_status_description` 
            FROM (((`monthly_closures` `mc` left join `orders` `o` on(((`mc`.`id` = `o`.`monthly_closure_id`) and (`o`.`order_status` <> 'cancelado')))) left join `order_details` `od` on((`o`.`id` = `od`.`order_id`))) left join `payments` `p` on(((`o`.`id` = `p`.`order_id`) and (`p`.`transaction_status` = 'completado')))) 
            GROUP BY `mc`.`id`, `mc`.`month`, `mc`.`year`, `mc`.`closure_status`, `mc`.`opening_date`, `mc`.`closing_date` 
            ORDER BY `mc`.`year` DESC, `mc`.`month` DESC
        ");

        DB::statement("DROP VIEW IF EXISTS `view_order_details_complete`");
        DB::statement("
            CREATE OR REPLACE VIEW `view_order_details_complete` AS 
            SELECT `o`.`id` AS `order_id`, `o`.`order_date` AS `order_date`, `o`.`order_status` AS `order_status`, `o`.`payment_status` AS `payment_status`, `o`.`total_amount` AS `order_total`, `u`.`id` AS `customer_id`, `u`.`name` AS `customer_name`, `u`.`email` AS `customer_email`, `od`.`id` AS `detail_id`, `od`.`product_id` AS `product_id`, `p`.`name` AS `product_name`, `od`.`quantity` AS `quantity`, `od`.`quantity_type` AS `quantity_type`, `od`.`unit_price_at_order` AS `unit_price_at_order`, `od`.`subtotal` AS `subtotal`, `p`.`current_unit_price` AS `current_price`, (case when (`p`.`current_unit_price` <> `od`.`unit_price_at_order`) then 'Precio ha cambiado' else 'Precio actual' end) AS `price_status`, (`p`.`current_unit_price` - `od`.`unit_price_at_order`) AS `price_difference`, `mc`.`month` AS `closure_month`, `mc`.`year` AS `closure_year`, `mc`.`closure_status` AS `closure_status`, `o`.`confirmed_by_user_id` AS `confirmed_by`, `o`.`confirmation_date` AS `confirmation_date`, `o`.`delivered_by_user_id` AS `delivered_by`, `o`.`delivery_date` AS `delivery_date` 
            FROM ((((`orders` `o` join `users` `u` on((`o`.`user_id` = `u`.`id`))) join `order_details` `od` on((`o`.`id` = `od`.`order_id`))) join `products` `p` on((`od`.`product_id` = `p`.`id`))) join `monthly_closures` `mc` on((`o`.`monthly_closure_id` = `mc`.`id`))) 
            ORDER BY `o`.`order_date` DESC, `o`.`id` ASC, `od`.`id` ASC
        ");

        DB::statement("DROP VIEW IF EXISTS `view_payment_status`");
        DB::statement("
            CREATE OR REPLACE VIEW `view_payment_status` AS 
            SELECT `u`.`id` AS `user_id`, `u`.`name` AS `employee_name`, `o`.`id` AS `order_id`, `o`.`total_amount` AS `total_amount`, `o`.`order_date` AS `order_date`, coalesce(sum(`p`.`payment_amount`),0) AS `total_paid`, (`o`.`total_amount` - coalesce(sum(`p`.`payment_amount`),0)) AS `balance_due`, `o`.`payment_status` AS `payment_status`, `o`.`order_status` AS `order_status`, (case when (`o`.`payment_status` = 'liquidado') then 'Pagado completo' when ((`o`.`payment_status` = 'abonado') and ((`o`.`total_amount` - coalesce(sum(`p`.`payment_amount`),0)) > 0)) then 'Pago parcial' when (`o`.`payment_status` = 'pendiente') then 'Pendiente de pago' else 'Estado desconocido' end) AS `payment_status_description` 
            FROM ((`users` `u` join `orders` `o` on((`u`.`id` = `o`.`user_id`))) left join `payments` `p` on(((`o`.`id` = `p`.`order_id`) and (`p`.`transaction_status` = 'completado')))) 
            GROUP BY `u`.`id`, `u`.`name`, `o`.`id`, `o`.`total_amount`, `o`.`order_date`, `o`.`payment_status`, `o`.`order_status` 
            ORDER BY `o`.`order_date` DESC
        ");

        DB::statement("DROP VIEW IF EXISTS `view_price_history`");
        DB::statement("
            CREATE OR REPLACE VIEW `view_price_history` AS 
            SELECT `ph`.`id` AS `history_id`, `ph`.`product_id` AS `product_id`, `p`.`name` AS `product_name`, `ph`.`previous_price` AS `previous_price`, `ph`.`new_price` AS `new_price`, `ph`.`change_date` AS `change_date`, (`ph`.`new_price` - `ph`.`previous_price`) AS `price_change`, round((((`ph`.`new_price` - `ph`.`previous_price`) / `ph`.`previous_price`) * 100),2) AS `percentage_change`, `ph`.`changed_by_user_id` AS `changed_by_user_id`, `u`.`name` AS `changed_by_user_name`, (case when (`ph`.`new_price` > `ph`.`previous_price`) then 'Aumento' when (`ph`.`new_price` < `ph`.`previous_price`) then 'Disminución' else 'Sin cambio' end) AS `change_type`, `p`.`current_unit_price` AS `current_price`, (to_days(curdate()) - to_days(`ph`.`change_date`)) AS `days_since_change` 
            FROM ((`price_history` `ph` join `products` `p` on((`ph`.`product_id` = `p`.`id`))) left join `users` `u` on((`ph`.`changed_by_user_id` = `u`.`id`))) 
            ORDER BY `ph`.`change_date` DESC, `p`.`name` ASC
        ");

        DB::statement("DROP VIEW IF EXISTS `view_users_roles`");
        DB::statement("
            CREATE OR REPLACE VIEW `view_users_roles` AS 
            SELECT `u`.`id` AS `user_id`, `u`.`name` AS `user_name`, `u`.`email` AS `email`, `u`.`phone` AS `phone`, `u`.`user_type` AS `user_type`, `u`.`status` AS `status`, `u`.`registered_at` AS `registration_date`, group_concat(distinct `r`.`name` order by `r`.`name` ASC separator ', ') AS `assigned_roles`, group_concat(distinct `p`.`name` order by `p`.`name` ASC separator ', ') AS `direct_permissions`, count(distinct `r`.`id`) AS `total_roles`, count(distinct `p`.`id`) AS `total_direct_permissions` 
            FROM ((((`users` `u` left join `model_has_roles` `mhr` on(((`u`.`id` = `mhr`.`model_id`) and (`mhr`.`model_type` = 'App\\\\Models\\\\User')))) left join `roles` `r` on((`mhr`.`role_id` = `r`.`id`))) left join `model_has_permissions` `mhp` on(((`u`.`id` = `mhp`.`model_id`) and (`mhp`.`model_type` = 'App\\\\Models\\\\User')))) left join `permissions` `p` on((`mhp`.`permission_id` = `p`.`id`))) 
            GROUP BY `u`.`id`, `u`.`name`, `u`.`email`, `u`.`phone`, `u`.`user_type`, `u`.`status`, `u`.`registered_at` 
            ORDER BY `u`.`registered_at` DESC
        ");

        // Triggers
        DB::unprepared("
            CREATE TRIGGER `validate_monthly_closure` BEFORE INSERT ON `orders` FOR EACH ROW BEGIN
                DECLARE estado_cierre VARCHAR(20);
                DECLARE modo_inventario BOOLEAN;
                
                SELECT `closure_status`, `in_inventory` 
                INTO estado_cierre, modo_inventario
                FROM `monthly_closures` 
                WHERE `id` = NEW.`monthly_closure_id`;
                
                IF estado_cierre IS NULL THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Error: Período mensual no válido';
                ELSEIF estado_cierre != 'abierto' THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Error: El período está cerrado';
                ELSEIF modo_inventario = TRUE THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Error: Sistema en inventario';
                END IF;
            END
        ");

        DB::unprepared("
            CREATE TRIGGER `before_insert_order_detail` BEFORE INSERT ON `order_details` FOR EACH ROW BEGIN
                DECLARE inv_status VARCHAR(20);
                DECLARE prod_status VARCHAR(20);
                
                SELECT inventory_status, product_status
                INTO inv_status, prod_status
                FROM products
                WHERE id = NEW.product_id;
                
                IF inv_status = 'agotado' THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'No se pueden pedir productos agotados';
                END IF;
                
                IF prod_status = 'inactivo' THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'No se pueden pedir productos inactivos';
                END IF;
            END
        ");

        DB::unprepared("
            CREATE TRIGGER `calculate_order_total_delete` AFTER DELETE ON `order_details` FOR EACH ROW BEGIN
                UPDATE `orders` 
                SET `total_amount` = (
                    SELECT COALESCE(SUM(`subtotal`), 0) 
                    FROM `order_details` 
                    WHERE `order_id` = OLD.`order_id`
                ) 
                WHERE `id` = OLD.`order_id`;
            END
        ");

        DB::unprepared("
            CREATE TRIGGER `calculate_order_total_insert` AFTER INSERT ON `order_details` FOR EACH ROW BEGIN
                UPDATE `orders` 
                SET `total_amount` = (
                    SELECT SUM(`subtotal`) 
                    FROM `order_details` 
                    WHERE `order_id` = NEW.`order_id`
                ) 
                WHERE `id` = NEW.`order_id`;
            END
        ");

        DB::unprepared("
            CREATE TRIGGER `calculate_order_total_update` AFTER UPDATE ON `order_details` FOR EACH ROW BEGIN
                -- Si cambió el order_id (raro, pero posible)
                IF NEW.`order_id` != OLD.`order_id` THEN
                    -- Actualizar pedido VIEJO
                    UPDATE `orders` 
                    SET `total_amount` = (
                        SELECT COALESCE(SUM(`subtotal`), 0) 
                        FROM `order_details` 
                        WHERE `order_id` = OLD.`order_id`
                    ) 
                    WHERE `id` = OLD.`order_id`;
                    
                    -- Actualizar pedido NUEVO  
                    UPDATE `orders` 
                    SET `total_amount` = (
                        SELECT SUM(`subtotal`) 
                        FROM `order_details` 
                        WHERE `order_id` = NEW.`order_id`
                    ) 
                    WHERE `id` = NEW.`order_id`;
                ELSE
                    -- Mismo pedido, solo actualizar ese
                    UPDATE `orders` 
                    SET `total_amount` = (
                        SELECT SUM(`subtotal`) 
                        FROM `order_details` 
                        WHERE `order_id` = NEW.`order_id`
                    ) 
                    WHERE `id` = NEW.`order_id`;
                END IF;
            END
        ");

        DB::unprepared("
            CREATE TRIGGER `after_update_products_price` AFTER UPDATE ON `products` FOR EACH ROW BEGIN
                IF OLD.`current_unit_price` != NEW.`current_unit_price` THEN
                    INSERT INTO `price_history` (
                        `product_id`, 
                        `previous_price`, 
                        `new_price`, 
                        `changed_by_user_id`
                    ) VALUES (
                        NEW.`id`, 
                        OLD.`current_unit_price`, 
                        NEW.`current_unit_price`, 
                        COALESCE(NEW.`updated_by_user_id`, 1)
                    );
                END IF;
            END
        ");

        DB::unprepared("
            CREATE TRIGGER `before_update_product` BEFORE UPDATE ON `products` FOR EACH ROW BEGIN
                SET NEW.`updated_act` = CURRENT_TIMESTAMP;
            END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP TRIGGER IF EXISTS `before_update_product`");
        DB::unprepared("DROP TRIGGER IF EXISTS `after_update_products_price`");
        DB::unprepared("DROP TRIGGER IF EXISTS `calculate_order_total_update`");
        DB::unprepared("DROP TRIGGER IF EXISTS `calculate_order_total_insert`");
        DB::unprepared("DROP TRIGGER IF EXISTS `calculate_order_total_delete`");
        DB::unprepared("DROP TRIGGER IF EXISTS `before_insert_order_detail`");
        DB::unprepared("DROP TRIGGER IF EXISTS `validate_monthly_closure`");

        DB::statement("DROP VIEW IF EXISTS `view_users_roles`");
        DB::statement("DROP VIEW IF EXISTS `view_price_history`");
        DB::statement("DROP VIEW IF EXISTS `view_payment_status`");
        DB::statement("DROP VIEW IF EXISTS `view_order_details_complete`");
        DB::statement("DROP VIEW IF EXISTS `view_monthly_sales`");
        DB::statement("DROP VIEW IF EXISTS `view_available_products`");
    }
};
