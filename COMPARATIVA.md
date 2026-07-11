# Comparativa: Antecedentes Preliminares vs. Requerimientos Actuales

> Comparación entre lo discutido antes de julio 2026 (antecedentes) y los
> requerimientos vigentes a julio 2026.

---

## Resumen de cambios

| Aspecto | Preliminar (oct 2025 – jun 2026) | Actual (julio 2026) | Estado |
|---------|----------------------------------|---------------------|--------|
| **Alcance** | Perfil educativo master con usuarios hijos (alumnos/profesores) | Agrupaciones (ensembles) con roles planos (Archivero, Admin, Maestro, Usuario) | ❌ **Reemplazado** |
| **Repositorio** | Catálogos público/privados configurables por el master | Repositorio privado por agrupación, no público | ❌ **Cambió** |
| **Subida de partituras** | Subida masiva y baja masiva por el master (solo área privada) | Subida individual por Archivero/Maestro desde Control App | ❌ **Simplificado** |
| **Fixbug** | Subida con mismo nombre (enero 2026) | No incluido en requerimientos | ⏳ Fixbug pendiente |
| **Analytics** | Master ve búsquedas y visualizaciones de usuarios hijos | No incluido en alcance actual | ❌ **Eliminado** |
| **Filtro búsqueda** | Check para filtrar catálogo interno vs. general | No incluido en alcance actual | ❌ **Eliminado** |
| **Roles** | Master, profesor, alumno | Superadmin, Archivero, Administrador, Maestro, Usuario básico | ❌ **Reestructurado** |
| **App escritorio** | No mencionado específicamente | Control App (Flutter, Windows 11) con vinculación única | ✅ **Nuevo** |
| **Setlist** | No mencionado (surgió después como alternativa a concatenación) | RF-12 a RF-14 | ✅ **Nuevo** |
| **Ensayos** | "Asistència a assajos i actes" (solicitud usuario) | Listado fecha/hora/lugar (1ª versión simple) | ✅ **Simplificado** |
| **Pago** | Por transferencia, fuera de la app | Por transferencia, superadmin activa/desactiva | ✅ **Consolidado** |
| **Roles existentes** | Cliente preguntó por cómo implementar roles | Laratrust ya implementado (superadmin, editorial, composer, musician) | ✅ **Confirmado** |
| **Migración WordPress** | Cotización 200€, Contabo 4€/mes, Cloudflare | Separado de los requerimientos Faristol | 🔀 **Tema separado** |

---

## Lo que se quitó

| Funcionalidad | Motivo |
|---------------|--------|
| Perfil educativo master con perfiles hijos (alumnos, profesores) | Se reemplazó por el concepto de agrupaciones (ensembles) con roles |
| Subida masiva y baja masiva de partituras | Se simplificó a subida individual desde Control App |
| Analytics (master ve búsquedas de usuarios hijos) | No priorizado en versión actual |
| Filtro de búsqueda (catálogo interno vs. general) | No priorizado en versión actual |
| Migración WordPress a Contabo/Hostinger | Tema separado, no es feature de Faristol |

## Lo que se agregó

| Funcionalidad | Motivo |
|---------------|--------|
| Control App (Flutter, Windows 11) | Explicitamente pedida en Memoria PDF |
| Setlist (visor secuencial de partituras) | Reemplaza a "concatenación de PDFs", prioridad muy alta |
| Demo web para refinar requerimientos | Compromiso del desarrollador (06/07/2026) |
| Premium automático para miembros de agrupación | Memoria PDF §3.4 |
| Tabla `music_scores` polimórfica | WhatsApp Roberto |

## Lo que se mantuvo (evolucionó)

| Funcionalidad | Preliminar | Actual |
|---------------|------------|--------|
| Roles | Master, profesor, alumno | Superadmin, Archivero, Admin, Maestro, Usuario |
| Repositorio privado | Catálogo público/privado configurable | Solo privado por agrupación |
| Ensayos | "Asistència a assajos i actes" | Listado fecha/hora/lugar (1ª versión) |
| Pago | Transferencia bancaria | Transferencia bancaria + superadmin activa/desactiva |

---

## Línea de tiempo de las decisiones

| Fecha | Evento | Fuente |
|-------|--------|--------|
| **May 2023** | Inicio de la relación laboral: clases de Symfony/Laravel | Chat completo |
| Mar 2025 | Primera mención a "federación de bandas" (concepto inicial de agrupaciones) | Mensajes Roberto |
| Abr 2025 | Segunda mención a "federación de bandas" | Mensajes Roberto |
| Jul 2025 | MGX offline editor. Se menciona *"v2 = grupos"* | Audios |
| **03 Ene 2026** | **CFO propone acuerdo para desarrollo de grupos. No se concretó** | Audio |
| Ene 2026 | Fixbug subida mismo nombre. Reunión CFO | Audios |
| **08 Feb 2026** | **Notas Roberto: Perfil Educativo, Subida Masiva** (1ª versión agrupaciones) | Notas |
| Abr 2026 | Se discute modelo de negocio para agrupaciones. Migración hosting | Audios |
| **~Feb 2026** | **Se redefine alcance.** Perfil Educativo → agrupaciones simples | Audios |
| **~Jun 2026** | **Se confirma acuerdo para el desarrollo** | Audios |
| May–Jun 2026 | Roberto prepara Memoria de Actualización PDF | — |
| 06 Jul 2026 | Demo web ofrecida. Dossier + encuesta. Banda Benidorm confirmada | Grupo |
| **10 Jul 2026** | **Documento requerimientos Julio 2026. 19 RFs** | — |

> La Memoria de Actualización PDF (enviada por Roberto en julio 2026) reemplaza
> y supera a las notas preliminares de febrero 2026, estableciendo el alcance
> definitivo para la versión de agrupaciones.

> La Memoria de Actualización PDF (enviada por Roberto en julio 2026) reemplaza
> y supera a las notas preliminares de febrero 2026, estableciendo el alcance
> definitivo para la versión de agrupaciones.
