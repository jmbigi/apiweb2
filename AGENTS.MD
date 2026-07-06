# Reglas para el asistente AI

## Reglas de seguridad general
1. **Todo debe realizarse con extremo cuidado, de manera segura.** Este es un entorno de producción.
2. **Preguntar al usuario al menos 2 veces antes de eliminar o destruir cualquier cosa** (archivos, tablas, datos, etc.).
3. **No tomar decisiones peligrosas de manera automática.** Toda acción destructiva requiere confirmación explícita.
4. **Todo cambio debe probarse primero en `/tmp`** antes de implementarse en el proyecto real.
5. **Antes de comitear o realizar cualquier cambio en producción, probar de manera segura** (entorno de pruebas).
6. **Antes de comitear, pasar todos los tests:** `php artisan test --parallel` (o `vendor/bin/phpunit`).
7. **Los tests deben tener una cobertura cercana al 100%** del código nuevo/modificado.
8. **Protecciones contra código generado con IA:**
   - Todo código generado por IA debe ser revisado línea por línea por un humano antes de aceptarse.
   - No auto-aceptar ni auto-aplicar código generado.
   - Verificar seguridad, lógica y efectos secundarios antes de integrar.

## Reglas de estilo y calidad
9. **Ejecutar `vendor/bin/pint` (Laravel Pint)** antes de cada commit.
10. **Prohibido cometer `dd()`, `dump()`, `var_dump()`, `ray()`, `print_r()`** en código que se suba a producción.
11. **Agregar `declare(strict_types=1)`** en todos los archivos PHP nuevos.
12. **Validar siempre los request** usando Form Requests o `validate()` en los controladores.

## Reglas de base de datos y migraciones
13. **Toda migración debe ser reversible** (`up()` + `down()`) y probada en `/tmp` antes de ejecutar en producción.
14. **Nunca usar `DB::statement('DROP TABLE...')` sin confirmación explícita del usuario.**
15. **Hacer backup de la base de datos** antes de migraciones destructivas.
16. **No auto-ejecutar comandos destructivos** (`migrate:fresh`, `migrate:refresh`, `db:wipe`) sin preguntar al menos 2 veces.
17. **Usar `--force` solo con confirmación explícita del usuario.**

## Reglas de seguridad
18. **No exponer secretos:** verificar que ningún `.env`, clave API, token o contraseña quede en el código comiteado.
19. **Usar detección de N+1 queries** - revisar consultas Eloquent con `with()` para evitar lazy loading.

## Reglas de rutas y estructura
20. **Ejecutar `php artisan route:list --path=PATH`** para verificar rutas antes de agregar nuevas.
21. **Probar en `/tmp` antes de migrar cambios al proyecto real** (copiar archivos, probar, luego implementar).
22. **Actualizar este archivo cuando se agreguen nuevas reglas**, previa confirmación del usuario.
