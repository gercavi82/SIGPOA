# Fase 0 — Diagnóstico del nuevo SIGPOA

**Institución:** Dirección General de Aviación Civil (DGAC)  
**Sistema:** Sistema Integrado de Gestión del Plan Operativo Anual (SIGPOA)  
**Fecha del diagnóstico:** 2026-08-13  
**Última revisión:** 2026-08-13 (segunda inspección del repositorio)  
**Alcance:** Fase 0 exclusivamente; no incluye código funcional, migraciones ni cambios en sistemas externos.

## 1. Resumen ejecutivo

El repositorio inspeccionado en `C:\proyectos\SIGPOA` contiene ahora una instalación base ejecutable de **Laravel 12.66.0 sobre PHP 8.2.12**, con Pest 3.8, Vite 7 y Tailwind CSS 4. La aplicación permanece en estado de esqueleto: solo expone la página de bienvenida, usa SQLite como conexión predeterminada y conserva migraciones, seeder, README y pruebas de ejemplo de Laravel. No contiene aún módulos funcionales SIGPOA, autenticación operativa, RBAC, workflows, auditoría de negocio, modelo PostgreSQL, adaptadores IBM i/eSIGEF ni documentación fuente legacy.

La única fuente disponible para el análisis **funcional y legacy** es el **Prompt Maestro de Implementación del Nuevo SIGPOA**, suministrado como archivo adjunto. El repositorio aporta evidencia técnica sobre el esqueleto Laravel, pero no sobre reglas de negocio. El Prompt Maestro define alcance, principios y correspondencias conceptuales con archivos DB2, aunque no prueba estructuras físicas, claves, cardinalidades, formatos ni reglas detalladas.

Se recomienda construir un **monolito modular en Laravel**, con PostgreSQL como base transaccional principal, una capa de aplicación basada en casos de uso, workflows centralizados, RBAC, auditoría inmutable, documentos versionados y adaptadores desacoplados para IBM i, eSIGEF, firma y analítica. La integración IBM i debe comenzar en modo de solo lectura.

No se detectaron contradicciones críticas entre el esqueleto Laravel y el Prompt Maestro. Sí hay desviaciones de configuración esperables en un proyecto recién creado —SQLite, zona horaria UTC, locale inglés, datos ficticios del seeder y README genérico— que deberán resolverse en el plan previo de la Fase 1. Persiste un bloqueo documental crítico: antes de definir migraciones de negocio o convertir relaciones legacy en restricciones PostgreSQL deben obtenerse y validar los artefactos enumerados en la sección 12.

### Clasificación de certeza utilizada

| Marca | Significado |
|---|---|
| **REQUERIDO** | Declarado explícitamente en el Prompt Maestro. |
| **VERIFICADO** | Confirmado mediante inspección directa del repositorio. |
| **PROPUESTO** | Decisión arquitectónica recomendada, aún no implementada. |
| **PENDIENTE DE VALIDACIÓN** | No respaldado todavía por documentación fuente, metadatos DB2, BPM aprobado o código legacy. |

## 2. Método, alcance y evidencia

### 2.1 Actividades realizadas

1. Lectura completa del Prompt Maestro adjunto.
2. Dos inspecciones recursivas del repositorio, incluidos elementos ocultos del nivel raíz.
3. Inventario de archivos excluyendo únicamente dependencias y metadatos internos de Git.
4. Revisión de versiones, configuración, rutas, migraciones, modelo, seeder, pruebas y README.
5. Ejecución de pruebas, consulta del estado de migraciones y auditoría de dependencias PHP/JavaScript.
6. Contraste de los entregables solicitados contra la evidencia efectivamente disponible.

### 2.2 Resultado de la inspección

| Elemento | Resultado | Estado |
|---|---|---|
| Archivos relevantes inventariados | 73, sin contar dependencias | **VERIFICADO** |
| Aplicación Laravel | Laravel 12.66.0 / PHP 8.2.12; esqueleto ejecutable | **VERIFICADO** |
| Documentación local | Este diagnóstico y README genérico de Laravel | **VERIFICADO** |
| BPM/UML/DER | No disponibles | **VERIFICADO** |
| Persistencia actual | SQLite; migraciones estándar de usuarios, caché y jobs ejecutadas | **VERIFICADO** |
| Esquema PostgreSQL de negocio | No existe | **VERIFICADO** |
| Configuración IBM i/eSIGEF/BI | No existe | **VERIFICADO** |
| Código legacy o extractos DDS | No disponibles | **VERIFICADO** |
| Prompt Maestro | Única fuente funcional y arquitectónica | **VERIFICADO** |
| Pruebas automatizadas | 2 pruebas de ejemplo, 2 assertions, todas pasan | **VERIFICADO** |
| Auditoría de dependencias | Sin advisories conocidos en Composer y npm al 2026-08-13 | **VERIFICADO** |

### 2.3 Limitaciones

- No se tuvo acceso a IBM i, catálogos DB2, DDS, programas RPG/COBOL/CL, pantallas 5250 ni consultas SQL existentes.
- No se dispuso de BPM aprobado, UML, DER legacy, manuales funcionales, matrices de roles, catálogos, formularios o archivos de ejemplo.
- No se pudo validar la existencia o estructura de vistas, jobs Airflow, reportes SSRS/Superset ni contratos de servicios externos.
- No se ejecutaron conexiones ni cambios sobre IBM i, eSIGEF, PostgreSQL u otros sistemas.

## 3. A. Arquitectura encontrada

### 3.1 Arquitectura del repositorio nuevo

Existe el esqueleto convencional y simplificado de Laravel 12. **VERIFICADO**:

```text
Route GET /
   → closure en routes/web.php
   → resources/views/welcome.blade.php

Laravel
   ├─ app/Models/User.php
   ├─ migraciones estándar: users/sessions, cache y jobs
   ├─ SQLite local como conexión predeterminada
   ├─ Pest con dos pruebas de ejemplo
   ├─ Vite + Tailwind CSS 4
   └─ Laravel Boost para asistencia de desarrollo
```

Estado técnico observado:

| Aspecto | Hallazgo |
|---|---|
| Dominio SIGPOA | No implementado |
| Controladores de negocio | No existen; solo controlador base vacío |
| Autenticación | Modelo/migración base presentes; no hay rutas ni UI de login |
| Autorización/RBAC | No implementada |
| Base principal | SQLite; no cumple aún el objetivo PostgreSQL |
| Colas | Tablas y configuración estándar presentes; worker/estrategia institucional no validados |
| Frontend | Página de bienvenida de Laravel; no existe layout institucional |
| Configuración regional | `UTC` y `en`; debe validarse/cambiarse para Ecuador y español en Fase 1 |
| README | Plantilla genérica; no satisface el contenido requerido para SIGPOA |
| Seeder | Crea `Test User`; contradice la regla de no sembrar datos ficticios para producción si se conserva sin protección por entorno |
| Pruebas | Solo smoke test de `/` y prueba trivial; no cubren requisitos SIGPOA |

La aplicación base es adecuada como punto de partida técnico, pero no constituye todavía el “núcleo” de la Fase 1.

### 3.2 Ecosistema legacy declarado

El Prompt Maestro describe, sin aportar artefactos verificables, el siguiente ecosistema:

```text
Usuarios
   ├─ Pantallas 5250 / programas legacy
   └─ SIGPOA Web
          │
          ├─ IBM i / DB2
          │    ├─ DGACDATPRO (datos)
          │    └─ DGACPRGPRO (programas)
          ├─ eSIGEF (procesos y archivos/manual)
          ├─ SSRS
          └─ Flujo BI: V_PRO_SIGPOA_EJE → Python/Airflow → PostgreSQL → Superset
```

La existencia, configuración, sentido del flujo, frecuencias, responsables, credenciales, SLAs y contratos de estos componentes está **PENDIENTE DE VALIDACIÓN**.

## 4. B. Mapa funcional

| Macroproceso | Capacidades requeridas | Evidencia | Estado de detalle |
|---|---|---|---|
| Administración | Usuarios, RBAC, organización, catálogos, parámetros, períodos, documentos, auditoría | Prompt Maestro | Reglas y catálogos **PENDIENTES DE VALIDACIÓN** |
| Control POA | Apertura/cierre por año y fase, responsable y estado | Prompt Maestro; referencia `CONARC`/`HISAR7` | Estructura y reglas **PENDIENTES DE VALIDACIÓN** |
| POA de necesidades | Ingreso, corrección, revisión, devolución y aprobación multinivel | Prompt Maestro | BPM formal **PENDIENTE DE VALIDACIÓN** |
| Planificación | Actividades anuales, programación mensual, metas y montos | Prompt Maestro; `PLAARC`/`PLAAR1` | Campos/cardinalidad **PENDIENTES DE VALIDACIÓN** |
| Techos | Techo institucional, distribución, ajuste y aprobación por dirección | Prompt Maestro; `TECARC`/`TECAR1` | Fórmulas/metodología **PENDIENTES DE VALIDACIÓN** |
| Proforma y POA inicial | Priorización, ajuste, aprobación y registro eSIGEF | Prompt Maestro | Reglas de versionamiento y cierre **PENDIENTES** |
| Certificación POA | Solicitud, detalle, documentos, revisión, firma y actualización | Prompt Maestro; `CERAR2`/`LINARC`/`ACTAR1` | Claves y documentos **PENDIENTES** |
| Certificación presupuestaria | Registro, validación, trazabilidad y vínculo POA | Prompt Maestro; `CERA01`/`LINAR6` | Relación física **PENDIENTE** |
| Modificaciones | Sin/con afectación, asignación, reprogramación y posibles tipos adicionales | Prompt Maestro; familia `SOLAR`/`LINAR`/`MODAR` | Variantes y reglas financieras **PENDIENTES** |
| Ejecución | Lotes CSV, inconsistencias, CUR/devengados, actualización y cierre mensual | Prompt Maestro; `LINAR1`/`EJEAR1` | Layout CSV y matching **PENDIENTES** |
| Seguimiento y BI | Indicadores, vistas analíticas y convivencia del pipeline actual | Prompt Maestro | Semántica de indicadores **PENDIENTE** |
| Reportes | POA, ejecución, saldos, certificados y modificaciones con exportación | Prompt Maestro | Diseños/filtros/volúmenes **PENDIENTES** |

## 5. C. Actores

| Actor/rol funcional | Responsabilidades declaradas | Pendientes |
|---|---|---|
| Gestión Interna requirente | Crear/corregir POA y solicitudes; cargar documentos | Unidades, sustituciones, segregación y alcance territorial |
| Dirección de Área | Disponer ingreso, revisar, aprobar/devolver y firmar cuando aplique | Jerarquía organizacional y delegaciones |
| Analista DPGE | Revisar solicitudes/documentos, preaprobar, devolver o negar | Límites, asignación de bandejas y suplencias |
| Director DPGE | Aprobar/devolver, firmar, autorizar viabilidad y actualización POA | Delegación y firma conjunta |
| Financiero / Presupuesto | Validar presupuesto, operar eSIGEF, CUR y reformas | Perfiles separados y doble control |
| Administrador | Gestionar usuarios, roles, permisos y configuración | Restricciones sobre datos transaccionales/auditoría |
| Sistemas externos | IBM i, eSIGEF, firma, correo/notificación, SSRS, Airflow, Superset | Dueños, contratos, disponibilidad y seguridad |

Todos los actores provienen del Prompt Maestro. Identidades institucionales, organigrama, matrices RACI, delegaciones, suplencias y segregación de funciones están **PENDIENTES DE VALIDACIÓN**.

## 6. D. Módulos

Se propone delimitar los módulos del monolito por capacidad de negocio:

1. **Administration:** identidad local/institucional, organización, RBAC, catálogos y configuración.
2. **Planning:** proceso POA, actividades, programación, necesidades, proforma y POA inicial.
3. **BudgetCeilings:** techos institucionales y por unidad, metodología y ajustes.
4. **Certifications:** certificaciones POA, detalles, actualizaciones, documentos y firma.
5. **BudgetCertifications:** certificaciones presupuestarias y sus líneas.
6. **Modifications:** solicitud común, líneas, distribución mensual, análisis y resultado final.
7. **Execution:** importaciones, conciliación, CUR/devengados y cierres mensuales.
8. **Reports:** consultas, exportaciones, dashboard y acceso analítico.
9. **Audit:** transiciones, versiones, observaciones y eventos de seguridad.
10. **Integrations:** puertos/adaptadores IBM i, eSIGEF, firma, archivos, notificaciones y BI.

Los límites son **PROPUESTOS** y deben confirmarse mediante talleres de dominio; no implican microservicios ni bases separadas.

## 7. E. Inventario preliminar de tablas/objetos DB2

Ninguno de los siguientes objetos fue inspeccionado directamente. Su existencia, tipo de objeto, biblioteca, columnas, claves, índices, journals, volumen y uso actual están **PENDIENTES DE VALIDACIÓN**.

| Área | Objeto legacy declarado | Mapeo conceptual preliminar | Confianza relacional |
|---|---|---|---|
| Control | `CONARC` | Proceso POA | D — inferida desde el prompt |
| Control/histórico | `HISAR7` | Histórico del proceso | D |
| POA anual | `PLAARC` | Actividad POA | D |
| POA mensual | `PLAAR1` | Programación mensual | D |
| Necesidades anual | `PLAARCPOAN` | Actividad POA en fase necesidades | D |
| Necesidades mensual | `PLAAR1POAN` | Programación mensual de necesidades | D |
| Techos | `TECARC` | Techo institucional | D |
| Techos | `TECAR1` | Techo por dirección/unidad | D |
| Certificaciones POA | `CERAR2` | Certificación POA | D |
| Certificaciones POA | `LINARC` | Detalle de certificación | D |
| Certificaciones POA | `ACTAR1` | Actualización de certificación | D |
| Certificación presupuestaria | `CERA01` | Certificación presupuestaria | D |
| Certificación presupuestaria | `LINAR6` | Línea de certificación presupuestaria | D |
| Modificaciones | `SOLAR1` | Solicitud de modificación | D |
| Modificaciones | `SOLAR5` | Seguimiento/consulta DPGE | D |
| Modificaciones | `LINAR2` | Línea anual de modificación | D |
| Modificaciones | `LINAR3` | Distribución mensual de modificación | D |
| Modificaciones | `MODAR3` | Resultado final de modificación | D |
| Modificaciones | `MODAR4` | Resultado final mensual | D |
| Ejecución | `LINAR1` | Devengado/CUR | D |
| Ejecución temporal | `EJEAR1` | Staging legacy; no será entidad de dominio | D |
| Observaciones | `OBSAR1`, `OBSARC` | Observación | D |
| Históricos | `HISAR2`, `HISAR3`, `HISAR5`, `HISAR6` | Auditoría/transiciones/versiones | D |
| BI | `V_PRO_SIGPOA_EJE` | Vista/fuente analítica de ejecución | D |

### 7.1 Regla obligatoria para relaciones legacy

Toda relación descubierta debe registrarse en una matriz con una de estas categorías:

- **A. Confirmada físicamente:** constraint o referencia comprobada en catálogo DB2.
- **B. Confirmada por clave/acceso lógico:** clave o DDS lógico comprobado.
- **C. Confirmada funcionalmente por BPM:** comportamiento respaldado por BPM/código y validado por negocio.
- **D. Inferida y pendiente de validación:** hipótesis no apta para crear FK.

En este diagnóstico todas las correspondencias permanecen en **D**, porque no se dispone de evidencia primaria. Ninguna debe traducirse automáticamente a una FK PostgreSQL.

### 7.2 Inventario técnico requerido

Para cada objeto se deberá capturar: biblioteca/esquema, tipo (`PF`, `LF`, tabla, vista), DDS o DDL, columnas y tipos, claves e índices, constraints, miembros, journals/triggers, conteos, fechas mínima/máxima, nulos, duplicados, programas consumidores, consultas y clasificación A–D de cada relación.

## 8. F. Integraciones

| Integración declarada | Uso esperado | Estrategia propuesta | Estado |
|---|---|---|---|
| IBM i / DB2 | Consulta y migración progresiva de datos legacy | Conexión independiente, mínimo privilegio, inicialmente solo lectura; repositorios anti-corrupción | Contrato, driver, red y esquema **PENDIENTES** |
| eSIGEF | Certificaciones, reformas, CUR e importaciones | `EsigefGatewayInterface`; adaptador manual auditable si no hay API | Disponibilidad de API **PENDIENTE**; no inventar endpoints |
| Firma electrónica | Firma de documentos/aprobaciones | `SignatureProviderInterface`; proveedor externo y doble de pruebas | Proveedor, estándares y validez **PENDIENTES** |
| Archivos CSV | Importación de CUR/devengados | Staging con lote, hash, validación, idempotencia y trazabilidad | Layout/codificación/volumen **PENDIENTES** |
| Correo/in-app | Avisos basados en eventos | Colas, listeners y plantillas | SMTP/canales/SLA **PENDIENTES** |
| SSRS | Reportes legacy durante convivencia | Inventariar dependencias antes de retiro | Catálogo y usuarios **PENDIENTES** |
| Airflow/Python | Sincronización analítica | Mantener hasta análisis de impacto; reconciliar fuentes | DAG y frecuencia **PENDIENTES** |
| PostgreSQL analítico/Superset | Analítica y dashboards actuales | Vistas/modelos de lectura desacoplados del dominio | Esquema, métricas y ownership **PENDIENTES** |

## 9. G. Workflows

### 9.1 Procesos identificados

- Ciclo general POA: necesidades → ajuste de techos → proforma → POA inicial → certificación → modificación → ejecución → cierre.
- POA de necesidades: creación/solicitud → ingreso/corrección → aprobación de área o devolución → revisión DPGE → aprobación o corrección.
- Techos/proforma: registrar techo → históricos → calcular/distribuir → ajustar → aprobar → cambiar fase → priorizar POA → aprobar → registrar en eSIGEF/CUR.
- Certificación POA: elaboración/documentos → revisión de área/firma → revisión DPGE/preaprobación → aprobación/firma de Dirección DPGE.
- Modificación sin afectación: solicitud/documentos → área → verificación financiera → DPGE → viabilidad → aprobación → actualización POA.
- Modificación con afectación: flujo común + validación presupuestaria → autorización → reforma eSIGEF/CUR → actualización POA.
- Asignación de recursos: flujo común + saldo/actividad destino → viabilidad → reforma/CUR → actualización.
- Ejecución: certificados → CSV/CUR → lote → validar/conciliar → procesar → actualizar → cierre → seguimiento.

### 9.2 Diseño transversal propuesto

Las entidades críticas deben implementar estado mediante enums de código estable y una definición de workflow versionada. La única entrada para cambiar estado será un servicio de aplicación transaccional equivalente a:

```text
transition(entity, targetState, actor, context)
  → autoriza permiso/rol y alcance organizacional
  → bloquea o valida versión concurrente
  → valida estado origen/destino
  → valida campos, documentos, firma y presupuesto
  → aplica efectos de dominio
  → registra transición, versión y auditoría
  → publica evento después del commit
```

Estados iniciales sugeridos para certificación (`BORRADOR`, `ENVIADA_AREA`, `DEVUELTA_AREA`, `APROBADA_AREA`, `EN_REVISION_DPGE`, `DEVUELTA_DPGE`, `NEGADA`, `PREAPROBADA`, `APROBADA`, `FINALIZADA`) provienen del prompt, pero sus transiciones exactas, terminalidad, permisos y reglas están **PENDIENTES DE VALIDACIÓN**.

### 9.3 Preguntas obligatorias de workflow

- ¿Quién puede devolver, negar, cancelar, reabrir o subsanar cada expediente?
- ¿Qué estados son terminales y cuáles permiten corrección?
- ¿Qué documentos y firmas son obligatorios por transición y tipo?
- ¿Qué sucede con aprobaciones ante reemplazo/delegación del responsable?
- ¿Cuándo se reserva, libera o consume saldo?
- ¿Cuál es la regla ante concurrencia y doble aprobación?
- ¿Qué efectos externos son síncronos y cuáles reintentables?
- ¿Qué SLA, recordatorios y escalamiento aplican?

Todas las respuestas están **PENDIENTES DE VALIDACIÓN**.

## 10. H. Documentos

### 10.1 Situación encontrada

No se encontraron plantillas, ejemplos, catálogos de tipos documentales, políticas de retención ni archivos habilitantes. **VERIFICADO**.

### 10.2 Modelo propuesto

Un servicio central gestionará metadatos, almacenamiento, checksum, versión, estado y acceso. Los archivos no se sobrescribirán; cada sustitución generará una versión. Los documentos firmados o asociados a transacciones aprobadas no podrán borrarse físicamente.

Además de los campos mínimos del prompt, conviene modelar una entidad de firma separada y reglas de obligatoriedad por tipo de expediente/transición. La selección de almacenamiento (local privado, S3 compatible u otro gestor institucional), antivirus, clasificación, cifrado, retención, tamaño máximo y recuperación está **PENDIENTE DE VALIDACIÓN**.

## 11. I. Riesgos

| Riesgo | Probabilidad/impacto inicial | Mitigación recomendada |
|---|---|---|
| Reglas críticas solo en programas legacy | Alta/Crítico | Analizar código y trazas; talleres con usuarios; pruebas de caracterización |
| Relaciones DB2 implícitas | Alta/Crítico | Catálogo+DDS+programas+BPM; matriz A–D; no crear FK tipo D |
| Calidad/duplicidad de datos legacy | Alta/Alto | Perfilado temprano y reconciliación por conteos, montos, hashes y muestras |
| Desalineación BPM real/documentado | Alta/Alto | Walkthrough por rol y observación de casos reales |
| Semántica financiera ambigua | Media/Crítico | Glosario aprobado; reglas de saldo/codificado/devengado con ejemplos |
| Integración eSIGEF no automatizable | Alta/Alto | Flujo manual asistido, evidencias, doble control e idempotencia |
| Firma electrónica sin contrato técnico | Media/Alto | Spike técnico y proveedor desacoplado antes del módulo 5 |
| Corte prematuro del BI legacy | Media/Alto | Convivencia, linaje, comparación paralela y criterio formal de retiro |
| Autorización basada solo en rol | Media/Alto | RBAC + alcance organizacional + policies + segregación de funciones |
| Exposición de documentos/datos | Media/Crítico | Storage privado, autorización por descarga, cifrado, malware scan y logs |
| Doble aprobación/actualización | Media/Alto | Optimistic locking o bloqueo de fila, idempotency key y transacciones |
| Procesamiento pesado síncrono | Media/Medio | Jobs idempotentes, colas, progreso, reintentos y dead-letter handling |
| Alcance excesivo para un solo lanzamiento | Alta/Alto | Entregas por fase, feature flags, pilotos y criterios de salida |
| Baseline Laravel aún genérico | Alta/Alto | Aprobar arquitectura, decisiones y fuentes antes del código funcional; sustituir defaults de forma controlada |
| SQLite usado por defecto en lugar de PostgreSQL | Alta/Alto | Definir entorno PostgreSQL y comprobar extensiones, migraciones, backups y CI antes del modelo de negocio |
| Seeder con usuario ficticio | Media/Alto | Retirarlo o limitarlo explícitamente a desarrollo/pruebas antes de despliegues |
| Configuración regional `UTC`/`en` | Media/Medio | Validar y configurar zona `America/Guayaquil`, locale español y formatos institucionales |
| Artefactos generados/cachés en árbol de trabajo | Media/Medio | Confirmar política Git y evitar versionar `.env`, base SQLite, builds y vistas compiladas cuando no correspondan |

## 12. J. Información faltante

### 12.1 Bloqueante antes de diseñar migraciones definitivas

1. Acceso de solo lectura o exportación de catálogos DB2 de las bibliotecas relevantes.
2. DDS/DDL de archivos físicos, lógicos, vistas e índices prioritarios.
3. Código o inventario de programas que crean, actualizan y consultan esos objetos.
4. BPM oficiales versionados y aprobados para cada flujo.
5. DER/UML existentes y diccionario de datos/campos/códigos.
6. Conteos, calidad, volumen, crecimiento y muestras anonimizadas por tabla.
7. Reglas de conciliación financiera y ejemplos cerrados por ejercicio.

### 12.2 Bloqueante antes de implementar módulos específicos

- Matriz usuario–rol–permiso–unidad y reglas de delegación/segregación.
- Catálogo de fases, estados, prioridades, fuentes, tipos de gasto y documentos.
- Calendario fiscal, cierre/reapertura, redondeos, monedas y zona horaria institucional.
- Formularios, informes, resoluciones, plantillas y firmas requeridas.
- Layouts CSV eSIGEF reales, codificación, delimitador y ejemplos válidos/inválidos.
- Contrato o confirmación de inexistencia de API eSIGEF.
- Proveedor y política institucional de firma electrónica.
- Inventario de reportes SSRS, dashboards Superset, vistas, métricas y consumidores.
- DAG `dag_sigpoa_syncro.py`, configuración no secreta, periodicidad y manejo de fallos.
- Requisitos no funcionales: disponibilidad, RTO/RPO, concurrencia, rendimiento, retención y observabilidad.
- Plataforma objetivo: versiones aprobadas de PHP/Laravel/PostgreSQL, SO/contenedores, CI/CD, proxy, correo, storage, colas y secrets manager.
- Política de datos personales, clasificación de información y normativa de archivo/auditoría aplicable.

Todo lo anterior está **PENDIENTE DE VALIDACIÓN**.

## 13. K. Contradicciones y ambigüedades

### 13.1 Contradicciones críticas confirmadas

No se puede confirmar ninguna contradicción entre documentación legacy, DB2 y BPM porque esas fuentes no fueron aportadas. El código disponible es únicamente un esqueleto Laravel y no contiene reglas de negocio que puedan contrastarse. **VERIFICADO**.

Sí se observan diferencias entre el objetivo y la configuración inicial, que no se consideran contradicciones de negocio: conexión SQLite en vez de PostgreSQL, zona horaria UTC, locale inglés, README genérico y un usuario ficticio en el seeder.

### 13.2 Ambigüedades que deben resolverse

| Tema | Ambigüedad |
|---|---|
| POA anual/mensual | Se declara relación 1:N, pero no debe asumirse que siempre hay 12 meses. Falta regla por ejercicio/actividad. |
| Necesidades vs POA | Existen nombres legacy separados, mientras se recomienda no duplicar actividades. Debe definirse versionado/fases y estrategia de migración. |
| Certificación presupuestaria ↔ POA | El vínculo se requiere funcionalmente, pero se prohíbe asumir relación física exacta. |
| Roles y estados | Se pide no hardcodearlos y a la vez usar enums para estados. Se propone enum para identidad semántica y configuración/tablas para metadata/permisos/transiciones; requiere aprobación. |
| Estados genéricos | El prompt pide “tipos de estado” en seeders, pero cada agregado tiene ciclo distinto. Debe decidirse catálogo global vs estados por workflow. |
| `SOLAR5` | Se denomina consulta/seguimiento; falta confirmar si es archivo físico, lógico o vista y si requiere persistencia nueva. |
| Nómina/OTROS | Su inclusión depende expresamente de alcance/regla aún no aportada. |
| Firma | No se define si firma el documento, la aprobación, ambos, ni formato/estándar. |
| eSIGEF | No se sabe si existe API; cualquier automatización permanece prohibida hasta comprobarlo. |
| “Ejecutado” | Dashboard lista planificado, codificado, devengado y ejecutado, pero no define fórmula ni diferencia exacta. |
| Soft delete/auditoría | Debe precisarse si ciertos expedientes pueden anularse y cómo se conserva su efecto contable. |

Estas ambigüedades no impiden proponer arquitectura, pero sí impiden cerrar diseño detallado y migraciones de los módulos afectados.

## 14. L. Propuesta de arquitectura Laravel

### 14.1 Estilo

**PROPUESTO:** monolito modular, desplegable como una unidad y organizado por capacidades de negocio. La separación Presentación → Aplicación → Dominio → Persistencia/Integraciones se aplicará pragmáticamente; no se crearán carpetas vacías ni abstracciones sin un caso de uso real.

```text
HTTP / UI / API
      │
      ▼
Form Requests + Policies + Middleware
      │
      ▼
Actions / Application Services / DTOs
      │
      ├────────► Workflow Service
      ├────────► Document Service
      ├────────► Audit Service
      └────────► Domain Services
                      │
                      ▼
             Repositories / Eloquent
                      │
        ┌─────────────┴─────────────┐
        ▼                           ▼
 PostgreSQL transaccional     Outbox / Jobs / Events
                                      │
                          IBM i / eSIGEF / firma / BI
```

### 14.2 Estructura orientativa

```text
app/
  Modules/
    Administration/
    Planning/
    BudgetCeilings/
    Certifications/
    BudgetCertifications/
    Modifications/
    Execution/
    Reports/
    Audit/
  Integrations/
    Ibmi/
    Esigef/
    Signature/
    Reporting/
    Files/
    Notifications/
  Support/
```

Dentro de cada módulo solo se crearán carpetas necesarias, por ejemplo `Actions`, `DTOs`, `Enums`, `Models`, `Policies`, `Repositories`, `Services`, `Events` y `Http`.

### 14.3 Decisiones arquitectónicas iniciales propuestas

- PostgreSQL es la fuente de verdad del sistema nuevo; DB2 no será la base principal.
- IBM i operará inicialmente con credenciales de solo lectura y adaptadores que traduzcan nombres legacy a DTOs de negocio.
- Los controladores serán adaptadores delgados; no contendrán reglas financieras, SQL complejo ni transiciones directas.
- Workflows críticos se ejecutarán en transacciones, con validación de versión/bloqueo y registro de transición.
- RBAC se combinará con alcance por unidad organizacional; ocultar acciones en UI no sustituirá policies backend.
- Documentos se almacenarán fuera de rutas públicas, con checksum, versión y descarga autorizada.
- Integraciones y tareas pesadas usarán colas, idempotencia, reintentos y trazabilidad; los eventos externos deben emitirse después del commit.
- Auditoría, transiciones y versiones serán append-only a nivel de aplicación; la política de inmutabilidad DB debe definirse.
- Los reportes complejos usarán modelos de lectura/vistas y Jobs; el dominio no dependerá de Superset.
- Se recomienda un patrón outbox para notificaciones e integraciones críticas. **PENDIENTE DE VALIDACIÓN** según infraestructura disponible.

### 14.4 Decisiones que requieren ADR

1. ADR-001: monolito modular y límites de módulos.
2. ADR-002: estrategia de workflow y versionado de definiciones.
3. ADR-003: RBAC y alcance organizacional.
4. ADR-004: auditoría, versionado e inmutabilidad.
5. ADR-005: documentos y firma electrónica.
6. ADR-006: convivencia IBM i y capa anticorrupción.
7. ADR-007: integración manual/API eSIGEF.
8. ADR-008: estrategia BI, vistas de lectura y retiro legacy.
9. ADR-009: concurrencia e idempotencia.
10. ADR-010: migración, reconciliación y corte.

## 15. M. DER PostgreSQL propuesto

Este es un **modelo conceptual preliminar**, no un DDL. Nombres, atributos, nulabilidad, constraints y cardinalidades deben confirmarse antes de crear migraciones. Las FK entre entidades nuevas expresan el diseño objetivo; no certifican relaciones físicas del DB2 legacy.

### 15.1 Administración y organización

```text
users ──< user_roles >── roles ──< role_permissions >── permissions
  │
  └──< user_organization_assignments >── organizational_units
organizational_units ──< organizational_units (parent_id)
planning_periods
catalogs ──< catalog_items
system_parameters
document_types
```

### 15.2 Planificación y techos

```text
planning_periods ──< poa_processes
poa_processes ──< poa_activities ──< poa_activity_months
organizational_units ──< poa_activities
poa_processes ──< budget_ceilings ──< budget_ceiling_units
organizational_units ──< budget_ceiling_units
```

Debe evaluarse si las versiones de POA se representan con `entity_versions`, una revisión explícita de actividad o snapshots de propuesta. No se recomienda duplicar automáticamente toda actividad por fase.

### 15.3 Certificaciones

```text
poa_certifications ──< poa_certification_details
poa_certifications ──< poa_certification_updates
poa_certification_details >── poa_activities

budget_certifications ──< budget_certification_lines
budget_certification_lines ──? poa_certifications / poa_activities
```

La relación marcada `──?` está **PENDIENTE DE VALIDACIÓN** y no debe convertirse en FK hasta demostrarla.

### 15.4 Modificaciones

```text
poa_modification_requests ──< poa_modification_lines
poa_modification_lines ──< poa_modification_months
poa_modification_requests ──0..1 poa_modification_results
poa_modification_results ──< poa_modification_result_months
poa_modification_lines >──? poa_activities (origen/destino)
```

La forma exacta de origen/destino, líneas de débito/crédito y creación de actividad está **PENDIENTE DE VALIDACIÓN**.

### 15.5 Ejecución e importación

```text
import_batches ──< import_batch_rows
import_batch_rows ──0..1 budget_accruals
budget_accruals >──? budget_certification_lines
budget_accruals >──? poa_activities
execution_month_closures
```

El lote debe tener hash único dentro del tipo/fuente/período apropiado; la clave exacta de idempotencia está **PENDIENTE DE VALIDACIÓN**.

### 15.6 Transversales

```text
documents (polimórfico) ──< signatures
observations (polimórfico)
workflow_transitions (polimórfico)
entity_versions (polimórfico)
audit_logs (actor + entidad polimórfica + correlation_id)
notifications
integration_runs ──< integration_run_errors
```

La estrategia polimórfica facilita múltiples agregados, pero reduce FK directas hacia cada entidad. Debe evaluarse frente a tablas de vínculo explícitas para documentos y auditoría. **PENDIENTE DE VALIDACIÓN**.

### 15.7 Restricciones a considerar cuando el dominio se valide

- Unicidad de proceso por período/fase según regla aprobada.
- Unicidad del número de expediente dentro de su ámbito/año.
- Montos no negativos donde corresponda; líneas de ajuste pueden requerir signo y regla distinta.
- Mes dentro de 1–12 sin exigir automáticamente doce filas.
- Checks de totales y consistencia anual/mensual cuando la regla sea inequívoca.
- Índices por año, unidad, estado, responsable, número y fechas de bandeja.
- Version column para optimistic locking en agregados críticos.
- Soft delete solo donde sea funcionalmente válido; auditoría y documentos firmados sin eliminación física.

## 16. N. Plan de implementación por fases

Cada fase debe iniciar con objetivo, alcance, archivos, migraciones, riesgos, pruebas y rollback; debe terminar con evidencia equivalente y no avanzar ante fallos críticos.

| Fase | Objetivo/salida | Dependencias y criterio de entrada |
|---|---|---|
| 0. Diagnóstico | Este documento; inventarios, riesgos, brechas y arquitectura preliminar | Prompt Maestro; completar fuentes faltantes antes de aprobar diseño detallado |
| 1. Núcleo | Laravel base, PostgreSQL, autenticación, RBAC, organización mínima, auditoría, layout, logging y manejo de errores | Versiones/plataforma, IdP, matriz RBAC y NFR aprobados |
| 2. Control y catálogos | Proceso POA, períodos, organización/programática, catálogos y parámetros | BPM de fases, catálogos y estructura organizacional validados |
| 3. POA necesidades | Actividades, meses, versiones, observaciones, revisión/devolución/aprobación | Mapeo `PLAARC*`, BPM y reglas de metas/montos validados |
| 4. Techos/proforma | Techos, distribución, priorización, ajuste, proforma y POA inicial | Fórmulas, históricos, cierre y saldos aprobados |
| 5. Certificaciones | Solicitud, detalle, documentos, workflow, firma y actualizaciones | BPM, tipos documentales, proveedor de firma y mapeo legacy |
| 6. Modificaciones | Núcleo común y variantes: sin afectación, con afectación, asignación y reprogramación | Reglas financieras, eSIGEF, líneas origen/destino; Nómina solo si se valida |
| 7. Ejecución | Certificación presupuestaria, importación, conciliación, CUR/devengados y cierre | CSV reales, matching, idempotencia y controles de cierre aprobados |
| 8. Reportes/BI | Dashboard, reportes/exportaciones, vistas analíticas y convivencia BI | Diccionario de métricas, volúmenes, catálogo SSRS/Superset |
| 9. Migración/convivencia | ETL controlada, reconciliación, ejecución paralela, corte y rollback | Inventario DB2 completo, calidad aceptada, criterios de reconciliación y cutover |

### 16.1 Estrategia de pruebas transversal

- Unitarias para reglas puras, cálculos y validadores.
- Feature para HTTP, persistencia y casos de uso.
- Autorización por permiso, rol, unidad y segregación.
- Workflow para todas las transiciones válidas/inválidas, devoluciones y estados terminales.
- Concurrencia para doble aprobación, actualización obsoleta e idempotencia.
- Integración mediante dobles/entornos controlados para IBM i, eSIGEF, firma y storage.
- Importación para archivo inválido, duplicado, parcial, reintento y conciliación.
- Regresión con casos legacy anonimizados y resultados financieros conocidos.
- Seguridad para archivos, XSS/CSRF, sesiones, rate limits y filtración de secretos.
- Rendimiento para bandejas, reportes, importaciones y cierres con volúmenes representativos.

### 16.2 Estrategia de rollback por fase

- Cambios de aplicación desplegados de forma versionada y reversible.
- Migraciones con `down()` probado, excepto donde revertir destruya evidencia; esos casos requieren migración compensatoria y respaldo.
- Feature flags para habilitar módulos/usuarios piloto.
- Integraciones externas idempotentes y con registro de compensación; nunca suponer rollback remoto.
- Migración con backups, checkpoint, reconciliación y plan de retorno al legacy durante convivencia.
- No retirar pipeline BI ni escritura legacy hasta cumplir criterios formales de corte.

## 17. Criterios de salida de la Fase 0

| Criterio | Estado |
|---|---|
| Repositorio inspeccionado | Cumplido; segunda inspección realizada tras incorporarse Laravel |
| Arquitectura encontrada documentada | Cumplido: esqueleto Laravel 12 y ecosistema legacy declarado, claramente separados |
| Mapa funcional, actores y módulos | Cumplido a nivel preliminar |
| Inventario DB2 | Parcial: nombres declarados; metadatos pendientes |
| Integraciones y workflows inventariados | Cumplido a nivel preliminar |
| Documentos inventariados | Bloqueado: no se aportaron artefactos |
| Riesgos, faltantes y ambigüedades | Cumplido |
| Arquitectura y DER propuestos | Cumplido a nivel conceptual |
| Plan por fases | Cumplido |
| Validación con fuentes primarias y dueños de negocio | **PENDIENTE DE VALIDACIÓN** |

## 18. Recomendación de decisión

Se recomienda **mantener la Fase 0 como diagnóstico preliminar y no considerar iniciada ni terminada la Fase 1 por la sola instalación de Laravel**. El siguiente paso debe combinar una ronda formal de descubrimiento con la preparación del plan “ANTES” de Fase 1. Deben incorporarse fuentes primarias, resolver ambigüedades y aprobar al menos los ADR de arquitectura base, RBAC, workflow, auditoría e integración IBM i.

Orden sugerido inmediato:

1. Entregar DDS/DDL, catálogo DB2 y programas consumidores de los objetos prioritarios.
2. Entregar BPM, DER/UML, manuales, formularios y casos reales anonimizados.
3. Realizar talleres por macroproceso y aprobar glosario, estados, permisos y reglas financieras.
4. Completar matriz de relaciones A–D y perfilado de datos.
5. Confirmar plataforma, NFR e integraciones.
6. Actualizar este diagnóstico y emitir ADR iniciales.
7. Solo entonces presentar el plan “ANTES” de Fase 1 para aprobación/ejecución.

## 19. Verificación técnica de la segunda revisión

| Comprobación | Resultado |
|---|---|
| `php artisan about --only=environment` | Laravel 12.66.0, PHP 8.2.12, entorno local, debug habilitado |
| `php artisan route:list --except-vendor` | Una ruta: `GET /` |
| `php artisan migrate:status` | Tres migraciones estándar ejecutadas, batch 1 |
| `php artisan config:show database.default` | `sqlite` |
| `php artisan config:show app.timezone` | `UTC` |
| `php artisan config:show app.locale` | `en` |
| `php artisan test --compact` | 2 pruebas aprobadas, 2 assertions |
| `composer audit --no-interaction` | Sin advisories conocidos |
| `npm audit --omit=dev` | 0 vulnerabilidades reportadas |

Estas comprobaciones demuestran que el esqueleto funciona localmente; no prueban autenticación, RBAC, PostgreSQL, workflows ni funciones SIGPOA.

---

**Control de no invención:** los nombres de objetos legacy, flujos, roles y requisitos funcionales aquí descritos provienen del Prompt Maestro. Las decisiones nuevas se identifican como propuestas y los detalles sin evidencia se marcan como **PENDIENTE DE VALIDACIÓN**.
