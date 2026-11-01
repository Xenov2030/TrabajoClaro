// Local harness: genuine PostgREST/PostgreSQL, pre-issued test JWT; no hosted Auth/Google simulation.
import http from "node:http";
import { spawn } from "node:child_process";
import { jwt } from "../tests/integration/session.mjs";
const proxy = http.createServer((req, res) => {
    if (!req.url.startsWith("/rest/v1/")) {
        res.writeHead(501);
        res.end("Hosted Auth is not part of this database harness");
        return;
    }
    res.setHeader("Access-Control-Allow-Origin", "http://127.0.0.1:3001");
    res.setHeader(
        "Access-Control-Allow-Headers",
        req.headers["access-control-request-headers"] ||
            "authorization,apikey,content-type",
    );
    res.setHeader("Access-Control-Allow-Methods", "GET,POST,OPTIONS");
    if (req.method === "OPTIONS") {
        res.writeHead(204);
        res.end();
        return;
    }
    const remote = http.request(
        {
            hostname: "127.0.0.1",
            port: 54320,
            path: req.url.replace("/rest/v1", ""),
            method: req.method,
            headers: { ...req.headers, host: "127.0.0.1:54320" },
        },
        (reply) => {
            res.writeHead(reply.statusCode, reply.headers);
            reply.pipe(res);
        },
    );
    remote.on("error", () => {
        res.writeHead(502);
        res.end();
    });
    req.pipe(remote);
});
proxy.listen(54321, "127.0.0.1");
const next = spawn(
    "node",
    [
        "node_modules/next/dist/bin/next",
        "dev",
        "--port",
        "3001",
        "--hostname",
        "127.0.0.1",
    ],
    {
        stdio: "inherit",
        env: {
            ...process.env,
            NEXT_TELEMETRY_DISABLED: "1",
            NEXT_PUBLIC_SUPABASE_URL: "http://127.0.0.1:54321",
            NEXT_PUBLIC_SUPABASE_PUBLISHABLE_KEY: jwt("anon"),
        },
    },
);
function stop() {
    next.kill();
    proxy.close();
}
process.on("SIGTERM", stop);
process.on("SIGINT", stop);
next.on("exit", () => proxy.close());
