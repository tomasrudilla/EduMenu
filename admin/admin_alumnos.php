<?php
session_start();
require '../conexion/db.php';

// Parámetros de Filtro
$search = $_GET['search'] ?? '';
$fecha_filtro = $_GET['fecha'] ?? date('Y-m-d'); 
$anio = $_GET['anio'] ?? 'Todos';
$division = $_GET['division'] ?? 'Todos';
$estado = $_GET['estado'] ?? 'Todos';

// Consulta SQL con JOIN para platos reales
$sql = "SELECT 
            a.id, 
            a.nombre_completo AS alumno, 
            a.curso, 
            f.apellido_responsable,
            s.tipo AS seleccion_tipo,
            s.observacion AS seleccion_obs,
            m.plato_principal, 
            m.plato_alternativo, 
            m.opcion_veggie
        FROM alumnos a
        LEFT JOIN familias f ON a.familia_id = f.id
        LEFT JOIN selecciones s ON a.id = s.alumno_id AND s.fecha = ?
        LEFT JOIN menus m ON m.fecha = ?
        WHERE (a.nombre_completo LIKE ? OR f.nombre_responsable LIKE ? OR f.apellido_responsable LIKE ?)";

$params = [$fecha_filtro, $fecha_filtro, "%$search%", "%$search%", "%$search%"];

if ($anio !== 'Todos') { $sql .= " AND a.curso LIKE ?"; $params[] = "$anio%"; }
if ($division !== 'Todos') { $sql .= " AND a.curso LIKE ?"; $params[] = "%$division"; }

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
        
        /* Estilo para fila verificada */
        .row-verified { opacity: 0.5; background-color: #f1f5f9 !important; transition: all 0.3s; }
        .row-verified p.alumno-name { text-decoration: line-through; color: #94a3b8; }

        /* Custom Checkbox Naranja */
        .check-orange {
            width: 24px; height: 24px;
            cursor: pointer;
            accent-color: #ea580c;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 flex flex-col md:flex-row h-screen overflow-hidden">

    <?php include '../includes/sidebar_admin.php'; ?>

    <main class="flex-1 flex flex-col min-w-0 overflow-hidden">
        <header class="h-auto md:h-24 bg-white border-b border-slate-200 px-6 md:px-10 py-4 flex flex-col md:flex-row items-center justify-between gap-4 shrink-0">
            <div>
                <h1 class="text-2xl font-black text-slate-900 uppercase tracking-tight">Panel de Alumnos</h1>
                <p class="text-sm text-slate-500 font-bold uppercase tracking-widest">
                    Día: <span class="text-orange-600"><?= date('d/m/Y', strtotime($fecha_filtro)) ?></span>
                </p>
            </div>
            <button onclick="openModal()" class="bg-orange-600 text-white px-6 py-3 rounded-2xl text-base font-bold hover:bg-orange-700 transition-all shadow-lg flex items-center gap-2">
                <i class="ph ph-plus-circle text-xl"></i> Nuevo Alumno
            </button>
        </header>

        <section class="p-4 md:p-6 shrink-0">
            <form method="GET" class="bg-white p-6 rounded-[2.5rem] border border-slate-200 shadow-sm grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-5">
                <div class="lg:col-span-1">
                    <label class="text-xs font-black text-orange-600 uppercase mb-2 block tracking-widest">Búsqueda</label>
                    <input type="text" name="search" value="<?= htmlspecialchars($search); ?>" placeholder="Nombre..." class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-orange-500 outline-none text-base">
                </div>
                <div>
                    <label class="text-xs font-black text-slate-400 uppercase mb-2 block tracking-widest">Día</label>
                    <input type="date" name="fecha" value="<?= $fecha_filtro ?>" onchange="this.form.submit()" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-base">
                </div>
                <div>
                    <label class="text-xs font-black text-slate-400 uppercase mb-2 block tracking-widest">Año</label>
                    <select name="anio" onchange="this.form.submit()" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-base font-medium">
                        <option value="Todos">Todos</option>
                        <?php foreach(['1ero','2do','3ero','4to','5to','6to'] as $a): ?>
                            <option value="<?= $a ?>" <?= $anio == $a ? 'selected' : ''; ?>><?= $a ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-black text-slate-400 uppercase mb-2 block tracking-widest">División</label>
                    <select name="division" onchange="this.form.submit()" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-base font-medium">
                        <option value="Todos">Todas</option>
                        <?php foreach(['A','B','C'] as $d): ?>
                            <option value="<?= $d ?>" <?= $division == $d ? 'selected' : ''; ?>><?= $d ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-black text-slate-400 uppercase mb-2 block tracking-widest">Selección</label>
                    <select name="estado" onchange="this.form.submit()" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-base font-medium">
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
                    <table class="w-full text-left border-collapse min-w-[1000px]">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-200 sticky top-0 z-10">
                                <th class="px-8 py-5 text-xs font-black text-slate-400 uppercase tracking-widest text-center w-20">Check</th>
                                <th class="px-8 py-5 text-xs font-black text-slate-400 uppercase tracking-widest">Alumno / Familia</th>
                                <th class="px-8 py-5 text-xs font-black text-slate-400 uppercase tracking-widest text-center">Curso</th>
                                <th class="px-8 py-5 text-xs font-black text-slate-400 uppercase tracking-widest">Detalle de Comida</th>
                                <th class="px-8 py-5 text-xs font-black text-slate-400 uppercase tracking-widest text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 overflow-y-auto">
                            <?php foreach($alumnos as $alumno): 
                                $tipo = $alumno['seleccion_tipo'];
                                $obs = $alumno['seleccion_obs'];
                                
                                $nombre_plato = "Sin asignar";
                                $postre = "—";

                                if ($tipo === 'menu' && !empty($obs)) {
                                    if (strpos($obs, 'Plato: Principal') !== false) $nombre_plato = $alumno['plato_principal'] ?? 'No cargado';
                                    elseif (strpos($obs, 'Plato: Alternativo') !== false) $nombre_plato = $alumno['plato_alternativo'] ?? 'No cargado';
                                    elseif (strpos($obs, 'Plato: Veggie') !== false) $nombre_plato = $alumno['opcion_veggie'] ?? 'No cargado';
                                    $postre = (strpos($obs, 'Postre: SI') !== false) ? "SÍ" : "NO";
                                }
                            ?>
                            <tr class="hover:bg-slate-50/50 transition-all student-row" id="alumno-row-<?= $alumno['id'] ?>">
                                <td class="px-8 py-6 text-center">
                                    <input type="checkbox" 
                                           class="check-orange" 
                                           onchange="verifyStudent(<?= $alumno['id'] ?>, this)"
                                           id="check-<?= $alumno['id'] ?>">
                                </td>
                                <td class="px-8 py-6">
                                    <p class="font-extrabold text-slate-900 text-lg alumno-name"><?= htmlspecialchars($alumno['alumno']); ?></p>
                                    <p class="text-xs text-slate-400 font-bold uppercase tracking-tight">
                                        <?= $alumno['apellido_responsable'] ? 'Fam. ' . htmlspecialchars($alumno['apellido_responsable']) : 'Sin Vínculo' ?>
                                    </p>
                                </td>
                                <td class="px-8 py-6 text-center">
                                    <span class="bg-slate-100 px-4 py-1.5 rounded-xl text-sm font-black text-slate-600 uppercase">
                                        <?= htmlspecialchars($alumno['curso']); ?>
                                    </span>
                                </td>
                                <td class="px-8 py-6">
                                    <?php if (!$tipo): ?>
                                        <span class="text-slate-300 italic text-sm font-bold">Sin selección</span>
                                    <?php elseif ($tipo === 'ausente'): ?>
                                        <span class="text-red-500 font-black text-sm uppercase flex items-center gap-2">
                                            <i class="ph-fill ph-user-minus text-lg"></i> Ausente
                                        </span>
                                    <?php elseif ($tipo === 'vianda'): ?>
                                        <span class="text-blue-600 font-black text-sm uppercase flex items-center gap-2">
                                            <i class="ph-fill ph-backpack text-lg"></i> Trae Vianda
                                        </span>
                                    <?php else: ?>
                                        <div class="flex flex-col">
                                            <span class="text-emerald-600 font-black text-base uppercase flex items-center gap-2">
                                                <i class="ph-fill ph-bowl-food text-xl"></i> <?= htmlspecialchars($nombre_plato) ?>
                                            </span>
                                            <span class="text-xs font-bold text-slate-400 uppercase mt-1">Postre: <span class="<?= $postre == 'SÍ' ? 'text-purple-600' : '' ?>"><?= $postre ?></span></span>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-8 py-6 text-center">
                                    <div class="flex justify-center gap-3">
                                        <a href="editar_alumno.php?id=<?= $alumno['id']; ?>" class="w-10 h-10 flex items-center justify-center hover:bg-orange-100 hover:text-orange-600 rounded-xl transition-all text-slate-400 border border-slate-100">
                                            <i class="ph ph-pencil-simple text-xl"></i>
                                        </a>
                                        <a href="ver_alumno.php?id=<?= $alumno['id']; ?>" class="w-10 h-10 flex items-center justify-center hover:bg-blue-100 hover:text-blue-600 rounded-xl transition-all text-slate-400 border border-slate-100">
                                            <i class="ph ph-eye text-xl"></i>
                                        </a>
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

    <script>
        // Función para marcar alumnos como verificados
        function verifyStudent(id, checkbox) {
            const row = document.getElementById('alumno-row-' + id);
            const verifiedList = JSON.parse(localStorage.getItem('verified_students_<?= $fecha_filtro ?>') || '[]');

            if (checkbox.checked) {
                row.classList.add('row-verified');
                if (!verifiedList.includes(id)) verifiedList.push(id);
            } else {
                row.classList.remove('row-verified');
                const index = verifiedList.indexOf(id);
                if (index > -1) verifiedList.splice(index, 1);
            }

            localStorage.setItem('verified_students_<?= $fecha_filtro ?>', JSON.stringify(verifiedList));
        }

        // Cargar estado de verificación al iniciar la página
        window.addEventListener('load', () => {
            const verifiedList = JSON.parse(localStorage.getItem('verified_students_<?= $fecha_filtro ?>') || '[]');
            verifiedList.forEach(id => {
                const row = document.getElementById('alumno-row-' + id);
                const check = document.getElementById('check-' + id);
                if (row && check) {
                    row.classList.add('row-verified');
                    check.checked = true;
                }
            });
        });

        // Lógica modal (se mantiene igual)
        const modal = document.getElementById('modalAlumno');
        function openModal() { modal.classList.remove('hidden'); setTimeout(() => modal.querySelector('div').classList.remove('scale-95'), 10); }
        function closeModal() { modal.querySelector('div').classList.add('scale-95'); setTimeout(() => modal.classList.add('hidden'), 200); }
    </script>
</body>
</html>