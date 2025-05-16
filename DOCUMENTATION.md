d-mart/
├── backend/                    # Backend Node.js + Express + MySQL (MVC)
│   ├── controllers/           # Lógica que maneja las peticiones y responde (Controller)
│   │   ├── usuariosController.js
│   │   ├── productosController.js
│   │   └── ventasController.js
│   ├── models/                # Lógica de acceso a datos (Model)
│   │   ├── usuarioModel.js
│   │   ├── productoModel.js
│   │   └── ventaModel.js
│   ├── routes/                # Definición de rutas y endpoints
│   │   ├── usuariosRoutes.js
│   │   ├── productosRoutes.js
│   │   └── ventasRoutes.js
│   ├── middlewares/           # Middlewares personalizados (auth, errores, etc.)
│   ├── utils/                 # Funciones auxiliares y helpers
│   ├── db.js                  # Configuración y conexión a la base de datos MySQL
│   ├── index.js               # Entrada principal del backend (server)
│   ├── package.json
│   └── .env                  # Variables de entorno (no subir a Git)
│
├── frontend/                   # Frontend con Vite + Vanilla JS + TailwindCSS (View)
│   ├── public/                # Archivos estáticos (imágenes, favicon, etc.)
│   ├── src/
│   │   ├── assets/            # CSS, imágenes, etc.
│   │   ├── components/        # Componentes JS reutilizables (botones, modales)
│   │   ├── pages/             # Vistas o páginas principales
│   │   ├── services/          # Funciones para consumir API (fetch wrappers)
│   │   ├── main.js            # Punto de entrada JS
│   │   └── index.html         # Archivo HTML principal
│   ├── package.json
│   └── vite.config.js
│
├── docker-compose.yml          # Orquestación de servicios (mysql, backend, frontend, phpmyadmin)
├── .gitignore                 # Ignorar node_modules, .env, etc.
├── README.md                  # Documentación del proyecto
└── LICENSE                   # Licencia (MIT, etc.)
