# Preguntas Pendientes — Fase 1 (RESUELTAS)

> Resueltas el 2026-07-23

1. **Creación de agrupaciones (solo superadmin)**: ✅ **RESUELTO**. Ya existe superadmin en web2: `superadmin_test@email.com` (id=1, name="superadmin"). Creado via seeder.

2. **Rutas Laravel para subdirectorios Flutter**: ✅ **RESUELTO**. Apache sirve directamente los builds Flutter desde `public/visorweb2/` y `public/control-app/`. No se necesita ruta Laravel — es más eficiente, más estándar y no añade latencia del framework.

3. **Login API para Control App demo**: ✅ **RESUELTO**. Usa la misma API de web2 (`/api/auth/login`) con Sanctum. Ya implementado: acepta `cif` opcional para vincular a ensemble.

4. **Gráficos de la Control App**: ✅ **RESUELTO**. Usar datos reales del dashboard (miembros activos, ensayos planificados, scores, etc.) con un gráfico simple tipo `pie` o `bar` usando `fl_chart` u otra librería ligera.

5. **Migración a `music_scores`**: ✅ **RESUELTO**. NO ejecutar migraciones automatizadas. Las migraciones existentes ya están aplicadas (5 migraciones nuevas: `ensembles`, `ensemble_user`, `rehearsals`, `ensemble_folders`, + alter `music_scores`). Solo hacer cambios manuales con confirmación explícita.

6. **Setlist PDF concatenation**: ✅ **RESUELTO**. Sí puede mezclar partituras globales + privadas, siempre que el usuario tenga permisos (sea miembro activo de la agrupación propietaria de la partitura privada).