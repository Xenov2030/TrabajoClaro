# Hechos para contenido · arquitectura migrada

No se generan publicaciones todavía. Autor Nicolas Aguirres, [portfolio](https://nicolasaguirrescode.com/). La hipótesis: pequeños negocios que ya tienen encargos necesitan claridad de alcance, próximas acciones y saldos; no hay entrevistas o problema confirmado por usuarios acreditados. No afirmar ahorro, ventas, adopción o validación de mercado.

Funciones demostrables con proyecto configurado: clientes, presupuesto/publicación, aceptación en sesión independiente, conversión, cobro manual/saldo, feedback y exportación. Diferencias verificables frente a gestión manual: snapshot publicado no cambia al editar cliente/borrador; total calculado en servidor; conversión/reintento de cobro controlados; saldo e historial de anulación. No cuantificar beneficios económicos ni decir que consigue clientes.

Decisión técnica comprobable: migración autorizada de Laravel/MySQL a Next.js/Supabase para el despliegue conectado en Vercel, usando Jornadapp como referencia de arquitectura. Google autentica; PostgreSQL guarda datos, no Google Drive. RLS/funciones transaccionales mantienen controles en servidor; BigInt/numeric evitan float monetario. Es una reimplementación, no una configuración de salida de Vite.

Desafíos comprobables: deployment anterior buscaba `dist` aunque Vite emitía `public/build`; la migración configura Next.js/`.next`. En pruebas reales, refrescar desmontaba la pantalla y perdía el enlace recién publicado; se corrigió el refresco de fondo. Next.js normaliza la barra final y la ruta pública inicialmente no reconocía `/p`; se corrigió la ruta. El arnés local precisó headers CORS y claims JWT estándar. Esos problemas y sus correcciones no acreditan producción operativa.

Aprendizajes: separar validación de interfaz, reglas de DB y servicios externos; una sesión preemitida permite probar RPC pero no prueba Google/Auth; compilar no demuestra deployment ni instalación PWA.

Codex implementó código/migración/pruebas/documentación en esta sesión. La autorización de migrar está en el pedido del titular; no hay evidencia de revisión humana exhaustiva, entrevistas ni resultados comerciales. No atribuir decisiones o revisión a Nicolas sin confirmación. [Estado vigente](ESTADO_BETA.md) y [pruebas/límites](VERIFICACION_MIGRACION.md) son la fuente actual; [material Laravel histórico](legacy-laravel/CONTENIDO.md) no se usa para afirmar cobertura de la migración.

## Evidencia pública incorporada el 2026-11-01

Se verificó https://trabajo-claro-orpin.vercel.app/ con datos ficticios en Chromium móvil/escritorio: demo, cliente, presupuesto, aceptación independiente, trabajo, cobro parcial/saldo, PDF, feedback y piloto. Dos demos no pudieron leer ni mutar registros ajenos; recarga y logout comprobados. Son pruebas técnicas, no usuarios comerciales ni validación de mercado. Registro/login por correo, comprobación independiente de Google, instalación física PWA y operación del proveedor siguen pendientes. La implementación web de instalación se describe en [PWA](PWA.md); no usar pruebas de eventos emulados como demostración de una instalación real. [Alcance exacto y evidencia](VERIFICACION_MIGRACION.md#verificación-pública--2026-11-01).
