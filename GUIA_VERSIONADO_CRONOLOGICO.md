# Guía de Práctica y Versionado: Evolución del Sistema de Pedidos

Este documento está estructurado cronológicamente como un "historial de versiones" para que puedas replicar y entender cada paso del desarrollo.

---

## V0.1: El Fundamento (Instalación)
**Objetivo**: Preparar el terreno con el framework y las herramientas administrativas.

### Comandos de Terminal
```bash
# 1. Crear el proyecto Laravel
composer create-project laravel/laravel gestion_pedidos

# 2. Instalar el Panel Administrativo (Filament)
composer require filament/filament:"4.0" -W

# 3. Instalar el gestor de permisos
composer require spatie/laravel-permission
```

---

## V0.2: Arquitectura de Datos (Tablas)
**Objetivo**: Definir **QUÉ** datos vamos a guardar.
**Archivo**: `database/migrations/2025_12_09_080000_create_ecommerce_tables.php`

### Código Clave: Relaciones
Aquí definimos que un pedido (`orders`) pertenece a un usuario y a un cierre mensual.

```php
Schema::create('orders', function (Blueprint $table) {
    $table->integer('id')->autoIncrement();
    // Relaciones (Foreign Keys)
    $table->integer('user_id'); 
    $table->integer('monthly_closure_id');
    
    // Datos del pedido
    $table->decimal('total_amount', 10, 2);
    $table->enum('order_status', ['pendiente', 'confirmado', ...]);
    
    // Constraints (Restricciones físicas)
    $table->foreign('user_id')->references('id')->on('users');
});
```

**Explicación**: Usamos `enum` para limitar las opciones de estado directamente en la base de datos, evitando datos inválidos.

---

## V0.3: Lógica de Negocio Blindada (Triggers)
**Objetivo**: Definir **CÓMO** se comportan los datos. Esta es la capa de seguridad más importante.
**Archivo**: `database/migrations/2025_12_09_080001_create_db_logic.php`

### Script 1: Validación de Inventario
Este trigger se dispara *antes* de que se guarde un detalle de pedido.

```sql
CREATE TRIGGER `before_insert_order_detail` BEFORE INSERT ON `order_details` FOR EACH ROW BEGIN
    DECLARE inv_status VARCHAR(20);
    
    -- Consultamos el estado del producto
    SELECT inventory_status INTO inv_status FROM products WHERE id = NEW.product_id;
    
    -- Si está agotado, CANCELAMOS la operación con un error
    IF inv_status = 'agotado' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No se pueden pedir productos agotados';
    END IF;
END
```

### Script 2: Cálculo Automático
Este trigger actualiza el total del pedido automáticamente cuando agregas un producto.

```sql
CREATE TRIGGER `calculate_order_total_insert` AFTER INSERT ON `order_details` FOR EACH ROW BEGIN
    UPDATE `orders` 
    SET `total_amount` = (
        SELECT SUM(`subtotal`) FROM `order_details` WHERE `order_id` = NEW.`order_id`
    ) 
    WHERE `id` = NEW.`order_id`;
END
```

---

## V0.4: Interfaz de Usuario (Primer Intento)
**Objetivo**: Crear formularios visuales para alimentar la base de datos.
**Nota**: Aquí se utilizó código basado en Filament v3, lo que causaría problemas más adelante.

**Archivo Original**: `app/Filament/Resources/ProductResource.php`
```php
// CÓDIGO ANTIGUO (Causante de error en v4)
use Filament\Forms\Components\Section; // <-- Namespace antiguo
use Filament\Forms\Components\TextInput;

public static function form(Form $form): Form
{
    return $form->schema([
        Section::make('General')... // Filament busca esto en 'Forms' y no lo encuentra
    ]);
}
```

---

## V0.5: El "Bug" de Estilos (Crisis)
**Problema**: Al navegar a `gestion_pedidos.test`, el sitio se veía sin diseño (blanco y texto plano).
**Causa**: Vite (el compilador de estilos) no estaba configurado para aceptar conexiones desde un dominio local personalizado, y Laravel apuntaba a `localhost` en lugar del dominio real.

### Solución Paso 1: Configurar Entorno
**Archivo**: `.env`
```ini
# ANTES
APP_URL=http://localhost

# DESPUÉS (Corregido para coincidir con tu navegador)
APP_URL=http://127.0.0.1:8000
```

### Solución Paso 2: Configurar Vite
**Archivo**: `vite.config.js`
```javascript
export default defineConfig({
    // ... plugins
    server: {
        host: '127.0.0.1', // Forzar IP local
        cors: true,        // Permitir carga de recursos cruzados
    },
});
```

---

## V1.0: Refactorización a Filament v4 (Solución Final)
**Objetivo**: Corregir los errores "Class not found" actualizando los namespaces.

### Cambio Crítico: Componentes de Diseño
**Archivo**: `app/Filament/Resources/Products/Schemas/ProductForm.php`

**Diff (Diferencia de código):**
```diff
- use Filament\Forms\Components\Section;
+ use Filament\Schemas\Components\Section;

- use Filament\Forms\Get;
+ use Filament\Schemas\Components\Utilities\Get;
```

**Explicación**:
En la versión 4.0, Filament separó los componentes puramente visuales (como `Section` o `Grid`) de los componentes de entrada de datos (como `TextInput`).
*   Los inputs se quedan en `Filament\Forms`.
*   La estructura se mueve a `Filament\Schemas`.

---

## Resumen de Práctica
Si quieres practicar "romper" y arreglar el sistema:

1.  **Para romper los estilos**: Borra el bloque `server: { ... }` de `vite.config.js` y ejecuta `npm run dev`. Verás los errores en la consola del navegador.
2.  **Para romper la creación de productos**: En `ProductForm.php`, cambia `use Filament\Schemas\Components\Section;` por `use Filament\Forms\Components\Section;`. Verás el error 500 "Class not found".
3.  **Para probar los Triggers**: Intenta insertar un pedido por SQL directo de un producto agotado. MySQL te rechazará la consulta aunque no uses la página web.
