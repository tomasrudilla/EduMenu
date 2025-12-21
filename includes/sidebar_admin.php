<?php
// Obtenemos el nombre del archivo actual para marcar el link como activo
$archivo_actual = basename($_SERVER['PHP_SELF']);
?>

<aside class="w-72 bg-[#0f172a] text-white flex flex-col border-r border-slate-800 h-screen">
    <div class="p-8 flex items-center gap-3">
        <div class="bg-[#ea580c] p-2 rounded-xl shadow-lg shadow-orange-900/20">
            <i class="ph ph-bowl-food text-2xl text-white"></i>
        </div>
        <span class="text-2xl font-bold tracking-tight text-white">EduMenu</span>
    </div>

    <nav class="flex-1 px-4 space-y-2">
        
        <a href="admin_alumnos.php" 
           class="flex items-center gap-3 p-4 rounded-2xl font-semibold transition-all group 
           <?php echo ($archivo_actual == 'admin_alumnos.php') ? 'bg-[#ea580c] text-white shadow-lg shadow-orange-900/40' : 'text-slate-400 hover:text-white hover:bg-slate-800'; ?>">
            <i class="ph ph-users-three text-xl"></i>
            Gestión Alumnos
        </a>

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