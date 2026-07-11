# Tabla Resumen — Actualización Faristol para Agrupaciones

> **Versión:** Julio 2026
>
> Los requerimientos evolucionaron durante el proceso de análisis. Los antecedentes
> preliminares (enero–junio 2026) están documentados en `ANTECEDENTES_PRELIMINARES.md`
> y no forman parte del alcance actual.

| # | Área | RF | Tabla DB | Descripción | Fuente Principal | Decisiones / Notas |
|---|------|----|----------|-------------|------------------|---------------------|
| 1 | **Agrupaciones** | RF-01 | `ensembles` | Creación desde panel admin general | Memoria PDF | Solo superadmin (Biblioscores) |
| 2 | **Agrupaciones** | RF-02 | — | Solo superadmin da de alta agrupaciones | Memoria PDF | Concepto multi-cliente |
| 3 | **Agrupaciones** | RF-03 | `ensembles` | Cada agrupación: espacio propio + repositorio privado | Memoria PDF | — |
| 4 | **Agrupaciones** | RF-04 | `music_scores` + `ensemble_score` | Partituras privadas solo para miembros | Memoria PDF | Relación polimórfica o FK |
| 5 | **Agrupaciones** | RF-05 | — | No miembros no pueden ver ni buscar | Memoria PDF | — |
| 6 | **Agrupaciones** | RF-06 | `ensemble_score` | Repositorio organizado por carpetas. Solo desde Control App | Memoria PDF | Archivero/Maestro gestionan |
| 7 | **Agrupaciones** | RF-07 | `music_scores` | Tabla partituras polimórfica (compositor O agrupación) | WhatsApp Roberto | Laravel uniones polimórficas |
| 8 | **Agrupaciones** | RF-08 | `ensembles` | Estructura: nombre, repo, miembros, roles, visibilidad | Memoria PDF | — |
| 9 | **Agrupaciones** | RF-09 | — | Miembro se considera Premium instantáneamente | Memoria PDF §3.4 | Upgrade lógico |
| 10 | **Agrupaciones** | RF-10 | `subscribed_user` | Script renovación no debe cobrar a miembros | Memoria PDF §3.4 | Si abandona, pierde beneficios |
| 11 | **Agrupaciones** | — | — | Límites: 400 usuarios, partituras ilimitadas | Memoria PDF | — |
| 12 | **Ensayos** | RF-11 | `rehearsals` | Visualizar listado (fecha, hora, lugar, instructor) | Audio Roberto | 1ª versión simplificada |
| 13 | **Roles** | — | `roles` | Superadmin: admin Biblioscores, multi-cliente | Memoria PDF | Roles existentes: superadmin, editorial, composer, musician |
| 14 | **Roles** | — | `ensemble_user` | Archivero: sube/actualiza repositorio privado | Memoria PDF | — |
| 15 | **Roles** | — | `ensemble_user` | Administrador: gestión usuarios, roles, subir partituras | Memoria PDF | — |
| 16 | **Roles** | — | `ensemble_user` | Maestro: sube partituras (app móvil + carpeta), planifica ensayos | Memoria PDF | Solo Maestro planifica |
| 17 | **Roles** | — | `ensemble_user` | Usuario básico: consulta partituras y ensayos. Solo app móvil | Memoria PDF | — |
| 18 | **Setlist** | RF-12 | — | Crear Setlist local. Solo Personal/Offline. Sin backend. | PDF + Roberto | Solo local. No incluye Agrupaciones ni Control App |
| 19 | **Setlist** | RF-13 | — | Ordenar y reordenar partituras dentro del Setlist | Audio Roberto | Local, sin servidor |
| 20 | **Setlist** | RF-14 | — | Visor secuencial: muestra obras una tras otra, avance automático. Reutiliza visor PDF existente | Audio Roberto | Para ensayos/conciertos. Sin conexión a servidor |
| 21 | **Faristol App** | — | — | Nueva opción menú lateral: Ensembles (inglés) | Mockup + Gemini | App NO se traduce |
| 22 | **Faristol App** | — | — | Filtro agrupaciones + listado ensayos + Setlists | Audio Roberto | APK cambia poco |
| 23 | **Control App** | RF-15 | `ensembles` | Primer inicio: vinculación única a agrupación | Memoria PDF | Flutter Windows 11 |
| 24 | **Control App** | RF-16 | — | Vinculación persistente (hasta reset manual) | Memoria PDF | — |
| 25 | **Control App** | RF-17 | `ensemble_score`, `rehearsals` | Gestión: repo, usuarios, ensayos | Memoria PDF | — |
| 26 | **Control App** | RF-18 | — | Sin visualización ni edición de partituras | Memoria PDF | Solo gestión |
| 27 | **Control App** | RF-19 | — | Sin pagos (sin Stripe) | Memoria PDF | — |
| 28 | **Control App** | — | — | Idioma: español. No multi-lenguaje | Decisión propia | — |
| 29 | **Prototipo Web** | — | web2 | Prototipo web totalmente funcional en web2.faristol.net (BD copia) para probar y refinar ambas apps. Transición a producción en ~3 días | Compromiso desarrollador | BD web2 copia. Todo en el prototipo debe ser factible en producción desde el día 1 |
| 30 | **Pago** | — | — | Transferencia bancaria. Contrato ad hoc | Memoria PDF | Fuera de la app |
| 31 | **Roadmap** | — | — | Fase 1: Prototipo Web → Fase 2: Desarrollo (~1 sem) → Fase 3: APK pruebas (2d) → Fase 4: Distribuciones (3d) → Fase 5: Publicación tiendas (3d) | — | 100h totales (20+40+10+10+10+5+5) |
