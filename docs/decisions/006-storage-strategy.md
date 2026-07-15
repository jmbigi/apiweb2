# ADR-006: Estrategia de Almacenamiento Híbrido

## Estado
Aceptado

## Contexto
Faristol maneja diferentes tipos de contenido con distintos patrones de acceso: PDFs originales (preservación), imágenes procesadas (performance), metadatos (búsqueda), y datos de usuario (personalización).

## Decisión
Adoptar una estrategia de almacenamiento híbrido que optimiza cada tipo de contenido según su uso y criticidad.

## Filosofía Central
**"Cada tipo de dato merece su propio hogar optimizado"**

## Estrategia por Tipo de Contenido

### PDFs Originales - Preservación
**Storage**: S3/Wasabi con versioning
**Justificación**: Contenido inmutable que debe preservarse íntegramente.

#### Características
- **Durabilidad**: 99.999999999% (11 9's)
- **Acceso**: Infrecuente, solo para regenerar procesados
- **Costo**: Optimizado para almacenamiento, no para ancho de banda
- **Backup**: Replicación automática cross-region

### Imágenes Procesadas - Performance
**Storage**: CDN Edge + S3 como origin
**Justificación**: Acceso frecuente desde ubicaciones globales.

#### Estrategia de Cache
- **Edge**: 90 días (imágenes raramente cambian)
- **Browser**: 1 año con versionado por URL
- **Invalidation**: Solo cuando se reprocesa PDF original

### Metadatos - Búsqueda
**Storage**: Base de datos con índices optimizados
**Justificación**: Necesidad de búsqueda compleja y joins relacionales.

#### Optimizaciones
- **Fulltext search**: Índices en títulos y descripciones
- **Faceted search**: Índices compuestos para filtros
- **Analytics data**: Tablas separadas para no impactar búsquedas

### Datos de Usuario - Personalización
**Storage**: Base de datos principal con backup frecuente
**Justificación**: Datos críticos con alta consistencia requerida.

## Decisiones No Convencionales

### Anotaciones Híbridas
**Decisión**: Anotaciones viven primariamente en frontend, secundariamente en backend.

#### Rationale Técnico
- **LocalStorage/IndexedDB**: Performance inmediata
- **Database**: Backup y sincronización entre dispositivos
- **Sync strategy**: Eventual consistency con conflict resolution

### Múltiples Resoluciones Precomputadas
**Decisión**: Generar y almacenar múltiples resoluciones al momento de upload.

#### Trade-off Aceptado
- **Costo de storage**: 3x más espacio usado
- **Beneficio**: Cero tiempo de procesamiento en visualización
- **Justificación**: La experiencia de lectura musical no puede esperar

### Separación Geográfica por Uso
**Decisión**: Contenido estático (CDN) vs datos dinámicos (base de datos regional).

#### Distribución Estratégica
- **Contenido**: Global via CDN
- **APIs**: Regional para latencia mínima
- **Analytics**: Centralizado para agregación

## Patrones de Acceso Considerados

### Lectura Musical
**Patrón**: Acceso secuencial a páginas de partitura
**Optimización**: Preload de página siguiente

### Búsqueda de Repertorio
**Patrón**: Muchas búsquedas, pocas selecciones
**Optimización**: Cache agresivo de resultados de búsqueda

### Práctica con Anotaciones
**Patrón**: Muchas escrituras pequeñas, pocas lecturas
**Optimización**: Batching de sincronización

## Consideraciones de Costo

### Estructura de Costos
- **Storage**: ~40% del presupuesto de infraestructura
- **Bandwidth**: ~35% (CDN para imágenes)
- **Compute**: ~25% (procesamiento de PDFs)

### Optimizaciones de Costo
- **Intelligent Tiering**: Contenido raramente accedido → storage clase fría
- **Compression**: WebP para imágenes, con fallback a JPEG
- **Lifecycle policies**: Eliminación automática de versiones antiguas

## Backup y Disaster Recovery

### Estrategia de Backup
#### PDFs Originales
- **Replicación**: 3 regiones geográficas
- **Versioning**: Inmutable, nunca se sobrescribe
- **Testing**: Restore tests mensuales

#### Base de Datos
- **Snapshots**: Cada 6 horas
- **Point-in-time recovery**: 30 días de retención
- **Cross-region**: Replica de lectura en región secundaria

#### Datos de Usuario
- **Export functionality**: Los usuarios pueden exportar sus datos
- **GDPR compliance**: Delete completo cuando se solicite
- **Backup verification**: Tests automatizados de integridad

## Escalabilidad Planificada

### Crecimiento por Fases
#### Fase 1 (Actual): Single Region
- **Users**: <10K
- **Storage**: <1TB
- **Architecture**: Monolith con S3

#### Fase 2 (6-12 meses): Multi-Region
- **Users**: <100K
- **Storage**: <10TB
- **Architecture**: API Gateway + CDN global

#### Fase 3 (1-2 años): Global Scale
- **Users**: <1M
- **Storage**: <100TB
- **Architecture**: Microservices + Edge computing

## Métricas de Éxito

### Performance
- **Time to first page**: <2 segundos globally
- **Search response time**: <500ms P95
- **Upload success rate**: >99.5%

### Reliability
- **Uptime**: 99.9% (8.77 horas downtime/año máximo)
- **Data durability**: 99.999999% (pérdida <1 archivo/10M/año)
- **Backup recovery**: <1 hora RTO, <15 minutos RPO

### Cost Efficiency
- **Cost per user**: Trending down con escala
- **Storage efficiency**: >80% utilization rate
- **CDN hit rate**: >95% para contenido estático

## Consideraciones de Migración

### Exit Strategy
**Preparación**: Todos los datos deben ser exportables en formatos estándar.
**Garantía**: Los usuarios nunca pierden acceso a sus datos por decisiones técnicas.

### Vendor Independence
**Principle**: No lock-in crítico con ningún proveedor específico.
**Implementation**: Abstraction layers para storage y CDN.

---
**Fecha**: 18 de Enero, 2024  
**Autor**: Equipo de Infraestructura  
**Revisores**: [Arquitectura, DevOps, Finanzas]
