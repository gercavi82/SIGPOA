# Workflows — Fase 2

## Proceso POA

```text
BORRADOR ──► ABIERTO ──► CERRADO
    └──────► CANCELADO
```

Las transiciones se ejecutan mediante una Action transaccional. Se valida permiso, estado origen, destino permitido y versión del registro; se bloquea la fila y se genera auditoría con motivo obligatorio. `CERRADO` y `CANCELADO` son terminales en esta versión.

Esta máquina representa el control mínimo requerido para Fase 2. Reaperturas, solapamiento entre fases, fechas obligatorias y actores específicos están **PENDIENTES DE VALIDACIÓN** y no se inventaron.

## Actividad POA de necesidades

```text
BORRADOR / DEVUELTA_AREA / DEVUELTA_DPGE → ENVIADA_AREA
ENVIADA_AREA → DEVUELTA_AREA | APROBADA_AREA
APROBADA_AREA → DEVUELTA_DPGE | APROBADA
```

Cada transición valida versión, registra actor y comentario, y se ejecuta dentro de una transacción. `APROBADA` es terminal. La asignación definitiva de cada transición a Dirección de Área o DPGE permanece pendiente de la matriz institucional; actualmente se exige el permiso granular `poa.review`.

## Techos presupuestarios

```text
BORRADOR → EN_ANALISIS → APROBADO
                    └─→ BORRADOR
```

La distribución nunca puede exceder el techo. Para aprobar, debe distribuirse el total institucional. Una actividad solo puede priorizarse en una fase Proforma/POA Inicial abierta y su unidad no puede superar el techo aprobado. Las fórmulas históricas de cálculo permanecen **PENDIENTES DE VALIDACIÓN**.

## Certificación POA

```text
BORRADOR → ENVIADA_AREA → APROBADA_AREA → EN_REVISION_DPGE → PREAPROBADA → APROBADA → FINALIZADA
               └→ DEVUELTA_AREA                    ├→ DEVUELTA_DPGE
                                                    └→ NEGADA
```

Las aprobaciones de Área y DPGE registran firma mediante un contrato desacoplado. Toda transición exige documento habilitante, comentario, permiso y versión vigente.

## Modificaciones POA

```text
BORRADOR → ENVIADA_AREA → VERIFICACION_FINANCIERA → REVISION_DPGE → VIABLE → AUTORIZADA
                                                                         ├→ APROBADA
                                                                         └→ REFORMA_PENDIENTE → APROBADA
```

Las devoluciones regresan a `DEVUELTA`; DPGE puede negar. Con afectación/asignación se exige registro manual auditable de reforma y CUR antes de aprobar.
