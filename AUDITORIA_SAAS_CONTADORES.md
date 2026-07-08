# Auditoría funcional y roadmap del SaaS para despachos contables

## 1. Objetivo de este documento

Este archivo define el contexto funcional del sistema, lo que actualmente resuelve, las capacidades que se quieren incorporar y los criterios que deben utilizarse para auditar el desarrollo.

Debe servir como referencia para:

- Codex y otros agentes de desarrollo.
- Planeación funcional.
- Auditoría de módulos existentes.
- Diseño de nuevas funcionalidades.
- Priorización del roadmap.
- Validación de reglas de negocio.
- Evitar que el sistema evolucione como una colección aislada de herramientas.

La meta del producto es convertirse en un **sistema operativo para despachos contables**, no solamente en una herramienta de descarga de documentos fiscales.

---

# 2. Visión del producto

El sistema está dirigido principalmente a:

- Contadores independientes.
- Despachos contables.
- Auxiliares contables.
- Supervisores fiscales.
- Socios o administradores de despacho.
- Empresas que administran múltiples contribuyentes.
- Clientes finales de los despachos.

La plataforma debe permitir que un contador administre desde un solo lugar:

1. Su cartera de contribuyentes.
2. La información fiscal de cada cliente.
3. Los CFDI emitidos y recibidos.
4. Las obligaciones fiscales.
5. La preparación y revisión de impuestos.
6. El seguimiento de requerimientos.
7. La seguridad social.
8. Las declaraciones y pagos.
9. Los documentos y evidencias.
10. Las tareas internas del despacho.
11. La comunicación con el contribuyente.
12. El cierre mensual de cada cliente.

---

# 3. Estado funcional actual

De acuerdo con el desarrollo existente, el sistema ya cuenta con las siguientes capacidades principales.

## 3.1 Gestión de contribuyentes

El sistema permite administrar contribuyentes y centralizar información como:

- RFC.
- Razón social.
- Régimen fiscal.
- Datos de contacto.
- Documentos fiscales.
- Credenciales necesarias para consultas.
- Información general del contribuyente.

### Objetivo de auditoría

Validar que cada contribuyente pueda funcionar como una entidad central del sistema y que todos los módulos posteriores estén relacionados correctamente con él.

---

## 3.2 Descarga masiva de CFDI

El sistema permite:

- Descargar CFDI emitidos.
- Descargar CFDI recibidos.
- Descargar XML.
- Filtrar por fecha.
- Filtrar por tipo de comprobante.
- Filtrar por RFC emisor o receptor.
- Filtrar por estado del comprobante.
- Consultar metadatos.
- Mantener un repositorio fiscal por contribuyente.

### Objetivo de auditoría

Revisar:

- Integridad de las descargas.
- Detección de CFDI duplicados.
- Estado vigente o cancelado.
- Relación entre CFDI y contribuyente.
- Procesamiento de conceptos.
- Procesamiento de impuestos.
- Procesamiento de complementos.
- Manejo de errores del SAT.
- Reintentos.
- Trazabilidad de solicitudes.
- Separación correcta por ejercicio y periodo.
- Capacidad de volver a procesar XML.
- Validación del UUID.
- Persistencia del XML original.

---

## 3.3 Constancia de Situación Fiscal

El sistema permite generar o consultar la Constancia de Situación Fiscal de los contribuyentes.

### Objetivo de auditoría

Validar que la información obtenida se refleje en el expediente del contribuyente:

- RFC.
- Nombre o razón social.
- Régimen fiscal.
- Actividades económicas.
- Domicilio fiscal.
- Obligaciones.
- Fecha de inicio de operaciones.
- Estatus del contribuyente.
- Fecha de consulta.
- Documento PDF original.

---

## 3.4 Opinión de Cumplimiento

El sistema permite consultar y descargar la opinión de cumplimiento.

### Objetivo de auditoría

Registrar:

- Resultado positivo o negativo.
- Fecha de consulta.
- Fecha de emisión.
- Tipo de opinión.
- Documento PDF.
- Motivos de incumplimiento, cuando existan.
- Historial de consultas.
- Alertas por opinión negativa.
- Responsable asignado para seguimiento.

---

## 3.5 Credenciales cifradas

El sistema almacena credenciales de los contribuyentes de forma cifrada.

### Objetivo de auditoría

Validar:

- Cifrado en reposo.
- Credenciales no visibles en texto plano.
- Acceso restringido por rol.
- Bitácora de acceso.
- Rotación de credenciales.
- Fecha de última actualización.
- Manejo de credenciales incorrectas.
- Bloqueo por intentos fallidos.
- Protección de secretos en logs.
- Separación de contraseña de usuario y credenciales fiscales.
- Política de respaldo.
- Política de eliminación.
- Consentimiento del contribuyente.

---

## 3.6 Panel de control

El sistema cuenta con un dashboard general.

Actualmente debe mostrar, como mínimo:

- Certificados próximos a vencer.
- Accesos recientes.
- Alertas.
- Estado general de los contribuyentes.
- Métricas operativas.
- Tickets o incidencias.
- Actividad reciente.

### Objetivo de auditoría

Comprobar que cada indicador sea accionable.

Un indicador no debe ser únicamente visual. Debe permitir navegar al listado que originó la métrica.

Ejemplo:

```text
8 opiniones negativas
→ abrir listado de contribuyentes
→ mostrar fecha de consulta
→ mostrar responsable
→ mostrar acción pendiente
```

---

# 4. Problema actual del producto

El sistema ya resuelve una parte importante de la operación:

- Obtención de documentos.
- Consulta de información fiscal.
- Concentración de datos.
- Descarga de CFDI.
- Administración de contribuyentes.

Sin embargo, todavía debe evolucionar de:

> Repositorio y herramienta de consulta fiscal.

A:

> Plataforma de control, análisis, cumplimiento y cierre mensual para despachos contables.

La siguiente etapa debe enfocarse en transformar datos fiscales en:

- Alertas.
- Conciliaciones.
- Papeles de trabajo.
- Cálculos preliminares.
- Tareas.
- Flujos de autorización.
- Evidencias.
- Seguimiento.
- Decisiones operativas.

---

# 5. Meta funcional

El contador debe poder entrar al sistema y responder rápidamente:

- ¿Qué clientes tienen obligaciones pendientes?
- ¿Qué declaraciones vencen próximamente?
- ¿Qué contribuyentes presentan incumplimientos?
- ¿Qué CFDI tienen inconsistencias?
- ¿Cuánto IVA se cobró?
- ¿Cuánto IVA se pagó?
- ¿Cuánto IVA es acreditable?
- ¿Qué facturas PPD no tienen complemento?
- ¿Qué pagos bancarios no están relacionados con CFDI?
- ¿Qué clientes no han entregado estados de cuenta?
- ¿Qué periodos están listos para cierre?
- ¿Qué requerimientos deben atenderse?
- ¿Qué devoluciones siguen en proceso?
- ¿Qué diferencias existen entre emisión IMSS y SUA?
- ¿Qué certificados están próximos a vencer?
- ¿Qué tareas tiene asignadas cada miembro del despacho?

---

# 6. Módulos objetivo

## 6.1 Expediente integral del contribuyente

Cada contribuyente debe contar con un expediente organizado por secciones.

### Pestañas sugeridas

```text
Resumen
Información fiscal
Regímenes
Obligaciones
CFDI
Impuestos
Declaraciones
Buzón Tributario
Devoluciones
IMSS / INFONAVIT
Documentos
Tareas
Bitácora
Accesos
Configuración
```

### Resumen del contribuyente

Debe mostrar:

- RFC.
- Razón social.
- Tipo de persona.
- Régimen principal.
- Estatus.
- Responsable interno.
- Próxima obligación.
- Impuesto estimado del periodo.
- Última descarga de CFDI.
- Última opinión de cumplimiento.
- Última constancia fiscal.
- Certificados próximos a vencer.
- Alertas activas.
- Documentos pendientes.
- Periodo contable abierto.
- Estado del cierre mensual.

---

## 6.2 Dashboard del despacho

El dashboard debe mostrar el estado completo de la cartera.

### Indicadores recomendados

- Contribuyentes activos.
- Contribuyentes con alertas.
- Contribuyentes con opinión negativa.
- Declaraciones pendientes.
- Declaraciones vencidas.
- Periodos abiertos.
- Periodos en revisión.
- Periodos cerrados.
- CFDI nuevos.
- CFDI cancelados.
- CFDI con inconsistencias.
- PPD sin complemento.
- Certificados próximos a vencer.
- Credenciales inválidas.
- Requerimientos pendientes.
- Devoluciones en proceso.
- Diferencias IMSS.
- Clientes sin estados de cuenta.
- Tareas vencidas.
- Tareas por usuario.

### Regla

Todo KPI debe permitir:

- Filtrar.
- Navegar al detalle.
- Exportar.
- Asignar responsable.
- Cambiar estado.
- Crear tarea.

---

## 6.3 Centro fiscal mensual

Este debe ser uno de los módulos principales.

Por contribuyente y periodo debe mostrar:

### Ingresos

- Total facturado.
- Total cobrado.
- Ingresos PUE.
- Ingresos PPD.
- Ingresos pendientes de cobro.
- Notas de crédito.
- CFDI cancelados.
- Base gravada por tasa.
- Ingresos exentos.
- Ingresos tasa cero.

### Egresos

- Total facturado.
- Total pagado.
- Gastos PUE.
- Gastos PPD.
- Gastos pendientes de pago.
- Notas de crédito.
- CFDI cancelados.
- Gastos sin soporte.
- Gastos posiblemente no deducibles.

### Impuestos

- IVA trasladado.
- IVA efectivamente cobrado.
- IVA acreditable.
- IVA efectivamente pagado.
- IVA retenido.
- ISR retenido.
- Retenciones sufridas.
- Retenciones por enterar.
- Impuesto preliminar.
- Saldo a cargo.
- Saldo a favor.

### Estado operativo

- CFDI descargados.
- Estados de cuenta cargados.
- Conciliación completada.
- Inconsistencias revisadas.
- Papeles de trabajo generados.
- Cálculo revisado.
- Autorización del cliente.
- Declaración presentada.
- Pago registrado.
- Periodo cerrado.

---

## 6.4 Determinación y conciliación de impuestos

El sistema no debe limitarse a comparar “impuestos cobrados contra impuestos pagados”.

Debe separar:

- IVA facturado.
- IVA efectivamente cobrado.
- IVA acreditable facturado.
- IVA efectivamente pagado.
- IVA retenido al contribuyente.
- IVA retenido a terceros.
- ISR provisional.
- ISR retenido.
- Retenciones.
- IEPS, cuando aplique.
- Otros impuestos configurables.

### Reglas funcionales

#### CFDI PUE

El sistema puede utilizar el CFDI como indicio de pago, pero debe permitir:

- Confirmación bancaria.
- Ajuste manual.
- Evidencia.
- Regla configurable por despacho.

#### CFDI PPD

El sistema debe utilizar complementos de pago para determinar:

- Fecha de pago.
- Monto pagado.
- Parcialidad.
- Saldo anterior.
- Saldo insoluto.
- Impuestos proporcionales.
- Moneda.
- Tipo de cambio.

### Trazabilidad requerida

Cada monto debe permitir navegar:

```text
Impuesto
→ periodo
→ contribuyente
→ cliente o proveedor
→ CFDI
→ complemento
→ movimiento bancario
→ evidencia
```

---

## 6.5 Conciliación bancaria

El sistema debe permitir importar:

- Excel.
- CSV.
- Archivos bancarios.
- Estados de cuenta estructurados.
- Carga manual.

### Funciones

- Relacionar depósitos con CFDI emitidos.
- Relacionar retiros con CFDI recibidos.
- Relacionar un movimiento con varios CFDI.
- Relacionar varios movimientos con un CFDI.
- Manejar pagos parciales.
- Manejar diferencias por centavos.
- Detectar duplicados.
- Detectar depósitos sin factura.
- Detectar facturas sin depósito.
- Detectar pagos sin CFDI.
- Detectar transferencias internas.
- Detectar comisiones.
- Detectar préstamos.
- Detectar aportaciones de socios.
- Detectar devoluciones.
- Clasificar movimientos.
- Permitir reglas automáticas.
- Permitir confirmación manual.

### Estados sugeridos

- Sin conciliar.
- Coincidencia sugerida.
- Conciliado automáticamente.
- Conciliado manualmente.
- Con diferencia.
- Ignorado con justificación.
- Pendiente de documentación.

---

## 6.6 Auditoría automática de CFDI

El sistema debe detectar inconsistencias.

### Reglas sugeridas

- CFDI duplicado.
- CFDI cancelado.
- CFDI vigente con sustitución.
- PPD sin complemento.
- Complemento sin CFDI relacionado.
- Diferencia entre factura y pagos.
- Pago mayor al saldo.
- Saldo insoluto incorrecto.
- Forma de pago inconsistente.
- Método de pago inconsistente.
- RFC inválido.
- Uso de CFDI incompatible.
- Régimen fiscal inconsistente.
- Código postal inconsistente.
- Moneda o tipo de cambio inconsistente.
- IVA mal calculado.
- Retención no considerada.
- Nota de crédito sin CFDI relacionado.
- Nómina de empleado dado de baja.
- Factura fuera del periodo.
- Fecha futura.
- Factura recibida después del cierre.
- CFDI cancelado después de ser considerado.
- Diferencia entre XML y registros internos.
- Proveedor bloqueado.
- Proveedor en lista de riesgo.
- Concepto posiblemente no deducible.
- Pago bancario sin CFDI.
- CFDI sin evidencia de pago.

### Severidades

- Informativa.
- Advertencia.
- Importante.
- Crítica.

### Tratamiento de alertas

Cada alerta debe poder:

- Asignarse.
- Comentarse.
- Justificarse.
- Corregirse.
- Ignorarse con autorización.
- Convertirse en tarea.
- Cerrarse.
- Reabrirse.
- Conservar evidencia.
- Registrar bitácora.

---

# 7. Integración de módulos adicionales

## 7.1 Devoluciones SAT

El sistema debe manejar cada devolución como expediente.

### Datos

- Contribuyente.
- Impuesto.
- Periodo.
- Monto solicitado.
- Monto autorizado.
- Monto pagado.
- Fecha de solicitud.
- Folio.
- Número de control.
- Estado.
- Responsable.
- Fecha límite.
- Cuenta bancaria.
- Documentos.
- Observaciones.

### Estados

- Borrador.
- Preparación.
- Presentada.
- En proceso.
- Requerida.
- Atendiendo requerimiento.
- Desistida.
- Negada.
- Autorizada.
- Pagada.
- Cerrada.

### Funciones

- Adjuntar acuse.
- Adjuntar requerimientos.
- Adjuntar respuestas.
- Registrar movimientos.
- Registrar comunicaciones.
- Crear tareas.
- Alertar vencimientos.
- Calcular días transcurridos.
- Registrar resolución.
- Registrar depósito.
- Mantener historial completo.

---

## 7.2 Buzón Tributario

Debe funcionar como centro de notificaciones y requerimientos.

### Tipos de registro

- Mensaje informativo.
- Invitación.
- Carta invitación.
- Requerimiento.
- Notificación formal.
- Resolución.
- Oficio.
- Aviso.

### Datos

- Contribuyente.
- Fecha de recepción.
- Fecha de apertura.
- Autoridad.
- Número de oficio.
- Asunto.
- Resumen.
- Fecha límite.
- Responsable.
- Estado.
- Documento original.
- Evidencia de respuesta.
- Acuse.

### Estados

- Nuevo.
- No abierto.
- En análisis.
- En atención.
- Esperando información.
- Respondido.
- Cerrado.
- Vencido.

### Alertas

- No abierto.
- Sin responsable.
- Vence en 5 días.
- Vence en 3 días.
- Vence mañana.
- Vencido.
- Respuesta sin presentar.
- Presentado sin acuse.
- Cerrado sin evidencia.

---

## 7.3 SIPARE e IMSS

El sistema debe controlar obligaciones patronales sin intentar reemplazar inicialmente al SUA.

### Entidades principales

- Registro patronal.
- Periodo.
- Emisión mensual.
- Emisión bimestral.
- Archivo SUA.
- Línea SIPARE.
- Pago.
- Comprobante.
- Diferencia.
- Trabajador.
- Movimiento afiliatorio.

### Estados del periodo

- Abierto.
- Información pendiente.
- Emisión cargada.
- SUA cargado.
- Comparado.
- Con diferencias.
- Corregido.
- Línea generada.
- Pagado.
- Cerrado.
- Vencido.

---

## 7.4 Emisión IMSS vs SUA

El sistema debe comparar, por trabajador:

- NSS.
- Nombre.
- Salario base de cotización.
- Días cotizados.
- Incapacidades.
- Ausentismos.
- Fecha de alta.
- Fecha de baja.
- Modificación salarial.
- Crédito INFONAVIT.
- Cuotas IMSS.
- RCV.
- INFONAVIT.
- Total.

### Tipos de diferencia

- Trabajador en emisión, no en SUA.
- Trabajador en SUA, no en emisión.
- Diferencia salarial.
- Diferencia de días.
- Alta no reflejada.
- Baja no reflejada.
- Modificación salarial pendiente.
- Incapacidad no considerada.
- Ausentismo no considerado.
- Crédito INFONAVIT diferente.
- Diferencia de importe.
- Diferencia por actualización.
- Diferencia por recargos.

### Acciones

- Justificar.
- Corregir.
- Ignorar con autorización.
- Crear tarea.
- Adjuntar evidencia.
- Marcar resuelta.

---

# 8. Papeles de trabajo

El sistema debe generar papeles de trabajo por contribuyente y periodo.

## Reportes sugeridos

- Relación de ingresos.
- Relación de egresos.
- IVA trasladado.
- IVA acreditable.
- IVA cobrado.
- IVA pagado.
- Retenciones.
- Nómina emitida.
- Complementos de pago.
- CFDI cancelados.
- Notas de crédito.
- PPD sin complemento.
- Complementos sin factura.
- Facturas sin pago.
- Pagos sin factura.
- Operaciones con público en general.
- Top clientes.
- Top proveedores.
- Proveedores nuevos.
- Clientes nuevos.
- Variaciones mensuales.
- Variaciones anuales.
- Conciliación bancaria.
- Determinación preliminar.
- Diferencias detectadas.
- Documentación pendiente.

### Requisitos

- Exportación a Excel.
- Exportación a PDF.
- Filtros.
- Columnas configurables.
- Trazabilidad hasta XML.
- Firmas o aprobación.
- Versión del papel de trabajo.
- Fecha de generación.
- Usuario que lo generó.
- Bloqueo después del cierre.

---

# 9. Calendario fiscal

El calendario debe generarse desde las obligaciones de cada contribuyente.

### Eventos

- Declaraciones mensuales.
- Declaración anual.
- Pagos provisionales.
- IVA.
- ISR.
- Retenciones.
- DIOT.
- Contabilidad electrónica.
- Informativas.
- IMSS.
- INFONAVIT.
- Impuesto sobre nómina.
- Prima de riesgo.
- Renovación de e.firma.
- Renovación de certificados.
- Opiniones de cumplimiento.
- Requerimientos.
- Devoluciones.
- Obligaciones estatales.

### Estados

- Por iniciar.
- En preparación.
- Esperando información.
- En revisión.
- Autorizada.
- Presentada.
- Pagada.
- Cerrada.
- Vencida.
- No aplicable.

### Alertas

- Vence en 10 días.
- Vence en 5 días.
- Vence en 3 días.
- Vence mañana.
- Vencida.
- Sin responsable.
- Sin documentación.
- Sin autorización.
- Sin comprobante de pago.

---

# 10. Flujo mensual del contribuyente

Cada periodo fiscal debe funcionar como expediente.

```text
Periodo creado
    ↓
Información pendiente
    ↓
CFDI descargados
    ↓
Estados de cuenta cargados
    ↓
Conciliación bancaria
    ↓
Inconsistencias detectadas
    ↓
Inconsistencias revisadas
    ↓
Papeles de trabajo generados
    ↓
Cálculo preliminar
    ↓
Revisión interna
    ↓
Autorización del cliente
    ↓
Declaración presentada
    ↓
Pago registrado
    ↓
Periodo cerrado
```

### Reglas

- No cerrar un periodo con alertas críticas sin autorización.
- No modificar cifras cerradas sin reapertura.
- Toda reapertura debe registrar motivo.
- Toda modificación debe quedar en bitácora.
- El cierre debe guardar una fotografía de cifras y documentos.
- Las cifras históricas no deben depender de datos que puedan cambiar después.

---

# 11. Portal del cliente

El contribuyente debe contar con acceso limitado.

### Puede ver

- Estado general.
- Obligaciones próximas.
- Impuestos estimados.
- Declaraciones pendientes.
- Líneas de captura.
- Acuses.
- Opiniones de cumplimiento.
- Documentos solicitados.
- Mensajes del contador.
- Requerimientos.
- Estado de devoluciones.
- Historial de periodos.

### Puede hacer

- Subir estados de cuenta.
- Subir documentos.
- Responder solicitudes.
- Aprobar cálculos.
- Rechazar con comentario.
- Descargar declaraciones.
- Descargar líneas de captura.
- Cargar comprobantes de pago.
- Confirmar información.
- Consultar tareas pendientes.

### No debe poder

- Ver credenciales fiscales.
- Modificar cálculos internos.
- Ver información de otros contribuyentes.
- Cerrar periodos.
- Eliminar evidencias.
- Cambiar reglas fiscales.

---

# 12. Roles y permisos

## Roles sugeridos

### Administrador de plataforma

- Configuración global.
- Suscripciones.
- Despachos.
- Seguridad.
- Catálogos.
- Auditoría técnica.

### Socio del despacho

- Acceso total al despacho.
- Configuración.
- Usuarios.
- Clientes.
- Reportes.
- Cierres.
- Autorizaciones.

### Supervisor

- Revisar periodos.
- Asignar trabajo.
- Aprobar cierres.
- Consultar indicadores.
- Resolver alertas.

### Contador

- Gestionar contribuyentes.
- Revisar CFDI.
- Preparar impuestos.
- Generar papeles.
- Presentar declaraciones.
- Registrar pagos.

### Auxiliar

- Cargar documentos.
- Clasificar movimientos.
- Conciliar.
- Atender tareas.
- Preparar información.

### Auditor

- Consulta.
- Reportes.
- Bitácoras.
- Evidencias.
- Sin modificación crítica.

### Cliente

- Portal limitado.
- Carga de documentos.
- Autorizaciones.
- Consulta.

---

# 13. Bitácora y trazabilidad

Toda acción relevante debe registrar:

- Usuario.
- Fecha.
- Hora.
- IP.
- Sesión.
- Módulo.
- Entidad.
- Acción.
- Valor anterior.
- Valor nuevo.
- Motivo.
- Documento relacionado.

### Acciones obligatorias en bitácora

- Acceso a credenciales.
- Descarga de documentos.
- Consulta al SAT.
- Cambio de estatus.
- Modificación de impuestos.
- Conciliación manual.
- Justificación de alerta.
- Cierre de periodo.
- Reapertura.
- Eliminación.
- Cambio de permisos.
- Autorización del cliente.
- Registro de declaración.
- Registro de pago.

---

# 14. Seguridad

## Requisitos mínimos

- Cifrado de credenciales.
- Roles y permisos.
- Separación por despacho.
- Separación por contribuyente.
- Protección contra acceso cruzado.
- Bitácora.
- Doble factor opcional.
- Sesiones seguras.
- Caducidad de sesión.
- Protección de archivos.
- URLs temporales.
- No exponer secretos en logs.
- Respaldo.
- Recuperación.
- Control de descargas.
- Control de exportaciones.
- Política de eliminación.
- Auditoría de accesos.

## Multi-tenant

Debe verificarse que:

- Un despacho no pueda consultar datos de otro.
- Un usuario no pueda acceder a contribuyentes no asignados.
- Los archivos estén separados.
- Los jobs respeten el tenant.
- Los reportes respeten el tenant.
- Las consultas globales estén restringidas.
- Las rutas y policies validen pertenencia.

---

# 15. Modelo de estados recomendado

## CFDI

- Descargado.
- Procesado.
- Vigente.
- Cancelado.
- Sustituido.
- Con inconsistencias.
- Revisado.
- Ignorado.
- Relacionado.
- Conciliado.

## Periodo fiscal

- Borrador.
- Abierto.
- Información pendiente.
- En proceso.
- En revisión.
- Autorizado.
- Presentado.
- Pagado.
- Cerrado.
- Reabierto.
- Vencido.

## Tarea

- Pendiente.
- En proceso.
- Bloqueada.
- Esperando cliente.
- En revisión.
- Completada.
- Cancelada.
- Vencida.

## Documento

- Pendiente.
- Cargado.
- Validado.
- Rechazado.
- Vencido.
- Sustituido.

---

# 16. Auditoría técnica sugerida

Codex debe revisar la aplicación en las siguientes capas.

## 16.1 Arquitectura

- Estructura modular.
- Separación de dominios.
- Servicios.
- Repositorios.
- Jobs.
- Eventos.
- Notificaciones.
- Policies.
- Scopes multi-tenant.
- Dependencias externas.
- Procesos asíncronos.

## 16.2 Base de datos

Revisar:

- Relaciones.
- Índices.
- Llaves foráneas.
- Restricciones únicas.
- Soft deletes.
- Estados.
- Timestamps.
- Tablas pivote.
- Campos monetarios.
- Precisión decimal.
- Fechas fiscales.
- Almacenamiento de XML.
- Snapshots.
- Historial.
- Auditoría.

## 16.3 Seguridad

Revisar:

- Credenciales.
- Secrets.
- Logs.
- Policies.
- Middleware.
- Tenant isolation.
- Descarga de archivos.
- Exposición de rutas.
- Mass assignment.
- Validaciones.
- Autorización.
- CSRF.
- XSS.
- Inyección.
- Rate limiting.

## 16.4 Integraciones

Revisar:

- SAT.
- Descarga masiva.
- CIEC.
- e.firma.
- CFDI.
- Constancia.
- Opinión.
- Servicios externos.
- Manejo de caídas.
- Reintentos.
- Timeouts.
- Idempotencia.
- Logs.
- Alertas.
- Colas.

## 16.5 Frontend

Revisar:

- Navegación.
- Filtros.
- Tablas.
- Paginación.
- Exportaciones.
- Estados vacíos.
- Errores.
- Cargas.
- Permisos visuales.
- Responsividad.
- Accesibilidad.
- Consistencia.

## 16.6 Calidad

Revisar:

- Pruebas unitarias.
- Pruebas de integración.
- Pruebas de permisos.
- Pruebas multi-tenant.
- Pruebas de cálculos.
- Pruebas de importación.
- Pruebas de concurrencia.
- Pruebas de jobs.
- Pruebas de cierres.

---

# 17. Preguntas que Codex debe responder durante la auditoría

1. ¿Qué módulos existen realmente?
2. ¿Qué módulos están incompletos?
3. ¿Qué funcionalidades están implementadas únicamente en frontend?
4. ¿Qué funcionalidades están implementadas únicamente en backend?
5. ¿Qué tablas no se utilizan?
6. ¿Qué rutas no tienen permisos?
7. ¿Qué procesos no tienen bitácora?
8. ¿Qué jobs no son idempotentes?
9. ¿Qué integraciones fallan silenciosamente?
10. ¿Qué módulos no respetan tenant?
11. ¿Qué cálculos fiscales ya existen?
12. ¿Qué datos se obtienen del XML?
13. ¿Se procesan complementos de pago?
14. ¿Se procesan nóminas?
15. ¿Se procesan retenciones?
16. ¿Se procesan cancelaciones?
17. ¿Existe relación entre CFDI y pagos?
18. ¿Existe relación entre CFDI y movimientos bancarios?
19. ¿Existe cierre mensual?
20. ¿Existe snapshot al cerrar?
21. ¿Existen tareas?
22. ¿Existen responsables por contribuyente?
23. ¿Existen alertas configurables?
24. ¿Existe portal del cliente?
25. ¿Qué falta para llegar al centro fiscal mensual?

---

# 18. Entregable esperado de la auditoría

Codex debe generar un reporte con esta estructura:

## Resumen ejecutivo

- Estado general.
- Riesgos.
- Fortalezas.
- Deuda técnica.
- Recomendación principal.

## Inventario de módulos

| Módulo | Estado | Backend | Frontend | Base de datos | Permisos | Pruebas |
|---|---|---|---|---|---|---|

## Hallazgos

| ID | Módulo | Severidad | Hallazgo | Evidencia | Recomendación |
|---|---|---|---|---|---|

## Brechas funcionales

| Capacidad objetivo | Existe | Parcial | No existe | Comentario |
|---|---|---|---|---|

## Riesgos técnicos

- Seguridad.
- Datos.
- Multi-tenant.
- Integraciones.
- Rendimiento.
- Escalabilidad.
- Trazabilidad.

## Roadmap recomendado

- Fase 1.
- Fase 2.
- Fase 3.
- Dependencias.
- Riesgos.
- Estimación relativa.

---

# 19. Roadmap funcional propuesto

## Fase 1: consolidación del núcleo existente

- Auditoría de contribuyentes.
- Auditoría de descarga masiva.
- Auditoría de XML.
- Historial de constancias.
- Historial de opiniones.
- Seguridad de credenciales.
- Dashboard accionable.
- Roles y permisos.
- Bitácora.
- Alertas básicas.
- Responsable por contribuyente.

## Fase 2: centro fiscal mensual

- Periodos fiscales.
- Resumen de ingresos.
- Resumen de egresos.
- IVA trasladado.
- IVA acreditable.
- IVA cobrado.
- IVA pagado.
- Retenciones.
- PUE y PPD.
- Complementos.
- Cancelaciones.
- Inconsistencias.
- Papeles de trabajo.
- Cierre mensual.
- Snapshot.

## Fase 3: operación del despacho

- Tareas.
- Calendario fiscal.
- Flujos de revisión.
- Autorizaciones.
- Portal del cliente.
- Carga documental.
- Declaraciones.
- Líneas de captura.
- Pagos.
- Acuses.

## Fase 4: conciliación bancaria

- Importación bancaria.
- Motor de coincidencias.
- Reglas automáticas.
- Pagos parciales.
- Conciliación manual.
- Alertas.
- Reportes.

## Fase 5: módulos avanzados

- Buzón Tributario.
- Devoluciones.
- SIPARE.
- IMSS.
- Emisión vs SUA.
- INFONAVIT.
- Impuesto sobre nómina.
- Integraciones contables.

---

# 20. Prioridad recomendada

La siguiente funcionalidad prioritaria debe ser:

## Centro fiscal mensual

Motivos:

- Aprovecha los XML que el sistema ya descarga.
- Genera valor inmediato.
- Permite construir papeles de trabajo.
- Permite calcular IVA preliminar.
- Permite detectar inconsistencias.
- Prepara el camino para conciliación bancaria.
- Convierte la plataforma en una herramienta operativa.
- Reduce trabajo manual del contador.

### Primera versión del centro fiscal mensual

Debe incluir:

- Selector de contribuyente.
- Selector de periodo.
- Ingresos facturados.
- Ingresos cobrados.
- Egresos facturados.
- Egresos pagados.
- IVA trasladado.
- IVA acreditable.
- IVA cobrado.
- IVA pagado.
- Retenciones.
- CFDI cancelados.
- PPD sin complemento.
- Alertas.
- Estado del periodo.
- Exportación.

---

# 21. Principios de desarrollo

1. No duplicar información derivable del XML sin justificación.
2. Mantener XML original.
3. Mantener trazabilidad.
4. Evitar cálculos sin desglose.
5. Separar cálculo preliminar de declaración oficial.
6. No cerrar periodos sin snapshot.
7. No modificar periodos cerrados sin reapertura.
8. Toda automatización debe poder auditarse.
9. Toda acción crítica debe quedar en bitácora.
10. Toda integración externa debe manejar reintentos.
11. Toda operación multi-tenant debe validar pertenencia.
12. Todo KPI debe ser accionable.
13. Toda alerta debe poder resolverse.
14. Toda cifra debe poder explicarse.
15. Todo documento debe conservar versión e historial.

---

# 22. Alcance y límites

El sistema puede:

- Concentrar información.
- Analizar CFDI.
- Generar cálculos preliminares.
- Detectar inconsistencias.
- Preparar papeles de trabajo.
- Controlar obligaciones.
- Registrar declaraciones.
- Registrar pagos.
- Administrar evidencias.
- Apoyar al contador.

El sistema no debe asumir automáticamente que:

- Un cálculo preliminar equivale a una declaración oficial.
- Un CFDI PUE siempre fue pagado.
- Todo gasto es deducible.
- Todo IVA es acreditable.
- Toda consulta externa estará disponible.
- Toda diferencia es un error.
- Toda regla fiscal aplica igual a todos los regímenes.

Las reglas deben ser configurables, trazables y revisables.

---

# 23. Instrucciones para Codex

Al trabajar con este proyecto:

1. Leer este archivo antes de proponer cambios.
2. No asumir que una pantalla implica que la funcionalidad está completa.
3. Revisar backend, frontend, base de datos, jobs y permisos.
4. Confirmar multi-tenant en cada consulta.
5. Identificar reglas fiscales codificadas.
6. No modificar cálculos fiscales sin documentar impacto.
7. Proponer cambios por fases.
8. Evitar reescrituras innecesarias.
9. Priorizar trazabilidad y seguridad.
10. Documentar hallazgos con archivos, clases, rutas y tablas.
11. Diferenciar:
   - implementado;
   - parcial;
   - simulado;
   - pendiente;
   - obsoleto.
12. No considerar terminado un módulo sin:
   - permisos;
   - validaciones;
   - errores;
   - bitácora;
   - pruebas;
   - estados;
   - navegación;
   - exportación, cuando aplique.

---

# 24. Definición de éxito

El sistema habrá alcanzado la meta cuando un despacho pueda operar el ciclo mensual completo de un contribuyente dentro de la plataforma:

```text
Alta del contribuyente
→ integración del expediente
→ descarga de CFDI
→ carga bancaria
→ conciliación
→ detección de inconsistencias
→ generación de papeles
→ cálculo preliminar
→ revisión
→ autorización del cliente
→ declaración
→ pago
→ cierre
→ auditoría histórica
```

La propuesta de valor final debe ser:

> Una plataforma para controlar el cumplimiento, analizar información fiscal, preparar cierres mensuales y administrar toda la operación de un despacho contable desde un solo lugar.
