import { createClient } from "@supabase/supabase-js";
let client;
export function cloudConfig() {
    const url = process.env.NEXT_PUBLIC_SUPABASE_URL || "";
    const key = process.env.NEXT_PUBLIC_SUPABASE_PUBLISHABLE_KEY || "";
    if (!url && !key) return { ready: false };
    if (
        (!/^https:\/\/[a-z0-9-]+\.supabase\.co$/.test(url) &&
            !(
                process.env.NODE_ENV === "development" &&
                /^http:\/\/127\.0\.0\.1:54321$/.test(url)
            )) ||
        !key
    )
        throw Error("Revisá la URL y la clave pública de Supabase en Vercel.");
    if (key.startsWith("sb_secret_"))
        throw Error("Nunca uses una clave secreta en variables públicas.");
    if (key.split(".").length === 3) {
        try {
            const role = JSON.parse(
                atob(
                    key.split(".")[1].replaceAll("-", "+").replaceAll("_", "/"),
                ),
            ).role;
            if (role !== "anon") throw Error();
        } catch {
            throw Error(
                "La clave pública debe ser publishable o anon, nunca service_role.",
            );
        }
    }
    return { ready: true, url, key };
}
export function cloud() {
    const c = cloudConfig();
    if (!c.ready)
        throw Error(
            "Falta conectar Supabase. Consultá la guía de despliegue; no se guardaron cambios.",
        );
    return (client ||= createClient(c.url, c.key, {
        auth: {
            flowType: "pkce",
            detectSessionInUrl: true,
            persistSession: true,
            autoRefreshToken: true,
        },
    }));
}
export async function command(action, payload = {}) {
    const { data, error } = await cloud().rpc("tc_command", {
        action,
        payload,
    });
    if (error) throw Error(error.message);
    return data;
}
