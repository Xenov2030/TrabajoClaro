"use client";
import { createContext, useContext, useEffect, useState } from "react";
const InstallContext = createContext({
    available: false,
    installed: false,
    busy: false,
    message: "",
    install: async () => {},
    platform: "desktop",
    update: false,
});
export function InstallProvider({ children }) {
    const [prompt, P] = useState(null),
        [installed, I] = useState(false),
        [busy, B] = useState(false),
        [message, M] = useState(""),
        [platform, F] = useState("desktop"),
        [update, U] = useState(false);
    useEffect(() => {
        const media = matchMedia("(display-mode: standalone)");
        const sync = () => I(media.matches || navigator.standalone === true);
        sync();
        media.addEventListener("change", sync);
        const ios =
            /iPad|iPhone|iPod/.test(navigator.userAgent) ||
            (navigator.platform === "MacIntel" && navigator.maxTouchPoints > 1);
        F(
            ios
                ? "ios"
                : /Android/.test(navigator.userAgent)
                  ? "android"
                  : "desktop",
        );
        const before = (e) => {
            e.preventDefault();
            P(e);
        };
        const done = () => {
            I(true);
            P(null);
            M("TrabajoClaro se instaló. Podés abrirlo desde su icono.");
        };
        window.addEventListener("beforeinstallprompt", before);
        window.addEventListener("appinstalled", done);
        let active = true;
        if ("serviceWorker" in navigator && window.isSecureContext) {
            navigator.serviceWorker
                .register("/sw.js", { scope: "/", updateViaCache: "none" })
                .then((reg) => {
                    if (!active) return;
                    if (reg.waiting) U(true);
                    reg.addEventListener("updatefound", () => {
                        const worker = reg.installing;
                        worker?.addEventListener("statechange", () => {
                            if (
                                active &&
                                worker.state === "installed" &&
                                navigator.serviceWorker.controller
                            )
                                U(true);
                        });
                    });
                })
                .catch(() => {
                    if (active)
                        M(
                            "No se pudo preparar la instalación. Revisá la conexión y volvé a abrir esta página; podés seguir usando el navegador.",
                        );
                });
        }
        return () => {
            active = false;
            media.removeEventListener("change", sync);
            window.removeEventListener("beforeinstallprompt", before);
            window.removeEventListener("appinstalled", done);
        };
    }, []);
    async function install() {
        if (!prompt || busy) return;
        B(true);
        M("");
        try {
            await prompt.prompt();
            const result = await prompt.userChoice;
            M(
                result.outcome === "accepted"
                    ? "Solicitud aceptada. El navegador completará la instalación."
                    : "Instalación cancelada. Podés seguir usando TrabajoClaro en el navegador.",
            );
        } catch {
            M(
                "El navegador no pudo abrir la instalación. Consultá las instrucciones o volvé a intentarlo desde su menú.",
            );
        } finally {
            P(null);
            B(false);
        }
    }
    return (
        <InstallContext.Provider
            value={{
                available: !!prompt,
                installed,
                busy,
                message,
                install,
                platform,
                update,
            }}
        >
            {children}
        </InstallContext.Provider>
    );
}
export function InstallAction() {
    const c = useContext(InstallContext);
    if (c.installed) return null;
    return c.available ? (
        <button
            type="button"
            className="button secondary"
            disabled={c.busy}
            onClick={c.install}
        >
            {c.busy ? "Abriendo instalación…" : "Instalar TrabajoClaro"}
        </button>
    ) : (
        <a className="button secondary" href="/instalar">
            Instalar TrabajoClaro
        </a>
    );
}
export function InstallPage() {
    const c = useContext(InstallContext);
    return (
        <section className="card install-instructions">
            <img
                src="/icon-192.png"
                width="80"
                height="80"
                alt="Icono de TrabajoClaro"
            />
            <h1>TrabajoClaro, a mano</h1>
            <p>
                Agregá TrabajoClaro a tu dispositivo para abrirlo desde su
                propio icono. Es una aplicación web: no necesitás descargar un
                APK ni un ejecutable.
            </p>
            <p className="notice">
                <strong>Requiere conexión para trabajar.</strong> Instalarla no
                agrega funcionamiento offline, sincronización ni respaldo de
                datos. No cierres un formulario sin guardarlo.
            </p>
            {c.installed ? (
                <p role="status" className="notice">
                    Ya estás usando TrabajoClaro en modo aplicación.
                </p>
            ) : (
                <>
                    <InstallAction />
                    {!c.available && (
                        <p>
                            Este navegador todavía no ofrece un diálogo de
                            instalación. Seguí los pasos de tu dispositivo; si
                            la opción no aparece, podés continuar desde el
                            navegador.
                        </p>
                    )}
                </>
            )}
            {c.message && (
                <p role="status" className="notice">
                    {c.message}
                </p>
            )}
            {c.update && (
                <p role="status" className="notice">
                    Hay una actualización disponible. Guardá tus formularios y
                    cerrá todas las ventanas de TrabajoClaro; al volver a abrir
                    se podrá activar. No recargamos tu trabajo automáticamente.
                </p>
            )}
            <section aria-label="Instrucciones de instalación">
                <h2>
                    {c.platform === "ios"
                        ? "En este iPhone o iPad"
                        : c.platform === "android"
                          ? "En este dispositivo Android"
                          : "En tu computadora"}
                </h2>
                {c.platform === "ios" ? (
                    <p>
                        En Safari, abrí el menú Compartir y elegí «Añadir a
                        pantalla de inicio». Si aparece «Abrir como app web»,
                        mantenelo activado y confirmá «Añadir». Si otro
                        navegador no muestra esa opción, abrí este sitio en
                        Safari.
                    </p>
                ) : c.platform === "android" ? (
                    <p>
                        En Chrome o Edge, abrí el menú del navegador y buscá
                        «Instalar aplicación» o «Añadir a pantalla de inicio».
                        Si solo ofrece un acceso directo, ese acceso puede
                        abrirse como una pestaña y no implica instalación
                        standalone.
                    </p>
                ) : (
                    <p>
                        En Chrome o Edge, buscá el icono de instalación en la
                        barra de direcciones o la opción «Instalar TrabajoClaro»
                        en el menú. En Safari para Mac compatible, usá «Archivo
                        → Añadir al Dock». Si tu navegador no ofrece
                        instalación, usá la web normalmente.
                    </p>
                )}
                <details>
                    <summary>Ver instrucciones para otros dispositivos</summary>
                    <h3>iPhone y iPad</h3>
                    <p>Safari → Compartir → Añadir a pantalla de inicio.</p>
                    <h3>Android</h3>
                    <p>
                        Chrome/Edge → Menú → Instalar aplicación o Añadir a
                        pantalla de inicio.
                    </p>
                    <h3>Computadora</h3>
                    <p>
                        Chrome/Edge → icono o menú de instalación. Safari para
                        Mac compatible → Archivo → Añadir al Dock. Firefox u
                        otros pueden no ofrecer esta función.
                    </p>
                </details>
            </section>
            <hr />
            <h2>Acceso y datos</h2>
            <p>
                La instalación abre el mismo servicio y conserva su separación
                por cuenta. Tu sesión puede necesitar un nuevo ingreso según el
                navegador. Google sigue requiriendo autorización y la demo
                mantiene su vencimiento; instalarla no evita el cierre de sesión
                ni conserva una demo vencida.
            </p>
            <p>
                Para recibir una versión nueva, guardá antes de cerrar y volver
                a abrir. Si el navegador no encuentra conexión, no podremos
                guardar operaciones; revisá los registros antes de reenviar una
                solicitud con resultado incierto.
            </p>
            <div className="actions">
                <a className="button" href="/app">
                    Abrir mi negocio
                </a>
                <a href="/">Continuar en el navegador</a>
            </div>
        </section>
    );
}
