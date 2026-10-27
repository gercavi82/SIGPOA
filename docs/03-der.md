# DER — Fase 2

```text
planning_periods 1 ── N poa_processes 1 ── N control_lines
users 1 ── N poa_processes (responsable / creador)

catalogs 1 ── N catalog_items
catalog_items 1 ── N catalog_items (jerarquía opcional)

organizational_units 1 ── N programmatic_structures
programmatic_structures 1 ── N programmatic_structures (jerarquía opcional)

system_parameters (independiente)
audit_logs → entidades auditables mediante relación polimórfica

poa_processes 1 ── N poa_activities 1 ── N poa_activity_months
organizational_units 1 ── N poa_activities
poa_activities 1 ── N observations / entity_versions / workflow_transitions

planning_periods 1 ── N budget_ceilings 1 ── N budget_ceiling_units
organizational_units 1 ── N budget_ceiling_units
poa_activities 1 ── N poa_activity_phase_allocations N ── 1 poa_processes

poa_certifications 1 ── N poa_certification_details N ── 1 poa_activities
poa_certifications 1 ── N poa_certification_updates
poa_certifications 1 ── N documents 1 ── N signatures

poa_modification_requests 1 ── N poa_modification_lines 1 ── N poa_modification_months
poa_modification_requests 1 ── 0..1 poa_modification_results 1 ── N poa_modification_result_months
```

Las relaciones descritas corresponden al diseño PostgreSQL nuevo. No demuestran relaciones físicas en DB2 y no alteran la clasificación A–D del diagnóstico legacy.
