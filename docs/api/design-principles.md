# 🔌 Principios de Diseño de API - Faristol

## 🎯 Enfoque Central

La API de Faristol está diseñada para ser **intuitiva para músicos** - si alguien entiende música, debería poder entender nuestros endpoints.

## 🌟 Principios Únicos

### Nomenclatura Musical
**Decisión**: Usar terminología familiar para músicos en lugar de jerga técnica.

#### Ejemplos de Nomenclatura
- `composer` no `author`
- `music_score` no `document`
- `instruments` no `tags`
- `difficulty` no `level`
- `style` no `category`

### Contexto Preservado
**Filosofía**: Cada endpoint debe llevar suficiente contexto para ser autoexplicativo.

#### Estructura de Respuesta Contextual
```json
{
  "status": true,
  "message": "Partitura obtenida exitosamente",
  "data": {
    "music_score": {...},
    "user_context": {
      "can_annotate": true,
      "annotations_remaining": 12,
      "is_favorite": false
    }
  }
}
```

## 🔄 Patrones de Endpoints

### Patrón "Check" para Validaciones
**Uso**: Verificar estados sin modificar datos.
```
GET /api/auth/user/check-user
GET /api/auth/user/check-subscription
GET /api/auth/user/check-composer
```

**Beneficio**: Separar lectura de validación de operaciones de negocio.

### Patrón "Sync" para Operaciones Complejas
**Uso**: Operaciones que sincronizan estado entre sistemas.
```
POST /api/inapp-subscription/sync-subscribe
POST /api/music-score/sync-upload-status
```

**Justificación**: Indica que hay lógica compleja de sincronización involucrada.

### Patrón "Toggle" para Estados Binarios
**Uso**: Cambiar estado on/off de manera intuitiva.
```
GET /api/music-score/fav-music-score  # Agregar a favoritos
GET /api/music-score/remove-fav-music-score  # Remover de favoritos
```

**Particularidad**: GET para operaciones toggle (no convencional pero intuitivo).

## 🎨 Diseño de Respuestas

### Información Progresiva
**Principio**: Más información para usuarios con más privilegios.

#### Ejemplo: Información de Usuario
```json
// Usuario básico
{
  "user": {
    "name": "Juan",
    "subscription_level": 1
  }
}

// Usuario compositor
{
  "user": {
    "name": "Juan",
    "subscription_level": 1,
    "composer_profile": {...},
    "upload_permissions": {...}
  }
}
```

### Errores Educativos
**Filosofía**: Los errores deben enseñar, no solo informar.

```json
{
  "status": false,
  "message": "Has alcanzado el límite de anotaciones",
  "error_code": "ANNOTATION_LIMIT_REACHED",
  "help": {
    "current_limit": 5,
    "upgrade_for": "15 anotaciones con plan Básico",
    "upgrade_url": "/subscription/plans"
  }
}
```

## 🔐 Seguridad Integrada

### Rate Limiting Contextual
**Implementación**: Límites diferentes según la acción y el usuario.

#### Límites por Contexto
- **Búsquedas**: Sin límite (acción de lectura)
- **Uploads**: 10 por hora (acción costosa)
- **Login**: 5 por 15 minutos (seguridad)
- **API general**: 1000 por minuto (uso normal)

### Autorización Granular
**Principio**: Verificar permisos específicos, no solo autenticación.

```json
// Respuesta incluye capacidades
{
  "music_score": {...},
  "permissions": {
    "can_download": true,
    "can_annotate": true,
    "can_edit": false,
    "download_quality": "high"
  }
}
```

## 📱 Mobile-First Considerations

### Payloads Optimizados
**Decisión**: Respuestas mínimas por defecto, expansión opcional.

```
GET /api/music-score/list  // Respuesta básica
GET /api/music-score/list?expand=composer,stats  // Respuesta expandida
```

### Offline-Friendly
**Preparación**: Estructura preparada para uso offline.
- **ETags**: Para validación de cache
- **Timestamps**: Para sincronización
- **Conflict resolution**: Headers para manejo de conflictos

## 🔍 Búsqueda y Filtrado

### Filtros Musicalmente Intuitivos
**Diseño**: Parámetros que un músico usaría naturalmente.

```
GET /api/music-score/list?
  composer=Bach&
  instruments[]=piano&
  difficulty=intermediate&
  style=baroque&
  key_signature=C+Major
```

### Búsqueda Fuzzy por Defecto
**Decisión**: Búsqueda tolerante a errores por defecto.
**Justificación**: Nombres de compositores y obras pueden tener variaciones.

### Sugerencias Inteligentes
**Feature**: Autocompletado basado en contenido real.
```
GET /api/search/suggestions?q=bach+inv
// Retorna: ["Bach Invention No. 1", "Bach Invention No. 2", ...]
```

## 📊 Analytics Integrados

### Tracking Transparente
**Principio**: Analytics que benefician al usuario, no solo al negocio.

```json
{
  "music_score": {...},
  "analytics": {
    "popularity_score": 4.7,
    "difficulty_rating": "community_verified",
    "usage_in_schools": 23
  }
}
```

### Métricas Educativas
**Enfoque**: Datos que ayudan a profesores y estudiantes.
- **Difficulty consensus**: Dificultad validada por comunidad
- **Educational usage**: Uso en instituciones
- **Learning path**: Progresión sugerida de partituras

## 🚀 Performance Patterns

### Cache Inteligente
**Estrategia**: Cache basado en naturaleza del contenido.

#### Políticas de Cache
- **Partituras**: Cache largo (contenido estático)
- **Búsquedas**: Cache medio (datos semi-estáticos)
- **Usuario**: Sin cache (datos dinámicos)
- **Analytics**: Cache corto (actualizaciones frecuentes)

### Lazy Loading Estructurado
**Implementación**: Carga progresiva predecible.
```
GET /api/music-score/{id}  // Metadatos básicos
GET /api/music-score/{id}/content  // Contenido pesado
GET /api/music-score/{id}/related  // Contenido relacionado
```

## 🔮 Preparación Futura

### API Versioning Strategy
**Plan**: Sin versionado hasta que sea absolutamente necesario.
**Preparación**: Headers para versionado futuro.
```
API-Version: 1.0
Content-Type: application/json
```

### GraphQL Readiness
**Consideración**: Estructura actual compatible con GraphQL futuro.
**Criterio**: Migrar cuando el 80% de clientes necesiten queries complejas.

### Webhook Integration
**Planificación**: Sistema de eventos para integraciones externas.
**Casos de uso**: Notificaciones de nuevas partituras, cambios de usuario.

---
**Relacionado**: [API Design Philosophy](../decisions/005-api-design-philosophy.md) | [Security Approach](../technical/security-approach.md)
