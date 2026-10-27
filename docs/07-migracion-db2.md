# 07 - ESTRATEGIA DE MIGRACIÓN, CONVIVENCIA Y RECONCILIACIÓN DB2 → POSTGRESQL

## 1. Contexto y Objetivos

La transición desde el sistema legacy SIGPOA alojado en **IBM i (DB2 for i)** hacia el nuevo sistema en **Laravel + PostgreSQL** requiere una estrategia de migración no destructiva, desacoplada y formalmente reconciliada.

### Principios Mandatorios:
1. **Acceso DB2 de Solo Lectura (READ-ONLY)**: Ninguna rutina de migración ni consulta escribe directamente en tablas de IBM i.
2. **Desacoplamiento Semántico**: No se exponen nombres físicos legacy (`PLAARC`, `CERAR2`, `SOLAR1`, `LINAR1`) al dominio ni a las vistas de la aplicación.
3. **No Migrar sin Reconciliación**: Todo lote migrado debe someterse a verificación de conteos, sumatorias financieras, comprobación de integridad SHA-256 y clasificación estricta de relaciones.

---

## 2. Mapeo de Tablas Físicas DB2 a Modelos PostgreSQL

| Tabla DB2 | Nombre Funcional Legacy | Modelo Destino PostgreSQL | DTO de Extracción | Clasificación Relación |
|---|---|---|---|---|
| **CONARC** / **HISAR7** | Control de Proceso POA | `poa_processes` / `planning_periods` | `LegacyPoaControlDto` | **B** (Clave Lógica) |
| **PLAARC** / **PLAAR1** | Actividades Anuales y Mensuales | `poa_activities` / `poa_activity_months` | `LegacyPoaActivityDto` | **B** (DDS planum) |
| **TECARC** / **TECAR1** | Techos Presupuestarios | `budget_ceilings` / `budget_ceiling_units` | `LegacyBudgetCeilingDto` | **B** (DDS tecsec) |
| **CERAR2** / **LINARC** | Certificaciones POA | `poa_certifications` / `poa_certification_details` | `LegacyPoaCertificationDto` | **C** (BPM cernu2) |
| **CERA01** / **LINAR6** | Certificaciones Presupuestarias | `budget_certifications` / `budget_certification_lines` | `LegacyBudgetCertificationDto` | **B** (DDS cernu2) |
| **SOLAR1** / **LINAR2** | Solicitudes de Modificación | `poa_modification_requests` / `poa_modification_lines` | `LegacyModificationDto` | **C** (BPM solnu3) |
| **LINAR1** | Devengados y CURs (eSIGEF) | `budget_accruals` | `LegacyExecutionDto` | **C** (BPM linco1) |

---

## 3. Clasificación de Relaciones (Sección 2 del Prompt Maestro)

Para garantizar que la base de datos PostgreSQL mantenga integridad referencial pura sin crear llaves foráneas artificiales sobre datos ambiguos, se aplica la siguiente taxonomía:

1. **Tipo A - Confirmada Físicamente**:
   - Tablas con claves foráneas explícitas definidas a nivel relacional.
   - *Ejemplo*: `budget_certification_lines.budget_certification_id` → `budget_certifications.id`.

2. **Tipo B - Confirmada por Clave / Acceso Lógico DDS**:
   - Tablas estructuradas en IBM i cuyas relaciones se gobiernan por archivos lógicos y campos de clave compuesta.
   - *Ejemplo*: `PLAARC.planum` con `PLAAR1.planum`.

3. **Tipo C - Confirmada Funcionalmente por BPM / Programas**:
   - Relaciones que el sistema legacy valida a nivel del código de programa (RPG/COBOL/PHP legacy) y flujos operacionales.
   - *Ejemplo*: `CERAR2.cernu2` con `LINARC.linoac` (vinculación a actividad POA).

4. **Tipo D - Inferida y Pendiente de Validación**:
   - Datos huérfanos o sin trazabilidad explícita (e.g. un CUR importado cuyo código de actividad no existía en el POA inicial).
   - *Tratamiento*: **Nunca se convierten en FK dura automáticamente**. Se aíslan en el flujo de reconciliación para su resolución asistida.

---

## 4. Herramientas de Extracción y Reconciliación

### A. Repositorios de Integración (`app/Integrations/Ibmi/`)
- `IbmiPoaRepository`
- `IbmiCertificationRepository`
- `IbmiModificationRepository`
- `IbmiExecutionRepository`

### B. Servicios (`app/Services/Migration/`)
- `MigrationService`: Ejecuta la importación transaccional asistida, asociando usuarios responsables, períodos fiscales e historial de auditoría.
- `ReconciliationService`: Compara conteos, montos acumulados y emite un Hash SHA-256 por cada ejecución.

### C. Comandos Artisan
```bash
# Simular migración sin escribir en base de datos
php artisan sigpoa:migrate-legacy --year=2026 --dry-run

# Ejecutar migración formal
php artisan sigpoa:migrate-legacy --year=2026

# Auditar y generar reporte de reconciliación
php artisan sigpoa:reconcile-legacy --year=2026
```

---

## 5. Panel de Control de Convivencia

Accesible en la ruta `/migration/reconciliation` bajo el permiso `admin.parameters`, permitiendo a los directores y auditores verificar visualmente el estado de coincidencia cuantitativa y financiera entre ambos motores de base de datos.
