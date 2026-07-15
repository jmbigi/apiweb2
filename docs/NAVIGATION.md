# 🧭 Índice de Navegación - Documentación Faristol

## 🎯 Guías de Inicio Rápido

### Para Nuevos Desarrolladores
1. **[README Principal](README.md)** - Visión general del proyecto
2. **[System Overview](architecture/system-overview.md)** - Arquitectura general
3. **[Onboarding Guide](guides/onboarding-guide.md)** - Primera semana en el proyecto
4. **[Development Workflow](guides/development-workflow.md)** - Cómo trabajamos

### Para Desarrolladores Musicales
1. **[Musical Annotation System](technical/annotation-system.md)** - Sistema único de anotaciones
2. **[Mobile-First Approach](decisions/007-mobile-first-approach.md)** - Enfoque mobile específico
3. **[PDF Processing Strategy](decisions/003-pdf-processing-strategy.md)** - Procesamiento de partituras
4. **[API Design Philosophy](decisions/005-api-design-philosophy.md)** - API "humano-primero"

### Para Arquitectos de Sistema
1. **[Data Model](architecture/data-model.md)** - Modelo de datos evolutivo
2. **[Storage Strategy](decisions/006-storage-strategy.md)** - Almacenamiento híbrido
3. **[Performance Strategy](technical/performance-strategy.md)** - Performance musical
4. **[Security Approach](technical/security-approach.md)** - Confianza progresiva

## 📚 Documentación por Categorías

### 🏗️ Arquitectura
| Documento | Enfoque | Audiencia |
|-----------|---------|-----------|
| [System Overview](architecture/system-overview.md) | Visión general arquitectónica | Todos |
| [Data Model](architecture/data-model.md) | Modelo de datos único | Backend devs |

### 📋 Decisiones Arquitectónicas (ADRs)
| ADR | Tema | Impacto |
|-----|------|---------|
| [001 - Unique Phone Numbers](decisions/001-unique-phone-numbers.md) | Identificadores únicos | Seguridad/UX |
| [002 - Subscription Model](decisions/002-subscription-model.md) | Modelo progresivo vs restrictivo | Business/UX |
| [003 - PDF Processing](decisions/003-pdf-processing-strategy.md) | Pre-procesamiento vs tiempo real | Performance |
| [004 - Composer Approval](decisions/004-composer-approval-workflow.md) | Aprobación manual vs automática | Calidad/Escalabilidad |
| [005 - API Design](decisions/005-api-design-philosophy.md) | API "humano-primero" | Developer Experience |
| [006 - Storage Strategy](decisions/006-storage-strategy.md) | Almacenamiento híbrido | Infrastructure |
| [007 - Mobile-First](decisions/007-mobile-first-approach.md) | Prioridad mobile musical | UX/Performance |

### 🔧 Documentación Técnica
| Documento | Especialización | Nivel |
|-----------|-----------------|-------|
| [Security Approach](technical/security-approach.md) | Confianza progresiva | Intermedio |
| [Performance Strategy](technical/performance-strategy.md) | Performance musical | Avanzado |
| [Annotation System](technical/annotation-system.md) | Frontend-first annotations | Intermedio |

### 🚀 Operaciones
| Documento | Área | Urgencia |
|-----------|------|----------|
| [Monitoring Strategy](operations/monitoring-strategy.md) | Métricas musicales | Alta |
| [Deployment Guide](operations/deployment-guide.md) | Zero-downtime musical | Alta |

### 🧪 Testing y Calidad
| Documento | Enfoque | Aplicación |
|-----------|---------|------------|
| [Testing Strategy](testing/testing-strategy.md) | Testing musical específico | Todos los devs |
| [Debugging Guide](guides/debugging-guide.md) | Debugging contextual | Troubleshooting |

### 🔌 Integraciones
| Documento | Servicio | Criticidad |
|-----------|----------|------------|
| [PayPal Integration](integrations/paypal-integration.md) | Pagos sin fricción | Alta |

### 📖 Guías de Desarrollo
| Documento | Audiencia | Propósito |
|-----------|-----------|-----------|
| [Onboarding Guide](guides/onboarding-guide.md) | Nuevos desarrolladores | Primera semana |
| [Development Workflow](guides/development-workflow.md) | Todos los devs | Proceso diario |
| [Debugging Guide](guides/debugging-guide.md) | Todos los devs | Resolución de problemas |

### 🤝 Contribución
| Documento | Audiencia | Objetivo |
|-----------|-----------|----------|
| [Contribution Guide](contributing/contribution-guide.md) | Colaboradores externos | Contribuir efectivamente |

## 🎵 Rutas de Aprendizaje por Rol

### Frontend Developer Musical
```
1. System Overview → 2. Mobile-First Approach → 3. Annotation System → 4. Performance Strategy
```

### Backend Developer Musical
```
1. System Overview → 2. Data Model → 3. API Design Philosophy → 4. Security Approach
```

### DevOps Engineer Musical
```
1. System Overview → 2. Storage Strategy → 3. Deployment Guide → 4. Monitoring Strategy
```

### Product Manager Musical
```
1. README → 2. Subscription Model → 3. Composer Approval → 4. User Research Areas
```

### QA Engineer Musical
```
1. System Overview → 2. Testing Strategy → 3. Debugging Guide → 4. Performance Strategy
```

## 🔍 Búsqueda por Tema

### Por Funcionalidad Musical
- **Anotaciones**: [Annotation System](technical/annotation-system.md), [Testing Strategy](testing/testing-strategy.md)
- **Búsqueda Musical**: [API Design](decisions/005-api-design-philosophy.md), [Performance Strategy](technical/performance-strategy.md)
- **Partituras PDF**: [PDF Processing](decisions/003-pdf-processing-strategy.md), [Storage Strategy](decisions/006-storage-strategy.md)
- **Suscripciones**: [Subscription Model](decisions/002-subscription-model.md), [PayPal Integration](integrations/paypal-integration.md)

### Por Decisión No Convencional
- **Teléfonos únicos**: [Unique Phone Numbers](decisions/001-unique-phone-numbers.md)
- **Modelo progresivo**: [Subscription Model](decisions/002-subscription-model.md)
- **API humano-primero**: [API Design Philosophy](decisions/005-api-design-philosophy.md)
- **Confianza progresiva**: [Security Approach](technical/security-approach.md)

### Por Tecnología
- **Laravel/PHP**: [Data Model](architecture/data-model.md), [Security Approach](technical/security-approach.md)
- **Frontend/JS**: [Mobile-First](decisions/007-mobile-first-approach.md), [Annotation System](technical/annotation-system.md)
- **Infrastructure**: [Storage Strategy](decisions/006-storage-strategy.md), [Deployment Guide](operations/deployment-guide.md)
- **APIs**: [API Design Philosophy](decisions/005-api-design-philosophy.md), [PayPal Integration](integrations/paypal-integration.md)

## 🚨 Documentación de Emergencia

### Problemas Críticos en Producción
1. **[Debugging Guide](guides/debugging-guide.md)** - Diagnóstico rápido
2. **[Monitoring Strategy](operations/monitoring-strategy.md)** - Métricas de salud
3. **[Deployment Guide](operations/deployment-guide.md)** - Rollback procedures

### Onboarding Urgente
1. **[Onboarding Guide](guides/onboarding-guide.md)** - Setup rápido
2. **[Development Workflow](guides/development-workflow.md)** - Proceso inmediato
3. **[System Overview](architecture/system-overview.md)** - Contexto esencial

### Research y Decisiones
1. **[ADRs por fecha](#-decisiones-arquitectónicas-adrs)** - Decisiones cronológicas
2. **[System Overview](architecture/system-overview.md)** - Justificaciones generales
3. **[Data Model](architecture/data-model.md)** - Decisiones de diseño de datos

## 📈 Roadmap de Documentación

### Documentación Existente
✅ Arquitectura fundamental  
✅ Decisiones arquitectónicas clave  
✅ Documentación técnica especializada  
✅ Guías operacionales  
✅ Procesos de desarrollo  

### Próximas Adiciones
🔄 Documentación de APIs específicas  
🔄 Guías de integración adicionales  
🔄 Documentación de microservicios  
🔄 Guías de escalabilidad  

---

**💡 Tip**: ¿No encuentras lo que buscas? Consulta el [Contribution Guide](contributing/contribution-guide.md) para proponer nueva documentación.

**📞 Ayuda**: Para dudas específicas, revisa [Debugging Guide](guides/debugging-guide.md) o contacta al equipo.
