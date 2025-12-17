# Documentación del Proyecto Laravel + Filament

Se ha implementado el sistema de gestión de pedidos de leche basado en la estructura de base de datos proporcionada.

## Características Implementadas

### 1. Base de Datos
- **Migraciones**: Se han creado migraciones que replican exactamente la estructura SQL original, incluyendo tipos de datos, llaves foráneas e índices.
- **Lógica de Negocio (BD)**: 
    - Se han implementado todos los **Disparadores (Triggers)** solicitados para:
        - Validación de cierres mensuales antes de insertar pedidos.
        - Validación de inventario y estado de producto en detalles de pedido.
        - Cálculo automático del total del pedido al modificar detalles.
        - Historial de precios automático al modificar productos.
    - Se han creado todas las **Vistas** para reportes y consultas complejas.

### 2. Panel Administrativo (FilamentPHP)
- **Usuarios**: Gestión completa con asignación de Roles y validación de tipos.
- **Roles y Permisos**: Sistema ACL robusto utilizando `spatie/laravel-permission`.
- **Productos**: 
    - Formulario dinámico para manejo de cajas vs piezas.
    - Control de inventario y precios.
    - Visualización de historial de precios (solo lectura).
- **Pedidos**:
    - Formulario complejo con `Repeater` para gestionar los detalles del pedido en la misma pantalla.
    - Clálculos reactivos en tiempo real (mientras se edita).
    - Selección inteligente del Cierre Mensual activo.
- **Pagos**: Registro de pagos con soporte para comprobantes (imágenes).
- **Cierres Mensuales**: Gestión de aperturas y cierres de mes.

### 3. Seguridad y Acceso
- Se ha creado un usuario administrador por defecto:
    - **Email**: `admin@lechera.com`
    - **Password**: `12345678`
- Se ha configurado un `Seeder` (`InitialDataSeeder`) que carga todos los Roles y Permisos definidos en el requerimiento.

## Instrucciones de Instalación / Reinicio

Si desea reiniciar la base de datos completamente con los datos de prueba:

```bash
php artisan migrate:fresh --seed --seeder=InitialDataSeeder
```

## Notas Adicionales
- La lógica de cálculo de totales reside en la base de datos (Triggers), pero se ha replicado visualmente en el frontend para mejorar la experiencia de usuario.
- Los errores de validación de base de datos (ej. "Período cerrado") se mostrarán como excepciones si se intentan violar las reglas desde la aplicación, garantizando la integridad de los datos.
