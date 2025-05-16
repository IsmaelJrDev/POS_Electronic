document.getElementById("loginForm").addEventListener("submit", function (e) {
    e.preventDefault();

    const username = document.getElementById("username").value;
    const password = document.getElementById("password").value;

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
                // Redirecciona a dashboard por rol:
                setTimeout(() => {
                    window.location.href =
                        data.rol === "admin"
                            ? "dashboard_admin.html"
                            : "ventas.html";
                }, 1000);
            } else {
                mensaje.textContent = "Usuario o contraseña incorrectos.";
                mensaje.style.color = "red";
            }
        });
});
