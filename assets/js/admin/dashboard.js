document.addEventListener("DOMContentLoaded", () => {
    fetch("../../controller/dashboardController.php")
        .then((res) => res.json())
        .then((data) => {
            // Productos críticos
            document.querySelector(".card.danger .card-info p").textContent =
                data.productosCriticos;

            // Ventas totales
            document.querySelector(".card.success .card-info p").textContent =
                "$" + Number(data.ventasTotales).toLocaleString();

            // Ventas diarias (hoy)
            document.querySelector(".card.warning .card-info p").textContent =
                "$" + Number(data.ventasHoy).toLocaleString();

            // Ventas diarias (últimos 7 días)
            const bars = document.querySelectorAll(
                ".progress-bar-container .bar"
            );
            data.ventasDiarias.reverse(); // Para que el más antiguo sea el primero
            bars.forEach((bar, i) => {
                if (data.ventasDiarias[i]) {
                    const total = data.ventasDiarias[i].total;
                    bar.style.height = Math.min(100, total / 2) + "%"; // Ajusta el divisor según tus montos
                    bar.title = `${data.ventasDiarias[i].dia}: $${total}`;
                    bar.querySelector("span").textContent = "$" + total;
                } else {
                    bar.style.height = "10%";
                    bar.title = "";
                    bar.querySelector("span").textContent = "$0";
                }
            });
        });
});

// Modal para productos críticos
// Asegúrate de tener el HTML del modal en tu dashboard.html
document.querySelector(".card.danger").addEventListener("click", () => {
    fetch("../../controller/productosCriticosController.php")
        .then((res) => res.json())
        .then((productos) => {
            const modal = document.getElementById("modalCriticos");
            const lista = document.getElementById("listaCriticos");
            lista.innerHTML = "";

            if (productos.length === 0) {
                lista.innerHTML = "<li>No hay productos críticos.</li>";
            } else {
                productos.forEach((p) => {
                    const li = document.createElement("li");
                    li.textContent = `${p.nombre} (Stock: ${p.stock})`;
                    lista.appendChild(li);
                });
            }
            modal.style.display = "flex";
        });
});

document.getElementById("cerrarModalCriticos").addEventListener("click", () => {
    document.getElementById("modalCriticos").style.display = "none";
});

document.getElementById("btnCompras").addEventListener("click", function (e) {
    e.preventDefault();
    document.querySelector(".main").innerHTML = `
        <section class="compras-section">
            <h2>Registrar Compra</h2>
            <form id="formCompra">
                <label>
                    Producto:
                    <select id="productoCompra" required>
                        <option value="">Cargando productos...</option>
                    </select>
                </label>
                <label>
                    Cantidad:
                    <input type="number" id="cantidadCompra" min="1" required>
                </label>
                <label>
                    Precio de compra:
                    <input type="number" id="precioCompra" min="0.01" step="0.01" required>
                </label>
                <button type="submit">Registrar Compra</button>
            </form>
            <div id="mensajeCompra"></div>
        </section>
    `;

    // Llenar el select de productos
    fetch("../../controller/productosController.php")
        .then((res) => res.json())
        .then((productos) => {
            const select = document.getElementById("productoCompra");
            select.innerHTML =
                '<option value="">Selecciona un producto</option>';
            productos.forEach((p) => {
                const option = document.createElement("option");
                option.value = p.id;
                option.textContent = p.nombre;
                select.appendChild(option);
            });
        });

    // Manejar el formulario de compras
    document
        .getElementById("formCompra")
        .addEventListener("submit", function (e) {
            e.preventDefault();
            const producto_id = document.getElementById("productoCompra").value;
            const cantidad = document.getElementById("cantidadCompra").value;
            const precio = document.getElementById("precioCompra").value;

            fetch("../../controller/compraController.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                },
                body: `producto_id=${producto_id}&cantidad=${cantidad}&precio=${precio}`,
            })
                .then((res) => res.json())
                .then((data) => {
                    document.getElementById("mensajeCompra").textContent =
                        data.message;
                    if (data.success) {
                        document.getElementById("formCompra").reset();
                    }
                });
        });
});

document.getElementById("btnProductos").addEventListener("click", function (e) {
    e.preventDefault();
    fetch("../../controller/productosController.php?todos=1")
        .then((res) => res.json())
        .then((productos) => {
            const content = `
                <h2>Lista de Productos</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th><th>Nombre</th><th>Stock</th><th>Precio</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${productos
                            .map(
                                (p) => `
                            <tr>
                                <td>${p.id}</td>
                                <td>${p.nombre}</td>
                                <td>${p.stock}</td>
                                <td>$${p.precio}</td>
                            </tr>
                        `
                            )
                            .join("")}
                    </tbody>
                </table>
            `;
            document.getElementById("dashboardContent").innerHTML = content;
        });
});

document.getElementById("btnVentas").addEventListener("click", function (e) {
    e.preventDefault();
    fetch("../../controller/ventaController.php?resumen=1")
        .then((res) => res.json())
        .then((ventas) => {
            const content = `
                <h2>Ventas Recientes</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th><th>Fecha</th><th>Total</th><th>Cliente</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${ventas
                            .map(
                                (v) => `
                            <tr>
                                <td>${v.id}</td>
                                <td>${v.fecha}</td>
                                <td>$${v.total}</td>
                                <td>${v.contacto || "-"}</td>
                            </tr>
                        `
                            )
                            .join("")}
                    </tbody>
                </table>
            `;
            document.getElementById("dashboardContent").innerHTML = content;
        });
});
