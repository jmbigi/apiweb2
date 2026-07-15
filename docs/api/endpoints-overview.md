# 🔌 Endpoints Overview - Faristol API

## 🎯 Filosofía de Endpoints

Los endpoints de Faristol están diseñados para **reflejar el pensamiento musical**, no solo patrones REST estándar. Un músico debería poder intuir qué hace cada endpoint sin leer documentación técnica.

## 🌟 Patrones Únicos de Diseño

### Nomenclatura Musical vs Técnica
**Decisión**: Usar terminología que los músicos reconocen naturalmente.

#### Ejemplos de Nomenclatura Musical
```
✅ /api/music-score/list           (músicos buscan "partituras")
❌ /api/documents/list             (genérico, no musical)

✅ /api/composer/get-composers     (término musical específico)
❌ /api/authors/list               (ambiguo)

✅ /api/music-score/fav-music-score    (acción musical clara)
❌ /api/scores/favorite                (menos descriptivo)
```

### Endpoints "Check" para Validaciones
**Patrón único**: Separar validaciones de operaciones para claridad musical.

#### Filosofía del Patrón "Check"
- **Propósito**: Verificar estados sin modificar datos
- **Beneficio**: Músicos pueden validar permisos antes de actuar
- **Implementación**: GET requests que retornan estado actual

### Endpoints "Sync" para Operaciones Complejas
**Patrón específico**: Operaciones que coordinan múltiples sistemas.

#### Casos de Uso "Sync"
- **Suscripciones**: Sincronizar estado PayPal con sistema local
- **Anotaciones**: Sincronizar datos frontend con backend
- **Contenido**: Sincronizar uploads con procesamiento

## 📋 Categorías de Endpoints

### 🔐 Autenticación Musical
**Enfoque**: Autenticación que entiende contextos de uso musical.

#### Endpoints de Autenticación
- `POST /api/auth/user/signup` - Registro con validación musical específica
- `POST /api/auth/login` - Login con preservación de contexto musical
- `POST /api/auth/logout` - Logout que preserva trabajo no guardado
- `GET /api/auth/user/check-user` - Verificación de estado sin interrumpir flujo

#### Particularidades de Autenticación
- **Sesiones largas**: Tokens optimizados para práctica musical extendida
- **Múltiples dispositivos**: Sincronización entre tablet/móvil/desktop
- **Verificación no intrusiva**: Checks que no interrumpen práctica
- **Recovery musical**: OTP por email preservando contexto de uso

### 🎵 Gestión de Partituras
**Core del sistema**: Endpoints optimizados para flujo musical.

#### Endpoints de Partituras
- `GET /api/music-score/list` - Listado con filtros musicales específicos
- `GET /api/music-score/get-music-score` - Obtener partitura con contexto de usuario
- `POST /api/music-score/upload-music-score` - Upload con procesamiento musical
- `GET /api/music-score/getPdfContent` - Contenido optimizado por dispositivo

#### Decisiones No Convencionales
- **Filtros musicales**: Parámetros como `difficulty`, `instruments[]`, `key_signature`
- **Contexto de usuario**: Respuestas incluyen permisos y límites actuales
- **Optimización por dispositivo**: Contenido adaptado a tablet/móvil
- **Preloading inteligente**: Sugerencias de próximas páginas

### 🎭 Sistema de Compositores
**Workflow único**: Gestión de compositores históricos vs modernos.

#### Endpoints de Compositores
- `GET /api/composer/get-composers` - Listado con filtros históricos/contemporáneos
- `POST /api/composer/request-composer-status` - Solicitud para ser compositor
- `POST /api/composer/sync-request-status` - Sincronización de estado de solicitud

#### Particularidades del Sistema
- **Separación histórica**: Compositores clásicos vs usuarios-compositores
- **Proceso de aprobación**: Workflow manual con criterios musicales
- **Verificación cultural**: Validación de autenticidad musical

### 💳 Suscripciones Musicales
**Sistema progresivo**: Suscripciones que respetan flujo musical.

#### Endpoints de Suscripción
- `GET /api/auth/user/check-subscription` - Estado sin interrumpir práctica
- `POST /api/inapp-subscription/sync-subscribe` - Suscripción preservando contexto
- `POST /api/premium-trial/apply-trial` - Pruebas que no rompen flujo

#### Enfoque Musical Específico
- **Verificación no intrusiva**: Checks que no interrumpen sesiones de práctica
- **Activación inmediata**: Acceso instantáneo sin reiniciar sesión
- **Preservación de contexto**: Vuelta al punto exacto de práctica
- **Trials musicales**: Múltiples pruebas para evaluación real

## 🎨 Decisiones de Diseño Únicas

### Respuestas Contextualmente Enriquecidas
**Principio**: Cada respuesta debe incluir contexto suficiente para decisiones musicales.

#### Estructura de Respuesta Musical
```json
{
  "status": true,
  "message": "Mensaje claro para músicos",
  "data": {
    // Datos principales
  },
  "musical_context": {
    "user_permissions": {},
    "subscription_limits": {},
    "educational_context": {}
  }
}
```

### GET para Operaciones Toggle
**Decisión controversial**: Usar GET para algunas operaciones de cambio de estado.

#### Justificación Musical
- **Simplicidad**: Músicos pueden bookmarkear operaciones comunes
- **Velocidad**: Sin necesidad de construir POST requests complejos
- **Intuitividad**: URLs que se explican a sí mismas

#### Ejemplos de Toggle GET
- `GET /api/music-score/fav-music-score?music_score_id=1` - Agregar favorito
- `GET /api/music-score/remove-fav-music-score?music_score_id=1` - Remover favorito

### Parámetros Musicalmente Intuitivos
**Enfoque**: Parámetros que un músico entendería sin documentación.

#### Ejemplos de Parámetros Musicales
```
?composer=Bach                    (no author_id=123)
?difficulty=intermediate          (no level=2)
?instruments[]=piano&instruments[]=violin  (no tags[])
?key_signature=C+Major           (terminología musical estándar)
?time_signature=4/4              (notación musical familiar)
```

## 🔍 Patterns de Búsqueda Musical

### Búsqueda Tolerante a Errores
**Implementación**: Búsqueda que entiende variaciones musicales comunes.

#### Inteligencia de Búsqueda
- **Variaciones de nombres**: "Bach" encuentra "J.S. Bach", "Johann Sebastian Bach"
- **Títulos en múltiples idiomas**: "Für Elise" encuentra "Para Elisa"
- **Abreviaciones musicales**: "Op." encuentra "Opus"
- **Búsqueda fonética**: Tolerancia a errores de pronunciación

### Filtros Musicales Avanzados
**Diseño**: Filtros que reflejan cómo músicos realmente categoriza música.

#### Filtros Disponibles
- **Por período**: Barroco, Clásico, Romántico, Contemporáneo
- **Por dificultad técnica**: Evaluada por comunidad musical
- **Por uso educativo**: Método, ejercicio, repertorio, examen
- **Por contexto cultural**: Tradición musical, región geográfica

## 📊 Rate Limiting Musical

### Límites Contextuales
**Filosofía**: Rate limiting que entiende patrones de uso musical.

#### Límites por Tipo de Acción
```
Búsquedas: Sin límite (exploración musical debe ser libre)
Uploads: 10/hora (procesamiento intensivo)
Login attempts: 5/15min (seguridad estándar)
API general: 1000/min (uso intensivo durante práctica)
Anotaciones: Sin límite (parte core de la experiencia)
```

### Excepciones Educacionales
**Consideración**: Instituciones educativas tienen patrones únicos.

#### Ajustes para Educación
- **Aulas**: Rate limiting relajado para IP institucionales
- **Exámenes**: Ventanas temporales con límites especiales
- **Horarios académicos**: Ajustes automáticos por calendario educativo

## 🚨 Manejo de Errores Musical

### Errores Educativos
**Principio**: Los errores deben enseñar, no solo informar problemas.

#### Tipos de Errores Musicales
```json
{
  "status": false,
  "error_code": "ANNOTATION_LIMIT_REACHED",
  "message": "Has alcanzado el límite de 5 anotaciones",
  "help": {
    "suggestion": "Puedes upgradearte para anotaciones ilimitadas",
    "upgrade_url": "/subscription/plans",
    "current_usage": "5/5 anotaciones utilizadas"
  }
}
```

### Recovery Automático
**Implementación**: Sistemas de recovery que no interrumpen práctica musical.

#### Estrategias de Recovery
- **Offline graceful**: Funcionalidad reducida sin conexión
- **Auto-retry**: Reintentos automáticos para operaciones críticas
- **Fallback content**: Contenido alternativo si falla el preferido
- **State preservation**: Preservar trabajo del usuario durante errores

---
**Relacionado**: [API Design Philosophy](../decisions/005-api-design-philosophy.md) | [Security Approach](../technical/security-approach.md)
