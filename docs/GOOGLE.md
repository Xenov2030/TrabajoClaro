# Conectar el acceso con Google

Revisión: 2026-11-01, Argentina. TrabajoClaro usa Supabase Auth para iniciar sesión con Google; no solicita acceso a Drive ni almacena datos en la nube personal del usuario.

## Estado comprobado

El botón «Continuar con Google» existe en acceso y registro. Invoca `signInWithOAuth({ provider: 'google', options: { redirectTo: location.origin + '/auth/callback' } })`. El 2026-11-01 se probó el botón en https://trabajo-claro-orpin.vercel.app/login: Supabase respondió **HTTP 400, `Unsupported provider: provider is not enabled`**. No se ingresó una cuenta Google ni se completó OAuth. Por tanto, la integración de código no significa proveedor configurado ni login real verificado.

No hay acceso administrativo a Google Cloud/Supabase conectado a esta tarea. El titular debe configurar el cliente y guardar su secreto de forma segura en Supabase. No copiar secretos a GitHub, Vercel `NEXT_PUBLIC_*`, capturas ni chat.

## 1. Supabase: localizar Google y su callback

1. Abrir el proyecto **TrabajoClaro** en Supabase.
2. Ir a **Authentication → Sign In / Providers → Google**.
3. Localizar la **Callback URL / OAuth Redirect URL** que muestra Supabase. Para el proyecto observado en la prueba es:

   ```text
   https://jowizadkvcsnhteosqvo.supabase.co/auth/v1/callback
   ```

   Confirmar copiándola desde la consola: si el proyecto cambia, cambia esta dirección. No usar `/rest/v1/` ni la dirección del dashboard.
4. Mantener abierto el formulario. Requiere un Client ID y un Client Secret de Google; la Publishable key de Supabase no reemplaza estos campos.

## 2. Google Cloud: crear un cliente OAuth web

1. Abrir [Google Cloud Console](https://console.cloud.google.com/). Seleccionar/crear un proyecto dedicado a TrabajoClaro. No modificar el cliente de Jornadapp sin revisar sus integraciones existentes. No activar servicios pagos para este procedimiento.
2. Entrar en **Google Auth Platform**. Según la interfaz, también aparece como **APIs & Services → OAuth consent screen / Credentials**.
3. Configurar **Branding**: nombre TrabajoClaro y correos de soporte/contacto reales del titular. No inventar políticas, dominios verificados ni aprobación de Google. Si exige URLs o verificación adicionales, completar ese requisito antes de ofrecer acceso general.
4. En **Audience**, elegir el público adecuado. Para testers externos a una organización corresponde normalmente **External**. Si queda en **Testing**, agregar sus cuentas Google a **Test users**. No afirmar que cualquiera puede ingresar mientras siga limitado a esos usuarios.
5. Solicitar solo identidad básica: `openid`, correo y perfil. No solicitar Drive, Gmail ni permisos que esta aplicación no utiliza.
6. En **Clients → Create client**, elegir **Web application**; nombre, por ejemplo, `TrabajoClaro web`.
7. En **Authorized redirect URIs**, agregar exactamente la Callback URL de **Supabase** del paso anterior. Esta es la dirección a la que Google devuelve la autorización; no es `/auth/callback` de Vercel.
8. Si la consola solicita **Authorized JavaScript origins**, usar `https://trabajo-claro-orpin.vercel.app` como origen del sitio, sin ruta. El flujo actual es redirección mediante Supabase, no el SDK JavaScript de Google.
9. Crear el cliente y copiar **Client ID** y **Client Secret** directamente al formulario seguro de Google en Supabase. No enviarlos en chat ni colocarlos en el frontend.

## 3. Supabase: habilitar y guardar

1. Volver a **Authentication → Sign In / Providers → Google**.
2. Completar **Client IDs** con el Client ID y **Client Secret** con el secreto del cliente web.
3. Activar **Enable Sign in with Google** y guardar. Mantener las comprobaciones de seguridad predeterminadas; no deshabilitar nonce ni omitir verificaciones para solucionar errores.
4. En **Authentication → URL Configuration**, configurar:

   | Campo | Valor |
   | --- | --- |
   | Site URL | `https://trabajo-claro-orpin.vercel.app` |
   | Redirect URLs | `https://trabajo-claro-orpin.vercel.app/auth/callback` |

   Esta segunda URL es el retorno de **Supabase a TrabajoClaro**, distinto del callback autorizado en Google.
5. No agregar comodines amplios de Vercel ni el enlace de rama protegido como destino de testers. Si se cambia el dominio, actualizar ambas configuraciones con los dominios realmente autorizados.

Guardar el proveedor y las Redirect URLs en Supabase **no requiere redeploy** de Vercel: el botón ya está implementado. Un cambio de variables `NEXT_PUBLIC_*`, en cambio, sí exige recompilar.

## 4. Verificación manual necesaria

El titular o un tester autorizado debe usar su propia cuenta Google; el asistente no puede completar esa autenticación sin una sesión autorizada disponible y no solicita contraseñas/códigos de Google.

1. En ventana privada, abrir https://trabajo-claro-orpin.vercel.app/login y pulsar «Continuar con Google».
2. Debe aparecer el selector/consentimiento de Google, no el error de proveedor deshabilitado. En modo Testing, usar una cuenta agregada como tester.
3. Autorizar identidad básica y comprobar que vuelve a TrabajoClaro. Si aparece el enlace «Continuar a mi negocio» en `/auth/callback`, usarlo para abrir `/app`.
4. Configurar negocio si la cuenta es nueva, crear un cliente/presupuesto ficticio y salir.
5. Volver a ingresar con la misma cuenta y comprobar que conserva el negocio y sus datos. Una segunda cuenta debe tener su propio negocio; no heredar información de otra demo o cuenta.
6. Probar cancelar el consentimiento sin quedar conectado a la cuenta anterior. Revisar resultados y comunicar solo el error, nunca la URL de retorno completa, códigos, tokens o secretos.

Hasta completar estos pasos, registrar **Google configurado, pendiente de verificación completa**. La comprobación automática de que aparece Google no acredita acceso, retorno, persistencia ni aislamiento de cuentas reales.

## Diagnóstico

| Error | Qué revisar |
| --- | --- |
| `provider is not enabled` | Habilitación/guardado del proveedor Google en el proyecto Supabase correcto. |
| `redirect_uri_mismatch` | Google debe autorizar exactamente el callback `/auth/v1/callback` de Supabase. |
| Usuario no autorizado / acceso bloqueado | Audience/Testing y lista Test users, estado de publicación o políticas de la cuenta Google. |
| Vuelve al dominio equivocado | Site URL y Redirect URLs de Supabase; dominio desde el que se inició el flujo. |
| Fallo de código PKCE o sesión | Mismo navegador/contexto y almacenamiento habilitado; no copiar el enlace de callback a otro dispositivo. Revisar error sin publicar códigos/tokens. |
