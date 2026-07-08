# Checklist de tareas pendientes, problemas y dudas — ACTUALIZADO

_Revisado el 2026-07-08 tras aplicación de cambios_

---

## 🔴 Seguridad y configuración

- [NO hacer] **SSH key de www-data**: Está usando la key de root. Crear una deploy key dedicada para DesktopDemoWeb.
- [✅ HECHO] **`phpunit.xml` peligroso**: `DB_CONNECTION=sqlite` y `DB_DATABASE=:memory:` **descomentados** — tests usan BD en memoria.
- [✅ HECHO] **`.env.testing` creado**: Aísla los tests de la BD real con `DB_CONNECTION=sqlite`.
- [✅ NO APLICA] **`.env` con password en texto plano**: El archivo `.env` **no está trackeado** por git (`.gitignore` lo excluye). No hay riesgo.

---

## 🟡 Repositorio y limpieza

- [NO hacer] **Old repo `FaristolWeb`**: El proyecto original en GitLab ya no se usa. No se eliminó nada.
- [✅ HECHO] **Scripts huérfanos**: `certbot_testfile.sh` y `traer_visor_web.sh` **actualizados** para el dominio `web2.faristol.net` y la estructura actual.
- [✅ HECHO] **`public/backend-laravel-run.sh`**: **Eliminado** de `public/` y reemplazado por `backend-dev-server.sh` en la raíz.

---

## 🟡 Arquitectura y deuda técnica

- [✅ NO APLICA] **Migraciones**: Se verificaron 53 migraciones trackeadas por git. El checklist original decía "solo 1" pero es incorrecto — sí existen migraciones para todas las tablas.
- [✅ HECHO] **Tests**: 
  - `EnvironmentTest.php` — verifica aislamiento de `.env.testing`
  - `SafetyTest.php` — 14 tests sin necesidad de app (verifican archivos, .gitignore, config)
  - `SafetyAppTest.php` — 17 tests que requieren app (saltados sin pdo_sqlite)
  - Total: **72 tests (32 unit + 17 app-safety + 23 feature)**
  - **32 pasan, 17 saltados, 23 fallan** (sin pdo_sqlite)
- [IGNORAR] **Sin staging**: `APP_ENV=production` directo. No se modificó.
- [IGNORAR] **Traducción frágil**: No se modificó. Sigue usando Google Translate sin caché.
- [IGNORAR] **PDF síncrono**: No se modificó. Sigue siendo síncrono.
- [✅ HECHO] **Caché sin limpieza**: Creado comando `artisan cache:clear-pdfs` con TTL configurable y opción `--dry-run`.

---

## 🔵 Dudas del proyecto (de `PREGUNTAS_Y_RESPUESTAS.MD`)

- [IGNORAR] **Comunicación Flutter-Laravel**: No se modificó.
- [IGNORAR] **PayPal**: No se modificó.
- [IGNORAR] **Políticas de autorización**: No se modificó.
- [IGNORAR] **Registros huérfanos**: No se modificó.
- [✅ VERIFICADO] **`public/web/caching_server.py`**: El archivo no existe en el repo (directorio `public/web/` está en `.gitignore` por ser build de Flutter). No hay nada que actualizar.
- [IGNORAR] **Relación entre sitios**: No se modificó.
