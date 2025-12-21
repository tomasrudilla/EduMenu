<?php
// Obtenemos el nombre del archivo actual para marcar el link como activo
$archivo_actual = basename($_SERVER['PHP_SELF']);
?>

<aside class="w-72 bg-[#0f172a] text-white flex flex-col border-r border-slate-800 h-screen">
    <div class="p-8 flex items-center gap-3">
        <div class="bg-[#ea580c] p-2 rounded-xl shadow-lg">
            <i class="ph ph-bowl-food text-2xl text-white"></i>
        </div>
        <span class="text-2xl font-bold tracking-tight text-white">EduMenu</span>
    </div>

    <div class="px-6 mb-6">
        <div class="bg-slate-800/50 border border-slate-700/50 p-4 rounded-2xl text-left">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 bg-orange-500/20 text-orange-500 rounded-full flex items-center justify-center font-bold">G</div>
                <div>
                    <p class="text-sm font-bold leading-tight text-white">Familia Gomez</p>
                    <p class="text-[10px] text-slate-400 uppercase tracking-wider">ID Cliente: #4402</p>
                </div>
            </div>
            <div class="text-[11px] text-slate-300 bg-slate-700/50 p-2 rounded-lg">
                <i class="ph ph-student"></i> Alumno: <strong>Tomás (5to A)</strong>
            </div>
        </div>
    </div>

    <nav class="flex-1 px-4 space-y-1">
        <a href="familia_planificador.php" 
           class="flex items-center gap-3 p-3.5 rounded-xl font-semibold transition-all group 
           <?php echo ($archivo_actual == 'familia_planificador.php') ? 'bg-[#ea580c] text-white shadow-lg shadow-orange-900/20' : 'text-slate-400 hover:text-white hover:bg-slate-800'; ?>">
            <i class="ph ph-calendar-plus text-xl"></i>
            Planificador
        </a>

        <a href="familia_historial.php" 
           class="flex items-center gap-3 p-3.5 rounded-xl font-semibold transition-all group 
           <?php echo ($archivo_actual == 'familia_historial.php') ? 'bg-[#ea580c] text-white shadow-lg shadow-orange-900/20' : 'text-slate-400 hover:text-white hover:bg-slate-800'; ?>">
            <i class="ph ph-clock-counter-clockwise text-xl"></i>
            Historial
        </a>

        <a href="familia_facturas.php" 
           class="flex items-center gap-3 p-3.5 rounded-xl font-semibold transition-all group 
           <?php echo ($archivo_actual == 'familia_facturas.php') ? 'bg-[#ea580c] text-white shadow-lg shadow-orange-900/20' : 'text-slate-400 hover:text-white hover:bg-slate-800'; ?>">
            <i class="ph ph-receipt text-xl"></i>
            Facturas & Pagos
        </a>

        <a href="familia_perfil.php" 
           class="flex items-center gap-3 p-3.5 rounded-xl font-semibold transition-all group 
           <?php echo ($archivo_actual == 'familia_perfil.php') ? 'bg-[#ea580c] text-white shadow-lg shadow-orange-900/20' : 'text-slate-400 hover:text-white hover:bg-slate-800'; ?>">
            <i class="ph ph-user-gear text-xl"></i>
            Perfil Alumno
        </a>
    </nav>

    <div class="p-6 border-t border-slate-800">
        <a href="../logout.php" class="flex items-center gap-3 text-slate-400 hover:text-red-400 transition-colors font-medium text-sm px-4 py-2 rounded-xl hover:bg-red-500/10">
            <i class="ph ph-sign-out text-xl"></i>
            Cerrar Sesión
        </a>
    </div>
</aside>