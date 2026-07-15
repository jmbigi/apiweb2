# ADR-004: Flujo de Aprobación Manual para Compositores

## Estado
Aceptado

## Contexto
La plataforma debe distinguir entre compositores históricos (Bach, Mozart) y usuarios modernos que desean subir contenido original. La calidad y autenticidad del contenido es crucial para mantener la reputación educativa.

## Decisión
Implementar un sistema de solicitudes con aprobación manual humana para otorgar permisos de compositor.

## Filosofía Subyacente
**"La curaduría humana es irreemplazable para content de calidad"**

## Justificación del Enfoque Manual

### ¿Por qué no automático?
- **Calidad subjetiva**: La música requiere criterio humano
- **Contexto cultural**: Los admin pueden evaluar relevancia cultural
- **Spam prevention**: Evita uploads masivos de contenido irrelevante
- **Community building**: Crea sensación de exclusividad y calidad

### ¿Por qué no restricción completa?
- **Democratización**: Compositores emergentes merecen plataforma
- **Contenido fresco**: La música contemporánea enriquece el catálogo
- **Engagement**: Los usuarios se sienten más conectados al contribuir

## Diseño del Workflow

### Estados del Proceso
```
Usuario → Solicitud → Pendiente → Revisión → {Aprobado | Rechazado}
                                               ↓
                                        Perfil Compositor + Rol
```

### Criterios de Evaluación
1. **Calidad musical**: Nivel técnico y artístico
2. **Originalidad**: Contenido propio, no copias
3. **Relevancia educativa**: Valor para estudiantes
4. **Completitud**: Información biográfica adecuada

### Información Requerida
- **Biografía mínima**: 100 caracteres obligatorios
- **Formación musical**: Opcional pero valorada
- **Portfolio**: Enlaces a trabajos previos
- **Género musical**: Para categorización
- **Obras de muestra**: Ejemplos representativos

## Particularidades del Sistema

### Múltiples Solicitudes Permitidas
**Decisión**: Un usuario puede solicitar múltiples veces.
**Justificación**: Los artistas evolucionan, criterios pueden cambiar.

### Separación Usuario-Compositor
**Decisión**: El perfil de compositor es independiente del usuario.
**Beneficio**: Permite múltiples compositores por usuario (manager/agente).

### Preservación de Historial
**Decisión**: Todas las solicitudes se mantienen en histórico.
**Justificación**: Permite revisar evolución del artista y decisiones pasadas.

## Casos Edge Considerados

### Compositores Fallecidos
**Problema**: ¿Cómo manejar herencias musicales?
**Solución**: Proceso especial para representantes legales
**Implementación**: Campo de "representante legal" en solicitudes

### Colaboraciones
**Problema**: ¿Obras con múltiples compositores?
**Solución**: Compositor principal + créditos adicionales
**Futuro**: Sistema de co-autoría

### Géneros Emergentes
**Problema**: ¿Cómo evaluar géneros nuevos?
**Solución**: Criterios flexibles + consulta con expertos externos

## Métricas de Éxito

### Calidad del Contenido
- **User ratings**: Puntuación promedio de partituras de compositores aprobados
- **Educational adoption**: Uso en instituciones educativas
- **Community feedback**: Comentarios y engagement

### Eficiencia del Proceso
- **Time to decision**: Tiempo promedio de respuesta
- **Approval rate**: % de solicitudes aprobadas
- **Reapplication rate**: % que solicita nuevamente tras rechazo

## Consecuencias

### Positivas
- **Alta calidad** de contenido garantizada
- **Confianza educativa** en la plataforma
- **Community exclusiva** de compositores verificados
- **Spam prevention** efectivo

### Negativas
- **Barrera de entrada** para nuevos compositores
- **Subjetividad** en decisiones
- **Workload manual** para administradores
- **Potencial bias** en evaluaciones

### Mitigación de Riesgos
- **Criterios claros** y publicados
- **Múltiples revisores** para decisiones importantes
- **Proceso de apelación** para rechazos
- **Feedback constructivo** en rechazos

## Evolución Futura

### Automatización Parcial
- **Pre-screening**: IA para detectar contenido obviamente inadecuado
- **Portfolio analysis**: Análisis automático de calidad técnica
- **Community voting**: Sistema de votación de la comunidad

### Criterios Dinámicos
- **A/B testing**: Experimentar con diferentes criterios
- **Seasonal adjustments**: Adaptar criterios según demanda
- **Genre specialists**: Revisores especializados por género

---
**Fecha**: 15 de Enero, 2024  
**Autor**: Equipo de Producto  
**Revisores**: [UX, Community, Legal]
