<?php
session_start();
require 'conexion/db.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nombre']  = $user['nombre'];
        $_SESSION['rol']     = $user['rol'];
        $_SESSION['familia_id'] = $user['familia_id']; 

        if ($user['rol'] === 'admin') {
            header("Location: admin/admin_alumnos.php");
        } else {
            header("Location: familia/familia_planificador.html");
        }
        exit;
    } else {
        $error = "Credenciales incorrectas. Intentá de nuevo.";
    }
}
?>

<!DOCTYPE html>
<html lang="es" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión | EduMenu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    <style>
        :root {
            --primary: #ea580c; /* Naranja vibrante */
            --primary-hover: #f97316; /* Naranja más brillante para hover */
            --dark-bg: #0f172a; /* Slate 900 - Fondo principal */
            --dark-card: #1e293b; /* Slate 800 - Fondo de tarjeta */
            --dark-input: #334155; /* Slate 700 - Fondo de inputs */
            --text-light: #f8fafc;
            --text-muted: #94a3b8;
        }

        body { 
            background: white;
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: var(--text-light);
        }

        .login-card {
            background: var(--dark-card);
            border: 1px solid #334155;
            border-radius: 32px;
            /* Sombra naranja sutil */
            box-shadow: 0 25px 50px -12px rgba(234, 88, 12, 0.15);
            width: 100%;
            max-width: 850px;
            display: flex;
            overflow: hidden;
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Sidebar: Degradado Naranja a Oscuro */
        .login-sidebar {
            background: linear-gradient(160deg, var(--primary) 0%, #9a3412 100%);
            width: 40%;
            padding: 50px;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        /* Un toque sutil de fondo en el sidebar */
        .login-sidebar::before {
            content: ''; position: absolute; top:0; left:0; right:0; bottom:0;
            background: url('data:image/svg+xml,<svg width="100" height="100" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg"><circle cx="50" cy="50" r="40" stroke="white" stroke-width="1" fill="none" opacity="0.1"/></svg>');
            background-size: 200px; opacity: 0.2;
        }

        .login-form-area { padding: 60px; width: 60%; }
        .brand-icon { font-size: 3rem; margin-bottom: 25px; }
        h2 { font-weight: 800; color: white; }
        p.subtitle { color: var(--text-muted); font-size: 0.95rem; margin-bottom: 35px; }

        .form-label {
            font-weight: 700;
            font-size: 0.75rem;
            color: var(--primary); /* Etiquetas en naranja */
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .form-control {
            padding: 14px 18px;
            border-radius: 14px;
            border: 1px solid var(--dark-input);
            background: var(--dark-bg) !important; /* Fondo oscuro para inputs */
            color: white !important;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(234, 88, 12, 0.2);
        }
        
        /* Color del placeholder en dark mode */
        .form-control::placeholder { color: var(--text-muted); opacity: 0.7; }

        .btn-submit {
            background: var(--primary);
            color: white;
            border: none;
            padding: 16px;
            border-radius: 16px;
            font-weight: 700;
            width: 100%;
            transition: all 0.3s ease;
        }

        .btn-submit:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(234, 88, 12, 0.4);
        }

        .register-link a { color: var(--primary); text-decoration: none; font-weight: 700; transition:0.2s;}
        .register-link a:hover { color: var(--primary-hover); }

        @media (max-width: 768px) {
            .login-card { flex-direction: column; }
            .login-sidebar, .login-form-area { width: 100%; padding: 40px; }
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="login-sidebar">
            <div style="position: relative; z-index: 2;">
                <div class="brand-icon"><i class="ph ph-bowl-food"></i></div>
                <h1 class="fw-800 h3">EduMenu</h1>
                <p class="mt-2" style="opacity: 0.9; font-size: 0.9rem; line-height: 1.6;">
                    Tu acceso inteligente a la nutrición escolar. Gestioná menús y pagos en modo oscuro.
                </p>
            </div>
        </div>

        <div class="login-form-area">
            <h2>Bienvenido</h2>
            <p class="subtitle">Ingresá tus credenciales para continuar</p>

            <?php if($error): ?>
                <div class="alert alert-danger border-0 d-flex align-items-center" style="border-radius: 12px; background: rgba(220, 38, 38, 0.1); color: #fca5a5; font-size: 0.9rem;">
                    <i class="ph ph-warning-circle me-2 fs-5"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-4">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" placeholder="nombre@ejemplo.com" required>
                </div>

                <div class="mb-4">
                    <label class="form-label">Contraseña</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>

                <button type="submit" class="btn-submit">
                    Ingresar al Panel
                </button>

                <div class="mt-4 text-center text-muted small register-link">
                    ¿No tenés cuenta? <a href="registro.php">Registrar Familia</a>
                </div>
            </form>
        </div>
    </div>

</body>
</html>