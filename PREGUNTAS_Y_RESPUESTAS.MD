# PREGUNTAS Y RESPONSABILIDADES PENDIENTES

## 🔴 Arquitectura y datos

### 1. Migraciones de base de datos
Solo existe 1 migración (`create_site_visits_table`). Las ~35 tablas restantes (users, music_scores, composers, orders, etc.) no están versionadas.
- P: ¿Se crearon desde un dump de la DB de producción?
- P: Si hay que replicar el entorno desde cero, no hay manera con las migraciones actuales.
- Propuesta: Generar migraciones que reflejen el esquema actual.

### 2. Comunicación del visor Flutter con el backend
El visor Flutter corre en el navegador (`public/web/`), pero `routes/api.php` está vacío.
- P: ¿El visor se comunica con esta misma Laravel o con otro backend?
- P: Si habla con esta Laravel, ¿faltan rutas API por definir?
- P: Si usa otro backend, ¿cuál es y dónde está?

### 3. Relación entre los tres sitios
- **faristol.net** → WordPress (landing, blog, avisos legales)
- **web.faristol.net** → Laravel producción
- **web2.faristol.net** → Laravel demo2 (este proyecto)
- P: ¿Cómo se sincronizan? ¿Comparten DB?
- P: ¿web2 es copia exacta de web? ¿Con qué frecuencia se actualiza?
- P: ¿Hay planes de unificar o mantener separados?

---

## 🟡 Técnicas

### 4. Traducción dinámica con Google Translate no oficial
Se usa `Stichoza\GoogleTranslate` (scraper) en cada visita para traducir títulos, descripciones, etc.
- P: ¿Qué pasa si Google cambia su estructura y el scraper deja de funcionar?
- P: ¿No sería más robusto traducir una vez y cachear (ej. Redis, BD, o archivos)?
- Impacto: Cada request hace 5-8 llamadas HTTP externas antes de renderizar.

### 5. Tests automatizados
PHPUnit está instalado pero no hay ni un solo test.
- El `AGENTS.MD` exige pasar tests antes de cada commit, pero no existen.
- P: ¿Se quiere empezar a escribir tests? ¿Cobertura mínima deseada?

### 6. Sin entorno de desarrollo/staging
`APP_ENV=production` con `APP_DEBUG=false` en el día a día.
- P: Cualquier error no intencional (un `dd()` olvidado, una excepción sin capturar) se ve en producción como 500.
- Propuesta: Tener al menos un entorno de staging o usar `APP_DEBUG=true` condicional.

### 7. Generación de PDF síncrona
`MockUpController@generatePdf` lanza Chrome headless y bloquea la respuesta hasta terminar.
- P: ¿Si varios usuarios piden PDF a la vez, el servidor puede degradarse?
- Propuesta: Usar colas (Laravel Queue) con Redis o base de datos para procesar PDFs en segundo plano.

---

## 🟢 Menores / Observaciones

### 8. Registros huérfanos en `files_s3_s`
AGENTS.MD menciona 353 registros que no lee la app.
- P: ¿Se pueden limpiar o hay algún proceso que aún los necesite?

### 9. Caché de PDFs e imágenes sin limpieza
`public/cache/` acumula archivos generados sin rotación.
- Propuesta: Agregar un comando `artisan cache:clear-pdfs` o TTL.

### 10. Integración PayPal
El paquete `srmklive/paypal` está instalado y hay modelos `Order`, `SubscriptionPlan`, `SubscribedUser`, `Webhook`.
- P: ¿La integración está operativa o en desarrollo?
- P: ¿Los webhooks de PayPal se están recibiendo correctamente?

### 11. `public/web/caching_server.py`
Archivo Python dentro del build de Flutter.
- P: ¿Es necesario o es un residual del desarrollo?

### 12. Rate limiting en rutas públicas ✅ RESUELTO
Las rutas públicas de scores (score, styles, instruments, pdf, image, page) no tenían límite de peticiones.
- **Resuelto:** Implementado rate limiter `public` a 120 requests/minuto por IP en rutas públicas.

### 13. Sin políticas de autorización (Laravel Policies)
Laratrust está configurado pero no hay Policies definidas.
- P: ¿Se gestionan los permisos solo desde el código o se necesita una interfaz admin?
- P: El panel de Laratrust está deshabilitado. ¿Hay otro panel admin o se maneja por código/DB directa?
