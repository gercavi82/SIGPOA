# SIGPOA — DGAC

Nueva plataforma del Sistema Integrado de Gestión del Plan Operativo Anual de la Dirección General de Aviación Civil del Ecuador.

## Estado

Fase 6: dominio unificado de modificaciones POA, líneas/mensualización, workflow financiero/DPGE y control manual de reforma eSIGEF/CUR.

## Requisitos

- PHP 8.2 o posterior con `pdo_pgsql`
- PostgreSQL
- Composer 2
- Node.js y npm

## Instalación local

```bash
composer install
copy .env.example .env
php artisan key:generate
npm install
npm run build
php artisan migrate --seed
php artisan sigpoa:create-administrator administrador@institucion.gob.ec
php artisan serve
```

Configure las credenciales PostgreSQL únicamente en `.env`. No registre ese archivo en Git. La conexión IBM i se reserva para un adaptador de solo lectura pendiente de validación; no se incluyen credenciales ni endpoints ficticios.

## Operación

```bash
php artisan queue:work
php artisan schedule:work
php artisan storage:link
```

El enlace público de storage no debe utilizarse para documentos habilitantes privados. La estrategia documental se implementará en una fase posterior.

## Desarrollo y pruebas

```bash
composer run dev
php artisan test --compact
vendor/bin/pint --format agent
npm run build
composer audit
npm audit
```

Las pruebas automatizadas usan SQLite para aislamiento. Producción y ambientes integrados deben usar PostgreSQL.

## Despliegue

Producción requiere HTTPS, `APP_DEBUG=false`, cookies seguras, secretos inyectados por el entorno, worker de colas supervisado, scheduler, backups PostgreSQL, almacenamiento privado y ejecución de `php artisan optimize`. El procedimiento institucional, CI/CD, RTO/RPO y rollback permanecen pendientes de validación.

Consulte [el diagnóstico](docs/00-diagnostico.md), [la arquitectura](docs/01-arquitectura.md), [roles y permisos](docs/05-roles-permisos.md) y [pruebas](docs/08-pruebas.md).
