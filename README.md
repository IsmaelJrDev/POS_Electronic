# 🛒 Punto de Venta Web (Vanilla JS, PHP, MySQL, MVC)

Este proyecto es un sistema de punto de venta web desarrollado con **HTML, CSS, JavaScript, PHP y MySQL** bajo el patrón de arquitectura **MVC (Modelo-Vista-Controlador)**.

✅ Login interactivo sin recarga  
✅ Separación clara de lógica (MVC)  
✅ Compatible con servidores locales como XAMPP

## 🚀 Características principales

-   Login con validación de usuario y contraseña
-   Redirección automática por rol (`admin` o `empleado`)
-   Comunicación frontend-backend con `fetch()`
-   Estructura limpia tipo MVC

## 📂 Estructura del proyecto

```
/pos-system/
├── /model/           → Lógica de acceso a datos
├── /view/            → Vistas HTML del sistema
├── /controller/      → Manejo de la lógica y control de flujo
├── /assets/          → Archivos estáticos (CSS, JS, imágenes)
├── /config/          → Conexión a la base de datos
├── index.php         → Entrada (opcional)
└── README.md         → Este archivo
```

## 🛠️ Tecnologías

-   HTML5 / CSS3
-   JavaScript (Vanilla)
-   PHP (sin frameworks)
-   MySQL
-   XAMPP o similar

## 🗃️ Requisitos

-   PHP 7.4+
-   MySQL/MariaDB
-   Navegador moderno
-   Servidor local (XAMPP, WAMP, etc.)

## 🔧 Configuración del entorno

Este proyecto usa un archivo `.env` para almacenar las credenciales de la base de datos.  
Crea un archivo `.env` en la raíz del proyecto con el siguiente contenido:

```env
DB_HOST=localhost
DB_NAME=punto_venta
DB_USER=root
DB_PASS=
```

> ⚠️ El archivo `.env` **ya está excluido del repositorio** por medio de `.gitignore`.

El archivo `config/database.php` lee estas variables automáticamente usando `parse_ini_file()` para establecer la conexión.

## ⚙️ Instalación

1. Clona el repositorio:
    ```bash
    git clone https://github.com/tu_usuario/pos-system.git
    ```
2. Importa el archivo `.sql` (si lo generas) a tu base de datos en **phpMyAdmin**
3. Crea tu `.env` con los datos de conexión
4. Inicia tu servidor (XAMPP o similar) y abre `/view/login.html`

## 🔐 Credenciales de ejemplo

| Usuario | Contraseña | Rol      |
| ------- | ---------- | -------- |
| admin   | admin123   | admin    |
| juan    | juan123    | empleado |

## 📌 Estado actual

✅ Login básico  
⏳ Próximo: módulo de ventas, productos, tickets y reportes

## 📄 Licencia

Este proyecto es de código abierto bajo la licencia MIT.
