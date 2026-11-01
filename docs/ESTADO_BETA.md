# Cierre de alcance y estado de la primera beta

Revisión: **2026-10-31, America/Buenos_Aires**. Código del repositorio; sin versión etiquetada ni URL pública acreditados. Autor: Nicolas Aguirres.

**Conclusión:** el recorrido principal funciona contra el backend real en pruebas locales. No está lista para entregarse como servicio público ni como PWA instalada. Hay requisitos posteriores aún pendientes. El último pedido cierra el alcance: esta revisión no incorpora módulos ni oculta esos faltantes.

Se contrastaron el pedido original adjunto, las ampliaciones de diseño/testers/despliegue, documentación, firma, marca, preparación de beta y PWA. Las filas agrupan requisitos relacionados; una pantalla o un manifest no acreditan una función completa.

## Criterios y evidencia

- **Implementado y verificado:** código y comprobación concreta, limitada al entorno indicado.
- **Implementado, pendiente de verificar:** existe la implementación, falta comprobar el aspecto indicado.
- **Parcial:** una parte funciona y se identifica lo que falta.
- **Pendiente:** no hay implementación completa; no se ofrece como disponible.
- **Fuera de la primera beta:** exclusión explícita. No significa requisito completado.

Referencias reproducibles, desde la raíz:

| Código | Evidencia de esta revisión |
| --- | --- |
| F | `docker compose exec -T app php artisan test`: **34 aprobadas, 333 comprobaciones**, SQLite. Archivos [WorkflowTest](../tests/Feature/WorkflowTest.php), [BetaFeedbackTest](../tests/Feature/BetaFeedbackTest.php), [ExampleTest](../tests/Feature/ExampleTest.php). |
| M | `docker compose exec -T app php artisan test --configuration=phpunit.mysql.xml`: **34 aprobadas, 333 comprobaciones**, base MySQL de pruebas separada. |
| B | `npx playwright test`: **8 aprobadas**, Chromium, 360×800 y 1440×1000. [Recorrido](../tests/Browser/journey.spec.js), [registro y feedback](../tests/Browser/beta.spec.js), [marca](../tests/Browser/brand.spec.js). |
| V | Capturas generadas en `test-results/`: landing, acceso/registro, panel, editor, presupuesto público, feedback y muestra `/marca`. Revisión visual y controles automáticos de desbordes, carga de logo y foco de firma; no certificación de accesibilidad. |
| A | Los cuatro PDFs generados (público/interno × móvil/escritorio) contienen una sola firma exacta y enlace al portfolio; marca de impresión presente. Pillow confirmó PNG de 16/32/180/192/512 y ICO real de 16/32/48. Enlaces relativos de README/CHANGELOG/docs sin destinos inexistentes; `git diff --check` sin errores. |
| O | `/up` respondió HTTP 200; `php artisan schedule:list` enumera `demo:prune` horario y `feedback:prune` diario; Pint aprobó 57 archivos. |
| H | Ensayo anterior documentado en [beta 2](beta-2-verificacion.md): producción local, reinicio, dump y restauración. No se repitió despliegue/backup en este cierre. No acredita operación en un proveedor. |

El primer intento de B no pudo iniciar Chromium por `setsockopt: Operation not permitted` del sandbox; la repetición autorizada fuera de esa restricción completó las ocho pruebas. No se atribuyó ese fallo a la aplicación ni se ocultó con una respuesta simulada. Los logs de ejecución en `/tmp/tc-final-{php,mysql,browser}.log` son artefactos temporales del entorno, no documentación portable ni respaldo.

## Producto y recorrido operativo

| Requisito | Estado | Evidencia y límite |
| --- | --- | --- |
| Monolito Laravel/PHP, MySQL, Blade/Tailwind/Vite, migraciones y ejemplos | Implementado y verificado | F/M/B; stack y comandos en README. Alpine no fue necesario. PHP real requiere 8.4.1+, no cualquier hosting PHP 8.2. |
| Registro, login/logout; propietario y negocio propio | Implementado y verificado | F/M: dos registros independientes; B: registro, salida y reingreso. País, rubro, zona horaria y ARS/USD. |
| Membresías e internacionalización futuras | Parcial | Modelo de membresía y `lang/es`; sin invitaciones, matriz de roles ni traducción integral de vistas. |
| Cliente: crear, editar, buscar, archivar e historial asociado | Implementado y verificado | F/M, `customerIndex/customerSave/customerArchive`; archivo conserva historia. |
| Presupuesto: número por negocio, alcance/exclusiones, fechas, ítems/categorías, descuento, impuesto, anticipo y condiciones | Implementado y verificado | F/M/B: alta y totales del servidor con BCMath/centavos; reglas y redondeos en [alcance](alcance.md). |
| Borrador manual, duplicación, revisión y publicación | Implementado y verificado | F/M/B; guardar no publica. Versiones son snapshots; editar cliente/borrador no altera la versión publicada. |
| Enlace aleatorio con hash, revocable, vencimiento y sustitución | Implementado y verificado | F/M: versiones inválidas/revocadas/sustituidas/vencidas bloqueadas; privacidad en errores. Token visible solo al publicar. |
| Aceptación/rechazo/pedido de modificación sin cuenta | Implementado y verificado | F/M/B: nombre y confirmación, decisión asociada a versión; GET no decide y no se duplica. No firma certificada ni garantía de identidad. |
| Trabajo desde presupuesto aceptado, conversión idempotente | Implementado y verificado | F/M/B: conversión repetida no duplica; importe preservado. |
| Trabajo manual, estados, fechas, próxima acción, lista/tablero e historial | Implementado y verificado | F/M/B; tablero sin arrastrar tarjetas. Terminar no equivale a cobrar. |
| Cambios de importe con motivo/confirmación y límite por cobrado | Implementado y verificado | F/M: no puede reducirse por debajo de lo cobrado; queda historial. |
| Cobros parciales, fecha/medio/referencia, saldo, anulación y reintentos | Implementado y verificado | F/M: idempotencia, sobrepago rechazado, anulación con motivo; B: total 1.500,50, cobro 500, saldo 1.000,50. Solo registro interno. |
| Monedas separadas y panel de atención con datos reales | Implementado y verificado | F/M: no mezclar ARS/USD; B: panel. No ganancia, conversión monetaria ni cifras comerciales inventadas. |
| Mensaje WhatsApp editable y enlace oficial | Implementado y verificado | Generación/codificación en código y pruebas funcionales; no se envió mensaje real. Publicar/abrir no demuestra entrega o lectura. |
| CSV de clientes, presupuestos, trabajos y movimientos | Implementado y verificado | F/M: pertenencia, fórmulas peligrosas neutralizadas, sin firma del autor. Exportación no reemplaza backup. |
| Medición mínima separada de auditoría; demos excluidas | Implementado y verificado | F/M: eventos y primeros hitos. Hipótesis/activación/uso repetido en [piloto](piloto.md); sin métricas, entrevistas o validación comercial acreditadas. |

## Seguridad, protección y organización

| Requisito | Estado | Evidencia y límite |
| --- | --- | --- |
| Aislamiento por negocio en servidor, IDs, mutaciones, versiones, cobros, listas y exportaciones | Implementado y verificado | F/M: `test_business_isolation_covers_ids_mutations_versions_payments_and_lists`, claves compuestas y dos registros con saldos independientes. No auditoría externa. |
| CSRF, sesiones, límites por IP, ORM y escape de contenido | Implementado y verificado | F/M: CSRF, XSS/escape, límites independientes y privacidad pública. Preparación de cookies seguras en producción H; destino público pendiente. |
| No exponer secretos, tokens por referrer/indexación ni datos innecesarios del cliente | Implementado y verificado | F/M: headers incluso en errores, logging seguro, datos públicos mínimos. No equivale a escaneo integral de infraestructura. |
| Conservar formulario ante validación | Parcial | `old()` en campos e ítems, errores del servidor. No hay prueba de recuperación de cada campo ni protección frente a cierre/crash. |
| Guardado de borradores con estado visible | Parcial | Botón manual y confirmación «Borrador guardado»; estado de envío. **No autosave** ni recuperación local automática. |
| Advertencia antes de abandonar con cambios | Pendiente | No hay seguimiento de cambios ni `beforeunload`. Guardar antes de salir. |
| Error de conexión y reintento sin duplicar todas las operaciones | Parcial | Cobros/conversión/decisiones tienen protección; bloqueo visual de doble envío. No reintento de red guiado general ni idempotencia de toda creación. Ante respuesta incierta revisar lista antes de reenviar. |
| Conflictos de edición entre pestañas | Pendiente | Locks transaccionales protegen operaciones; no comparación de versión del formulario. Dos ediciones de borrador pueden sobrescribirse. Usar una sola pestaña editora. |
| Búsqueda/filtros clientes, presupuestos y trabajos por estado/fechas/paginación | Parcial | Clientes: búsqueda y archivados, paginación con query string. Presupuestos: paginación sin filtros. Trabajos: lista/tablero sin paginación. No filtro de fechas general, retorno conservado ni botón general de limpiar. |
| Guía corta omitible/retomable con progreso real | Pendiente | Registro configura negocio y existen enlaces/vacíos orientadores, pero no guía con estado/progreso. El guion de tester no sustituye esa función. |

## Testers, cuenta y soporte

| Requisito | Estado | Evidencia y límite |
| --- | --- | --- |
| Demo directa sin registro y datos ficticios aislados | Implementado y verificado | F/M/B: espacio por visitante, acciones reales, limpieza 24 h más siguiente ejecución horaria. No credenciales compartidas en landing. |
| Cuenta de prueba con información persistente | Implementado y verificado | F/M/B, política `/beta`; no borrado automático de cuentas registradas. H verifica persistencia local tras reinicio. |
| Aviso beta, versión y recomendación de datos ficticios | Implementado y verificado | F/M/B y `config/beta.php`. No se inventaron logos, clientes, testimonios ni estadísticas. |
| Feedback visible, pantalla, descripción, contacto opcional y versión | Implementado y verificado | F/M/B: almacenamiento real, CSRF/honeypot/throttle, pantalla por clave permitida sin token ni captura automática del formulario. Sin adjuntos. |
| Bandeja privada del administrador con estados pendiente/en revisión/resuelto | Pendiente | No hay rutas, rol de producto ni columna de estado. Propietarios no pueden consultar reportes ajenos. No suplantación. Consulta técnica restringida abajo, no una bandeja implementada. |
| Actualizar perfil personal y contraseña | Pendiente | Configuración edita el **negocio**, no nombre/email/password de usuario. |
| Recuperar acceso con correo | Pendiente | Sin rutas de recuperación ni envío transaccional. Configurar SMTP por sí solo no lo completa. No simular entrega. |
| Exportaciones accesibles desde cuenta | Implementado y verificado | F/M, enlaces en Configuración y listados. |
| Solicitud de baja con confirmación y ejecución documentada | Pendiente | No formulario ni procedimiento de eliminación de cuentas registradas validado. No promesa de borrado inmediato. No ejecutar SQL improvisado sobre historia. |
| Explicar alcance de borrado y respaldos | Parcial | Demo/feedback tienen retención documentada. Falta política operativa de baja de cuentas y copias: el feedback de una demo puede permanecer sin vínculo hasta 90 días; los backups previos no se reescriben al limpiar. No afirmar cumplimiento legal automático. |
| Políticas legales y soporte para lanzamiento | Pendiente | Requiere revisión del responsable y canal operativo definido; no se inventan textos legales. |

## Diseño, atribución y documentación

| Requisito | Estado | Evidencia y límite |
| --- | --- | --- |
| Sistema visual, móvil/escritorio, sin emojis de interfaz | Implementado y verificado | B/V; tokens, Manrope OFL local, etiquetas, vacíos, carga, errores y confirmaciones; presupuesto con tarjetas en móvil. |
| Recorrido completo exclusivamente con teclado y accesibilidad exhaustiva | Parcial | B verifica foco visible/firma; etiquetas/errores y contraste de paleta revisados. No prueba de extremo a extremo solo con teclado, lector de pantalla ni certificación WCAG. |
| Logo original horizontal, eslogan, símbolo, claro/oscuro/mono; muestra local | Implementado y verificado | B/V, [MARCA](MARCA.md), `/marca`, SVG limpios y eslogan HTML. Logo del producto separado de la firma personal. |
| Favicon SVG/ICO/PNG, Apple e iconos 192/512 reales | Implementado y verificado | B valida carga/firmas de formato; revisión de tamaños y símbolo en muestra. No comprobación en todos los sistemas físicos. |
| Nombre/dominios y disponibilidad legal | Parcial | Consultas intentadas bloqueadas por proxy, [límites de investigación](MARCA.md). Sin conflicto confirmado ni disponibilidad determinada. No se registró nada ni se sustituyó el nombre provisional. |
| Firma exacta y enlace externo seguro en todas las superficies/impresión | Implementado y verificado | Componente reusable; B, foco y PDFs interno/público, anotaciones al portfolio. Texto exacto «DESARROLLADO POR NICOLAS AGUIRRES». Ausente en CSV y archivos técnicos. |
| README técnico/editorial, autor/portfolio, sin otra agencia | Implementado y verificado | [README](../README.md), [CONTENIDO](CONTENIDO.md), [GUIA_CAPTURAS](GUIA_CAPTURAS.md), [CHANGELOG](../CHANGELOG.md). Sin publicaciones, métricas o historias inventadas; revisión humana de Codex no acreditada sin evidencia. |
| Instalación, variables sin secretos, migraciones/ejemplos/comandos y estructura | Implementado y verificado | README y scripts actuales; Docker probado, instalación nativa y descarga del remoto pendientes. No nuevas dependencias en este cierre. |

## PWA y publicación

| Requisito | Estado | Evidencia y límite |
| --- | --- | --- |
| URL pública HTTPS independiente de la computadora del usuario | Pendiente | Entorno administrado sin credenciales/identidades de despliegue ni destino configurado. **No hay URL pública real que entregar.** |
| Manifest con identidad estable y apertura standalone | Parcial | Existe `/site.webmanifest`, name/short_name/start_url `/`/scope `/`/colores/iconos. Usa **display: browser**, sin `id` explícito. No contiene tokens. |
| Iconos PWA y maskable con margen seguro | Parcial | PNG 192/512 y Apple 180 existentes. No variante maskable. |
| «Instalar TrabajoClaro», diálogo nativo por acción, alternativas por dispositivo y `/instalar` | Pendiente | No manejo `beforeinstallprompt`, página ni botón implementados. No simular instalación; usar navegador normal. |
| Instalación y login/logout/navegación desde app instalada | Pendiente | No ensayo standalone ni dispositivo instalado. No declarar PWA terminada. |
| Conexión obligatoria y caché segura/actualización sin pérdida | Parcial | Requiere conexión. **No service worker ni caché offline de aplicación**; no se implementó sincronización ni actualización PWA. No prometer recuperación de formularios. |
| Verificaciones públicas de landing/demo/registro/decisión/trabajo/cobro/feedback/aislamiento/PWA | Pendiente | F/M/B son locales; repetir en destino autorizado. Caducidad, cuotas y suspensión desconocidas. |

## Operación

| Requisito | Estado | Evidencia y límite |
| --- | --- | --- |
| Salud sin datos/secretos | Implementado y verificado | O: GET `/up` 200. Es salud de arranque Laravel, **no verifica DB/cron/backup ni disponibilidad externa**. |
| Logs útiles sin secretos | Implementado y verificado | F/M: `SafeLog` oculta excepción/credenciales/token y conserva tipo/código/archivo/línea. Sin tablero de errores ni alertas automáticas. |
| Producción: debug off, secretos externos, DB privada, migración controlada y volúmenes | Parcial | H y configuración `compose.production.yaml`, no ejecución en hosting real. La imagen anterior no incorpora automáticamente cambios posteriores de marca. Reconstruir desde checkout al desplegar. |
| Persistencia al reiniciar | Implementado y verificado | H: huella anterior/posterior idéntica y recuentos de negocio/usuarios/clientes/presupuestos/trabajos/cobros/feedback. Solo ensayo local anterior, no recreación o desastre del proveedor. |
| Ejecutar respaldo y restauración de prueba | Implementado y verificado | H: dump comprimido restaurado en `trabajoclaro_restore_check`, recuentos coincidentes. Script en `scripts/backup-production.sh`. No se repitió en este cierre ni hay respaldo externo automático autorizado. |
| Scheduler y colas | Parcial | O muestra programación; F/M ejecutan limpiezas y retención. Servicio `scheduler` preparado y comprobado en ensayo anterior. No monitoreo de ejecución futura. No trabajos de cola de aplicación que requieran worker hoy. |
| Rollback y límite de migraciones | Parcial | Procedimiento abajo y en [despliegue](despliegue.md); no ensayo de rollback completo. |
| IA/fiscal/pagos/suscripciones/inventario/nómina/contabilidad/marketplace/chat/nativas/offline/automatización WhatsApp | Fuera de la primera beta | Exclusión explícita. Registro manual de cobros e instalación web solicitada no equivalen a procesamiento de pagos ni app nativa. |

## Guion exacto de diez minutos

**Precondición:** entorno local encendido por el desarrollador, o futura URL HTTPS comprobada. Actualmente un tester externo no puede acceder desde una URL entregada. No enviar `localhost` como enlace público. Usar solo datos ficticios y conexión; no instalar como PWA en este estado.

1. **0:00–1:00:** abrir landing; leer versión/aviso. Elegir «Probar demo» (sin registro). Para probar cuenta persistente, usar «Crear cuenta de prueba», correo de prueba propio y contraseña personal; nunca compartir claves.
2. **1:00–2:00:** en Configuración revisar nombre/rubro/país/zona/moneda. En Clientes crear «Cliente ficticio beta», sin datos personales; comprobar que aparece en búsqueda.
3. **2:00–4:00:** Nuevo presupuesto, cliente anterior, título «Estante de prueba», alcance «Fabricación e instalación», vencimiento futuro. Un ítem cantidad 1/precio 1000, ARS, descuento/impuesto/anticipo 0. Guardar y revisar **ARS 1.000,00**.
4. **4:00–5:00:** publicar versión y conservar el enlace en privado; se muestra una vez. Abrir en otra sesión privada sin cuenta del negocio. No difundir ni capturar el token.
5. **5:00–6:00:** como cliente revisar alcance/condiciones, escribir nombre ficticio, confirmar y registrar aceptación. Debe aparecer confirmación; no implica firma certificada.
6. **6:00–7:00:** volver al propietario, recargar presupuesto y pulsar «Abrir / crear trabajo». Ver cliente/importe; repetir apertura no debe crear otro trabajo. Definir estado/próxima acción/fecha.
7. **7:00–8:00:** registrar cobro interno **ARS 250,00**. Saldo esperado **ARS 750,00**; no se mueve dinero. Marcar terminado no elimina ese saldo. No reenviar a ciegas si se corta la conexión.
8. **8:00–9:00:** revisar tablero y exportación en Configuración. Salir y volver a entrar si se registró cuenta. Otra demo o cuenta no debe mostrar el cliente/documento anteriores; no explorar datos de terceros reales.
9. **9:00–10:00:** «Enviar feedback», pantalla, descripción de qué intentó/esperaba/ocurrió y contacto opcional. Confirmar «Guardamos tu feedback». Sin adjuntos, contraseñas, enlaces privados ni contenido personal.

Guardar manualmente antes de salir, editar en una sola pestaña, no depender de recuperación por correo. Límites de IP pueden requerir esperar; no desactivar protecciones públicas. Demo 24 h; cuentas persistentes sin limpieza automática; feedback 90 días. Detalle y preguntas en [piloto](piloto.md).

## Consultar feedback y errores

No hay administrador web de producto. Solo el operador autorizado con acceso al servidor/base puede consultar feedback; **no otorgar shell o base a testers** ni crear una cuenta administrativa compartida.

Desde el checkout local, una consulta técnica limitada (datos ficticios) es:

```sh
docker compose exec -T app php artisan tinker --execute='dump(App\Models\FeedbackEntry::query()->latest()->limit(20)->get(["id", "screen", "release", "description", "contact", "created_at"])->toArray());'
```

Muestra texto/contacto que puede ser personal: no pegar resultados en Git, capturas públicas o chat. No permite cambiar estados porque esa función no existe. En producción usar el acceso seguro del proveedor y mínimo privilegio; no exponer esta consulta en una ruta web.

Errores locales: revisar de forma privada `storage/logs/laravel.log` (o archivos diarios según `LOG_CHANNEL`). Producción: mismo storage persistente y logs de contenedores/proveedor, con filtros de seguridad existentes. Buscar fecha/hora, versión, pantalla, tipo de excepción y ubicación; solicitar pasos de reproducción sin claves ni token. No habilitar debug ni registrar cuerpos completos. `/up` no reemplaza investigar DB/scheduler.

## Bloqueos y decisión de entrega

- **Tu intervención para acceso externo:** elegir/conectar de forma segura un hosting existente autorizado compatible PHP 8.4.1+/MySQL o contenedores y dominio/subdominio HTTPS. No pegar claves en chat. [Opciones y requisitos](despliegue.md); Vercel requiere adaptación y DB externa, no es el destino directo preparado. No se contrataron servicios ni tocaron sitios existentes.
- Confirmar cuotas/costos/vigencia/inactividad, responsable operativo, política de bajas/respaldos y revisión legal antes de invitar usuarios con datos reales.
- Correo/recuperación requiere implementación y proveedor, no solo credenciales. Pendientes de PWA, soporte y protección de edición requieren una decisión posterior de alcance, no una aprobación ficticia de pruebas.
- La búsqueda de nombre/dominios requiere acceso a fuentes externas permitidas y revisión especializada si se pretende consolidar la marca. No se afirma disponibilidad legal.

**Entrega actual:** candidata para pruebas locales acompañadas del recorrido principal. No declarar beta pública ni PWA lista. La próxima publicación debe reconstruir la imagen, migrar explícitamente, comprobar HTTPS y repetir recorrido/aislamiento/persistencia/backup en el destino real. No se agregó funcionalidad nueva en este cierre.
