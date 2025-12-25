<?php
session_start();
require '../conexion/db.php';

// 1. Identificación de la Familia
$familia_id = $_SESSION['familia_id'] ?? 1; 

// 2. OBTENER HIJOS VINCULADOS (Corregido: nombre_completo -> nombre, apellido | activo -> status)
$stmt_h = $pdo->prepare("SELECT nombre, apellido FROM alumnos WHERE familia_id = ? AND status = 'ACTIVE'");
$stmt_h->execute([$familia_id]);
$alumnos_data = $stmt_h->fetchAll(PDO::FETCH_ASSOC);

$hijos = [];
foreach ($alumnos_data as $alu) {
    $hijos[] = $alu['nombre'] . ' ' . $alu['apellido'];
}
$cantidad_hijos = count($hijos);

// 3. Balance Total Familiar (Suma de transacciones)
$stmt_saldo = $pdo->prepare("SELECT SUM(monto) FROM transacciones WHERE familia_id = ?");
$stmt_saldo->execute([$familia_id]);
$balance_real = $stmt_saldo->fetchColumn() ?: 0;

$monto_deuda = ($balance_real < 0) ? abs($balance_real) : 0;
$esta_saldado = ($balance_real >= 0);

// 4. Últimos Movimientos
$stmt_mov = $pdo->prepare("SELECT * FROM transacciones WHERE familia_id = ? ORDER BY fecha DESC LIMIT 10");
$stmt_mov->execute([$familia_id]);
$movimientos = $stmt_mov->fetchAll();

function formatearFecha($fecha) { return date('d/m/Y', strtotime($fecha)); }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduMenu | Estado de Cuenta</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: #f8fafc; 
            -webkit-font-smoothing: antialiased;
        }
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .status-card { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); }
        .status-card-ok { background: linear-gradient(135deg, #312e81 0%, #4338ca 100%); }
    </style>
</head>
<body class="flex flex-col md:flex-row h-screen overflow-hidden text-slate-700">

    <?php include '../includes/sidebar_familia.php'; ?>

    <main class="flex-1 flex flex-col overflow-hidden">
        
        <header class="min-h-[80px] bg-white border-b border-slate-200 px-4 md:px-10 py-4 flex flex-col sm:flex-row items-start sm:items-center justify-between sticky top-0 z-10 gap-4">
            <div>
                <?php if ($cantidad_hijos > 1): ?>
                    <h1 class="text-lg md:text-xl font-extrabold text-slate-900 uppercase tracking-tight">Estado de Cuenta Unificado</h1>
                    <p class="text-[9px] md:text-[10px] text-slate-400 font-bold uppercase tracking-widest leading-none mt-1">
                        Incluye a: <?= implode(', ', $hijos) ?>
                    </p>
                <?php else: ?>
                    <h1 class="text-lg md:text-xl font-extrabold text-slate-900 uppercase tracking-tight">Estado de Cuenta</h1>
                    <p class="text-[9px] md:text-[10px] text-slate-400 font-bold uppercase tracking-widest leading-none mt-1">Resumen de consumos y pagos</p>
                <?php endif; ?>
            </div>
            
            <div class="flex items-center gap-4">
                <?php if($esta_saldado): ?>
                    <div class="flex items-center gap-2 bg-emerald-50 text-emerald-600 border border-emerald-100 px-3 md:px-4 py-1.5 rounded-xl shadow-sm">
                        <i class="ph-bold ph-check-circle text-lg"></i>
                        <span class="text-[9px] md:text-[10px] font-black uppercase tracking-tight whitespace-nowrap">Sin Pendientes</span>
                    </div>
                <?php else: ?>
                    <div class="flex items-center gap-2 bg-red-50 text-red-600 border border-red-100 px-3 md:px-4 py-1.5 rounded-xl shadow-sm">
                        <i class="ph-bold ph-warning-circle text-lg"></i>
                        <span class="text-[9px] md:text-[10px] font-black uppercase tracking-tight whitespace-nowrap">Saldo Deudor</span>
                    </div>
                <?php endif; ?>
            </div>
        </header>

        <div class="flex-1 p-4 md:p-8 overflow-y-auto bg-slate-50/40">
            
            <div class="<?= $esta_saldado ? 'status-card-ok' : 'status-card' ?> rounded-[2rem] md:rounded-[2.5rem] p-6 md:p-10 mb-8 md:mb-10 text-white relative overflow-hidden shadow-2xl transition-all">
                <div class="absolute top-0 right-0 w-64 md:w-80 h-64 md:h-80 bg-white/5 rounded-full -mr-32 md:-mr-40 -mt-32 md:-mt-40 blur-3xl"></div>
                
                <div class="relative z-10 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-8">
                    <div class="w-full">
                        <p class="text-white/50 font-bold uppercase tracking-[0.25em] text-[9px] md:text-[10px] mb-2 md:mb-3">
                            <?= ($cantidad_hijos > 1) ? 'Saldo Actual de la Familia' : ($esta_saldado ? 'Situación de cuenta' : 'Total adeudado') ?>
                        </p>
                        <h2 class="text-4xl sm:text-5xl md:text-6xl font-black tracking-tighter break-words">
                            <?= $esta_saldado ? 'Sin Pendientes' : '$' . number_format($monto_deuda, 0, ',', '.') ?>
                        </h2>
                        <div class="flex items-center gap-3 mt-6 md:mt-8 text-white/40 border-t border-white/10 pt-4 md:pt-6">
                            <i class="ph ph-info text-lg md:text-xl"></i>
                            <p class="text-[9px] md:text-[10px] font-semibold uppercase tracking-widest leading-relaxed">
                                <?php 
                                if ($cantidad_hijos > 1) {
                                    echo "Balance total de todos los hijos vinculados";
                                } else {
                                    echo $esta_saldado ? "Usted no posee cargos pendientes de pago" : "Por favor, regularice su deuda con la institución";
                                }
                                ?>
                            </p>
                        </div>
                    </div>

                    <?php if(!$esta_saldado): ?>
                        <div class="flex flex-col gap-3 w-full lg:w-auto">
                            <button class="bg-indigo-600 hover:bg-indigo-500 text-white px-8 md:px-12 py-4 md:py-5 rounded-2xl font-bold text-xs md:text-sm uppercase tracking-widest transition-all shadow-xl active:scale-95 flex items-center justify-center gap-3 w-full">
                                <i class="ph-bold ph-credit-card text-xl"></i> Pagar ahora
                            </button>
                            <p class="text-[8px] md:text-[9px] text-center text-white/30 font-bold uppercase tracking-widest">Actualizado al <?= date('d/m/Y') ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="flex items-center justify-between mb-6 px-2">
                <h3 class="text-sm md:text-base font-extrabold text-slate-900 uppercase tracking-tight">Movimientos Recientes</h3>
            </div>

            <div class="bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-sm">
                <?php if(empty($movimientos)): ?>
                    <div class="p-12 md:p-20 text-center flex flex-col items-center">
                        <i class="ph ph-receipt-x text-5xl text-slate-200 mb-4"></i>
                        <p class="text-slate-400 font-bold text-xs uppercase tracking-widest">Aún no hay actividad registrada</p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[600px]">
                            <thead>
                                <tr class="bg-slate-50/50 border-b border-slate-100">
                                    <th class="px-6 md:px-8 py-5 text-[9px] md:text-[10px] font-black text-slate-400 uppercase tracking-widest"><?= ($cantidad_hijos > 1) ? 'Detalle (Hijo/Pago)' : 'Detalle' ?></th>
                                    <th class="px-6 md:px-8 py-5 text-[9px] md:text-[10px] font-black text-slate-400 uppercase tracking-widest">Fecha</th>
                                    <th class="px-6 md:px-8 py-5 text-[9px] md:text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Monto</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <?php foreach($movimientos as $m): 
                                    $es_pago = $m['monto'] > 0;
                                ?>
                                <tr class="hover:bg-slate-50/30 transition-colors">
                                    <td class="px-6 md:px-8 py-4 md:py-5">
                                        <div class="flex items-center gap-3 md:gap-4">
                                            <div class="w-9 h-9 md:w-10 md:h-10 rounded-xl flex items-center justify-center text-lg md:text-xl <?= $es_pago ? 'bg-indigo-50 text-indigo-600' : 'bg-slate-100 text-slate-400' ?>">
                                                <i class="<?= $es_pago ? 'ph ph-receipt' : 'ph ph-bowl-food' ?>"></i>
                                            </div>
                                            <div>
                                                <span class="font-bold text-slate-800 text-xs md:text-sm block leading-tight"><?= htmlspecialchars($m['descripcion']) ?></span>
                                                <p class="text-[8px] md:text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1">
                                                    Vía: <?= str_replace('_', ' ', strtoupper($m['metodo_pago'])) ?>
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 md:px-8 py-4 md:py-5 text-[10px] md:text-xs font-bold text-slate-500 whitespace-nowrap">
                                        <?= formatearFecha($m['fecha']) ?>
                                    </td>
                                    <td class="px-6 md:px-8 py-4 md:py-5 text-right">
                                        <span class="text-xs md:text-sm font-black <?= $es_pago ? 'text-emerald-600' : 'text-slate-900' ?> whitespace-nowrap">
                                            <?= $es_pago ? '+' : '-' ?>$<?= number_format(abs($m['monto']), 0, ',', '.') ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <footer class="min-h-[56px] bg-white border-t border-slate-200 px-4 md:px-10 py-3 flex flex-col sm:flex-row items-center justify-between gap-2">
            <p class="text-[8px] md:text-[10px] text-slate-400 font-bold uppercase tracking-widest flex items-center gap-2 text-center sm:text-left">
                <i class="ph ph-info-bold text-indigo-500 text-base"></i> ¿Dudas? Por favor comuníquese con administración
            </p>
            <a href="#" class="text-[8px] md:text-[10px] font-black text-indigo-600 uppercase tracking-widest hover:underline decoration-2">Exportar Movimientos (PDF)</a>
        </footer>
    </main>

</body>
</html>