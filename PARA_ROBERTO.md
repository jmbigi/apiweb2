# Faristol — Actualización para Agrupaciones

> Documento para Roberto Blasco Villarroya
> Julio 2026

---

## Resumen del proyecto

| Concepto | Detalle |
|----------|---------|
| **Objetivo** | Añadir gestión de agrupaciones (bandas, conservatorios, orquestas) a Faristol |
| **Entregable 1** | **Faristol App** (Android/iOS) — nueva sección "Ensembles" en el menú lateral |
| **Entregable 2** | **Control App** (Windows 11) — aplicación de escritorio para gestión |
| **Fase previa** | **Prototipo web** en web2.faristol.net para validar todo antes del desarrollo |
| **Presupuesto** | 100 horas totales |

## Lo que incluye (19 requerimientos)

| Área | Funcionalidades |
|------|----------------|
| **Agrupaciones** | Creación desde panel admin, repositorio privado, roles (archivero, administrador, maestro, usuario), premium automático para miembros |
| **Ensayos** | Listado con fecha, hora, lugar e instructor |
| **Setlist** | Lista ordenada de partituras desde el espacio Personal/Offline, con visor secuencial y avance automático (solo local, sin backend) |
| **Faristol App** | Nueva opción "Ensembles" en el menú lateral, filtro por agrupación, listado de ensayos |
| **Control App** | Aplicación Windows 11 con login, dashboard y gestión de repositorio, usuarios y ensayos |
| **Pago** | Transferencia bancaria, fuera de la app. El superadmin activa/desactiva cuentas manualmente |

## Lo que no incluye (para evitar malentendidos)

| Funcionalidad | Motivo |
|---------------|--------|
| Perfil educativo con jerarquía master→alumnos/profesores | Se acordó reemplazar por agrupaciones planas con roles |
| Subida masiva de partituras | Se acordó subida individual desde Control App |
| Visualización/descarga de PDFs en Control App | La Control App es solo gestión, no visor (RF-18) |
| Analytics de uso (master ve búsquedas de usuarios hijos) | No se acordó en el alcance final |
| Filtro de búsqueda (catálogo interno vs. general) | No se acordó en el alcance final |
| Confirmación de asistencia a ensayos | Roberto dijo: "a futuro y que lo pidan" |
| Pedal Bluetooth | Pendiente de cotización, post-octubre |

---

## Plan de trabajo (100h)

| Fase | Duración | Descripción |
|------|----------|-------------|
| **1. Prototipo web** | Jul–Ago | Prototipo funcional en web2.faristol.net para probar y validar los flujos |
| **2. Desarrollo** | ~1 semana | Backend API + Control App Windows + actualización Faristol App |
| **3. Pruebas (APK)** | ~2 días | Versión de prueba, corrección de errores |
| **4. Distribuciones** | ~3 días | Builds para Android, iOS y Windows |
| **5. Publicación** | ~3 días | Google Play + Apple App Store |

---

## Detalles técnicos (para Roberto)

### Backend (Laravel 10)

| Cambio | Tipo | Impacto en código existente |
|--------|------|----------------------------|
| 4 tablas nuevas: `ensembles`, `ensemble_user`, `ensemble_score`, `rehearsals` | Migraciones | **Nulo** — son tablas nuevas, no modifican existentes |
| Nuevos endpoints API para CRUD de agrupaciones, miembros, ensayos | Rutas + Controlador | **Mínimo** — se agregan al final de `api.php`, no se tocan rutas existentes |
| Premium automático para miembros de agrupación | 1 método en `SubscriptionService` | **Mínimo** — agrega un campo al response de `check-subscription`. No modifica la BD ni la lógica de pagos |
| Polimorfismo en `music_scores` para agrupaciones | Relación nueva en el modelo | **Nulo** — `music_scores` ya tiene relaciones polimórficas vía `files_s3_s`. Se sigue el mismo patrón |

### Faristol App (Flutter Android/iOS)

Tal como hablamos, la app móvil cambia muy poco:

| Cambio | Dónde | Tiempo estimado |
|--------|-------|-----------------|
| Agregar "Ensembles" al menú lateral | `menu_page.dart` (2 líneas) + `mainpage.dart` (1 case) | **1h** |
| Nueva pantalla con listado de agrupaciones del usuario | `ensembles_page.dart` (nuevo archivo) | **2h** |
| Filtro por agrupación en búsqueda | Pantalla de búsqueda existente | **1h** |
| Listado de ensayos (fecha, hora, lugar) | `rehearsals_page.dart` (nuevo) | **1h** |
| Setlist local con visor secuencial | Reutiliza el visor PDF existente (`pdfx`) con avance automático | **2h** |
| **Total** | **5h** | Como dijiste: *"el APK va a tener poquísimos cambios"* ✅ |

### Setlist (funcionalidad local, sin backend)

Los Setlists se guardan como **JSON en el dispositivo** (kilobytes). No se almacenan en el servidor. Las partituras que contienen ya están en el espacio Personal/Offline de cada usuario. El visor secuencial es el mismo visor PDF que ya existe (`pdfx`), pero con avance automático al terminar cada obra. Como quedamos: **visor secuencial, no concatenación de PDFs**.

### Control App (Windows 11 — Flutter)

Es una **aplicación nueva**, independiente de la app móvil. No porta el código existente. Se construye desde cero con las dependencias necesarias para gestión (sin anuncios, sin detección de capturas, sin visor PDF). La lógica de negocio está en la API de web2, la app solo consume endpoints.

**Repositorio y carpetas:** El archivero/administrador puede crear y organizar carpetas desde la Control App. Al subir una partitura, la interfaz muestra el nombre del archivo y su tamaño (KB/MB) antes de confirmar la subida — no se necesita abrir el PDF.

**Validación experimental de PDFs:** Durante la subida, el backend verificará que el archivo tenga formato PDF válido (`%PDF-` header) y que no esté encriptado (`/Encrypt`). Es una característica exploratoria: puede fallar con PDFs atípicos y, si genera problemas, se revertirá sin impacto en el resto del desarrollo. Al no estar presupuestada, no puedo dedicarle horas de más — si requiere ajustes adicionales, lo hablamos y valoramos aparte.

### Prototipo web (web2.faristol.net)

Ya está operativo con Laravel 10.48, PHP 8.2 y la base de datos `web2` (copia de producción con 558 usuarios). Sirve para validar los flujos de gestión antes de escribirlos en las apps nativas. Lo que se pruebe aquí define el comportamiento final.

---

## Sobre el presupuesto y los alcances

Roberto, quiero serte completamente sincero.

Este proyecto está presupuestado en 100 horas, que he calculado al detalle para cubrir exactamente lo que hemos hablado: el prototipo, el backend, la app móvil y la aplicación de escritorio. No hay margen para añadidos — las 100 horas están ajustadas a lo justo.

Te pido esto con toda la transparencia del mundo: si durante el proceso surge alguna idea nueva o funcionalidad extra, por muy buena que sea, va a ser difícil incorporarla sin que se desajuste todo. Lo que hemos definido juntos (los 19 puntos del documento) es justo lo que podemos hacer bien en este tiempo y con este presupuesto.

Mi compromiso es entregar eso, bien hecho, sin prisas de última hora y sin sorpresas. Si luego vemos que hace falta algo más, lo hablamos y lo planificamos aparte, como siempre hemos hecho.

¿Te parece bien?

Un abrazo,
Juan
