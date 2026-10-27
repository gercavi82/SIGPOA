# Roles y permisos — Fase 1

Los roles iniciales provienen del Prompt Maestro: Gestión Interna requirente, Dirección de Área, Analista DPGE, Director DPGE, Financiero/Presupuesto y Administrador.

Los permisos iniciales se agrupan en `poa`, `certification`, `modification`, `execution`, `reports` y `admin`. El seeder asigna todos los permisos únicamente al rol Administrador; las asignaciones de los demás roles están **PENDIENTES DE VALIDACIÓN** mediante una matriz institucional.

La autorización backend se resuelve por relación usuario → rol activo → permiso. La asociación con unidades organizacionales permitirá aplicar alcance de datos en módulos posteriores. La UI deberá ocultar acciones no autorizadas, pero toda operación seguirá validándose en backend.

No se crea ningún usuario por seeder. El alta inicial del administrador debe realizarse mediante un procedimiento operativo seguro aún pendiente de definición.
