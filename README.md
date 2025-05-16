# POS_Electronic - Sistema Punto de Venta

Este es un sistema de punto de venta (POS) desarrollado con tecnologías modernas para ofrecer un ambiente seguro, escalable y fácil de mantener. El proyecto utiliza:

- **Frontend:** Vite + Vanilla JavaScript + TailwindCSS + SweetAlert
- **Backend:** Node.js + Express + MySQL
- **Base de datos:** MySQL 8.0
- **Docker:** Para orquestar y contenerizar toda la aplicación (frontend, backend, base de datos y phpMyAdmin)

---

## 🔧 Requisitos

- Docker y Docker Compose instalados
- Node.js (para desarrollo local, opcional si usas Docker)

---

## 🚀 Instalación y uso

1. Clona el repositorio:

   ```bash
   git clone https://github.com/tuusuario/d-mart.git
   cd d-mart
   ```

2. Crea el archivo `.env` en la carpeta `backend/` con las variables necesarias:

   ```env
   MYSQL_HOST=mysql
   MYSQL_USER=root
   MYSQL_PASSWORD=12345
   MYSQL_DATABASE=d_mart
   PORT=3000
   ```

3. Construye y levanta los contenedores con Docker Compose:

   ```bash
   docker-compose up --build
   ```

4. Accede a:

   - **Frontend (Vite):** http://localhost:5173
   - **Backend (API):** http://localhost:3000/api
   - **phpMyAdmin:** http://localhost:8080

---

## 🗂 Estructura del proyecto

```
d-mart/
├── backend/       # Código backend (Express)
├── frontend/      # Código frontend (Vite + JS + Tailwind)
├── docker-compose.yml
├── .gitignore
└── README.md
```

---

## 🛠 Tecnologías usadas

- [Node.js](https://nodejs.org/)
- [Express](https://expressjs.com/)
- [MySQL](https://www.mysql.com/)
- [Vite](https://vitejs.dev/)
- [TailwindCSS](https://tailwindcss.com/)
- [Docker](https://www.docker.com/)
- [phpMyAdmin](https://www.phpmyadmin.net/)

---

## 🔐 Seguridad y buenas prácticas

- Las credenciales de la base de datos están en un archivo `.env` que **no debe subirse a GitHub**.
- El backend expone APIs para que el frontend pueda interactuar con la base de datos de forma segura.
- El frontend **nunca** se conecta directamente a la base de datos.

---

## 📈 Mejoras futuras

- Implementar autenticación con JWT.
- Añadir roles de usuario (empleado, administrador).
- Añadir módulos para gestión de inventarios y reportes.
- Mejorar la interfaz con más funcionalidades.

---

## 🤝 Contribuciones

¡Las contribuciones son bienvenidas! Haz un fork, crea tu rama, y abre un pull request.

---

## 📄 Licencia

Este proyecto está bajo la licencia MIT.

---

---

**Dudas o sugerencias?** Contacta conmigo vía email o abre un issue.
