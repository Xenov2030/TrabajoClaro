# Operación y soporte de la beta migrada

Solo el operador autorizado del proyecto Supabase consulta feedback desde SQL Editor. No dar acceso a testers, no publicar claves privilegiadas ni cuentas administrativas compartidas.

```sql
select id, screen, release, description, contact, created_at
from public.tc_feedback order by created_at desc limit 50;

select id, name, email, message, created_at
from public.tc_pilot_requests order by created_at desc limit 50;
```

Los resultados pueden contener datos personales: no copiarlos a Git, capturas públicas ni chat. La bandeja de estados pendiente/en revisión/resuelto aún no existe. No hay suplantación de cuentas.

Para investigar: fecha/hora, versión, categoría de pantalla, pasos y mensaje visible; sin contraseña, token, payload completo ni datos reales del cliente. Revisar logs de Vercel (build/funciones) y Supabase (Auth/PostgREST/Postgres). La lógica operativa corre en RPC, no dentro de un servidor Laravel. El logger seguro PHP queda histórico y no protege automáticamente los logs del proveedor nuevo. No habilitar captura de cuerpos/analítica que copie enlaces privados. Tokens nuevos en fragmento `/p/#...` reducen filtración por rutas, pero no sustituyen revisión de logs ni una auditoría.

`/up` verifica que Next.js responde, no la DB. Consultar estado de Supabase, último job cron, backup y cuotas por separado. Sin colas de aplicación que requieran un worker. [Cron, respaldos, restauración y rollback](DESPLIEGUE_SUPABASE_VERCEL.md#4-limpieza-respaldo-y-recuperación).

Retención: demo 24 h en acceso/RLS; borrado físico al correr `tc_prune()`. Feedback 90 días más siguiente limpieza; cuentas registradas sin poda automática. Las filas anónimas de Auth no se eliminan con esta poda. Los backups anteriores no se modifican al borrar datos de la base activa. No existe solicitud/baja de cuenta completa todavía; no ejecutar borrados manuales sin verificar alcance, documentos, movimientos y política acordada. Antes de limpieza/cierre de cuentas registradas, aviso mínimo de 7 días y exportación; avisos no automatizados. Revisión legal pendiente.
