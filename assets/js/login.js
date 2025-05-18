// Script para manejar el envío del formulario de login y la redirección según el rol del usuario.

document.getElementById("loginForm").addEventListener("submit", function (e) {
    e.preventDefault(); // Evita el envío tradicional del formulario

    const username = document.getElementById("username").value;
    const password = document.getElementById("password").value;

    // Envía los datos al backend usando fetch y espera la respuesta en JSON
    fetch("../controller/loginController.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ username, password }),
    })
        .then((res) => res.json())
        .then((data) => {
            const mensaje = document.getElementById("mensaje");
            if (data.success) {
                mensaje.textContent = "Login exitoso. Rol: " + data.rol;
                mensaje.style.color = "green";
                // Redirecciona al dashboard correspondiente según el rol
                setTimeout(() => {
                    if (data.rol === "admin") {
                        window.location.href = "../view/admin/dashboard.html";
                    } else if (data.rol === "empleado") {
                        window.location.href = "../view/cliente/dashboard.html";
                    }
                }, 1000);
            } else {
                mensaje.textContent = "Usuario o contraseña incorrectos.";
                mensaje.style.color = "red";
            }
        });
});
