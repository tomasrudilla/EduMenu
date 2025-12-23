<?php
session_start();
require '../conexion/db.php';

// 1. Verificación de Seguridad (Opcional, descomentar si tenés el rol configurado)
// if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') { header("Location: ../login.php"); exit; }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { header("Location: admin_alumnos.php"); exit; }

// --- 2. PROCESAR ACCIONES (POST) ---

// A. Actualizar Datos
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['actualizar_alumno'])) {
    $nombre = $_POST['nombre_completo'];
    $curso = $_POST['curso_anio'] . " " . $_POST['curso_division'];
    $familia_id = !empty($_POST['familia_id']) ? $_POST['familia_id'] : null;
    $alergias = $_POST['alergias'];

    try {
        $stmt_upd = $pdo->prepare("UPDATE alumnos SET nombre_completo = ?, curso = ?, familia_id = ?, alergias = ? WHERE id = ?");
        $stmt_upd->execute([$nombre, $curso, $familia_id, $alergias, $id]);
        $success_msg = "Los datos de $nombre se actualizaron correctamente.";
    } catch (PDOException $e) {
        $error_mensaje = "Error al actualizar: " . $e->getMessage();
    }
}

// B. Eliminar Alumno (Zona de Peligro)
if (isset($_POST['eliminar_alumno'])) {
    $stmt_del = $pdo->prepare("DELETE FROM alumnos WHERE id = ?");
    $stmt_del->execute([$id]);
    header("Location: admin_alumnos.php?status=deleted");
    exit;
}

// --- 3. OBTENER DATOS ACTUALES ---
$stmt = $pdo->prepare("SELECT * FROM alumnos WHERE id = ?");
$stmt->execute([$id]);
$alumno = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$alumno) { die("Alumno no encontrado."); }

// Separación del curso para los selectores
$partes_curso = explode(" ", $alumno['curso']);
$anio_actual = $partes_curso[0] . (isset($partes_curso[1]) && $partes_curso[1] == "Año" ? " Año" : "");
$division_actual = end($partes_curso);

// Lista de familias
$familias = $pdo->query("SELECT id, nombre_responsable, apellido_responsable FROM familias ORDER BY apellido_responsable ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduMenu | Configuración de Alumno</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        .form-input {
            width: 100%; padding: 16px 20px; background-color: #f1f5f9;
            border: 1px solid #e2e8f0; border-radius: 20px; font-size: 0.95rem;
            outline: none; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-weight: 600; color: #1e293b;
        }
        .form-input:focus {
            border-color: #ea580c; background-color: #fff;
            box-shadow: 0 0 0 5px rgba(234, 88, 12, 0.1);
            transform: translateY(-1px);
        }
        .section-card {
            background: white; border-radius: 2.5rem; border: 1px solid #e2e8f0;
            padding: 40px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>
<body class="flex h-screen overflow-hidden">

    <?php include '../includes/sidebar_admin.php'; ?>

    <main class="flex-1 flex flex-col overflow-hidden">
        <header class="h-24 bg-white/80 backdrop-blur-md border-b border-slate-200 px-12 flex items-center justify-between sticky top-0 z-10">
            <div class="flex items-center gap-5">
                <a href="admin_alumnos.php" class="w-12 h-12 flex items-center justify-center bg-slate-100 rounded-2xl text-slate-500 hover:bg-orange-100 hover:text-orange-600 transition-all shadow-sm">
                    <i class="ph-bold ph-arrow-left text-xl"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight ">Configuración de Perfil</h1>
                    <nav class="flex gap-2 text-[10px] font-bold uppercase tracking-widest text-slate-400 mt-1">
                        <span class="hover:text-orange-500 cursor-pointer">Panel</span>
                        <span>/</span>
                        <span class="hover:text-orange-500 cursor-pointer">Alumnos</span>
                        <span>/</span>
                        <span class="text-slate-800">Editar</span>
                    </nav>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div class="bg-emerald-100 text-emerald-600 px-4 py-2 rounded-xl text-xs font-black uppercase tracking-tighter">
                    Activo en Sistema
                </div>
            </div>
        </header>

        <section class="flex-1 p-12 overflow-y-auto space-y-10 bg-slate-50/50">
            
            <form method="POST" id="formUpdate" class="max-w-5xl mx-auto space-y-8">
                
                <div class="section-card relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-orange-50 rounded-bl-full -mr-16 -mt-16 opacity-50"></div>
                    <div class="flex items-center gap-3 mb-10">
                        <div class="w-10 h-10 bg-orange-600 text-white rounded-xl flex items-center justify-center shadow-lg shadow-orange-200">
                            <i class="ph-bold ph-identification-card text-xl"></i>
                        </div>
                        <h3 class="text-lg font-black text-slate-800 uppercase tracking-tighter">Información Académica</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 relative z-10">
                        <div class="md:col-span-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 block ml-2">Nombre Completo del Estudiante</label>
                            <input type="text" name="nombre_completo" value="<?= htmlspecialchars($alumno['nombre_completo']) ?>" required class="form-input">
                        </div>
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 block ml-2">Año de Cursada</label>
                            <select name="curso_anio" class="form-input appearance-none">
                                <?php foreach(['1ero','2do','3ero','4to','5to','6to'] as $a): ?>
                                    <option value="<?= $a ?> Año" <?= ($anio_actual == $a." Año") ? 'selected' : '' ?>><?= $a ?> Año</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 block ml-2">División / Comisión</label>
                            <select name="curso_division" class="form-input appearance-none">
                                <?php foreach(['A','B','C','D'] as $d): ?>
                                    <option value="<?= $d ?>" <?= ($division_actual == $d) ? 'selected' : '' ?>><?= $d ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div class="section-card">
                        <div class="flex items-center gap-3 mb-8">
                            <div class="w-10 h-10 bg-red-100 text-red-600 rounded-xl flex items-center justify-center">
                                <i class="ph-bold ph-first-aid-kit text-xl"></i>
                            </div>
                            <h3 class="text-lg font-black text-slate-800 uppercase tracking-tighter">Restricciones de Salud</h3>
                        </div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 block ml-2">Alergias o Observaciones Médicas</label>
                        <textarea name="alergias" rows="4" placeholder="Escribe aquí si es celíaco, alérgico a frutos secos, etc..." class="form-input resize-none h-40"><?= htmlspecialchars($alumno['alergias']) ?></textarea>
                    </div>

                    <div class="section-card border-orange-200 bg-orange-50/20">
                        <div class="flex items-center gap-3 mb-8">
                            <div class="w-10 h-10 bg-blue-600 text-white rounded-xl flex items-center justify-center shadow-lg shadow-blue-100">
                                <i class="ph-bold ph-users-three text-xl"></i>
                            </div>
                            <h3 class="text-lg font-black text-slate-800 uppercase tracking-tighter">Vinculación Familiar</h3>
                        </div>
                        <label class="text-[10px] font-black text-orange-600 uppercase tracking-widest mb-3 block ml-2">Padre / Madre / Tutor Responsable</label>
                        <select name="familia_id" class="form-input h-16 border-orange-200">
                            <option value="">-- Sin familia vinculada --</option>
                            <?php foreach($familias as $f): ?>
                                <option value="<?= $f['id'] ?>" <?= ($alumno['familia_id'] == $f['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($f['apellido_responsable'] . ", " . $f['nombre_responsable']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="text-[10px] text-slate-400 mt-5 font-bold uppercase italic leading-relaxed">
                            <i class="ph-bold ph-info"></i> Vincular una familia permite que los responsables puedan seleccionar el menú desde su panel privado.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-6 pt-6">
                    <button type="submit" name="actualizar_alumno" class="flex-[3] bg-[#ea580c] text-white py-6 rounded-[2rem] font-black uppercase tracking-widest hover:bg-[#f97316] shadow-2xl shadow-orange-200 transition-all flex items-center justify-center gap-3">
                        <i class="ph-bold ph-cloud-arrow-up text-2xl"></i> Guardar Cambios en Ficha
                    </button>
                    <button type="button" onclick="confirmarEliminar()" class="flex-1 bg-white text-red-500 border-2 border-red-100 py-6 rounded-[2rem] font-black uppercase tracking-widest hover:bg-red-500 hover:text-white hover:border-red-500 transition-all shadow-sm">
                        <i class="ph-bold ph-trash text-xl"></i>
                    </button>
                </div>
            </form>

            <form method="POST" id="formDelete" class="hidden">
                <input type="hidden" name="eliminar_alumno" value="1">
            </form>

        </section>
    </main>

    <script>
        // Notificación de Éxito
        <?php if (isset($success_msg)): ?>
        Swal.fire({
            title: '¡Ficha Actualizada!',
            text: '<?= $success_msg ?>',
            icon: 'success',
            confirmButtonColor: '#ea580c',
            borderRadius: '2rem',
            customClass: { popup: 'rounded-[2.5rem]' }
        }).then(() => {
            window.location.href = 'ver_alumno.php?id=<?= $id ?>';
        });
        <?php endif; ?>

        // Confirmación de Eliminación
        function confirmarEliminar() {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción eliminará al alumno de forma permanente del sistema.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sí, eliminar alumno',
                cancelButtonText: 'Cancelar',
                borderRadius: '2.5rem'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('formDelete').submit();
                }
            })
        }
    </script>
</body>
</html>