# 🧪 Estrategia de Testing - Faristol

## 🎯 Filosofía de Testing

El testing en Faristol debe **validar experiencia musical real**, no solo funcionalidad técnica. Un test que pasa pero rompe el flujo de práctica musical es un test fallido.

## 🌟 Principios Únicos de Testing

### Musical User Journey Testing
**Concepto**: Tests que siguen el journey completo de un músico real.

#### Journey Patterns Testados
- **Descubrimiento**: Buscar → Filtrar → Seleccionar repertorio
- **Práctica**: Abrir → Navegar → Anotar → Practicar
- **Estudio**: Comparar → Analizar → Marcar → Organizar
- **Enseñanza**: Buscar → Asignar → Monitorear → Evaluar

### Context-Aware Testing
**Principio**: Tests que consideran contexto de uso musical.

#### Contextos de Testing
- **Individual practice**: Sesiones largas, navegación intensiva
- **Classroom use**: Múltiples usuarios, dispositivos compartidos
- **Performance prep**: Acceso rápido, funcionalidad offline crítica
- **Casual browsing**: Exploración, descubrimiento de contenido

## 🏗️ Pyramid de Testing Musical

### Unit Tests - Fundamentos Musicales
**Enfoque**: Lógica de negocio específica musical.

#### Areas Críticas
- **Music theory validation**: Validación de tonalidades, compases
- **Search algorithms**: Ranking y relevancia musical
- **Subscription logic**: Límites y permisos por plan
- **File processing**: Validación de PDFs musicales

#### Testing Patterns Musicales
```javascript
// Test específico: Validación de metadata musical
describe('Musical Metadata Validation', () => {
  test('validates key signature format', () => {
    expect(validateKeySignature('C Major')).toBe(true);
    expect(validateKeySignature('G# Minor')).toBe(true);
    expect(validateKeySignature('Invalid Key')).toBe(false);
  });
  
  test('validates time signature', () => {
    expect(validateTimeSignature('4/4')).toBe(true);
    expect(validateTimeSignature('3/4')).toBe(true);
    expect(validateTimeSignature('7/8')).toBe(true);
    expect(validateTimeSignature('invalid')).toBe(false);
  });
});

// Test específico: Lógica de anotaciones
describe('Annotation System', () => {
  test('respects subscription limits', () => {
    const freeUser = { subscription: { type: 0 } };
    const annotations = createMockAnnotations(6); // Más del límite free
    
    expect(canAddAnnotation(freeUser, annotations)).toBe(false);
  });
});
```

### Integration Tests - Flujos Musicales
**Enfoque**: Interacciones entre componentes que afectan experiencia musical.

#### Flujos Críticos Testados
- **PDF Upload Pipeline**: Upload → Processing → Delivery
- **Search Integration**: Query → Index → Results → Ranking
- **Authentication Flow**: Login → Token → Permissions → Access
- **Subscription Workflow**: Selection → Payment → Activation → Access

#### Musical Integration Examples
```javascript
// Test de flujo completo de práctica
describe('Practice Session Flow', () => {
  test('complete practice workflow', async () => {
    // 1. User searches for music
    const searchResults = await searchMusic('Bach Invention');
    expect(searchResults.length).toBeGreaterThan(0);
    
    // 2. Selects and loads score
    const score = await loadMusicScore(searchResults[0].id);
    expect(score.pages).toBeDefined();
    expect(score.pages.length).toBeGreaterThan(0);
    
    // 3. Creates annotation
    const annotation = await createAnnotation({
      scoreId: score.id,
      page: 1,
      content: 'Practice slowly here'
    });
    expect(annotation.id).toBeDefined();
    
    // 4. Navigates between pages
    const nextPage = await navigateToPage(score.id, 2);
    expect(nextPage.content).toBeDefined();
  });
});
```

### End-to-End Tests - Experiencia Musical Completa
**Enfoque**: Validar experiencia completa desde perspectiva del músico.

#### Scenarios Musicales
- **New musician onboarding**: Registro → Verificación → Primera búsqueda → Primera práctica
- **Regular practice session**: Login → Búsqueda rápida → Práctica con anotaciones
- **Subscription upgrade**: Trial → Límite alcanzado → Upgrade → Funcionalidad completa
- **Educational workflow**: Profesor busca → Asigna → Estudiante practica

## 🎨 Testing Musical Específico

### Performance Testing Musical
**Enfoque**: Performance tests que reflejan uso musical real.

#### Musical Performance Scenarios
```javascript
// Test de performance durante práctica
describe('Musical Performance Tests', () => {
  test('page navigation under 200ms', async () => {
    const startTime = performance.now();
    await navigateToNextPage(scoreId);
    const endTime = performance.now();
    
    expect(endTime - startTime).toBeLessThan(200);
  });
  
  test('annotation response under 50ms', async () => {
    const startTime = performance.now();
    await createAnnotation(annotationData);
    const endTime = performance.now();
    
    expect(endTime - startTime).toBeLessThan(50);
  });
  
  test('search results under 500ms', async () => {
    const startTime = performance.now();
    const results = await searchMusic('Mozart');
    const endTime = performance.now();
    
    expect(endTime - startTime).toBeLessThan(500);
    expect(results.length).toBeGreaterThan(0);
  });
});
```

### Device-Specific Testing
**Estrategia**: Testing en dispositivos que músicos realmente usan.

#### Priority Device Testing
- **iPad (múltiples generaciones)**: Dispositivo primario para práctica
- **Android tablets**: Samsung Galaxy Tab, superficie común
- **Smartphones**: Testing de búsqueda rápida y referencia
- **Desktop/Laptop**: Gestión de biblioteca y uploads

#### Device Testing Matrix
```javascript
// Testing responsivo musical
describe('Device Compatibility', () => {
  const devices = [
    { name: 'iPad Pro', width: 1024, height: 1366 },
    { name: 'iPad Mini', width: 768, height: 1024 },
    { name: 'Galaxy Tab', width: 800, height: 1280 },
    { name: 'iPhone', width: 375, height: 667 }
  ];
  
  devices.forEach(device => {
    test(`score readability on ${device.name}`, async () => {
      await setViewport(device.width, device.height);
      await loadMusicScore(testScoreId);
      
      // Verificar que texto musical es legible
      const scoreContent = await getScoreContent();
      expect(scoreContent.isReadable).toBe(true);
    });
  });
});
```

### Accessibility Testing Musical
**Enfoque**: Accesibilidad específica para músicos con necesidades especiales.

#### Musical Accessibility Tests
- **Screen reader compatibility**: Para músicos con problemas visuales
- **High contrast mode**: Validar legibilidad en modo alto contraste
- **Large text support**: Texto agrandado sin afectar partituras
- **Keyboard navigation**: Navegación completa sin mouse

## 🔧 Tools y Frameworks Musical

### Custom Testing Utilities
**Desarrollo**: Herramientas específicas para testing musical.

#### Musical Test Helpers
```javascript
// Utilidades de testing musical
class MusicalTestUtils {
  static async createMockMusicScore(options = {}) {
    return {
      id: generateId(),
      name: options.name || 'Test Score',
      composer: options.composer || 'Test Composer',
      pages: options.pages || 3,
      difficulty: options.difficulty || 'intermediate',
      ...options
    };
  }
  
  static async simulatePracticeSession(scoreId, duration = 30000) {
    // Simula sesión de práctica realista
    await loadScore(scoreId);
    
    for (let i = 0; i < duration / 5000; i++) {
      await navigateToRandomPage();
      await wait(1000);
      
      if (Math.random() > 0.7) {
        await createRandomAnnotation();
      }
      
      await wait(4000);
    }
  }
  
  static validateMusicalMetadata(score) {
    return {
      hasValidKeySignature: isValidKeySignature(score.key_signature),
      hasValidTimeSignature: isValidTimeSignature(score.time_signature),
      hasValidDifficulty: ['beginner', 'intermediate', 'advanced'].includes(score.difficulty),
      hasValidComposer: score.composer && score.composer.length > 0
    };
  }
}
```

### Continuous Testing Musical
**Estrategia**: Testing continuo que monitorea calidad musical.

#### Musical CI/CD Testing
- **Pre-commit hooks**: Validar funcionalidad musical básica
- **PR testing**: Tests completos de flujos musicales
- **Staging validation**: Testing con contenido musical real
- **Production monitoring**: Tests sintéticos continuos

## 📊 Musical Test Data

### Realistic Test Content
**Principio**: Usar contenido musical realista en tests.

#### Test Data Strategy
- **Public domain scores**: Bach, Mozart, Beethoven para testing
- **Synthetic metadata**: Metadata musical válido pero no real
- **User personas**: Perfiles realistas de diferentes tipos de músicos
- **Usage patterns**: Patrones de uso basados en comportamiento real

### Test User Personas
**Implementación**: Diferentes tipos de usuarios para testing completo.

#### Musical User Types
```javascript
const testPersonas = {
  casualMusician: {
    subscription: { type: 0 }, // Free
    instruments: ['piano'],
    level: 'beginner',
    usage: 'occasional_practice'
  },
  
  musicStudent: {
    subscription: { type: 1 }, // Basic
    instruments: ['violin', 'piano'],
    level: 'intermediate',
    usage: 'regular_study'
  },
  
  musicTeacher: {
    subscription: { type: 2 }, // Premium
    instruments: ['multiple'],
    level: 'advanced',
    usage: 'teaching_preparation'
  },
  
  professionalMusician: {
    subscription: { type: 2 }, // Premium
    instruments: ['specialized'],
    level: 'professional',
    usage: 'performance_preparation'
  }
};
```

## 🚨 Testing Crisis Scenarios

### Musical Emergency Testing
**Enfoque**: Testing de scenarios críticos para músicos.

#### Crisis Scenarios
- **Pre-concert failure**: Sistema falla justo antes de concierto
- **Mid-practice interruption**: Pérdida de acceso durante práctica
- **Exam day issues**: Problemas durante exámenes importantes
- **Class presentation failure**: Falla durante presentación educativa

### Recovery Testing
**Validación**: Capacidad de recovery sin pérdida de trabajo musical.

#### Recovery Scenarios
```javascript
describe('Musical Recovery Scenarios', () => {
  test('recovers annotations after network failure', async () => {
    // Crear anotaciones
    const annotations = await createMultipleAnnotations(5);
    
    // Simular pérdida de red
    await simulateNetworkFailure();
    
    // Verificar que anotaciones persisten localmente
    const localAnnotations = await getLocalAnnotations();
    expect(localAnnotations.length).toBe(5);
    
    // Restaurar red y verificar sincronización
    await restoreNetwork();
    await waitForSync();
    
    const syncedAnnotations = await getServerAnnotations();
    expect(syncedAnnotations.length).toBe(5);
  });
});
```

---
**Relacionado**: [Development Workflow](../guides/development-workflow.md) | [Debugging Guide](../guides/debugging-guide.md)
