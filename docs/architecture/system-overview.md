# 🏗️ Visión General del Sistema - Faristol

## 🎯 Concepto Central

Faristol no es simplemente un repositorio de partituras. Es una **plataforma de experiencia musical interactiva** que transforma documentos estáticos en herramientas de aprendizaje dinámicas.

## 🌟 Decisiones Arquitectónicas Clave

### 1. Arquitectura de Contenido Híbrida

**Decisión**: Combinar contenido estático (PDFs) con capas interactivas dinámicas.

**Justificación**: Los músicos necesitan tanto la fidelidad visual del documento original como herramientas modernas de interacción.

**Implementación**:
- PDFs almacenados como fuente de verdad
- Capas de anotación superpuestas en el frontend
- Sincronización bidireccional entre documento y datos de usuario

### 2. Sistema de Roles Evolutivo

**Decisión**: Un usuario puede evolucionar de músico a compositor sin perder su historial.

**Particularidad**: Los roles se acumulan en lugar de reemplazarse. Un compositor sigue siendo músico con funcionalidades adicionales.

**Beneficio**: Preserva la experiencia del usuario y permite transiciones naturales.

### 3. Modelo de Suscripción Progresivo

**Decisión**: En lugar de bloquear contenido, limitamos herramientas de productividad.

**Filosofía**: El acceso a la música debe ser universal, pero las herramientas profesionales justifican el pago.

**Implementación**:
- Free: Acceso total a partituras, herramientas básicas
- Paid: Herramientas avanzadas, sin limitaciones, sin publicidad

## 🔄 Flujos de Datos Únicos

### Flujo de Anotaciones
```
Usuario crea anotación → Validación de límites → Storage local → Sincronización en lotes → Persistencia servidor
```

**Particularidad**: Las anotaciones viven primero en el frontend para respuesta inmediata, luego se sincronizan en segundo plano.

### Flujo de Compositor
```
Usuario solicita → Admin revisa → Aprobación → Creación de perfil compositor → Asignación de rol → Notificación
```

**Particularidad**: El proceso es manual para garantizar calidad, pero automatizado en la ejecución.

### Flujo de Procesamiento PDF
```
Upload → Validación → Conversión a imágenes → Generación de thumbnails → Extracción de metadatos → Indexación
```

**Decisión**: Procesamos todo al momento de upload para optimizar la experiencia de lectura posterior.

## 🎨 Patrones de Diseño Adoptados

### 1. Strategy Pattern - Gestión de Suscripciones
Cada nivel de suscripción implementa su propia estrategia de limitaciones sin acoplar lógica de negocio.

### 2. Observer Pattern - Notificaciones
Sistema de eventos que permite reaccionar a cambios de estado sin dependencias directas.

### 3. Factory Pattern - Procesamiento de Archivos
Diferentes tipos de archivo (PDF, MIDI, XML) se procesan con factories específicas.

## 🔒 Filosofía de Seguridad

### Principio de Confianza Gradual
- **Usuarios nuevos**: Acceso básico, validaciones estrictas
- **Usuarios establecidos**: Más libertades, menos validaciones
- **Compositores verificados**: Confianza máxima en sus uploads

### Datos como Assets del Usuario
Las anotaciones y favoritos son propiedad del usuario. El sistema facilita pero no controla estos datos.

## 📱 Consideraciones Multi-Plataforma

### API-First Design
La web app consume la misma API que futuras aplicaciones móviles, garantizando consistencia.

### Responsive Data Loading
Los datos se cargan progresivamente según el dispositivo y conexión del usuario.

## 🔍 Decisiones No Convencionales

### 1. Teléfonos como Identificadores Únicos
**Decisión**: Los teléfonos deben ser únicos en el sistema.
**Justificación**: Prevenir cuentas duplicadas y facilitar recuperación de acceso.
**Implementación**: Formato internacional obligatorio con validación estricta.

### 2. Pruebas Premium Múltiples pero Limitadas
**Decisión**: Permitir hasta 3 pruebas premium de 31 días, no necesariamente consecutivas.
**Justificación**: Dar oportunidades reales de evaluación sin abuso del sistema.

### 3. Compositores Históricos vs Usuarios-Compositores
**Decisión**: Mantener perfiles de compositores clásicos separados de usuarios modernos.
**Justificación**: Preservar integridad histórica mientras permitimos contenido contemporáneo.

## 🚀 Escalabilidad Planificada

### Microservicios Preparados
La arquitectura actual permite extraer servicios específicos (procesamiento PDF, gestión de pagos) sin refactoring mayor.

### Cache Inteligente
Sistema de cache que aprende patrones de uso para precargar contenido relevante.

### CDN Ready
Todos los assets están preparados para distribución global mediante CDN.

## 📊 Métricas que Importan

- **Time to First Score**: Tiempo desde login hasta visualizar primera partitura
- **Annotation Engagement**: Porcentaje de usuarios que usan anotaciones regularmente
- **Composer Pipeline**: Tiempo promedio desde solicitud hasta aprobación
- **Subscription Conversion**: Ratio de free a paid por características específicas

---

**Próximo**: [Data Model](data-model.md) | **Relacionado**: [Security Approach](../technical/security-approach.md)
