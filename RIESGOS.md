# Análisis de Riesgos — Faristol: Actualización para Agrupaciones

> Presupuesto: 100h totales | Versión: Julio 2026
> Basado en investigación del código fuente existente (Laravel + Flutter).

---

## 1. Riesgos Técnicos

### R1 — Backup iCloud de datos offline (riesgo preexistente, no del Setlist)

| | |
|---|---|
**Probabilidad** | Muy baja
**Impacto** | Muy bajo
**Fuente** | Investigación de `hive_flutter` + `path_provider` en iOS

**Realidad:** El Setlist guarda solo **JSON de referencias** (kilobytes). No guarda los PDFs. Los PDFs ya están almacenados en el espacio Personal Music Score / Offline, que es funcionalidad **existente** de la app desde antes de este proyecto.

**El riesgo real (muy pequeño):**
- La app actual guarda sus datos (incluyendo PDFs offline) en la carpeta `Documents/` del dispositivo iOS
- Por defecto, iOS incluye esa carpeta en los backups de iCloud
- Esto NO es un error, no causa fallos, no afecta al rendimiento
- Es simplemente que un usuario con muchos PDFs offline podría ver su espacio de iCloud algo más ocupado
- Ningún usuario ha reportado esto como problema

**¿Afecta esto al proyecto actual?** No. El Setlist no introduce almacenamiento nuevo significativo. Y el comportamiento de backup iOS es el mismo que ya tiene la app desde su versión 1.0.

**Mitigación:** Si en el futuro se quisiera optimizar, es tan simple como cambiar el directorio de Hive en `main.dart`. Pero no es necesario ni requerido ahora.

---

### R2 — Polimorfismo de `music_scores`

| | |
|---|---|
**Probabilidad** | Baja
**Impacto** | Medio
**Fuente** | Investigación de modelos y migraciones existentes

**Hallazgos del código existente:**
- El patrón polimórfico ya existe en `files_s3_s` (tabla `fileable_id` + `fileable_type`)
- `MusicScore` ya tiene relaciones con múltiples tablas vía pivotes (`fk_music_score_composer`, `fk_music_score_instrument`, etc.)
- La complejidad adicional de agregar `ensemble_id` como FK opcional es baja

**Mitigación:**
- Seguir el mismo patrón de `files_s3_s` para la relación polimórfica
- No modificar consultas existentes de `music_scores` — solo agregar la nueva relación
- Tiempo estimado: **1 hora**

---

### R3 — Control App (Flutter Windows 11)

| | |
|---|---|
**Probabilidad** | Alta
**Impacto** | Medio
**Fuente** | Análisis de dependencias Flutter para Windows

**Hallazgos del código existente:**

La app móvil actual **NO PUEDE compilarse para Windows** sin cambios. Problemas identificados:

| Paquete | Problema en Windows | Solución |
|---------|--------------------|----------|
| `google_mobile_ads` ^6.0.0 | **Error de compilación** — no existe para Windows | **Eliminar** — la Control App no necesita anuncios |
| `screenshot_callback` ^3.0.1 | **Error de compilación** — solo Android/iOS | **Eliminar** — no aplica en escritorio |
| `flutter_screenshot_detect` ^0.1.7 | **Error de compilación** — solo Android/iOS | **Eliminar** — no aplica en escritorio |
| `webview_flutter` ^4.8.0 | **Error de compilación** — no existe para Windows | Reemplazar por `webview_flutter_windows` o eliminar |
| `fluttertoast` ^8.2.8 | **Error en tiempo de ejecución** — `showToast()` no soporta Windows (62 usos) | Migrar a `FToast` o usar SnackBar |

**Aclaración importante:** La Control App es una **aplicación nueva**, no un port de la app móvil. No necesita anuncios, detección de capturas ni WebView. El desarrollo debe comenzar con `flutter create --platforms=windows` y solo incluir las dependencias necesarias para gestión (HTTP, charts, PDF, etc.).

**Mitigación:**
- Crear proyecto nuevo con `flutter create --platforms=windows .`
- No incluir `google_mobile_ads`, `screenshot_callback`, `flutter_screenshot_detect`
- Usar `FToast` o `SnackBar` para notificaciones (no `Fluttertoast.showToast()`)
- `pdfx` es compatible con Windows (requiere `flutter pub run pdfx:install_windows`)
- Tiempo estimado de configuración inicial: **4 horas**

---

### R4 — Migración de web2 a producción en 3 días

| | |
|---|---|
**Probabilidad** | Media
**Impacto** | Alto
**Fuente** | Análisis de 52 migraciones existentes

**Hallazgos del código existente:**
- **Todas** las 52 migraciones tienen método `down()` — totalmente reversibles
- Patrón consistente: 98% usan clases anónimas (`return new class extends Migration`)
- Las migraciones nuevas propuestas son solo 4 tablas: `ensembles`, `ensemble_user`, `ensemble_score`, `rehearsals`
- El `DatabaseSeeder.php` usa transacciones con `upsert()` para idempotencia

**Mitigación:**
- Seguir el mismo patrón existente (clase anónima, `up()` + `down()`)
- Migraciones nuevas no modifican tablas existentes — son tablas nuevas
- Probar migración en web2 primero: `php artisan migrate --database=web2`
- Tener script de rollback listo: `php artisan migrate:rollback --database=web2`
- La migración en producción será 4 tablas nuevas = **~10 minutos**, no 3 días
- Los 3 días son para el proceso completo (desarrollo + pruebas + publicación), no para la migración en sí

---

### R5 — Tiempo de revisión en Apple App Store

| | |
|---|---|
**Probabilidad** | Alta
**Impacto** | Medio
**Fuente** | Historial de rechazo previo (Mayo 2024)

**Hallazgos del código existente:**

| Archivo | Hallazgo |
|---------|----------|
| `ios/Runner/Info.plist` | `ITSAppUsesNonExemptEncryption = false` — sin problemas de crypto |
| `Observaciones_Apple/Submit_02_may_rev...odt` | Rechazo anterior (v1.1) por: pantalla en blanco, IAP faltantes, falta eliminación de cuenta |
| App actual | Ya publicada y funcional (v1.9.3) — los problemas anteriores están resueltos |

**Mitigación:**
- La app ya está publicada, las actualizaciones tienen revisiones más rápidas (usualmente 24-48h)
- Usar **TestFlight** para pruebas mientras tanto (permite distribución sin aprobación)
- La actualización de Faristol App es incremental (nueva sección Ensembles), no un cambio radical
- Plan de contingencia: si Apple rechaza, publicar solo Android mientras se corrige
- Tiempo estimado de revisión típico: **24-72h** (apps ya publicadas)

---

## 2. Riesgos de Gestión

### R6 — 100h pueden ser insuficientes

| | |
|---|---|
**Probabilidad** | Media
**Impacto** | Alto

**Mitigación:**
- 5h de margen ya incluidas en el presupuesto
- Priorizar estrictamente el alcance definido (19 RFs)
- No añadir funcionalidades no solicitadas
- Si se requiere más tiempo, congelar y evaluar追加 presupuesto

### R7 — Retraso en feedback de Biblioscores/Faristol

| | |
|---|---|
**Probabilidad** | Alta
**Impacto** | Medio

**Mitigación:**
- Establecer fechas límite claras para feedback
- El prototipo es para validar, no para iterar sin límite
- Documentar decisiones pendientes como "pendiente de confirmación"

### R8 — Dependencia de Roberto o Quico para resolver dudas técnicas

| | |
|---|---|
**Probabilidad** | Media
**Impacto** | Medio

**Mitigación:**
- WEB2 no tiene archivo `.env` real — solo `env.example`. Se necesitarán credenciales de Roberto/Quico para la BD
- Tiempo estimado de configuración: **2 horas** si las credenciales están disponibles
- Documentar todo lo posible durante el prototipo para minimizar consultas

### R9 — Problemas de acceso a web2.faristol.net

| | |
|---|---|
**Probabilidad** | Baja
**Impacto** | Alto

**Mitigación:**
- Confirmar acceso a web2.faristol.net y BD web2 **antes** de empezar el prototipo
- Tener entorno local como respaldo (dump de web2 en local)

---

## 3. Riesgos de Producto

### R10 — Premium lógico conflictivo con suscripciones existentes

| | |
|---|---|
**Probabilidad** | Baja
**Impacto** | Medio

**Mitigación:**
- No toca `subscription_plan` ni `subscribed_user` — es verificación en endpoint `check-subscription`
- No requiere migración de BD
- Tiempo estimado de implementación: **2 horas**

### R11 — Roles de agrupación solapados con Laratrust

| | |
|---|---|
**Probabilidad** | Baja
**Impacto** | Medio

**Mitigación:**
- Roles de agrupación son independientes (`ensemble_user.role`)
- No se mezclan con `role_user` de Laratrust
- Ya existen roles similares implementados en otras apps Laravel sin conflictos

### R12 — BD web2 desactualizada respecto a producción

| | |
|---|---|
**Probabilidad** | Media
**Impacto** | Bajo

**Mitigación:**
- Usar dump lo más reciente posible
- El prototipo valida comportamiento, no datos exactos
- Diferencias documentadas no afectan el desarrollo

### R13 — Cambios requeridos por Google Play / App Store

| | |
|---|---|
**Probabilidad** | Baja
**Impacto** | Medio

**Mitigación:**
- Revisión de políticas antes de implementar
- No se introducen funcionalidades no estándar
- La app ya está publicada en ambas tiendas

### R14 — Validación experimental de PDFs puede rechazar partituras legítimas

| | |
|---|---|
**Probabilidad** | Media
**Impacto** | Alto

**Mitigación:**
- Validación exclusiva de magic bytes (`%PDF-`) y búsqueda de `/Encrypt` (sin dependencias externas)
- Flag de configuración para activar/desactivar (por defecto desactivada en producción)
- Si genera falsos positivos, se revertirá inmediatamente sin horas extra presupuestadas
- Al no estar presupuestada, no se dedicarán horas adicionales; se documentará claramente como riesgo experimental

---

## 4. Matriz de Riesgos

```
Alto  │     R5(Store)  R7(feedback)    R1(iOS)  R4(migración)
      │                                          R6(100h)
Medio │  R8(deps)  R12(web2)            R3(Win)
      │
Bajo  │  R2(polimorf)  R10(premium)     R9(acceso web2)
      │  R11(roles)  R13(tiendas)
      │
      └─────────────────────────────────────────────
         Baja           Media           Alta
                  PROBABILIDAD
```

## 5. Plan de Contingencia

| # | Escenario | Acción | Tiempo estimado |
|---|-----------|--------|-----------------|
| 1 | **iOS Setlist no funciona offline** | Usar `getApplicationSupportDirectory()` en vez del default de Hive | 2h |
| 2 | **Control App no compila en Windows** | Crear proyecto nuevo solo con dependencias necesarias (sin ads, sin screenshot) | 4h |
| 3 | **Migración falla en producción** | `php artisan migrate:rollback` — todas las migraciones tienen `down()` | 10min |
| 4 | **Apple rechaza actualización** | Publicar solo Android. Corregir y reenviar. Usar TestFlight mientras | Variable |
| 5 | **100h presupuestadas se agotan** | Congelar desarrollo. Entregar lo completado. Evaluar追加 | — |
| 6 | **web2 no accesible** | Usar dump local como respaldo | 2h |
| 7 | **Feedback no llega** | Continuar con lo validado hasta el momento. Marcar diferencias | — |
