<?php
session_start();
require '../conexion/db.php';

// // 1. Seguridad: Solo admin
// if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
//     header("Location: ../login.php");
//     exit;
// }

// 2. Cálculos de Fechas
$mes_actual = date('m');
$anio_actual = date('Y');
$mes_pasado = date('m', strtotime("-1 month"));
$anio_pasado = date('Y', strtotime("-1 month"));

// 3. Obtener Recaudación del Mes Actual (Solo ingresos: monto > 0)
$stmt = $pdo->prepare("SELECT SUM(monto) FROM transacciones WHERE MONTH(fecha) = ? AND YEAR(fecha) = ? AND monto > 0");
$stmt->execute([$mes_actual, $anio_actual]);
$recaudado_mes = $stmt->fetchColumn() ?: 0;

// 4. Obtener Recaudación del Mes Pasado (Para la comparativa)
$stmt = $pdo->prepare("SELECT SUM(monto) FROM transacciones WHERE MONTH(fecha) = ? AND YEAR(fecha) = ? AND monto > 0");
$stmt->execute([$mes_pasado, $anio_pasado]);
$recaudado_pasado = $stmt->fetchColumn() ?: 0;

$diferencia_porcentaje = 0;
if ($recaudado_pasado > 0) {
    $diferencia_porcentaje = (($recaudado_mes - $recaudado_pasado) / $recaudado_pasado) * 100;
}

// 5. Deuda Total y Cantidad de Familias Morosas
$sql_deuda = "SELECT SUM(saldo_familia) as total_deuda, COUNT(*) as familias_morosas 
              FROM (
                SELECT SUM(monto) as saldo_familia 
                FROM transacciones 
                GROUP BY familia_id 
                HAVING saldo_familia < 0
              ) as subquery";
$stmt_deuda = $pdo->query($sql_deuda);
$data_deuda = $stmt_deuda->fetch();

// 6. Obtener Precios Actuales desde la nueva tabla precios_servicios
$stmt_precios = $pdo->query("SELECT tipo, precio FROM precios_servicios");
$precios = $stmt_precios->fetchAll(PDO::FETCH_KEY_PAIR);
$precio_menu = $precios['menu'] ?? 0;
$precio_vianda = $precios['vianda'] ?? 0;

// 7. Listado de Últimas Transacciones (Incluyendo método de pago)
$stmt_trans = $pdo->prepare("SELECT t.*, f.apellido_responsable, f.nombre_responsable 
                             FROM transacciones t 
                             JOIN familias f ON t.familia_id = f.id 
                             WHERE t.monto > 0 
                             ORDER BY t.fecha DESC 
                             LIMIT 10");
$stmt_trans->execute();
$ultimas_trans = $stmt_trans->fetchAll();

// Función para formatear fechas
function formatearFecha($fecha) {
    $d = date('Y-m-d', strtotime($fecha));
    if ($d == date('Y-m-d')) return "Hoy, " . date('H:i', strtotime($fecha)) . " hs";
    if ($d == date('Y-m-d', strtotime("-1 day"))) return "Ayer, " . date('H:i', strtotime($fecha)) . " hs";
    return date('d/m, H:i', strtotime($fecha)) . " hs";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduMenu | Finanzas Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-thumb { background: #ea580c; border-radius: 10px; }
        .finance-card { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .finance-card:hover { transform: translateY(-5px); box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05); }
    </style>
</head>
<body class="bg-[#f8fafc] text-slate-900 flex h-screen overflow-hidden">

    <?php include '../includes/sidebar_admin.php'; ?>

    <main class="flex-1 flex flex-col overflow-hidden">
        
        <header class="h-24 bg-white border-b border-slate-200 px-10 flex items-center justify-between sticky top-0 z-10 shadow-sm">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 uppercase tracking-tighter italic">Balance Financiero</h1>
                <p class="text-sm text-slate-500 font-medium">Control centralizado de ingresos y deudas familiares.</p>
            </div>
            
            <div class="flex items-center gap-4">
                <button onclick="window.print()" class="bg-[#0f172a] text-white px-6 py-3 rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-slate-800 transition-all flex items-center gap-2 shadow-lg">
                    <i class="ph-bold ph-printer text-lg"></i> Imprimir Reporte
                </button>
            </div>
        </header>

        <section class="p-10 pb-6 overflow-y-auto">
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-8">
                <div class="finance-card bg-white p-8 rounded-[2.5rem] border border-slate-200 shadow-sm relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-emerald-50 rounded-bl-full -mr-12 -mt-12"></div>
                    <div class="flex items-center gap-4 mb-6 relative z-10">
                        <div class="w-14 h-14 bg-emerald-100 text-emerald-600 rounded-2xl flex items-center justify-center shadow-inner">
                            <i class="ph-bold ph-chart-line-up text-2xl"></i>
                        </div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Ingresos (Mes)</p>
                    </div>
                    <h3 class="text-4xl font-black text-slate-900 tracking-tighter">$<?php echo number_format($recaudado_mes, 0, ',', '.'); ?></h3>
                    <p class="text-[10px] <?php echo $diferencia_porcentaje >= 0 ? 'text-emerald-500' : 'text-red-500'; ?> font-black mt-3 uppercase flex items-center gap-1">
                        <i class="ph-bold <?php echo $diferencia_porcentaje >= 0 ? 'ph-arrow-up' : 'ph-arrow-down'; ?>"></i> 
                        <?php echo number_format(abs($diferencia_porcentaje), 1); ?>% vs anterior
                    </p>
                </div>

                <div class="finance-card bg-white p-8 rounded-[2.5rem] border border-slate-200 shadow-sm relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-red-50 rounded-bl-full -mr-12 -mt-12"></div>
                    <div class="flex items-center gap-4 mb-6 relative z-10">
                        <div class="w-14 h-14 bg-red-100 text-red-500 rounded-2xl flex items-center justify-center">
                            <i class="ph-bold ph-warning-octagon text-2xl"></i>
                        </div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Deuda Global</p>
                    </div>
                    <h3 class="text-4xl font-black text-red-500 tracking-tighter">$<?php echo number_format(abs($data_deuda['total_deuda'] ?? 0), 0, ',', '.'); ?></h3>
                    <p class="text-[10px] text-slate-400 font-black mt-3 uppercase"><?php echo $data_deuda['familias_morosas']; ?> familias pendientes</p>
                </div>

                <div class="finance-card bg-[#0f172a] p-8 rounded-[2.5rem] shadow-2xl relative overflow-hidden text-white">
                    <div class="absolute -bottom-10 -right-10 w-32 h-32 bg-orange-600/10 rounded-full blur-2xl"></div>
                    <div class="flex items-center gap-4 mb-6 relative z-10">
                        <div class="w-14 h-14 bg-orange-500/10 text-orange-500 rounded-2xl flex items-center justify-center border border-orange-500/20">
                            <i class="ph-bold ph-bowl-food text-2xl"></i>
                        </div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Precio Menú</p>
                    </div>
                    <h3 class="text-4xl font-black text-white tracking-tighter">$<?php echo number_format($precio_menu, 0, ',', '.'); ?></h3>
                    <p class="text-[10px] text-orange-500 font-black mt-3 uppercase">Tarifa oficial hoy</p>
                </div>

                <div class="finance-card bg-white p-8 rounded-[2.5rem] border border-slate-200 shadow-sm relative overflow-hidden">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-14 h-14 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center">
                            <i class="ph-bold ph-backpack text-2xl"></i>
                        </div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Precio Vianda</p>
                    </div>
                    <h3 class="text-4xl font-black text-slate-900 tracking-tighter">$<?php echo number_format($precio_vianda, 0, ',', '.'); ?></h3>
                    <p class="text-[10px] text-blue-600 font-black mt-3 uppercase">Costo administrativo</p>
                </div>
            </div>

            <div class="mt-12 bg-white rounded-[3rem] border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-8 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <h3 class="font-black text-slate-800 flex items-center gap-3 uppercase text-sm tracking-widest">
                        <i class="ph-bold ph-clock-counter-clockwise text-orange-600 text-xl"></i> Flujo de Ingresos Recientes
                    </h3>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100">
                                <th class="px-10 py-6">Fecha y Hora</th>
                                <th class="px-10 py-6">Responsable Familiar</th>
                                <th class="px-10 py-6">Monto</th>
                                <th class="px-10 py-6">Vía de Pago</th>
                                <th class="px-10 py-6">Concepto</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <?php foreach($ultimas_trans as $t): ?>
                            <tr class="hover:bg-slate-50/80 transition-all group">
                                <td class="px-10 py-6 text-xs font-bold text-slate-500">
                                    <?php echo formatearFecha($t['fecha']); ?>
                                </td>
                                <td class="px-10 py-6">
                                    <span class="font-black text-slate-800 text-sm italic">Fam. <?php echo htmlspecialchars($t['apellido_responsable']); ?></span>
                                    <p class="text-[10px] text-slate-400 font-bold uppercase"><?php echo htmlspecialchars($t['nombre_responsable']); ?></p>
                                </td>
                                <td class="px-10 py-6">
                                    <div class="flex items-center gap-2">
                                        <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                                        <span class="text-emerald-600 font-black text-lg">$<?php echo number_format($t['monto'], 0, ',', '.'); ?></span>
                                    </div>
                                </td>
                                <td class="px-10 py-6 text-center">
                                    <?php 
                                        $label = "Efectivo"; $color = "text-slate-600 bg-slate-100"; $icon = "ph-money";
                                        if($t['metodo_pago'] == 'sistema_mp') { $label = "MP Sistema"; $color = "text-orange-700 bg-orange-100"; $icon = "ph-globe"; }
                                        if($t['metodo_pago'] == 'caja_mp') { $label = "MP Caja"; $color = "text-blue-700 bg-blue-100"; $icon = "ph-device-mobile"; }
                                    ?>
                                    <span class="<?php echo $color; ?> px-4 py-2 rounded-xl text-[9px] font-black uppercase tracking-widest flex items-center gap-2 w-fit">
                                        <i class="ph-bold <?php echo $icon; ?> text-base"></i> <?php echo $label; ?>
                                    </span>
                                </td>
                                <td class="px-10 py-6 text-[10px] font-bold text-slate-500 uppercase tracking-tighter">
                                    <?php echo htmlspecialchars($t['descripcion']); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if(empty($ultimas_trans)): ?>
                    <div class="p-20 text-center flex flex-col items-center">
                        <i class="ph-bold ph-receipt-x text-6xl text-slate-200 mb-4"></i>
                        <p class="text-slate-400 font-black uppercase tracking-widest text-xs">Sin movimientos registrados este mes</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

</body>
</html>