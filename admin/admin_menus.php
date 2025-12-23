<?php
session_start();
require '../conexion/db.php';

// 2. Lógica de Navegación de Semanas
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
$monday = new DateTime();
$monday->setISODate((int)date('Y'), (int)date('W')); 
if ($offset !== 0) { $monday->modify("$offset week"); }

$success = false;
$price_updated = false;

// 3. PROCESAR GUARDADO DE MENÚS (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_menus'])) {
    try {
        $pdo->beginTransaction();
        foreach ($_POST['menu'] as $fecha => $datos) {
            $stmt = $pdo->prepare("INSERT INTO menus (fecha, plato_principal, plato_alternativo, opcion_veggie, postre) 
                                   VALUES (?, ?, ?, ?, ?) 
                                   ON DUPLICATE KEY UPDATE 
                                   plato_principal = VALUES(plato_principal), 
                                   plato_alternativo = VALUES(plato_alternativo),
                                   opcion_veggie = VALUES(opcion_veggie),
                                   postre = VALUES(postre)");
            $stmt->execute([$fecha, $datos['principal'], $datos['alternativo'], $datos['veggie'], $datos['postre']]);
        }
        $pdo->commit();
        $success = true;
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Error al guardar: " . $e->getMessage();
    }
}

// 4. PROCESAR CAMBIO DE PRECIOS (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_price'])) {
    $tipo = $_POST['tipo'];
    $nuevo_precio = $_POST['nuevo_precio'];
    
    $stmt = $pdo->prepare("UPDATE precios_servicios SET precio = ? WHERE tipo = ?");
    if($stmt->execute([$nuevo_precio, $tipo])) {
        $price_updated = true;
    }
}

// 5. Obtener Menús de la Semana
$start_date = $monday->format('Y-m-d');
$end_date = (clone $monday)->modify('+4 days')->format('Y-m-d');
$stmt = $pdo->prepare("SELECT fecha, plato_principal, plato_alternativo, opcion_veggie, postre 
                       FROM menus 
                       WHERE fecha BETWEEN ? AND ?");
$stmt->execute([$start_date, $end_date]);
$menus_db = $stmt->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);

// 6. Obtener Precios actuales
$precios_ref = $pdo->query("SELECT tipo, precio FROM precios_servicios")->fetchAll(PDO::FETCH_KEY_PAIR);

$dias_nombres = ['LUNES', 'MARTES', 'MIÉRCOLES', 'JUEVES', 'VIERNES'];
$hoy_fmt = date('Y-m-d');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduMenu | Planificador de Menús</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-thumb { background: #ea580c; border-radius: 10px; }
        .menu-input { width: 100%; padding: 10px 14px; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; font-size: 0.85rem; outline: none; transition: all 0.3s ease; }
        .menu-input:focus { border-color: #ea580c; background-color: #fff; box-shadow: 0 0 0 4px rgba(234, 88, 12, 0.08); transform: translateY(-1px); }
        .day-card { transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); border-radius: 2.5rem; }
        .day-card:hover { transform: translateY(-8px); box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05); }
        .label-caps { font-size: 10px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px; display: flex; align-items: center; gap: 6px; }
    </style>
</head>
<body class="flex h-screen overflow-hidden">

    <?php include '../includes/sidebar_admin.php'; ?>

    <main class="flex-1 flex flex-col overflow-hidden">
        
        <header class="h-24 bg-white border-b border-slate-200 px-10 flex items-center justify-between sticky top-0 z-10">
            <div>
                <div class="flex items-center gap-3">
                    <div class="bg-orange-100 text-orange-600 p-2 rounded-xl shadow-sm">
                        <i class="ph-bold ph-calendar-plus text-xl"></i>
                    </div>
                    <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight italic">Planificador de Platos</h1>
                </div>
                <div class="flex gap-4 mt-1 ml-11">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Precios Hoy:</span>
                    <button type="button" onclick="openPriceModal('menu', <?= $precios_ref['menu'] ?? 0 ?>)" class="text-[10px] font-black text-emerald-600 uppercase tracking-widest hover:bg-emerald-50 px-2 py-0.5 rounded-lg transition-all">
                        Menú $<?= number_format($precios_ref['menu'] ?? 0, 0) ?> <i class="ph ph-pencil-simple"></i>
                    </button>
                    <button type="button" onclick="openPriceModal('vianda', <?= $precios_ref['vianda'] ?? 0 ?>)" class="text-[10px] font-black text-blue-600 uppercase tracking-widest hover:bg-blue-50 px-2 py-0.5 rounded-lg transition-all">
                        Vianda $<?= number_format($precios_ref['vianda'] ?? 0, 0) ?> <i class="ph ph-pencil-simple"></i>
                    </button>
                </div>
            </div>

            <div class="flex items-center gap-5">
                <div class="flex items-center bg-slate-100 p-1.5 rounded-2xl border border-slate-200 shadow-sm">
                    <a href="?offset=<?= $offset-1 ?>" class="p-2.5 hover:bg-white hover:text-orange-600 rounded-xl transition-all shadow-sm"><i class="ph-bold ph-caret-left"></i></a>
                    <div class="px-5 text-center">
                        <span class="block text-[10px] font-black text-slate-400 uppercase tracking-tighter leading-none mb-1">Semana</span>
                        <span class="text-sm font-extrabold text-slate-700 whitespace-nowrap"><?= $monday->format('d/m') ?> — <?= (clone $monday)->modify('+4 days')->format('d/m') ?></span>
                    </div>
                    <a href="?offset=<?= $offset+1 ?>" class="p-2.5 hover:bg-white hover:text-orange-600 rounded-xl transition-all shadow-sm"><i class="ph-bold ph-caret-right"></i></a>
                </div>

                <button type="submit" form="mainForm" name="save_menus" class="bg-[#ea580c] text-white px-8 py-3.5 rounded-2xl text-sm font-black uppercase tracking-widest hover:bg-[#f97316] shadow-lg shadow-orange-200 transition-all flex items-center gap-2">
                    <i class="ph-bold ph-floppy-disk"></i> Guardar Menús
                </button>
            </div>
        </header>

        <section class="flex-1 p-10 overflow-y-auto bg-slate-50/50">
            <form id="mainForm" method="POST">
                <div class="grid grid-cols-1 xl:grid-cols-5 gap-8 max-w-[1600px] mx-auto">
                    <?php for ($i = 0; $i < 5; $i++): 
                        $date = (clone $monday)->modify("+$i days");
                        $f = $date->format('Y-m-d');
                        $is_today = ($f === $hoy_fmt);
                        $m = $menus_db[$f] ?? null; 
                    ?>
                    <div class="day-card bg-white border <?= $is_today ? 'border-orange-200 ring-4 ring-orange-50' : 'border-slate-200' ?> p-8 shadow-sm flex flex-col relative overflow-hidden group">
                        <?php if($is_today): ?>
                            <div class="absolute top-0 right-0 bg-orange-500 text-white px-5 py-2 rounded-bl-2xl text-[10px] font-black tracking-widest animate-pulse">HOY</div>
                        <?php endif; ?>

                        <div class="mb-8">
                            <h3 class="text-orange-600 font-black text-2xl tracking-tighter italic uppercase"><?= $dias_nombres[$i] ?></h3>
                            <div class="flex items-center gap-2 mt-1 text-slate-400">
                                <i class="ph-bold ph-calendar-blank"></i>
                                <span class="font-bold text-sm tracking-tight"><?= $date->format('d \d\e F') ?></span>
                            </div>
                        </div>

                        <div class="space-y-6 flex-1">
                            <div class="space-y-1">
                                <label class="label-caps"><i class="ph-fill ph-bowl-food text-orange-500"></i> Plato Principal</label>
                                <input type="text" name="menu[<?= $f ?>][principal]" value="<?= htmlspecialchars($m['plato_principal'] ?? '') ?>" class="menu-input font-bold text-slate-800 border-slate-200" placeholder="Ej: Milanesas con puré">
                            </div>
                            <div class="space-y-1">
                                <label class="label-caps"><i class="ph-bold ph-shuffle text-slate-400"></i> Opción Alternativa</label>
                                <input type="text" name="menu[<?= $f ?>][alternativo]" value="<?= htmlspecialchars($m['plato_alternativo'] ?? '') ?>" class="menu-input text-slate-600 italic" placeholder="Ej: Tarta de jamón y queso">
                            </div>
                            <div class="space-y-1">
                                <label class="label-caps text-emerald-600"><i class="ph-fill ph-leaf text-emerald-500"></i> Opción Vegetariana</label>
                                <input type="text" name="menu[<?= $f ?>][veggie]" value="<?= htmlspecialchars($m['opcion_veggie'] ?? '') ?>" class="menu-input text-emerald-700 bg-emerald-50/20 border-emerald-100" placeholder="Ej: Hamburguesa de lentejas">
                            </div>
                            <div class="space-y-1">
                                <label class="label-caps"><i class="ph-fill ph-cookie text-purple-500"></i> Postre</label>
                                <input type="text" name="menu[<?= $f ?>][postre]" value="<?= htmlspecialchars($m['postre'] ?? '') ?>" class="menu-input" placeholder="Ej: Fruta de estación">
                            </div>
                        </div>
                    </div>
                    <?php endfor; ?>
                </div>
            </form>
        </section>
    </main>

    <div id="priceModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-slate-900/60 backdrop-blur-sm">
        <div class="bg-white w-full max-w-sm rounded-[2.5rem] shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-200">
            <div class="p-8 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                <div>
                    <h2 class="text-xl font-black text-slate-900 uppercase tracking-tighter">Actualizar Precio</h2>
                    <p id="modalTipoTitle" class="text-xs font-bold text-orange-600 uppercase tracking-widest"></p>
                </div>
                <button onclick="closePriceModal()" class="text-slate-400 hover:text-red-500"><i class="ph ph-x-circle text-3xl"></i></button>
            </div>
            
            <form method="POST" class="p-8 space-y-6">
                <input type="hidden" name="tipo" id="inputTipo">
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Nuevo Valor ($)</label>
                    <div class="relative">
                        <span class="absolute left-5 top-1/2 -translate-y-1/2 font-bold text-slate-400">$</span>
                        <input type="number" name="nuevo_precio" id="inputPrecio" required class="w-full pl-10 pr-6 py-4 bg-slate-100 border-none rounded-2xl outline-none focus:ring-2 focus:ring-orange-500 font-black text-xl text-slate-700">
                    </div>
                </div>
                <button type="submit" name="update_price" class="w-full bg-dark-pro bg-slate-900 text-white py-4 rounded-2xl font-black uppercase tracking-widest hover:bg-slate-800 shadow-lg transition-all">
                    Guardar Nuevo Precio
                </button>
            </form>
        </div>
    </div>

    <script>
        function openPriceModal(tipo, precioActual) {
            document.getElementById('inputTipo').value = tipo;
            document.getElementById('inputPrecio').value = precioActual;
            document.getElementById('modalTipoTitle').innerText = "Servicio: " + tipo;
            document.getElementById('priceModal').classList.remove('hidden');
        }

        function closePriceModal() {
            document.getElementById('priceModal').classList.add('hidden');
        }

        <?php if ($success): ?>
            Swal.fire({ title: '¡Menú Actualizado!', text: 'La planificación semanal ha sido guardada.', icon: 'success', timer: 2000, showConfirmButton: false, customClass: { popup: 'rounded-[2.5rem]' } });
        <?php endif; ?>

        <?php if ($price_updated): ?>
            Swal.fire({ title: '¡Precio Actualizado!', text: 'El valor del servicio se actualizó correctamente.', icon: 'success', confirmButtonColor: '#ea580c', customClass: { popup: 'rounded-[2.5rem]' } });
        <?php endif; ?>
    </script>
</body>
</html>