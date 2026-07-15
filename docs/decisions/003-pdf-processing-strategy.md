# ADR-003: Estrategia de Procesamiento de PDFs

## Estado
Aceptado

## Contexto
Las partituras musicales requieren alta fidelidad visual y navegación fluida. Decidir entre procesar PDFs en tiempo real vs pre-procesamiento afecta significativamente la experiencia de usuario.

## Decisión
Pre-procesamiento completo al momento de upload con generación de múltiples formatos y calidades.

## Enfoque Técnico

### Procesamiento en Upload
**Rationale**: Optimizar experiencia de lectura sobre velocidad de upload

#### Pipeline de Procesamiento
1. **Validación**: Verificar integridad y formato del PDF
2. **Conversión**: PDF → Imágenes por página (múltiples resoluciones)
3. **Thumbnails**: Generar previsualizaciones rápidas
4. **Metadatos**: Extraer información musical cuando sea posible
5. **Indexación**: Preparar contenido para búsqueda

### Múltiples Calidades por Suscripción
- **Low (Free)**: 150 DPI - Lectura básica
- **Medium (Basic)**: 300 DPI - Calidad estándar
- **High (Premium)**: 600 DPI - Calidad profesional

## Decisiones No Convencionales

### Cache Inteligente por Página
**Problema**: PDFs musicales pueden ser largos (50+ páginas)
**Solución**: Cargar páginas bajo demanda con preload inteligente
**Beneficio**: Balance entre performance y uso de ancho de banda

### Marcas de Agua Dinámicas
**Problema**: Proteger contenido sin degradar experiencia free
**Solución**: Marcas de agua sutiles solo en descargas, no en visualización
**Implementación**: Aplicadas en tiempo real según nivel de suscripción

### Anotaciones como Capa Separada
**Problema**: Integrar anotaciones sin modificar PDFs originales
**Solución**: SVG overlay con coordenadas relativas
**Beneficio**: Preserva documentos originales, permite sincronización

## Consideraciones de Escalabilidad

### Storage Strategy
- **Originals**: S3/Wasabi para preservación
- **Processed**: CDN para distribución global
- **Thumbnails**: Edge cache para velocidad

### Procesamiento Asíncrono
- **Immediate**: Validación y respuesta al usuario
- **Background**: Procesamiento intensivo en queues
- **Progressive**: Calidades disponibles gradualmente

## Casos Edge Manejados

### PDFs Complejos
- **Partituras escaneadas**: OCR opcional para búsqueda
- **Múltiples instrumentos**: Detección automática cuando posible
- **Formatos no estándar**: Fallbacks graceful

### Fallos de Procesamiento
- **Corrupción parcial**: Procesar páginas válidas, marcar problemáticas
- **Timeouts**: Reintentos inteligentes con timeouts incrementales
- **Storage failures**: Rollback automático con notificación

## Métricas de Performance
- **Time to First Page**: < 2 segundos post-upload
- **Processing Success Rate**: > 98%
- **Storage Efficiency**: Ratio compresión vs calidad
- **User Satisfaction**: Feedback sobre calidad de imagen

## Consecuencias

### Positivas
- **Experiencia de lectura óptima** desde primer acceso
- **Navegación fluida** sin esperas
- **Calidad consistente** independiente del documento original

### Negativas
- **Upload time mayor** por procesamiento
- **Storage costs** por múltiples versiones
- **Complejidad** en pipeline de procesamiento

### Trade-offs Aceptados
- **Latencia inicial** vs performance posterior
- **Costo de storage** vs experiencia de usuario
- **Complejidad técnica** vs simplicidad de uso

---
**Fecha**: 14 de Enero, 2024  
**Autor**: Equipo Técnico  
**Revisores**: [Arquitectura, UX, DevOps]
