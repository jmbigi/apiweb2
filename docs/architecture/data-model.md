# 🗄️ Modelo de Datos - Faristol

## 🎯 Filosofía del Modelo

El modelo de datos de Faristol está diseñado para **flexibilidad evolutiva**. Los usuarios pueden cambiar de rol, las partituras pueden evolucionar en complejidad, y el sistema debe adaptarse sin perder información histórica.

## 🌟 Decisiones de Modelado Únicas

### 1. Usuarios con Identidad Múltiple

**Decisión**: Un usuario puede tener múltiples "identidades" sin crear cuentas separadas.

**Implementación**:
- Usuario base con datos personales
- Roles que se acumulan (músico + compositor + admin)
- Perfil de compositor separado pero vinculado

**Beneficio**: Preserva la experiencia unificada mientras permite especializaciones.

### 2. Teléfonos como Identificadores Auxiliares

**Decisión**: Los teléfonos son únicos y obligatorios en formato internacional.

**Justificación**:
- Facilita recuperación de cuentas
- Previene cuentas duplicadas
- Permite futuras funcionalidades (SMS, WhatsApp)

**Formato**: `(+34)123456789` - Código país + número sin espacios ni guiones

### 3. Suscripciones Superpuestas

**Decisión**: Permitir múltiples suscripciones simultáneas, la más alta prevalece.

**Casos de uso**:
- Usuario paga Basic, recibe gift de Premium
- Prueba Premium mientras tiene Basic activo
- Transiciones sin pérdida de servicio

## 🔄 Relaciones Complejas

### Usuario ↔ Compositor
```
Usuario (1) ←→ (0..1) Composer
Usuario (1) ←→ (0..*) ComposerRequest
```

**Particularidad**: Un usuario puede solicitar ser compositor múltiples veces (rechazos, cambios de criterios).

### Partitura ↔ Instrumentos
```
MusicScore (1) ←→ (*) MusicScoreInstruments ←→ (*) Instrument
```

**Decisión**: Relación many-to-many porque una partitura puede ser adaptable a múltiples instrumentos.

### Usuario ↔ Anotaciones (Implícita)
```
Usuario (1) ←→ (*) AnnotationSessions (frontend)
AnnotationSession ←→ (*) Annotations (frontend)
```

**Particularidad**: Las anotaciones viven en el frontend y se sincronizan opcionalmente.

## 📊 Datos Calculados vs Almacenados

### Almacenados (Performance)
- Contadores de descargas por partitura
- Estadísticas de visualización agregadas
- Niveles de suscripción actuales

### Calculados (Flexibilidad)
- Límites de anotaciones por usuario
- Permisos dinámicos por rol
- Estado de elegibilidad para pruebas

## 🔒 Decisiones de Privacidad

### Datos Sensibles Encriptados
- Números de teléfono completos
- Datos de pagos (cuando aplicable)
- Información biográfica detallada de compositores

### Datos Anónimos Agregados
- Patrones de uso por región
- Estadísticas de popularidad de instrumentos
- Métricas de engagement sin identificación personal

### Soft Deletes Estratégicos
- Usuarios: Soft delete para preservar referencias
- Partituras: Hard delete solo por admin, soft delete por usuario
- Anotaciones: Hard delete inmediato (propiedad del usuario)

## 🎨 Patrones de Datos Únicos

### 1. Estado Evolutivo de Compositores
```
ComposerRequest → (pendiente) → (aprobado) → Composer + Role
                              → (rechazado) → (nueva solicitud posible)
```

### 2. Pruebas Premium Inteligentes
```
PremiumTrial: {
  used_count: 0-3,
  last_used_at: timestamp,
  can_use: calculated
}
```

**Lógica**: No almacenamos "trials disponibles", sino que calculamos elegibilidad basado en uso histórico.

### 3. Suscripciones con Contexto
```
SubscribedUser: {
  status: active/expired/cancelled,
  payment_method: paypal/trial/manual,
  auto_renew: boolean
}
```

**Particularidad**: El `payment_method` determina el flujo de renovación y cancelación.

## 🔍 Indexación Estratégica

### Índices de Performance
- `music_scores(status, composer_id, created_at)` - Listados principales
- `users(email, status)` - Login y validaciones
- `log_view_music_score_details(music_score_id, created_at)` - Analytics

### Índices de Búsqueda
- Fulltext en `music_scores(name, description)` - Búsqueda principal
- `composers(nationality, period)` - Filtros culturales
- `instruments(family_id, status)` - Navegación por categorías

## 🚀 Preparación para Escalabilidad

### Particionado Futuro
- Logs de visualización por fecha
- Archivos de partituras por región
- Datos de analytics por período

### Separación de Responsabilidades
- Tablas transaccionales (usuarios, suscripciones)
- Tablas de contenido (partituras, compositores)
- Tablas de analytics (logs, estadísticas)

## 🔧 Particularidades de Implementación

### JSON Campos Específicos
- `subscription_plans.features` - Configuración flexible de características
- `composer_requests.genres` - Múltiples géneros musicales
- `music_scores.tags` - Etiquetado libre

### Timestamps Inteligentes
- `created_at` - Siempre presente, inmutable
- `updated_at` - Actualizado automáticamente
- `verified_at` - Campos específicos para eventos importantes

### Estados Enum Controlados
- `music_scores.difficulty` - Beginner/Intermediate/Advanced (escalable)
- `subscribed_users.status` - Active/Expired/Cancelled/Payment_Failed
- `composer_requests.status` - Pending/Approved/Rejected/Reviewing

---

**Próximo**: [API Design](../api/design-principles.md) | **Relacionado**: [Development Workflow](../guides/development-workflow.md)
