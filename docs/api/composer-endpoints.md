# 🎭 Composer Endpoints - Faristol API

## 🎯 Filosofía de Compositores

El sistema de compositores debe **democratizar la creación musical** mientras mantiene estándares de calidad. Distinguimos entre patrimonio musical histórico y creatividad contemporánea.

## 🌟 Decisiones Únicas del Sistema

### Dualidad Histórica vs Contemporánea
**Decisión arquitectónica**: Separar compositores históricos de usuarios-compositores modernos.

#### Razones de la Separación
- **Preservación histórica**: Mantener integridad de información de compositores clásicos
- **Flexibilidad moderna**: Permitir evolución de perfiles de compositores contemporáneos
- **Gestión de calidad**: Diferentes estándares para contenido histórico vs contemporáneo
- **Escalabilidad**: Modelos de datos optimizados para cada tipo

### Aprobación Manual vs Automática
**Decisión controversial**: Proceso de aprobación completamente manual.

#### Justificación Musical
- **Calidad subjetiva**: La música requiere criterio humano para evaluación
- **Contexto cultural**: Los admin pueden evaluar relevancia cultural
- **Spam prevention**: Evita uploads masivos de contenido irrelevante
- **Community building**: Crea sensación de exclusividad y calidad

## 📋 Endpoints de Gestión de Compositores

### Listado de Compositores
```http
GET /api/composer/get-composers
```

#### Parámetros de Filtrado Musical
```
?type=historical              // historical | contemporary | all
?nationality=Spanish          // Filtro por nacionalidad
?period=baroque              // Período musical
?active_status=verified      // verified | pending | all
?has_scores=true            // Solo compositores con partituras disponibles
?sort=alphabetical          // alphabetical | chronological | popularity
```

#### Respuesta Enriquecida
```json
{
  "status": true,
  "data": {
    "composers": [
      {
        "id": 123,
        "name": "Johann Sebastian Bach",
        "type": "historical",
        "nationality": "German",
        "birth_year": 1685,
        "death_year": 1750,
        "period": "Baroque",
        "scores_count": 247,
        "educational_popularity": 95,
        "cultural_significance": "Major",
        "biographical_note": "Master of counterpoint and fugue..."
      }
    ],
    "filters_applied": {...},
    "educational_context": {
      "most_studied": ["Bach", "Mozart", "Chopin"],
      "beginner_friendly": ["Clementi", "Czerny", "Burgmüller"],
      "period_distribution": {
        "baroque": 45,
        "classical": 67,
        "romantic": 89,
        "contemporary": 34
      }
    }
  }
}
```

### Solicitud de Estado de Compositor
```http
POST /api/composer/request-composer-status
```

#### Información Requerida para Solicitud
```json
{
  "composer_name": "María García López",
  "biography": "Compositora española especializada en música contemporánea...",
  "musical_education": "Conservatorio Superior de Madrid, Composición",
  "portfolio_url": "https://mariagarcia.music/portfolio",
  "genres": ["Contemporary Classical", "Chamber Music", "Solo Piano"],
  "sample_works": [
    {
      "title": "Sonata para Piano No. 1",
      "year": 2023,
      "description": "Obra en tres movimientos..."
    }
  ],
  "references": {
    "professional": "Dr. Juan Pérez - Catedrático de Composición",
    "institutional": "Conservatorio Superior de Madrid"
  }
}
```

#### Proceso de Evaluación
- **Technical review**: Verificación de formación y credenciales musicales
- **Portfolio assessment**: Evaluación de calidad musical y originalidad
- **Cultural relevance**: Evaluación de relevancia para la comunidad musical
- **Educational value**: Potencial valor pedagógico de las obras

### Sincronización de Estado de Solicitud
```http
POST /api/composer/sync-request-status
```

#### Estados Posibles de Solicitud
- **pending**: Solicitud recibida, en proceso de revisión
- **under_review**: En evaluación por panel de músicos
- **additional_info_required**: Necesita información adicional
- **approved**: Aprobado, permisos otorgados
- **rejected**: Rechazado con feedback constructivo

#### Feedback Estructurado
```json
{
  "status": "additional_info_required",
  "feedback": {
    "technical_score": 8,
    "originality_score": 7,
    "educational_value": 6,
    "areas_for_improvement": [
      "Proporcionar más ejemplos de obras pedagógicas",
      "Clarificar influencias musicales contemporáneas"
    ],
    "reviewer_comments": "Trabajo prometedor, necesita mayor diversidad en portfolio",
    "next_steps": "Subir 2-3 obras adicionales de diferentes estilos",
    "estimated_review_time": "2-3 semanas"
  }
}
```

## 🎨 Gestión de Contenido de Compositores

### Upload de Partituras por Compositores
```http
POST /api/composer/upload-score
```

#### Metadatos Musicales Enriquecidos
```json
{
  "title": "Sonatina en Do Mayor",
  "subtitle": "Para estudiantes intermedios",
  "opus_number": "Op. 15 No. 1",
  "year_composed": 2024,
  "duration_minutes": 8,
  "difficulty_level": "intermediate",
  "educational_purpose": "Technical development",
  "instruments": ["piano"],
  "key_signature": "C Major",
  "time_signature": "4/4",
  "tempo_marking": "Allegro moderato",
  "musical_techniques": [
    "Scale passages",
    "Arpeggios",
    "Simple counterpoint"
  ],
  "program_notes": "Esta sonatina está diseñada para desarrollar...",
  "performance_notes": "Prestar atención a la articulación en compás 23...",
  "cultural_context": "Inspirada en el estilo clásico vienés",
  "educational_level": "3rd year piano students"
}
```

#### Workflow de Publicación
1. **Immediate availability**: Disponible para el compositor inmediatamente
2. **Community preview**: Disponible para revisión por músicos verificados
3. **Educational assessment**: Evaluación por educadores musicales
4. **Public release**: Disponible públicamente tras aprobación

### Gestión de Contenido Propio
```http
GET /api/composer/my-scores
PUT /api/composer/update-score/{score_id}
DELETE /api/composer/delete-score/{score_id}
```

#### Analytics para Compositores
- **Usage statistics**: Estadísticas de uso de sus partituras
- **Educational adoption**: Adopción en instituciones educativas
- **Community feedback**: Ratings y comentarios de la comunidad
- **Performance tracking**: Seguimiento de interpretaciones

## 🎓 Compositores y Educación

### Colaboración con Instituciones
```http
GET /api/composer/educational-partnerships
POST /api/composer/submit-educational-content
```

#### Programa de Compositores Educativos
- **Curriculum integration**: Integración con currículums educativos
- **Pedagogical content**: Contenido específicamente pedagógico
- **Teacher collaboration**: Colaboración directa con educadores
- **Student feedback**: Feedback directo de estudiantes

### Comisiones Educacionales
```http
POST /api/composer/educational-commission
```

#### Sistema de Comisiones
- **Institution requests**: Solicitudes de instituciones educativas
- **Composer matching**: Matching con compositores apropiados
- **Project management**: Gestión de proyectos de comisión
- **Quality assurance**: Asegurar calidad del contenido comisionado

## 📊 Analytics de Compositores

### Métricas de Impacto Musical
```http
GET /api/composer/impact-analytics/{composer_id}
```

#### KPIs de Compositor
- **Reach metrics**: Alcance de sus obras
- **Educational impact**: Uso en contextos educativos
- **Community engagement**: Engagement de la comunidad musical
- **Cultural contribution**: Contribución a diversidad cultural

### Trending Composers
```http
GET /api/composer/trending
```

#### Algoritmo de Trending
- **Recent activity**: Actividad reciente de uploads y uso
- **Educational adoption**: Adopción rápida en instituciones
- **Community ratings**: Ratings altos de la comunidad
- **Cultural relevance**: Relevancia cultural emergente

## 🌍 Consideraciones Culturales

### Diversidad Musical Global
```http
GET /api/composer/cultural-diversity
```

#### Promoción de Diversidad
- **Geographic representation**: Representación geográfica equilibrada
- **Cultural traditions**: Respeto por tradiciones musicales locales
- **Language accessibility**: Accesibilidad en múltiples idiomas
- **Gender equity**: Promoción de equidad de género

### Preservación Cultural
```http
POST /api/composer/cultural-preservation
```

#### Iniciativas de Preservación
- **Traditional music**: Preservación de música tradicional
- **Oral traditions**: Documentación de tradiciones orales
- **Regional styles**: Promoción de estilos musicales regionales
- **Intergenerational transfer**: Transferencia entre generaciones

## 🔮 Evolución del Sistema

### AI-Assisted Composition
**Evaluación futura**: Herramientas de IA para asistir composición.

#### Consideraciones Éticas
- **Human creativity**: Mantener la creatividad humana como central
- **AI transparency**: Transparencia sobre uso de IA en composición
- **Cultural sensitivity**: Sensibilidad cultural en herramientas de IA
- **Educational value**: Mantener valor educativo de proceso creativo

### Blockchain para Derechos
**Evaluación**: Sistema blockchain para gestión de derechos de autor.

#### Beneficios Potenciales
- **Immutable attribution**: Atribución inmutable de autoría
- **Smart contracts**: Contratos inteligentes para royalties
- **Global recognition**: Reconocimiento global de derechos
- **Transparent transactions**: Transacciones transparentes

---
**Relacionado**: [Composer Approval Workflow](../decisions/004-composer-approval-workflow.md) | [Content Management](../guides/content-management.md)
