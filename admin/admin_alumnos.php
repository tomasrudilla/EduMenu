<?php
session_start();
require '../conexion/db.php';

// 1. Procesar la creación de un nuevo alumno
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['crear_alumno'])) {
    $nombre   = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $anio     = $_POST['anio'];
    $curso    = $_POST['curso']; 
    $alergias = $_POST['alergias'] ?? '';

    try {
        $stmt_ins = $pdo->prepare("INSERT INTO alumnos (nombre, apellido, anio, curso, alergias, status) VALUES (?, ?, ?, ?, ?, 'ACTIVE')");
        $stmt_ins->execute([$nombre, $apellido, $anio, $curso, $alergias]);
        
        header("Location: admin_alumnos.php?status=success");
        exit;
    } catch (PDOException $e) {
        $error_mensaje = "Error al crear el alumno: " . $e->getMessage();
    }
}

// 2. Parámetros de Filtro
$search = $_GET['search'] ?? '';
$fecha_filtro = $_GET['fecha'] ?? date('Y-m-d'); 
$anio_f = $_GET['anio'] ?? 'Todos';
$division_f = $_GET['division'] ?? 'Todos';
$estado = $_GET['estado'] ?? 'Todos';

// 3. Consulta SQL Principal
$sql = "SELECT 
            a.id, 
            CONCAT(a.nombre, ' ', a.apellido) AS alumno, 
            a.anio,
            a.curso, 
            f.apellido_responsable,
            s.tipo AS seleccion_tipo,
            s.plato_seleccionado,
            s.tiene_postre,
            m.plato_principal, 
            m.plato_alternativo, 
            m.opcion_veggie
        FROM alumnos a
        LEFT JOIN familias f ON a.familia_id = f.id
        LEFT JOIN selecciones s ON a.id = s.alumno_id AND s.fecha = ?
        LEFT JOIN menus m ON m.fecha = ?
        WHERE (a.nombre LIKE ? OR a.apellido LIKE ? OR f.apellido_responsable LIKE ?)";

$params = [$fecha_filtro, $fecha_filtro, "%$search%", "%$search%", "%$search%"];

if ($anio_f !== 'Todos') { $sql .= " AND a.anio = ?"; $params[] = $anio_f; }
if ($division_f !== 'Todos') { $sql .= " AND a.curso = ?"; $params[] = $division_f; }

if ($estado !== 'Todos') {
    if ($estado === 'Comen Menú') $sql .= " AND s.tipo = 'menu'";
    if ($estado === 'Traen Vianda') $sql .= " AND s.tipo = 'vianda'";
    if ($estado === 'Ausentes') $sql .= " AND s.tipo = 'ausente'";
    if ($estado === 'Sin Selección') $sql .= " AND s.tipo IS NULL";
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
        body { font-family: 'Plus Jakarta Sans', sans-serif; font-size: 16px; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .row-verified { opacity: 0.4; background-color: #f1f5f9 !important; transition: all 0.3s; }
        .row-verified p.alumno-name { text-decoration: line-through; }
        .check-orange { width: 26px; height: 26px; cursor: pointer; accent-color: #ea580c; }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 flex flex-col md:flex-row h-screen overflow-hidden">

    <?php include '../includes/sidebar_admin.php'; ?>

    <main class="flex-1 flex flex-col min-w-0 overflow-hidden">
        <header class="h-auto md:h-24 bg-white border-b border-slate-200 px-6 md:px-10 py-4 flex flex-col md:flex-row items-center justify-between gap-4 shrink-0 shadow-sm">
            <div>
                <h1 class="text-2xl font-black text-slate-900 uppercase tracking-tight">Panel de Alumnos</h1>
                <p class="text-sm text-slate-500 font-bold uppercase tracking-widest mt-1">
                    Día: <span class="text-orange-600"><?= date('d/m/Y', strtotime($fecha_filtro)) ?></span>
                </p>
            </div>
            <button onclick="openModal()" class="bg-orange-600 text-white px-6 py-3 rounded-2xl text-base font-bold hover:bg-orange-700 shadow-lg flex items-center gap-2 transition-all">
                <i class="ph ph-plus-circle text-xl"></i> Nuevo Alumno
            </button>
        </header>

        <section class="p-4 md:p-6 shrink-0">
            <form method="GET" class="bg-white p-6 rounded-[2.5rem] border border-slate-200 shadow-sm grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-5">
                <div class="lg:col-span-1">
                    <label class="text-[10px] font-black text-orange-600 uppercase mb-2 block tracking-widest">Búsqueda</label>
                    <input type="text" name="search" value="<?= htmlspecialchars($search); ?>" placeholder="Alumno..." class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-orange-500 outline-none">
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase mb-2 block tracking-widest">Fecha</label>
                    <input type="date" name="fecha" value="<?= $fecha_filtro ?>" onchange="this.form.submit()" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl">
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase mb-2 block tracking-widest">Año</label>
                    <select name="anio" onchange="this.form.submit()" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl font-bold">
                        <option value="Todos">Todos</option>
                        <?php foreach(['1','2','3','4','5','6'] as $a): ?>
                            <option value="<?= $a ?>" <?= $anio_f == $a ? 'selected' : ''; ?>><?= $a ?>º Año</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase mb-2 block tracking-widest">División</label>
                    <select name="division" onchange="this.form.submit()" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl font-bold">
                        <option value="Todos">Todas</option>
                        <?php foreach(['A','B','C'] as $d): ?>
                            <option value="<?= $d ?>" <?= $division_f == $d ? 'selected' : ''; ?>><?= $d ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase mb-2 block tracking-widest">Selección</label>
                    <select name="estado" onchange="this.form.submit()" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl font-bold">
                        <option value="Todos">Todos</option>
                        <option <?= $estado == 'Comen Menú' ? 'selected' : ''; ?>>Comen Menú</option>
                        <option <?= $estado == 'Traen Vianda' ? 'selected' : ''; ?>>Traen Vianda</option>
                        <option <?= $estado == 'Ausentes' ? 'selected' : ''; ?>>Ausentes</option>
                        <option <?= $estado == 'Sin Selección' ? 'selected' : ''; ?>>Sin Selección</option>
                    </select>
                </div>
            </form>
        </section>

        <section class="flex-1 px-4 md:px-6 pb-6 overflow-hidden">
            <div class="bg-white rounded-[2.5rem] border border-slate-200 shadow-sm h-full flex flex-col overflow-hidden">
                <div class="overflow-x-auto h-full">
                    <table class="w-full text-left border-collapse min-w-[1200px]">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-200 sticky top-0 z-10">
                                <th class="px-8 py-5 text-[11px] font-black text-slate-400 uppercase tracking-widest">Alumno / Familia</th>
                                <th class="px-6 py-5 text-[11px] font-black text-slate-400 uppercase tracking-widest text-center">Año</th>
                                <th class="px-6 py-5 text-[11px] font-black text-slate-400 uppercase tracking-widest text-center">Curso</th>
                                <th class="px-8 py-5 text-[11px] font-black text-slate-400 uppercase tracking-widest">Comida de Hoy</th>
                                <th class="px-8 py-5 text-[11px] font-black text-slate-400 uppercase tracking-widest text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 overflow-y-auto">
                            <?php foreach($alumnos as $alumno): 
                                $tipo = $alumno['seleccion_tipo'];
                                $cat_plato = $alumno['plato_seleccionado'];
                                $postre_val = $alumno['tiene_postre'];
                                
                                $nombre_plato = "—";
                                if ($tipo === 'menu') {
                                    if ($cat_plato === 'Principal') $nombre_plato = $alumno['plato_principal'] ?? 'No cargado';
                                    elseif ($cat_plato === 'Alternativo') $nombre_plato = $alumno['plato_alternativo'] ?? 'No cargado';
                                    elseif ($cat_plato === 'Veggie') $nombre_plato = $alumno['opcion_veggie'] ?? 'No cargado';
                                }
                            ?>
                            <tr class="hover:bg-slate-50/50 transition-all student-row" id="row-<?= $alumno['id'] ?>">
                                <td class="px-8 py-6">
                                    <p class="font-extrabold text-slate-900 text-lg alumno-name leading-tight"><?= htmlspecialchars($alumno['alumno']); ?></p>
                                    <p class="text-xs text-slate-400 font-bold uppercase tracking-tight mt-1">
                                        <?= $alumno['apellido_responsable'] ? 'Fam. ' . htmlspecialchars($alumno['apellido_responsable']) : 'S/V' ?>
                                    </p>
                                </td>
                                <td class="px-6 py-6 text-center">
                                    <span class="bg-orange-50 text-orange-600 px-3 py-1 rounded-lg text-sm font-black uppercase">
                                        <?= htmlspecialchars($alumno['anio']); ?>º
                                    </span>
                                </td>
                                <td class="px-6 py-6 text-center">
                                    <span class="bg-slate-100 text-slate-600 px-3 py-1 rounded-lg text-sm font-black uppercase">
                                        "<?= htmlspecialchars($alumno['curso']); ?>"
                                    </span>
                                </td>
                                <td class="px-8 py-6">
                                    <?php if (!$tipo): ?>
                                        <span class="text-slate-300 italic text-sm font-bold">Sin selección</span>
                                    <?php elseif ($tipo === 'ausente'): ?>
                                        <span class="text-red-500 font-black text-sm uppercase flex items-center gap-2"><i class="ph-fill ph-user-minus text-xl"></i> Ausente</span>
                                    <?php elseif ($tipo === 'vianda'): ?>
                                        <span class="text-blue-600 font-black text-sm uppercase flex items-center gap-2"><i class="ph-fill ph-backpack text-xl"></i> Vianda</span>
                                    <?php else: ?>
                                        <div class="flex flex-col">
                                            <span class="text-emerald-600 font-black text-base uppercase flex items-center gap-2">
                                                <i class="ph-fill ph-bowl-food text-2xl"></i> <?= htmlspecialchars($nombre_plato) ?>
                                            </span>
                                            <span class="text-xs font-bold mt-1 <?= $postre_val ? 'text-purple-600' : 'text-slate-400' ?> uppercase">
                                                Postre: <?= $postre_val ? "SÍ" : "NO" ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex items-center justify-center gap-4">
                                        <div class="flex items-center gap-2 bg-slate-50 px-3 py-2 rounded-xl border border-slate-100">
                                            <input type="checkbox" class="check-orange" id="check-<?= $alumno['id'] ?>" onchange="toggleVerify(<?= $alumno['id'] ?>, this)">
                                            <label for="check-<?= $alumno['id'] ?>" class="text-[10px] font-black text-slate-400 uppercase hidden sm:block">Visto</label>
                                        </div>
                                        <div class="flex gap-2">
                                            <a href="editar_alumno.php?id=<?= $alumno['id']; ?>" class="w-10 h-10 flex items-center justify-center hover:bg-orange-100 hover:text-orange-600 rounded-xl transition-all text-slate-400 border border-slate-100"><i class="ph ph-pencil-simple text-xl"></i></a>
                                            <a href="ver_alumno.php?id=<?= $alumno['id']; ?>" class="w-10 h-10 flex items-center justify-center hover:bg-blue-100 hover:text-blue-600 rounded-xl transition-all text-slate-400 border border-slate-100"><i class="ph ph-eye text-xl"></i></a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>

    <div id="modalAlumno" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
        <div class="bg-white w-full max-w-lg rounded-[2.5rem] shadow-2xl scale-95 transition-all overflow-hidden">
            <div class="p-8 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <h2 class="text-xl font-black text-slate-900 uppercase">Registrar Alumno</h2>
                <button onclick="closeModal()" class="text-slate-400 hover:text-red-500 transition-colors"><i class="ph ph-x-circle text-3xl"></i></button>
            </div>
            <form method="POST" class="p-8 space-y-6">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-[10px] font-black text-orange-600 uppercase mb-2 block">Nombre</label>
                        <input type="text" name="nombre" required class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-base outline-none focus:ring-2 focus:ring-orange-500">
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-orange-600 uppercase mb-2 block">Apellido</label>
                        <input type="text" name="apellido" required class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-base outline-none focus:ring-2 focus:ring-orange-500">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase mb-2 block">Año Escolar</label>
                        <select name="anio" class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-base font-bold">
                            <?php foreach(['1','2','3','4','5','6'] as $o): ?><option value="<?= $o ?>"><?= $o ?>º Año</option><?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase mb-2 block">División/Curso</label>
                        <select name="curso" class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-base font-bold">
                            <option>A</option><option>B</option><option>C</option>
                        </select>
                    </div>
                </div>
                <div class="pt-4 flex gap-4">
                    <button type="button" onclick="closeModal()" class="flex-1 py-4 font-black uppercase text-slate-400 bg-slate-100 rounded-2xl hover:bg-slate-200 transition-colors">Cerrar</button>
                    <button type="submit" name="crear_alumno" class="flex-1 py-4 font-black uppercase text-white bg-orange-600 rounded-2xl shadow-lg hover:bg-orange-700 transition-colors">Crear Alumno</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleVerify(id, checkbox) {
            const row = document.getElementById('row-' + id);
            const storageKey = 'verified_students_<?= $fecha_filtro ?>';
            let list = JSON.parse(localStorage.getItem(storageKey) || '[]');
            if (checkbox.checked) {
                row.classList.add('row-verified');
                if (!list.includes(id)) list.push(id);
            } else {
                row.classList.remove('row-verified');
                list = list.filter(item => item !== id);
            }
            localStorage.setItem(storageKey, JSON.stringify(list));
        }

        window.addEventListener('load', () => {
            const list = JSON.parse(localStorage.getItem('verified_students_<?= $fecha_filtro ?>') || '[]');
            list.forEach(id => {
                const row = document.getElementById('row-' + id);
                const check = document.getElementById('check-' + id);
                if (row && check) { row.classList.add('row-verified'); check.checked = true; }
            });
        });

        const modal = document.getElementById('modalAlumno');
        function openModal() { modal.classList.remove('hidden'); setTimeout(() => modal.querySelector('div').classList.remove('scale-95'), 10); }
        function closeModal() { modal.querySelector('div').classList.add('scale-95'); setTimeout(() => modal.classList.add('hidden'), 200); }
    </script>
</body>
</html>