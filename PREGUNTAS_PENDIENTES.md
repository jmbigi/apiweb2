# Preguntas Pendientes — Fase 1

1. **Creación de agrupaciones (solo superadmin)**: ¿Hay ya un usuario superadmin creado en web2 o necesitas que lo cree? ¿O está solo en la BD de `ios.faristol.net` y hay que migrarlo?

2. **Rutas Laravel para subdirectorios Flutter**: ¿Ruta tipo `Route::get('/visorweb2/{any?}', ...)` que sirva el `index.html` desde Laravel, o simplemente copiar el build a `public/visorweb2/` y que Apache lo sirva directamente?

3. **Login API para Control App demo**: ¿Usa la misma API de web2 (`/api/auth/login`) para loguearse, o son credenciales mock/fijas para la demo?

4. **Gráficos de la Control App**: ¿Tienes datos de ejemplo concretos para stats y gráficos, o los creo mock (ej: "12 miembros activos", "5 ensayos planificados", distribución por roles)?

5. **Migración a `music_scores`**: ¿Ejecuto `php artisan make:migration` ahora para agregar `agrupacion_id` y `uploaded_by`? ¿O prefieres SQL manual?

6. **Setlist PDF concatenation**: ¿Un setlist puede mezclar partituras globales + privadas de agrupación en el mismo PDF?