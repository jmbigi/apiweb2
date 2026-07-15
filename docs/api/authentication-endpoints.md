# 🔐 Authentication Endpoints - Faristol API

## 🎯 Filosofía de Autenticación Musical

La autenticación en Faristol debe ser **invisible durante la práctica musical**. Un músico no debería pensar en tokens o sesiones mientras está inmerso en su música.

## 🌟 Decisiones de Autenticación Únicas

### Sesiones de Práctica Extendidas
**Problema**: Los músicos practican por horas sin interrupciones.
**Solución**: Tokens de larga duración con renovación transparente.

#### Características de Sesiones Musicales
- **Duración inicial**: 30 días por token
- **Renovación automática**: Cada 7 días de uso activo
- **Preservación de estado**: Mantener contexto musical durante renovación
- **Multi-device sync**: Sincronización entre dispositivos de práctica

### Verificación No Intrusiva
**Principio**: Verificar estado sin interrumpir flujo musical.

#### Patrón "Check" para Autenticación
- **Propósito**: Validar permisos sin requerir re-autenticación
- **Implementación**: Endpoints dedicados para verificación de estado
- **Beneficio**: Músicos pueden continuar práctica sin interrupciones

## 📋 Endpoints de Autenticación

### Registro Musical
```http
POST /api/auth/user/signup
```

#### Decisiones Específicas de Registro
- **Teléfono obligatorio**: Identificador único para recovery
- **Formato internacional**: `(+34)123456789` estandarizado
- **Rol automático**: Asignación automática de rol 'musician'
- **Email verification**: Verificación sin bloquear uso inmediato

#### Particularidades del Flujo
- **Validación progresiva**: Permitir uso básico antes de verificación completa
- **Onboarding musical**: Flujo adaptado para músicos nuevos
- **Device fingerprinting**: Reconocimiento de dispositivos musicales comunes

### Login Contextual
```http
POST /api/auth/login
```

#### Características Únicas
- **Context preservation**: Preservar intención de acceso durante login
- **Device recognition**: Identificar tablets/dispositivos musicales
- **Educational flow**: Flujo especial para cuentas institucionales
- **Practice session restore**: Restaurar sesión de práctica interrumpida

#### Respuesta Enriquecida
```json
{
  "status": true,
  "data": {
    "user": {
      "id": 123,
      "name": "Músico",
      "roles": ["musician"],
      "subscription_level": 0,
      "musical_context": {
        "last_score_accessed": 456,
        "practice_session_active": false,
        "annotation_limits": {
          "used": 3,
          "total": 5
        }
      }
    },
    "token": "...",
    "expires_at": "2024-02-15T10:00:00Z"
  }
}
```

### Verificación de Estado
```http
GET /api/auth/user/check-user
```

#### Propósito Musical
- **Non-blocking verification**: Verificar sin interrumpir práctica
- **Context awareness**: Incluir información relevante para decisiones musicales
- **Performance optimized**: Respuesta ultra-rápida para no afectar UX

#### Información Contextual Incluida
- **Subscription status**: Nivel actual y límites
- **Session state**: Estado de sesión de práctica
- **Device permissions**: Permisos específicos del dispositivo
- **Educational context**: Información institucional si aplica

### Recovery Musical
```http
POST /api/auth/user/request-otp
POST /api/auth/user/verify-otp
POST /api/auth/user/change-password
```

#### Flujo de Recovery Adaptado
- **Musical context preservation**: Mantener contexto durante recovery
- **Multiple recovery methods**: Email primario, teléfono como backup
- **Educational institution support**: Consideraciones especiales para escuelas
- **Practice session protection**: No perder trabajo durante recovery

## 🎨 Autenticación para Contextos Específicos

### Autenticación Educacional
**Consideraciones**: Instituciones tienen necesidades únicas.

#### Características Educacionales
- **Bulk account management**: Gestión masiva de cuentas estudiantiles
- **Shared device support**: Soporte para dispositivos compartidos en aulas
- **Educational SSO**: Integración con sistemas institucionales
- **Parent/guardian permissions**: Consideraciones para menores

### Autenticación de Compositores
**Flujo especial**: Usuarios que también son compositores verificados.

#### Dual Identity Management
- **Role accumulation**: Roles que se acumulan (musician + composer)
- **Elevated permissions**: Permisos adicionales para upload de contenido
- **Verification badge**: Indicadores de verificación en respuestas
- **Content ownership**: Gestión de ownership de partituras propias

## 🔧 Particularidades Técnicas

### Token Management Musical
**Estrategia**: Tokens optimizados para patrones de uso musical.

#### Token Characteristics
- **Long-lived by default**: 30 días para reducir interrupciones
- **Auto-renewal logic**: Renovación basada en actividad musical
- **Device binding**: Opcional para dispositivos de práctica principales
- **Educational institution tokens**: Configuración especial para escuelas

### Session Persistence Musical
**Implementación**: Persistencia que entiende contexto musical.

#### Session State Tracking
- **Current score**: Última partitura accedida
- **Page position**: Posición exacta en partitura multipágina
- **Annotation state**: Estado de anotaciones no sincronizadas
- **Practice timing**: Tiempo de sesión actual para analytics

### Multi-Device Musical Authentication
**Enfoque**: Sincronización entre dispositivos de práctica.

#### Device Synchronization
- **Primary device**: Tablet de práctica principal
- **Secondary devices**: Móvil para búsquedas, desktop para gestión
- **Cross-device state**: Sincronización de posición y anotaciones
- **Conflict resolution**: Manejo de cambios simultáneos entre dispositivos

## 🚨 Security Considerations Musicales

### Rate Limiting Musical
**Balance**: Seguridad vs fluidez de práctica musical.

#### Login Rate Limits
- **Standard attempts**: 5 intentos por 15 minutos
- **Educational exceptions**: Límites relajados para IPs institucionales
- **Device recognition**: Límites más permisivos para dispositivos conocidos
- **Recovery context**: Límites especiales durante recovery de sesión

### Educational Compliance
**Consideraciones**: Cumplimiento para uso en instituciones educativas.

#### Compliance Features
- **FERPA compliance**: Protección de datos estudiantiles
- **Parental controls**: Gestión de permisos para menores
- **Institutional oversight**: Capacidades de monitoreo para administradores
- **Data retention**: Políticas de retención adaptadas a calendarios académicos

### Fraud Detection Musical
**Patrones**: Detección de uso sospechoso específica musical.

#### Musical Usage Patterns
- **Practice session anomalies**: Sesiones anormalmente largas o frecuentes
- **Geographic impossibilities**: Accesos simultáneos desde ubicaciones distantes
- **Content access patterns**: Descarga masiva inconsistente con uso musical
- **Educational vs personal use**: Patrones que indican uso comercial no autorizado

---
**Relacionado**: [Unique Phone Numbers](../decisions/001-unique-phone-numbers.md) | [Security Approach](../technical/security-approach.md)
