# ADR-002: Modelo de Suscripción Progresivo vs Restrictivo

## Estado
Aceptado

## Contexto
La mayoría de plataformas musicales bloquean contenido detrás de paywalls. Necesitábamos decidir si seguir este patrón o crear algo diferente que respete la naturaleza educativa de la música.

## Decisión
Adoptar un modelo progresivo donde el contenido es libre pero las herramientas de productividad justifican el pago.

## Filosofía Central
**"La música debe ser accesible, las herramientas profesionales deben ser valoradas"**

## Justificación

### Enfoque No Convencional
- **Free**: Acceso completo a partituras + herramientas básicas
- **Basic**: Sin publicidad + herramientas mejoradas
- **Premium**: Herramientas profesionales + funcionalidades avanzadas

### Decisiones Específicas

#### Anotaciones Limitadas por Nivel
- **Rationale**: Las anotaciones son herramientas de estudio profesional
- **Implementación**: 5 → 15 → Ilimitadas
- **Beneficio**: Los usuarios ven valor inmediato al upgradearse

#### Favoritos Solo en Premium
- **Rationale**: Curación de contenido es una funcionalidad premium
- **Beneficio**: Incentiva lealtad y uso recurrente

#### Sin Bloqueo de Contenido
- **Rationale**: La educación musical debe ser universal
- **Diferenciador**: Nos distingue de competidores restrictivos

## Casos Edge Considerados

### Pruebas Premium Múltiples
- **Problema**: ¿Cómo evitar abuso sin ser restrictivos?
- **Solución**: 3 pruebas de 31 días en total (no consecutivas)
- **Beneficio**: Oportunidades reales de evaluación

### Transiciones Sin Pérdida
- **Problema**: ¿Qué pasa con datos al cambiar de plan?
- **Solución**: Los datos se preservan, solo se limita funcionalidad nueva
- **Ejemplo**: Anotaciones existentes se mantienen, nuevas se limitan

## Métricas de Validación
- **Engagement Rate**: Mayor tiempo en plataforma vs competidores
- **Conversion Rate**: % de free users que upgraden
- **Retention Rate**: Usuarios que mantienen suscripción después de trial
- **Educational Impact**: Uso en instituciones educativas

## Consecuencias

### Positivas
- **Diferenciación clara** del mercado
- **Barrera de entrada baja** para nuevos usuarios
- **Modelo educativo sostenible**
- **Valor percibido alto** en upgrades

### Negativas
- **Revenue slower** comparado con modelos restrictivos
- **Complejidad** en lógica de limitaciones
- **Expectativas altas** de contenido gratuito

### Riesgos Mitigados
- **Piratería reducida**: Al ser contenido libre, menos incentivo
- **Churn educativo bajo**: Instituciones aprecian acceso libre
- **Word-of-mouth positivo**: Modelo ético genera recomendaciones

## Notas de Implementación
- Sistema de flags por funcionalidad, no por contenido
- Analytics detallados sobre patrones de uso por nivel
- A/B testing en límites de herramientas
- Feedback loops con usuarios educativos

---
**Fecha**: 12 de Enero, 2024  
**Autor**: Equipo de Producto  
**Revisores**: [Desarrollo, Business, UX]
