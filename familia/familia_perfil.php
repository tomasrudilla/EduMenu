<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduMenu | Perfil del Alumno</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-thumb { background: #ea580c; border-radius: 10px; }

        .form-input {
            width: 100%;
            padding: 12px 16px;
            background-color: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 14px;
            font-size: 0.95rem;
            transition: all 0.2s ease;
            outline: none;
        }
        .form-input:focus {
            border-color: #ea580c;
            background-color: #ffffff;
            box-shadow: 0 0 0 4px rgba(234, 88, 12, 0.1);
        }
        .form-input:disabled {
            background-color: #f1f5f9;
            color: #94a3b8;
            cursor: not-allowed;
        }
        
        .section-card {
            background: white;
            border-radius: 2rem;
            border: 1px solid #e2e8f0;
            padding: 2.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 flex h-screen overflow-hidden">

    <?php include '../includes/sidebar_familia.php'; ?>

    <main class="flex-1 flex flex-col overflow-hidden">
        
        <header class="h-20 bg-white border-b border-slate-200 px-10 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-extrabold text-slate-900 uppercase tracking-tight">Perfil del Alumno</h1>
                <p class="text-sm text-slate-500">Mantené actualizada la información médica y de contacto.</p>
            </div>
            <div class="flex items-center gap-4 text-orange-600">
                <i class="ph ph-info text-xl"></i>
                <span class="text-xs font-bold uppercase tracking-wider">Última actualización: 10/12/2025</span>
            </div>
        </header>

        <div class="flex-1 p-10 overflow-y-auto">
            <div class="max-w-4xl mx-auto">
                
                <form action="#" method="POST" class="space-y-8">
                    
                    <div class="section-card">
                        <div class="flex items-center gap-3 mb-8 border-b border-slate-100 pb-4">
                            <div class="w-10 h-10 bg-orange-100 text-orange-600 rounded-xl flex items-center justify-center text-xl">
                                <i class="ph ph-student"></i>
                            </div>
                            <h3 class="text-lg font-extrabold text-slate-800">Datos Académicos</h3>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2 block ml-1">Nombre Completo</label>
                                <input type="text" class="form-input font-semibold" value="Tomás Gomez" disabled>
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2 block ml-1">Curso / División</label>
                                <input type="text" class="form-input font-semibold" value="5to Año - División A" disabled>
                            </div>
                        </div>
                    </div>

                    <div class="section-card border-l-4 border-l-orange-500">
                        <div class="flex items-center gap-3 mb-8 border-b border-slate-100 pb-4">
                            <div class="w-10 h-10 bg-red-100 text-red-600 rounded-xl flex items-center justify-center text-xl">
                                <i class="ph ph-first-aid-kit"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-extrabold text-slate-800">Salud y Restricciones</h3>
                                <p class="text-xs text-slate-400 font-medium">Información esencial para el equipo de cocina.</p>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <div>
                                <label class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 block ml-1">
                                    Alergias o Condiciones Médicas <span class="text-orange-600">*</span>
                                </label>
                                <textarea rows="3" class="form-input resize-none" placeholder="Ej: Celíaco, intolerancia a la lactosa, alérgico a los frutos secos...">Sin restricciones declaradas.</textarea>
                                <p class="text-[11px] text-slate-400 mt-2 flex items-center gap-1 italic">
                                    <i class="ph ph-warning-circle"></i> Si el alumno no tiene alergias, dejar como "Sin restricciones".
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="section-card">
                        <div class="flex items-center gap-3 mb-8 border-b border-slate-100 pb-4">
                            <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center text-xl">
                                <i class="ph ph-phone-call"></i>
                            </div>
                            <h3 class="text-lg font-extrabold text-slate-800">Contacto de Emergencia</h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2 block ml-1">Teléfono Principal</label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-medium">+54</span>
                                    <input type="text" class="form-input pl-12" value="9 11 1234 5678">
                                </div>
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2 block ml-1">Email para Reportes</label>
                                <input type="email" class="form-input" value="familia.gomez@email.com">
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-4 mt-10 pb-10">
                        <button type="button" class="px-8 py-4 text-sm font-bold text-slate-400 hover:text-slate-600 transition-colors">Descartar</button>
                        <button type="submit" class="bg-dark-pro bg-[#0f172a] text-white px-10 py-4 rounded-2xl font-extrabold hover:bg-slate-800 transition-all shadow-xl flex items-center gap-2">
                            <i class="ph ph-floppy-disk-back"></i> Guardar Cambios
                        </button>
                    </div>

                </form>
            </div>
        </div>

        <footer class="h-14 bg-white border-t border-slate-200 px-10 flex items-center justify-between text-[11px] text-slate-400 font-bold uppercase tracking-wider">
            <p>EduMenu Security Protocol v2.1</p>
            <p>Tus datos están protegidos y solo son visibles por la administración</p>
        </footer>
    </main>

</body>
</html>