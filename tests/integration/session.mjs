import { createHmac } from "node:crypto";
export const owner = "aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa";
export function jwt(role, sub) {
    const enc = (v) => Buffer.from(JSON.stringify(v)).toString("base64url");
    const data =
        enc({ alg: "HS256", typ: "JWT" }) +
        "." +
        enc({
            role,
            sub,
            exp: Math.floor(Date.now() / 1000) + 3600,
            iat: Math.floor(Date.now() / 1000),
            aud: "authenticated",
            is_anonymous: false,
        });
    return (
        data +
        "." +
        createHmac("sha256", "trabajoclaro-test-only-secret-at-least-32-bytes")
            .update(data)
            .digest("base64url")
    );
}
export function session() {
    return {
        access_token: jwt("authenticated", owner),
        refresh_token: "test-only-no-auth-provider",
        token_type: "bearer",
        expires_in: 3600,
        expires_at: Math.floor(Date.now() / 1000) + 3600,
        user: {
            id: owner,
            aud: "authenticated",
            role: "authenticated",
            email: "owner@example.test",
            app_metadata: { provider: "email" },
            user_metadata: {},
            created_at: new Date().toISOString(),
        },
    };
}
