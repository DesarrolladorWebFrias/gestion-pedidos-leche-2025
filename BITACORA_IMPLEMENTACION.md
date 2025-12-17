# Bitácora Completa de Implementación: Sistema de Gestión de Pedidos

## 1. Fase de Inicialización y Configuración

El proyecto comenzó con la configuración de un entorno Laravel 12 híbrido con Filament 4.0, diseñado para correr en un servidor local (Laragon) bajo el dominio `gestion_pedidos.test` y/o `localhost`.

### Comandos Iniciales
```bash
# Instalación del esqueleto Laravel
composer create-project laravel/laravel gestion_pedidos

# Instalación de Filament (Admin Panel)
composer require filament/filament:"4.0" -W
php artisan filament:install --panels

# Instalación de Spatie Permissions (Gestión de Roles)
composer require spatie/laravel-permission
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
```

---

## 2. Diseño de Base de Datos (SQL Centric)

Se optó por una arquitectura donde la integridad de los datos es garantizada por la base de datos (MySQL) y no solo por la aplicación (PHP).

### Estructura de Tablas (`2025_12_09_080000_create_ecommerce_tables.php`)
Se definieron tabas relacionales normalizadas:
*   **users**: Extendida para incluir `user_type` (admin, empleado, cliente) y `status`.
*   **products**: Manejo de inventario, unidades (caja/pieza) y precios históricos.
*   **monthly_closures**: Control contable para agrupar pedidos por mes.
*   **orders / order_details**: Cabecera y detalle de pedidos.
*   **payments**: Seguimiento de abonos y liquidaciones.
*   **price_history**: Auditoría de cambios de precios.

### Lógica Avanzada (`2025_12_09_080001_create_db_logic.php`)
Se implementaron "Triggers" y "Views" para automatizar procesos críticos:

1.  **Triggers (Automatización)**:
    *   `validate_monthly_closure`: Impide crear pedidos si el mes contable está cerrado.
    *   `before_insert_order_detail`: Valida stock y estado activo de productos antes de vender.
    *   `calculate_order_total_*`: Recalcula automáticamente el total del pedido al modificar sus detalles.
    *   `after_update_products_price`: Genera automáticamente un registro en el historial al cambiar un precio.

2.  **Vistas (Reportes)**:
    *   `view_monthly_sales`: Reporte consolidado de ventas por mes.
    *   `view_available_products`: Lista de precios y stock formateada para lectura humana.

---

## 3. Implementación de Modelos Eloquent

Se crearon modelos para interactuar con las tablas, definiendo relaciones estrictas:

*   **User**: Implementa `FilamentUser` para acceso al panel y `HasRoles` para permisos.
*   **Order**: Relación `belongsTo` con Usuario y Cierre Mensual, `hasMany` con Detalles.
*   **Product**: Lógica de acceso a historial de precios.

---

## 4. Construcción del Panel Administrativo (Filament)

Se generaron recursos CRUD para cada entidad del sistema.

### Generación de Recursos
```bash
php artisan make:filament-resource User
php artisan make:filament-resource Product
php artisan make:filament-resource Order
php artisan make:filament-resource MonthlyClosure
```

### Personalización de Formularios (Schemas)
Aquí se implementó la lógica visual reactiva (que luego es respaldada por los triggers de BD):

*   **Pedidos (`OrderForm.php`)**:
    *   Uso de `Repeater` para los detalles del pedido.
    *   Cálculo en tiempo real (Livewire) de subtotales: `Cantidad * Precio`.
    *   Bloqueo de campos según estado del pedido.
*   **Productos (`ProductForm.php`)**:
    *   Desactivación dinámica de "Piezas por caja" si la unidad es "Pieza".

---

## 5. Resolución de Conflictos y Estabilización (Fase Reciente)

El día 09/12/2025 se realizaron ajustes críticos para corregir errores de visualización y compatibilidad con Filament v4.

### A. Problema de Estilos (Assets 404)
El sistema no cargaba CSS/JS debido a incoherencias entre la URL local y la configuración de Vite.

**Solución**:
1.  Ajuste de `.env`: `APP_URL=http://127.0.0.1:8000`
2.  Configuración de `vite.config.js`:
    ```javascript
    server: { host: '127.0.0.1', cors: true }
    ```
3.  Publicación de assets: `php artisan filament:assets`
4.  Limpieza de caché: `php artisan optimize:clear`
5.  Compilación: `npm run build`

### B. Actualización de Namespaces (Filament v4)
La versión 4.0 cambió la ubicación de ciertos componentes, causando errores 500 ("Class not found").

**Cambios en Código PHP**:
Se actualizaron las importaciones en todos los `Form` y `Resources`.

*   **Antes**: `use Filament\Forms\Components\Section;`
*   **Ahora**: `use Filament\Schemas\Components\Section;`

*   **Antes**: `use Filament\Forms\Get;`
*   **Ahora**: `use Filament\Schemas\Components\Utilities\Get;`

Estos cambios se aplicaron en:
*   `UserForm.php`
*   `MonthlyClosureForm.php`
*   `ProductForm.php`
*   `OrderForm.php`

---

## 6. Estado Actual del Sistema

El sistema es funcional y robusto:
1.  **Backend**: Integridad de datos garantizada por MySQL Triggers.
2.  **Frontend Admin**: Interfaz reactiva construida con Filament v4, visualmente corregida y sin errores de consola.
3.  **Seguridad**: Acceso basado en Roles (Spatie) y validación de estados de usuario.

---

## 7. Automatización de Pagos y Resolución de Problemas (13/12/2025)

Se implementó un sistema automático para el control de saldos y validación de pagos, además de corregir errores de espacio de nombres en Filament.

### A. Automatización de Saldos y Deudas
Se modificó la base de datos para manejar el saldo de los pedidos de forma automática a nivel de motor de base de datos (Triggers), eliminando errores de cálculo manual.

1.  **Cambios en BD**:
    *   Se agregó la columna `pending_amount` a la tabla `orders`.
    *   Se crearon Triggers (`create_payment_logic_triggers`) que se disparan al INSERTAR, ACTUALIZAR o BORRAR pagos.
    *   **Lógica Automática**:
        *   Calcula: `Pendiente = Total - Pagos "Completados"`.
        *   Asigna Estado del Pedido:
            *   `liquidado` si debe 0.
            *   `abonado` si debe algo pero ha pagado una parte.
            *   `pendiente` si no tiene pagos válidos.

### B. Flujo de Validación Admins/Empleados
1.  **Formulario**: Los pagos creados por clientes nacen como "Pendientes". Solo Admins/Empleados pueden cambiar a "Validado".
2.  **Interfaz**: Se agregaron botones de acción rápida ("Validar" y "Rechazar") en la tabla de pagos.
3.  **Seguridad**: Campos críticos (`pending_amount`) bloqueados o de solo lectura en formularios.

### C. Resolución de Error "Class not found" en Actions
**Problema**: Al agregar botones a `PaymentsTable`, el sistema arrojó `Error 500: Class Filament\Tables\Actions\EditAction not found`.
**Causa**: Conflicto de namespaces. Se intentó usar `Filament\Tables\Actions\EditAction` mezclado con `Filament\Actions\EditAction` (que es el que se usa en otros recursos como Orders).
**Solución**: Se unificaron las importaciones para usar `Filament\Actions\EditAction` y `Filament\Actions\Action`, asegurando consistencia con el resto del proyecto generado. Se aprendió que en esta versión/structura, las acciones genéricas residen bajo `Filament\Actions`.
