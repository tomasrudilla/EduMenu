<?php
session_start();
require '../conexion/db.php';

// if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
//     header("Location: ../login.php");
//     exit;
// }

// 1. Lógica para limpiar logs antiguos (más de 30 días)
if (isset($_POST['limpiar_logs'])) {
    $pdo->query("DELETE FROM logs_auditoria WHERE fecha < DATE_SUB(NOW(), INTERVAL 30 DAY)");
    $msg_limpieza = "Se han eliminado los registros de más de 30 días.";
}

// 2. Filtros
$busqueda = $_GET['search'] ?? '';
$tipo_filtro = $_GET['tipo'] ?? 'Todas las acciones';

// 3. Consulta SQL dinámica
$sql = "SELECT l.*, u.nombre as nombre_usuario, u.rol as rol_usuario 
        FROM logs_auditoria l 
        LEFT JOIN usuarios u ON l.usuario_id = u.id 
        WHERE (l.accion LIKE ? OR l.detalle LIKE ? OR u.nombre LIKE ?)";

$params = ["%$busqueda%", "%$busqueda%", "%$busqueda%"];

if ($tipo_filtro !== 'Todas las acciones') {
    $sql .= " AND l.tipo = ?";
    // Mapeo de texto a ENUM
    $mapeo = ['Pagos' => 'PAGO', 'Menús' => 'MENU', 'Asistencias' => 'SELECCION'];
    $params[] = $mapeo[$tipo_filtro] ?? $tipo_filtro;
}

$sql .= " ORDER BY l.fecha DESC LIMIT 100";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll();

// Función para iconos y colores según tipo
function obtenerEstiloLog($tipo) {
    switch ($tipo) {
        case 'MENU': return ['icon' => 'ph-calendar-check-fill', 'bg' => 'bg-blue-100', 'text' => 'text-blue-600'];
        case 'SELECCION': return ['icon' => 'ph-fork-knife-fill', 'bg' => 'bg-orange-100', 'text' => 'text-orange-600'];
        case 'PAGO': return ['icon' => 'ph-credit-card-fill', 'bg' => 'bg-emerald-100', 'text' => 'text-emerald-600'];
        case 'PERFIL': return ['icon' => 'ph-user-gear-fill', 'bg' => 'bg-purple-100', 'text' => 'text-purple-600'];
        default: return ['icon' => 'ph-info-fill', 'bg' => 'bg-slate-100', 'text' => 'text-slate-600'];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>EduMenu | Auditoría</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .bg-dark-pro { background-color: #0f172a; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-thumb { background: #ea580c; border-radius: 10px; }
        .icon-box { display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 10px; }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 flex h-screen overflow-hidden">

    <?php include '../includes/sidebar_admin.php'; ?>

    <main class="flex-1 flex flex-col overflow-hidden">
        <header class="h-20 bg-white border-b border-slate-200 px-10 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-extrabold text-slate-900 uppercase">Registro de Auditoría</h1>
                <p class="text-sm text-slate-500">Historial de movimientos.</p>
            </div>
            <form method="POST">
                <button type="submit" name="limpiar_logs" class="bg-white border border-slate-200 text-slate-600 px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-slate-50 transition-all flex items-center gap-2">
                    <i class="ph ph-trash"></i> Limpiar logs > 30 días
                </button>
            </form>
        </header>

        <section class="p-10 pb-6">
            <form method="GET" class="flex flex-wrap gap-4 items-center justify-between bg-dark-pro p-4 rounded-3xl shadow-lg border border-slate-800">
                <div class="flex items-center gap-4 flex-1">
                    <div class="relative w-full max-w-md">
                        <i class="ph ph-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-500"></i>
                        <input type="text" name="search" value="<?= htmlspecialchars($busqueda) ?>" placeholder="Buscar..." class="w-full pl-12 pr-4 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white text-sm outline-none focus:border-orange-500">
                    </div>
                    <select name="tipo" onchange="this.form.submit()" class="bg-slate-900 border border-slate-700 text-slate-300 text-sm rounded-xl px-4 py-2.5 outline-none focus:border-orange-500">
                        <?php foreach(['Todas las acciones', 'Pagos', 'Menús', 'Asistencias'] as $opc): ?>
                            <option <?= $tipo_filtro == $opc ? 'selected' : '' ?>><?= $opc ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="text-slate-400 text-sm font-medium">
                    Mostrando <span class="text-orange-500"><?= count($logs) ?></span> registros
                </div>
            </form>
        </section>

        <section class="flex-1 p-10 pt-0 overflow-y-auto">
            <div class="bg-white rounded-[2rem] border border-slate-200 shadow-sm overflow-hidden">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            <th class="px-8 py-5 text-xs font-bold text-slate-400 uppercase tracking-widest">Tipo</th>
                            <th class="px-8 py-5 text-xs font-bold text-slate-400 uppercase tracking-widest">Fecha y Hora</th>
                            <th class="px-8 py-5 text-xs font-bold text-slate-400 uppercase tracking-widest">Usuario</th>
                            <th class="px-8 py-5 text-xs font-bold text-slate-400 uppercase tracking-widest">Acción Realizada</th>
                            <th class="px-8 py-5 text-xs font-bold text-slate-400 uppercase tracking-widest">Detalle</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach($logs as $log): 
                            $estilo = obtenerEstiloLog($log['tipo']);
                        ?>
                        <tr class="hover:bg-slate-50 transition-all">
                            <td class="px-8 py-5">
                                <div class="icon-box <?= $estilo['bg'] ?> <?= $estilo['text'] ?>">
                                    <i class="<?= $estilo['icon'] ?> text-xl"></i>
                                </div>
                            </td>
                            <td class="px-8 py-5 text-sm text-slate-500 font-semibold"><?= date('d/m - H:i:s', strtotime($log['fecha'])) ?></td>
                            <td class="px-8 py-5">
                                <span class="bg-slate-100 text-slate-700 px-3 py-1 rounded-lg text-xs font-bold uppercase">
                                    <?= htmlspecialchars($log['nombre_usuario'] ?? 'Sistema') ?>
                                </span>
                            </td>
                            <td class="px-8 py-5 font-bold text-slate-800 text-sm"><?= htmlspecialchars($log['accion']) ?></td>
                            <td class="px-8 py-5 text-sm text-slate-500 italic"><?= htmlspecialchars($log['detalle']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>