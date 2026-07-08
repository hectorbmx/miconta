# Roadmap de accion SaaS para despachos contables

## 1. Objetivo

Este documento convierte la auditoria funcional de `AUDITORIA_SAAS_CONTADORES.md` en un plan de ejecucion por fases.

La meta es evolucionar la plataforma desde una herramienta de descarga y consulta fiscal hacia un sistema operativo mensual para despachos contables, con:

- Descarga masiva SAT automatica o manual configurable.
- Perfil del contribuyente con indicadores fiscales reales.
- Centro fiscal mensual por periodo.
- Alertas accionables.
- Trazabilidad desde cada cifra hasta CFDI, XML, complemento, evidencia y accion.
- Seguridad multi-tenant robusta.

## 2. Principios de ejecucion

1. No construir indicadores ni UI fiscal sobre datos fragiles.
2. Corregir riesgos multi-tenant antes de automatizar procesos.
3. Todo KPI debe navegar al detalle que explica la cifra.
4. Toda descarga SAT debe ser trazable, reintentable e idempotente.
5. Separar calculo preliminar de declaracion oficial.
6. Separar IVA facturado de IVA efectivamente cobrado o pagado.
7. No cerrar periodos sin snapshot.
8. No modificar periodos cerrados sin reapertura y bitacora.
9. Toda accion critica debe registrarse.
10. Todo entregable debe dejar pruebas o evidencia de verificacion.

## 3. Checkpoint actual

Fecha de referencia: 2026-07-07.

Estado detectado:

- Existe SaaS base con tenants, usuarios, roles, planes, Stripe y acceso por despacho.
- Existe gestion de contribuyentes.
- Existe descarga masiva SAT funcional, pero requiere intervencion manual para verificar y continuar.
- Existe almacenamiento de CFDI, conceptos y algunos datos fiscales.
- Existe servicio parcial de resumen mensual.
- Existe CSF y opinion de cumplimiento de forma parcial.
- Existe contabilidad/polizas basicas.
- Existe API para n8n/WhatsApp.
- No existe centro fiscal mensual formal con estados, cierre, snapshot y reapertura.
- No existe conciliacion bancaria.
- No existe calendario fiscal operativo.
- No existe motor de tareas.
- No existe bitacora de negocio completa.

Cambios existentes en worktree al iniciar auditoria:

- `AUDITORIA_SAAS_CONTADORES.md` estaba sin trackear.
- `resources/views/components/layouts/client.blade.php` estaba modificado.

## 4. Hallazgos que bloquean crecimiento seguro

### H1. Rutas SAT duplicadas

Archivo: `routes/web.php`.

`download-requests` se registra dos veces. La segunda declaracion como resource completo expone rutas `edit`, `update` y `destroy` aunque el controlador no implementa comportamiento real para ellas.

Riesgo:

- Superficie de permisos innecesaria.
- Errores 500 o comportamiento inconsistente.
- Dificulta auditar el modulo SAT.

### H2. Job de descarga vacio

Archivo: `app/Jobs/ProcessSatDownloadRequest.php`.

El job existe pero no contiene logica. El flujo actual depende de botones y llamadas sincronas desde controlador/servicio.

Riesgo:

- No hay automatizacion real.
- No hay reintentos robustos.
- El contador debe refrescar manualmente.
- La experiencia no escala para multiples contribuyentes.

### H3. Riesgo multi-tenant por UUID global

Archivo: `app/Services/Sat/SatDescargaMasivaService.php`.

Se usa `SatCfdi::updateOrCreate(['uuid' => $uuid])`.

Riesgo:

- Si dos contribuyentes o tenants descargan el mismo CFDI, se puede sobrescribir `customer_id`, `sat_download_request_id` o `xml_path`.
- Esto es critico para privacidad y consistencia fiscal.

### H4. Complementos de pago no procesados en el flujo activo

Archivo: `app/Services/Sat/SatDescargaMasivaService.php`.

El metodo `processPagos()` existe, pero el flujo activo de `processPackage()` llama principalmente `processConceptos()`.

Riesgo:

- Los indicadores de IVA cobrado/pagado no pueden ser confiables.
- PPD sin complemento puede quedar mal calculado.
- No se puede construir centro fiscal mensual completo.

### H5. Descarga solo de documentos vigentes

Archivo: `app/Services/Sat/SatDescargaMasivaService.php`.

La consulta usa `DocumentStatus::active()`.

Riesgo:

- No se detectan cancelaciones.
- No se cumple el objetivo de auditar CFDI cancelados, sustituidos o modificados.

### H6. Secretos y metadatos sensibles

Archivos:

- `app/Services/Sat/SatDescargaMasivaService.php`.
- `database/migrations/2026_05_20_000215_add_sat_credentials_to_customers_table.php`.
- `app/Models/Customer.php`.

Riesgo:

- Se registra `password_length`.
- `csd_password` debe verificarse para confirmar si esta cifrado o si queda sin cast.
- Falta bitacora de acceso a credenciales.

## 5. Fase 0: saneamiento tecnico SAT y seguridad

### Objetivo

Dejar el nucleo SAT seguro, consistente y auditable antes de automatizar o redisenar UI.

### Alcance

- Eliminar rutas duplicadas de `download-requests`.
- Limitar rutas SAT a las acciones realmente soportadas.
- Corregir persistencia de CFDI para que sea segura por tenant y contribuyente.
- Revisar restricciones unicas e indices para CFDI.
- Asegurar cifrado de todas las credenciales fiscales.
- Remover logs sensibles.
- Activar procesamiento de complementos de pago en el flujo real.
- Corregir limpieza de archivos temporales.
- Agregar pruebas de aislamiento multi-tenant.

### Checkpoint 0.1: rutas SAT limpias

Criterios:

- `php artisan route:list` no muestra rutas SAT no soportadas.
- No existen duplicados de `download-requests`.
- Las rutas destructivas solo existen si hay funcion y permiso explicito.

Evidencia esperada:

- Diff de `routes/web.php`.
- Salida resumida de `php artisan route:list`.

### Checkpoint 0.2: CFDI seguro por tenant/contribuyente

Criterios:

- La persistencia de CFDI no sobrescribe datos entre tenants.
- La unicidad considera al menos `customer_id + uuid`, o una regla equivalente justificada.
- Los archivos XML quedan bajo ruta separada por tenant y customer.

Evidencia esperada:

- Migracion o ajuste de indice.
- Prueba automatizada de no cruce entre tenants.

### Checkpoint 0.3: complementos de pago activos

Criterios:

- Los complementos de pago se procesan desde el flujo activo de descarga.
- `sat_cfdi_pagos` se llena cuando el XML contiene complemento de pago.
- Se evitan duplicados al reprocesar.

Evidencia esperada:

- Prueba con XML de pago.
- Validacion de relacion factura-complemento por UUID.

### Checkpoint 0.4: secretos protegidos

Criterios:

- Todas las contrasenas fiscales relevantes usan casts cifrados o mecanismo equivalente.
- No se registra longitud, contenido ni derivados innecesarios de contrasenas.
- Acceso a credenciales queda preparado para bitacora.

Evidencia esperada:

- Diff de modelo/servicio.
- Busqueda sin resultados de logs sensibles.

## 6. Fase 1: descarga masiva automatica configurable

### Objetivo

Permitir que el contador elija descarga manual o automatica por contribuyente y/o por configuracion del despacho.

### Alcance funcional

Configuracion por contribuyente:

- Modo de descarga: manual o automatica.
- Tipos: emitidas, recibidas o ambas.
- Frecuencia: diaria, semanal o mensual.
- Rango automatico: mes actual, mes anterior o ultimos N dias.
- Hora preferida.
- Dia del mes o dia de semana, cuando aplique.
- Estado activo/inactivo.

Configuracion por despacho:

- Valores por defecto para nuevos contribuyentes.
- Limites operativos para evitar exceso de solicitudes al SAT.
- Ventana horaria sugerida.
- Politica de reintentos.

### Alcance tecnico

- Crear tabla de configuracion de descarga SAT.
- Implementar job real para iniciar solicitudes.
- Implementar job real para verificar solicitudes pendientes.
- Implementar job real para descargar y procesar paquetes listos.
- Implementar scheduler en `app/Console/Kernel.php`.
- Evitar solicitudes duplicadas para mismo contribuyente, periodo, tipo y estado abierto.
- Registrar historial de intentos y errores.

### Checkpoint 1.1: modelo de configuracion

Criterios:

- Existe configuracion persistente por contribuyente.
- Existe fallback desde configuracion del despacho.
- El perfil del contribuyente muestra el modo actual.

Evidencia esperada:

- Migracion.
- Modelo/relacion.
- UI basica de configuracion.

### Checkpoint 1.2: jobs idempotentes

Criterios:

- Un job puede ejecutarse mas de una vez sin duplicar descargas abiertas.
- Los estados avanzan de forma clara.
- Los errores quedan registrados.

Estados sugeridos:

- `pending`.
- `querying`.
- `waiting_sat`.
- `verifying`.
- `downloading`.
- `processing`.
- `completed`.
- `failed`.
- `needs_attention`.

Evidencia esperada:

- Tests de idempotencia.
- Logs de estado controlados.

### Checkpoint 1.3: scheduler operativo

Criterios:

- El scheduler detecta configuraciones activas.
- Crea solicitudes cuando corresponde.
- Verifica solicitudes pendientes sin boton manual.
- Descarga paquetes cuando estan listos.

Evidencia esperada:

- Comando artisan o scheduler probado.
- Registro de una solicitud automatica simulada o real.

### Checkpoint 1.4: UI de estado de descarga

Criterios:

- El contador ve si la descarga esta activa.
- El contador ve ultima descarga emitidas y recibidas.
- El contador ve errores recientes.
- Existe accion manual "Descargar ahora".
- Existe accion "Configurar automatizacion".

Evidencia esperada:

- Vista actualizada del perfil del contribuyente.
- Captura o verificacion visual local.

## 7. Fase 2: perfil del contribuyente operativo

### Objetivo

Transformar la pantalla del contribuyente en un expediente operativo con indicadores rapidos, reales y accionables.

### Estructura sugerida

Secciones:

- Encabezado fiscal.
- Estado SAT.
- Resumen fiscal mensual.
- Alertas.
- Descargas recientes.
- CFDI destacados.
- Acciones rapidas.
- Contabilidad/polizas.
- Documentos fiscales.

### KPIs iniciales

Estado SAT:

- Ultima descarga emitidas.
- Ultima descarga recibidas.
- CFDI nuevos del periodo.
- CFDI cancelados.
- PPD sin complemento.
- Complementos de pago detectados.
- Solicitudes SAT fallidas.

Indicadores fiscales:

- Ingresos facturados.
- Egresos facturados.
- IVA trasladado facturado.
- IVA acreditable facturado.
- IVA efectivamente cobrado.
- IVA efectivamente pagado.
- Retenciones.
- Estimado preliminar.

Estado operativo:

- Periodo actual.
- Alertas criticas.
- Alertas pendientes.
- Estado de cierre.
- Documentos pendientes.

### Checkpoint 2.1: rediseño de layout del perfil

Criterios:

- El primer viewport muestra la identidad fiscal y estado operativo.
- No hay tarjetas decorativas sin accion.
- Los KPIs principales son visibles sin navegar a varias pantallas.

Evidencia esperada:

- Vista `client.clientes.show` actualizada.
- Verificacion visual desktop.

### Checkpoint 2.2: KPIs con navegacion

Criterios:

- Cada KPI enlaza a CFDI/listado filtrado o detalle correspondiente.
- La cifra y el detalle usan la misma consulta base.
- Los estados vacios explican que falta.

Evidencia esperada:

- Links funcionales.
- Validacion manual con datos existentes.

### Checkpoint 2.3: acciones rapidas

Criterios:

- Descargar ahora.
- Configurar descarga automatica.
- Ver CFDI del periodo.
- Ver PPD sin complemento.
- Ver complementos.
- Generar o ver resumen fiscal.

Evidencia esperada:

- Acciones visibles y protegidas por tenant.

## 8. Fase 3: centro fiscal mensual

### Objetivo

Crear un expediente mensual por contribuyente y periodo.

### Entidades sugeridas

`fiscal_periods`:

- `tenant_id`.
- `customer_id`.
- `year`.
- `month`.
- `status`.
- `opened_at`.
- `reviewed_at`.
- `authorized_at`.
- `presented_at`.
- `paid_at`.
- `closed_at`.
- `reopened_at`.
- `closed_by`.
- `snapshot_json`.

`fiscal_period_snapshots`:

- `fiscal_period_id`.
- `generated_by`.
- `totals_json`.
- `alerts_json`.
- `source_counts_json`.
- `created_at`.

`fiscal_period_alerts`:

- `tenant_id`.
- `customer_id`.
- `fiscal_period_id`.
- `severity`.
- `type`.
- `status`.
- `related_type`.
- `related_id`.
- `assigned_to`.
- `resolution`.
- `resolved_at`.

### Estados sugeridos

- `open`.
- `missing_information`.
- `cfdi_downloaded`.
- `in_review`.
- `authorized`.
- `presented`.
- `paid`.
- `closed`.
- `reopened`.
- `overdue`.

### Checkpoint 3.1: modelo de periodo

Criterios:

- Existe un periodo fiscal unico por tenant, customer, year y month.
- El perfil del contribuyente puede abrir el periodo actual.
- El periodo muestra estado y resumen.

Evidencia esperada:

- Migraciones.
- Modelos.
- Ruta y vista basica.

### Checkpoint 3.2: resumen mensual version 1

Criterios:

- Calcula ingresos, egresos, IVA y retenciones.
- Distingue facturado vs efectivamente cobrado/pagado cuando hay complementos.
- Identifica PUE como indicio configurable, no como verdad absoluta.

Evidencia esperada:

- Servicio de resumen ajustado.
- Pruebas de PUE/PPD.

### Checkpoint 3.3: cierre y snapshot

Criterios:

- No se puede cerrar con alertas criticas abiertas sin autorizacion.
- El cierre guarda snapshot de cifras.
- La reapertura exige motivo.

Evidencia esperada:

- Migracion de snapshots.
- Prueba de cierre/reapertura.

## 9. Fase 4: motor de alertas CFDI

### Objetivo

Detectar inconsistencias fiscales y convertirlas en acciones.

### Reglas iniciales

- PPD sin complemento.
- Complemento sin CFDI relacionado.
- CFDI cancelado.
- CFDI moneda extranjera.
- CFDI sin impuestos parseados.
- Pago mayor al saldo.
- Diferencia entre factura y complemento.
- CFDI emitido fuera del periodo.
- CFDI recibido despues del cierre.
- RFC inconsistente con contribuyente.
- XML no procesado completamente.

### Checkpoint 4.1: estructura de alertas

Criterios:

- Las alertas se guardan con severidad, estado y relacion.
- Las alertas no se duplican al recalcular.
- Cada alerta puede abrir su entidad origen.

### Checkpoint 4.2: UI de alertas

Criterios:

- Alertas visibles en perfil y centro mensual.
- Filtros por severidad y estado.
- Acciones: revisar, justificar, ignorar, resolver.

## 10. Fase 5: papeles de trabajo y exportacion

### Objetivo

Generar salidas utiles para el contador sin romper trazabilidad.

### Reportes iniciales

- Relacion de ingresos.
- Relacion de egresos.
- IVA trasladado.
- IVA acreditable.
- IVA cobrado.
- IVA pagado.
- Retenciones.
- PPD sin complemento.
- CFDI cancelados.
- Complementos de pago.
- Resumen preliminar.

### Checkpoint 5.1: exportacion Excel

Criterios:

- Exporta datos filtrados del periodo.
- Incluye UUID y columnas trazables.
- Respeta tenant.

### Checkpoint 5.2: PDF/resumen ejecutivo

Criterios:

- PDF con cifras principales.
- Indica que es calculo preliminar.
- Incluye fecha, usuario y periodo.

## 11. Fase 6: bitacora, permisos y auditoria

### Objetivo

Registrar acciones criticas y cerrar brechas de control.

### Acciones a registrar

- Acceso a credenciales.
- Cambio de credenciales.
- Cambio de configuracion automatica.
- Solicitud SAT creada.
- Verificacion SAT.
- Descarga de paquete.
- Procesamiento de XML.
- Descarga de PDF/archivo.
- Exportacion.
- Cambio manual de estado.
- Justificacion de alerta.
- Cierre de periodo.
- Reapertura de periodo.
- Cambio de permisos.

### Checkpoint 6.1: bitacora base

Criterios:

- Existe tabla o paquete de activity log.
- Registra usuario, tenant, entidad, accion, datos previos y nuevos cuando aplique.

### Checkpoint 6.2: permisos por rol

Criterios:

- Socio/admin despacho.
- Supervisor.
- Contador.
- Auxiliar.
- Auditor.
- Cliente limitado en fase posterior.

## 12. Roadmap resumido por entregables

| Entregable | Fase | Prioridad | Resultado |
|---|---:|---:|---|
| Saneamiento rutas SAT | 0 | Alta | Menos superficie y menos errores |
| Seguridad UUID multi-tenant | 0 | Critica | Evita cruce de CFDI |
| Complementos de pago activos | 0 | Critica | Base para IVA cobrado/pagado |
| Configuracion descarga automatica | 1 | Alta | Control por contribuyente/despacho |
| Jobs y scheduler SAT | 1 | Alta | Descarga sin boton manual |
| UI estado de descarga | 1 | Alta | Transparencia operativa |
| Perfil contribuyente redisenado | 2 | Alta | Indicadores rapidos y accionables |
| Periodos fiscales | 3 | Alta | Centro mensual formal |
| Alertas CFDI | 4 | Media/Alta | Control operativo |
| Cierre con snapshot | 3 | Alta | Historial fiscal confiable |
| Papeles de trabajo | 5 | Media | Salidas contables |
| Bitacora completa | 6 | Alta | Auditoria y cumplimiento |

## 13. Primer bloque recomendado para ejecutar

El primer bloque debe ser:

1. Fase 0 completa.
2. Checkpoints 1.1 y 1.2.
3. UI minima de estado de descarga en perfil del contribuyente.

Motivo:

- La automatizacion depende de idempotencia y seguridad.
- Los KPIs fiscales dependen de complementos y datos correctos.
- El contador necesita ver que el proceso avanza solo antes de confiar en el centro mensual.

## 14. Definicion de listo por fase

Una fase se considera lista solo si cumple:

- Migraciones aplicadas o documentadas.
- Rutas revisadas.
- Tenant isolation validado.
- UI conectada a datos reales.
- Estados vacios y errores visibles.
- Pruebas automatizadas cuando el cambio afecte datos, permisos o calculos.
- Evidencia de verificacion registrada en este documento o en un checkpoint separado.

## 15. Bitacora de avances

### Checkpoint inicial

- Documento creado para guiar la ejecucion por fases.
- Basado en la auditoria inicial y los hallazgos detectados.
- Siguiente accion recomendada: ejecutar Fase 0.

