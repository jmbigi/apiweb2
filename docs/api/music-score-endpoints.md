# 🎵 Music Score Endpoints - Faristol API

## 🎯 Filosofía de Endpoints de Partituras

Los endpoints de partituras están diseñados para **imitar el comportamiento natural** de los músicos con partituras físicas: buscar, hojear, marcar, organizar y practicar.

## 🌟 Decisiones de Diseño Únicas

### Navegación Musical vs Web Tradicional
**Diferencia clave**: Los músicos navegan por estructura musical, no por páginas web.

#### Patrones de Navegación Musical
- **Búsqueda por contexto musical**: Compositor, período, dificultad, instrumento
- **Navegación estructural**: Por movimientos, secciones, sistemas musicales
- **Acceso rápido**: Bookmarks a pasajes específicos, repeticiones, codas
- **Lectura continua**: Preloading inteligente de páginas siguientes

### Content Delivery Adaptativo
**Decisión**: Contenido adaptado al dispositivo y contexto de uso.

#### Adaptaciones por Contexto
- **Tablet practice**: Máxima resolución, optimización de batería
- **Mobile reference**: Carga rápida, navegación optimizada para pulgar
- **Desktop management**: Funcionalidades completas de organización
- **Classroom use**: Optimización para múltiples usuarios simultáneos

## 📋 Endpoints de Gestión de Partituras

### Búsqueda y Listado Musical
```http
GET /api/music-score/list
```

#### Parámetros Musicales Específicos
```
?search=Bach+Invention          // Búsqueda textual inteligente
?composer=Bach                  // Filtro por compositor
?difficulty=intermediate        // Nivel técnico musical
?instruments[]=piano           // Array de instrumentos
?key_signature=C+Major         // Tonalidad específica
?time_signature=4/4           // Compás específico
?style=baroque                // Período o estilo musical
?page=1&per_page=20          // Paginación adaptada a contenido visual
```

#### Particularidades de la Búsqueda
- **Fuzzy matching**: Tolerancia a errores en nombres de compositores
- **Multi-language support**: Búsqueda en múltiples idiomas para obras internacionales
- **Musical intelligence**: Comprende abreviaciones musicales (Op., BWV, K.)
- **Cultural awareness**: Respeta variaciones regionales de títulos

#### Respuesta Enriquecida
```json
{
  "status": true,
  "data": {
    "scores": [...],
    "pagination": {...},
    "search_context": {
      "total_found": 45,
      "search_suggestions": ["Bach Inventions", "Bach Two-Part Inventions"],
      "filters_applied": {
        "composer": "Bach",
        "difficulty": "intermediate"
      },
      "educational_note": "Inventions are excellent for developing two-hand independence"
    }
  }
}
```

### Acceso a Partitura Individual
```http
GET /api/music-score/get-music-score
```

#### Contexto Musical en Respuesta
- **User permissions**: Qué puede hacer el usuario con esta partitura
- **Educational metadata**: Información pedagógica relevante
- **Practice suggestions**: Sugerencias basadas en nivel del usuario
- **Related content**: Partituras relacionadas por compositor, estilo, dificultad

#### Optimizaciones por Dispositivo
- **Tablet optimization**: Resolución y formato optimizados para atril
- **Mobile adaptation**: Navegación táctil optimizada
- **Desktop enhancement**: Funcionalidades adicionales de análisis
- **Offline preparation**: Preloading para uso sin conexión

### Contenido PDF Musical
```http
GET /api/music-score/getPdfContent
```

#### Decisiones de Performance Musical
- **Multi-resolution serving**: Diferentes calidades según suscripción y dispositivo
- **Page-by-page delivery**: Entrega progresiva para navegación fluida
- **Intelligent preloading**: Predicción de próximas páginas necesarias
- **Practice mode optimization**: Configuración específica para sesiones de práctica

#### Parámetros de Optimización
```
?page=1                    // Página específica
?resolution=high          // Calidad según suscripción
?device_type=tablet       // Optimización por dispositivo
?practice_mode=true       // Modo práctica con preloading agresivo
```

## 🎨 Sistema de Favoritos Musical

### Gestión de Favoritos
```http
GET /api/music-score/fav-music-score?music_score_id=123
GET /api/music-score/remove-fav-music-score?music_score_id=123
```

#### Decisión Controversial: GET para Operaciones de Estado
**Justificación musical**:
- **Simplicidad**: Músicos pueden bookmarkear operaciones comunes
- **Velocidad**: Sin necesidad de construir requests complejos durante práctica
- **Intuitividad**: URLs auto-explicativas

#### Inteligencia de Favoritos
- **Practice tracking**: Correlación con tiempo de práctica real
- **Educational grouping**: Agrupación automática por propósito educativo
- **Recommendation engine**: Sugerencias basadas en favoritos existentes
- **Sharing capabilities**: Compartir listas de favoritos con profesores/estudiantes

### Listado de Favoritos
```http
GET /api/music-score/user-fav-music-score
```

#### Organización Musical
- **Chronological**: Por fecha de agregado a favoritos
- **By practice frequency**: Ordenado por frecuencia de acceso
- **Educational grouping**: Agrupado por propósito (examen, concierto, estudio)
- **Difficulty progression**: Ordenado por nivel de dificultad

## 🔄 Upload y Gestión de Contenido

### Upload Musical
```http
POST /api/music-score/upload-music-score
```

#### Procesamiento Musical Específico
- **Format validation**: Verificación de PDFs musicales válidos
- **Quality assessment**: Evaluación automática de legibilidad musical
- **Metadata extraction**: Extracción automática de información musical cuando posible
- **Educational categorization**: Clasificación automática por nivel y propósito

#### Workflow de Aprobación
- **Immediate availability**: Disponible inmediatamente para el compositor
- **Community review**: Revisión por músicos de la comunidad
- **Educational assessment**: Evaluación de valor pedagógico
- **Cultural validation**: Verificación de contexto cultural apropiado

### Gestión de Contenido Propio
```http
GET /api/music-score/user-uploaded-scores
PUT /api/music-score/update-metadata/{id}
DELETE /api/music-score/delete-score/{id}
```

#### Características de Gestión
- **Version control**: Manejo de versiones de partituras
- **Usage analytics**: Estadísticas de uso por otros músicos
- **Educational adoption**: Tracking de uso en instituciones educativas
- **Community feedback**: Sistema de feedback y ratings

### Descarga Musical Inteligente
```http
GET /api/music-score/download/{id}
```

#### Decisiones de Descarga Únicas
**Marca de agua condicional**: Aplicada según nivel de suscripción durante descarga, no en visualización.

#### Lógica de Marca de Agua
- **Free users**: Marca de agua sutil con atribución a Faristol
- **Basic/Premium users**: Sin marca de agua
- **Educational institutions**: Marca de agua especial institucional
- **Composer's own works**: Sin marca de agua independiente de suscripción

#### Formatos de Descarga
- **PDF original**: Máxima calidad disponible según suscripción
- **Optimized PDF**: Optimizado para dispositivo específico
- **Print-ready**: Formato optimizado para impresión física
- **Mobile-friendly**: Formato optimizado para lectura en móviles

## 🎓 Endpoints Educacionales

### Contenido para Educadores
```http
GET /api/music-score/educational-collections
GET /api/music-score/by-curriculum/{curriculum_id}
```

#### Funcionalidades Educativas
- **Curriculum alignment**: Partituras organizadas por currículo educativo
- **Difficulty progression**: Secuencias pedagógicas estructuradas
- **Bulk assignment**: Asignación masiva a grupos de estudiantes
- **Progress tracking**: Seguimiento de progreso estudiantil

#### Colecciones Especializadas
- **Method book integration**: Integración con libros de método populares
- **Exam preparation**: Colecciones específicas para exámenes
- **Technique development**: Agrupaciones por técnica musical específica
- **Cultural education**: Colecciones por tradición cultural

### Analytics Educacionales
```http
GET /api/music-score/usage-analytics/{score_id}
```

#### Métricas Educativas
- **Adoption rate**: Uso en instituciones educativas
- **Student success correlation**: Correlación con éxito estudiantil
- **Practice pattern analysis**: Análisis de patrones de práctica
- **Educational effectiveness**: Métricas de efectividad pedagógica

#### Insights Pedagógicos
- **Optimal learning sequence**: Secuencia de aprendizaje óptima
- **Common difficulty points**: Puntos de dificultad comunes identificados
- **Teaching methodology correlation**: Correlación con metodologías de enseñanza
- **Cultural adaptation**: Adaptación cultural para diferentes regiones

## 🔍 Búsqueda Avanzada Musical

### Búsqueda Semántica Musical
```http
GET /api/music-score/semantic-search
```

#### Capacidades Semánticas
- **Musical similarity**: Búsqueda por similitud musical
- **Technical challenge**: Búsqueda por desafíos técnicos específicos
- **Educational purpose**: Búsqueda por propósito pedagógico
- **Cultural context**: Búsqueda por contexto cultural o histórico

#### Algoritmos Únicos
- **Harmonic progression matching**: Matching por progresiones armónicas
- **Rhythmic pattern recognition**: Reconocimiento de patrones rítmicos
- **Melodic contour analysis**: Análisis de contorno melódico
- **Style period classification**: Clasificación automática por período

### Autocompletado Musical
```http
GET /api/music-score/search-suggestions
```

#### Inteligencia de Sugerencias
- **Composer name completion**: Autocompletado de nombres de compositores
- **Work title suggestions**: Sugerencias de títulos de obras
- **Musical term recognition**: Reconocimiento de términos musicales
- **Educational context**: Sugerencias adaptadas a nivel educativo

#### Personalización de Sugerencias
- **User history**: Basado en historial de búsqueda del usuario
- **Educational level**: Adaptado al nivel educativo del usuario
- **Instrument preference**: Preferencias instrumentales
- **Cultural background**: Contexto cultural del usuario

## 📊 Analytics y Tracking Musical

### Métricas de Uso Musical
```http
GET /api/music-score/practice-analytics
```

#### Tracking Musical Específico
- **Practice session duration**: Duración de sesiones de práctica
- **Page navigation patterns**: Patrones de navegación entre páginas
- **Annotation frequency**: Frecuencia y tipo de anotaciones
- **Return frequency**: Frecuencia de retorno a partituras específicas

#### Métricas Avanzadas
- **Learning progression**: Progresión de aprendizaje medible
- **Technique mastery**: Dominio de técnicas específicas
- **Repertoire expansion**: Expansión de repertorio personal
- **Cultural exploration**: Exploración de diferentes culturas musicales

### Performance Metrics
```http
GET /api/music-score/performance-metrics
```

#### Métricas de Performance Musical
- **Load time by device**: Tiempo de carga por tipo de dispositivo
- **Navigation smoothness**: Fluidez de navegación entre páginas
- **Search relevance**: Relevancia de resultados de búsqueda
- **User satisfaction**: Indicadores de satisfacción musical

#### Optimización Continua
- **A/B testing results**: Resultados de tests A/B en funcionalidades
- **Feature adoption rates**: Tasas de adopción de nuevas características
- **Error rate analysis**: Análisis de tasas de error por funcionalidad
- **Performance regression detection**: Detección de regresiones de performance

## 🌐 Endpoints Multilingües

### Localización Musical
```http
GET /api/music-score/localized-content
```

#### Adaptación Cultural
- **Title translations**: Traducciones de títulos respetando tradiciones
- **Cultural annotations**: Anotaciones específicas por cultura
- **Regional preferences**: Preferencias musicales regionales
- **Educational standards**: Estándares educativos locales

### Notación Musical Regional
```http
GET /api/music-score/notation-variants
```

#### Variaciones de Notación
- **Do Re Mi vs C D E**: Sistemas de nombres de notas
- **Regional clefs**: Claves utilizadas regionalmente
- **Ornament notation**: Notación de ornamentos por tradición
- **Rhythmic notation**: Diferencias en notación rítmica

---
**Relacionado**: [PDF Processing Strategy](../decisions/003-pdf-processing-strategy.md) | [Performance Strategy](../technical/performance-strategy.md)
