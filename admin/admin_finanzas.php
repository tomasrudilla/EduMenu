<?php
session_start();
require '../conexion/db.php';

// // 1. Seguridad: Solo admin
// if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
//     header("Location: ../login.php");
//     exit;
// }

// --- NUEVA LÓGICA: Procesar Actualización de Precios ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_tarifas'])) {
    $nuevo_menu = $_POST['nuevo_precio_menu'];
    $nueva_vianda = $_POST['nuevo_precio_vianda'];

    try {
        $pdo->beginTransaction();
        $stmt_upd = $pdo->prepare("UPDATE precios_servicios SET precio = ? WHERE tipo = ?");
        $stmt_upd->execute([$nuevo_menu, 'menu']);
        $stmt_upd->execute([$nueva_vianda, 'vianda']);
        
        // Log de auditoría opcional
        $stmt_log = $pdo->prepare("INSERT INTO logs_auditoria (tipo, accion, detalle) VALUES ('SISTEMA', 'Cambio de Precio', ?)");
        $stmt_log->execute(["Precios actualizados: Menú $$nuevo_menu, Vianda $$nueva_vianda"]);

        $pdo->commit();
        $success_msg = "Tarifas actualizadas correctamente.";
    } catch (Exception $e) {
        $pdo->rollBack();
        $error_msg = "Error: " . $e->getMessage();
    }
}

// 2. Cálculos de Fechas
$mes_actual = date('m');
$anio_actual = date('Y');
$mes_pasado = date('m', strtotime("-1 month"));
$anio_pasado = date('Y', strtotime("-1 month"));

// 3. Obtener Recaudación del Mes Actual
$stmt = $pdo->prepare("SELECT SUM(monto) FROM transacciones WHERE MONTH(fecha) = ? AND YEAR(fecha) = ? AND monto > 0");
$stmt->execute([$mes_actual, $anio_actual]);
$recaudado_mes = $stmt->fetchColumn() ?: 0;

// 4. Obtener Recaudación del Mes Pasado
$stmt = $pdo->prepare("SELECT SUM(monto) FROM transacciones WHERE MONTH(fecha) = ? AND YEAR(fecha) = ? AND monto > 0");
$stmt->execute([$mes_pasado, $anio_pasado]);
$recaudado_pasado = $stmt->fetchColumn() ?: 0;

$diferencia_porcentaje = 0;
if ($recaudado_pasado > 0) {
    $diferencia_porcentaje = (($recaudado_mes - $recaudado_pasado) / $recaudado_pasado) * 100;
}

// 5. Deuda Total y Familias Morosas
$sql_deuda = "SELECT SUM(saldo_familia) as total_deuda, COUNT(*) as familias_morosas 
              FROM (
                SELECT SUM(monto) as saldo_familia 
                FROM transacciones 
                GROUP BY familia_id 
                HAVING saldo_familia < 0
              ) as subquery";
$stmt_deuda = $pdo->query($sql_deuda);
$data_deuda = $stmt_deuda->fetch();

// 6. Obtener Precios Actuales
$stmt_precios = $pdo->query("SELECT tipo, precio FROM precios_servicios");
$precios = $stmt_precios->fetchAll(PDO::FETCH_KEY_PAIR);
$precio_menu = $precios['menu'] ?? 0;
$precio_vianda = $precios['vianda'] ?? 0;

// 7. Últimas Transacciones
$stmt_trans = $pdo->prepare("SELECT t.*, f.apellido_responsable, f.nombre_responsable 
                             FROM transacciones t 
                             JOIN familias f ON t.familia_id = f.id 
                             WHERE t.monto > 0 
                             ORDER BY t.fecha DESC 
                             LIMIT 10");
$stmt_trans->execute();
$ultimas_trans = $stmt_trans->fetchAll();

function formatearFecha($fecha) {
    $d = date('Y-m-d', strtotime($fecha));
    if ($d == date('Y-m-d')) return "Hoy, " . date('H:i', strtotime($fecha)) . " hs";
    return date('d/m, H:i', strtotime($fecha)) . " hs";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>EduMenu | Finanzas Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .finance-card { transition: all 0.3s ease; }
        .finance-card:hover { transform: translateY(-5px); }
    </style>
</head>
<body class="bg-[#f8fafc] text-slate-900 flex h-screen overflow-hidden">

    <?php include '../includes/sidebar_admin.php'; ?>

    <main class="flex-1 flex flex-col overflow-hidden">
        
        <header class="h-24 bg-white border-b border-slate-200 px-10 flex items-center justify-between sticky top-0 z-10 shadow-sm">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 uppercase tracking-tighter italic">Balance Financiero</h1>
                <p class="text-sm text-slate-500 font-medium">Gestión de ingresos y actualización de tarifas.</p>
            </div>
            
            <div class="flex items-center gap-4">
                <button onclick="document.getElementById('modalPrecios').classList.remove('hidden')" class="bg-orange-600 text-white px-6 py-3 rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-orange-700 transition-all flex items-center gap-2 shadow-lg shadow-orange-200">
                    <i class="ph-bold ph-pencil-line text-lg"></i> Ajustar Tarifas
                </button>
                <button onclick="window.print()" class="bg-[#0f172a] text-white px-6 py-3 rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-slate-800 transition-all">
                    <i class="ph-bold ph-printer text-lg"></i>
                </button>
            </div>
        </header>

        <section class="p-10 pb-6 overflow-y-auto">
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-8">
                <div class="finance-card bg-white p-8 rounded-[2.5rem] border border-slate-200 shadow-sm relative overflow-hidden">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-14 h-14 bg-emerald-100 text-emerald-600 rounded-2xl flex items-center justify-center"><i class="ph-bold ph-chart-line-up text-2xl"></i></div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Ingresos</p>
                    </div>
                    <h3 class="text-4xl font-black text-slate-900">$<?php echo number_format($recaudado_mes, 0, ',', '.'); ?></h3>
                    <p class="text-[10px] <?php echo $diferencia_porcentaje >= 0 ? 'text-emerald-500' : 'text-red-500'; ?> font-black mt-3 uppercase">
                        <?php echo number_format($diferencia_porcentaje, 1); ?>% vs anterior
                    </p>
                </div>

                <div class="finance-card bg-white p-8 rounded-[2.5rem] border border-slate-200 shadow-sm relative overflow-hidden">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-14 h-14 bg-red-100 text-red-500 rounded-2xl flex items-center justify-center"><i class="ph-bold ph-warning-octagon text-2xl"></i></div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Deuda Global</p>
                    </div>
                    <h3 class="text-4xl font-black text-red-500">$<?php echo number_format(abs($data_deuda['total_deuda'] ?? 0), 0, ',', '.'); ?></h3>
                    <p class="text-[10px] text-slate-400 font-black mt-3 uppercase"><?php echo $data_deuda['familias_morosas']; ?> familias</p>
                </div>

                <div class="finance-card bg-[#0f172a] p-8 rounded-[2.5rem] shadow-2xl relative overflow-hidden text-white group">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-14 h-14 bg-orange-500/20 text-orange-500 rounded-2xl flex items-center justify-center border border-orange-500/20"><i class="ph-bold ph-bowl-food text-2xl"></i></div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Precio Menú</p>
                    </div>
                    <h3 class="text-4xl font-black text-white tracking-tighter">$<?php echo number_format($precio_menu, 0, ',', '.'); ?></h3>
                    <button onclick="document.getElementById('modalPrecios').classList.remove('hidden')" class="absolute top-6 right-6 opacity-0 group-hover:opacity-100 transition-opacity text-orange-500"><i class="ph-bold ph-pencil text-xl"></i></button>
                </div>

                <div class="finance-card bg-white p-8 rounded-[2.5rem] border border-slate-200 shadow-sm relative overflow-hidden group">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-14 h-14 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center"><i class="ph-bold ph-backpack text-2xl"></i></div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Precio Vianda</p>
                    </div>
                    <h3 class="text-4xl font-black text-slate-900">$<?php echo number_format($precio_vianda, 0, ',', '.'); ?></h3>
                    <button onclick="document.getElementById('modalPrecios').classList.remove('hidden')" class="absolute top-6 right-6 opacity-0 group-hover:opacity-100 transition-opacity text-blue-600"><i class="ph-bold ph-pencil text-xl"></i></button>
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
                            <tr class="text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">
                                <th class="px-10 py-6">Fecha</th>
                                <th class="px-10 py-6">Responsable</th>
                                <th class="px-10 py-6">Monto</th>
                                <th class="px-10 py-6">Vía</th>
                                <th class="px-10 py-6">Concepto</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <?php foreach($ultimas_trans as $t): ?>
                            <tr class="hover:bg-slate-50/80 transition-all">
                                <td class="px-10 py-6 text-xs font-bold text-slate-500"><?php echo formatearFecha($t['fecha']); ?></td>
                                <td class="px-10 py-6"><span class="font-black text-slate-800 text-sm">Fam. <?php echo htmlspecialchars($t['apellido_responsable']); ?></span></td>
                                <td class="px-10 py-6"><span class="text-emerald-600 font-black text-lg">$<?php echo number_format($t['monto'], 0, ',', '.'); ?></span></td>
                                <td class="px-10 py-6">
                                    <span class="bg-slate-100 px-4 py-2 rounded-xl text-[9px] font-black uppercase tracking-widest"><?php echo str_replace('_', ' ', $t['metodo_pago']); ?></span>
                                </td>
                                <td class="px-10 py-6 text-[10px] font-bold text-slate-500 uppercase"><?php echo htmlspecialchars($t['descripcion']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>

    <div id="modalPrecios" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-slate-900/60 backdrop-blur-sm">
        <div class="bg-white w-full max-w-md rounded-[2.5rem] shadow-2xl overflow-hidden">
            <div class="p-8 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                <h2 class="text-xl font-black text-slate-900 uppercase tracking-tighter">Ajustar Tarifas</h2>
                <button onclick="document.getElementById('modalPrecios').classList.add('hidden')" class="text-slate-400 hover:text-red-500"><i class="ph ph-x-circle text-3xl"></i></button>
            </div>
            <form method="POST" class="p-8 space-y-6">
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block ml-1">Precio Menú Diario ($)</label>
                    <input type="number" name="nuevo_precio_menu" value="<?php echo $precio_menu; ?>" step="0.01" required class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-lg focus:ring-2 focus:ring-orange-500 outline-none">
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block ml-1">Precio Vianda Diaria ($)</label>
                    <input type="number" name="nuevo_precio_vianda" value="<?php echo $precio_vianda; ?>" step="0.01" required class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-lg focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <button type="submit" name="actualizar_tarifas" class="w-full bg-[#0f172a] text-white py-5 rounded-2xl font-black uppercase hover:bg-slate-800 transition-all shadow-xl">Guardar Nuevos Precios</button>
            </form>
        </div>
    </div>

    <script>
        <?php if(isset($success_msg)): ?>
            Swal.fire({ title: '¡Hecho!', text: '<?= $success_msg ?>', icon: 'success', confirmButtonColor: '#ea580c' });
        <?php endif; ?>
        <?php if(isset($error_msg)): ?>
            Swal.fire({ title: 'Error', text: '<?= $error_msg ?>', icon: 'error', confirmButtonColor: '#ef4444' });
        <?php endif; ?>
    </script>
</body>
</html>