# 💳 Subscription Endpoints - Faristol API

## 🎯 Filosofía de Suscripciones Musicales

Las suscripciones en Faristol deben **preservar el flujo musical**. Un músico que decide upgradear durante la práctica no debe perder momentum - la transición debe ser invisible y el acceso inmediato.

## 🌟 Decisiones de Suscripción Únicas

### Modelo Progresivo vs Restrictivo
**Filosofía fundamental**: "La música debe ser accesible, las herramientas profesionales justifican el pago"

#### Enfoque No Convencional
- **Free**: Acceso completo a partituras + herramientas básicas
- **Basic**: Sin publicidad + herramientas mejoradas + más anotaciones
- **Premium**: Herramientas profesionales + anotaciones ilimitadas + favoritos

### Activación Sin Fricción
**Principio**: La suscripción debe activarse sin interrumpir la sesión musical.

#### Características de Activación
- **Immediate access**: Beneficios disponibles instantáneamente
- **Context preservation**: Mantener posición exacta en partitura
- **Session continuity**: Sin recargas o re-logins necesarios
- **Cross-device sync**: Sincronización inmediata entre dispositivos

## 📋 Endpoints de Verificación

### Verificación de Estado de Suscripción
```http
GET /api/auth/user/check-subscription
```

#### Información Contextual Musical
```json
{
  "status": true,
  "data": {
    "subscription_level": 0,
    "plan_name": "Free",
    "is_paid": false,
    "features": {
      "annotations_limit": 5,
      "annotations_used": 3,
      "annotations_remaining": 2,
      "has_favorites": false,
      "has_high_quality_pdf": false,
      "advertisement_free": false
    },
    "upgrade_suggestions": {
      "next_level": "Basic",
      "next_level_benefits": ["15 anotaciones", "Sin publicidad", "PDF alta calidad"],
      "price": "€4.99/mes"
    },
    "trial_status": {
      "premium_trials_used": 1,
      "premium_trials_remaining": 2,
      "can_use_trial": true
    }
  }
}
```

#### Decisiones de Diseño
- **Non-blocking check**: Verificación que no interrumpe práctica
- **Contextual limits**: Información específica sobre límites actuales
- **Upgrade hints**: Sugerencias contextuales para mejora
- **Trial availability**: Estado de pruebas premium disponibles

### Verificación de Estado de Compositor
```http
GET /api/auth/user/check-composer
```

#### Información de Compositor
- **Application status**: Estado de solicitud para ser compositor
- **Upload permissions**: Permisos actuales de subida de contenido
- **Content statistics**: Estadísticas de contenido ya subido
- **Community rating**: Rating de la comunidad para obras subidas

## 🎁 Sistema de Pruebas Premium

### Aplicar Prueba Premium
```http
POST /api/premium-trial/apply-trial
```

#### Lógica de Pruebas Única
**Decisión**: 3 pruebas de 31 días en total (no consecutivas)

#### Validaciones Específicas
- **Active subscription check**: No permitir si ya tiene suscripción activa
- **Trial limit validation**: Máximo 3 pruebas por usuario/teléfono
- **Educational exceptions**: Consideraciones especiales para instituciones
- **Geographic restrictions**: Limitaciones por región si aplican

#### Respuesta de Activación
```json
{
  "status": true,
  "message": "Prueba premium activada por 31 días",
  "data": {
    "trial_start": "2024-01-15T10:00:00Z",
    "trial_end": "2024-02-15T10:00:00Z",
    "features_unlocked": [
      "Anotaciones ilimitadas",
      "Sistema de favoritos",
      "Sin publicidad",
      "PDF alta calidad"
    ],
    "remaining_trials": 2,
    "immediate_benefits": {
      "annotations_limit": "unlimited",
      "favorites_enabled": true,
      "ad_free": true
    }
  }
}
```

## 💳 Integración PayPal Musical

### Iniciar Suscripción
```http
POST /api/inapp-subscription/sync-subscribe
```

#### Parámetros Musicales
```json
{
  "type": 2,                    // 1=Basic, 2=Premium
  "return_url": "/practice/continue",  // Volver al contexto musical
  "cancel_url": "/practice/score/123", // Mantener contexto si cancela
  "musical_context": {
    "current_score_id": 123,
    "current_page": 5,
    "practice_session_id": "uuid"
  }
}
```

#### Preservación de Contexto Musical
- **Session preservation**: Mantener sesión de práctica durante pago
- **Context restoration**: Volver exactamente donde estaba el músico
- **State synchronization**: Sincronizar anotaciones pendientes antes de upgrade
- **Device coordination**: Coordinar upgrade entre múltiples dispositivos

### Confirmación de Suscripción
```http
POST /api/inapp-subscription/confirm-subscription
```

#### Activación Instantánea
- **Immediate activation**: Beneficios disponibles sin delay
- **Cross-device sync**: Propagación inmediata a todos los dispositivos
- **Session enhancement**: Mejora inmediata de sesión actual
- **Context restoration**: Vuelta perfecta al punto de práctica

## 🎓 Suscripciones Educacionales

### Gestión Institucional
```http
GET /api/subscription/institutional-plans
POST /api/subscription/bulk-subscribe
```

#### Características Educacionales
- **Bulk management**: Gestión masiva de cuentas estudiantiles
- **Academic calendar sync**: Suscripciones alineadas con períodos académicos
- **Educational pricing**: Descuentos especiales para instituciones
- **Teacher oversight**: Capacidades de supervisión para educadores

#### Consideraciones Especiales
- **FERPA compliance**: Cumplimiento de regulaciones educativas
- **Parental permissions**: Gestión de permisos para menores
- **Usage analytics**: Métricas específicas para evaluación educativa
- **Content filtering**: Restricciones de contenido por edad/nivel

## 📊 Analytics de Suscripción Musical

### Métricas de Conversión
```http
GET /api/subscription/conversion-analytics
```

#### KPIs Musicales Específicos
- **Practice-to-pay conversion**: % usuarios que upgraden durante práctica
- **Feature-triggered upgrades**: Conversiones por límite alcanzado
- **Trial-to-paid conversion**: Conversión de pruebas a suscripciones pagas
- **Educational vs individual**: Patrones de conversión por tipo de usuario

### Análisis de Uso
```http
GET /api/subscription/usage-analytics
```

#### Métricas de Valor Musical
- **Feature utilization**: Uso real de funcionalidades premium
- **Practice enhancement**: Correlación suscripción vs tiempo de práctica
- **Educational impact**: Impacto en progreso musical mensurable
- **Retention correlation**: Retención vs engagement musical

### Estadísticas de Uso de Partituras por Usuario
```http
GET /api/subscription/user-score-usage
```

#### Parámetros Específicos
```
?user_id=123                    // ID del usuario
?score_id=456                   // ID de partitura específica (opcional)
?date_from=2024-01-01          // Fecha inicio del período
?date_to=2024-01-31            // Fecha fin del período
?metrics=views,time,annotations // Métricas específicas
```

#### Respuesta de Estadísticas
```json
{
  "status": true,
  "data": {
    "user_id": 123,
    "period": {
      "from": "2024-01-01",
      "to": "2024-01-31"
    },
    "scores_accessed": [
      {
        "score_id": 456,
        "score_title": "Bach - Invention No. 1",
        "total_views": 15,
        "total_time_minutes": 180,
        "unique_sessions": 8,
        "annotations_created": 12,
        "pages_visited": [1, 2, 3],
        "last_accessed": "2024-01-30T14:30:00Z",
        "practice_pattern": {
          "most_active_hour": 19,
          "average_session_duration": 22,
          "return_frequency": "daily"
        }
      }
    ],
    "aggregate_stats": {
      "total_practice_time": 540,
      "total_scores_practiced": 8,
      "total_annotations": 45,
      "subscription_value_score": 8.5
    }
  }
}
```

#### Endpoints de Registro de Uso (Backend)
**Estos son los endpoints que registran el uso para generar las estadísticas**:

```http
POST /api/analytics/track-score-view
POST /api/analytics/track-practice-session
POST /api/analytics/track-annotation-usage
```

#### Parámetros de Tracking
```json
// Track Score View
{
  "user_id": 123,
  "score_id": 456,
  "page_number": 1,
  "device_type": "tablet",
  "session_id": "uuid",
  "timestamp": "2024-01-15T10:30:00Z"
}

// Track Practice Session
{
  "user_id": 123,
  "score_id": 456,
  "session_start": "2024-01-15T10:00:00Z",
  "session_end": "2024-01-15T10:45:00Z",
  "pages_navigated": [1, 2, 3, 2, 1],
  "annotations_created": 3,
  "device_type": "tablet"
}
```

## 🚨 Gestión de Problemas de Suscripción

### Sincronización de Estado
```http
POST /api/subscription/sync-status
GET /api/subscription/payment-status/{subscription_id}
```

#### Resolución de Conflictos
- **PayPal sync issues**: Resolución de desincronización con PayPal
- **Multi-device conflicts**: Conflictos de estado entre dispositivos
- **Session recovery**: Recuperación de sesiones interrumpidas por problemas de pago
- **Manual activation**: Activación manual por soporte cuando sea necesario

### Cancelación Musical
```http
POST /api/subscription/cancel
```

#### Cancelación Sin Fricción
- **Immediate effect**: Cancelación efectiva inmediata o al final del período
- **Feature graceful degradation**: Degradación suave de funcionalidades
- **Data preservation**: Preservación de anotaciones y favoritos
- **Reactivation simplicity**: Reactivación simple sin pérdida de datos

## 🔮 Funcionalidades Futuras

### Suscripciones Flexibles
**Evaluación**: Suscripciones adaptativas basadas en uso real.

#### Conceptos en Evaluación
- **Usage-based pricing**: Precios basados en uso real de funcionalidades
- **Seasonal subscriptions**: Suscripciones alineadas con calendario académico
- **Family plans**: Planes familiares para múltiples músicos
- **Ensemble subscriptions**: Suscripciones grupales para conjuntos musicales

### AI-Powered Recommendations
**Visión**: Sugerencias de upgrade basadas en patrones musicales.

#### Inteligencia Predictiva
- **Practice pattern analysis**: Análisis de patrones para sugerir upgrades
- **Educational progression**: Sugerencias basadas en progreso musical
- **Feature prediction**: Predicción de qué funcionalidades el usuario necesitará
- **Optimal timing**: Timing óptimo para ofrecer upgrades

---
**Relacionado**: [Subscription Model](../decisions/002-subscription-model.md) | [PayPal Integration](../integrations/paypal-integration.md)
