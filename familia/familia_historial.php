<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduMenu | Historial de Comidas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-thumb { background: #ea580c; border-radius: 10px; }
        
        .month-divider {
            position: relative;
            display: flex;
            align-items: center;
            margin: 2rem 0 1rem 0;
        }
        .month-divider::after {
            content: "";
            flex: 1;
            height: 1px;
            background: #e2e8f0;
            margin-left: 1rem;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 flex h-screen overflow-hidden">

    <?php include '../includes/sidebar_familia.php'; ?>

    <main class="flex-1 flex flex-col overflow-hidden">
        
        <header class="h-20 bg-white border-b border-slate-200 px-10 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-extrabold text-slate-900 uppercase tracking-tight">Historial de Comidas</h1>
                <p class="text-sm text-slate-500">Resumen detallado de la alimentación de tu hijo en 2025.</p>
            </div>
            <div class="flex items-center gap-4">
                <select class="bg-slate-100 border-none rounded-xl px-4 py-2 text-sm font-bold text-slate-600 focus:ring-2 focus:ring-orange-500">
                    <option>Ciclo Lectivo 2025</option>
                    <option>Ciclo Lectivo 2024</option>
                </select>
            </div>
        </header>

        <div class="flex-1 p-10 overflow-y-auto">
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                <div class="bg-white p-6 rounded-[2rem] border border-slate-200 shadow-sm flex items-center gap-5">
                    <div class="w-12 h-12 bg-orange-100 text-orange-600 rounded-2xl flex items-center justify-center text-2xl">
                        <i class="ph ph-chart-line-up-bold"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Inversión Total</p>
                        <h4 class="text-xl font-black text-slate-800">$117.000</h4>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-[2rem] border border-slate-200 shadow-sm flex items-center gap-5">
                    <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center text-2xl">
                        <i class="ph ph-bowl-food-bold"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Menús Consumidos</p>
                        <h4 class="text-xl font-black text-slate-800">26 Platos</h4>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-[2rem] border border-slate-200 shadow-sm flex items-center gap-5">
                    <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-2xl flex items-center justify-center text-2xl">
                        <i class="ph ph-check-circle-bold"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Asistencia</p>
                        <h4 class="text-xl font-black text-slate-800">98%</h4>
                    </div>
                </div>
            </div>

            <div class="month-divider">
                <span class="text-sm font-black text-orange-600 uppercase tracking-widest">Noviembre</span>
            </div>

            <div class="space-y-4">
                <div class="bg-white p-6 rounded-3xl border border-slate-200 flex items-center justify-between hover:border-orange-200 transition-all group">
                    <div class="flex items-center gap-5">
                        <div class="w-12 h-12 bg-slate-50 text-slate-400 rounded-2xl flex items-center justify-center text-xl group-hover:bg-orange-50 group-hover:text-orange-600 transition-colors">
                            <i class="ph ph-calendar-blank"></i>
                        </div>
                        <div>
                            <p class="font-extrabold text-slate-800">Semana del 20/11 al 24/11</p>
                            <div class="flex items-center gap-3 mt-1">
                                <span class="text-xs font-medium text-slate-500 flex items-center gap-1"><i class="ph ph-fork-knife"></i> 3 Menús</span>
                                <span class="text-xs font-medium text-slate-500 flex items-center gap-1"><i class="ph ph-backpack"></i> 2 Viandas</span>
                            </div>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-black text-slate-800">$13.500</p>
                        <p class="text-[10px] font-bold text-emerald-500 uppercase">Procesado</p>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-3xl border border-slate-200 flex items-center justify-between hover:border-orange-200 transition-all group">
                    <div class="flex items-center gap-5">
                        <div class="w-12 h-12 bg-slate-50 text-slate-400 rounded-2xl flex items-center justify-center text-xl group-hover:bg-orange-50 group-hover:text-orange-600 transition-colors">
                            <i class="ph ph-calendar-blank"></i>
                        </div>
                        <div>
                            <p class="font-extrabold text-slate-800">Semana del 13/11 al 17/11</p>
                            <div class="flex items-center gap-3 mt-1">
                                <span class="text-xs font-medium text-slate-500 flex items-center gap-1"><i class="ph ph-fork-knife"></i> 5 Menús</span>
                            </div>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-black text-slate-800">$22.500</p>
                        <p class="text-[10px] font-bold text-emerald-500 uppercase">Procesado</p>
                    </div>
                </div>
            </div>

            <div class="month-divider">
                <span class="text-sm font-black text-slate-400 uppercase tracking-widest">Octubre</span>
            </div>

            <div class="space-y-4">
                <div class="bg-[#0f172a] p-8 rounded-[2.5rem] text-white flex items-center justify-between shadow-xl relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-orange-600/10 rounded-full -mr-16 -mt-16 blur-2xl"></div>
                    <div class="flex items-center gap-6">
                        <div class="w-16 h-16 bg-orange-600 rounded-2xl flex items-center justify-center text-3xl shadow-lg shadow-orange-600/20">
                            <i class="ph ph-article-ny-times"></i>
                        </div>
                        <div>
                            <p class="text-orange-500 font-bold uppercase tracking-widest text-[10px] mb-1">Resumen Mensual Cargado</p>
                            <h4 class="text-2xl font-black">Octubre 2025</h4>
                            <p class="text-slate-400 text-sm mt-1">Total: 18 Menús consumidos • 2 Ausencias justificadas</p>
                        </div>
                    </div>
                    <div class="text-right relative z-10">
                        <p class="text-3xl font-black text-white">$81.000</p>
                        <button class="text-orange-500 font-bold text-xs hover:text-orange-400 flex items-center gap-1 ml-auto mt-2">
                            VER DETALLE <i class="ph ph-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </div>

        </div>

        <footer class="h-14 bg-white border-t border-slate-200 px-10 flex items-center justify-between text-xs text-slate-400 font-medium">
            <p>© 2025 EduMenu - Sistema de Gestión de Comedores</p>
            <div class="flex gap-6">
                <a href="#" class="hover:text-orange-600 transition-colors">Descargar Reporte Anual (Excel)</a>
            </div>
        </footer>
    </main>

</body>
</html>