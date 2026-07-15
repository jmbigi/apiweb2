# 🎨 Sistema de Anotaciones - Diseño Técnico

## 🎯 Concepto Único

El sistema de anotaciones de Faristol **vive primero en el frontend** y se sincroniza opcionalmente al backend. Esta decisión arquitectónica prioriza la responsividad de la interfaz sobre la persistencia inmediata.

## 🌟 Particularidades del Diseño

### Frontend-First Architecture
**Decisión**: Las anotaciones se crean y manipulan completamente en el frontend.

**Justificación**:
- **Responsividad inmediata**: Sin latencia de red en cada interacción
- **Trabajo offline**: Los usuarios pueden anotar sin conexión
- **Batching inteligente**: Sincronización eficiente en lotes

### Limitaciones Progresivas por Suscripción
**Implementación**: El límite se valida en el frontend y se confirma en el backend.

#### Lógica de Limitación
- **Free (5 anotaciones)**: Warning a las 4, bloqueo en la 6ta
- **Basic (15 anotaciones)**: Warning a las 12, bloqueo en la 16va  
- **Premium (Ilimitadas)**: Sin restricciones

### Persistencia Híbrida

#### Storage Local (Primario)
- **IndexedDB**: Para persistencia local robusta
- **Estructura**: Por partitura y usuario
- **Sincronización**: Flag dirty/clean por anotación

#### Storage Remoto (Backup)
- **Base de datos**: Solo como respaldo y sincronización entre dispositivos
- **Trigger**: Sincronización automática cada N anotaciones o tiempo
- **Resolución de conflictos**: Last-write-wins con timestamps

## 🔧 Arquitectura Técnica

### Coordenadas Relativas
**Problema**: Las anotaciones deben ser responsive y escalables.
**Solución**: Coordenadas porcentuales relativas al viewport de la página.

#### Sistema de Coordenadas
- **X, Y**: Porcentajes (0-100) relativos a la página
- **Width, Height**: Del container de anotación
- **Zoom-independent**: Se mantienen al cambiar zoom

### Tipos de Anotación Soportados
1. **Text Notes**: Texto libre con posicionamiento
2. **Highlights**: Áreas rectangulares de resaltado
3. **Arrows**: Indicadores direccionales
4. **Circles**: Marcadores circulares

### Sincronización Inteligente

#### Triggers de Sincronización
- **Por cantidad**: Cada 5 anotaciones nuevas
- **Por tiempo**: Cada 2 minutos de inactividad
- **Por navegación**: Al cambiar de partitura
- **Manual**: Botón de sincronización explícito

#### Gestión de Conflictos
- **Estrategia**: Last-write-wins con merge inteligente
- **Casos edge**: Anotaciones en misma posición
- **Recovery**: Backup local en caso de fallo de sync

## 🎨 Consideraciones de UX

### Feedback Visual Inmediato
- **Estado de sync**: Indicadores visuales del estado
- **Límites**: Warnings progresivos antes del bloqueo
- **Conflicts**: Notificaciones no-intrusivas

### Responsive Design
- **Mobile-first**: Anotaciones optimizadas para touch
- **Desktop enhancement**: Shortcuts de teclado y menús contextuales
- **Cross-device**: Sincronización entre dispositivos

## 🔍 Patrones Únicos Implementados

### Lazy Loading de Anotaciones
Las anotaciones se cargan solo para páginas visibles, optimizando memoria.

### Undo/Redo Local
Sistema de historial local sin dependencia del servidor para acciones inmediatas.

### Export/Import de Anotaciones
Los usuarios pueden exportar sus anotaciones como backup personal.

## 📊 Métricas y Analytics

### Métricas de Engagement
- **Annotations per Session**: Promedio de anotaciones por sesión
- **Retention by Annotation Usage**: Correlación uso vs retención
- **Upgrade Trigger**: % que upgradea al alcanzar límite

### Métricas Técnicas
- **Sync Success Rate**: % de sincronizaciones exitosas
- **Local Storage Usage**: Uso promedio de IndexedDB
- **Performance Impact**: Latencia agregada por anotaciones

## 🚀 Evolución Planificada

### Funcionalidades Futuras
- **Collaborative Annotations**: Anotaciones compartidas entre usuarios
- **Voice Annotations**: Notas de audio vinculadas a posiciones
- **Smart Suggestions**: IA que sugiere anotaciones basadas en contenido

### Optimizaciones Técnicas
- **WebAssembly**: Para procesamiento intensivo de coordenadas
- **Service Workers**: Para sincronización en background
- **Real-time Sync**: WebSockets para colaboración en tiempo real

---
**Relacionado**: [PDF Processing Strategy](../decisions/003-pdf-processing-strategy.md) | [Subscription Model](../decisions/002-subscription-model.md)
