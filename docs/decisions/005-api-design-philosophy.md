# ADR-005: Filosofía de Diseño de API

## Estado
Aceptado

## Contexto
La API de Faristol debe servir tanto a la aplicación web actual como a futuras aplicaciones móviles, mientras mantiene simplicidad para desarrolladores externos que quieran integrarse.

## Decisión
Adoptar un diseño de API **"humano-primero"** que prioriza claridad y facilidad de uso sobre pureza REST estricta.

## Filosofía Central
**"Si un músico puede entender el endpoint, está bien diseñado"**

## Principios de Diseño

### URLs Descriptivas sobre REST Puro
**Decisión**: Preferir claridad descriptiva sobre adherencia estricta a REST.

#### Ejemplos de Decisiones
```
❌ REST puro: GET /api/users/{id}/music-scores/{score_id}/favorites
✅ Nuestro enfoque: GET /api/music-score/fav-music-score?music_score_id={id}
```

**Justificación**: Los desarrolladores entienden inmediatamente qué hace el endpoint.

### Respuestas Consistentes pero Flexibles
**Estructura Base**:
```json
{
  "status": boolean,
  "message": "string descriptivo",
  "data": {...}
}
```

**Particularidad**: El campo `message` siempre está presente y es legible por humanos.

### Parámetros Intuitivos
**Decisión**: Nombres de parámetros que un músico entendería.
- `composer_id` en lugar de `author_id`
- `difficulty` en lugar de `level`
- `instruments[]` en lugar de `tags[]`

## Decisiones No Convencionales

### Múltiples Métodos para Misma Funcionalidad
**Problema**: Diferentes clientes prefieren diferentes patrones.
**Solución**: Ofrecer múltiples formas de hacer lo mismo.

#### Ejemplo: Favoritos
```
GET /api/music-score/fav-music-score?music_score_id=1
POST /api/favorites/toggle {music_score_id: 1}
```

**Beneficio**: Flexibilidad para diferentes casos de uso.

### Query Parameters sobre Path Parameters
**Decisión**: Preferir query params para filtros y opciones.
**Justificación**: Más fácil de construir dinámicamente en frontends.

### Estados en Español para Responses
**Decisión**: Mensajes de respuesta en español cuando sea apropiado.
**Justificación**: La audiencia principal habla español.

## Patrones Específicos de Faristol

### Endpoints de "Check"
**Patrón**: `/check-{resource}` para validaciones.
```
GET /api/auth/user/check-user
GET /api/auth/user/check-subscription
GET /api/auth/user/check-composer
```

**Beneficio**: Separar validaciones de operaciones principales.

### Endpoints de Sincronización
**Patrón**: `/sync-{action}` para operaciones complejas.
```
POST /api/inapp-subscription/sync-subscribe
POST /api/composer/sync-request-status
```

**Justificación**: Indica que es una operación que sincroniza estado.

### Paginación Inteligente
**Decisión**: Paginación adaptativa según el contenido.
- **Partituras**: 20 por página (imágenes pesadas)
- **Compositores**: 50 por página (solo texto)
- **Búsquedas**: 15 por página (relevancia)

## Manejo de Errores Educativo

### Códigos de Error Específicos
```json
{
  "status": false,
  "message": "Has agotado tus pruebas premium gratuitas",
  "error_code": "TRIAL_EXHAUSTED",
  "help": "Puedes suscribirte a un plan premium para continuar"
}
```

### Errores de Validación Detallados
```json
{
  "status": false,
  "message": "Error de validación",
  "errors": {
    "telephone": ["El formato debe ser (+34)123456789"]
  }
}
```

## Versionado Pragmático

### Sin Versionado de URL
**Decisión**: No versionar en URL (por ahora).
**Justificación**: Flexibilidad para evolucionar sin romper integraciones.

### Versionado por Headers (Futuro)
**Preparación**: Header `API-Version` para futuras versiones.
**Beneficio**: Transiciones suaves entre versiones.

## Autenticación Flexible

### Múltiples Métodos Soportados
- **Bearer Token**: Para aplicaciones SPA
- **Cookie Session**: Para aplicaciones web tradicionales
- **API Key**: Para integraciones de terceros (futuro)

### Tokens con Contexto
**Decisión**: Los tokens incluyen información de suscripción.
**Beneficio**: Menos roundtrips para verificar permisos.

## Consideraciones de Performance

### Respuestas Mínimas por Defecto
**Principio**: Solo devolver datos necesarios por defecto.
**Expansión**: Parámetro `?expand=` para datos adicionales.

### Cache Headers Inteligentes
- **Contenido estático**: Cache largo (1 año)
- **Datos de usuario**: Sin cache
- **Listas públicas**: Cache medio (1 hora)

## Documentación Auto-Generada

### Swagger/OpenAPI Integration
**Decisión**: Documentación generada automáticamente desde código.
**Beneficio**: Documentación siempre actualizada.

### Ejemplos Realistas
**Principio**: Todos los ejemplos usan datos musicales reales.
**Beneficio**: Desarrolladores entienden el contexto inmediatamente.

## Métricas de Éxito

### Developer Experience
- **Time to first call**: < 5 minutos desde documentación
- **Error rate**: < 2% en llamadas válidas
- **Support tickets**: Trending down sobre tiempo

### Performance
- **Response time**: P95 < 200ms para endpoints principales
- **Availability**: 99.9% uptime
- **Rate limit hits**: < 1% de todas las llamadas

## Evolución Futura

### GraphQL Consideration
**Evaluación**: Considerar GraphQL para casos complejos.
**Criterio**: Cuando REST se vuelva demasiado verboso.

### Webhooks para Eventos
**Planificación**: Sistema de webhooks para integraciones externas.
**Casos de uso**: Notificaciones de nuevas partituras, cambios de suscripción.

### Real-time Updates
**Visión**: WebSockets para colaboración en tiempo real.
**Preparación**: Arquitectura actual compatible con adición de WebSockets.

---
**Fecha**: 16 de Enero, 2024  
**Autor**: Equipo de API  
**Revisores**: [Frontend, Mobile, DevRel]
