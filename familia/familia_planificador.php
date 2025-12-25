<?php
session_start();
require '../conexion/db.php';

// 1. Identificación de la Familia e Hijos
$familia_id = $_SESSION['familia_id'] ?? 1; 

$stmt_hijos = $pdo->prepare("SELECT id, nombre, apellido, anio, curso FROM alumnos WHERE familia_id = ? AND status = 'ACTIVE'");
$stmt_hijos->execute([$familia_id]);
$mis_hijos = $stmt_hijos->fetchAll();

$alumno_id = isset($_GET['alumno_id']) ? (int)$_GET['alumno_id'] : ($mis_hijos[0]['id'] ?? 1);

// 2. Lógica de Navegación Semanal
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
$monday = new DateTime();
$monday->setISODate((int)date('Y'), (int)date('W'));
if ($offset !== 0) { $monday->modify("$offset week"); }
$start_week = $monday->format('Y-m-d');
$end_week = (clone $monday)->modify('+4 days')->format('Y-m-d');

$now = new DateTime(); 

$success = false;

// 3. Procesar Guardado con VALIDACIÓN DE HORARIO LÍMITE (9:00 AM)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_plan'])) {
    $stmt_val = $pdo->prepare("SELECT fecha FROM menus WHERE fecha BETWEEN ? AND ?");
    $stmt_val->execute([$start_week, $end_week]);
    $fechas_validas = $stmt_val->fetchAll(PDO::FETCH_COLUMN);

    try {
        $pdo->beginTransaction();
        foreach ($_POST['day'] as $fecha => $data) {
            $tipo = $data['tipo'] ?? ''; 
            
            $deadline = new DateTime($fecha . ' 09:00:00');
            if ($now > $deadline) continue; 

            if (empty($tipo) || !in_array($fecha, $fechas_validas)) continue;

            $plato = ($tipo === 'menu') ? ($data['plato'] ?: null) : null;
            if ($tipo === 'menu' && $plato === null) continue;

            $postre = (isset($data['postre']) && $tipo === 'menu') ? 1 : 0;

            $stmt = $pdo->prepare("INSERT INTO selecciones (alumno_id, fecha, tipo, plato_seleccionado, tiene_postre) 
                                   VALUES (?, ?, ?, ?, ?) 
                                   ON DUPLICATE KEY UPDATE 
                                   tipo = VALUES(tipo), 
                                   plato_seleccionado = VALUES(plato_seleccionado), 
                                   tiene_postre = VALUES(tiene_postre)");
            $stmt->execute([$alumno_id, $fecha, $tipo, $plato, $postre]);
        }
        $pdo->commit();
        $success = true;
    } catch (Exception $e) { 
        $pdo->rollBack(); 
    }
}

// 4. Obtener Datos para la Interfaz
$stmt_m = $pdo->prepare("SELECT fecha, id, plato_principal, plato_alternativo, opcion_veggie, postre FROM menus WHERE fecha BETWEEN ? AND ?");
$stmt_m->execute([$start_week, $end_week]);
$menus_semana = $stmt_m->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);

$stmt_s = $pdo->prepare("SELECT fecha, tipo, plato_seleccionado, tiene_postre FROM selecciones WHERE alumno_id = ? AND fecha BETWEEN ? AND ?");
$stmt_s->execute([$alumno_id, $start_week, $end_week]);
$selecciones = $stmt_s->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);

$dias_nombres = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduMenu | Planificador</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #fcfcfd; }
        .day-card { border-radius: 2.5rem; transition: all 0.4s; background: white; }
        .day-disabled { opacity: 0.6; filter: grayscale(1); pointer-events: none; }
        .section-blur { filter: blur(6px); opacity: 0.3; pointer-events: none; transform: scale(0.97); }
        .dish-btn {
            width: 100%; text-align: left; padding: 0.85rem 1rem; border-radius: 18px;
            border: 2px solid #f1f5f9; font-size: 0.75rem; font-weight: 700;
            transition: all 0.2s; display: flex; align-items: center; gap: 8px;
        }
        .dish-btn.active { border-color: #ea580c; background: #fff7ed; color: #9a3412; }
        .mode-btn {
            flex: 1; padding: 0.75rem; border-radius: 18px; font-size: 9px; font-weight: 800;
            text-transform: uppercase; border: 2px solid #f1f5f9; background: #f8fafc; color: #94a3b8; transition: all 0.3s;
        }
        .mode-btn.active-vianda { border-color: #0f172a; background: #1e293b; color: white; }
        .mode-btn.active-ausente { border-color: #ef4444; background: #fef2f2; color: #ef4444; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
    </style>
</head>
<body class="flex flex-col md:flex-row min-h-screen text-slate-800">

    <?php include '../includes/sidebar_familia.php'; ?>

    <main class="flex-1 flex flex-col min-w-0">
        <header class="min-h-24 bg-white/80 backdrop-blur-md border-b border-slate-100 px-4 md:px-8 py-4 flex flex-col sm:flex-row items-center justify-between sticky top-0 z-20 gap-4">
            <div>
                <h1 class="text-lg md:text-xl font-black tracking-tighter uppercase">Planificador Semanal</h1>
                <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest">Gestión de menú escolar</p>
            </div>
            
            <div class="flex items-center bg-white p-1 rounded-2xl border border-slate-200 shadow-sm">
                <a href="?alumno_id=<?= $alumno_id ?>&offset=<?= $offset-1 ?>" class="w-9 h-9 md:w-10 md:h-10 flex items-center justify-center hover:bg-slate-50 text-slate-400 hover:text-orange-600 rounded-xl transition-all">
                    <i class="ph-bold ph-arrow-left text-lg"></i>
                </a>
                <div class="px-4 md:px-6 text-center cursor-pointer relative" onclick="document.getElementById('calTrigger').showPicker()">
                    <span class="block text-[7px] font-black text-orange-500 uppercase tracking-widest leading-none mb-0.5">Semana del</span>
                    <span class="text-[10px] md:text-xs font-black text-slate-700 whitespace-nowrap uppercase">
                        <?= $monday->format('d M') ?> — <?= (clone $monday)->modify('+4 days')->format('d M') ?>
                    </span>
                    <input type="date" id="calTrigger" class="absolute inset-0 opacity-0" onchange="window.location.href='?alumno_id=<?= $alumno_id ?>&goto_date=' + this.value">
                </div>
                <a href="?alumno_id=<?= $alumno_id ?>&offset=<?= $offset+1 ?>" class="w-9 h-9 md:w-10 md:h-10 flex items-center justify-center hover:bg-slate-50 text-slate-400 hover:text-orange-600 rounded-xl transition-all">
                    <i class="ph-bold ph-arrow-right text-lg"></i>
                </a>
            </div>
        </header>

        <?php if (count($mis_hijos) > 1): ?>
        <div class="bg-white border-b border-slate-100 px-4 md:px-8 py-3 flex items-center gap-4 overflow-x-auto no-scrollbar shadow-sm">
            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest whitespace-nowrap">Planificar para:</span>
            <div class="flex gap-2">
                <?php foreach($mis_hijos as $hijo): ?>
                    <a href="?alumno_id=<?= $hijo['id'] ?>&offset=<?= $offset ?>" 
                       class="px-5 py-2 rounded-2xl text-[10px] font-bold transition-all border flex items-center gap-2 whitespace-nowrap
                       <?= ($alumno_id == $hijo['id']) ? 'bg-[#ea580c] text-white border-[#ea580c] shadow-lg shadow-orange-200' : 'bg-slate-50 text-slate-400 border-slate-200 hover:bg-slate-100' ?>">
                        <i class="ph-bold ph-student text-sm"></i> 
                        <?= htmlspecialchars($hijo['nombre'] . ' ' . $hijo['apellido']) ?> 
                        <span class="opacity-60">(<?= htmlspecialchars($hijo['anio']) ?>º <?= htmlspecialchars($hijo['curso']) ?>)</span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <section class="flex-1 p-4 md:p-8 overflow-y-auto bg-[#fafbfc]">
            <form method="POST" id="mainForm">
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4 md:gap-6 max-w-[1550px] mx-auto">
                    <?php for ($i = 0; $i < 5; $i++): 
                        $date = (clone $monday)->modify("+$i days");
                        $f = $date->format('Y-m-d');
                        $m = $menus_semana[$f] ?? null;
                        $sel = $selecciones[$f] ?? null;
                        
                        $deadline = new DateTime($f . ' 09:00:00');
                        $expired = ($now > $deadline);
                        $menu_cargado = ($m !== null); 
                        
                        $tipo = $sel['tipo'] ?? ''; 
                        $plato_sel = $sel['plato_seleccionado'] ?? '';
                        $postre_sel = (isset($sel['tiene_postre']) && $sel['tiene_postre'] == 1);
                    ?>
                    <div class="day-card p-5 md:p-6 border border-slate-100 shadow-sm flex flex-col min-h-[500px] relative overflow-hidden group <?= (!$menu_cargado || $expired) ? 'day-disabled' : 'hover:shadow-xl' ?>" id="card-<?= $f ?>">
                        
                        <?php if(!$menu_cargado): ?>
                            <div class="absolute inset-0 z-30 flex items-center justify-center bg-white/40 backdrop-blur-[2px]">
                                <span class="bg-slate-800 text-white text-[10px] font-black uppercase px-4 py-2 rounded-full shadow-lg">Menú no disponible</span>
                            </div>
                        <?php elseif($expired): ?>
                            <div class="absolute inset-0 z-30 flex items-center justify-center bg-white/10 backdrop-blur-[1px]">
                                <span class="bg-red-600 text-white text-[10px] font-black uppercase px-4 py-2 rounded-full shadow-lg">Plazo vencido (9:00 AM)</span>
                            </div>
                        <?php endif; ?>

                        <div class="mb-4 md:mb-6 flex justify-between items-start">
                            <div>
                                <span class="text-slate-400 font-bold text-[9px] uppercase tracking-widest block mb-1"><?= $dias_nombres[$i] ?></span>
                                <h3 class="text-2xl md:text-3xl font-black text-slate-900 tracking-tighter"><?= $date->format('d') ?></h3>
                            </div>
                            <div class="w-10 h-10 bg-orange-50 text-orange-500 rounded-xl flex items-center justify-center">
                                <i class="ph-bold ph-calendar-star text-xl"></i>
                            </div>
                        </div>

                        <input type="hidden" name="day[<?= $f ?>][tipo]" id="tipo-<?= $f ?>" value="<?= $tipo ?>">
                        <input type="hidden" name="day[<?= $f ?>][plato]" id="plato-<?= $f ?>" value="<?= $plato_sel ?>">

                        <div id="section-<?= $f ?>" class="flex-1 space-y-3 transition-all duration-500 <?= ($tipo === 'vianda' || $tipo === 'ausente') ? 'section-blur' : '' ?>">
                            <p class="text-[7px] font-black text-orange-600 uppercase tracking-widest mb-1">
                                Selección válida hasta las 9:00 AM del mismo día
                            </p>

                            <button type="button" onclick="setDish('<?= $f ?>', 'Principal')" class="dish-btn <?= ($plato_sel == 'Principal') ? 'active' : '' ?>">
                                <i class="ph-bold ph-bowl-food text-lg"></i>
                                <span class="truncate"><?= !empty($m['plato_principal']) ? htmlspecialchars($m['plato_principal']) : 'Plato no cargado' ?></span>
                            </button>

                            <button type="button" onclick="setDish('<?= $f ?>', 'Alternativo')" class="dish-btn <?= ($plato_sel == 'Alternativo') ? 'active' : '' ?>">
                                <i class="ph-bold ph-cooking-pot text-lg"></i>
                                <span class="truncate"><?= !empty($m['plato_alternativo']) ? htmlspecialchars($m['plato_alternativo']) : 'Opción no cargada' ?></span>
                            </button>

                            <button type="button" onclick="setDish('<?= $f ?>', 'Veggie')" class="dish-btn <?= ($plato_sel == 'Veggie') ? 'active' : '' ?>">
                                <i class="ph-bold ph-leaf text-lg"></i>
                                <span class="truncate"><?= !empty($m['opcion_veggie']) ? htmlspecialchars($m['opcion_veggie']) : 'Opción no cargada' ?></span>
                            </button>

                            <div class="pt-4 mt-2 border-t border-slate-50">
                                <label class="cursor-pointer">
                                    <input type="checkbox" name="day[<?= $f ?>][postre]" id="chk-postre-<?= $f ?>" class="hidden" <?= $postre_sel ? 'checked' : '' ?> onchange="togglePostreUI('<?= $f ?>')">
                                    <div id="ui-postre-<?= $f ?>" class="p-3 rounded-2xl border border-dashed flex items-center justify-between transition-all <?= $postre_sel ? 'border-purple-400 bg-purple-50 text-purple-700' : 'border-slate-100 text-slate-400' ?>">
                                        <div class="flex items-center gap-2">
                                            <i class="ph-bold ph-cookie text-lg"></i>
                                            <span class="text-[9px] font-black uppercase"><?= $postre_sel ? 'Postre OK' : 'Postre' ?></span>
                                        </div>
                                        <div class="w-2 h-2 rounded-full <?= $postre_sel ? 'bg-purple-500' : 'bg-slate-200' ?>"></div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="mt-4 pt-4 border-t border-slate-50 flex gap-2">
                            <button type="button" onclick="setMode('<?= $f ?>', 'vianda')" id="m-v-<?= $f ?>" class="mode-btn <?= ($tipo == 'vianda') ? 'active-vianda' : '' ?>">
                                <i class="ph-bold ph-backpack text-sm mb-0.5"></i><br>Vianda
                            </button>
                            <button type="button" onclick="setMode('<?= $f ?>', 'ausente')" id="m-a-<?= $f ?>" class="mode-btn <?= ($tipo == 'ausente') ? 'active-ausente' : '' ?>">
                                <i class="ph-bold ph-user-minus text-sm mb-0.5"></i><br>Ausente
                            </button>
                        </div>
                    </div>
                    <?php endfor; ?>
                </div>

                <div class="mt-8 md:mt-12 flex justify-center pb-20 px-4">
                    <button type="submit" name="guardar_plan" class="w-full md:w-auto bg-[#ea580c] text-white px-8 md:px-16 py-4 md:py-5 rounded-[2rem] font-black text-sm md:text-lg hover:bg-orange-700 shadow-xl flex items-center justify-center gap-4 transition-all">
                        <i class="ph-bold ph-cloud-arrow-up text-2xl md:text-3xl"></i> 
                        <span class="whitespace-nowrap uppercase">Guardar Planificación</span>
                    </button>
                </div>
            </form>
        </section>
    </main>

    <script>
        function isDayEnabled(fecha) {
            const card = document.getElementById('card-' + fecha);
            return card && !card.classList.contains('day-disabled');
        }

        function setDish(fecha, choice) {
            if(!isDayEnabled(fecha)) return;
            document.getElementById('plato-' + fecha).value = choice;
            document.getElementById('tipo-' + fecha).value = 'menu';
            
            const card = document.getElementById('card-' + fecha);
            card.querySelectorAll('.dish-btn').forEach(b => b.classList.remove('active'));
            const btns = card.querySelectorAll('.dish-btn');
            if(choice === 'Principal') btns[0].classList.add('active');
            if(choice === 'Alternativo') btns[1].classList.add('active');
            if(choice === 'Veggie') btns[2].classList.add('active');

            document.getElementById('section-' + fecha).classList.remove('section-blur');
            document.getElementById('m-v-' + fecha).classList.remove('active-vianda');
            document.getElementById('m-a-' + fecha).classList.remove('active-ausente');
        }

        function setMode(fecha, type) {
            if(!isDayEnabled(fecha)) return;
            const input = document.getElementById('tipo-' + fecha);
            const section = document.getElementById('section-' + fecha);
            
            if (input.value === type) {
                input.value = ''; 
                section.classList.remove('section-blur');
                document.getElementById('m-' + type.charAt(0) + '-' + fecha).classList.remove('active-' + type);
            } else {
                input.value = type;
                section.classList.add('section-blur');
                document.getElementById('m-v-' + fecha).classList.remove('active-vianda');
                document.getElementById('m-a-' + fecha).classList.remove('active-ausente');
                document.getElementById('m-' + type.charAt(0) + '-' + fecha).classList.add('active-' + type);
                document.getElementById('plato-' + fecha).value = '';
                document.getElementById('card-' + fecha).querySelectorAll('.dish-btn').forEach(b => b.classList.remove('active'));
            }
        }

        function togglePostreUI(fecha) {
            if(!isDayEnabled(fecha)) return;
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
            Swal.fire({ title: '¡Hecho!', text: 'Planificación guardada.', icon: 'success', confirmButtonColor: '#ea580c', borderRadius: '2.5rem' });
        <?php endif; ?>
    </script>
</body>
</html>