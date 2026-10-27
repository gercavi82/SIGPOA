# Arquitectura — Fase 1

SIGPOA adopta un monolito modular Laravel 12. PostgreSQL es la base objetivo; SQLite se limita a pruebas automatizadas y al entorno local actual mientras se habilita `pdo_pgsql`.

El flujo base es HTTP → middleware/Form Request → controller → Action/Service → Eloquent/PostgreSQL. Las transiciones de negocio futuras deberán entrar por servicios transaccionales y no por controladores. IBM i, eSIGEF, firma y BI se conectarán mediante contratos/adaptadores, sin exponer nombres legacy al dominio.

## Núcleo implementado

- Autenticación de sesión con regeneración, logout seguro y rate limiting.
- RBAC persistente con roles, permisos y middleware backend.
- Asociación de usuarios a unidades organizacionales jerárquicas.
- Auditoría con actor, entidad, valores, IP, agente y correlation ID.
- Layout Blade/Tailwind responsive y navegación autenticada.
- Configuración objetivo PostgreSQL y regionalización para Ecuador.

## Pendientes de validación

- Proveedor de identidad institucional y recuperación de contraseña.
- Matriz definitiva de permisos, segregación, delegaciones y unidades.
- Driver/conectividad PHP PostgreSQL en el host actual.
- Inmutabilidad de auditoría a nivel de base, retención y exportación.
- Infraestructura de colas, correo, storage, observabilidad y secrets manager.
