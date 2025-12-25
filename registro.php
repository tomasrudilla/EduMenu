<?php
// 1. Carga de librerías
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; 
require 'conexion/db.php';

$registro_exitoso = false; 
$error_mensaje = "";

// 2. OBTENER ALUMNOS DISPONIBLES (Buscando por texto 'ACTIVE')
try {
    // La consulta ahora busca el string exacto 'ACTIVE'
    $stmt = $pdo->query("SELECT id, nombre, apellido, anio, curso FROM alumnos WHERE status = 'ACTIVE' AND familia_id IS NULL ORDER BY apellido ASC");
    $todos_los_alumnos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $todos_los_alumnos = [];
}

// 3. PROCESAR EL REGISTRO
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $alumnos_ids    = array_filter(array_unique($_POST['alumno_id'] ?? [])); 
    $nombre_resp    = trim($_POST['nombre_responsable']);
    $apellido_resp  = trim($_POST['apellido_responsable']);
    $email          = trim($_POST['email']);
    $pass           = $_POST['password'];
    $confirm_pass   = $_POST['confirm_password'];

    $stmtCheckEmail = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmtCheckEmail->execute([$email]);
    
    if (empty($alumnos_ids)) {
        $error_mensaje = "Debes vincular al menos un hijo.";
    } elseif ($stmtCheckEmail->fetch()) {
        $error_mensaje = "El correo electrónico ya está registrado.";
    } elseif ($pass !== $confirm_pass) {
        $error_mensaje = "Las contraseñas no coinciden.";
    } else {
        try {
            $pdo->beginTransaction();

            // PASO A: Crear la Familia
            $stmtFam = $pdo->prepare("INSERT INTO familias (nombre_responsable, apellido_responsable) VALUES (?, ?)");
            $stmtFam->execute([$nombre_resp, $apellido_resp]);
            $id_familia_nueva = $pdo->lastInsertId();

            // PASO B: Vincular alumnos
            $stmtAlu = $pdo->prepare("UPDATE alumnos SET familia_id = ? WHERE id = ? AND familia_id IS NULL");
            foreach ($alumnos_ids as $aid) {
                $stmtAlu->execute([$id_familia_nueva, $aid]);
            }

            // PASO C: Crear Usuario con nombre y apellido separados
            $password_hash = password_hash($pass, PASSWORD_BCRYPT);
            $stmtUser = $pdo->prepare("INSERT INTO usuarios (nombre, apellido, email, password, rol, familia_id) VALUES (?, ?, ?, ?, 'familia', ?)");
            $stmtUser->execute([$nombre_resp, $apellido_resp, $email, $password_hash, $id_familia_nueva]);

            $pdo->commit();
            $registro_exitoso = true; 

            // --- ENVÍO CON PHPMAILER ---
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com'; 
                $mail->SMTPAuth   = true;
                $mail->Username   = 'tomasrudilla@gmail.com'; 
                $mail->Password   = 'gpzn hbvi znqj nooq';   
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;
                $mail->setFrom('noreply@edumenu.com', 'EduMenu');
                $mail->addAddress($email, $nombre_resp);
                $mail->isHTML(true);
                $mail->Subject = 'Bienvenido a EduMenu';
                $mail->Body    = "<h2>¡Hola, $nombre_resp!</h2><p>Tu cuenta familiar ha sido creada.</p>";
                $mail->send();
            } catch (Exception $e) { }

        } catch (Exception $e) {
            $pdo->rollBack();
            $error_mensaje = "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro | EduMenu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root { --primary: #ea580c; --dark-bg: #0f172a; --dark-card: #1e293b; }
        body { background: #ffffff; font-family: 'Plus Jakarta Sans', sans-serif; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 40px 20px; }
        .reg-card { background: var(--dark-card); border-radius: 32px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.2); width: 100%; max-width: 1100px; display: flex; overflow: hidden; }
        .reg-sidebar { background: linear-gradient(160deg, var(--primary) 0%, #9a3412 100%); width: 30%; padding: 40px; color: white; display: flex; flex-direction: column; justify-content: space-between; }
        .reg-form-area { padding: 50px; width: 70%; background: var(--dark-card); color: white; }
        .form-label { font-weight: 700; font-size: 0.65rem; color: var(--primary); text-transform: uppercase; letter-spacing: 1px; }
        .form-control, .form-select { border-radius: 12px; background-color: var(--dark-bg) !important; border: 1px solid #334155; color: white !important; font-size: 0.85rem; padding: 12px 15px; }
        .form-control:focus, .form-select:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(234, 88, 12, 0.2); outline: none; }
        .student-row { background: rgba(15, 23, 42, 0.5); padding: 20px; border-radius: 20px; border: 1px solid #334155; margin-bottom: 15px; position: relative; }
        .btn-add { background: rgba(234, 88, 12, 0.1); color: var(--primary); border: 1px dashed var(--primary); border-radius: 12px; padding: 12px; width: 100%; font-weight: 700; font-size: 0.8rem; }
        .btn-submit { background: var(--primary); color: white; border: none; padding: 16px; border-radius: 16px; font-weight: 800; width: 100%; text-transform: uppercase; }
        .password-wrapper { position: relative; }
        .btn-eye { position: absolute; right: 15px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #94a3b8; cursor: pointer; z-index: 10; }
    </style>
</head>
<body>
    <div class="reg-card">
        <div class="reg-sidebar d-none d-md-flex">
            <div><i class="ph ph-users-four" style="font-size: 3rem;"></i><h2 class="mt-4 fw-800">Familia EduMenu</h2><p class="small opacity-75">Vincule a sus hijos para gestionar sus menús de forma unificada.</p></div>
            <div class="small opacity-50 uppercase tracking-widest font-bold">Registro Seguro</div>
        </div>

        <div class="reg-form-area">
            <h4 class="fw-800 mb-4 text-white">Crear Cuenta Familiar</h4>
            <?php if($error_mensaje): ?>
                <div class="alert alert-danger border-0 bg-danger bg-opacity-10 text-danger small mb-4 rounded-3"><i class="ph ph-warning-circle me-1"></i> <?= $error_mensaje ?></div>
            <?php endif; ?>

            <form method="POST" id="regForm">
                <div class="mb-4">
                    <label class="form-label mb-3">Vincular Alumnos</label>
                    <div id="studentsContainer">
                        <div class="student-row" id="row_1">
                            <div class="row g-2">
                                <div class="col-md-3">
                                    <label class="form-label text-white-50">Año</label>
                                    <select class="form-select year-f" onchange="filterLocal(1)">
                                        <option value="">-</option>
                                        <option value="1">1ero</option><option value="2">2do</option><option value="3">3ero</option>
                                        <option value="4">4to</option><option value="5">5to</option><option value="6">6to</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label text-white-50">Curso</label>
                                    <select class="form-select course-f" onchange="filterLocal(1)">
                                        <option value="">-</option><option value="A">A</option><option value="B">B</option><option value="C">C</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-white-50">Alumno</label>
                                    <select name="alumno_id[]" class="form-select student-s" required onchange="checkDuplicate(this)">
                                        <option value="">Elegí filtros...</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn-add" onclick="addStudentRow()"><i class="ph ph-plus-circle me-1"></i> Agregar otro hijo</button>
                </div>

                <hr class="my-4 opacity-10">

                <div class="row g-3 mb-3">
                    <div class="col-md-6"><label class="form-label">Nombre Responsable</label><input type="text" name="nombre_responsable" class="form-control" required placeholder="Nombre"></div>
                    <div class="col-md-6"><label class="form-label">Apellido Responsable</label><input type="text" name="apellido_responsable" class="form-control" required placeholder="Apellido"></div>
                </div>

                <div class="mb-3"><label class="form-label">Email Principal</label><input type="email" name="email" class="form-control" placeholder="familia@ejemplo.com" required></div>

                <div class="row g-3 mb-5">
                    <div class="col-md-6">
                        <label class="form-label">Contraseña</label>
                        <div class="password-wrapper"><input type="password" name="password" id="p1" class="form-control" required onkeyup="validatePass()"><button type="button" class="btn-eye" onclick="toggleEye('p1', this)"><i class="ph ph-eye"></i></button></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Confirmar Contraseña</label>
                        <div class="password-wrapper"><input type="password" name="confirm_password" id="p2" class="form-control" required onkeyup="validatePass()"><button type="button" class="btn-eye" onclick="toggleEye('p2', this)"><i class="ph ph-eye"></i></button></div>
                        <div id="matchIndicator" class="match-badge" style="display: none; color: #10b981; align-items: center; gap: 4px;"><i class="ph-fill ph-check-circle"></i> Coinciden</div>
                    </div>
                </div>

                <button type="submit" class="btn-submit">Finalizar Registro</button>
            </form>
        </div>
    </div>

    <script>
        const ALUMNOS_DATA = <?php echo json_encode($todos_los_alumnos); ?>;
        let rowCount = 1;

        function validatePass() {
            const p1 = document.getElementById('p1').value;
            const p2 = document.getElementById('p2').value;
            const indicator = document.getElementById('matchIndicator');
            indicator.style.display = (p1 !== "" && p1 === p2) ? 'flex' : 'none';
        }

        function addStudentRow() {
            if(rowCount >= 5) return;
            rowCount++;
            const container = document.getElementById('studentsContainer');
            const div = document.createElement('div');
            div.className = 'student-row';
            div.id = `row_${rowCount}`;
            div.innerHTML = `
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-2" style="font-size: 0.6rem" onclick="this.parentElement.remove();"></button>
                <div class="row g-2">
                    <div class="col-md-3"><label class="form-label text-white-50">Año</label><select class="form-select year-f" onchange="filterLocal(${rowCount})"><option value="">-</option><option value="1">1ero</option><option value="2">2do</option><option value="3">3ero</option><option value="4">4to</option><option value="5">5to</option><option value="6">6to</option></select></div>
                    <div class="col-md-3"><label class="form-label text-white-50">Curso</label><select class="form-select course-f" onchange="filterLocal(${rowCount})"><option value="">-</option><option value="A">A</option><option value="B">B</option><option value="C">C</option></select></div>
                    <div class="col-md-6"><label class="form-label text-white-50">Alumno</label><select name="alumno_id[]" class="form-select student-s" required onchange="checkDuplicate(this)"><option value="">Elegí filtros...</option></select></div>
                </div>`;
            container.appendChild(div);
        }

        function filterLocal(id) {
            const row = document.getElementById(`row_${id}`);
            const anio_f = row.querySelector('.year-f').value;
            const curso_f = row.querySelector('.course-f').value;
            const sel = row.querySelector('.student-s');
            sel.innerHTML = '<option value="">Cargando...</option>';
            const filtered = ALUMNOS_DATA.filter(a => a.anio == anio_f && a.curso === curso_f);
            if(filtered.length > 0) {
                sel.innerHTML = '<option value="">Selecciona...</option>';
                filtered.forEach(a => { 
                    sel.innerHTML += `<option value="${a.id}">${a.nombre} ${a.apellido}</option>`; 
                });
            } else { sel.innerHTML = '<option value="">Sin resultados</option>'; }
        }

        function checkDuplicate(selectElement) {
            const selects = document.querySelectorAll('.student-s');
            const values = Array.from(selects).map(s => s.value).filter(v => v !== "");
            if (values.filter(v => v === selectElement.value).length > 1) {
                Swal.fire({ title: '¡Duplicado!', text: 'Ya seleccionaste a este alumno.', icon: 'warning', confirmButtonColor: '#ea580c' });
                selectElement.value = "";
            }
        }

        function toggleEye(id, btn) {
            const input = document.getElementById(id);
            const icon = btn.querySelector('i');
            input.type = (input.type === "password") ? "text" : "password";
            icon.classList.toggle('ph-eye'); icon.classList.toggle('ph-eye-slash');
        }

        <?php if($registro_exitoso): ?>
            Swal.fire({ title: '¡Hecho!', text: 'Familia registrada.', icon: 'success', confirmButtonColor: '#ea580c' })
            .then(() => { window.location.href = 'login.php'; });
        <?php endif; ?>
    </script>
</body>
</html>