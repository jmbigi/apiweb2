# Faristol — Nueva Versión: Actualización para Bandas y Agrupaciones Musicales

> **Versión:** Julio 2026
>
> **Antecedentes preliminares (enero–junio 2026):** Ver `ANTECEDENTES_PRELIMINARES.md`
> para conversaciones anteriores a julio que no forman parte del alcance actual.
>
> **Comparativa con versión preliminar:** Ver `COMPARATIVA.md` para el detalle de
> qué cambió entre las propuestas iniciales y los requerimientos actuales.
>
> Documento de requerimientos basado en:
> - Memoria de Actualización — Faristol para Bandas y Agrupaciones Musicales (PDF)
> - Dossier Faristol Bandas / Conservatorios (PDF)
> - Conversaciones de WhatsApp (julio 2026)
> - Transcripciones de 40 audios (faster-whisper large-v3)
> - Mockup de interfaz enviado por Roberto (+ análisis Gemini)
> - Encuesta de validación a usuarios (4 respuestas, Excel)
> - App Store y web faristol.net
> - Código fuente existente (API Laravel + Flutter)

---

## 1. Resumen Ejecutivo

Faristol es una plataforma digital para que músicos y compositores puedan gestionar,
distribuir y visualizar partituras de forma segura, organizada y legal. Actualmente
está publicada para iOS y Android en inglés, con planes Free (anuncios), Basic (2,99€)
y Premium (5,99€). La desarrolla **Biblioscores S.L.** (Calp, Alicante).

**Stack actual:** Laravel 10 (backend), Flutter/Dart (app móvil), Blade + Metronic (admin web),
MySQL, Laratrust (roles), Sanctum (auth API), Wasabi S3 (almacenamiento).

Esta actualización amplía Faristol para dar soporte a **bandas, conservatorios,
orquestas y agrupaciones musicales**. Consta de **dos entregables** precedidos
por un **prototipo web** (Julio–Agosto 2026):

**Entregable 1 — Faristol App (Android/iOS):**
- Nueva sección "Ensembles" en el menú lateral
- Repositorio privado de agrupaciones
- Listado de ensayos
- Setlist local (visor secuencial de partituras)

**Entregable 2 — Control App (Desktop Windows 11):**
- Aplicación de escritorio para gestión de agrupaciones
- Roles: archivero, administrador, maestro, usuario
- Gestión de repositorio, usuarios y ensayos

**Fase previa — Prototipo Web (Jul–Ago 2026):**
- Prueba y validación de funcionalidades antes del desarrollo
- Transición simple y fluida a la versión final

### 1.1. Equipo

- **Biblioscores S.L.** — Empresa propietaria de Faristol
- **Quico** — Decisiones estratégicas y financieras
- **Roberto Blasco Villarroya** — Gestión del proyecto
- **Tú** — Desarrollo

---

## 2. Módulo de Agrupaciones

### 2.1. Creación de Agrupaciones

- **RF-01**: La creación de agrupaciones musicales se realizará exclusivamente desde
  el panel de administración general de Faristol. Tabla: `ensembles`.
- **RF-02**: Solo el usuario con rol `superadmin` (administrador de Biblioscores/Faristol)
  podrá dar de alta nuevas agrupaciones.
- **RF-03**: Cada agrupación dispondrá de su propio espacio dentro de la plataforma,
  con repositorio privado de partituras y configuración de usuarios propia.

### 2.2. Repositorio Privado

- **RF-04**: Las partituras subidas por una agrupación serán privadas y exclusivas
  para los miembros de dicha agrupación. La tabla `music_scores` existente se
  relacionará con `ensembles` mediante una clave polimórfica o foránea.
- **RF-05**: Un usuario que no pertenezca a la agrupación no podrá ver ni acceder a
  esas partituras, ni siquiera a través de los filtros de búsqueda generales.
- **RF-06**: Cada agrupación tendrá un repositorio privado organizado por carpetas,
  proyectos o conciertos. Las carpetas son creadas y gestionadas exclusivamente
  desde la **Control App** por el Archivero o Maestro. La app móvil solo visualiza
  el repositorio ya organizado.

### 2.3. Estructura de Datos

- **RF-07**: La tabla `music_scores` debe soportar relación polimórfica para poder
  apuntar tanto a compositores individuales como a agrupaciones (Laravel,
  uniones polimórficas vía `fileable` en `files_s3_s` y `ensemble_score`).
- **RF-08**: Cada agrupación (`ensembles`) tendrá asociados: nombre, repositorio de
  partituras, miembros con roles (vía `ensemble_user`) y configuración de visibilidad.

### 2.4. Condiciones para Usuarios de Agrupación

- **RF-09**: Todos los usuarios asignados a una agrupación serán considerados
  instantáneamente como usuarios premium (sin limitaciones, sin anuncios).
  Upgrade lógico: no es un cambio de plan real en `subscription_plan`.
- **RF-10**: El script de renovación de usuarios de pago no deberá cobrar ni
  degradar a los usuarios mientras pertenezcan a una agrupación (`subscribed_user`).
  Si el usuario abandona o es removido, pierde los beneficios premium asociados.

### 2.5. Límites de la Agrupación

| Concepto | Límite |
|----------|--------|
| Usuarios por agrupación | 400 (extensible si se renegocia) |
| Partituras | Virtualmente ilimitadas |
| Planificación de ensayos | Ilimitada |

### 2.6. Tipos de Agrupación

- Bandas de música
- Conservatorios profesionales y superiores
- Escuelas municipales de música
- Orquestas juveniles y profesionales
- Agrupaciones que gestionan repertorio colectivo

---

## 3. Planificador de Ensayos

*Basado en audio de Roberto: "la pantalla de ensayos simplemente es un día y una hora
y como mucho un lugar" — primera versión simplificada.*

- **RF-11**: Visualizar listado de ensayos programados con fecha, hora, lugar e
  instructor. Tabla: `rehearsals`. Acceso desde la pestaña "Ensembles" en la app.

*Nota: Confirmación de asistencia, notificaciones y funcionalidades avanzadas
quedan para versiones futuras ("a futuro y que lo pidan" — Roberto).*

---

## 4. Sistema de Roles

Se implementará sobre el sistema existente **Laratrust** (tablas `roles`, `permissions`,
`role_user`, `permission_user`, `permission_role`). Roles actuales: `superadmin`,
`editorial`, `composer`, `musician`.

### 4.1. Roles Definidos

| Rol | Permisos | Ámbito | Tabla |
|-----|----------|--------|-------|
| **Superadmin** | Administrador de Biblioscores/Faristol. Crear agrupaciones, activar/desactivar cuentas manualmente. Multi-cliente. | Global | `roles` (existente) |
| **Archivero** | Digitalización, subida y actualización del repositorio privado de partituras. | Agrupación | `ensemble_user` |
| **Administrador** | Gestión de usuarios, asignación de roles, subida de partituras, gestión integral de la agrupación. | Agrupación | `ensemble_user` |
| **Maestro / Instructor** | Subida de partituras al repositorio (también desde app móvil, con selección de carpeta) y planificación de ensayos. | Agrupación | `ensemble_user` |
| **Usuario básico** | Consulta de partituras y ensayos de su agrupación. Usa solo la app Faristol móvil. | Agrupación | `ensemble_user` |

### 4.2. Reglas de Roles

- Un usuario puede tener más de un rol simultáneamente (Laratrust)
- Solo el usuario con rol Maestro puede planificar ensayos
- El superadmin activa y desactiva cuentas de agrupación de forma manual
  (no hay Stripe ni pagos automatizados en la Control App)
- El Maestro puede subir partituras desde la app Faristol móvil,
  probablemente con selección de carpeta
- El usuario básico, en principio, solo usa la app Faristol móvil

---

## 5. Setlist

La funcionalidad de **concatenación de partituras** fue solicitada por usuarios
en la encuesta y confirmada por Roberto como **"muy importante"** (prioridad muy
alta en la Memoria PDF). Se implementa como **Setlist** (lista ordenada de
partituras con visor secuencial).

**Importante:** El Setlist es una funcionalidad **local** (solo en el dispositivo
móvil, sin backend). Opera exclusivamente con partituras del espacio **Personal
Music Score / Offline**. No incluye Setlists de Agrupaciones ni sincronización
con servidor. Tampoco incluye gestión de Setlists desde la Control App.

- **RF-12**: Crear un Setlist: lista ordenada de partituras del espacio
  **Personal Music Score / Offline** (sección existente en la app). Solo local,
  sin backend.
- **RF-13**: Ordenar y reordenar las partituras dentro del Setlist
  (arrastrar y soltar, o subir/bajar). Datos guardados localmente.
- **RF-14**: Visor especial de Setlist en la app existente: al abrir un Setlist,
  las partituras se muestran una tras otra en orden. Al llegar al final de una,
  se pasa automáticamente a la siguiente, simulando la experiencia de tenerlas
  concatenadas físicamente en un atril. Reutiliza el visor de PDF existente
  (`pdfx` u otro) para la visualización. Solo local, sin conexión a servidor.
  Funcionalidad pensada para ensayos y conciertos donde el músico no necesita
  buscar las partituras una por una.

---

## 6. Aplicaciones

### 6.1. Faristol App (Android/iOS)

App existente publicada en inglés (Flutter). Modificaciones necesarias:

- **Nueva opción en el menú lateral izquierdo**: "Ensembles" (inglés)
- La sección Ensembles muestra:
  - Lista de agrupaciones a las que pertenece el usuario (`ensemble_user`)
  - Filtro para seleccionar por agrupación (un músico puede estar en varias)
  - Al seleccionar una agrupación: repertorio privado, ensayos programados
    (`rehearsals`), Setlists (`setlists`)
- El Setlist funciona con partituras del espacio **Personal Music Score / Offline**
  (solo local, sin backend, sin Agrupaciones)
- El usuario básico solo usa la app móvil
- El Maestro puede subir partituras al repositorio de la agrupación desde la app
- No requiere cambios en el modelo de suscripción existente (`subscription_plan`,
  `subscribed_user`)
- Conserva el idioma inglés (la app existente no se traduce)

### 6.2. Control App (Desktop Windows 11)

Nueva aplicación de escritorio para la gestión de agrupaciones.

**Tecnología:** Flutter (Windows 11)

**Características:**
- **RF-15**: Primer inicio: vinculación única a una agrupación (`ensembles`).
  Posteriormente solo solicitará usuario y contraseña.
- **RF-16**: La vinculación se mantendrá salvo que el usuario reinicie manualmente
  el sistema de login.
- **RF-17**: Funcionalidades de gestión: repositorio (`ensemble_score`), subir
  partituras (`music_scores`), administrar usuarios (`ensemble_user`),
  planificar ensayos (`rehearsals`).
- **RF-18**: **No** incluye visualización ni edición de partituras.
- **RF-19**: No incluye sistema de pagos (Stripe ni similar).

**Idioma:** Español (no multi-lenguaje)

**Pantallas previstas:**
1. **Login**: vinculación a agrupación en primer inicio, luego solo user + pass
2. **Dashboard / Menú principal**: acceso a módulos de gestión

### 6.3. Prototipo Web (Julio–Agosto 2026)

- Compromiso explícito del desarrollador: *"Voy a construir una demo web para
  discutir"* (mensaje del 06/07 en el grupo Faristol Mac App)
- **Fase de prototipado web** durante julio y agosto de 2026 para probar y
  refinar las nuevas funcionalidades.
- El prototipo web se alojará en **web2.faristol.net** y utilizará la
  base de datos **web2** (copia reciente de la base de producción, ligeramente
  anterior). Esta base es independiente de la de producción activa.
- El prototipo debe ser **totalmente funcional y completo**, no una maqueta.
  Operará sobre web2 con datos reales para validar todos los flujos.
- **Regla fundamental:** todo lo que se implemente en el prototipo debe estar
  **técnica y prácticamente resuelto desde el día 1** para funcionar en la
  versión final. No se incluirán funcionalidades que no sean factibles de
  llevar a producción. El prototipo sirve para validar, no para experimentar
  con ideas inviables.
- El prototipo web servirá para **investigar y validar** el comportamiento de
  ambas aplicaciones:
  - **Control App** (escritorio): flujos de gestión, roles, ensayos
  - **Faristol App** (móvil): navegación, ensembles, setlist
- La transición del prototipo web a la versión de producción final debe ser
  **muy rápida — aproximadamente 3 días**. Por eso es crítico que todo quede
  investigado y validado durante la fase de prototipo.
- Se presentará a Biblioscores/Faristol para obtener feedback antes del desarrollo.

---

## 7. Modelo de Pago para Agrupaciones

- El pago de las agrupaciones se realizará por **transferencia bancaria**,
  fuera de la aplicación.
- El **contrato** se hará ad hoc para cada agrupación.
- El **superadmin** (Biblioscores/Faristol) activa y desactiva las cuentas
  manualmente. Concepto multi-cliente.
- Este modelo permite flexibilidad sin necesidad de integrar un sistema de pago
  automatizado dentro de la app. No afecta a `subscription_plan` ni `subscribed_user`.

---

## 8. Presupuesto y Roadmap

### 8.1. Presupuesto de horas

El proyecto completo está presupuestado en **100 horas totales**, distribuidas
de la siguiente manera:

| Actividad | Horas | Detalle |
|-----------|-------|---------|
| Prototipo web (web2) | 15h | Setup web2.faristol.net + BD web2 + flujos funcionales de gestión, roles y ensayos para validar con Biblioscores |
| Backend API | 20h | Migraciones (4 tablas nuevas), modelos, controladores, endpoints CRUD para ensembles, miembros, partituras privadas, ensayos |
| Control App (Flutter Windows) | 15h | Proyecto nuevo desde cero solo con dependencias necesarias. Login, dashboard, gestión de repo/usuarios/ensayos |
| Faristol App (Android/iOS) | 5h | Añadir opción "Ensembles" al menú lateral. Filtro de agrupaciones. Listado de ensayos. Visor Setlist local |
| Reuniones, coordinación | 8h | Reuniones con Biblioscores/Faristol, coordinación, revisiones de prototipo |
| Pruebas y correcciones (APK) | 12h | Pruebas internas, corrección de errores, verificación en dispositivos reales |
| Distribuciones y builds | 10h | Generación de builds para Android, iOS y Windows. Preparación de assets, capturas, metadata |
| Publicación en tiendas | 8h | Envío a Google Play y Apple App Store. Respuesta a comentarios de revisores |
| Margen / imprevistos | 7h | Para cubrir cualquier desviación o tarea no contemplada |
| **Total** | **100h** | |

### 8.2. Roadmap

| Fase | Actividad | Periodo | Descripción |
|------|-----------|---------|-------------|
| **1** | Prototipo Web | Jul–Ago 2026 | Prototipo web totalmente funcional en web2.faristol.net (BD web2, copia de producción). Probar y refinar todas las funcionalidades nuevas de ambas apps. Investigar y validar antes del desarrollo definitivo. Obtener feedback de Biblioscores/Faristol. |
| **2** | Desarrollo Web + App Flutter | ~1 semana | Partiendo del prototipo web validado, desarrollo de la versión web definitiva + actualización de la app Flutter (Android/iOS) con las nuevas funcionalidades. |
| **3** | APK pruebas | ~2 días | Generación de versión APK para pruebas internas y con usuarios seleccionados. Corrección de errores. |
| **4** | Distribuciones | ~3 días | Generación de builds finales para todas las plataformas (Android, iOS, Windows). Preparación de assets y metadata. |
| **5** | Publicación en tiendas | ~3 días | Envío a Google Play Store y Apple App Store para revisión y aprobación. |

---

## 9. Desarrollos Futuros (sin planificación actual)

Funcionalidades identificadas en las fuentes pero sin compromiso ni planificación
para esta versión ni para una fecha concreta:

| Funcionalidad | Situación |
|---------------|-----------|
| **Confirmación de asistencia a ensayos** | Roberto lo dejó explícitamente para futuro: *"eso ya a futuro y que lo pidan"* |
| **Notificaciones de ensayos** | No mencionado explícitamente. Depende de la asistencia. |
| **Pedal Bluetooth** para paso de páginas | Pendiente de cotización e investigación. Post-octubre. |
| **Contacto entre músicos** | Prioridad baja según tabla de la Memoria PDF. |
| **Archivo autonómico/nacional/internacional** | Pendiente de consulta legal. |

---

> **Convenciones de nombres:** Tablas en snake_case plural (`ensembles`, `rehearsals`).
> Modelos Laravel en PascalCase (`Ensemble`, `Rehearsal`). Pivotes con `_` simple
> (`ensemble_user`, `setlist_score`).
>
> **Roles Laratrust existentes:** `superadmin`, `editorial`, `composer`, `musician`.
>
> **Tablas existentes relevantes:** `music_scores`, `users`, `subscription_plan`,
> `subscribed_user`, `files_s3_s` (polimórfico), `roles`, `permissions`, `role_user`.
>
> Fuentes: 167 mensajes WhatsApp · 40 transcripciones audio · Memoria Actualización PDF ·
> Dossier PDF · Mockup UI (1920×1080) · Encuesta usuarios (4 respuestas) · faristol.net ·
> App Store · Wikipedia (setlist) · Código fuente API Laravel + Flutter
