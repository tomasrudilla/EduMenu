<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduMenu | Finanzas y Recaudación</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        
        .bg-dark-pro { background-color: #0f172a; } /* Negro/Azul profundo */
        .text-orange-main { color: #ea580c; }
        .bg-orange-main { background-color: #ea580c; }

        /* Scrollbar estilo EduMenu */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #ea580c; border-radius: 10px; }

        .finance-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .finance-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 flex h-screen overflow-hidden">

    <?php include '../includes/sidebar_admin.php'; ?>

    <main class="flex-1 flex flex-col overflow-hidden">
        
        <header class="h-20 bg-white border-b border-slate-200 px-10 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-extrabold text-slate-900 uppercase tracking-tight">Reporte Financiero</h1>
                <p class="text-sm text-slate-500">Corte al <?php echo date('d/m/Y'); ?></p>
            </div>
            
            <div class="flex items-center gap-4">
                <div class="bg-slate-100 p-1 rounded-xl flex">
                    <button class="px-4 py-2 text-xs font-bold bg-white text-orange-600 rounded-lg shadow-sm">MENSUAL</button>
                    <button class="px-4 py-2 text-xs font-bold text-slate-500 hover:text-slate-700">ANUAL</button>
                </div>
                <button class="bg-dark-pro text-white px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-slate-800 transition-all flex items-center gap-2">
                    <i class="ph ph-printer"></i> Imprimir Balance
                </button>
            </div>
        </header>

        <section class="p-10 pb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="finance-card bg-white p-6 rounded-[2rem] border border-slate-200 shadow-sm relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-emerald-50 rounded-bl-full -mr-10 -mt-10"></div>
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-2xl flex items-center justify-center">
                            <i class="ph ph-trend-up text-2xl font-bold"></i>
                        </div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Recaudado (Mes)</p>
                    </div>
                    <h3 class="text-3xl font-extrabold text-slate-900">$1.240.500</h3>
                    <p class="text-xs text-emerald-500 font-bold mt-2 flex items-center gap-1">
                        <i class="ph ph-caret-up"></i> +15.2% vs mes anterior
                    </p>
                </div>

                <div class="finance-card bg-white p-6 rounded-[2rem] border border-slate-200 shadow-sm relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-orange-50 rounded-bl-full -mr-10 -mt-10"></div>
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-12 h-12 bg-orange-100 text-orange-600 rounded-2xl flex items-center justify-center">
                            <i class="ph ph-warning-circle text-2xl font-bold"></i>
                        </div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Deuda Pendiente</p>
                    </div>
                    <h3 class="text-3xl font-extrabold text-slate-900">$320.000</h3>
                    <p class="text-xs text-orange-600 font-bold mt-2">12 Familias con saldo negativo</p>
                </div>

                <div class="finance-card bg-dark-pro p-6 rounded-[2rem] shadow-xl relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-orange-600/10 rounded-bl-full -mr-10 -mt-10"></div>
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-12 h-12 bg-orange-600 text-white rounded-2xl flex items-center justify-center">
                            <i class="ph ph-chart-pie-slice text-2xl font-bold"></i>
                        </div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Costo x Menú</p>
                    </div>
                    <h3 class="text-3xl font-extrabold text-white">$2.100</h3>
                    <p class="text-xs text-slate-400 mt-2">Margen operativo actual: <span class="text-orange-500 font-bold">54%</span></p>
                </div>
            </div>
        </section>

        <section class="flex-1 p-10 pt-0 overflow-y-auto">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2">
                        <i class="ph ph-receipt text-orange-600"></i> Últimos Pagos Ingresados
                    </h3>
                    <div class="relative">
                        <i class="ph ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="text" placeholder="Buscar por familia..." class="pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-orange-500 transition-all">
                    </div>
                </div>
                
                <table class="w-full text-left">
                    <thead>
                        <tr class="text-xs font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">
                            <th class="px-8 py-4">Fecha y Hora</th>
                            <th class="px-8 py-4">Familia</th>
                            <th class="px-8 py-4">Monto</th>
                            <th class="px-8 py-4">Método de Pago</th>
                            <th class="px-8 py-4 text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-8 py-5 text-sm text-slate-500 font-medium">Hoy, 10:30 hs</td>
                            <td class="px-8 py-5">
                                <span class="font-bold text-slate-800 text-sm">Gomez-Perez</span>
                            </td>
                            <td class="px-8 py-5">
                                <span class="text-emerald-600 font-extrabold">$15.000</span>
                            </td>
                            <td class="px-8 py-5 text-sm text-slate-600">
                                <div class="flex items-center gap-2">
                                    <i class="ph ph-device-mobile-speaker text-lg text-blue-500"></i> MercadoPago
                                </div>
                            </td>
                            <td class="px-8 py-5 text-center">
                                <span class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-lg text-xs font-bold uppercase">Aprobado</span>
                            </td>
                        </tr>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-8 py-5 text-sm text-slate-500 font-medium">Ayer, 14:20 hs</td>
                            <td class="px-8 py-5">
                                <span class="font-bold text-slate-800 text-sm">Lopez-Garcia</span>
                            </td>
                            <td class="px-8 py-5">
                                <span class="text-emerald-600 font-extrabold">$4.500</span>
                            </td>
                            <td class="px-8 py-5 text-sm text-slate-600">
                                <div class="flex items-center gap-2">
                                    <i class="ph ph-money text-lg text-amber-600"></i> Efectivo (Caja)
                                </div>
                            </td>
                            <td class="px-8 py-5 text-center">
                                <span class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-lg text-xs font-bold uppercase">Aprobado</span>
                            </td>
                        </tr>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-8 py-5 text-sm text-slate-500 font-medium">18/12, 09:00 hs</td>
                            <td class="px-8 py-5">
                                <span class="font-bold text-slate-800 text-sm">Martinez-Sosa</span>
                            </td>
                            <td class="px-8 py-5">
                                <span class="text-slate-600 font-extrabold">$12.000</span>
                            </td>
                            <td class="px-8 py-5 text-sm text-slate-600">
                                <div class="flex items-center gap-2">
                                    <i class="ph ph-bank text-lg text-slate-600"></i> Transferencia
                                </div>
                            </td>
                            <td class="px-8 py-5 text-center">
                                <span class="bg-amber-100 text-amber-700 px-3 py-1 rounded-lg text-xs font-bold uppercase">Pendiente</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

</body>
</html>