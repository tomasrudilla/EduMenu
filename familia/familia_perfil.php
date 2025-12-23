<?php
session_start();
require '../conexion/db.php';

// 1. Validar sesión
$familia_id = $_SESSION['familia_id'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;

if (!$familia_id || !$user_id) {
    header("Location: ../login.php");
    exit;
}

// 2. OBTENER TODOS LOS HIJOS VINCULADOS
$stmt_hijos = $pdo->prepare("SELECT id, nombre_completo, curso FROM alumnos WHERE familia_id = ? AND activo = 1");
$stmt_hijos->execute([$familia_id]);
$mis_hijos = $stmt_hijos->fetchAll();

$alumno_id = isset($_GET['alumno_id']) ? (int)$_GET['alumno_id'] : ($mis_hijos[0]['id'] ?? 0);
$success_msg = null;

// 3. PROCESAR ACTUALIZACIÓN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_cambios'])) {
    $alergias = $_POST['alergias'];
    $telefono = $_POST['telefono'];
    $email = $_POST['email'];
    $target_alumno = $_POST['alumno_id_hidden'];

    try {
        $pdo->beginTransaction();
        $stmt1 = $pdo->prepare("UPDATE alumnos SET alergias = ? WHERE id = ? AND familia_id = ?");
        $stmt1->execute([$alergias, $target_alumno, $familia_id]);

        $stmt2 = $pdo->prepare("UPDATE familias SET telefono = ? WHERE id = ?");
        $stmt2->execute([$telefono, $familia_id]);

        $stmt3 = $pdo->prepare("UPDATE usuarios SET email = ? WHERE id = ?");
        $stmt3->execute([$email, $user_id]);

        $pdo->commit();
        $success_msg = "Los datos se han actualizado correctamente.";
    } catch (Exception $e) {
        $pdo->rollBack();
        $error_msg = "Error: " . $e->getMessage();
    }
}

// 4. OBTENER DATOS ACTUALES
if ($alumno_id > 0) {
    $stmt = $pdo->prepare("
        SELECT a.nombre_completo, a.curso, a.alergias, f.telefono, u.email 
        FROM alumnos a 
        JOIN familias f ON a.familia_id = f.id 
        JOIN usuarios u ON u.familia_id = f.id 
        WHERE a.id = ? AND f.id = ? AND u.id = ?
    ");
    $stmt->execute([$alumno_id, $familia_id, $user_id]);
    $datos = $stmt->fetch();
}

if (!$datos) {
    die("No se pudo recuperar la información. Verifica la vinculación de alumnos.");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduMenu | Perfil del Alumno</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        .form-input { width: 100%; padding: 12px 16px; background-color: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 14px; outline: none; transition: all 0.2s; }
        .form-input:focus { border-color: #ea580c; background-color: #fff; box-shadow: 0 0 0 4px rgba(234, 88, 12, 0.1); }
        .section-card { background: white; border-radius: 2rem; border: 1px solid #e2e8f0; padding: 1.5rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
        @media (min-width: 768px) { .section-card { padding: 2.5rem; } }
        /* Ocultar scrollbar en selector */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="flex flex-col md:flex-row min-h-screen text-slate-800">

    <?php include '../includes/sidebar_familia.php'; ?>

    <main class="flex-1 flex flex-col min-w-0">
        <header class="min-h-20 bg-white border-b border-slate-200 px-4 md:px-10 flex items-center justify-between shadow-sm z-10 py-4 gap-4">
            <h1 class="text-lg md:text-xl font-extrabold text-slate-900 uppercase">Perfil del Alumno</h1>
            <div class="hidden sm:flex items-center gap-2 text-orange-600">
                <i class="ph ph-info-bold text-xl"></i>
                <span class="text-[10px] font-black uppercase tracking-widest whitespace-nowrap">Información Verificada</span>
            </div>
        </header>

        <?php if (count($mis_hijos) > 1): ?>
        <div class="bg-white border-b border-slate-100 px-4 md:px-10 py-4 overflow-x-auto no-scrollbar">
            <div class="flex items-center gap-4">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex-shrink-0">Cambiar Perfil:</span>
                <div class="flex gap-2">
                    <?php foreach($mis_hijos as $hijo): ?>
                        <a href="?alumno_id=<?= $hijo['id'] ?>" 
                           class="px-4 py-2 rounded-xl text-xs font-bold transition-all border flex items-center gap-2 whitespace-nowrap
                           <?= ($alumno_id == $hijo['id']) ? 'bg-[#ea580c] text-white border-[#ea580c] shadow-lg shadow-orange-200' : 'bg-slate-50 text-slate-400 border-slate-200' ?>">
                            <i class="ph-bold ph-student"></i>
                            <?= htmlspecialchars($hijo['nombre_completo']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="flex-1 p-4 md:p-10 overflow-y-auto">
            <div class="max-w-4xl mx-auto">
                <form action="familia_perfil.php?alumno_id=<?= $alumno_id ?>" method="POST" class="space-y-6 md:space-y-8">
                    <input type="hidden" name="alumno_id_hidden" value="<?= $alumno_id ?>">

                    <div class="section-card">
                        <div class="flex items-center gap-3 mb-6 md:mb-8 border-b pb-4">
                            <i class="ph ph-student text-2xl text-orange-600"></i>
                            <h3 class="text-base md:text-lg font-extrabold text-slate-800">Datos Académicos</h3>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase mb-2 block">Nombre Completo</label>
                                <input type="text" class="form-input font-bold opacity-70" value="<?= htmlspecialchars($datos['nombre_completo']) ?>" disabled>
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase mb-2 block">Curso / División</label>
                                <input type="text" class="form-input font-bold opacity-70" value="<?= htmlspecialchars($datos['curso']) ?>" disabled>
                            </div>
                        </div>
                    </div>

                    <div class="section-card border-l-4 border-l-orange-500">
                        <div class="flex items-center gap-3 mb-6 md:mb-8 border-b pb-4">
                            <i class="ph ph-first-aid-kit text-2xl text-red-600"></i>
                            <h3 class="text-base md:text-lg font-extrabold text-slate-800">Salud de <?= explode(' ', $datos['nombre_completo'])[0] ?></h3>
                        </div>
                        <label class="text-[10px] font-bold text-slate-500 uppercase mb-2 block">Alergias o Condiciones Médicas</label>
                        <textarea name="alergias" rows="3" class="form-input font-semibold" placeholder="Sin restricciones declaradas."><?= htmlspecialchars($datos['alergias']) ?></textarea>
                    </div>

                    <div class="section-card">
                        <div class="flex items-center gap-3 mb-6 md:mb-8 border-b pb-4">
                            <i class="ph ph-phone-call text-2xl text-blue-600"></i>
                            <h3 class="text-base md:text-lg font-extrabold text-slate-800">Contacto de la Cuenta</h3>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase mb-2 block">Teléfono Familiar</label>
                                <input type="text" name="telefono" class="form-input font-semibold" value="<?= htmlspecialchars($datos['telefono']) ?>">
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase mb-2 block">Email Principal</label>
                                <input type="email" name="email" class="form-control form-input font-semibold" value="<?= htmlspecialchars($datos['email']) ?>">
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-center md:justify-end mt-10 pb-10">
                        <button type="submit" name="guardar_cambios" class="w-full md:w-auto bg-[#0f172a] text-white px-8 py-4 rounded-2xl font-extrabold hover:bg-slate-800 transition-all shadow-xl flex items-center justify-center gap-3 active:scale-95">
                            <i class="ph-bold ph-floppy-disk"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        <?php if ($success_msg): ?>
        Swal.fire({ 
            title: '¡Actualizado!', 
            text: '<?= $success_msg ?>', 
            icon: 'success', 
            confirmButtonColor: '#0f172a',
            borderRadius: '2.5rem'
        });
        <?php endif; ?>
    </script>
</body>
</html>