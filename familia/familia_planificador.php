<?php
session_start();
require '../conexion/db.php';

// 1. Identificación del Alumno
$alumno_id = isset($_GET['alumno_id']) ? (int)$_GET['alumno_id'] : 1; 

// 2. Lógica de Navegación y Salto de Fecha
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
if (isset($_GET['goto_date'])) {
    $target = new DateTime($_GET['goto_date']);
    $base = new DateTime();
    $base->setISODate((int)$base->format('Y'), (int)$base->format('W'));
    $target->setISODate((int)$target->format('Y'), (int)$target->format('W'));
    $offset = (int)($base->diff($target)->format('%r%a') / 7);
}

$monday = new DateTime();
$monday->setISODate((int)date('Y'), (int)date('W'));
if ($offset !== 0) { $monday->modify("$offset week"); }
$start_week = $monday->format('Y-m-d');
$end_week = (clone $monday)->modify('+4 days')->format('Y-m-d');

$success = false;

// 3. Procesar Guardado (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_plan'])) {
    try {
        $pdo->beginTransaction();
        foreach ($_POST['day'] as $fecha => $data) {
            $tipo = $data['tipo']; // 'menu', 'vianda', 'ausente'
            $plato = $data['plato'] ?? 'Ninguno';
            $postre = isset($data['postre']) ? 'SI' : 'NO';
            $obs = ($tipo === 'menu') ? "Plato: $plato | Postre: $postre" : "";

            $stmt = $pdo->prepare("INSERT INTO selecciones (alumno_id, fecha, tipo, observacion) 
                                   VALUES (?, ?, ?, ?) 
                                   ON DUPLICATE KEY UPDATE tipo = VALUES(tipo), observacion = VALUES(observacion)");
            $stmt->execute([$alumno_id, $fecha, $tipo, $obs]);
        }
        $pdo->commit();
        $success = true;
    } catch (Exception $e) { $pdo->rollBack(); }
}

// 4. Obtener Datos (Fecha siempre primero para FETCH_UNIQUE)
$stmt_m = $pdo->prepare("SELECT fecha, plato_principal, plato_alternativo, opcion_veggie, postre FROM menus WHERE fecha BETWEEN ? AND ?");
$stmt_m->execute([$start_week, $end_week]);
$menus_semana = $stmt_m->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);

$stmt_s = $pdo->prepare("SELECT fecha, tipo, observacion FROM selecciones WHERE alumno_id = ? AND fecha BETWEEN ? AND ?");
$stmt_s->execute([$alumno_id, $start_week, $end_week]);
$selecciones = $stmt_s->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);

$precios = $pdo->query("SELECT tipo, precio FROM precios_servicios")->fetchAll(PDO::FETCH_KEY_PAIR);

$dias_nombres = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>EduMenu | Planificador</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #fcfcfd; }
        .day-card { border-radius: 2.5rem; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); background: white; }
        .section-blur { filter: blur(6px); opacity: 0.3; pointer-events: none; transform: scale(0.97); }
        .dish-btn {
            width: 100%; text-align: left; padding: 0.85rem 1rem; border-radius: 18px;
            border: 2px solid #f1f5f9; font-size: 0.75rem; font-weight: 700;
            transition: all 0.2s; position: relative; display: flex; align-items: center; gap: 8px;
        }
        .dish-btn.active { border-color: #ea580c; background: #fff7ed; color: #9a3412; }
        .dish-btn.active i { color: #ea580c; }
        
        .mode-btn {
            flex: 1; padding: 0.75rem; border-radius: 18px; font-size: 9px; font-weight: 800;
            text-transform: uppercase; border: 2px solid #f1f5f9; background: #f8fafc; color: #94a3b8; transition: all 0.3s;
        }
        .mode-btn.active-vianda { border-color: #0f172a; background: #1e293b; color: white; }
        .mode-btn.active-ausente { border-color: #ef4444; background: #fef2f2; color: #ef4444; }
    </style>
</head>
<body class="flex h-screen overflow-hidden text-slate-800">

    <?php include '../includes/sidebar_familia.php'; ?>

    <main class="flex-1 flex flex-col overflow-hidden">
        <header class="h-24 bg-white/80 backdrop-blur-md border-b border-slate-100 px-8 flex items-center justify-between sticky top-0 z-10">
            <div>
                <h1 class="text-xl font-black tracking-tighter uppercase italic">Planificador Semanal</h1>
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-0.5">Selección de platos para el alumno</p>
            </div>
            
            <div class="flex items-center bg-white p-1.5 rounded-2xl border border-slate-200 shadow-sm">
                <a href="?offset=<?= $offset-1 ?>" class="w-10 h-10 flex items-center justify-center hover:bg-slate-50 text-slate-400 hover:text-orange-600 rounded-xl transition-all">
                    <i class="ph-bold ph-arrow-left text-lg"></i>
                </a>
                
                <div class="px-6 text-center cursor-pointer relative group" onclick="document.getElementById('calTrigger').showPicker()">
                    <span class="block text-[8px] font-black text-orange-500 uppercase tracking-widest leading-none mb-0.5">Semana del</span>
                    <span class="text-xs font-black text-slate-700 whitespace-nowrap uppercase">
                        <?= $monday->format('d M') ?> — <?= (clone $monday)->modify('+4 days')->format('d M') ?>
                    </span>
                    <input type="date" id="calTrigger" class="absolute inset-0 opacity-0" onchange="window.location.href='?goto_date=' + this.value">
                </div>

                <a href="?offset=<?= $offset+1 ?>" class="w-10 h-10 flex items-center justify-center hover:bg-slate-50 text-slate-400 hover:text-orange-600 rounded-xl transition-all">
                    <i class="ph-bold ph-arrow-right text-lg"></i>
                </a>
            </div>
        </header>

        <section class="flex-1 p-8 overflow-y-auto bg-[#fafbfc]">
            <form method="POST" id="mainForm">
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-6 max-w-[1550px] mx-auto">
                    <?php for ($i = 0; $i < 5; $i++): 
                        $date = (clone $monday)->modify("+$i days");
                        $f = $date->format('Y-m-d');
                        $m = $menus_semana[$f] ?? null;
                        $sel = $selecciones[$f] ?? null;
                        $tipo = $sel['tipo'] ?? 'menu'; 

                        $plato_sel = ''; $postre_sel = false;
                        if($sel && strpos($sel['observacion'] ?? '', 'Plato:') !== false) {
                            preg_match('/Plato: (.*?) \|/', $sel['observacion'], $match_p);
                            $plato_sel = $match_p[1] ?? '';
                            $postre_sel = (strpos($sel['observacion'], 'Postre: SI') !== false);
                        }
                    ?>
                    <div class="day-card p-6 border border-slate-100 shadow-sm flex flex-col h-[560px] relative overflow-hidden group hover:shadow-xl transition-shadow" id="card-<?= $f ?>">
                        <div class="mb-6 flex justify-between items-start">
                            <div>
                                <span class="text-slate-400 font-bold text-[10px] uppercase tracking-widest block mb-1"><?= $dias_nombres[$i] ?></span>
                                <h3 class="text-3xl font-black text-slate-900 tracking-tighter"><?= $date->format('d') ?></h3>
                            </div>
                            <div class="w-10 h-10 bg-orange-50 text-orange-500 rounded-xl flex items-center justify-center">
                                <i class="ph-bold ph-calendar-star text-xl"></i>
                            </div>
                        </div>

                        <input type="hidden" name="day[<?= $f ?>][tipo]" id="tipo-<?= $f ?>" value="<?= $tipo ?>">
                        <input type="hidden" name="day[<?= $f ?>][plato]" id="plato-<?= $f ?>" value="<?= $plato_sel ?>">

                        <div id="section-<?= $f ?>" class="flex-1 space-y-3 transition-all duration-500 <?= ($tipo !== 'menu') ? 'section-blur' : '' ?>">
                            <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-2 flex items-center gap-1">
                                 Opciones Disponibles
                            </p>

                            <button type="button" onclick="setDish('<?= $f ?>', 'Principal')" id="btn-p-<?= $f ?>" class="dish-btn <?= ($plato_sel == 'Principal') ? 'active' : '' ?>">
                                <i class="ph-bold ph-bowl-food text-lg"></i>
                                <span><?= $m['plato_principal'] ?? 'No cargado' ?></span>
                            </button>

                            <button type="button" onclick="setDish('<?= $f ?>', 'Alternativo')" id="btn-a-<?= $f ?>" class="dish-btn <?= ($plato_sel == 'Alternativo') ? 'active' : '' ?>">
                                <i class="ph-bold ph-cooking-pot text-lg"></i>
                                <span><?= $m['plato_alternativo'] ?? 'No cargado' ?></span>
                            </button>

                            <button type="button" onclick="setDish('<?= $f ?>', 'Veggie')" id="btn-v-<?= $f ?>" class="dish-btn <?= ($plato_sel == 'Veggie') ? 'active' : '' ?>">
                                <i class="ph-bold ph-leaf text-lg"></i>
                                <span><?= $m['opcion_veggie'] ?? 'No cargado' ?></span>
                            </button>

                            <div class="pt-4 mt-2 border-t border-slate-50">
                                <label class="cursor-pointer">
                                    <input type="checkbox" name="day[<?= $f ?>][postre]" id="chk-postre-<?= $f ?>" class="hidden" <?= $postre_sel ? 'checked' : '' ?> onchange="togglePostreUI('<?= $f ?>')">
                                    <div id="ui-postre-<?= $f ?>" class="p-3.5 rounded-2xl border border-dashed flex items-center justify-between transition-all <?= $postre_sel ? 'border-purple-400 bg-purple-50 text-purple-700' : 'border-slate-100 text-slate-400' ?>">
                                        <div class="flex items-center gap-2">
                                            <i class="ph-bold ph-cookie text-xl"></i>
                                            <span class="text-[10px] font-black uppercase"><?= $postre_sel ? 'Postre OK' : 'Postre' ?></span>
                                        </div>
                                        <div class="w-2 h-2 rounded-full <?= $postre_sel ? 'bg-purple-500' : 'bg-slate-200' ?>"></div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="mt-4 pt-4 border-t border-slate-50 flex gap-2">
                            <button type="button" onclick="setMode('<?= $f ?>', 'vianda')" id="m-v-<?= $f ?>" class="mode-btn <?= ($tipo == 'vianda') ? 'active-vianda' : '' ?>">
                                <i class="ph-bold ph-backpack text-base mb-0.5"></i><br>Vianda
                            </button>
                            <button type="button" onclick="setMode('<?= $f ?>', 'ausente')" id="m-a-<?= $f ?>" class="mode-btn <?= ($tipo == 'ausente') ? 'active-ausente' : '' ?>">
                                <i class="ph-bold ph-user-minus text-base mb-0.5"></i><br>Ausente
                            </button>
                        </div>
                    </div>
                    <?php endfor; ?>
                </div>

                <div class="mt-12 flex justify-center pb-20">
                    <button type="submit" name="guardar_plan" class="bg-[#ea580c] text-white px-16 py-5 rounded-[2rem] font-black text-lg hover:bg-orange-700 hover:scale-105 transition-all shadow-xl shadow-orange-200 flex items-center gap-4 active:scale-95">
                        <i class="ph-bold ph-cloud-arrow-up text-3xl"></i> 
                        GUARDAR PLANIFICACIÓN
                    </button>
                </div>
            </form>
        </section>
    </main>

    <script>
        function setDish(fecha, choice) {
            document.getElementById('plato-' + fecha).value = choice;
            document.getElementById('tipo-' + fecha).value = 'menu';
            const card = document.getElementById('card-' + fecha);
            card.querySelectorAll('.dish-btn').forEach(b => b.classList.remove('active'));
            const map = { 'Principal': 'btn-p-', 'Alternativo': 'btn-a-', 'Veggie': 'btn-v-' };
            document.getElementById(map[choice] + fecha).classList.add('active');
            document.getElementById('section-' + fecha).classList.remove('section-blur');
            document.getElementById('m-v-' + fecha).classList.remove('active-vianda');
            document.getElementById('m-a-' + fecha).classList.remove('active-ausente');
        }

        function setMode(fecha, type) {
            const input = document.getElementById('tipo-' + fecha);
            const section = document.getElementById('section-' + fecha);
            if (input.value === type) {
                input.value = 'menu'; section.classList.remove('section-blur');
                document.getElementById('m-' + type.charAt(0) + '-' + fecha).classList.remove('active-' + type);
            } else {
                input.value = type; section.classList.add('section-blur');
                document.getElementById('m-v-' + fecha).classList.remove('active-vianda');
                document.getElementById('m-a-' + fecha).classList.remove('active-ausente');
                document.getElementById('m-' + type.charAt(0) + '-' + fecha).classList.add('active-' + type);
                document.getElementById('plato-' + fecha).value = '';
                document.getElementById('card-' + fecha).querySelectorAll('.dish-btn').forEach(b => b.classList.remove('active'));
            }
        }

        function togglePostreUI(fecha) {
            const chk = document.getElementById('chk-postre-' + fecha);
            const ui = document.getElementById('ui-postre-' + fecha);
            const text = ui.querySelector('span');
            if(chk.checked) {
                ui.classList.replace('border-slate-100', 'border-purple-400');
                ui.classList.add('bg-purple-50', 'text-purple-700');
                text.innerText = 'Postre OK';
            } else {
                ui.classList.replace('border-purple-400', 'border-slate-100');
                ui.classList.remove('bg-purple-50', 'text-purple-700');
                text.innerText = 'Postre';
            }
        }

        <?php if ($success): ?>
            Swal.fire({ title: '¡Perfecto!', text: 'La planificación ha sido guardada correctamente.', icon: 'success', confirmButtonColor: '#ea580c', borderRadius: '2.5rem' });
        <?php endif; ?>
    </script>
</body>
</html>