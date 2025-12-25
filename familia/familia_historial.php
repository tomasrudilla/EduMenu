<?php
session_start();
require '../conexion/db.php';

// 1. Validar sesión
$familia_id = $_SESSION['familia_id'] ?? null;
if (!$familia_id) {
    header("Location: ../login.php");
    exit;
}

// 2. OBTENER ALUMNOS VINCULADOS (Corregido según nueva estructura)
$stmt_hijos = $pdo->prepare("SELECT id, nombre, apellido, curso FROM alumnos WHERE familia_id = ? AND status = 'ACTIVE'");
$stmt_hijos->execute([$familia_id]);
$mis_hijos = $stmt_hijos->fetchAll();

$alumno_id = isset($_GET['alumno_id']) ? (int)$_GET['alumno_id'] : ($mis_hijos[0]['id'] ?? 0);

if ($alumno_id === 0) {
    $error_alumno = "No se encontró ningún alumno vinculado a su cuenta familiar.";
} else {
    $alumno_actual = null;
    foreach($mis_hijos as $h) { if($h['id'] == $alumno_id) $alumno_actual = $h; }
    
    // Concatenamos nombre y apellido para el encabezado
    $alumno_nombre = $alumno_actual['nombre'] . ' ' . $alumno_actual['apellido'];

    // 3. Obtener Precios
    $precios_res = $pdo->query("SELECT tipo, precio FROM precios_servicios")->fetchAll(PDO::FETCH_KEY_PAIR);
    $p_menu = $precios_res['menu'] ?? 0;
    $p_vianda = $precios_res['vianda'] ?? 0;

    // 4. Consulta de Historial
    $anio_lectivo = date('Y');
    $stmt_hist = $pdo->prepare("
        SELECT fecha, tipo, MONTH(fecha) as mes_num, WEEK(fecha, 1) as semana_num 
        FROM selecciones 
        WHERE alumno_id = ? AND YEAR(fecha) = ?
        ORDER BY fecha DESC
    ");
    $stmt_hist->execute([$alumno_id, $anio_lectivo]);
    $raw_history = $stmt_hist->fetchAll(PDO::FETCH_ASSOC);

    // 5. Agrupación
    $historial_agrupado = [];
    $total_invertido = 0; $total_platos = 0;
    $meses_es = [1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'];

    foreach ($raw_history as $reg) {
        $mes_txt = $meses_es[$reg['mes_num']];
        $sem = $reg['semana_num'];

        if (!isset($historial_agrupado[$mes_txt][$sem])) {
            $dto = new DateTime();
            $dto->setISODate($anio_lectivo, $sem);
            $lunes = $dto->format('d/m');
            $dto->modify('+4 days');
            $viernes = $dto->format('d/m');

            $historial_agrupado[$mes_txt][$sem] = ['rango' => "Semana del $lunes al $viernes", 'menus' => 0, 'viandas' => 0, 'subtotal' => 0];
        }

        if ($reg['tipo'] == 'menu') {
            $historial_agrupado[$mes_txt][$sem]['menus']++;
            $historial_agrupado[$mes_txt][$sem]['subtotal'] += $p_menu;
            $total_invertido += $p_menu; $total_platos++;
        } elseif ($reg['tipo'] == 'vianda') {
            $historial_agrupado[$mes_txt][$sem]['viandas']++;
            $historial_agrupado[$mes_txt][$sem]['subtotal'] += $p_vianda;
            $total_invertido += $p_vianda;
        }
    }
    $total_registros = count($raw_history);
    $asistencia = ($total_registros > 0) ? round(($total_platos / $total_registros) * 100) : 0;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduMenu | Historial de Consumo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-thumb { background: #ea580c; border-radius: 10px; }
        .month-divider { position: relative; display: flex; align-items: center; margin: 2rem 0 1rem 0; }
        .month-divider::after { content: ""; flex: 1; height: 1px; background: #e2e8f0; margin-left: 1rem; }
    </style>
</head>
<body class="flex flex-col md:flex-row h-screen overflow-hidden text-slate-900">

    <?php include '../includes/sidebar_familia.php'; ?>

    <main class="flex-1 flex flex-col overflow-hidden">
        <header class="min-h-[80px] bg-white border-b border-slate-200 px-4 md:px-10 py-4 flex items-center justify-between shadow-sm z-10">
            <div>
                <h1 class="text-lg md:text-xl font-extrabold uppercase tracking-tight leading-tight">Historial de Comidas</h1>
                <?php if(isset($alumno_nombre)): ?>
                    <p class="text-[9px] md:text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none mt-1">Reporte de: <?= htmlspecialchars($alumno_nombre) ?></p>
                <?php endif; ?>
            </div>
        </header>

        <?php if (count($mis_hijos) > 1): ?>
        <div class="bg-white border-b border-slate-100 px-4 md:px-10 py-4 overflow-x-auto whitespace-nowrap">
            <div class="flex items-center gap-4">
                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest flex-shrink-0">Ver historial de:</span>
                <div class="flex gap-2">
                    <?php foreach($mis_hijos as $hijo): ?>
                        <a href="?alumno_id=<?= $hijo['id'] ?>" 
                           class="px-4 py-2 rounded-xl text-[11px] font-bold transition-all border flex items-center gap-2
                           <?= ($alumno_id == $hijo['id']) ? 'bg-[#ea580c] text-white border-[#ea580c] shadow-lg shadow-orange-200' : 'bg-slate-50 text-slate-400 border-slate-200' ?>">
                            <i class="ph-bold ph-student"></i>
                            <?= htmlspecialchars($hijo['nombre'] . ' ' . $hijo['apellido']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="flex-1 p-4 md:p-10 overflow-y-auto">
            <?php if (isset($error_alumno)): ?>
                <div class="bg-white p-8 md:p-12 rounded-[2rem] md:rounded-[3rem] border border-slate-200 text-center shadow-sm">
                    <i class="ph ph-user-focus text-5xl md:text-6xl text-slate-200 mb-4"></i>
                    <p class="text-slate-500 font-bold text-sm md:text-base"><?= $error_alumno ?></p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6 mb-8 md:mb-10">
                    <div class="bg-white p-5 md:p-6 rounded-[1.5rem] md:rounded-[2rem] border border-slate-200 shadow-sm flex items-center gap-4 md:gap-5">
                        <div class="w-10 h-10 md:w-12 md:h-12 bg-orange-100 text-orange-600 rounded-xl md:rounded-2xl flex items-center justify-center text-xl md:text-2xl"><i class="ph ph-trend-up-bold"></i></div>
                        <div>
                            <p class="text-[9px] md:text-[10px] font-bold text-slate-400 uppercase tracking-widest">Inversión Total</p>
                            <h4 class="text-lg md:text-xl font-black">$<?= number_format($total_invertido, 0, ',', '.') ?></h4>
                        </div>
                    </div>
                    <div class="bg-white p-5 md:p-6 rounded-[1.5rem] md:rounded-[2rem] border border-slate-200 shadow-sm flex items-center gap-4 md:gap-5">
                        <div class="w-10 h-10 md:w-12 md:h-12 bg-blue-100 text-blue-600 rounded-xl md:rounded-2xl flex items-center justify-center text-xl md:text-2xl"><i class="ph ph-bowl-food-bold"></i></div>
                        <div>
                            <p class="text-[9px] md:text-[10px] font-bold text-slate-400 uppercase tracking-widest">Menús</p>
                            <h4 class="text-lg md:text-xl font-black"><?= $total_platos ?> Platos</h4>
                        </div>
                    </div>
                    <div class="bg-white p-5 md:p-6 rounded-[1.5rem] md:rounded-[2rem] border border-slate-200 shadow-sm flex items-center gap-4 md:gap-5 sm:col-span-2 lg:col-span-1">
                        <div class="w-10 h-10 md:w-12 md:h-12 bg-emerald-100 text-emerald-600 rounded-xl md:rounded-2xl flex items-center justify-center text-xl md:text-2xl"><i class="ph ph-check-circle-bold"></i></div>
                        <div>
                            <p class="text-[9px] md:text-[10px] font-bold text-slate-400 uppercase tracking-widest">Uso de Comedor</p>
                            <h4 class="text-lg md:text-xl font-black"><?= $asistencia ?>%</h4>
                        </div>
                    </div>
                </div>

                <?php if (empty($historial_agrupado)): ?>
                    <div class="bg-white p-12 rounded-[2rem] border border-slate-200 text-center shadow-sm">
                        <i class="ph ph-calendar-x text-5xl md:text-6xl text-slate-200 mb-4"></i>
                        <p class="text-slate-400 font-bold uppercase text-[10px] tracking-widest">No hay registros para el año <?= $anio_lectivo ?></p>
                    </div>
                <?php else: ?>
                    <?php foreach ($historial_agrupado as $mes => $semanas): ?>
                        <div class="month-divider">
                            <span class="text-[11px] md:text-sm font-black text-orange-600 uppercase tracking-widest"><?= $mes ?></span>
                        </div>
                        <div class="space-y-3 md:space-y-4">
                            <?php foreach ($semanas as $s): ?>
                                <div class="bg-white p-4 md:p-6 rounded-2xl md:rounded-3xl border border-slate-200 flex flex-col sm:flex-row items-start sm:items-center justify-between hover:border-orange-200 transition-all group gap-4">
                                    <div class="flex items-center gap-4 md:gap-5 w-full">
                                        <div class="w-10 h-10 md:w-12 md:h-12 bg-slate-50 text-slate-400 rounded-xl md:rounded-2xl flex items-center justify-center text-xl group-hover:bg-orange-50 group-hover:text-orange-600 transition-colors shadow-inner flex-shrink-0">
                                            <i class="ph ph-calendar-blank"></i>
                                        </div>
                                        <div class="overflow-hidden">
                                            <p class="font-extrabold text-slate-800 uppercase text-xs md:text-sm tracking-tighter truncate leading-tight"><?= $s['rango'] ?></p>
                                            <div class="flex flex-wrap items-center gap-3 mt-1 text-[9px] md:text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                                                <span class="flex items-center gap-1"><i class="ph ph-fork-knife"></i> <?= $s['menus'] ?> Menús</span>
                                                <span class="flex items-center gap-1"><i class="ph ph-backpack"></i> <?= $s['viandas'] ?> Viandas</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex sm:flex-col items-center sm:items-end justify-between w-full sm:w-auto border-t sm:border-0 pt-3 sm:pt-0">
                                        <p class="text-base md:text-lg font-black text-slate-800 leading-none mb-1">$<?= number_format($s['subtotal'], 0, ',', '.') ?></p>
                                        <span class="bg-emerald-50 text-emerald-600 px-2 py-0.5 rounded text-[8px] md:text-[9px] font-black uppercase tracking-tighter border border-emerald-100 whitespace-nowrap">Procesado</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>