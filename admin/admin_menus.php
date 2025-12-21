<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduMenu | Planificador de Menús</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        
        .bg-dark-pro { background-color: #0f172a; } 
        .text-orange-main { color: #ea580c; }
        .bg-orange-main { background-color: #ea580c; }

        /* Scrollbar estilo EduMenu */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #ea580c; border-radius: 10px; }

        /* Estilo para los inputs de la tarjeta */
        .menu-input {
            width: 100%;
            padding: 10px 14px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            font-size: 0.875rem;
            transition: all 0.2s ease;
            outline: none;
        }
        .menu-input:focus {
            border-color: #ea580c;
            background-color: #ffffff;
            box-shadow: 0 0 0 4px rgba(234, 88, 12, 0.1);
        }

        .day-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .day-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 20px -5px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 flex h-screen overflow-hidden">

    <?php include '../includes/sidebar_admin.php'; ?>

    <main class="flex-1 flex flex-col overflow-hidden">
        
        <header class="h-20 bg-white border-b border-slate-200 px-10 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-extrabold text-slate-900 uppercase tracking-tight text-orange-main">Planificador de Menús</h1>
                <p class="text-sm text-slate-500">Diseña la propuesta gastronómica de la semana.</p>
            </div>
            
            <div class="flex items-center gap-6">
                <div class="flex items-center bg-slate-100 p-1.5 rounded-2xl border border-slate-200">
                    <button class="p-2 hover:bg-white hover:text-orange-600 rounded-xl transition-all"><i class="ph ph-caret-left-bold"></i></button>
                    <span class="px-4 text-sm font-bold text-slate-700">Semana del 18 de Dic</span>
                    <button class="p-2 hover:bg-white hover:text-orange-600 rounded-xl transition-all"><i class="ph ph-caret-right-bold"></i></button>
                </div>
                <button class="bg-dark-pro text-white px-6 py-3 rounded-2xl text-sm font-bold hover:bg-slate-800 transition-all flex items-center gap-2 shadow-lg">
                    <i class="ph ph-floppy-disk"></i> Guardar Menús
                </button>
            </div>
        </header>

        <section class="flex-1 p-10 overflow-y-auto">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-6 h-full">
                
                <div class="day-card bg-white rounded-[2.5rem] border border-slate-200 p-6 flex flex-col shadow-sm">
                    <div class="flex items-center justify-between mb-6">
                        <span class="text-orange-600 font-black text-lg">LUN 18</span>
                        <div class="w-8 h-8 bg-orange-100 text-orange-600 rounded-full flex items-center justify-center">
                            <i class="ph ph-sun-dim-fill"></i>
                        </div>
                    </div>
                    
                    <div class="space-y-4 flex-1">
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1 mb-1 block">Plato Principal</label>
                            <input type="text" class="menu-input font-bold text-slate-700" value="Milanesa c/ Puré">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1 mb-1 block">Postre</label>
                            <input type="text" class="menu-input" value="Flan con Dulce">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1 mb-1 block">Opción Veggie</label>
                            <input type="text" class="menu-input text-emerald-600 font-medium" value="Milanesa de Soja">
                        </div>
                    </div>
                    <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-slate-400">
                        <span>CALORÍAS: 450 kcal</span>
                        <i class="ph ph-info cursor-pointer hover:text-orange-600"></i>
                    </div>
                </div>

                <div class="day-card bg-white rounded-[2.5rem] border border-slate-200 p-6 flex flex-col shadow-sm">
                    <div class="flex items-center justify-between mb-6">
                        <span class="text-slate-800 font-black text-lg">MAR 19</span>
                    </div>
                    <div class="space-y-4 flex-1">
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1 mb-1 block">Plato Principal</label>
                            <input type="text" class="menu-input font-bold text-slate-700" value="Fideos con Tuco">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1 mb-1 block">Postre</label>
                            <input type="text" class="menu-input" value="Fruta Estacional">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1 mb-1 block">Opción Veggie</label>
                            <input type="text" class="menu-input text-emerald-600 font-medium" value="Fideos Integrales">
                        </div>
                    </div>
                    <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-slate-400">
                        <span>CALORÍAS: 380 kcal</span>
                        <i class="ph ph-info"></i>
                    </div>
                </div>

                <div class="day-card bg-white rounded-[2.5rem] border border-slate-200 p-6 flex flex-col shadow-sm">
                    <div class="flex items-center justify-between mb-6">
                        <span class="text-slate-800 font-black text-lg">MIE 20</span>
                    </div>
                    <div class="space-y-4 flex-1">
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1 mb-1 block">Plato Principal</label>
                            <input type="text" class="menu-input font-bold text-slate-700" value="Pollo al Horno">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1 mb-1 block">Postre</label>
                            <input type="text" class="menu-input" value="Gelatina">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1 mb-1 block">Opción Veggie</label>
                            <input type="text" class="menu-input text-emerald-600 font-medium" value="Bife de Seitán">
                        </div>
                    </div>
                    <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-slate-400">
                        <span>CALORÍAS: 410 kcal</span>
                        <i class="ph ph-info"></i>
                    </div>
                </div>

                <div class="day-card bg-white rounded-[2.5rem] border border-slate-200 p-6 flex flex-col shadow-sm">
                    <div class="flex items-center justify-between mb-6">
                        <span class="text-slate-800 font-black text-lg">JUE 21</span>
                    </div>
                    <div class="space-y-4 flex-1">
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1 mb-1 block">Plato Principal</label>
                            <input type="text" class="menu-input font-bold text-slate-700" value="Pastel de Papa">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1 mb-1 block">Postre</label>
                            <input type="text" class="menu-input" value="Helado">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1 mb-1 block">Opción Veggie</label>
                            <input type="text" class="menu-input text-emerald-600 font-medium" value="Pastel de Calabaza">
                        </div>
                    </div>
                    <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-slate-400">
                        <span>CALORÍAS: 520 kcal</span>
                        <i class="ph ph-info"></i>
                    </div>
                </div>

                <div class="day-card bg-dark-pro rounded-[2.5rem] p-6 flex flex-col shadow-xl">
                    <div class="flex items-center justify-between mb-6">
                        <span class="text-orange-600 font-black text-lg">VIE 22</span>
                        <div class="bg-orange-600 text-white px-2 py-1 rounded-lg text-[10px] font-black uppercase tracking-tighter">PIZZA DAY</div>
                    </div>
                    <div class="space-y-4 flex-1">
                        <div>
                            <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1 mb-1 block">Plato Principal</label>
                            <input type="text" class="menu-input bg-slate-800 border-slate-700 text-white font-bold focus:bg-slate-700" value="Pizza Party">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1 mb-1 block">Postre</label>
                            <input type="text" class="menu-input bg-slate-800 border-slate-700 text-white focus:bg-slate-700" value="Ensalada de Frutas">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1 mb-1 block">Opción Veggie</label>
                            <input type="text" class="menu-input bg-slate-800 border-slate-700 text-emerald-400 font-medium focus:bg-slate-700" value="Pizza Vegana">
                        </div>
                    </div>
                    <div class="mt-6 pt-4 border-t border-slate-800 flex items-center justify-between text-xs font-bold text-slate-500">
                        <span>ESPECIAL SEMANA</span>
                        <i class="ph ph-star-fill text-orange-500"></i>
                    </div>
                </div>

            </div>
        </section>

        <footer class="h-14 bg-white border-t border-slate-200 px-10 flex items-center gap-6 text-xs text-slate-400 font-medium">
            <div class="flex items-center gap-2">
                <div class="w-2 h-2 bg-emerald-500 rounded-full"></div>
                Menú publicado visible para familias
            </div>
            <div class="flex items-center gap-2">
                <div class="w-2 h-2 bg-orange-500 rounded-full animate-pulse"></div>
                Edición en tiempo real habilitada
            </div>
        </footer>
    </main>

</body>
</html>