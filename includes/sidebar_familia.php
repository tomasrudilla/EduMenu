<?php
// 1. Obtener nombre de archivo para links activos
$archivo_actual = basename($_SERVER['PHP_SELF']);

// 2. Obtener datos reales de la base de datos (se asume sesión iniciada)
$familia_id = $_SESSION['familia_id'] ?? 1;

$stmt_fam = $pdo->prepare("SELECT apellido_responsable FROM familias WHERE id = ?");
$stmt_fam->execute([$familia_id]);
$familia_data = $stmt_fam->fetch();

// CORRECCIÓN: Se agrega 'anio' a la selección para poder mostrarlo
$stmt_alumnos = $pdo->prepare("SELECT nombre, apellido, anio, curso FROM alumnos WHERE familia_id = ? AND status = 'ACTIVE'");
$stmt_alumnos->execute([$familia_id]);
$alumnos_vinculados = $stmt_alumnos->fetchAll();
$cantidad_alumnos = count($alumnos_vinculados);

$stmt_balance = $pdo->prepare("SELECT SUM(monto) FROM transacciones WHERE familia_id = ?");
$stmt_balance->execute([$familia_id]);
$balance = $stmt_balance->fetchColumn() ?: 0;
$deuda_monto = ($balance < 0) ? abs($balance) : 0;
?>

<nav class="md:hidden bg-[#0f172a] text-white border-b border-slate-800 sticky top-0 z-50 h-20 px-6 flex items-center justify-between">
    <div class="flex items-center gap-3">
        <div class="bg-[#ea580c] p-1.5 rounded-lg shadow-lg shadow-orange-900/20">
            <i class="ph ph-bowl-food text-xl text-white"></i>
        </div>
        <span class="text-xl font-bold tracking-tight text-white">EduMenu</span>
    </div>

    <button id="hamburger-btn" class="text-3xl text-slate-400 hover:text-white transition-colors">
        <i class="ph ph-list"></i>
    </button>

    <div id="mobile-drawer" class="fixed inset-0 bg-[#0f172a] z-[60] transform translate-x-full transition-transform duration-300 ease-in-out p-8">
        <div class="flex justify-between items-center mb-10">
            <span class="text-xl font-black uppercase tracking-tighter text-orange-500">Menú</span>
            <button id="close-drawer" class="text-3xl text-slate-400"><i class="ph ph-x"></i></button>
        </div>

        <div class="space-y-6">
            <nav class="flex flex-col gap-3">
                <a href="familia_planificador.php" class="flex items-center gap-4 p-4 rounded-2xl font-bold <?= ($archivo_actual == 'familia_planificador.php') ? 'bg-[#ea580c] text-white' : 'text-slate-400 bg-slate-800/30' ?>">
                    <i class="ph ph-calendar-plus text-2xl"></i> Planificador
                </a>
                <a href="familia_historial.php" class="flex items-center gap-4 p-4 rounded-2xl font-bold <?= ($archivo_actual == 'familia_historial.php') ? 'bg-[#ea580c] text-white' : 'text-slate-400 bg-slate-800/30' ?>">
                    <i class="ph ph-clock-counter-clockwise text-2xl"></i> Historial
                </a>
                <a href="familia_facturas.php" class="flex items-center gap-4 p-4 rounded-2xl font-bold <?= ($archivo_actual == 'familia_facturas.php') ? 'bg-[#ea580c] text-white' : 'text-slate-400 bg-slate-800/30' ?>">
                    <i class="ph ph-receipt text-2xl"></i> Facturas
                </a>
                <a href="familia_perfil.php" class="flex items-center gap-4 p-4 rounded-2xl font-bold <?= ($archivo_actual == 'familia_perfil.php') ? 'bg-[#ea580c] text-white' : 'text-slate-400 bg-slate-800/30' ?>">
                    <i class="ph ph-user-gear text-2xl"></i> Mi Perfil
                </a>
            </nav>

            <div class="pt-6 border-t border-slate-800">
                <a href="../logout.php" class="flex items-center gap-4 p-4 text-red-400 font-bold">
                    <i class="ph ph-sign-out text-2xl"></i> Cerrar Sesión
                </a>
            </div>
        </div>
    </div>
</nav>

<aside class="hidden md:flex w-72 bg-[#0f172a] text-white flex-col border-r border-slate-800 h-screen sticky top-0 flex-shrink-0">
    <div class="p-8 flex items-center gap-3">
        <div class="bg-[#ea580c] p-2 rounded-xl shadow-lg shadow-orange-900/20">
            <i class="ph ph-bowl-food text-2xl text-white"></i>
        </div>
        <span class="text-2xl font-bold tracking-tight text-white">EduMenu</span>
    </div>

    <div class="px-6 mb-6">
        <div class="bg-slate-800/50 border border-slate-700/50 p-4 rounded-2xl">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 bg-orange-500/20 text-orange-500 rounded-full flex items-center justify-center font-bold">
                    <?= strtoupper(substr($familia_data['apellido_responsable'] ?? 'F', 0, 1)) ?>
                </div>
                <div>
                    <p class="text-sm font-bold text-white leading-tight">Fam. <?= htmlspecialchars($familia_data['apellido_responsable'] ?? 'Gomez') ?></p>
                    <p class="text-[10px] text-slate-400 uppercase tracking-wider">ID Cliente: #<?= str_pad($familia_id, 4, '0', STR_PAD_LEFT) ?></p>
                </div>
            </div>

            <div class="space-y-2 pt-2 border-t border-slate-700/50">
                <?php foreach (array_slice($alumnos_vinculados, 0, 2) as $alu): ?>
                    <div class="text-[10px] text-slate-300 bg-slate-700/30 p-2 rounded-lg flex items-center gap-2">
                        <i class="ph ph-student text-orange-500"></i>
                        <span class="truncate">
                            <strong><?= htmlspecialchars($alu['nombre'] . ' ' . $alu['apellido']) ?></strong>
                            <span class="text-slate-500 block">
                                <?= htmlspecialchars($alu['anio']) ?>º <?= htmlspecialchars($alu['curso']) ?>
                            </span>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <nav class="flex-1 px-4 space-y-1">
        <a href="familia_planificador.php" class="flex items-center gap-3 p-3.5 rounded-xl font-semibold transition-all <?= ($archivo_actual == 'familia_planificador.php') ? 'bg-[#ea580c] text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' ?>">
            <i class="ph ph-calendar-plus text-xl"></i> Planificador
        </a>
        <a href="familia_historial.php" class="flex items-center gap-3 p-3.5 rounded-xl font-semibold transition-all <?= ($archivo_actual == 'familia_historial.php') ? 'bg-[#ea580c] text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' ?>">
            <i class="ph ph-clock-counter-clockwise text-xl"></i> Historial
        </a>
        <a href="familia_facturas.php" class="flex items-center gap-3 p-3.5 rounded-xl font-semibold transition-all <?= ($archivo_actual == 'familia_facturas.php') ? 'bg-[#ea580c] text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' ?>">
            <i class="ph ph-receipt text-xl"></i> Facturas
        </a>
        <a href="familia_perfil.php" class="flex items-center gap-3 p-3.5 rounded-xl font-semibold transition-all <?= ($archivo_actual == 'familia_perfil.php') ? 'bg-[#ea580c] text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' ?>">
            <i class="ph ph-user-gear text-xl"></i> Perfil
        </a>
    </nav>

    <div class="px-6 mb-4">
        <div class="<?= ($deuda_monto > 0) ? 'bg-red-500/10 border-red-500/20' : 'bg-emerald-500/10 border-emerald-500/20' ?> border p-4 rounded-2xl flex items-center justify-between">
            <div>
                <p class="text-[9px] font-bold <?= ($deuda_monto > 0) ? 'text-red-400' : 'text-emerald-400' ?> uppercase tracking-widest">Situación</p>
                <p class="text-sm font-black text-white"><?= ($deuda_monto > 0) ? '$'.number_format($deuda_monto, 0, ',', '.') : 'AL DÍA' ?></p>
            </div>
            <i class="ph ph-<?= ($deuda_monto > 0) ? 'warning-circle text-red-500' : 'check-circle text-emerald-500' ?> text-xl"></i>
        </div>
    </div>

    <div class="p-6 border-t border-slate-800">
        <a href="../logout.php" class="flex items-center gap-3 text-slate-400 hover:text-red-400 transition-colors font-medium text-sm px-4 py-2 rounded-xl hover:bg-red-500/10">
            <i class="ph ph-sign-out text-xl"></i> Cerrar Sesión
        </a>
    </div>
</aside>

<script>
    // Lógica para abrir/cerrar el drawer móvil
    const hamburgerBtn = document.getElementById('hamburger-btn');
    const closeDrawerBtn = document.getElementById('close-drawer');
    const drawer = document.getElementById('mobile-drawer');

    hamburgerBtn?.addEventListener('click', () => {
        drawer.classList.remove('translate-x-full');
    });

    closeDrawerBtn?.addEventListener('click', () => {
        drawer.classList.add('translate-x-full');
    });
</script>