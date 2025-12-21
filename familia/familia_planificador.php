<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduMenu | Planificador Semanal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-thumb { background: #ea580c; border-radius: 10px; }

        .day-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
        }
        .day-card:hover {
            transform: translateY(-5px);
        }
        .day-card.active-menu {
            border-color: #ea580c;
            background-color: #fff7ed;
            box-shadow: 0 10px 15px -3px rgba(234, 88, 12, 0.1);
        }
        .day-card.active-vianda {
            border-color: #0f172a;
            background-color: #f8fafc;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 flex h-screen overflow-hidden">

    <?php include '../includes/sidebar_familia.php'; ?>

    <main class="flex-1 flex flex-col overflow-hidden">
        
        <header class="h-20 bg-white border-b border-slate-200 px-10 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-extrabold text-slate-900 uppercase tracking-tight">Planificador Semanal</h1>
                <p class="text-sm text-slate-500">Seleccioná la modalidad de almuerzo para cada día.</p>
            </div>
            <div class="flex items-center gap-4">
                <div class="flex items-center bg-slate-100 p-1 rounded-xl border border-slate-200">
                    <button class="p-2 hover:bg-white rounded-lg transition-all text-slate-400"><i class="ph ph-caret-left-bold"></i></button>
                    <span class="px-4 text-xs font-bold text-slate-700 uppercase tracking-widest">18 Dic - 22 Dic</span>
                    <button class="p-2 hover:bg-white rounded-lg transition-all text-slate-400"><i class="ph ph-caret-right-bold"></i></button>
                </div>
                <button class="bg-dark-pro bg-[#0f172a] text-white px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-slate-800 transition-all flex items-center gap-2">
                    <i class="ph ph-file-pdf"></i> Menú PDF
                </button>
            </div>
        </header>

        <div class="flex-1 p-10 overflow-y-auto">
            
            <div class="flex gap-6 mb-8 bg-white p-4 rounded-2xl border border-slate-200 w-fit shadow-sm">
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 bg-orange-500 rounded-full"></div>
                    <span class="text-xs font-bold text-slate-500 uppercase">Comen Menú</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 bg-slate-900 rounded-full"></div>
                    <span class="text-xs font-bold text-slate-500 uppercase">Traen Vianda</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 border-2 border-dashed border-slate-300 rounded-full"></div>
                    <span class="text-xs font-bold text-slate-500 uppercase">Sin Definir</span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
                
                <div class="day-card active-menu bg-white rounded-[2.5rem] border-2 border-orange-200 p-8 flex flex-col h-80 relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-6 text-orange-200">
                        <i class="ph ph-bowl-food text-6xl rotate-12"></i>
                    </div>
                    <span class="text-slate-400 font-bold text-sm uppercase tracking-widest mb-1">Lunes</span>
                    <h3 class="text-3xl font-black text-slate-900 mb-6">18</h3>
                    
                    <div class="mt-auto">
                        <p class="text-xs font-bold text-orange-600 uppercase mb-2">Plato del día:</p>
                        <p class="font-bold text-slate-800 leading-tight">Milanesa de Ternera con Puré de Papa</p>
                        <div class="mt-4 bg-orange-600 text-white text-center py-2 rounded-xl text-xs font-black uppercase tracking-tighter">
                            <i class="ph ph-check-circle-fill"></i> Menú Confirmado
                        </div>
                    </div>
                </div>

                <div class="day-card active-vianda bg-white rounded-[2.5rem] border-2 border-slate-900 p-8 flex flex-col h-80 relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-6 text-slate-200">
                        <i class="ph ph-backpack text-6xl -rotate-12"></i>
                    </div>
                    <span class="text-slate-400 font-bold text-sm uppercase tracking-widest mb-1">Martes</span>
                    <h3 class="text-3xl font-black text-slate-900 mb-6">19</h3>
                    
                    <div class="mt-auto">
                        <p class="text-xs font-bold text-slate-400 uppercase mb-2">Plato del día:</p>
                        <p class="font-medium text-slate-400 line-through italic">Fideos con Tuco</p>
                        <div class="mt-4 bg-slate-900 text-white text-center py-2 rounded-xl text-xs font-black uppercase tracking-tighter">
                            <i class="ph ph-bag-simple-fill"></i> Trae Vianda
                        </div>
                    </div>
                </div>

                <div class="day-card bg-white rounded-[2.5rem] border-2 border-dashed border-slate-200 p-8 flex flex-col h-80 hover:border-orange-300 group">
                    <span class="text-slate-400 font-bold text-sm uppercase tracking-widest mb-1">Miércoles</span>
                    <h3 class="text-3xl font-black text-slate-900 mb-6 text-slate-300 group-hover:text-orange-500 transition-colors">20</h3>
                    
                    <div class="mt-auto">
                        <p class="text-xs font-bold text-slate-400 uppercase mb-2">Propuesta:</p>
                        <p class="font-bold text-slate-700">Pollo al Horno con Arroz</p>
                        <div class="grid grid-cols-2 gap-2 mt-4">
                            <button class="bg-slate-100 hover:bg-orange-600 hover:text-white text-slate-600 p-2 rounded-xl text-[10px] font-bold uppercase transition-all">Elegir Menú</button>
                            <button class="bg-slate-100 hover:bg-slate-900 hover:text-white text-slate-600 p-2 rounded-xl text-[10px] font-bold uppercase transition-all">Vianda</button>
                        </div>
                    </div>
                </div>

                <div class="day-card bg-white rounded-[2.5rem] border-2 border-dashed border-slate-200 p-8 flex flex-col h-80 hover:border-orange-300 group">
                    <span class="text-slate-400 font-bold text-sm uppercase tracking-widest mb-1">Jueves</span>
                    <h3 class="text-3xl font-black text-slate-900 mb-6 text-slate-300 group-hover:text-orange-500 transition-colors">21</h3>
                    
                    <div class="mt-auto">
                        <p class="text-xs font-bold text-slate-400 uppercase mb-2">Propuesta:</p>
                        <p class="font-bold text-slate-700">Pastel de Papa</p>
                        <div class="grid grid-cols-2 gap-2 mt-4">
                            <button class="bg-slate-100 hover:bg-orange-600 hover:text-white text-slate-600 p-2 rounded-xl text-[10px] font-bold uppercase transition-all">Elegir Menú</button>
                            <button class="bg-slate-100 hover:bg-slate-900 hover:text-white text-slate-600 p-2 rounded-xl text-[10px] font-bold uppercase transition-all">Vianda</button>
                        </div>
                    </div>
                </div>

                <div class="day-card bg-white rounded-[2.5rem] border-2 border-dashed border-slate-200 p-8 flex flex-col h-80 hover:border-orange-300 group">
                    <span class="text-slate-400 font-bold text-sm uppercase tracking-widest mb-1">Viernes</span>
                    <h3 class="text-3xl font-black text-slate-900 mb-6 text-slate-300 group-hover:text-orange-500 transition-colors">22</h3>
                    
                    <div class="mt-auto">
                        <p class="text-xs font-bold text-slate-400 uppercase mb-2">Propuesta:</p>
                        <p class="font-bold text-slate-700 italic">Pizza Party</p>
                        <div class="grid grid-cols-2 gap-2 mt-4">
                            <button class="bg-slate-100 hover:bg-orange-600 hover:text-white text-slate-600 p-2 rounded-xl text-[10px] font-bold uppercase transition-all">Elegir Menú</button>
                            <button class="bg-slate-100 hover:bg-slate-900 hover:text-white text-slate-600 p-2 rounded-xl text-[10px] font-bold uppercase transition-all">Vianda</button>
                        </div>
                    </div>
                </div>

            </div>

            <div class="mt-12 flex justify-center">
                <button class="bg-orange-600 text-white px-12 py-5 rounded-[2rem] font-black text-lg hover:bg-orange-700 transition-all shadow-xl shadow-orange-200 flex items-center gap-3">
                    <i class="ph ph-floppy-disk-back-fill text-2xl"></i> GUARDAR PLANIFICACIÓN
                </button>
            </div>
        </div>

        <footer class="h-14 bg-white border-t border-slate-200 px-10 flex items-center gap-6 text-[10px] font-bold uppercase text-slate-400 tracking-widest">
            <div class="flex items-center gap-2">
                <div class="w-2 h-2 bg-emerald-500 rounded-full"></div> Periodo de edición abierto
            </div>
            <div class="ml-auto">
                EduMenu v2.0 • Sistema de Gestión Nutricional
            </div>
        </footer>
    </main>

</body>
</html>