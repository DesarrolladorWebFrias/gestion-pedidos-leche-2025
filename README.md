<p align="center">
  <img src="docs/images/banner.png" alt="Gestión de Pedidos Banner" width="100%">
</p>

# Sistema de Gestión de Pedidos

![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel)
![Filament](https://img.shields.io/badge/Filament-4.x-FDAE4B?style=for-the-badge&logo=filament)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=for-the-badge&logo=mysql)
![TailwindCSS](https://img.shields.io/badge/TailwindCSS-3.x-06B6D4?style=for-the-badge&logo=tailwindcss)

## 📋 Descripción del Sistema

El **Sistema de Gestión de Pedidos** es una plataforma web robusta y moderna diseñada para optimizar el flujo completo de ventas y administración de inventario. Construido sobre la potencia de **Laravel 12** y la elegancia de **Filament 4**, este sistema ofrece una interfaz intuitiva y eficiente para administradores, empleados y clientes.

El enfoque arquitectónico del sistema es híbrido, aprovechando lo mejor de la lógica de aplicación en PHP y la integridad de datos a nivel de base de datos mediante **Triggers, Vistas y Procedimientos Almacenados** en MySQL.

---

## ✨ Características Principales

### 🔐 Control de Acceso Basado en Roles (RBAC)
Sistema de seguridad granular gestionado con `spatie/laravel-permission`:
- **Super Admin**: Control total del sistema y configuraciones.
- **Administrador**: Gestión operativa completa (Usuarios, Productos, Reportes).
- **Empleado**: Procesamiento de pedidos y atención al cliente.
- **Cliente**: Portal de autoservicio para realizar y rastrear sus propios pedidos.

### 📦 Gestión de Productos Avanzada
- Catálogo completo con soporte para imágenes y categorización.
- Control de unidades de medida (Piezas, Cajas).
- **Conversión Automática**: Cálculo inteligente de precios y cantidades basado en la selección (Caja vs Pieza).
- Control de inventario en tiempo real.

### 🛒 Flujo de Pedidos Eficiente
- Interfaz de creación de pedidos optimizada ("Point of Sale" style).
- Selección de productos visual con avatares circulares y detalles claros.
- Cálculo automático de subtotales, impuestos y totales.
- Sincronización de zona horaria (`America/Mexico_City`) para registros precisos.

### 📊 Dashboard y Reportes
- Widgets interactivos para visualización de métricas clave (Ventas del día, Pedidos pendientes, etc.).
- Vistas personalizadas según el rol del usuario.
- Reportes exportables.

---

## 🏗️ Stack Tecnológico y Arquitectura

### Backend & Core
- **Framework**: Laravel 12
- **Admin Panel**: FilamentPHP 4.0
- **Base de Datos**: MySQL (Estructura Relacional Normalizada)

### Frontend & UI
- **Estilos**: TailwindCSS
- **Componentes**: Blade + Filament Components
- **Interactividad**: Alpine.js / Livewire

### Arquitectura de Datos ("Hybrid Core")
A diferencia de los sistemas tradicionales MVC, este proyecto delega lógica crítica a la base de datos para garantizar la integridad:
- **Triggers**: Automatización de movimientos de inventario y actualizaciones de estado tras la creación de pedidos.
- **Vistas (Views)**: Abstracción de reportes complejos y agregaciones de datos para un acceso rápido.
- **Foreign Keys**: Integridad referencial estricta.

---

## 🚀 Instalación y Configuración

Sigue estos pasos para desplegar el proyecto en tu entorno local:

1.  **Clonar el Repositorio**
    ```bash
    git clone https://github.com/tu-usuario/gestion_pedidos.git
    cd gestion_pedidos
    ```

2.  **Instalar Dependencias**
    ```bash
    composer install
    npm install
    ```

3.  **Configurar Entorno**
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```
    *Configura tus credenciales de base de datos en el archivo `.env`.*

4.  **Base de Datos y Migraciones**
    El sistema incluye migraciones que configuran tanto las tablas como los triggers y vistas.
    ```bash
    php artisan migrate --seed
    ```
    *El seeder creará los roles y el usuario Super Admin inicial.*

5.  **Compilar Assets**
    ```bash
    npm run dev
    ```

6.  **Iniciar Servidor**
    ```bash
    php artisan serve
    ```
    Accede al panel administrativo en: `http://localhost:8000/admin`

---

## 🖼️ Galería (Próximamente)

> Espacio reservado para capturas de pantalla del Dashboard, Formulario de Pedidos y Listado de Productos.

---

<p align="center">
    Desarrollado con ❤️ usando Laravel y Filament.
</p>
