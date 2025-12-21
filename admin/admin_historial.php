<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduMenu | Historial de Actividad</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        
        .bg-dark-pro { background-color: #0f172a; } 
        .text-orange-main { color: #ea580c; }
        .border-orange-soft { border-color: rgba(234, 88, 12, 0.1); }

        /* Scrollbar estilo EduMenu */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #ea580c; border-radius: 10px; }

        .log-row:hover {
            background-color: rgba(248, 250, 252, 0.8);
        }
        
        .icon-box {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 10px;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 flex h-screen overflow-hidden">

    <?php include '../includes/sidebar_admin.php'; ?>

    <main class="flex-1 flex flex-col overflow-hidden">
        
        <header class="h-20 bg-white border-b border-slate-200 px-10 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-extrabold text-slate-900 uppercase tracking-tight">Registro de Auditoría</h1>
                <p class="text-sm text-slate-500">Historial completo de movimientos en la plataforma.</p>
            </div>
            
            <div class="flex items-center gap-4">
                <button class="bg-white border border-slate-200 text-slate-600 px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-slate-50 transition-all flex items-center gap-2">
                    <i class="ph ph-trash"></i> Limpiar Logs Antiguos
                </button>
            </div>
        </header>

        <section class="p-10 pb-6">
            <div class="flex flex-wrap gap-4 items-center justify-between bg-dark-pro p-4 rounded-3xl shadow-lg border border-slate-800">
                <div class="flex items-center gap-4 flex-1">
                    <div class="relative w-full max-w-md">
                        <i class="ph ph-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-500"></i>
                        <input type="text" placeholder="Buscar por usuario o acción..." class="w-full pl-12 pr-4 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white text-sm outline-none focus:border-orange-500 transition-all">
                    </div>
                    <select class="bg-slate-900 border border-slate-700 text-slate-300 text-sm rounded-xl px-4 py-2.5 outline-none focus:border-orange-500">
                        <option>Todas las acciones</option>
                        <option>Pagos</option>
                        <option>Menús</option>
                        <option>Asistencias</option>
                    </select>
                </div>
                <div class="text-slate-400 text-sm font-medium">
                    Mostrando <span class="text-orange-500">120</span> registros totales
                </div>
            </div>
        </section>

        <section class="flex-1 p-10 pt-0 overflow-y-auto">
            <div class="bg-white rounded-[2rem] border border-slate-200 shadow-sm overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            <th class="px-8 py-5 text-xs font-bold text-slate-400 uppercase tracking-widest">Tipo</th>
                            <th class="px-8 py-5 text-xs font-bold text-slate-400 uppercase tracking-widest">Fecha y Hora</th>
                            <th class="px-8 py-5 text-xs font-bold text-slate-400 uppercase tracking-widest">Usuario</th>
                            <th class="px-8 py-5 text-xs font-bold text-slate-400 uppercase tracking-widest">Acción Realizada</th>
                            <th class="px-8 py-5 text-xs font-bold text-slate-400 uppercase tracking-widest">Detalle del Movimiento</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr class="log-row transition-all">
                            <td class="px-8 py-5">
                                <div class="icon-box bg-blue-100 text-blue-600">
                                    <i class="ph ph-calendar-check-fill text-xl"></i>
                                </div>
                            </td>
                            <td class="px-8 py-5 text-sm text-slate-500 font-semibold">17/12 - 08:30:12</td>
                            <td class="px-8 py-5">
                                <span class="bg-slate-100 text-slate-700 px-3 py-1 rounded-lg text-xs font-bold uppercase">Admin (Sistemas)</span>
                            </td>
                            <td class="px-8 py-5 font-bold text-slate-800 text-sm">Carga Masiva Menú</td>
                            <td class="px-8 py-5 text-sm text-slate-500 italic">Actualización de platos para la semana 52 (Diciembre).</td>
                        </tr>

                        <tr class="log-row transition-all">
                            <td class="px-8 py-5">
                                <div class="icon-box bg-orange-100 text-orange-600">
                                    <i class="ph ph-fork-knife-fill text-xl"></i>
                                </div>
                            </td>
                            <td class="px-8 py-5 text-sm text-slate-500 font-semibold">17/12 - 09:15:45</td>
                            <td class="px-8 py-5">
                                <span class="bg-slate-100 text-slate-700 px-3 py-1 rounded-lg text-xs font-bold uppercase">Fam. Martinez</span>
                            </td>
                            <td class="px-8 py-5 font-bold text-slate-800 text-sm">Selección de Comida</td>
                            <td class="px-8 py-5 text-sm text-slate-500 italic">Alumno: Lucas Martinez - Opción: <span class="text-orange-600 font-bold">Vianda</span>.</td>
                        </tr>

                        <tr class="log-row transition-all">
                            <td class="px-8 py-5">
                                <div class="icon-box bg-red-100 text-red-600">
                                    <i class="ph ph-user-minus-fill text-xl"></i>
                                </div>
                            </td>
                            <td class="px-8 py-5 text-sm text-slate-500 font-semibold">17/12 - 09:20:02</td>
                            <td class="px-8 py-5">
                                <span class="bg-slate-100 text-slate-700 px-3 py-1 rounded-lg text-xs font-bold uppercase">Fam. Diaz</span>
                            </td>
                            <td class="px-8 py-5 font-bold text-slate-800 text-sm">Reporte de Ausencia</td>
                            <td class="px-8 py-5 text-sm text-slate-500 italic">Motivo: Enfermedad. No se debitará el costo del día.</td>
                        </tr>

                        <tr class="log-row transition-all">
                            <td class="px-8 py-5">
                                <div class="icon-box bg-emerald-100 text-emerald-600">
                                    <i class="ph ph-credit-card-fill text-xl"></i>
                                </div>
                            </td>
                            <td class="px-8 py-5 text-sm text-slate-500 font-semibold">16/12 - 18:45:30</td>
                            <td class="px-8 py-5">
                                <span class="bg-slate-100 text-slate-700 px-3 py-1 rounded-lg text-xs font-bold uppercase">Fam. Gomez</span>
                            </td>
                            <td class="px-8 py-5 font-bold text-slate-800 text-sm">Pago Recibido</td>
                            <td class="px-8 py-5 text-sm text-slate-500 italic">Monto: <span class="text-emerald-600 font-bold">$15.000</span> via MercadoPago (ID: 99827).</td>
                        </tr>

                        <tr class="log-row transition-all">
                            <td class="px-8 py-5">
                                <div class="icon-box bg-purple-100 text-purple-600">
                                    <i class="ph ph-user-gear-fill text-xl"></i>
                                </div>
                            </td>
                            <td class="px-8 py-5 text-sm text-slate-500 font-semibold">16/12 - 14:10:12</td>
                            <td class="px-8 py-5">
                                <span class="bg-slate-100 text-slate-700 px-3 py-1 rounded-lg text-xs font-bold uppercase">Admin (Secretaría)</span>
                            </td>
                            <td class="px-8 py-5 font-bold text-slate-800 text-sm">Edición Perfil Alumno</td>
                            <td class="px-8 py-5 text-sm text-slate-500 italic">Se actualizó la restricción alimentaria de "Sofia Gomez" (Celíaca).</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

</body>
</html>