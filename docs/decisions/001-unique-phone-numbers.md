# ADR-001: Números de Teléfono como Identificadores Únicos

## Estado
Aceptado

## Contexto
Los usuarios necesitan una forma de recuperar sus cuentas cuando olvidan credenciales. El email puede cambiar o ser inaccesible, pero los números de teléfono tienden a ser más estables.

## Decisión
Los números de teléfono serán únicos en el sistema y obligatorios en formato internacional.

## Justificación

### Beneficios
- **Recuperación de cuenta confiable**: Los teléfonos son más estables que emails
- **Prevención de duplicados**: Un teléfono por usuario evita cuentas múltiples
- **Futuras funcionalidades**: SMS, WhatsApp, verificación 2FA
- **Identificación en soporte**: Atención más personalizada

### Consideraciones
- **Privacidad**: Los números se encriptan en base de datos
- **Formato único**: `(+34)123456789` para consistencia
- **Validación estricta**: Solo números válidos según códigos de país

## Consecuencias

### Positivas
- Usuarios tienen método confiable de recuperación
- Base para futuras funcionalidades de comunicación
- Reduce tickets de soporte por cuentas perdidas

### Negativas
- Usuarios sin teléfono no pueden registrarse
- Complejidad adicional en validación
- Potencial fricción en registro

### Mitigación
- Proceso de validación claro y amigable
- Opción de contacto manual para casos especiales
- Encriptación robusta para proteger privacidad

## Notas de Implementación
- Validación en frontend y backend
- Encriptación automática en modelo
- Índice único en base de datos
- API de validación de códigos de país

---
**Fecha**: 10 de Enero, 2024  
**Autor**: Equipo de Desarrollo  
**Revisores**: [Equipo de Producto, Seguridad]
