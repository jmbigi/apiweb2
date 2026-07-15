# Faristol - Sistema de Gestión de Partituras Musicales

## 🎵 Descripción General

Faristol es un sistema integral de gestión de partituras musicales desarrollado con Laravel 10. La plataforma permite a músicos, compositores y administradores gestionar, compartir y acceder a una extensa biblioteca de partituras musicales con funcionalidades avanzadas de suscripción y colaboración.

## ✨ Características Principales

### 👥 Gestión de Usuarios
- **Registro y Autenticación**: Sistema completo con verificación por email
- **Perfiles de Usuario**: Gestión de información personal y preferencias
- **Roles y Permisos**: Sistema basado en roles (músico, compositor, superadmin)
- **Autenticación de Dos Factores**: OTP por email para mayor seguridad

### 🎼 Sistema de Compositores
- **Catálogo de Compositores**: Base de datos completa de compositores
- **Solicitudes de Compositor**: Proceso de aplicación para usuarios
- **Gestión Biográfica**: Información detallada y histórica
- **Aprobación Administrativa**: Workflow de revisión y aprobación

### 📚 Biblioteca de Partituras
- **Subida Masiva**: Carga múltiple de archivos PDF
- **Categorización Avanzada**: Por compositor, instrumento y estilo
- **Sistema de Favoritos**: Colecciones personalizadas
- **Anotaciones**: Sistema de notas y marcadores
- **Búsqueda Inteligente**: Filtros múltiples y búsqueda por texto

### 💳 Sistema de Suscripciones
- **Tres Niveles**: Gratuito, Básico y Premium
- **Integración PayPal**: Pagos seguros y automatizados
- **Prueba Premium**: 31 días gratuitos para nuevos usuarios
- **Gestión Automática**: Renovaciones y cancelaciones

### 🎹 Gestión de Instrumentos
- **Familias de Instrumentos**: Organización jerárquica
- **Catálogo Completo**: Base de datos extensiva
- **Filtrado por Instrumento**: Búsqueda específica de partituras

### 🎨 Estilos Musicales
- **Categorización por Género**: Clásico, jazz, rock, etc.
- **Etiquetado Múltiple**: Partituras con múltiples estilos
- **Descubrimiento**: Exploración por preferencias musicales

### 🛡️ Panel Administrativo
- **Dashboard Completo**: Métricas y estadísticas en tiempo real
- **Gestión de Usuarios**: CRUD completo con exportación
- **Control de Contenido**: Moderación de partituras y compositores
- **Reportes Avanzados**: Análisis de uso y rendimiento

### 📱 API RESTful
- **Documentación Completa**: Endpoints para aplicaciones móviles
- **Autenticación Sanctum**: Tokens seguros para API
- **Sincronización**: Datos en tiempo real entre plataformas

### 📧 Sistema de Notificaciones
- **Emails Transaccionales**: Verificación, bienvenida, recordatorios
- **Ofertas Personalizadas**: Emails de retención y promoción
- **Plantillas Personalizables**: HTML responsive

## 📖 Estructura de Documentación

### Guías de Instalación y Configuración
- [📦 Guía de Instalación](installation.md) - Configuración paso a paso
- [🚀 Guía de Despliegue](deployment.md) - Producción y staging
- [⚙️ Configuración del Sistema](configuration.md) - Variables de entorno

### Documentación Técnica
- [🔌 Documentación de API](api.md) - Endpoints y ejemplos
- [🗄️ Esquema de Base de Datos](database-schema.md) - Estructura y relaciones
- [🏗️ Arquitectura del Sistema](architecture.md) - Patrones y diseño

### Guías de Usuario
- [👤 Gestión de Usuarios](user-management.md) - Administración de cuentas
- [💰 Sistema de Suscripciones](subscription-system.md) - Planes y pagos
- [🎼 Gestión de Partituras](music-score-management.md) - CRUD y organización
- [👨‍🎨 Sistema de Compositores](composer-management.md) - Gestión de autores

### Características Administrativas
- [🛠️ Funciones de Administrador](admin-features.md) - Panel de control
- [📊 Reportes y Analytics](reports-analytics.md) - Métricas del sistema
- [📧 Sistema de Emails](email-system.md) - Configuración y plantillas
- [🔐 Seguridad](security.md) - Protección y mejores prácticas

### Desarrollo y Mantenimiento
- [🧪 Testing](testing.md) - Pruebas unitarias e integración
- [🐛 Solución de Problemas](troubleshooting.md) - Problemas comunes
- [🔄 Actualizaciones](updates.md) - Proceso de actualización
- [📋 Changelog](changelog.md) - Historial de versiones

## 🚀 Inicio Rápido

### Requisitos del Sistema
- **PHP**: 8.1 o superior
- **Laravel**: 10.x
- **Base de Datos**: MySQL 8.0+ / PostgreSQL 13+
- **Servidor Web**: Apache/Nginx
- **Node.js**: 16.x o superior para assets

### Instalación Express
```bash
# 1. Clonar repositorio
git clone <repository-url> faristol
cd faristol

# 2. Instalar dependencias
composer install
npm install

# 3. Configurar entorno
cp .env.example .env
php artisan key:generate

# 4. Configurar base de datos
php artisan migrate --seed

# 5. Compilar assets
npm run build

# 6. Iniciar servidor
php artisan serve
```

### Acceso Inicial
1. **Panel Admin**: `http://localhost:8000/login`
2. **Usuario por defecto**: `superadmin@gmail.com`
3. **API Base**: `http://localhost:8000/api`
4. **Documentación API**: [api.md](api.md)

## 🛠️ Tecnologías Utilizadas

### Backend
- **Framework**: Laravel 10.x
- **Base de Datos**: MySQL/PostgreSQL
- **Autenticación**: Laravel Sanctum
- **Almacenamiento**: AWS S3/Local
- **Colas**: Redis/Database
- **Cache**: Redis/File

### Frontend
- **CSS Framework**: Bootstrap/Tailwind
- **JavaScript**: Vanilla JS/Alpine.js
- **Build Tool**: Vite
- **Icons**: FontAwesome/Feather

### Integraciones
- **Pagos**: PayPal API
- **Email**: SMTP/SendGrid/Mailgun
- **Almacenamiento**: AWS S3/Wasabi
- **PDF Processing**: PDFtk

## 📞 Soporte y Contacto

### Soporte Técnico
- **Sistema Interno**: `/support/en`
- **Email**: support@faristol.net
- **Documentación**: Esta documentación

### Desarrollo
- **Repositorio**: [GitHub/GitLab URL]
- **Issues**: [Issues URL]
- **Wiki**: [Wiki URL]

### Comunidad
- **Foro**: [Forum URL]
- **Discord**: [Discord URL]
- **Newsletter**: [Newsletter URL]

## 📊 Información del Proyecto

### Versiones
- **Actual**: v1.0.0
- **Laravel**: 10.x
- **PHP**: 8.1+
- **Base de Datos**: MySQL 8.0+

### Estado del Proyecto
- **Estado**: ✅ Producción
- **Mantenimiento**: 🔄 Activo
- **Última Actualización**: 2024-01-15
- **Próxima Release**: v1.1.0 (Q2 2024)

### Licencia
Este proyecto está licenciado bajo [Tipo de Licencia] - ver el archivo [LICENSE](../LICENSE) para detalles.

### Contribuidores
- **Desarrollador Principal**: [Nombre]
- **Equipo Backend**: [Nombres]
- **Equipo Frontend**: [Nombres]
- **QA Team**: [Nombres]

---

# 📚 Documentación Técnica - Faristol

## 🎯 Propósito de esta Documentación

Esta documentación está diseñada para:
- **Nuevos desarrolladores**: Comprender rápidamente la arquitectura y decisiones técnicas
- **Colaboradores actuales**: Referencia rápida sobre patrones y convenciones
- **Futuro mantenimiento**: Entender el "por qué" detrás de cada decisión técnica
- **Contribuyentes externos**: Guías para contribuir efectivamente

## 🏗️ Estructura de la Documentación

### 📁 [/decisions](decisions/) - Architecture Decision Records (ADRs)
Documentación sobre decisiones arquitectónicas importantes y patrones de diseño únicos del proyecto.

**ADRs Clave:**
- [001 - Unique Phone Numbers](decisions/001-unique-phone-numbers.md) - Teléfonos como identificadores únicos
- [002 - Subscription Model](decisions/002-subscription-model.md) - Modelo progresivo vs restrictivo
- [003 - PDF Processing Strategy](decisions/003-pdf-processing-strategy.md) - Pre-procesamiento vs tiempo real
- [004 - Composer Approval Workflow](decisions/004-composer-approval-workflow.md) - Aprobación manual vs automática
- [005 - API Design Philosophy](decisions/005-api-design-philosophy.md) - API "humano-primero"
- [006 - Storage Strategy](decisions/006-storage-strategy.md) - Almacenamiento híbrido
- [007 - Mobile-First Approach](decisions/007-mobile-first-approach.md) - Prioridad mobile musical

### 📁 [/architecture](architecture/) - Documentación Arquitectónica
Visión general del sistema y decisiones de modelado de datos.

- [System Overview](architecture/system-overview.md) - Arquitectura general y conceptos únicos
- [Data Model](architecture/data-model.md) - Modelo de datos evolutivo

### 📁 [/technical](technical/) - Documentación Técnica Especializada
Guías técnicas sobre implementaciones no convencionales y particularidades del sistema.

- [Security Approach](technical/security-approach.md) - Enfoque de "confianza progresiva"
- [Performance Strategy](technical/performance-strategy.md) - Performance optimizada para uso musical
- [Annotation System](technical/annotation-system.md) - Sistema frontend-first de anotaciones

### 📁 [/operations](operations/) - Documentación Operacional
Guías para deployment, monitoreo y operaciones que consideran contexto musical.

- [Monitoring Strategy](operations/monitoring-strategy.md) - Métricas específicas para experiencia musical
- [Deployment Guide](operations/deployment-guide.md) - Zero-downtime musical deployment

### 📁 [/testing](testing/) - Estrategias de Testing
Testing que valida experiencia musical real, no solo funcionalidad técnica.

- [Testing Strategy](testing/testing-strategy.md) - Framework de testing musical específico

### 📁 [/integrations](integrations/) - Documentación de Integraciones
Integraciones externas optimizadas para contexto musical.

- [PayPal Integration](integrations/paypal-integration.md) - Pagos sin fricción musical

### 📁 [/guides](guides/) - Guías de Desarrollo
Guías paso a paso para tareas específicas y casos de uso únicos.

- [Onboarding Guide](guides/onboarding-guide.md) - Primera semana para nuevos desarrolladores
- [Development Workflow](guides/development-workflow.md) - Proceso de desarrollo diario
- [Debugging Guide](guides/debugging-guide.md) - Debugging con contexto musical

### 📁 [/contributing](contributing/) - Documentación de Contribución
Guías para colaboradores externos y procesos de contribución.

- [Contribution Guide](contributing/contribution-guide.md) - Cómo contribuir efectivamente

### 📁 [/templates](templates/) - Templates de Documentación
Templates para mantener consistencia en nueva documentación.

- [ADR Template](templates/adr-template.md) - Template para nuevas decisiones arquitectónicas
- [Integration Guide Template](templates/integration-guide-template.md) - Template para nuevas integraciones

## 🌟 Características Únicas de Faristol

### Sistema de Anotaciones Frontend-First
- **Decisión**: Anotaciones viven primero en el frontend, sincronización opcional
- **Justificación**: Responsividad inmediata durante práctica musical
- **Implementación**: LocalStorage/IndexedDB con sync inteligente

### Modelo de Suscripción Progresivo
- **Filosofía**: "La música debe ser accesible, las herramientas profesionales justifican el pago"
- **Implementación**: Free (acceso total) → Paid (herramientas avanzadas)
- **Particularidad**: Nunca bloquear contenido, solo limitar productividad

### Gestión de PDF Multipágina
- **Decisión**: Pre-procesamiento completo al upload
- **Beneficio**: Zero tiempo de procesamiento durante lectura
- **Particularidad**: Múltiples calidades según suscripción

### Confianza Progresiva de Seguridad
- **Concepto**: Seguridad que se adapta al nivel de confianza del usuario
- **Implementación**: Menos validaciones para usuarios establecidos
- **Beneficio**: Balance entre seguridad y experiencia fluida

### API "Humano-Primero"
- **Filosofía**: "Si un músico puede entender el endpoint, está bien diseñado"
- **Implementación**: URLs descriptivas sobre REST puro
- **Particularidad**: Terminología musical en lugar de jerga técnica

## 🔍 Navegación Rápida

### Por Rol
| Rol | Documentos Esenciales |
|-----|----------------------|
| **Frontend Developer** | [Mobile-First Approach](decisions/007-mobile-first-approach.md) → [Annotation System](technical/annotation-system.md) → [Performance Strategy](technical/performance-strategy.md) |
| **Backend Developer** | [Data Model](architecture/data-model.md) → [API Design](decisions/005-api-design-philosophy.md) → [Security Approach](technical/security-approach.md) |
| **DevOps Engineer** | [Storage Strategy](decisions/006-storage-strategy.md) → [Deployment Guide](operations/deployment-guide.md) → [Monitoring Strategy](operations/monitoring-strategy.md) |
| **New Contributor** | [System Overview](architecture/system-overview.md) → [Onboarding Guide](guides/onboarding-guide.md) → [Contribution Guide](contributing/contribution-guide.md) |

### Por Necesidad
| Necesidad | Documento |
|-----------|-----------|
| Entender arquitectura general | [System Overview](architecture/system-overview.md) |
| Conocer decisiones clave | [ADRs Index](decisions/) |
| Implementar nuevas features | [Development Workflow](guides/development-workflow.md) |
| Resolver problemas | [Debugging Guide](guides/debugging-guide.md) |
| Contribuir al proyecto | [Contribution Guide](contributing/contribution-guide.md) |
| Hacer deployment | [Deployment Guide](operations/deployment-guide.md) |

### Por Particularidad Técnica
| Particularidad | Documentación |
|----------------|---------------|
| Anotaciones frontend-first | [Annotation System](technical/annotation-system.md) |
| API humano-primero | [API Design Philosophy](decisions/005-api-design-philosophy.md) |
| Seguridad progresiva | [Security Approach](technical/security-approach.md) |
| Performance musical | [Performance Strategy](technical/performance-strategy.md) |
| Modelo de suscripción único | [Subscription Model](decisions/002-subscription-model.md) |

## 📝 Convenciones de Documentación

### Principios de Escritura
- **Decisiones primero**: Siempre documentar el "por qué", no solo el "qué"
- **Contexto musical**: Cada decisión debe considerar impacto en músicos
- **Ejemplos mínimos**: Código solo cuando sea esencial para entender el concepto
- **Enlaces internos**: Referencias cruzadas entre documentos relacionados
- **Versionado**: Cada cambio significativo actualiza la fecha del documento

### Estructura de Documentos
1. **Filosofía/Principio**: Por qué existe esta aproximacion
2. **Decisiones únicas**: Qué hace diferente a Faristol
3. **Implementación**: Cómo se materializa la decisión
4. **Consecuencias**: Beneficios y trade-offs
5. **Evolución**: Cómo puede cambiar en el futuro

## 🚀 Próximos Pasos

### Si eres nuevo en el proyecto:
1. Lee [System Overview](architecture/system-overview.md)
2. Revisa [Onboarding Guide](guides/onboarding-guide.md)
3. Consulta [Development Workflow](guides/development-workflow.md)
4. Explora [Contribution Guide](contributing/contribution-guide.md)

### Si quieres contribuir:
1. Entiende [las decisiones arquitectónicas clave](decisions/)
2. Revisa [guías de contribución](contributing/contribution-guide.md)
3. Usa [templates apropiados](templates/) para nueva documentación
4. Sigue [el workflow de desarrollo](guides/development-workflow.md)

### Si necesitas resolver un problema:
1. Consulta [Debugging Guide](guides/debugging-guide.md)
2. Revisa [Monitoring Strategy](operations/monitoring-strategy.md)
3. Busca en documentación técnica relevante
4. Contacta al equipo si es necesario

## 📞 Soporte

- **Documentación completa**: [NAVIGATION.md](NAVIGATION.md)
- **Preguntas técnicas**: Ver [Debugging Guide](guides/debugging-guide.md)
- **Contribuciones**: Ver [Contribution Guide](contributing/contribution-guide.md)
- **Nuevas integraciones**: Usar [Integration Template](templates/integration-guide-template.md)

---

**Última actualización**: 15 de Enero, 2024  
**Mantenido por**: Equipo de Desarrollo Faristol  
**Versión de documentación**: v1.0
