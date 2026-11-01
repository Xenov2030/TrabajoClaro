# Guion de diez minutos · migración Next.js/Supabase

**URL pública:** [https://trabajo-claro-orpin.vercel.app/](https://trabajo-claro-orpin.vercel.app/). El recorrido principal de demo se verificó el 2026-11-01 con versión `0.2.0-beta.1`, en móvil y escritorio, contra Supabase real. Usar ese dominio; el enlace de rama redirigió al login de Vercel. Se conserva el navegador normal. Para probar instalación, abrir [la página de instalación](https://trabajo-claro-orpin.vercel.app/instalar) y seguir [el guion PWA](PWA.md); la instalación física/standalone aún necesita comprobación manual. Siempre conexión y datos ficticios. [Evidencia y límites](VERIFICACION_MIGRACION.md#verificación-pública--2026-11-01).

1. **0–1 min:** abrir landing; «Probar demo» crea usuario anónimo y negocio privado si Supabase lo permite. Alternativa «Crear cuenta»: correo/contraseña propios, confirmar correo si se solicita; Google solo si fue configurado.
2. **1–2:** completar negocio, rubro, país, zona y ARS. Crear «Cliente ficticio beta», sin contacto real.
3. **2–4:** crear presupuesto «Estante de prueba», alcance, fecha/vencimiento futuros, ítem cantidad 1/precio 1000, descuento/impuesto/anticipo 0. Guardar, verificar total ARS 1.000,00. Guardado manual; no autosave.
4. **4–5:** publicar versión y conservar el enlace. Abrir **el enlace completo incluido su fragmento** en ventana privada sin cuenta del negocio. No capturar ni difundir el token.
5. **5–6:** revisar y aceptar como «Cliente ficticio», con confirmación explícita. No es firma digital certificada.
6. **6–7:** volver al propietario, recargar y «Abrir / crear trabajo». Debe conservar alcance/importe; abrir otra vez no duplica. Definir próxima acción y fecha.
7. **7–8:** cobro manual ARS 250, saldo ARS 750. No mueve dinero. Revisar historial y, opcionalmente, anular con motivo. No reenviar a ciegas tras un corte de conexión.
8. **8–9:** consultar lista/tablero y exportación en Configuración. Con cuenta registrada, salir/reingresar y verificar persistencia. No usar dos pestañas editando el mismo documento.
9. **9–10:** «Enviar feedback», elegir pantalla y describir resultado esperado/observado; contacto opcional. Ver confirmación de guardado. Sin adjuntos, secretos ni datos personales.

Prueba adicional de aislamiento: otra cuenta/demo en perfil independiente no debe ver cliente, presupuesto ni trabajo anteriores. El operador debe comprobarlo también en el servicio real. Recuperación, perfil/contraseña y baja de cuenta siguen pendientes; no depender de ellas con información real. Demo 24 h más limpieza programada, feedback 90 días más limpieza. Cuotas y suspensión del plan se comunican después de verificarlas.
