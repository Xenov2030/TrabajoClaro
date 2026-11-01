"use client";
import { useEffect, useState } from "react";
import { Brand, Author } from "./brand";
import { InstallAction, InstallPage } from "./install";
import { cloud, cloudConfig, command } from "../lib/cloud.mjs";
import {
    money,
    input,
    csv,
    decimal,
    quote as previewQuote,
} from "../lib/money.mjs";
const RELEASE = "0.2.0-beta.1",
    TZ = "America/Argentina/Buenos_Aires";
const statuses = [
    "pendiente",
    "en curso",
    "en espera",
    "terminado",
    "cancelado",
];
const today = (timezone = TZ) =>
    new Intl.DateTimeFormat("en-CA", {
        timeZone: timezone,
        year: "numeric",
        month: "2-digit",
        day: "2-digit",
    }).format(new Date());
const later = () =>
    new Date(Date.now() + 15 * 86400000).toISOString().slice(0, 10);
export default function Application() {
    const [path, setPath] = useState("/"),
        [session, setSession] = useState(null),
        [business, setBusiness] = useState(null),
        [records, setRecords] = useState([]),
        [loading, setLoading] = useState(true),
        [error, setError] = useState(""),
        [notice, setNotice] = useState("");
    async function load(background = false) {
        if (!background) setLoading(true);
        try {
            const config = cloudConfig();
            if (!config.ready) {
                setLoading(false);
                return;
            }
            const { data, error } = await cloud().auth.getSession();
            if (error) throw error;
            if (window.location.pathname === "/auth/callback") {
                if (data.session) {
                    window.location.replace("/app");
                    return;
                }
                throw Error(
                    "No se completó el acceso. Volvé a Ingresar e iniciá la autorización desde este navegador.",
                );
            }
            setSession(data.session);
            if (data.session) {
                const b = await cloud()
                    .from("tc_businesses")
                    .select("*")
                    .maybeSingle();
                if (b.error)
                    throw Error(
                        "No se pudo cargar el negocio. Verificá la migración y la conexión de Supabase.",
                    );
                setBusiness(b.data);
                if (b.data) {
                    const rows = await cloud()
                        .from("tc_records")
                        .select("*")
                        .order("created_at", { ascending: false })
                        .limit(1000);
                    if (rows.error) throw rows.error;
                    setRecords(rows.data);
                }
            } else {
                setBusiness(null);
                setRecords([]);
            }
        } catch (e) {
            setError(e.message);
        } finally {
            setLoading(false);
        }
    }
    useEffect(() => {
        setPath(window.location.pathname);
        load();
    }, []);
    async function refresh(message = "Cambios guardados.") {
        await load(true);
        setNotice(message);
    }
    const customers = records.filter((r) => r.kind === "customer"),
        quotes = records.filter((r) => r.kind === "quote"),
        jobs = records.filter((r) => r.kind === "job"),
        payments = records.filter((r) => r.kind === "payment");
    const get = (id) => records.find((r) => r.id === id),
        customerName = (id) =>
            customers.find((c) => c.id === id)?.data.name ||
            "Cliente archivado";
    const balance = (j) =>
        j.data.amount_minor -
        payments
            .filter((p) => p.data.job_id === j.id && !p.data.voided_at)
            .reduce((a, p) => a + p.data.amount_minor, 0);
    const isApp = path.startsWith("/app"),
        parts = path.split("/").filter(Boolean),
        kind = parts[1],
        id = parts[2];
    async function logout() {
        const { error } = await cloud().auth.signOut();
        if (error) throw error;
        location.href = "/";
    }
    async function demo() {
        const { error } = await cloud().auth.signInAnonymously();
        if (error)
            throw Error(
                "No se pudo abrir la demo. El operador debe habilitar acceso anónimo en Supabase y revisar sus límites.",
            );
        await command("setup", {
            name: "Taller del Sur · ejemplo",
            industry: "Fabricación a medida",
            country: "AR",
            timezone: TZ,
            currency: "ARS",
        });
        await command("customer", {
            data: {
                name: "Cliente de ejemplo",
                phone: "",
                email: "",
                address: "",
                notes: "Datos ficticios de esta demo privada.",
            },
        });
        location.href = "/app";
    }
    async function action(name, payload, msg) {
        try {
            setError("");
            const result = await command(name, payload);
            await refresh(msg);
            return result;
        } catch (e) {
            setError(e.message);
            throw e;
        }
    }
    const authenticated = session && business;
    const businessToday = today(business?.data.timezone || TZ);
    return (
        <>
            <header className="topbar">
                <div className="shell header-inner">
                    <a href="/" className="brand">
                        <Brand />
                        <small>beta</small>
                    </a>
                    <div className="header-actions">
                        {session ? (
                            <>
                                <span className="muted">
                                    {business?.data.name || "Tu cuenta"}
                                </span>
                                <AsyncButton secondary onClick={logout}>
                                    Salir
                                </AsyncButton>
                            </>
                        ) : (
                            <>
                                <a href="/login">Ingresar</a>
                                <a className="button small" href="/register">
                                    Crear cuenta
                                </a>
                            </>
                        )}
                    </div>
                </div>
                {authenticated && (
                    <nav className="shell nav-links" aria-label="Principal">
                        {[
                            ["", "Inicio"],
                            ["customers", "Clientes"],
                            ["quotes", "Presupuestos"],
                            ["jobs", "Trabajos"],
                            ["settings", "Configuración"],
                        ].map(([p, l]) => (
                            <a
                                key={p}
                                href={"/app" + (p ? "/" + p : "")}
                                aria-current={
                                    path === "/app" + (p ? "/" + p : "")
                                        ? "page"
                                        : undefined
                                }
                            >
                                {l}
                            </a>
                        ))}
                    </nav>
                )}
            </header>
            <div className="beta-banner no-print">
                <div className="shell actions">
                    <small>
                        Beta {RELEASE}. Usá datos ficticios. Requiere conexión.
                        {business?.is_demo
                            ? " Demo privada: vence a las 24 horas."
                            : ""}
                    </small>
                    <a href={"/feedback?screen=" + screen(path)}>
                        Enviar feedback
                    </a>
                </div>
            </div>
            <main className="shell">
                {records.length >= 1000 && (
                    <p role="alert" className="notice">
                        Se alcanzó el límite de 1.000 registros cargados. Las
                        listas y exportaciones pueden estar incompletas;
                        consultá al operador antes de usarlas como copia.
                    </p>
                )}
                {error && (
                    <div role="alert" className="notice">
                        {error}{" "}
                        <button
                            className="text-button"
                            onClick={() => {
                                setError("");
                                load();
                            }}
                        >
                            Reintentar carga
                        </button>
                    </div>
                )}
                {notice && (
                    <div role="status" className="notice">
                        {notice}
                    </div>
                )}
                {path === "/p" || path.startsWith("/p/") ? (
                    <PublicQuote
                        token={
                            parts[1] ||
                            (typeof window !== "undefined"
                                ? window.location.hash.slice(1)
                                : "")
                        }
                    />
                ) : loading ? (
                    <p role="status">Cargando TrabajoClaro…</p>
                ) : isApp ? (
                    !session ? (
                        <div className="empty">
                            <h1>Ingresá para continuar</h1>
                            <a className="button" href="/login">
                                Ingresar
                            </a>
                        </div>
                    ) : !business ? (
                        <Setup
                            onSaved={() => refresh("Negocio configurado.")}
                        />
                    ) : (
                        <>
                            {kind === "customers" ? (
                                id ? (
                                    <CustomerForm
                                        record={id === "new" ? null : get(id)}
                                        onSave={async (d) => {
                                            const r = await action(
                                                "customer",
                                                {
                                                    id:
                                                        id === "new"
                                                            ? undefined
                                                            : id,
                                                    data: d,
                                                },
                                                "Cliente guardado.",
                                            );
                                            location.href =
                                                "/app/customers/" + r.id;
                                        }}
                                        onArchive={async () => {
                                            await action(
                                                "archive",
                                                { id },
                                                "Cliente archivado. Se conserva su historial.",
                                            );
                                            location.href = "/app/customers";
                                        }}
                                        quotes={quotes.filter(
                                            (q) => q.data.customer_id === id,
                                        )}
                                        jobs={jobs.filter(
                                            (j) => j.data.customer_id === id,
                                        )}
                                    />
                                ) : (
                                    <List
                                        title="Clientes"
                                        rows={customers}
                                        type="customers"
                                        name={(r) => r.data.name}
                                        status={(r) =>
                                            r.data.archived_at
                                                ? "Archivado"
                                                : "Activo"
                                        }
                                        subtitle={(r) =>
                                            r.data.phone ||
                                            r.data.email ||
                                            "Sin contacto"
                                        }
                                        states={["Activo", "Archivado"]}
                                    />
                                )
                            ) : kind === "quotes" ? (
                                id === "new" || parts[3] === "edit" ? (
                                    <QuoteForm
                                        record={id === "new" ? null : get(id)}
                                        business={business}
                                        customers={customers}
                                        onSave={async (d) => {
                                            const r = await action(
                                                "quote",
                                                {
                                                    id:
                                                        id === "new"
                                                            ? undefined
                                                            : id,
                                                    revision: get(id)?.revision,
                                                    data: d,
                                                },
                                                "Borrador guardado.",
                                            );
                                            location.href =
                                                "/app/quotes/" + r.id;
                                        }}
                                    />
                                ) : id ? (
                                    <QuoteDetail
                                        record={get(id)}
                                        business={business}
                                        customerName={customerName}
                                        versions={records.filter(
                                            (r) =>
                                                r.kind === "version" &&
                                                r.data.number ===
                                                    get(id)?.data.number,
                                        )}
                                        action={action}
                                    />
                                ) : (
                                    <List
                                        title="Presupuestos"
                                        rows={quotes}
                                        type="quotes"
                                        name={(r) =>
                                            "#" +
                                            r.data.number +
                                            " · " +
                                            r.data.title
                                        }
                                        status={(r) => r.data.status}
                                        subtitle={(r) =>
                                            money(
                                                r.data.total_minor,
                                                r.data.currency,
                                            )
                                        }
                                        states={[
                                            "borrador",
                                            "pendiente",
                                            "aceptado",
                                            "rechazado",
                                            "modificacion",
                                        ]}
                                    />
                                )
                            ) : kind === "jobs" ? (
                                id === "new" || parts[3] === "edit" ? (
                                    <JobForm
                                        record={id === "new" ? null : get(id)}
                                        customers={customers}
                                        currency={business.data.currency}
                                        onSave={async (d, extra) => {
                                            const r = await action(
                                                "job",
                                                {
                                                    id:
                                                        id === "new"
                                                            ? undefined
                                                            : id,
                                                    revision: get(id)?.revision,
                                                    data: d,
                                                    ...extra,
                                                },
                                                "Trabajo guardado.",
                                            );
                                            location.href = "/app/jobs/" + r.id;
                                        }}
                                    />
                                ) : id ? (
                                    <JobDetail
                                        record={get(id)}
                                        customerName={customerName}
                                        timezone={business.data.timezone}
                                        payments={payments.filter(
                                            (p) => p.data.job_id === id,
                                        )}
                                        balance={get(id) ? balance(get(id)) : 0}
                                        events={records.filter(
                                            (r) =>
                                                r.kind === "activity" &&
                                                r.data.subject_id === id,
                                        )}
                                        action={action}
                                    />
                                ) : (
                                    <List
                                        title="Trabajos"
                                        rows={jobs}
                                        type="jobs"
                                        name={(r) => r.data.title}
                                        status={(r) => r.data.status}
                                        subtitle={(r) =>
                                            "Saldo " +
                                            money(balance(r), r.data.currency)
                                        }
                                        states={statuses}
                                        board
                                    />
                                )
                            ) : kind === "settings" ? (
                                <>
                                    <Setup
                                        business={business}
                                        onSaved={() =>
                                            refresh(
                                                "Configuración guardada. Los documentos publicados se conservan.",
                                            )
                                        }
                                    />
                                    <section className="card">
                                        <h2>Exportar mis datos</h2>
                                        <p>
                                            CSV del negocio actual. No reemplaza
                                            un respaldo de la base.
                                        </p>
                                        <div className="actions">
                                            {[
                                                "customer",
                                                "quote",
                                                "job",
                                                "payment",
                                            ].map((k, i) => (
                                                <button
                                                    className="button secondary"
                                                    key={k}
                                                    onClick={() =>
                                                        download(
                                                            k,
                                                            records.filter(
                                                                (r) =>
                                                                    r.kind ===
                                                                    k,
                                                            ),
                                                        )
                                                    }
                                                >
                                                    {
                                                        [
                                                            "Clientes",
                                                            "Presupuestos",
                                                            "Trabajos",
                                                            "Movimientos",
                                                        ][i]
                                                    }
                                                </button>
                                            ))}
                                        </div>
                                        <p className="muted">
                                            Perfil personal, cambio de
                                            contraseña, recuperación y baja de
                                            cuenta siguen pendientes. No
                                            ingreses información sensible.
                                        </p>
                                    </section>
                                </>
                            ) : (
                                <>
                                    <div className="page-heading">
                                        <div>
                                            <span className="eyebrow">
                                                Tu negocio, en orden
                                            </span>
                                            <h1>Qué requiere atención</h1>
                                        </div>
                                        <a
                                            className="button"
                                            href="/app/quotes/new"
                                        >
                                            Nuevo presupuesto
                                        </a>
                                    </div>
                                    <div className="detail-grid">
                                        <section className="card">
                                            <h2>Presupuestos pendientes</h2>
                                            <RecordLinks
                                                rows={quotes.filter(
                                                    (q) =>
                                                        q.data.status ===
                                                        "pendiente",
                                                )}
                                                type="quotes"
                                                empty="No hay presupuestos esperando respuesta."
                                            />
                                        </section>
                                        <section className="card">
                                            <h2>Terminados con saldo</h2>
                                            <RecordLinks
                                                rows={jobs.filter(
                                                    (j) =>
                                                        j.data.status ===
                                                            "terminado" &&
                                                        balance(j) > 0,
                                                )}
                                                type="jobs"
                                                empty="No hay trabajos terminados con saldo."
                                            />
                                        </section>
                                        <section className="card">
                                            <h2>Próximas acciones vencidas</h2>
                                            <RecordLinks
                                                rows={jobs.filter(
                                                    (j) =>
                                                        ![
                                                            "terminado",
                                                            "cancelado",
                                                        ].includes(
                                                            j.data.status,
                                                        ) &&
                                                        j.data.action_on &&
                                                        j.data.action_on <
                                                            businessToday,
                                                )}
                                                type="jobs"
                                                empty="Sin acciones vencidas."
                                            />
                                        </section>
                                        <section className="card">
                                            <h2>
                                                Entregas próximas o atrasadas
                                            </h2>
                                            <RecordLinks
                                                rows={jobs.filter(
                                                    (j) =>
                                                        ![
                                                            "terminado",
                                                            "cancelado",
                                                        ].includes(
                                                            j.data.status,
                                                        ) &&
                                                        j.data.due_on &&
                                                        j.data.due_on <=
                                                            new Date(
                                                                Date.now() +
                                                                    7 *
                                                                        86400000,
                                                            )
                                                                .toISOString()
                                                                .slice(0, 10),
                                                )}
                                                type="jobs"
                                                empty="Sin entregas próximas."
                                            />
                                        </section>
                                    </div>
                                    <section className="card">
                                        <h2>Cobros registrados este mes</h2>
                                        {["ARS", "USD"].map((c) => (
                                            <p key={c}>
                                                {money(
                                                    payments
                                                        .filter(
                                                            (p) =>
                                                                !p.data
                                                                    .voided_at &&
                                                                p.data
                                                                    .currency ===
                                                                    c &&
                                                                p.data
                                                                    .paid_on >=
                                                                    businessToday.slice(
                                                                        0,
                                                                        7,
                                                                    ) +
                                                                        "-01",
                                                        )
                                                        .reduce(
                                                            (a, p) =>
                                                                a +
                                                                p.data
                                                                    .amount_minor,
                                                            0,
                                                        ),
                                                    c,
                                                )}
                                            </p>
                                        ))}
                                        <small>
                                            No es ganancia ni facturación
                                            fiscal.
                                        </small>
                                    </section>
                                </>
                            )}
                        </>
                    )
                ) : path === "/login" || path === "/register" ? (
                    <Auth register={path === "/register"} />
                ) : path === "/feedback" ? (
                    <Feedback />
                ) : path === "/instalar" ? (
                    <InstallPage />
                ) : path === "/marca" ? (
                    <BrandPage />
                ) : path === "/beta" ? (
                    <section className="card">
                        <h1>Cómo funciona esta beta</h1>
                        <p>
                            Requiere conexión. No procesa dinero, no factura y
                            no ofrece trabajo offline.
                        </p>
                        <p>
                            Demo: 24 horas y próxima limpieza programada.
                            Feedback: 90 días y próxima limpieza. Cuentas
                            registradas: sin borrado automático; cualquier
                            limpieza requiere aviso previo de al menos 7 días y
                            posibilidad de exportar.
                        </p>
                        <p>
                            Recuperación de contraseña y baja de cuenta están
                            pendientes. Los respaldos previos no se reescriben
                            al limpiar datos. Usá ejemplos ficticios.
                        </p>
                        <a href="/">Volver al inicio</a>
                    </section>
                ) : path === "/auth/callback" ? (
                    <section className="card">
                        <h1>Acceso a TrabajoClaro</h1>
                        <p>
                            Si la autorización terminó, te llevaremos a tu
                            negocio. Si no pudimos completar el acceso, iniciá
                            el proceso nuevamente.
                        </p>
                        <a className="button" href="/login">
                            Volver a ingresar
                        </a>
                    </section>
                ) : path === "/" ? (
                    <Landing demo={demo} />
                ) : (
                    <section className="empty">
                        <h1>Página no encontrada</h1>
                        <a href="/">Volver al inicio</a>
                    </section>
                )}
            </main>
            <footer className="site-footer">
                <div className="shell">
                    <span>
                        TrabajoClaro · Beta {RELEASE} ·{" "}
                        <a href="/beta">Condiciones de prueba</a> ·{" "}
                        <a href="/instalar">Instalar app</a> ·{" "}
                        <a href="/feedback">Enviar feedback</a>
                    </span>
                    <Author />
                </div>
            </footer>
        </>
    );
}
function screen(p) {
    return p === "/p" || p.startsWith("/p/")
        ? "public-quote"
        : p === "/app"
          ? "dashboard"
          : p.includes("customers")
            ? "customers"
            : p.includes("quotes")
              ? "quotes"
              : p.includes("jobs")
                ? "jobs"
                : p.includes("settings")
                  ? "settings"
                  : p === "/login"
                    ? "login"
                    : p === "/register"
                      ? "register"
                      : p === "/"
                        ? "landing"
                        : "other";
}
function AsyncButton({ onClick, children, secondary = false }) {
    const [busy, B] = useState(false),
        [error, E] = useState("");
    return (
        <>
            <button
                disabled={busy}
                className={"button " + (secondary ? "secondary" : "")}
                onClick={async () => {
                    B(true);
                    E("");
                    try {
                        await onClick();
                    } catch (e) {
                        E(e.message);
                    } finally {
                        B(false);
                    }
                }}
            >
                {busy ? "Procesando…" : children}
            </button>
            {error && (
                <span role="alert" className="error">
                    {error}
                </span>
            )}
        </>
    );
}
function Form({ onSubmit, children, label = "Guardar", dirtyGuard = true }) {
    const [busy, B] = useState(false),
        [error, E] = useState(""),
        [dirty, D] = useState(false);
    useEffect(() => {
        if (!dirtyGuard || !dirty || busy) return;
        const warn = (e) => {
            e.preventDefault();
            e.returnValue = "";
        };
        window.addEventListener("beforeunload", warn);
        return () => window.removeEventListener("beforeunload", warn);
    }, [dirty, dirtyGuard, busy]);
    return (
        <form
            className="form-stack"
            onChange={() => D(true)}
            aria-busy={busy}
            onSubmit={async (e) => {
                e.preventDefault();
                if (busy) return;
                const data = Object.fromEntries(new FormData(e.currentTarget));
                B(true);
                E("");
                try {
                    await onSubmit(data);
                    D(false);
                } catch (err) {
                    E(
                        err.message ||
                            "No pudimos guardar. Revisá la conexión y reintentá.",
                    );
                } finally {
                    B(false);
                }
            }}
        >
            {children}
            {error && (
                <p role="alert" className="notice">
                    {error} Tus campos se conservan. Revisá antes de reintentar.
                </p>
            )}
            <button className="button" disabled={busy}>
                {busy ? "Procesando…" : label}
            </button>
            {busy && <p role="status">Esperá un momento…</p>}
        </form>
    );
}
function Field({
    name,
    label,
    value = "",
    type = "text",
    required = false,
    options,
    maxLength,
    ...rest
}) {
    return (
        <label>
            {label}
            {required ? " *" : ""}
            {options ? (
                <select
                    name={name}
                    defaultValue={value}
                    required={required}
                    {...rest}
                >
                    {options.map((o) => (
                        <option
                            key={typeof o === "string" ? o : o.value}
                            value={typeof o === "string" ? o : o.value}
                        >
                            {typeof o === "string" ? o : o.label}
                        </option>
                    ))}
                </select>
            ) : type === "textarea" ? (
                <textarea
                    name={name}
                    defaultValue={value}
                    required={required}
                    maxLength={maxLength || 5000}
                    {...rest}
                />
            ) : (
                <input
                    name={name}
                    type={type}
                    defaultValue={value}
                    required={required}
                    maxLength={maxLength || 190}
                    {...rest}
                />
            )}
        </label>
    );
}
function Landing({ demo }) {
    return (
        <>
            <section className="hero">
                <div>
                    <span className="eyebrow">
                        Para negocios que hacen trabajos a medida
                    </span>
                    <h1>
                        Cada trabajo
                        <br />
                        en orden.
                        <br />
                        <em>Cada cobro, claro.</em>
                    </h1>
                    <p className="hero-lead">
                        Tené claro qué aprobaron, qué debés entregar y cuánto
                        falta cobrar.
                    </p>
                    <p className="muted">
                        Clientes, propuestas y seguimiento en un mismo lugar.
                        Beta con datos ficticios.
                    </p>
                    <div className="actions">
                        <AsyncButton onClick={demo}>Probar demo</AsyncButton>
                        <a className="button secondary" href="/register">
                            Crear cuenta de prueba
                        </a>
                        <InstallAction />
                    </div>
                </div>
                <div className="hero-demo">
                    <span className="example-tag">Ejemplo ilustrativo</span>
                    <h2>Un encargo, con contexto</h2>
                    <ol className="journey">
                        {[
                            "Cliente y alcance",
                            "Presupuesto compartido",
                            "Aprobación del cliente",
                            "Trabajo y próxima acción",
                            "Cobro registrado y saldo",
                        ].map((s, i) => (
                            <li key={s}>
                                <span className="journey-dot">{i + 1}</span>
                                <strong>{s}</strong>
                            </li>
                        ))}
                    </ol>
                </div>
            </section>
            <section className="card">
                <h2>Un recorrido simple, sin cargar lo mismo dos veces</h2>
                <p>
                    Publicá una versión del presupuesto. El cliente puede
                    decidir sin crear cuenta. Convertí la aceptación en un
                    trabajo y registrá los cobros manualmente.
                </p>
                <p>
                    No procesa pagos ni emite comprobantes fiscales. Publicar un
                    enlace no significa enviarlo. La aceptación no es una firma
                    digital certificada.
                </p>
                <a href="/beta">Qué podés probar y qué límites tiene la beta</a>
            </section>
            <Pilot />
        </>
    );
}
function Pilot() {
    const [saved, S] = useState(false);
    return (
        <section className="card">
            <h2>¿Lo probamos con tu próximo trabajo?</h2>
            {saved ? (
                <p role="status">
                    Guardamos tu solicitud de piloto. No se envían correos
                    automáticos.
                </p>
            ) : (
                <Form
                    label="Guardar solicitud"
                    onSubmit={async (d) => {
                        const { error } = await cloud().rpc("tc_send_pilot", {
                            name: d.name,
                            email: d.email,
                            message: d.message,
                            website: d.website,
                        });
                        if (error) throw error;
                        S(true);
                    }}
                >
                    <Field
                        name="name"
                        label="Tu nombre"
                        required
                        maxLength={120}
                    />
                    <Field
                        name="email"
                        label="Tu correo"
                        type="email"
                        required
                    />
                    <Field
                        name="message"
                        label="Qué querés probar"
                        type="textarea"
                        required
                        minLength={15}
                        maxLength={2000}
                    />
                    <div hidden>
                        <Field
                            name="website"
                            label="Dejar vacío"
                            tabIndex={-1}
                        />
                    </div>
                </Form>
            )}
        </section>
    );
}

function Auth({ register }) {
    const [sent, S] = useState("");
    return (
        <section
            className="card"
            style={{ maxWidth: 520, margin: "30px auto" }}
        >
            <Brand tagline />
            <h1>
                {register ? "Creá tu cuenta de prueba" : "Todo en su lugar."}
            </h1>
            <Form
                dirtyGuard={false}
                label={register ? "Crear cuenta" : "Ingresar"}
                onSubmit={async (d) => {
                    if (register && d.password !== d.confirm)
                        throw Error("Las contraseñas no coinciden.");
                    const result = register
                        ? await cloud().auth.signUp({
                              email: d.email.trim().toLowerCase(),
                              password: d.password,
                              options: {
                                  emailRedirectTo:
                                      location.origin + "/auth/callback",
                              },
                          })
                        : await cloud().auth.signInWithPassword({
                              email: d.email.trim().toLowerCase(),
                              password: d.password,
                          });
                    if (result.error) throw result.error;
                    if (!result.data.session) {
                        S(
                            "Cuenta creada. Confirmá tu correo antes de ingresar. Si no llega, revisá spam y consultá al operador; no se simula el envío.",
                        );
                        return;
                    }
                    location.href = "/app";
                }}
            >
                <Field
                    name="email"
                    label="Correo"
                    type="email"
                    required
                    autoComplete="email"
                />
                <Field
                    name="password"
                    label="Contraseña"
                    type="password"
                    required
                    minLength={10}
                    autoComplete={
                        register ? "new-password" : "current-password"
                    }
                />
                {register && (
                    <Field
                        name="confirm"
                        label="Repetir contraseña"
                        type="password"
                        required
                        minLength={10}
                        autoComplete="new-password"
                    />
                )}
            </Form>
            {sent && (
                <p role="status" className="notice">
                    {sent}
                </p>
            )}
            <hr />
            <AsyncButton
                secondary
                onClick={async () => {
                    const { error } = await cloud().auth.signInWithOAuth({
                        provider: "google",
                        options: {
                            redirectTo: location.origin + "/auth/callback",
                        },
                    });
                    if (error) throw error;
                }}
            >
                Continuar con Google
            </AsyncButton>
            <p>
                <a href={register ? "/login" : "/register"}>
                    {register ? "Ya tengo cuenta" : "Crear cuenta de prueba"}
                </a>
            </p>
            <p className="fineprint">
                Google requiere configuración externa. Recuperación de
                contraseña aún no disponible. Usá datos ficticios.
            </p>
        </section>
    );
}
function Setup({ business, onSaved }) {
    const d = business?.data || {};
    return (
        <section className="card">
            <h1>
                {business
                    ? "Configuración del negocio"
                    : "Configurá tu negocio"}
            </h1>
            <p>Solo los datos mínimos para preparar tu primera propuesta.</p>
            <Form
                onSubmit={async (values) => {
                    await command(business ? "settings" : "setup", values);
                    await onSaved();
                }}
            >
                <Field
                    name="name"
                    label="Nombre del negocio"
                    required
                    value={d.name}
                    maxLength={120}
                />
                <Field
                    name="industry"
                    label="Rubro"
                    required
                    value={d.industry}
                    maxLength={100}
                />
                <div className="form-grid">
                    <Field
                        name="country"
                        label="País"
                        value={d.country || "AR"}
                        options={[
                            "AR",
                            "UY",
                            "CL",
                            "BO",
                            "BR",
                            "PY",
                            "PE",
                            "CO",
                            "MX",
                            "ES",
                            "US",
                        ]}
                    />
                    <Field
                        name="currency"
                        label="Moneda predeterminada"
                        value={d.currency || "ARS"}
                        options={["ARS", "USD"]}
                    />
                </div>
                <Field
                    name="timezone"
                    label="Zona horaria"
                    required
                    value={d.timezone || TZ}
                />
            </Form>
        </section>
    );
}
function List({
    title,
    rows,
    type,
    name,
    status,
    subtitle,
    states,
    board = false,
}) {
    const [q, Q] = useState(""),
        [state, S] = useState(""),
        [page, P] = useState(1),
        [from, F] = useState(""),
        [to, T] = useState(""),
        [view, V] = useState(false);
    const filtered = rows.filter(
        (r) =>
            (name(r) + " " + (r.data.phone || "") + " " + (r.data.email || ""))
                .toLowerCase()
                .includes(q.toLowerCase()) &&
            (!state || status(r) === state) &&
            (!from || r.created_at.slice(0, 10) >= from) &&
            (!to || r.created_at.slice(0, 10) <= to),
    );
    const display = filtered.slice((page - 1) * 20, page * 20);
    return (
        <>
            <div className="page-heading">
                <h1>{title}</h1>
                <a href={"/app/" + type + "/new"} className="button">
                    {type === "customers"
                        ? "Nuevo cliente"
                        : type === "quotes"
                          ? "Nuevo presupuesto"
                          : "Nuevo trabajo"}
                </a>
            </div>
            <div className="filter-bar no-print">
                <label>
                    Buscar{" "}
                    <input
                        value={q}
                        onChange={(e) => {
                            Q(e.target.value);
                            P(1);
                        }}
                    />
                </label>
                <label>
                    Estado{" "}
                    <select
                        value={state}
                        onChange={(e) => {
                            S(e.target.value);
                            P(1);
                        }}
                    >
                        <option value="">Todos</option>
                        {states.map((s) => (
                            <option key={s}>{s}</option>
                        ))}
                    </select>
                </label>
                <label>
                    Desde{" "}
                    <input
                        type="date"
                        value={from}
                        onChange={(e) => {
                            F(e.target.value);
                            P(1);
                        }}
                    />
                </label>
                <label>
                    Hasta{" "}
                    <input
                        type="date"
                        value={to}
                        onChange={(e) => {
                            T(e.target.value);
                            P(1);
                        }}
                    />
                </label>
                <button
                    className="button secondary"
                    onClick={() => {
                        Q("");
                        S("");
                        F("");
                        T("");
                        P(1);
                    }}
                >
                    Limpiar filtros
                </button>
                {board && (
                    <button
                        className="button secondary"
                        onClick={() => V(!view)}
                    >
                        {view ? "Ver lista" : "Ver tablero"}
                    </button>
                )}
            </div>
            {view ? (
                <div className="board">
                    {states.map((s) => (
                        <section key={s} className="card">
                            <h2>{s}</h2>
                            <RecordLinks
                                rows={display.filter((r) => status(r) === s)}
                                type={type}
                                empty="Sin trabajos"
                            />
                        </section>
                    ))}
                </div>
            ) : (
                <div className="record-list">
                    {display.map((r) => (
                        <a
                            key={r.id}
                            className="record-row"
                            href={"/app/" + type + "/" + r.id}
                        >
                            <span>
                                <strong>{name(r)}</strong>
                                <small>{subtitle(r)}</small>
                            </span>
                            <span className="pill">{status(r)}</span>
                        </a>
                    ))}
                    {!filtered.length && (
                        <div className="empty">
                            <h2>No hay resultados</h2>
                            <p>Limpiá los filtros o creá tu primer registro.</p>
                        </div>
                    )}
                </div>
            )}
            <div className="actions no-print" style={{ marginTop: 20 }}>
                <button
                    className="button secondary"
                    disabled={page === 1}
                    onClick={() => P(page - 1)}
                >
                    Anterior
                </button>
                <span>
                    Página {page} · {filtered.length} registros
                </span>
                <button
                    className="button secondary"
                    disabled={page * 20 >= filtered.length}
                    onClick={() => P(page + 1)}
                >
                    Siguiente
                </button>
            </div>
            {rows.length >= 1000 && (
                <p role="alert">
                    Se alcanzó el límite de 1.000 registros cargados. Consultá
                    al operador antes de continuar.
                </p>
            )}
        </>
    );
}
function RecordLinks({ rows, type, empty }) {
    return (
        <div className="record-list">
            {rows.map((r) => (
                <a
                    className="record-row"
                    key={r.id}
                    href={"/app/" + type + "/" + r.id}
                >
                    {r.data.title || r.data.name}
                    <small>{r.data.status || ""}</small>
                </a>
            ))}
            {!rows.length && <p className="muted">{empty}</p>}
        </div>
    );
}
function CustomerForm({ record, onSave, onArchive, quotes, jobs }) {
    const d = record?.data || {};
    return (
        <>
            <a href="/app/customers">Volver a clientes</a>
            <section className="card">
                <h1>{record ? "Editar cliente" : "Nuevo cliente"}</h1>
                <Form onSubmit={onSave}>
                    <Field
                        name="name"
                        label="Nombre"
                        required
                        value={d.name}
                        maxLength={120}
                    />
                    <Field
                        name="phone"
                        label="Teléfono internacional (opcional)"
                        value={d.phone}
                        placeholder="+5491112345678"
                    />
                    <Field
                        name="email"
                        label="Correo (opcional)"
                        type="email"
                        value={d.email}
                    />
                    <Field
                        name="address"
                        label="Dirección (opcional)"
                        value={d.address}
                        maxLength={500}
                    />
                    <Field
                        name="notes"
                        label="Notas internas (opcional)"
                        type="textarea"
                        value={d.notes}
                        maxLength={2000}
                    />
                </Form>
                {record && !d.archived_at && (
                    <AsyncButton secondary onClick={onArchive}>
                        Archivar sin borrar historial
                    </AsyncButton>
                )}
            </section>
            {record && (
                <section className="card">
                    <h2>Presupuestos</h2>
                    <RecordLinks
                        rows={quotes}
                        type="quotes"
                        empty="Sin presupuestos"
                    />
                    <h2>Trabajos</h2>
                    <RecordLinks rows={jobs} type="jobs" empty="Sin trabajos" />
                </section>
            )}
        </>
    );
}
function QuoteForm({ record, business, customers, onSave }) {
    const d = record?.data || {},
        [items, I] = useState(
            d.items || [
                {
                    description: "",
                    quantity: "1",
                    unit_price: "",
                    category: "",
                },
            ],
        ),
        [calculation, C] = useState(null);
    const active = customers.filter((c) => !c.data.archived_at);
    if (!active.length)
        return (
            <section className="empty">
                <h1>Primero necesitás un cliente</h1>
                <a className="button" href="/app/customers/new">
                    Crear cliente
                </a>
            </section>
        );
    return (
        <>
            <a href="/app/quotes">Volver a presupuestos</a>
            <section className="card">
                <h1>{record ? "Editar borrador" : "Nuevo presupuesto"}</h1>
                <p>
                    Guardado manual. Guardar no publica ni envía la propuesta.
                </p>
                <Form
                    label="Guardar y revisar totales"
                    onSubmit={async (values) => {
                        const data = { ...values, items };
                        previewQuote(data);
                        await onSave(data);
                    }}
                >
                    <Field
                        name="customer_id"
                        label="Cliente"
                        required
                        value={d.customer_id || active[0].id}
                        options={active.map((c) => ({
                            value: c.id,
                            label: c.data.name,
                        }))}
                    />
                    <Field
                        name="title"
                        label="Título"
                        required
                        value={d.title}
                        maxLength={160}
                    />
                    <Field
                        name="scope"
                        label="Qué incluye el trabajo"
                        type="textarea"
                        required
                        value={d.scope}
                    />
                    <Field
                        name="exclusions"
                        label="Qué no incluye (opcional)"
                        type="textarea"
                        value={d.exclusions}
                        maxLength={3000}
                    />
                    <div className="form-grid">
                        <Field
                            name="issued_on"
                            label="Fecha"
                            type="date"
                            required
                            value={d.issued_on || today(business.data.timezone)}
                        />
                        <Field
                            name="expires_on"
                            label="Válido hasta"
                            type="date"
                            required
                            value={d.expires_on || later()}
                        />
                    </div>
                    <h2>Ítems</h2>
                    <p>
                        Usá punto decimal: cantidad hasta 3 decimales y precio
                        hasta 2.
                    </p>
                    {items.map((item, i) => (
                        <fieldset className="quote-item" key={i}>
                            <legend>Ítem {i + 1}</legend>
                            {[
                                ["description", "Descripción"],
                                ["quantity", "Cantidad"],
                                ["unit_price", "Precio unitario"],
                            ].map(([k, l]) => (
                                <label key={k}>
                                    {l} *
                                    <input
                                        required
                                        maxLength={
                                            k === "description" ? 500 : 14
                                        }
                                        inputMode={
                                            k === "description"
                                                ? "text"
                                                : "decimal"
                                        }
                                        value={item[k]}
                                        onChange={(e) =>
                                            I(
                                                items.map((v, j) =>
                                                    i === j
                                                        ? {
                                                              ...v,
                                                              [k]: e.target
                                                                  .value,
                                                          }
                                                        : v,
                                                ),
                                            )
                                        }
                                    />
                                </label>
                            ))}
                            <label>
                                Categoría
                                <select
                                    value={item.category || ""}
                                    onChange={(e) =>
                                        I(
                                            items.map((v, j) =>
                                                i === j
                                                    ? {
                                                          ...v,
                                                          category:
                                                              e.target.value,
                                                      }
                                                    : v,
                                            ),
                                        )
                                    }
                                >
                                    <option value="">Sin categoría</option>
                                    <option>material</option>
                                    <option>mano de obra</option>
                                </select>
                            </label>
                            <button
                                type="button"
                                className="text-button"
                                disabled={items.length === 1}
                                onClick={() =>
                                    I(items.filter((_, j) => i !== j))
                                }
                            >
                                Quitar ítem
                            </button>
                        </fieldset>
                    ))}
                    <button
                        type="button"
                        className="button secondary"
                        disabled={items.length >= 30}
                        onClick={() =>
                            I([
                                ...items,
                                {
                                    description: "",
                                    quantity: "1",
                                    unit_price: "",
                                    category: "",
                                },
                            ])
                        }
                    >
                        Agregar ítem
                    </button>
                    <div className="form-grid">
                        <Field
                            name="currency"
                            label="Moneda"
                            options={["ARS", "USD"]}
                            value={d.currency || business.data.currency}
                        />
                        <Field
                            name="discount"
                            label="Descuento fijo"
                            value={d.discount || "0"}
                            inputMode="decimal"
                        />
                        <Field
                            name="tax_rate"
                            label="Impuesto %"
                            value={d.tax_rate || "0"}
                            inputMode="decimal"
                        />
                        <Field
                            name="advance"
                            label="Anticipo solicitado"
                            value={d.advance || "0"}
                            inputMode="decimal"
                        />
                    </div>
                    <Field
                        name="conditions"
                        label="Condiciones (opcional)"
                        type="textarea"
                        value={d.conditions}
                        maxLength={3000}
                    />
                    <p className="fineprint">
                        Redondeo mitad hacia arriba por ítem; descuento fijo
                        antes del impuesto. El total definitivo se calcula en
                        PostgreSQL, no se confía en el navegador.
                    </p>
                </Form>
            </section>
        </>
    );
}
function QuoteDetail({ record, business, customerName, versions, action }) {
    const [link, L] = useState("");
    if (!record) return <NotFound />;
    const d = record.data;
    return (
        <>
            <div className="page-heading no-print">
                <div>
                    <span className="eyebrow">
                        Presupuesto #{d.number} · {d.status}
                    </span>
                    <h1>{d.title}</h1>
                </div>
                <div className="actions">
                    <a
                        className="button secondary"
                        href={"/app/quotes/" + record.id + "/edit"}
                    >
                        Editar borrador
                    </a>
                    <AsyncButton
                        secondary
                        onClick={async () => {
                            const r = await action("duplicate", {
                                id: record.id,
                            });
                            location.href = "/app/quotes/" + r.id + "/edit";
                        }}
                    >
                        Duplicar
                    </AsyncButton>
                </div>
            </div>
            <Document
                snapshot={{
                    ...d,
                    business: business.data,
                    customer: { name: customerName(d.customer_id) },
                }}
            />
            <section className="card no-print">
                <h2>Compartir una versión</h2>
                <p>
                    Publicar sustituye los enlaces anteriores. Guardá el enlace
                    ahora; no se puede recuperar. Publicar no significa enviar.
                </p>
                {d.status !== "aceptado" ? (
                    <AsyncButton
                        onClick={async () => {
                            const r = await action(
                                "publish",
                                { id: record.id },
                                "Versión publicada.",
                            );
                            L(location.origin + "/p/#" + r.token);
                        }}
                    >
                        Publicar versión
                    </AsyncButton>
                ) : (
                    <AsyncButton
                        onClick={async () => {
                            const r = await action("convert", {
                                id: record.id,
                            });
                            location.href = "/app/jobs/" + r.id;
                        }}
                    >
                        Abrir / crear trabajo
                    </AsyncButton>
                )}
                {link && (
                    <div className="form-stack">
                        <label>
                            Copiá y guardá el enlace
                            <input value={link} readOnly />
                        </label>
                        <a
                            href={link}
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            Abrir vista pública
                        </a>
                        <a
                            href={
                                "https://wa.me/?text=" +
                                encodeURIComponent(
                                    "Te comparto el presupuesto: " + link,
                                )
                            }
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            Preparar mensaje de WhatsApp
                        </a>
                        <small>
                            El usuario elige el contacto y decide enviar. No
                            registramos entrega.
                        </small>
                    </div>
                )}
            </section>
            <section className="card no-print">
                <h2>Versiones publicadas</h2>
                {versions.map((v) => (
                    <div className="record-row" key={v.id}>
                        <span>
                            Versión {v.data.version} ·{" "}
                            {money(v.data.total_minor, v.data.currency)}
                        </span>
                        <AsyncButton
                            secondary
                            onClick={() =>
                                action(
                                    "revoke",
                                    { id: v.id },
                                    "Se revocó el enlace pendiente. Las decisiones se conservan.",
                                )
                            }
                        >
                            Revocar enlace pendiente
                        </AsyncButton>
                    </div>
                ))}
                {!versions.length && <p>Sin versiones publicadas.</p>}
            </section>
        </>
    );
}
function Document({ snapshot: d }) {
    return (
        <section className="quote-document">
            <div className="print-logo">
                <Brand />
            </div>
            <span className="eyebrow">{d.business?.name}</span>
            <div className="page-heading">
                <div>
                    <h2>Presupuesto #{d.number}</h2>
                    <p>{d.customer?.name}</p>
                </div>
                <small>
                    Fecha: {d.issued_on}
                    <br />
                    Válido hasta: {d.expires_on}
                </small>
            </div>
            <h2>{d.title}</h2>
            <h3>Alcance</h3>
            <p style={{ whiteSpace: "pre-wrap" }}>{d.scope}</p>
            {d.exclusions && (
                <>
                    <h3>No incluye</h3>
                    <p style={{ whiteSpace: "pre-wrap" }}>{d.exclusions}</p>
                </>
            )}
            {d.items?.map((i, k) => (
                <article key={k}>
                    <strong>{i.description}</strong>
                    <div className="actions">
                        <span>
                            {i.quantity} ×{" "}
                            {money(decimal(i.unit_price), d.currency)}
                        </span>
                        <strong>{money(i.total_minor, d.currency)}</strong>
                    </div>
                    {i.category && <small>{i.category}</small>}
                </article>
            ))}
            <div style={{ marginTop: 20 }}>
                <p>Subtotal: {money(d.subtotal_minor, d.currency)}</p>
                <p>
                    Descuento: {money(d.discount_minor, d.currency)} · Impuesto:{" "}
                    {money(d.tax_minor, d.currency)}
                </p>
                <h2>Total: {money(d.total_minor, d.currency)}</h2>
                <p>Anticipo solicitado: {money(d.advance_minor, d.currency)}</p>
            </div>
            {d.conditions && (
                <>
                    <h3>Condiciones</h3>
                    <p style={{ whiteSpace: "pre-wrap" }}>{d.conditions}</p>
                </>
            )}
            <p className="fineprint">
                Propuesta comercial. No es comprobante fiscal ni firma digital
                certificada.
            </p>
            <button
                className="button secondary no-print"
                onClick={() => window.print()}
            >
                Imprimir / guardar PDF
            </button>
        </section>
    );
}
function PublicQuote({ token }) {
    const [result, R] = useState(null),
        [error, E] = useState(""),
        [notice, N] = useState("");
    async function fetchQuote(decision = null) {
        const { data, error } = await cloud().rpc("tc_public_quote", {
            token,
            p_decision: decision,
        });
        if (error) throw Error(error.message);
        R(data);
        return data;
    }
    useEffect(() => {
        fetchQuote().catch((e) => E(e.message));
    }, [token]);
    if (error)
        return (
            <div role="alert" className="empty">
                <h1>No pudimos abrir la propuesta</h1>
                <p>{error}</p>
                <p>
                    Pedí al negocio un enlace vigente o reintentá si se cortó la
                    conexión.
                </p>
                <AsyncButton
                    secondary
                    onClick={async () => {
                        await fetchQuote();
                        E("");
                    }}
                >
                    Reintentar
                </AsyncButton>
            </div>
        );
    if (!result) return <p role="status">Cargando propuesta…</p>;
    return (
        <>
            <Document snapshot={result.snapshot} />
            <section className="card no-print">
                <h2>Estado: {result.status}</h2>
                {notice && (
                    <p role="status" className="notice">
                        {notice}
                    </p>
                )}
                {result.status === "pendiente" ? (
                    <Form
                        label="Registrar mi decisión"
                        onSubmit={async (d) => {
                            await fetchQuote({
                                ...d,
                                confirmed: d.confirmed === "on",
                            });
                            N("Registramos tu decisión para esta versión.");
                        }}
                    >
                        <Field
                            name="name"
                            label="Tu nombre"
                            required
                            maxLength={120}
                        />
                        <Field
                            name="decision"
                            label="Decisión"
                            options={[
                                {
                                    value: "aceptado",
                                    label: "Aceptar presupuesto",
                                },
                                {
                                    value: "rechazado",
                                    label: "Rechazar presupuesto",
                                },
                                {
                                    value: "modificacion",
                                    label: "Pedir modificación",
                                },
                            ]}
                        />
                        <Field
                            name="message"
                            label="Comentario (opcional)"
                            type="textarea"
                            maxLength={2000}
                        />
                        <label className="checkbox-label">
                            <input type="checkbox" name="confirmed" required />
                            Confirmo que revisé esta versión y quiero registrar
                            mi decisión.
                        </label>
                    </Form>
                ) : (
                    <p>
                        Esta versión no admite otra decisión. Contactá al
                        negocio si necesitás un cambio.
                    </p>
                )}
            </section>
        </>
    );
}
function JobForm({ record, customers, currency, onSave }) {
    const d = record?.data || {};
    if (!customers.length)
        return (
            <section className="empty">
                <h1>Necesitás un cliente</h1>
                <a href="/app/customers/new">Crear cliente</a>
            </section>
        );
    return (
        <section className="card">
            <h1>{record ? "Actualizar trabajo" : "Nuevo trabajo"}</h1>
            <Form
                label="Guardar trabajo"
                onSubmit={(values) => {
                    const { reason, confirm_change, ...data } = values;
                    return onSave(data, {
                        reason,
                        confirm_change: confirm_change === "on",
                    });
                }}
            >
                <Field
                    name="customer_id"
                    label="Cliente"
                    value={d.customer_id || customers[0].id}
                    options={customers
                        .filter(
                            (c) =>
                                !c.data.archived_at || c.id === d.customer_id,
                        )
                        .map((c) => ({ value: c.id, label: c.data.name }))}
                />
                <Field
                    name="title"
                    label="Título"
                    value={d.title}
                    required
                    maxLength={160}
                />
                <Field
                    name="scope"
                    label="Alcance"
                    type="textarea"
                    value={d.scope}
                    required
                />
                <div className="form-grid">
                    <Field
                        name="amount"
                        label="Importe acordado"
                        value={input(d.amount_minor)}
                        required
                        inputMode="decimal"
                    />
                    <Field
                        name="currency"
                        label="Moneda"
                        value={d.currency || currency}
                        options={["ARS", "USD"]}
                    />
                    <Field
                        name="status"
                        label="Estado"
                        value={d.status || "pendiente"}
                        options={statuses}
                    />
                </div>
                <div className="form-grid">
                    <Field
                        name="starts_on"
                        label="Inicio"
                        type="date"
                        value={d.starts_on}
                    />
                    <Field
                        name="due_on"
                        label="Entrega"
                        type="date"
                        value={d.due_on}
                    />
                </div>
                <Field
                    name="next_action"
                    label="Próxima acción"
                    value={d.next_action}
                    maxLength={1000}
                />
                <Field
                    name="action_on"
                    label="Fecha de próxima acción"
                    type="date"
                    value={d.action_on}
                />
                {record && (
                    <>
                        <Field
                            name="reason"
                            label="Motivo si cambia el importe"
                            maxLength={1000}
                        />
                        <label className="checkbox-label">
                            <input type="checkbox" name="confirm_change" />
                            Confirmo el cambio de importe acordado.
                        </label>
                        <p>
                            Cliente y moneda se conservan. Un cambio de importe
                            requiere motivo y no puede ser menor a lo cobrado.
                        </p>
                    </>
                )}
            </Form>
        </section>
    );
}
function JobDetail({
    record,
    customerName,
    payments,
    balance,
    events,
    action,
    timezone,
}) {
    const [key, K] = useState(() => crypto.randomUUID());
    if (!record) return <NotFound />;
    const d = record.data;
    return (
        <>
            <div className="page-heading">
                <div>
                    <span className="eyebrow">{d.status}</span>
                    <h1>{d.title}</h1>
                    <p>{customerName(d.customer_id)}</p>
                </div>
                <a
                    className="button secondary"
                    href={"/app/jobs/" + record.id + "/edit"}
                >
                    Actualizar trabajo
                </a>
            </div>
            <div className="detail-grid">
                <section className="card">
                    <h2>El acuerdo</h2>
                    <p style={{ whiteSpace: "pre-wrap" }}>{d.scope}</p>
                    <p>Importe: {money(d.amount_minor, d.currency)}</p>
                    <h2>Saldo: {money(balance, d.currency)}</h2>
                    <p>Entrega: {d.due_on || "Sin fecha"}</p>
                    <p>
                        Próxima acción: {d.next_action || "Sin definir"} ·{" "}
                        {d.action_on || ""}
                    </p>
                    <small>Terminar el trabajo no lo marca como cobrado.</small>
                </section>
                <section className="card">
                    <h2>Registrar cobro</h2>
                    <p>
                        Registro interno de cobros. No es un comprobante fiscal.
                        No mueve dinero.
                    </p>
                    <Form
                        label="Registrar cobro"
                        onSubmit={async (values) => {
                            await action(
                                "pay",
                                {
                                    id: record.id,
                                    data: {
                                        ...values,
                                        currency: d.currency,
                                        key,
                                    },
                                },
                                "Cobro registrado.",
                            );
                            K(crypto.randomUUID());
                        }}
                    >
                        <Field
                            name="amount"
                            label={"Importe en " + d.currency}
                            required
                            inputMode="decimal"
                        />
                        <Field
                            name="paid_on"
                            label="Fecha del cobro"
                            type="date"
                            value={today(timezone)}
                            required
                        />
                        <Field
                            name="method"
                            label="Medio"
                            options={[
                                "transferencia",
                                "efectivo",
                                "tarjeta",
                                "otro",
                            ]}
                        />
                        <Field
                            name="reference"
                            label="Referencia (opcional)"
                            maxLength={190}
                        />
                    </Form>
                    <small>
                        Si no recibís confirmación, revisá los movimientos antes
                        de reintentar o recargar.
                    </small>
                </section>
            </div>
            <section className="card">
                <h2>Movimientos</h2>
                {payments.map((p) => (
                    <article className="record-row" key={p.id}>
                        <div>
                            <strong>
                                {money(p.data.amount_minor, p.data.currency)}
                            </strong>
                            <p>
                                {p.data.paid_on} · {p.data.method}
                            </p>
                            {p.data.voided_at ? (
                                <p>Anulado: {p.data.void_reason}</p>
                            ) : (
                                <Form
                                    label="Anular con motivo"
                                    onSubmit={(values) =>
                                        action(
                                            "void",
                                            { id: p.id, reason: values.reason },
                                            "Movimiento anulado con historial.",
                                        )
                                    }
                                >
                                    <Field
                                        name="reason"
                                        label="Motivo de anulación"
                                        required
                                        minLength={3}
                                        maxLength={1000}
                                    />
                                </Form>
                            )}
                        </div>
                    </article>
                ))}
                {!payments.length && <p>Aún no hay cobros registrados.</p>}
            </section>
            <section className="card">
                <h2>Historial</h2>
                {events.map((e) => (
                    <p key={e.id}>
                        {e.data.name} ·{" "}
                        {new Date(e.created_at).toLocaleString("es-AR")}
                    </p>
                ))}
            </section>
        </>
    );
}
function Feedback() {
    const [saved, S] = useState(false);
    return (
        <section className="card" style={{ maxWidth: 650, margin: "auto" }}>
            <h1>Enviar feedback</h1>
            <p>
                Contanos qué intentaste hacer y qué ocurrió. No incluyas claves,
                enlaces privados ni datos personales. Sin adjuntos.
            </p>
            {saved ? (
                <div role="status" className="notice">
                    Guardamos tu feedback. No se envían correos automáticos.{" "}
                    <button className="text-button" onClick={() => S(false)}>
                        Enviar otro comentario
                    </button>
                </div>
            ) : (
                <Form
                    label="Guardar feedback"
                    onSubmit={async (d) => {
                        const { error } = await cloud().rpc(
                            "tc_send_feedback",
                            {
                                screen: d.screen,
                                description: d.description,
                                contact: d.contact,
                                website: d.website,
                            },
                        );
                        if (error) throw error;
                        S(true);
                    }}
                >
                    <Field
                        name="screen"
                        label="Pantalla"
                        value={
                            typeof window === "undefined"
                                ? "other"
                                : new URLSearchParams(location.search).get(
                                      "screen",
                                  ) || "other"
                        }
                        options={[
                            "landing",
                            "login",
                            "register",
                            "dashboard",
                            "customers",
                            "quotes",
                            "jobs",
                            "settings",
                            "public-quote",
                            "other",
                        ]}
                    />
                    <Field
                        name="description"
                        label="Descripción"
                        type="textarea"
                        required
                        minLength={10}
                        maxLength={3000}
                    />
                    <Field name="contact" label="Contacto (opcional)" />
                    <div hidden>
                        <Field
                            name="website"
                            label="Dejar vacío"
                            tabIndex={-1}
                        />
                    </div>
                    <small>Versión {RELEASE}</small>
                </Form>
            )}
        </section>
    );
}
function BrandPage() {
    return (
        <>
            <h1>Orden que se ve.</h1>
            <p>
                Identidad provisional de TrabajoClaro. Disponibilidad del nombre
                y dominios pendiente de verificar.
            </p>
            <div className="brand-gallery-grid">
                {[
                    "horizontal",
                    "horizontal-dark",
                    "horizontal-mono",
                    "tagline",
                    "tagline-dark",
                    "tagline-mono",
                ].map((v) => (
                    <section
                        key={v}
                        className={
                            "brand-sample " +
                            (v.includes("dark") ? "brand-sample--dark" : "")
                        }
                    >
                        <img
                            style={{ width: "100%", maxWidth: 316 }}
                            src={"/brand/logo-" + v + ".svg"}
                            alt={"TrabajoClaro · " + v}
                        />
                    </section>
                ))}
            </div>
            <section className="card">
                <h2>Símbolo, favicon e icono de app</h2>
                <div className="actions">
                    {[16, 32, 64, 192].map((n) => (
                        <img
                            key={n}
                            src={
                                n === 16
                                    ? "/favicon-16x16.png"
                                    : n === 32
                                      ? "/favicon-32x32.png"
                                      : "/icon-192.png"
                            }
                            width={n}
                            height={n}
                            alt={"Símbolo a " + n + " píxeles"}
                        />
                    ))}
                </div>
                <p>
                    Manrope, licencia SIL OFL 1.1.{" "}
                    <a href="/fonts/manrope/OFL.txt">Leer licencia</a>
                </p>
            </section>
        </>
    );
}
function NotFound() {
    return (
        <section className="empty">
            <h1>No encontramos ese registro</h1>
            <p>Puede pertenecer a otro negocio o no estar cargado.</p>
            <a href="/app">Volver al panel</a>
        </section>
    );
}
function download(kind, rows) {
    const headers = ["id", "fecha", "datos"];
    const content = csv([
        headers,
        ...rows.map((r) => [r.id, r.created_at, JSON.stringify(r.data)]),
    ]);
    const url = URL.createObjectURL(
        new Blob([content], { type: "text/csv;charset=utf-8" }),
    );
    const a = document.createElement("a");
    a.href = url;
    a.download = kind + ".csv";
    a.click();
    setTimeout(() => URL.revokeObjectURL(url), 1000);
}
