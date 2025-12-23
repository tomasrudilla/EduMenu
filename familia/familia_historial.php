<?php
session_start();
require '../conexion/db.php';

// 1. Validar que la sesión exista
$familia_id = $_SESSION['familia_id'] ?? null;

if (!$familia_id) {
    die("Error: No se encontró una sesión de familia activa.");
}

// 2. Obtener el Alumno y validar que exista
$stmt_alumno = $pdo->prepare("SELECT id, nombre_completo FROM alumnos WHERE familia_id = ? LIMIT 1");
$stmt_alumno->execute([$familia_id]);
$alumno = $stmt_alumno->fetch();

// --- SOLUCIÓN AL ERROR DEL OFFSET ---
if (!$alumno) {
    // Si no hay alumno, redirigimos o mostramos un mensaje amigable
    die("No se encontró ningún alumno vinculado a su cuenta familiar. Por favor, contacte a la administración.");
}

$alumno_id = $alumno['id'];
$alumno_nombre = $alumno['nombre_completo'];

// 3. Obtener Precios para cálculos
$precios_res = $pdo->query("SELECT tipo, precio FROM precios_servicios")->fetchAll(PDO::FETCH_KEY_PAIR);
$p_menu = $precios_res['menu'] ?? 0;
$p_vianda = $precios_res['vianda'] ?? 0;

// 4. Consulta de Historial
$anio_lectivo = 2025;
$stmt_hist = $pdo->prepare("
    SELECT fecha, tipo, MONTH(fecha) as mes, WEEK(fecha, 1) as semana_num 
    FROM selecciones 
    WHERE alumno_id = ? AND YEAR(fecha) = ?
    ORDER BY fecha DESC
");
$stmt_hist->execute([$alumno_id, $anio_lectivo]);
$raw_history = $stmt_hist->fetchAll(PDO::FETCH_ASSOC);

// 5. Agrupación de datos
$historial_agrupado = [];
$total_invertido = 0;
$total_platos = 0;
$meses_es = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
    7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
];

foreach ($raw_history as $reg) {
    $mes_txt = $meses_es[$reg['mes']];
    $sem = $reg['semana_num'];

    if (!isset($historial_agrupado[$mes_txt][$sem])) {
        $dto = new DateTime();
        $dto->setISODate($anio_lectivo, $sem);
        $lunes = $dto->format('d/m');
        $dto->modify('+4 days');
        $viernes = $dto->format('d/m');

        $historial_agrupado[$mes_txt][$sem] = [
            'rango' => "Semana del $lunes al $viernes",
            'menus' => 0,
            'viandas' => 0,
            'subtotal' => 0
        ];
    }

    if ($reg['tipo'] == 'menu') {
        $historial_agrupado[$mes_txt][$sem]['menus']++;
        $historial_agrupado[$mes_txt][$sem]['subtotal'] += $p_menu;
        $total_invertido += $p_menu;
        $total_platos++;
    } elseif ($reg['tipo'] == 'vianda') {
        $historial_agrupado[$mes_txt][$sem]['viandas']++;
        $historial_agrupado[$mes_txt][$sem]['subtotal'] += $p_vianda;
        $total_invertido += $p_vianda;
    }
}
$asistencia = (count($raw_history) > 0) ? round(($total_platos / count($raw_history)) * 100) : 0;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>EduMenu | Historial</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        .month-divider { position: relative; display: flex; align-items: center; margin: 2rem 0 1rem 0; }
        .month-divider::after { content: ""; flex: 1; height: 1px; background: #e2e8f0; margin-left: 1rem; }
    </style>
</head>
<body class="flex h-screen overflow-hidden text-slate-900">

    <?php include '../includes/sidebar_familia.php'; ?>

    <main class="flex-1 flex flex-col overflow-hidden">
        <header class="h-20 bg-white border-b border-slate-200 px-10 flex items-center justify-between shadow-sm">
            <div>
                <h1 class="text-xl font-extrabold uppercase italic tracking-tighter">Historial de Comidas</h1>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Alumno: <?= htmlspecialchars($alumno_nombre) ?></p>
            </div>
        </header>

        <div class="flex-1 p-10 overflow-y-auto">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                <div class="bg-white p-6 rounded-[2rem] border border-slate-200 shadow-sm flex items-center gap-5">
                    <div class="w-12 h-12 bg-orange-100 text-orange-600 rounded-2xl flex items-center justify-center text-2xl"><i class="ph ph-trend-up-bold"></i></div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Inversión Total</p>
                        <h4 class="text-xl font-black">$<?= number_format($total_invertido, 0, ',', '.') ?></h4>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-[2rem] border border-slate-200 shadow-sm flex items-center gap-5">
                    <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center text-2xl"><i class="ph ph-bowl-food-bold"></i></div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Menús</p>
                        <h4 class="text-xl font-black"><?= $total_platos ?> Platos</h4>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-[2rem] border border-slate-200 shadow-sm flex items-center gap-5">
                    <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-2xl flex items-center justify-center text-2xl"><i class="ph ph-check-circle-bold"></i></div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Asistencia</p>
                        <h4 class="text-xl font-black"><?= $asistencia ?>%</h4>
                    </div>
                </div>
            </div>

            <?php foreach ($historial_agrupado as $mes => $semanas): ?>
                <div class="month-divider">
                    <span class="text-sm font-black text-orange-600 uppercase tracking-widest"><?= $mes ?></span>
                </div>
                <div class="space-y-4">
                    <?php foreach ($semanas as $s): ?>
                        <div class="bg-white p-6 rounded-3xl border border-slate-200 flex items-center justify-between hover:border-orange-200 transition-all">
                            <div class="flex items-center gap-5">
                                <div class="w-12 h-12 bg-slate-50 text-slate-400 rounded-2xl flex items-center justify-center text-xl"><i class="ph ph-calendar-blank"></i></div>
                                <div>
                                    <p class="font-extrabold text-slate-800"><?= $s['rango'] ?></p>
                                    <div class="flex items-center gap-3 mt-1 text-xs font-medium text-slate-500">
                                        <span><i class="ph ph-fork-knife"></i> <?= $s['menus'] ?> Menús</span>
                                        <span><i class="ph ph-backpack"></i> <?= $s['viandas'] ?> Viandas</span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-black">$<?= number_format($s['subtotal'], 0, ',', '.') ?></p>
                                <p class="text-[10px] font-bold text-emerald-500 uppercase">Procesado</p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>