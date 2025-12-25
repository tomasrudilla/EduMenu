<?php
session_start();
require '../conexion/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { header("Location: admin_alumnos.php"); exit; }

// --- 2. PROCESAR ACCIONES (POST) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['actualizar_alumno'])) {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $anio = $_POST['anio'];
    $curso = $_POST['curso'];
    $familia_id = !empty($_POST['familia_id']) ? $_POST['familia_id'] : null;
    $alergias = $_POST['alergias'];

    try {
        // Actualizamos usando las nuevas columnas separadas
        $stmt_upd = $pdo->prepare("UPDATE alumnos SET nombre = ?, apellido = ?, anio = ?, curso = ?, familia_id = ?, alergias = ? WHERE id = ?");
        $stmt_upd->execute([$nombre, $apellido, $anio, $curso, $familia_id, $alergias, $id]);
        $success_msg = "Los datos de $nombre $apellido se actualizaron correctamente.";
    } catch (PDOException $e) {
        $error_mensaje = "Error al actualizar: " . $e->getMessage();
    }
}

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

// Lista de familias para el selector
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
            outline: none; transition: all 0.3s; font-weight: 600; color: #1e293b;
        }
        .form-input:focus { border-color: #ea580c; background-color: #fff; box-shadow: 0 0 0 5px rgba(234, 88, 12, 0.1); }
        .section-card { background: white; border-radius: 2.5rem; border: 1px solid #e2e8f0; padding: 40px; }
    </style>
</head>
<body class="flex h-screen overflow-hidden">

    <?php include '../includes/sidebar_admin.php'; ?>

    <main class="flex-1 flex flex-col overflow-hidden">
        <header class="h-24 bg-white/80 backdrop-blur-md border-b border-slate-200 px-12 flex items-center justify-between sticky top-0 z-10">
            <div class="flex items-center gap-5">
                <a href="admin_alumnos.php" class="w-12 h-12 flex items-center justify-center bg-slate-100 rounded-2xl text-slate-500 hover:bg-orange-100 hover:text-orange-600 transition-all">
                    <i class="ph-bold ph-arrow-left text-xl"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight ">Editar Estudiante</h1>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">ID Alumno: #<?= $id ?></p>
                </div>
            </div>
            <div class="bg-emerald-100 text-emerald-600 px-4 py-2 rounded-xl text-xs font-black uppercase">
                <?= htmlspecialchars($alumno['status']) ?>
            </div>
        </header>

        <section class="flex-1 p-12 overflow-y-auto space-y-10 bg-slate-50/50">
            <form method="POST" id="formUpdate" class="max-w-5xl mx-auto space-y-8">
                
                <div class="section-card relative">
                    <div class="flex items-center gap-3 mb-10">
                        <div class="w-10 h-10 bg-orange-600 text-white rounded-xl flex items-center justify-center shadow-lg">
                            <i class="ph-bold ph-identification-card text-xl"></i>
                        </div>
                        <h3 class="text-lg font-black text-slate-800 uppercase">Datos Personales</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase mb-3 block ml-2">Nombre</label>
                            <input type="text" name="nombre" value="<?= htmlspecialchars($alumno['nombre']) ?>" required class="form-input">
                        </div>
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase mb-3 block ml-2">Apellido</label>
                            <input type="text" name="apellido" value="<?= htmlspecialchars($alumno['apellido']) ?>" required class="form-input">
                        </div>
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase mb-3 block ml-2">Año Escolar</label>
                            <select name="anio" class="form-input">
                                <?php foreach(['1','2','3','4','5','6'] as $a): ?>
                                    <option value="<?= $a ?>" <?= ($alumno['anio'] == $a) ? 'selected' : '' ?>><?= $a ?>º Año</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase mb-3 block ml-2">División / Curso</label>
                            <select name="curso" class="form-input">
                                <?php foreach(['A','B','C','D'] as $d): ?>
                                    <option value="<?= $d ?>" <?= ($alumno['curso'] == $d) ? 'selected' : '' ?>><?= $d ?></option>
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
                            <h3 class="text-lg font-black text-slate-800 uppercase">Salud</h3>
                        </div>
                        <label class="text-[10px] font-black text-slate-400 uppercase mb-3 block">Observaciones Médicas</label>
                        <textarea name="alergias" rows="4" class="form-input resize-none h-40"><?= htmlspecialchars($alumno['alergias']) ?></textarea>
                    </div>

                    <div class="section-card border-orange-200 bg-orange-50/20">
                        <div class="flex items-center gap-3 mb-8">
                            <div class="w-10 h-10 bg-blue-600 text-white rounded-xl flex items-center justify-center">
                                <i class="ph-bold ph-users-three text-xl"></i>
                            </div>
                            <h3 class="text-lg font-black text-slate-800 uppercase">Familia</h3>
                        </div>
                        <label class="text-[10px] font-black text-orange-600 uppercase mb-3 block">Responsable Vinculado</label>
                        <select name="familia_id" class="form-input">
                            <option value="">-- Sin familia vinculada --</option>
                            <?php foreach($familias as $f): ?>
                                <option value="<?= $f['id'] ?>" <?= ($alumno['familia_id'] == $f['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($f['apellido_responsable'] . ", " . $f['nombre_responsable']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="flex items-center gap-6 pt-6">
                    <button type="submit" name="actualizar_alumno" class="flex-[3] bg-[#ea580c] text-white py-6 rounded-[2rem] font-black uppercase tracking-widest hover:bg-[#f97316] shadow-2xl transition-all">
                        Guardar Cambios
                    </button>
                    <button type="button" onclick="confirmarEliminar()" class="flex-1 bg-white text-red-500 border-2 border-red-100 py-6 rounded-[2rem] font-black uppercase hover:bg-red-500 hover:text-white transition-all">
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
        <?php if (isset($success_msg)): ?>
        Swal.fire({ title: '¡Actualizado!', text: '<?= $success_msg ?>', icon: 'success', confirmButtonColor: '#ea580c', borderRadius: '2rem' });
        <?php endif; ?>

        function confirmarEliminar() {
            Swal.fire({
                title: '¿Eliminar alumno?',
                text: "Esta acción no se puede deshacer.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'Sí, eliminar',
                borderRadius: '2.5rem'
            }).then((result) => {
                if (result.isConfirmed) { document.getElementById('formDelete').submit(); }
            })
        }
    </script>
</body>
</html>