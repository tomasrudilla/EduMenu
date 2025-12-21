<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduMenu | Facturas y Pagos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-thumb { background: #ea580c; border-radius: 10px; }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 flex h-screen overflow-hidden">

    <?php include '../includes/sidebar_familia.php'; ?>

    <main class="flex-1 flex flex-col overflow-hidden">
        
        <header class="h-20 bg-white border-b border-slate-200 px-10 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-extrabold text-slate-900 uppercase tracking-tight">Billetera EduMenu</h1>
                <p class="text-sm text-slate-500">Gestioná tus pagos y consultá el historial de consumos.</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-xs font-bold text-slate-400 uppercase">Estado:</span>
                <span class="bg-red-100 text-red-600 px-3 py-1 rounded-full text-[10px] font-black uppercase">Con Deuda</span>
            </div>
        </header>

        <div class="flex-1 p-10 overflow-y-auto">
            
            <div class="relative overflow-hidden bg-[#0f172a] rounded-[2.5rem] p-10 mb-10 text-white shadow-2xl shadow-orange-900/10">
                <div class="absolute top-0 right-0 w-64 h-64 bg-orange-600/10 rounded-full -mr-32 -mt-32 blur-3xl"></div>
                <div class="relative z-10 flex flex-col md:flex-row justify-between items-center gap-8">
                    <div>
                        <p class="text-orange-500 font-bold uppercase tracking-[0.2em] text-xs mb-2">Saldo Total Pendiente</p>
                        <h2 class="text-6xl font-black">$4.500</h2>
                        <div class="flex items-center gap-2 mt-4 text-slate-400">
                            <i class="ph ph-calendar-blank"></i>
                            <span class="text-sm font-medium">Próximo vencimiento: <strong>20 Dic, 2025</strong></span>
                        </div>
                    </div>
                    <div class="flex gap-4 w-full md:w-auto">
                        <button class="flex-1 md:flex-none bg-orange-600 hover:bg-orange-700 text-white px-8 py-4 rounded-2xl font-extrabold text-lg transition-all shadow-lg shadow-orange-600/20 flex items-center justify-center gap-2">
                            <i class="ph ph-lightning-fill"></i> Pagar Ahora
                        </button>
                        <button class="p-4 bg-slate-800 hover:bg-slate-700 text-white rounded-2xl transition-all">
                            <i class="ph ph-download-simple text-2xl"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-extrabold text-slate-800">Últimos Movimientos</h3>
                <div class="flex gap-2">
                    <button class="px-4 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-500 hover:bg-slate-50 transition-all">Todo</button>
                    <button class="px-4 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-500 hover:bg-slate-50 transition-all">Pagos</button>
                    <button class="px-4 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-500 hover:bg-slate-50 transition-all">Consumos</button>
                </div>
            </div>

            <div class="space-y-4">
                
                <div class="bg-white p-6 rounded-3xl border-2 border-orange-100 flex items-center justify-between hover:shadow-md transition-all group">
                    <div class="flex items-center gap-5">
                        <div class="w-14 h-14 bg-orange-50 text-orange-600 rounded-2xl flex items-center justify-center text-2xl shadow-inner">
                            <i class="ph ph-warning-circle-fill"></i>
                        </div>
                        <div>
                            <p class="font-extrabold text-slate-800 group-hover:text-orange-600 transition-colors">Adelanto Diciembre (Saldo Inicial)</p>
                            <p class="text-xs text-slate-400 font-medium">Referencia: TRX-9982 • 15 de Dic, 2025</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-8">
                        <div class="text-right">
                            <p class="text-xl font-black text-slate-800">$4.500</p>
                            <span class="text-[10px] font-black uppercase text-red-500 bg-red-50 px-2 py-0.5 rounded-md">Pendiente</span>
                        </div>
                        <i class="ph ph-caret-right text-slate-300 text-xl"></i>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-3xl border border-slate-100 flex items-center justify-between hover:shadow-md transition-all group opacity-80 hover:opacity-100">
                    <div class="flex items-center gap-5">
                        <div class="w-14 h-14 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center text-2xl">
                            <i class="ph ph-check-circle-fill"></i>
                        </div>
                        <div>
                            <p class="font-extrabold text-slate-800">Consumos Noviembre 2025</p>
                            <p class="text-xs text-slate-400 font-medium">Factura: #A000-1239 • 01 de Dic, 2025</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-8">
                        <div class="text-right">
                            <p class="text-xl font-black text-slate-800">$36.000</p>
                            <span class="text-[10px] font-black uppercase text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md">Pagado</span>
                        </div>
                        <i class="ph ph-caret-right text-slate-300 text-xl"></i>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-3xl border border-slate-100 flex items-center justify-between hover:shadow-md transition-all group opacity-80 hover:opacity-100">
                    <div class="flex items-center gap-5">
                        <div class="w-14 h-14 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center text-2xl">
                            <i class="ph ph-check-circle-fill"></i>
                        </div>
                        <div>
                            <p class="font-extrabold text-slate-800">Consumos Octubre 2025</p>
                            <p class="text-xs text-slate-400 font-medium">Factura: #A000-1150 • 01 de Nov, 2025</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-8">
                        <div class="text-right">
                            <p class="text-xl font-black text-slate-800">$31.200</p>
                            <span class="text-[10px] font-black uppercase text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md">Pagado</span>
                        </div>
                        <i class="ph ph-caret-right text-slate-300 text-xl"></i>
                    </div>
                </div>

            </div>
        </div>

        <footer class="h-16 bg-white border-t border-slate-200 px-10 flex items-center justify-between">
            <p class="text-xs text-slate-400 font-medium flex items-center gap-2">
                <i class="ph ph-info text-orange-500"></i> ¿Tenés dudas con un cargo? Contactanos al soporte.
            </p>
            <div class="flex gap-4">
                <a href="#" class="text-xs font-bold text-orange-600 hover:underline">Descargar Listado Completo (PDF)</a>
            </div>
        </footer>
    </main>

</body>
</html>