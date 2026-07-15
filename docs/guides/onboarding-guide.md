# 👋 Guía de Incorporación - Faristol

## 🎯 Bienvenido al Equipo

Esta guía está diseñada para que cualquier desarrollador pueda contribuir efectivamente a Faristol en su primera semana, entendiendo no solo el "cómo" sino el "por qué" de nuestras decisiones.

## 🌟 Lo que Hace Único a Faristol

### No Somos Spotify para Partituras
**Diferencia clave**: No vendemos acceso a contenido, vendemos herramientas para interactuar con contenido libre.

### Educación > Comercio
**Prioridad**: Las decisiones educativas siempre ganan sobre las comerciales.
**Ejemplo**: Mantener partituras gratuitas aunque podríamos cobrar por ellas.

### Músicos Reales, Problemas Reales
**Enfoque**: Cada feature resuelve un problema real que hemos observado en músicos.
**Validación**: Testeo constante con profesores y estudiantes de música.

## 🏗️ Arquitectura Mental

### Piensa en Capas
```
Usuarios (Músicos, Profesores, Estudiantes)
    ↓
Herramientas (Anotaciones, Favoritos, Búsqueda)
    ↓
Contenido (Partituras, Compositores)
    ↓
Infrastructure (Storage, Processing, Analytics)
```

### Flujo de Valor
**Pregunta clave**: ¿Esto ayuda a un músico a tocar mejor?
**Si no**: ¿Al menos no interfiere con su práctica?

## 🎭 Entendiendo los Roles

### El Usuario Evolutivo
**Concepto**: Un usuario puede evolucionar de músico → compositor → educador sin perder su historial.

#### Journey Típico
1. **Registro**: Músico aficionado buscando partituras
2. **Engagement**: Descubre anotaciones, empieza a usar
3. **Upgrade**: Necesita más anotaciones, se suscribe
4. **Contribution**: Quiere subir contenido, solicita ser compositor
5. **Community**: Se convierte en referente, ayuda a otros

### El Administrador Curador
**Rol**: No solo gestor técnico, sino curador de calidad musical.
**Decisiones**: Equilibrar acceso abierto con calidad educativa.

## 🔧 Configuración de Desarrollo

### Prerequisitos Conceptuales
Antes de escribir código, entiende estos conceptos:

#### 1. Anotaciones Frontend-First
Las anotaciones viven en el navegador y se sincronizan al servidor.
**Por qué**: Respuesta inmediata > Persistencia inmediata.

#### 2. Suscripciones Progresivas
No bloqueamos contenido, limitamos herramientas de productividad.
**Por qué**: Filosofía educativa > Máximas ganancias.

#### 3. Compositores Duales
Hay compositores históricos (Bach) y usuarios-compositores (modernos).
**Por qué**: Preservar integridad histórica + Permitir contenido nuevo.

### Setup Técnico Local

#### 1. Ambiente Base
```bash
# Clonar y configurar
git clone [repo] faristol
cd faristol
cp .env.example .env.local

# Docker environment
docker-compose up -d
```

#### 2. Datos de Prueba Musicales
```bash
# Seeders con datos realistas
php artisan db:seed --class=MusicalTestDataSeeder
# Esto crea: Bach, Mozart, partituras reales, usuarios de prueba
```

#### 3. Configuración de Storage
```bash
# MinIO local para simular S3
# Subir algunas partituras de prueba
php artisan storage:seed-test-scores
```

## 🎨 Patrones de Desarrollo

### 1. Mobile-First Mindset
**Regla**: Si no funciona bien en móvil, no está terminado.
**Razón**: Los músicos usan tablets durante práctica.

### 2. Educational Edge Cases
**Principio**: Siempre considerar uso en aulas.
**Ejemplos**: 
- ¿Qué pasa si 30 estudiantes acceden a la misma partitura?
- ¿Cómo se comporta con conexión lenta de escuela?

### 3. Graceful Degradation
**Filosofía**: La música no debe parar por problemas técnicos.
**Implementación**: Fallbacks para todo lo crítico.

## 📋 Primera Semana - Tareas Sugeridas

### Día 1-2: Exploración
- [ ] Registrarte como usuario y explorar como músico
- [ ] Subir una partitura de prueba
- [ ] Usar el sistema de anotaciones
- [ ] Probar diferentes niveles de suscripción

### Día 3-4: Código Base
- [ ] Revisar los ADRs más importantes
- [ ] Entender el flujo de upload de PDFs
- [ ] Explorar el sistema de roles y permisos
- [ ] Revisar tests principales

### Día 5: Primera Contribución
- [ ] Encontrar un bug menor o mejora de UX
- [ ] Hacer PR con test incluido
- [ ] Participar en code review

## 🎯 Áreas de Contribución

### Frontend (Vue.js/JavaScript)
**Enfoque**: Experiencia de usuario fluida, especialmente en mobile.
**Prioridades**: Performance, accesibilidad, interfaz intuitiva.

### Backend (Laravel/PHP)
**Enfoque**: API robusta, procesamiento eficiente de archivos.
**Prioridades**: Seguridad, escalabilidad, lógica de negocio clara.

### DevOps/Infrastructure
**Enfoque**: Deployments sin downtime, monitoreo efectivo.
**Prioridades**: Reliability, performance, cost optimization.

### UX/Product
**Enfoque**: Investigación con usuarios reales (músicos).
**Prioridades**: Usabilidad, accesibilidad, flujos educativos.

## 🤝 Cultura de Equipo

### Principios de Colaboración
1. **Pregunta antes de asumir**: Cada decisión tiene contexto musical
2. **Testea con músicos reales**: Tu abuela pianista > 100 developers
3. **Performance matters**: Un músico no puede esperar 3 segundos por una partitura
4. **Accessibility first**: La música debe ser para todos

### Code Review Philosophy
- **Funcionalidad**: ¿Resuelve un problema real de músicos?
- **Mantenibilidad**: ¿Podrá entenderlo el equipo en 6 meses?
- **Performance**: ¿Impacta la experiencia musical?
- **Educational impact**: ¿Mejora o complica el uso educativo?

## 🚀 Próximos Pasos

### Después de la Primera Semana
1. **Especialización**: Elegir área de enfoque principal
2. **Mentorship**: Conseguir mentor en el equipo
3. **User research**: Participar en sesiones con músicos
4. **Architecture**: Entender decisiones de largo plazo

### Crecimiento Continuo
- **Musical knowledge**: Aprender conceptos musicales básicos
- **Educational understanding**: Entender cómo se enseña música
- **Performance optimization**: Especializarse en web performance
- **Security expertise**: Profundizar en seguridad de datos sensibles

---
**¿Preguntas?** Contacta a cualquier miembro del equipo. Preferimos mil preguntas a una asunción incorrecta.

**Próximo paso**: [Development Workflow](development-workflow.md)
