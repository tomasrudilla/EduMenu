<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduMenu | Panel de Administración</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        
        /* Personalización de colores */
        .bg-dark-pro { background-color: #0f172a; } /* Negro/Azul muy oscuro */
        .border-orange-soft { border-color: rgba(234, 88, 12, 0.1); }
        
        /* Scrollbar personalizado */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #ea580c; border-radius: 10px; }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 flex h-screen overflow-hidden">

   <?php include '../includes/sidebar_admin.php'; ?>

    <main class="flex-1 flex flex-col overflow-hidden">
        
        <header class="h-20 bg-white border-b border-slate-200 px-10 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-extrabold text-slate-900 uppercase tracking-tight">Panel de Alumnos</h1>
                <p class="text-sm text-slate-500">Gestiona las selecciones y estados de cuenta hoy.</p>
            </div>
            
            <div class="flex items-center gap-4">
                <button class="flex items-center gap-2 bg-slate-100 px-4 py-2 rounded-xl text-sm font-bold text-slate-600 hover:bg-slate-200 transition-all">
                    <i class="ph ph-download-simple"></i> Exportar Reporte
                </button>
                <button class="flex items-center gap-2 bg-orange-600 text-white px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-orange-700 transition-all shadow-lg shadow-orange-100">
                    <i class="ph ph-plus-circle"></i> Nuevo Alumno
                </button>
            </div>
        </header>

        <section class="p-10 pb-4">
            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label class="text-xs font-bold text-orange-600 uppercase mb-2 block tracking-widest">Búsqueda Rápida</label>
                    <div class="relative">
                        <i class="ph ph-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="text" placeholder="Nombre del alumno o familia..." class="w-full pl-12 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-orange-500 outline-none transition-all">
                    </div>
                </div>
                <div class="w-48">
                    <label class="text-xs font-bold text-slate-500 uppercase mb-2 block tracking-widest">Año</label>
                    <select class="w-full p-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-orange-500 outline-none">
                        <option>Todos</option>
                        <option>1ero</option><option>2do</option><option>3ero</option><option>4to</option><option>5to</option><option>6to</option>
                    </select>
                </div>
                <div class="w-48">
                    <label class="text-xs font-bold text-slate-500 uppercase mb-2 block tracking-widest">Estado</label>
                    <select class="w-full p-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-orange-500 outline-none">
                        <option>Todos</option>
                        <option>Comen Menú</option>
                        <option>Traen Vianda</option>
                        <option>Con Deuda</option>
                    </select>
                </div>
            </div>
        </section>

        <section class="flex-1 p-10 pt-0 overflow-y-auto">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest">Alumno / Familia</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest">Curso</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest">Elección Hoy</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest">Cuenta</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaAlumnos" class="divide-y divide-slate-100">
                        </tbody>
                </table>
            </div>
        </section>
    </main>

    <script>
        // DATA HARDCODEADA (Simulando lo que vendría de MySQL)
        const ALUMNOS = [
            { id: 1, nombre: "Tomás Gomez", familia: "Fam. Gomez-Perez", curso: "5to Año A", seleccion: "menu", saldo: 1200 },
            { id: 2, nombre: "Lucia Fernandez", familia: "Fam. Fernandez", curso: "3er Año B", seleccion: "vianda", saldo: -4500 },
            { id: 3, nombre: "Marcos Diaz", familia: "Fam. Diaz-Varela", curso: "1er Año A", seleccion: "ausente", saldo: 0 },
            { id: 4, nombre: "Sofia Rodriguez", familia: "Fam. Rodriguez", curso: "2do Año B", seleccion: "menu", saldo: -8200 },
            { id: 5, nombre: "Mateo Lopez", familia: "Fam. Lopez", curso: "5to Año A", seleccion: "menu", saldo: 500 },
            { id: 6, nombre: "Valentina M.", familia: "Fam. Martinez", curso: "4to Año C", seleccion: "retiro", saldo: -1500 }
        ];

        const tabla = document.getElementById('tablaAlumnos');

        function renderTabla() {
            tabla.innerHTML = ALUMNOS.map(alumno => {
                // Estilo para el Badge de Selección
                let badgeSel = '';
                switch(alumno.seleccion) {
                    case 'menu': badgeSel = `<span class="bg-orange-100 text-orange-700 px-3 py-1 rounded-lg text-xs font-bold flex items-center gap-1 w-fit"><i class="ph ph-bowl-food"></i> MENÚ</span>`; break;
                    case 'vianda': badgeSel = `<span class="bg-slate-100 text-slate-600 px-3 py-1 rounded-lg text-xs font-bold flex items-center gap-1 w-fit"><i class="ph ph-backpack"></i> VIANDA</span>`; break;
                    case 'ausente': badgeSel = `<span class="bg-red-100 text-red-600 px-3 py-1 rounded-lg text-xs font-bold flex items-center gap-1 w-fit"><i class="ph ph-x-circle"></i> AUSENTE</span>`; break;
                    case 'retiro': badgeSel = `<span class="bg-purple-100 text-purple-600 px-3 py-1 rounded-lg text-xs font-bold flex items-center gap-1 w-fit"><i class="ph ph-person-simple-run"></i> RETIRO</span>`; break;
                }

                // Estilo para el Saldo
                const saldoClase = alumno.saldo < 0 ? 'text-red-500 font-bold' : 'text-emerald-500 font-bold';
                const saldoTexto = alumno.saldo < 0 ? `Debe $${Math.abs(alumno.saldo)}` : (alumno.saldo == 0 ? 'Al día' : `Favor $${alumno.saldo}`);

                return `
                    <tr class="hover:bg-slate-50/80 transition-all">
                        <td class="px-6 py-5">
                            <p class="font-bold text-slate-800">${alumno.nombre}</p>
                            <p class="text-xs text-slate-400">${alumno.familia}</p>
                        </td>
                        <td class="px-6 py-5 text-sm text-slate-600 font-medium">${alumno.curso}</td>
                        <td class="px-6 py-5">${badgeSel}</td>
                        <td class="px-6 py-5 text-sm">
                            <span class="${saldoClase}">${saldoTexto}</span>
                        </td>
                        <td class="px-6 py-5 text-center">
                            <div class="flex justify-center gap-2">
                                <button class="p-2 hover:bg-orange-100 hover:text-orange-600 rounded-xl transition-all text-slate-400" title="Editar"><i class="ph ph-pencil-simple-line text-lg"></i></button>
                                <button class="p-2 hover:bg-blue-100 hover:text-blue-600 rounded-xl transition-all text-slate-400" title="Ver Historial"><i class="ph ph-eye text-lg"></i></button>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        renderTabla();
    </script>
</body>
</html>