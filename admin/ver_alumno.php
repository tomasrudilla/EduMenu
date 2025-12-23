<?php
session_start();
require '../conexion/db.php';

// 1. Obtener ID y validar
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { header("Location: admin_alumnos.php"); exit; }

// --- 2. PROCESAR ACCIONES (POST) ---

// A. Actualizar Datos del Alumno / Cambiar Familia
if (isset($_POST['update_alumno'])) {
    $nombre = $_POST['nombre_completo'];
    $curso = $_POST['curso'];
    $fam_id = !empty($_POST['familia_id']) ? $_POST['familia_id'] : null;
    $alergias = $_POST['alergias'];

    $stmt = $pdo->prepare("UPDATE alumnos SET nombre_completo = ?, curso = ?, familia_id = ?, alergias = ? WHERE id = ?");
    $stmt->execute([$nombre, $curso, $fam_id, $alergias, $id]);
    $success_msg = "Datos actualizados correctamente.";
}

// B. Registrar Pago de Mes (Desde Modal)
if (isset($_POST['registrar_pago_mes'])) {
    $mes = $_POST['mes_num'];
    $metodo = $_POST['metodo_pago'];
    $anio = date('Y');
    $monto_mes = 15000; // Valor de la cuota simluada

    try {
        $pdo->beginTransaction();
        $stmt1 = $pdo->prepare("INSERT INTO pagos_mensuales (alumno_id, mes, anio, estado) VALUES (?, ?, ?, 'pagado') 
                                ON DUPLICATE KEY UPDATE estado = 'pagado'");
        $stmt1->execute([$id, $mes, $anio]);

        $stmt_f = $pdo->prepare("SELECT familia_id FROM alumnos WHERE id = ?");
        $stmt_f->execute([$id]);
        $fam_id = $stmt_f->fetchColumn();

        if ($fam_id) {
            $desc = "Pago Cuota Mes " . $mes . " (" . date('Y') . ")";
            $stmt2 = $pdo->prepare("INSERT INTO transacciones (familia_id, monto, descripcion, metodo_pago) VALUES (?, ?, ?, ?)");
            $stmt2->execute([$fam_id, $monto_mes, $desc, $metodo]);
        }
        $pdo->commit();
        $success_msg = "¡Pago registrado con éxito!";
    } catch (Exception $e) {
        $pdo->rollBack();
        $error_msg = "Error: " . $e->getMessage();
    }
}

// C. Toggle para volver a Pendiente
if (isset($_POST['volver_pendiente'])) {
    $mes = $_POST['mes_num'];
    $anio = date('Y');
    $stmt = $pdo->prepare("UPDATE pagos_mensuales SET estado = 'pendiente' WHERE alumno_id = ? AND mes = ? AND anio = ?");
    $stmt->execute([$id, $mes, $anio]);
    $success_msg = "Estado de cuota actualizado.";
}

// --- 3. OBTENER DATOS ACTUALIZADOS ---

// Datos básicos + Familia
$stmt = $pdo->prepare("SELECT a.*, f.nombre_responsable, f.apellido_responsable, f.id as fam_id 
                       FROM alumnos a 
                       LEFT JOIN familias f ON a.familia_id = f.id 
                       WHERE a.id = ?");
$stmt->execute([$id]);
$al = $stmt->fetch();

// Navegación Semanal
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
$monday = new DateTime();
$monday->setISODate((int)date('Y'), (int)date('W'));
if ($offset !== 0) { $monday->modify("$offset week"); }
$start_week = $monday->format('Y-m-d');
$end_week = (clone $monday)->modify('+4 days')->format('Y-m-d');

// --- CAMBIO AQUÍ: Traemos el plato_principal con un LEFT JOIN ---
$stmt_sel = $pdo->prepare("SELECT s.fecha, s.tipo, m.plato_principal 
                           FROM selecciones s 
                           LEFT JOIN menus m ON s.fecha = m.fecha 
                           WHERE s.alumno_id = ? AND s.fecha BETWEEN ? AND ?");
$stmt_sel->execute([$id, $start_week, $end_week]);
$selecciones = $stmt_sel->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);

// Grilla de Pagos Mensuales
$stmt_pm = $pdo->prepare("SELECT mes, estado FROM pagos_mensuales WHERE alumno_id = ? AND anio = ?");
$stmt_pm->execute([$id, date('Y')]);
$pagos_grid = $stmt_pm->fetchAll(PDO::FETCH_KEY_PAIR);

// Estadísticas Financieras
$saldo = 0;
$pagos_hist = [];
$stats_metodos = ['sistema_mp' => 0, 'caja_mp' => 0, 'caja_efectivo' => 0];
if ($al['familia_id']) {
    $saldo = $pdo->query("SELECT SUM(monto) FROM transacciones WHERE familia_id = ".$al['familia_id'])->fetchColumn() ?: 0;
    $pagos_hist = $pdo->query("SELECT * FROM transacciones WHERE familia_id = ".$al['familia_id']." AND monto > 0 ORDER BY fecha DESC LIMIT 5")->fetchAll();
    $metodos_db = $pdo->query("SELECT metodo_pago, COUNT(*) as cant FROM transacciones WHERE familia_id = ".$al['familia_id']." AND monto > 0 GROUP BY metodo_pago")->fetchAll(PDO::FETCH_KEY_PAIR);
    foreach($metodos_db as $k => $v) { if(isset($stats_metodos[$k])) $stats_metodos[$k] = $v; }
}
$total_metodos = array_sum($stats_metodos) ?: 1;

$todas_familias = $pdo->query("SELECT id, nombre_responsable, apellido_responsable FROM familias ORDER BY apellido_responsable ASC")->fetchAll();
$meses_nombres = [1=>'Ene', 2=>'Feb', 3=>'Mar', 4=>'Abr', 5=>'May', 6=>'Jun', 7=>'Jul', 8=>'Ago', 9=>'Sep', 10=>'Oct', 11=>'Nov', 12=>'Dic'];
$dias_nombres = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>EduMenu | Dashboard Integral</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-thumb { background: #ea580c; border-radius: 10px; }
        .mes-btn { transition: all 0.2s; border-radius: 16px; border: 1px solid #e2e8f0; padding: 12px; text-align: center; }
        .mes-pagado { background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%); border-color: #10b981; color: #065f46; font-weight: 800; }
        .mes-pendiente { background-color: #fff; color: #94a3b8; border-style: dashed; }
    </style>
</head>
<body class="flex h-screen overflow-hidden">

    <?php include '../includes/sidebar_admin.php'; ?>

    <main class="flex-1 flex flex-col overflow-hidden">
        <header class="h-20 bg-white border-b border-slate-200 px-10 flex items-center justify-between sticky top-0 z-10 shadow-sm">
            <div class="flex items-center gap-4">
                <a href="admin_alumnos.php" class="p-2 hover:bg-slate-100 rounded-full text-slate-400 hover:text-orange-600 transition-all"><i class="ph-bold ph-arrow-left text-2xl"></i></a>
                <div>
                    <h1 class="text-xl font-extrabold text-slate-900 uppercase tracking-tighter">Expediente Central</h1>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none">Gestión de Alumno y Cobros</p>
                </div>
            </div>
            <button onclick="document.getElementById('modalEdit').classList.remove('hidden')" class="bg-[#0f172a] text-white px-6 py-2.5 rounded-2xl text-xs font-bold flex items-center gap-2 hover:bg-slate-800 shadow-lg">
                <i class="ph ph-user-circle-gear text-lg text-orange-500"></i> Modificar Datos
            </button>
        </header>

        <section class="flex-1 p-8 overflow-y-auto space-y-8">
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="bg-white p-8 rounded-[3rem] border border-slate-200 shadow-sm col-span-2 flex items-center justify-between relative overflow-hidden group">
                    <div class="flex items-center gap-8 relative z-10">
                        <div class="w-32 h-32 bg-gradient-to-br from-orange-400 to-orange-600 text-white rounded-[2.5rem] flex items-center justify-center text-5xl font-black shadow-xl shadow-orange-200">
                            <?= strtoupper(substr($al['nombre_completo'], 0, 1)) ?>
                        </div>
                        <div>
                            <span class="bg-orange-100 text-orange-600 px-4 py-1.5 rounded-xl text-[10px] font-black uppercase mb-3 inline-block tracking-widest"><?= $al['curso'] ?></span>
                            <h2 class="text-4xl font-extrabold text-slate-900 tracking-tight"><?= htmlspecialchars($al['nombre_completo']) ?></h2>
                            <p class="text-slate-500 font-bold mt-1">Responsable: <span class="text-slate-800"><?= $al['nombre_responsable'].' '.$al['apellido_responsable'] ?></span></p>
                            <p class="text-[10px] font-black text-red-500 bg-red-50 px-3 py-1.5 rounded-lg border border-red-100 uppercase mt-4 w-fit italic">
                                <i class="ph-bold ph-warning-circle"></i> Alergias: <?= $al['alergias'] ?: 'Ninguna' ?>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-[#0f172a] p-8 rounded-[3rem] shadow-2xl text-white flex flex-col justify-center border border-slate-800 relative overflow-hidden">
                    <div class="absolute -bottom-10 -left-10 w-40 h-40 bg-orange-600/10 rounded-full blur-3xl"></div>
                    <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4">Saldo en Cuenta</p>
                    <h3 class="text-6xl font-black <?= $saldo < 0 ? 'text-red-400' : 'text-emerald-400' ?> tracking-tighter">
                        $<?= number_format(abs($saldo), 0, ',', '.') ?>
                    </h3>
                    <p class="text-sm mt-4 text-slate-400 font-medium">
                        <?= $saldo < 0 ? 'Deuda pendiente' : 'Saldo a favor' ?>
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 space-y-8">
                    <div class="bg-white p-8 rounded-[3rem] border border-slate-200 shadow-sm">
                        <div class="flex items-center justify-between mb-8">
                            <h3 class="font-extrabold text-slate-800 text-xl tracking-tight flex items-center gap-3"><i class="ph-bold ph-calendar-star text-orange-600"></i> Registro de Asistencia</h3>
                            <div class="flex bg-slate-100 p-1.5 rounded-2xl border border-slate-200 text-[10px] font-black uppercase">
                                <a href="?id=<?= $id ?>&offset=<?= $offset-1 ?>" class="p-2 hover:bg-white rounded-xl shadow-sm"><i class="ph-bold ph-caret-left"></i></a>
                                <span class="px-6 py-2.5 text-slate-700"><?= $monday->format('d/m') ?> — <?= (clone $monday)->modify('+4 days')->format('d/m') ?></span>
                                <a href="?id=<?= $id ?>&offset=<?= $offset+1 ?>" class="p-2 hover:bg-white rounded-xl shadow-sm"><i class="ph-bold ph-caret-right"></i></a>
                            </div>
                        </div>
                        <div class="grid grid-cols-5 gap-5">
                            <?php for ($i = 0; $i < 5; $i++): 
                                $date_f = (clone $monday)->modify("+$i days")->format('Y-m-d');
                                $sel = $selecciones[$date_f] ?? null;
                            ?>
                            <div class="bg-slate-50/50 p-6 rounded-[2.5rem] border border-slate-100 flex flex-col items-center text-center hover:bg-white transition-all min-h-[160px] justify-center">
                                <span class="text-[10px] font-black text-slate-400 uppercase mb-4 tracking-widest"><?= $dias_nombres[$i] ?></span>
                                <?php if($sel): 
                                    $icon = ($sel['tipo'] == 'vianda') ? 'ph-fill ph-backpack' : (($sel['tipo'] == 'ausente') ? 'ph-fill ph-x-circle' : 'ph-fill ph-bowl-food');
                                    $color = ($sel['tipo'] == 'vianda') ? 'text-blue-600' : (($sel['tipo'] == 'ausente') ? 'text-red-600' : 'text-orange-600');
                                    $bg = ($sel['tipo'] == 'vianda') ? 'bg-blue-100' : (($sel['tipo'] == 'ausente') ? 'bg-red-100' : 'bg-orange-100');
                                    
                                    // Mostramos el nombre del plato si es tipo 'menu', sino el tipo normal
                                    $texto_mostrar = ($sel['tipo'] == 'menu' && !empty($sel['plato_principal'])) ? $sel['plato_principal'] : $sel['tipo'];
                                ?>
                                    <div class="w-14 h-14 <?= $bg ?> <?= $color ?> rounded-[1.5rem] flex items-center justify-center mb-3 shadow-sm"><i class="<?= $icon ?> text-3xl"></i></div>
                                    <span class="text-[10px] font-extrabold uppercase tracking-tight leading-tight <?= $color ?> max-w-[80px]"><?= htmlspecialchars($texto_mostrar) ?></span>
                                <?php else: ?>
                                    <div class="w-14 h-14 bg-white text-slate-200 rounded-[1.5rem] flex items-center justify-center mb-3 border-2 border-dashed border-slate-100"><i class="ph-bold ph-minus text-2xl"></i></div>
                                    <span class="text-[10px] font-bold text-slate-300 italic uppercase">Vacío</span>
                                <?php endif; ?>
                            </div>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div class="bg-white p-8 rounded-[3rem] border border-slate-200 shadow-sm">
                        <div class="flex items-center justify-between mb-8">
                            <h3 class="font-extrabold text-slate-800 text-xl tracking-tight flex items-center gap-3"><i class="ph-bold ph-calendar-check text-orange-600"></i> Control de Cuotas (<?= date('Y') ?>)</h3>
                        </div>
                        <div class="grid grid-cols-4 md:grid-cols-6 xl:grid-cols-12 gap-3">
                            <?php foreach($meses_nombres as $num => $nombre): 
                                $estado = $pagos_grid[$num] ?? 'pendiente';
                                $is_paid = ($estado === 'pagado');
                            ?>
                                <?php if($is_paid): ?>
                                    <form method="POST">
                                        <input type="hidden" name="mes_num" value="<?= $num ?>">
                                        <button type="submit" name="volver_pendiente" class="mes-btn mes-pagado hover:scale-105 transition-all w-full">
                                            <span class="block text-[9px] opacity-70 font-black uppercase mb-1"><?= $nombre ?></span>
                                            <i class="ph-bold ph-check-circle text-xl"></i>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <button onclick="abrirModalPago(<?= $num ?>, '<?= $nombre ?>')" class="mes-btn mes-pendiente hover:scale-105 transition-all w-full">
                                        <span class="block text-[9px] opacity-70 font-black uppercase mb-1"><?= $nombre ?></span>
                                        <i class="ph-bold ph-circle-dashed text-xl"></i>
                                    </button>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="space-y-8">
                    <div class="bg-white p-7 rounded-[2.5rem] border border-slate-200 shadow-sm">
                        <h3 class="font-black text-slate-800 text-xs uppercase tracking-widest mb-6 flex items-center gap-2"><i class="ph-bold ph-chart-pie-slice text-orange-600 text-lg"></i> Mix de Pagos</h3>
                        <div class="space-y-6">
                            <?php 
                            $metodos_labels = ['sistema_mp' => 'MP Web', 'caja_mp' => 'MP Caja', 'caja_efectivo' => 'Efectivo'];
                            foreach($stats_metodos as $k => $v): 
                                $pct = round(($v/$total_metodos)*100);
                                $color_bar = ($k == 'sistema_mp') ? 'bg-orange-500' : (($k == 'caja_mp') ? 'bg-blue-500' : 'bg-slate-700');
                            ?>
                            <div>
                                <div class="flex justify-between text-[10px] font-black uppercase mb-2"><span><?= $metodos_labels[$k] ?></span><span><?= $pct ?>%</span></div>
                                <div class="h-2.5 w-full bg-slate-100 rounded-full overflow-hidden shadow-inner"><div class="h-full <?= $color_bar ?> rounded-full" style="width: <?= $pct ?>%"></div></div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="bg-white rounded-[2.5rem] border border-slate-200 shadow-sm overflow-hidden border-t-4 border-t-orange-500">
                        <div class="p-6 border-b border-slate-50 flex justify-between items-center"><h3 class="font-black text-slate-800 text-xs uppercase tracking-widest">Últimos Ingresos</h3><i class="ph-bold ph-receipt text-slate-300 text-lg"></i></div>
                        <div class="divide-y divide-slate-50">
                            <?php foreach($pagos_hist as $p): ?>
                            <div class="p-5 flex items-center justify-between hover:bg-slate-50 transition-colors">
                                <div><p class="text-sm font-black text-slate-800">$<?= number_format($p['monto'], 0, ',', '.') ?></p><p class="text-[9px] font-bold text-slate-400 uppercase"><?= date('d M, Y', strtotime($p['fecha'])) ?></p></div>
                                <span class="bg-emerald-50 text-emerald-600 px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-tighter border border-emerald-100">OK</span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <div id="modalPago" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-slate-900/60 backdrop-blur-sm">
        <div class="bg-white w-full max-w-md rounded-[2.5rem] shadow-2xl overflow-hidden">
            <div class="p-8 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                <div><h2 class="text-xl font-black text-slate-900 uppercase tracking-tighter">Registrar Pago</h2><p id="mesSeleccionado" class="text-xs font-bold text-orange-600 uppercase tracking-widest"></p></div>
                <button onclick="cerrarModalPago()" class="text-slate-400 hover:text-red-500"><i class="ph ph-x-circle text-3xl"></i></button>
            </div>
            <form method="POST" class="p-8 space-y-6">
                <input type="hidden" name="mes_num" id="inputMesNum">
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4 block">Seleccione el método:</label>
                    <div class="space-y-3">
                        <?php foreach(['caja_efectivo'=>'Efectivo en Caja', 'caja_mp'=>'MercadoPago (Caja)', 'sistema_mp'=>'MercadoPago (Web)'] as $val => $txt): ?>
                        <label class="flex items-center gap-4 p-4 rounded-2xl border border-slate-100 bg-slate-50 cursor-pointer hover:border-orange-500 transition-all">
                            <input type="radio" name="metodo_pago" value="<?= $val ?>" <?= $val == 'caja_efectivo' ? 'checked' : '' ?> class="accent-orange-600 w-5 h-5">
                            <span class="text-sm font-bold text-slate-700"><?= $txt ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <button type="submit" name="registrar_pago_mes" class="w-full bg-orange-600 text-white py-4 rounded-2xl font-black uppercase hover:bg-orange-700 shadow-lg">Confirmar Cobro</button>
            </form>
        </div>
    </div>

    <div id="modalEdit" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-slate-900/60 backdrop-blur-sm">
        <div class="bg-white w-full max-w-2xl rounded-[3rem] shadow-2xl overflow-hidden border border-slate-200">
            <div class="p-8 bg-slate-50 border-b border-slate-200 flex justify-between items-center">
                <h2 class="text-xl font-extrabold text-slate-900 uppercase">Modificar Ficha</h2>
                <button onclick="document.getElementById('modalEdit').classList.add('hidden')" class="text-slate-400 hover:text-red-500"><i class="ph ph-x-circle text-3xl"></i></button>
            </div>
            <form method="POST" class="p-10 grid grid-cols-2 gap-6">
                <div class="col-span-2"><label class="text-[10px] font-black text-slate-400 uppercase mb-2 block tracking-widest">Nombre Completo</label><input type="text" name="nombre_completo" value="<?= htmlspecialchars($al['nombre_completo']) ?>" class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl outline-none focus:ring-2 focus:ring-orange-500 font-bold"></div>
                <div><label class="text-[10px] font-black text-slate-400 uppercase mb-2 block">Curso</label><input type="text" name="curso" value="<?= htmlspecialchars($al['curso']) ?>" class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl outline-none"></div>
                <div><label class="text-[10px] font-black text-slate-400 uppercase mb-2 block">Familia</label><select name="familia_id" class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl outline-none focus:ring-2 focus:ring-orange-500"><option value="">-- Sin familia --</option><?php foreach($todas_familias as $f): ?><option value="<?= $f['id'] ?>" <?= ($f['id'] == $al['familia_id']) ? 'selected' : '' ?>><?= htmlspecialchars($f['apellido_responsable'] . ", " . $f['nombre_responsable']) ?></option><?php endforeach; ?></select></div>
                <div class="col-span-2"><label class="text-[10px] font-black text-slate-400 uppercase mb-2 block">Alergias</label><textarea name="alergias" class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl h-24 outline-none"><?= htmlspecialchars($al['alergias']) ?></textarea></div>
                <div class="col-span-2 pt-4"><button type="submit" name="update_alumno" class="w-full bg-orange-600 text-white py-5 rounded-2xl font-black uppercase hover:bg-orange-700 shadow-lg">Guardar Cambios</button></div>
            </form>
        </div>
    </div>

    <script>
        function abrirModalPago(num, nombre) { document.getElementById('inputMesNum').value = num; document.getElementById('mesSeleccionado').innerText = "CUOTA DE " + nombre; document.getElementById('modalPago').classList.remove('hidden'); }
        function cerrarModalPago() { document.getElementById('modalPago').classList.add('hidden'); }
        <?php if(isset($success_msg)): ?> Swal.fire({ title: '¡Hecho!', text: '<?= $success_msg ?>', icon: 'success', confirmButtonColor: '#ea580c' }); <?php endif; ?>
    </script>
</body>
</html>