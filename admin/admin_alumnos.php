<?php
session_start();
require '../conexion/db.php';

// 1. Verificación de Seguridad: Solo admin
// if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
//     header("Location: ../login.php");
//     exit;
// }

// 2. Procesar la creación de un nuevo alumno
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['crear_alumno'])) {
    $nombre = $_POST['nombre_completo'];
    $curso = $_POST['curso_anio'] . " " . $_POST['curso_division'];
    $alergias = $_POST['alergias'];

    try {
        $stmt_ins = $pdo->prepare("INSERT INTO alumnos (nombre_completo, curso, alergias, activo) VALUES (?, ?, ?, 1)");
        $stmt_ins->execute([$nombre, $curso, $alergias]);
        
        header("Location: admin_alumnos.php?status=success");
        exit;
    } catch (PDOException $e) {
        $error_mensaje = "Error al crear el alumno: " . $e->getMessage();
    }
}

// 3. Parámetros de Filtro
$search = isset($_GET['search']) ? $_GET['search'] : '';
$anio = isset($_GET['anio']) ? $_GET['anio'] : 'Todos';
$estado = isset($_GET['estado']) ? $_GET['estado'] : 'Todos';
$hoy = date('Y-m-d');

// 4. Construcción de la Consulta SQL Principal
$sql = "SELECT 
            a.id, 
            a.nombre_completo AS alumno, 
            a.curso, 
            f.nombre_responsable, 
            f.apellido_responsable,
            s.tipo AS seleccion_hoy,
            (SELECT COALESCE(SUM(t.monto), 0) FROM transacciones t WHERE t.familia_id = f.id) AS saldo
        FROM alumnos a
        LEFT JOIN familias f ON a.familia_id = f.id
        LEFT JOIN selecciones s ON a.id = s.alumno_id AND s.fecha = ?
        WHERE (a.nombre_completo LIKE ? OR f.nombre_responsable LIKE ? OR f.apellido_responsable LIKE ?)";

$params = [$hoy, "%$search%", "%$search%", "%$search%"];

if ($anio !== 'Todos') {
    $sql .= " AND a.curso LIKE ?";
    $params[] = "%$anio%";
}

if ($estado !== 'Todos') {
    if ($estado === 'Comen Menú') $sql .= " AND s.tipo = 'menu'";
    if ($estado === 'Traen Vianda') $sql .= " AND s.tipo = 'vianda'";
    if ($estado === 'Con Deuda') $sql .= " HAVING saldo < 0";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$alumnos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduMenu | Gestión de Alumnos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-thumb { background: #ea580c; border-radius: 10px; }
        .modal-active { overflow: hidden; }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 flex h-screen overflow-hidden">

    <?php include '../includes/sidebar_admin.php'; ?>

    <main class="flex-1 flex flex-col overflow-hidden">
        <header class="h-20 bg-white border-b border-slate-200 px-10 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-extrabold text-slate-900 uppercase tracking-tight">Panel de Alumnos</h1>
                <p class="text-sm text-slate-500">Gestiona las selecciones hoy (<?php echo date('d/m'); ?>).</p>
            </div>
            
            <div class="flex items-center gap-4">
                <button onclick="window.print()" class="bg-slate-100 px-4 py-2 rounded-xl text-sm font-bold text-slate-600 hover:bg-slate-200 transition-all">
                    <i class="ph ph-download-simple"></i> Exportar
                </button>
                <button onclick="openModal()" class="bg-orange-600 text-white px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-orange-700 transition-all shadow-lg shadow-orange-100">
                    <i class="ph ph-plus-circle"></i> Nuevo Alumno
                </button>
            </div>
        </header>

        <section class="p-10 pb-4">
            <form method="GET" class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label class="text-xs font-bold text-orange-600 uppercase mb-2 block tracking-widest">Búsqueda Rápida</label>
                    <div class="relative">
                        <i class="ph ph-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="text" name="search" value="<?= htmlspecialchars($search); ?>" placeholder="Alumno o familia..." class="w-full pl-12 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-orange-500 outline-none transition-all">
                    </div>
                </div>
                <div class="w-48">
                    <label class="text-xs font-bold text-slate-500 uppercase mb-2 block tracking-widest">Año</label>
                    <select name="anio" onchange="this.form.submit()" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-2xl outline-none focus:ring-2 focus:ring-orange-500">
                        <option value="Todos">Todos</option>
                        <?php foreach(['1ero','2do','3ero','4to','5to','6to'] as $a): ?>
                            <option value="<?= $a ?>" <?= $anio == $a ? 'selected' : ''; ?>><?= $a ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="w-48">
                    <label class="text-xs font-bold text-slate-500 uppercase mb-2 block tracking-widest">Estado</label>
                    <select name="estado" onchange="this.form.submit()" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-2xl outline-none focus:ring-2 focus:ring-orange-500">
                        <option value="Todos">Todos</option>
                        <option <?= $estado == 'Comen Menú' ? 'selected' : ''; ?>>Comen Menú</option>
                        <option <?= $estado == 'Traen Vianda' ? 'selected' : ''; ?>>Traen Vianda</option>
                        <option <?= $estado == 'Con Deuda' ? 'selected' : ''; ?>>Con Deuda</option>
                    </select>
                </div>
            </form>
        </section>

        <section class="flex-1 p-10 pt-0 overflow-y-auto">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest">Alumno / Familia</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest">Curso</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach($alumnos as $alumno): ?>
                        <tr class="hover:bg-slate-50/80 transition-all">
                            <td class="px-6 py-5">
                                <p class="font-bold text-slate-800"><?= htmlspecialchars($alumno['alumno']); ?></p>
                                <p class="text-xs text-slate-400 italic">
                                    <?= $alumno['apellido_responsable'] ? 'Fam. ' . htmlspecialchars($alumno['apellido_responsable']) : 'Sin familia vinculada' ?>
                                </p>
                            </td>
                            <td class="px-6 py-5 text-sm text-slate-600 font-medium"><?= htmlspecialchars($alumno['curso']); ?></td>
                            <td class="px-6 py-5 text-center">
                                <div class="flex justify-center gap-2">
                                    <a href="editar_alumno.php?id=<?= $alumno['id']; ?>" class="p-2 hover:bg-orange-100 hover:text-orange-600 rounded-xl transition-all text-slate-400" title="Editar">
                                        <i class="ph ph-pencil-simple-line text-lg"></i>
                                    </a>
                                    <a href="ver_alumno.php?id=<?= $alumno['id']; ?>" class="p-2 hover:bg-blue-100 hover:text-blue-600 rounded-xl transition-all text-slate-400" title="Ver Perfil">
                                        <i class="ph ph-eye text-lg"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <div id="modalAlumno" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-slate-900/60 backdrop-blur-sm">
        <div class="bg-white w-full max-w-lg rounded-[2.5rem] shadow-2xl border border-slate-200 overflow-hidden scale-95 transition-transform duration-300">
            <div class="p-8 border-b border-slate-100 flex justify-between items-center">
                <h2 class="text-xl font-extrabold text-slate-900 uppercase">Nuevo Alumno</h2>
                <button onclick="closeModal()" class="text-slate-400 hover:text-red-500 transition-colors"><i class="ph ph-x-circle text-3xl"></i></button>
            </div>
            <form method="POST" class="p-8 space-y-5">
                <div>
                    <label class="text-xs font-bold text-orange-600 uppercase mb-2 block tracking-widest">Nombre Completo</label>
                    <input type="text" name="nombre_completo" required placeholder="Ej: Juan Pérez" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl outline-none focus:ring-2 focus:ring-orange-500">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase mb-2 block tracking-widest">Año</label>
                        <select name="curso_anio" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl outline-none focus:ring-2 focus:ring-orange-500">
                            <?php foreach(['1ero','2do','3ero','4to','5to','6to'] as $anio_op): ?><option><?= $anio_op ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase mb-2 block tracking-widest">División</label>
                        <select name="curso_division" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl outline-none focus:ring-2 focus:ring-orange-500">
                            <option>A</option><option>B</option><option>C</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="text-xs font-bold text-slate-500 uppercase mb-2 block tracking-widest">Alergias / Observaciones</label>
                    <textarea name="alergias" rows="2" placeholder="Opcional..." class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl outline-none focus:ring-2 focus:ring-orange-500"></textarea>
                </div>
                <div class="pt-4 flex gap-3">
                    <button type="button" onclick="closeModal()" class="flex-1 py-4 text-sm font-bold text-slate-500 bg-slate-100 rounded-2xl">Cancelar</button>
                    <button type="submit" name="crear_alumno" class="flex-1 py-4 text-sm font-bold text-white bg-orange-600 rounded-2xl shadow-lg shadow-orange-100">Crear Alumno</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById('modalAlumno');
        function openModal() { modal.classList.remove('hidden'); document.body.classList.add('modal-active'); }
        function closeModal() { modal.classList.add('hidden'); document.body.classList.remove('modal-active'); }
        window.onclick = function(e) { if (e.target == modal) closeModal(); }

        <?php if(isset($_GET['status']) && $_GET['status'] == 'success'): ?>
        Swal.fire({ title: '¡Éxito!', text: 'Alumno creado correctamente.', icon: 'success', confirmButtonColor: '#ea580c' });
        <?php endif; ?>
    </script>
</body>
</html>