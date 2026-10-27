# Estrategia de pruebas

La suite usa Pest 3 y una base SQLite aislada. En Fase 1 cubre redirección de invitados, login/logout, cuenta inactiva, validación de credenciales, acceso sin permiso, acceso autorizado y contenido básico de auditoría.

Comandos:

```bash
php artisan test --compact
vendor/bin/pint --format agent
npm run build
composer audit
npm audit
```

Antes de producción se requieren pruebas adicionales con PostgreSQL real, sesiones concurrentes, rate limiting, cookies HTTPS, recuperación de contraseña, alcance organizacional, backups/restauración y carga.
