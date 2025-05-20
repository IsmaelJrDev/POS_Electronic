let productos = [];
let carrito = [];

// Cargar productos y categorías al iniciar
document.addEventListener("DOMContentLoaded", () => {
    fetch("../../controller/productosController.php")
        .then((res) => res.json())
        .then((data) => {
            productos = data;
            cargarCategorias();
            aplicarFiltros(); // Mostrar productos filtrados desde el inicio
        });

    document
        .getElementById("buscador")
        .addEventListener("input", aplicarFiltros);
    document
        .getElementById("categoriaFiltro")
        .addEventListener("change", aplicarFiltros);
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
    document
        .getElementById("cerrarSesion")
        .addEventListener("click", function () {
            window.location.href = "../../view/login.html";
        });
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
            <h3>${prod.nombre}</h3>
            <img src="../../assets/src/img_productos/${prod.imagen}" alt="${prod.nombre}">
            <p>${prod.descripcion}</p>
            <div class="precio">$${prod.precio}</div>
            <button onclick="agregarAlCarrito(${prod.id})">Agregar al carrito</button>
        </div>
    `
        )
        .join("");
}

// Llena el select de categorías únicas
function cargarCategorias() {
    const select = document.getElementById("categoriaFiltro");
    // Limpia opciones previas excepto la primera
    select.innerHTML = '<option value="">Todas las categorías</option>';
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

// Aplica búsqueda y filtro por categoría
function aplicarFiltros() {
    const texto = document.getElementById("buscador").value.toLowerCase();
    const categoria = document.getElementById("categoriaFiltro").value;
    const filtrados = productos.filter(
        (p) =>
            (p.nombre.toLowerCase().includes(texto) ||
                p.descripcion.toLowerCase().includes(texto)) &&
            (categoria === "" || p.categoria === categoria)
    );
    mostrarProductos(filtrados);
}

const numero = contacto.replace(/\D/g, ""); // Solo números
const mensaje = encodeURIComponent(
    `¡Gracias por tu compra!\nTu ticket:\nTotal: $${total}\nProductos:\n` +
        carrito.map((item) => `- ${item.nombre} x${item.cantidad}`).join("\n")
);
window.open(`https://wa.me/${numero}?text=${mensaje}`, "_blank");
