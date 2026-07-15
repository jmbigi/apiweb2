# 🐛 Guía de Debugging - Faristol

## 🎯 Filosofía de Debugging

En Faristol, el debugging debe considerar el **contexto musical** - un bug que interrumpe una sesión de práctica es más crítico que uno que afecta analytics.

## 🌟 Tipos de Issues Musicales

### Crítico - Interrumpe Práctica Musical
- **PDF no carga**: Músico no puede acceder a partitura
- **Anotaciones no responden**: Interfiere con estudio activo
- **Navegación entre páginas lenta**: Rompe flujo de lectura
- **Logout inesperado**: Pérdida de sesión durante práctica

### Alto - Degrada Experiencia Musical
- **Búsqueda lenta**: Frustra descubrimiento de repertorio
- **Calidad de imagen baja**: Dificulta lectura musical
- **Sincronización de anotaciones fallida**: Pérdida de trabajo
- **Performance inconsistente**: Variabilidad en tiempos de respuesta

### Medio - Inconvenientes Operacionales
- **Analytics incorrectos**: No afecta uso directo
- **Emails no enviados**: Problema de comunicación
- **Interface visual menor**: UX subóptima pero funcional

## 🔧 Herramientas de Debugging

### Frontend Debugging

#### DevTools Musical
**Configuración específica**: Performance tab configurado para detectar lag en anotaciones.

##### Métricas Clave a Monitorear
- **Frame rate**: Debe mantenerse >30fps durante anotaciones
- **Memory usage**: Detectar leaks en sesiones largas de práctica
- **Network timing**: Identificar bottlenecks en carga de páginas

#### Local Storage Debugging
**Particularidad**: Las anotaciones viven en localStorage, requiere debugging específico.

##### Commands Útiles
```javascript
// Ver anotaciones almacenadas localmente
console.log(JSON.parse(localStorage.getItem('faristol_annotations')));

// Limpiar cache de anotaciones (último recurso)
localStorage.removeItem('faristol_annotations');

// Verificar estado de sincronización
console.log(localStorage.getItem('sync_status'));
```

### Backend Debugging

#### Log Analysis Musical
**Enfoque**: Logs contextualizados por actividad musical.

##### Patterns de Log a Buscar
```bash
# Usuarios con muchas anotaciones fallidas
grep "annotation_failed" storage/logs/laravel.log | grep "user_id" | sort | uniq -c

# Partituras que fallan al cargar frecuentemente
grep "pdf_load_failed" storage/logs/laravel.log | grep -o "score_id:[0-9]*" | sort | uniq -c

# Búsquedas que timeout
grep "search_timeout" storage/logs/laravel.log | grep -o "query:.*" | head -20
```

#### Database Debugging Musical
**Queries lentas**: Identificar queries que afectan performance musical.

##### Queries Críticas a Monitorear
```sql
-- Búsquedas lentas (>500ms es problemático para música)
SELECT * FROM music_scores WHERE name LIKE '%mozart%' ORDER BY created_at;

-- Cargas de usuario con muchas anotaciones (puede ser lento)
SELECT u.*, COUNT(a.id) as annotation_count 
FROM users u 
LEFT JOIN annotations a ON u.id = a.user_id 
GROUP BY u.id 
HAVING annotation_count > 100;
```

### API Debugging

#### Request/Response Musical
**Focus**: Endpoints que impactan experiencia musical directamente.

##### Debugging Commands
```bash
# Test endpoint de PDF content (crítico para performance)
curl -w "@curl-format.txt" \
  -H "Authorization: Bearer $TOKEN" \
  https://api.faristol.net/api/music-score/getPdfContent

# Verificar rate limiting en búsquedas
curl -H "Authorization: Bearer $TOKEN" \
  https://api.faristol.net/api/music-score/list?search=bach

# Test de anotaciones (debe ser <50ms)
time curl -X POST \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"score_id":1,"page":1,"annotation":"test"}' \
  https://api.faristol.net/api/annotations/save
```

## 🎨 Scenarios de Debugging Comunes

### Scenario 1: "Las anotaciones no se guardan"

#### Debugging Steps
1. **Verificar localStorage**: ¿Se guardan localmente?
2. **Verificar network**: ¿Falla la sincronización?
3. **Verificar limits**: ¿Usuario excedió límite de plan?
4. **Verificar auth**: ¿Token expirado durante sesión larga?

#### Common Solutions
```javascript
// Verificar estado de anotaciones locales
const annotations = JSON.parse(localStorage.getItem('faristol_annotations') || '[]');
console.log(`Local annotations: ${annotations.length}`);

// Forzar sincronización
window.faristol.forceSync();

// Verificar límites de usuario
fetch('/api/auth/user/check-subscription')
  .then(r => r.json())
  .then(data => console.log('Annotation limits:', data.annotation_limits));
```

### Scenario 2: "PDF no carga o carga muy lento"

#### Debugging Steps
1. **Verificar CDN**: ¿Está sirviendo desde edge?
2. **Verificar processing**: ¿PDF fue procesado correctamente?
3. **Verificar device**: ¿Limitaciones de memoria en device?
4. **Verificar network**: ¿Conexión lenta o inestable?

#### Diagnostic Commands
```bash
# Verificar status de processing de PDF
curl https://api.faristol.net/api/music-score/processing-status/{id}

# Test CDN performance
curl -w "@curl-format.txt" https://cdn.faristol.net/scores/{id}/page-1.jpg

# Verificar si hay versión de baja resolución disponible
curl -I https://cdn.faristol.net/scores/{id}/page-1-low.jpg
```

### Scenario 3: "Búsqueda no encuentra resultados esperados"

#### Debugging Steps
1. **Verificar índices**: ¿Están actualizados?
2. **Verificar encoding**: ¿Problemas con caracteres especiales?
3. **Verificar permissions**: ¿Usuario puede ver el contenido?
4. **Verificar relevance**: ¿Algoritmo de ranking correcto?

#### Debug Queries
```sql
-- Verificar si partitura existe con nombre similar
SELECT name, composer_id FROM music_scores 
WHERE LOWER(name) LIKE LOWER('%bach%invention%');

-- Verificar índices fulltext
SHOW INDEX FROM music_scores WHERE Key_name = 'fulltext_search';

-- Verificar permisos de usuario
SELECT ms.name, ms.status, sp.type as plan_type
FROM music_scores ms
CROSS JOIN subscription_plans sp
WHERE ms.name LIKE '%bach%'
AND sp.id = (SELECT subscription_plan_id FROM subscribed_users WHERE user_id = ?);
```

## 🚨 Production Debugging

### Emergency Debugging
**Principio**: Nunca interrumpir sesiones musicales activas para debugging.

#### Safe Debugging Practices
- **Read-only operations**: Solo queries que no modifiquen estado
- **Minimal logging**: Evitar spam en logs durante peak usage
- **Feature flags**: Disable funcionalidades problemáticas sin downtime

### Performance Debugging en Vivo
**Herramientas**: New Relic, custom dashboards para métricas musicales.

#### Alertas Críticas
- **API response time >1s**: Para endpoints de partituras
- **Error rate >1%**: Para operaciones de anotaciones
- **Memory usage >80%**: Para servidores de processing
- **CDN hit rate <90%**: Para contenido de partituras

## 🔍 Advanced Debugging Techniques

### User Session Reconstruction
**Capacidad**: Reconstruir sesión de usuario para debugging post-mortem.

#### Data Points Requeridos
- **User actions**: Timestamp + acción + contexto
- **API calls**: Request/response para cada operación
- **Frontend state**: Estado de anotaciones y navegación
- **Performance metrics**: Timing de cada operación

### A/B Testing for Bug Fixes
**Estrategia**: Rollout gradual de fixes para validar efectividad.

#### Implementation Pattern
```javascript
// Feature flag para fix experimental
if (window.faristol.experimentalFix && Math.random() < 0.1) {
  // Nuevo código con fix
  newAnnotationHandler();
} else {
  // Código existing como fallback
  originalAnnotationHandler();
}
```

## 📊 Debugging Metrics

### Success Metrics
- **Bug resolution time**: <24h para críticos, <72h para altos
- **Regression rate**: <2% de bugs introducidos por fixes
- **User satisfaction**: Feedback positivo post-fix
- **Performance impact**: No degradación por debugging tools

### Learning Metrics
- **Root cause patterns**: Identificar causas sistemáticas
- **Prevention opportunities**: Detectar bugs evitables
- **Tool effectiveness**: Qué herramientas ayudan más

---
**Relacionado**: [Development Workflow](development-workflow.md) | [Performance Strategy](../technical/performance-strategy.md)
