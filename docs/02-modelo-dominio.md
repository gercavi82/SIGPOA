# Modelo de dominio — Fase 2

El núcleo de control se organiza alrededor de `PlanningPeriod` y `PoaProcess`. Cada período identifica un ejercicio y cada proceso representa una sola fase del ciclo POA dentro de ese ejercicio. La combinación período/fase es única.

`PoaProcess` inicia en `BORRADOR` y registra responsable, creador, apertura, cierre y versión de concurrencia. Sus fases se centralizan en `PoaPhase` y sus estados en `ProcessStatus`.

Los catálogos se representan mediante `Catalog` y `CatalogItem`, con elementos jerárquicos y metadata opcional. `ProgrammaticStructure` permite jerarquías por año, tipo y código. `ControlLine` pertenece a un proceso POA. `SystemParameter` conserva valores tipados, pero ningún secreto debe almacenarse allí.

Los códigos, tipos, jerarquías y reglas institucionales definitivas permanecen **PENDIENTES DE VALIDACIÓN**.

## Actividades POA de necesidades

`PoaActivity` pertenece a un proceso abierto en fase `POA_NECESIDADES`, una unidad organizacional y un responsable. `PoaActivityMonth` conserva únicamente los meses programados; no se exige crear doce filas. Cada creación o corrección genera un snapshot en `EntityVersion`. Las devoluciones generan `Observation` y todas las acciones de workflow se registran en `WorkflowTransition` y auditoría.

## Techos, proforma y POA inicial

`BudgetCeiling` representa el techo institucional por período y fuente; `BudgetCeilingUnit` distribuye el monto entre unidades. `PoaActivityPhaseAllocation` enlaza una actividad existente con un proceso de Proforma o POA Inicial y conserva prioridad, monto/meta ajustados y justificación. Este enlace evita duplicar actividades entre fases.

## Certificaciones POA

`PoaCertification` agrupa detalles asociados a actividades, documentos habilitantes y actualizaciones. `Document` es polimórfico, privado, versionado y conserva checksum. `Signature` registra proveedor, firmante, hashes y transacción; la integración depende de `SignatureProviderInterface`. El proveedor mock se usa exclusivamente en pruebas y el proveedor institucional permanece pendiente de configuración.

## Modificaciones POA

`PoaModificationRequest` unifica solicitudes sin/con afectación, asignación de recursos y reprogramación. Contiene líneas débito/crédito, distribución mensual y un resultado final opcional. Los tipos con impacto marcan `requires_esigef` y solo pueden aprobarse cuando reforma y CUR han sido registrados manualmente.
