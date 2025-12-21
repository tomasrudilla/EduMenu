<?php
require 'conexion/db.php';
$registro_exitoso = false; // Bandera para el script
$error_mensaje = "";

// 1. OBTENER TODOS LOS ALUMNOS (Igual que antes)
try {
    $stmt = $pdo->query("SELECT id, nombre_completo, curso FROM alumnos WHERE activo = 1 ORDER BY nombre_completo ASC");
    $todos_los_alumnos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $todos_los_alumnos = [];
}

// 2. PROCESAR EL REGISTRO
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $alumno_id      = $_POST['alumno_id'];
    $nombre_resp    = $_POST['nombre_responsable'];
    $apellido_resp  = $_POST['apellido_responsable'];
    $email          = $_POST['email'];
    $pass           = $_POST['password'];
    $confirm_pass   = $_POST['confirm_password'];

    if (empty($alumno_id)) {
        $error_mensaje = "Debes seleccionar a tu hijo de la lista.";
    } elseif ($pass !== $confirm_pass) {
        $error_mensaje = "Las contraseñas no coinciden.";
    } else {
        try {
            $pdo->beginTransaction();

            // PASO A: Insertar Familia (Limpio, sin mail ni pass)
            $stmtFam = $pdo->prepare("INSERT INTO familias (nombre_responsable, apellido_responsable) VALUES (?, ?)");
            $stmtFam->execute([$nombre_resp, $apellido_resp]);
            $id_familia_nueva = $pdo->lastInsertId();

            // PASO B: Actualizar Alumno
            $stmtAlu = $pdo->prepare("UPDATE alumnos SET familia_id = ? WHERE id = ?");
            $stmtAlu->execute([$id_familia_nueva, $alumno_id]);

            // PASO C: Crear Usuario
            $password_hash = password_hash($pass, PASSWORD_BCRYPT);
            $nombre_completo_user = $nombre_resp . " " . $apellido_resp;
            
            $stmtUser = $pdo->prepare("INSERT INTO usuarios (nombre, email, password, rol, familia_id) VALUES (?, ?, ?, 'familia', ?)");
            $stmtUser->execute([$nombre_completo_user, $email, $password_hash, $id_familia_nueva]);

            $pdo->commit();
            $registro_exitoso = true; // ACTIVAMOS EL POP-UP

        } catch (Exception $e) {
            $pdo->rollBack();
            $error_mensaje = "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <title>Registro Familia | EduMenu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        :root { --primary: #ea580c; --dark-bg: #0f172a; --dark-card: #1e293b; --dark-input: #334155; --text-light: #f8fafc; }
        body { background: radial-gradient(circle at top right, #2d1b14 0%, var(--dark-bg) 100%); font-family: 'Plus Jakarta Sans', sans-serif; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 40px 20px; color: var(--text-light); }
        .reg-card { background: var(--dark-card); border-radius: 32px; border: 1px solid #334155; box-shadow: 0 25px 50px -12px rgba(234, 88, 12, 0.15); width: 100%; max-width: 950px; display: flex; overflow: hidden; }
        .reg-sidebar { background: linear-gradient(160deg, var(--primary) 0%, #9a3412 100%); width: 30%; padding: 50px; color: white; display: flex; flex-direction: column; justify-content: space-between; }
        .reg-form-area { padding: 50px; width: 70%; }
        .filter-box { background: rgba(15, 23, 42, 0.6); padding: 25px; border-radius: 24px; border: 1px solid var(--dark-input); margin-bottom: 30px; }
        .form-label { font-weight: 700; font-size: 0.75rem; color: var(--primary); text-transform: uppercase; letter-spacing: 0.5px; }
        .form-control, .form-select { border-radius: 14px; padding: 12px; border: 1px solid var(--dark-input); background: var(--dark-bg) !important; color: white !important; }
        .form-control:focus, .form-select:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(234, 88, 12, 0.2); }
        .btn-submit { background: var(--primary); color: white; border: none; padding: 16px; border-radius: 16px; font-weight: 700; width: 100%; transition: 0.3s; }
        .btn-submit:hover { background: #f97316; transform: translateY(-2px); }
        
        /* Estilo personalizado para el pop-up de SweetAlert para que combine con el Dark Mode */
        .swal2-popup-custom {
            background: #1e293b !important;
            color: #ffffff !important;
            border-radius: 24px !important;
            border: 1px solid #334155 !important;
        }
    </style>
</head>
<body>

    <div class="reg-card">
        <div class="reg-sidebar">
            <div>
                <i class="ph ph-user-plus" style="font-size: 3.5rem;"></i>
                <h2 class="mt-4 fw-800 text-white">Unite a EduMenu</h2>
                <p class="mt-2" style="opacity: 0.9;">Vincular tu familia es el primer paso para una mejor alimentación.</p>
            </div>
            <div class="small opacity-75">© 2025 EduMenu</div>
        </div>

        <div class="reg-form-area">
            <?php if($error_mensaje): ?>
                <div class="alert alert-danger border-0 d-flex align-items-center mb-4" style="background: rgba(220, 38, 38, 0.1); color: #fca5a5; border-radius: 12px;">
                    <i class="ph ph-warning-circle me-2"></i> <?php echo $error_mensaje; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="filter-box">
                    <div class="row g-2">
                        <div class="col-md-3">
                            <label class="form-label">Año</label>
                            <select id="yearFilter" class="form-select">
                                <option value="">-</option>
                                <option value="1">1ero</option><option value="2">2do</option>
                                <option value="3">3ero</option><option value="4">4to</option>
                                <option value="5">5to</option><option value="6">6to</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">División</label>
                            <select id="courseFilter" class="form-select">
                                <option value="">-</option>
                                <option value="A">A</option><option value="B">B</option><option value="C">C</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mi Hijo/a es:</label>
                            <select id="studentSelect" name="alumno_id" class="form-select" disabled required>
                                <option value="">Primero elige filtros...</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Nombre Responsable</label>
                        <input type="text" name="nombre_responsable" class="form-control" placeholder="Juan" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Apellido Responsable</label>
                        <input type="text" name="apellido_responsable" class="form-control" placeholder="Perez" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email de Acceso</label>
                    <input type="email" name="email" class="form-control" placeholder="familia@ejemplo.com" required>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Contraseña</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Confirmar Contraseña</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                </div>

                <button type="submit" class="btn-submit shadow-lg shadow-orange-900/20">Finalizar y Vincular Familia</button>
            </form>
            
            <div class="text-center mt-4 small text-muted">
                ¿Ya tenés cuenta? <a href="login.php" style="color: var(--primary); font-weight: 700; text-decoration: none;">Iniciá sesión</a>
            </div>
        </div>
    </div>

    <script>
        // Data inyectada de PHP para el filtro local
        const ALUMNOS_DATA = <?php echo json_encode($todos_los_alumnos); ?>;
        const yearF = document.getElementById('yearFilter');
        const courseF = document.getElementById('courseFilter');
        const studentSelect = document.getElementById('studentSelect');

        function filterStudents() {
            const y = yearF.value;
            const c = courseF.value;
            studentSelect.innerHTML = '<option value="">Seleccioná...</option>';
            if (y && c) {
                const filtrados = ALUMNOS_DATA.filter(a => a.curso.includes(y) && a.curso.includes(c));
                if (filtrados.length > 0) {
                    filtrados.forEach(a => {
                        const opt = document.createElement('option');
                        opt.value = a.id;
                        opt.textContent = a.nombre_completo;
                        studentSelect.appendChild(opt);
                    });
                    studentSelect.disabled = false;
                } else {
                    studentSelect.innerHTML = '<option value="">No hay alumnos</option>';
                    studentSelect.disabled = true;
                }
            } else {
                studentSelect.disabled = true;
            }
        }
        yearF.addEventListener('change', filterStudents);
        courseF.addEventListener('change', filterStudents);

        // --- LÓGICA DEL POP-UP Y REDIRECCIÓN ---
        <?php if($registro_exitoso): ?>
            Swal.fire({
                title: '¡Registro Exitoso!',
                text: 'Tu familia ha sido vinculada. Redirigiendo al inicio de sesión...',
                icon: 'success',
                timer: 3000,
                timerProgressBar: true,
                showConfirmButton: false,
                customClass: {
                    popup: 'swal2-popup-custom'
                },
                background: '#1e293b',
                color: '#ffffff',
                iconColor: '#ea580c'
            }).then(() => {
                window.location.href = 'login.php';
            });
        <?php endif; ?>
    </script>
</body>
</html>