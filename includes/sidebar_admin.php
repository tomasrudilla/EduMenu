<?php
// 1. Detectar el archivo actual
$archivo_actual = basename($_SERVER['PHP_SELF']);

// 2. Lógica para sub-niveles de Alumnos
$alumno_nombre_sidebar = "";
$es_sub_alumno = false;

// Si estamos en editar o ver alumno, marcamos Gestión Alumnos como activo
if ($archivo_actual == 'editar_alumno.php' || $archivo_actual == 'ver_alumno.php') {
    $es_sub_alumno = true;
    
    // Obtenemos el ID desde la URL
    $id_url = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if ($id_url > 0) {
        // CORRECCIÓN: Consultamos nombre y apellido concatenados
        $stmt_side = $pdo->prepare("SELECT CONCAT(nombre, ' ', apellido) FROM alumnos WHERE id = ?");
        $stmt_side->execute([$id_url]);
        $alumno_nombre_sidebar = $stmt_side->fetchColumn();
    }
}
?>

<aside class="w-72 bg-[#0f172a] text-white flex flex-col border-r border-slate-800 h-screen">
    <div class="p-8 flex items-center gap-3">
        <div class="bg-[#ea580c] p-2 rounded-xl shadow-lg shadow-orange-900/20">
            <i class="ph ph-bowl-food text-2xl text-white"></i>
        </div>
        <span class="text-2xl font-bold tracking-tight text-white uppercase italic">EduMenu</span>
    </div>

    <nav class="flex-1 px-4 space-y-2">
        
        <div>
            <a href="admin_alumnos.php" 
               class="flex items-center gap-3 p-4 rounded-2xl font-semibold transition-all group 
               <?php echo ($archivo_actual == 'admin_alumnos.php' || $es_sub_alumno) ? 'bg-[#ea580c] text-white shadow-lg shadow-orange-900/40' : 'text-slate-400 hover:text-white hover:bg-slate-800'; ?>">
                <i class="ph ph-users-three text-xl"></i>
                Gestión Alumnos
            </a>

            <?php if ($es_sub_alumno): ?>
                <div class="ml-10 mt-2 p-3 border-l-2 border-orange-500/30 bg-orange-500/5 rounded-r-xl">
                    <p class="text-[9px] font-black text-orange-500 uppercase tracking-widest leading-none mb-1">
                        <?php echo ($archivo_actual == 'editar_alumno.php') ? 'Editando a:' : 'Viendo a:'; ?>
                    </p>
                    <p class="text-xs font-bold text-white truncate">
                        <?php echo htmlspecialchars($alumno_nombre_sidebar ?: 'Alumno #' . $id_url); ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>

        <a href="admin_menus.php" 
           class="flex items-center gap-3 p-4 rounded-2xl font-semibold transition-all group 
           <?php echo ($archivo_actual == 'admin_menus.php') ? 'bg-[#ea580c] text-white shadow-lg shadow-orange-900/40' : 'text-slate-400 hover:text-white hover:bg-slate-800'; ?>">
            <i class="ph ph-calendar-check text-xl"></i>
            Planificar Menús
        </a>

        <a href="admin_finanzas.php" 
           class="flex items-center gap-3 p-4 rounded-2xl font-semibold transition-all group 
           <?php echo ($archivo_actual == 'admin_finanzas.php') ? 'bg-[#ea580c] text-white shadow-lg shadow-orange-900/40' : 'text-slate-400 hover:text-white hover:bg-slate-800'; ?>">
            <i class="ph ph-chart-line-up text-xl"></i>
            Finanzas
        </a>

        <a href="admin_historial.php" 
           class="flex items-center gap-3 p-4 rounded-2xl font-semibold transition-all group 
           <?php echo ($archivo_actual == 'admin_historial.php') ? 'bg-[#ea580c] text-white shadow-lg shadow-orange-900/40' : 'text-slate-400 hover:text-white hover:bg-slate-800'; ?>">
            <i class="ph ph-clock-counter-clockwise text-xl"></i>
            Historial Logs
        </a>
    </nav>

    <div class="p-6 border-t border-slate-800">
        <div class="flex items-center gap-3 p-3 bg-slate-800/50 rounded-2xl border border-slate-700/50">
            <div class="w-10 h-10 bg-[#ea580c] rounded-xl flex items-center justify-center font-bold text-white shadow-inner">A</div>
            <div class="overflow-hidden">
                <p class="text-sm font-bold text-white truncate">Administrador</p>
                <p class="text-[10px] text-slate-500 uppercase tracking-widest font-bold">Panel de Control</p>
            </div>
            <a href="../logout.php" class="ml-auto text-slate-400 hover:text-orange-500 transition-colors">
                <i class="ph ph-sign-out text-lg"></i>
            </a>
        </div>
    </div>
</aside>