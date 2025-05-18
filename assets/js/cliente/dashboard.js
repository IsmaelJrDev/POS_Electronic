let productos = [];
let carrito = [];

// Cargar productos y categorías al iniciar
document.addEventListener("DOMContentLoaded", () => {
    fetch("../../controller/productosController.php")
        .then((res) => res.json())
        .then((data) => {
            productos = data;
            mostrarCategorias();
            mostrarProductos(productos);
        });

    // Buscador
    document
        .getElementById("buscador")
        .addEventListener("input", filtrarProductos);
    // Filtro por categoría
    document
        .getElementById("categoriaFiltro")
        .addEventListener("change", filtrarProductos);
    // Carrito
    document
        .getElementById("verCarrito")
        .addEventListener("click", mostrarCarrito);
    document.getElementById("cerrarModal").addEventListener("click", () => {
        document.getElementById("modalCarrito").style.display = "none";
    });
    window.onclick = function (event) {
        if (event.target == document.getElementById("modalCarrito")) {
            document.getElementById("modalCarrito").style.display = "none";
        }
    };
});

document.getElementById("cerrarSesion").addEventListener("click", function () {
    // Aquí puedes limpiar el almacenamiento local, cookies o hacer una petición al backend para cerrar sesión
    // Por ahora, solo redirige al login
    window.location.href = "../../view/login.html";
});

// Mostrar productos en el grid
function mostrarProductos(lista) {
    const contenedor = document.getElementById("productos");
    if (lista.length === 0) {
        contenedor.innerHTML = "<p>No hay productos para mostrar.</p>";
        return;
    }
    contenedor.innerHTML = lista
        .map(
            (prod) => `
        <div class="producto-card">
            <img src="../../src/img_productos/default.png" alt="Producto">
            <h3>${prod.nombre}</h3>
            <p>${prod.descripcion}</p>
            <div class="precio">$${prod.precio}</div>
            <button onclick="agregarAlCarrito(${prod.id})">Agregar al carrito</button>
        </div>
    `
        )
        .join("");
}

// Mostrar categorías únicas en el filtro
function mostrarCategorias() {
    const select = document.getElementById("categoriaFiltro");
    const categorias = [
        ...new Set(productos.map((p) => p.categoria).filter(Boolean)),
    ];
    categorias.forEach((cat) => {
        const option = document.createElement("option");
        option.value = cat;
        option.textContent = cat;
        select.appendChild(option);
    });
}

// Filtrar productos por búsqueda y categoría
function filtrarProductos() {
    const texto = document.getElementById("buscador").value.toLowerCase();
    const categoria = document.getElementById("categoriaFiltro").value;
    let filtrados = productos.filter(
        (p) =>
            (p.nombre.toLowerCase().includes(texto) ||
                p.descripcion.toLowerCase().includes(texto)) &&
            (categoria === "" || p.categoria === categoria)
    );
    mostrarProductos(filtrados);
}

// Carrito: agregar producto
window.agregarAlCarrito = function (id) {
    const prod = productos.find((p) => p.id === id);
    if (!prod) return;
    const item = carrito.find((i) => i.id === id);
    if (item) {
        item.cantidad += 1;
    } else {
        carrito.push({ ...prod, cantidad: 1 });
    }
    actualizarCarritoCantidad();
};

// Mostrar carrito en modal
function mostrarCarrito() {
    const modal = document.getElementById("modalCarrito");
    const items = document.getElementById("carritoItems");
    const total = document.getElementById("carritoTotal");
    if (carrito.length === 0) {
        items.innerHTML = "<p>El carrito está vacío.</p>";
        total.textContent = "";
    } else {
        items.innerHTML = carrito
            .map(
                (item) => `
            <div class="carrito-item">
                <span>${item.nombre} x${item.cantidad}</span>
                <span>$${(item.precio * item.cantidad).toFixed(2)}</span>
                <button onclick="eliminarDelCarrito(${
                    item.id
                })">Eliminar</button>
            </div>
        `
            )
            .join("");
        const suma = carrito.reduce(
            (acc, item) => acc + item.precio * item.cantidad,
            0
        );
        total.textContent = "Total: $" + suma.toFixed(2);
    }
    modal.style.display = "block";
}

// Eliminar producto del carrito
window.eliminarDelCarrito = function (id) {
    carrito = carrito.filter((item) => item.id !== id);
    actualizarCarritoCantidad();
    mostrarCarrito();
};

// Actualizar cantidad en el botón del carrito
function actualizarCarritoCantidad() {
    document.getElementById("carritoCantidad").textContent = carrito.reduce(
        (acc, item) => acc + item.cantidad,
        0
    );
}
