window.agregarAlCarrito = function (id) {
    const prod = productos.find((p) => p.id === id);
    if (!prod) return; // Si no existe el producto, no hace nada

    const item = carrito.find((i) => i.id === id);
    const cantidadEnCarrito = item ? item.cantidad : 0;

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
        document.getElementById("btnFinalizarCompra").style.display = "none";
        document.getElementById("contacto").style.display = "none";
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
        const subtotal = carrito.reduce(
            (acc, item) => acc + item.precio * item.cantidad,
            0
        );
        const iva = subtotal * 0.16;
        const totalFinal = subtotal + iva;
        total.innerHTML = `
            <div>Subtotal: $${subtotal.toFixed(2)}</div>
            <div>IVA (16%): $${iva.toFixed(2)}</div>
            <div><strong>Total: $${totalFinal.toFixed(2)}</strong></div>
        `;
        document.getElementById("btnFinalizarCompra").style.display = "block";
        document.getElementById("contacto").style.display = "block";
    }
    modal.style.display = "block";
}

// Finalizar compra
document.getElementById("btnFinalizarCompra").onclick = function () {
    const contacto = document.getElementById("contacto").value.trim();
    if (!contacto) {
        alert("Por favor, ingresa tu teléfono o correo electrónico.");
        return;
    }
    if (carrito.length === 0) return;
    const subtotal = carrito.reduce(
        (acc, item) => acc + item.precio * item.cantidad,
        0
    );
    const iva = subtotal * 0.16; // 16% de IVA
    const total = subtotal + iva;

    fetch("../../controller/ventaController.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            productos: carrito,
            total,
            contacto,
            iva,
            subtotal,
        }),
    })
        .then((res) => res.json())
        .then((data) => {
            if (data.success) {
                alert(
                    "¡Compra realizada con éxito! Se abrirá WhatsApp para enviar tu ticket."
                );
                const numero = contacto.replace(/\D/g, "");
                const mensaje = encodeURIComponent(
                    `¡Gracias por tu compra!\nTu ticket:\nSubtotal: $${subtotal.toFixed(
                        2
                    )}\nIVA: $${iva.toFixed(2)}\nTotal: $${total.toFixed(
                        2
                    )}\nProductos:\n` +
                        carrito
                            .map((item) => `- ${item.nombre} x${item.cantidad}`)
                            .join("\n")
                );
                window.open(
                    `https://wa.me/${numero}?text=${mensaje}`,
                    "_blank"
                );
                carrito = [];
                actualizarCarritoCantidad();
                aplicarFiltros();
                document.getElementById("modalCarrito").style.display = "none";
                document.getElementById("contacto").value = "";
            } else {
                alert(data.message || "Error al registrar la venta.");
            }
        });
};

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
