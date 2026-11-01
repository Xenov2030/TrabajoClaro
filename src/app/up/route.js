export function GET() {
    return Response.json(
        { status: "ok", release: "0.2.0-beta.1" },
        { headers: { "Cache-Control": "no-store" } },
    );
}
