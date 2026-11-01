export function decimal(value, places = 2) {
    const s = String(value);
    if (!new RegExp(`^\\d{1,9}(?:\\.\\d{1,${places}})?$`).test(s))
        throw Error("Usá punto decimal y un importe válido.");
    const [whole, part = ""] = s.split(".");
    return (
        BigInt(whole) * 10n ** BigInt(places) + BigInt(part.padEnd(places, "0"))
    );
}
export function quote(d) {
    let subtotal = 0n;
    const items = d.items.map((i) => {
        const q = decimal(i.quantity, 3);
        if (q <= 0n) throw Error("Cantidad inválida.");
        const line = (q * decimal(i.unit_price) + 500n) / 1000n;
        subtotal += line;
        return { ...i, total_minor: Number(line) };
    });
    const discount = decimal(d.discount || "0"),
        rate = decimal(d.tax_rate || "0");
    if (discount > subtotal || rate > 10000n)
        throw Error("Revisá descuento e impuesto.");
    const tax = ((subtotal - discount) * rate + 5000n) / 10000n,
        total = subtotal - discount + tax,
        advance = decimal(d.advance || "0");
    if (total > 99999999999n || advance > total)
        throw Error("Revisá total y anticipo.");
    return {
        ...d,
        items,
        subtotal_minor: Number(subtotal),
        discount_minor: Number(discount),
        tax_minor: Number(tax),
        total_minor: Number(total),
        advance_minor: Number(advance),
    };
}
export function money(n, c = "ARS") {
    const s = BigInt(n || 0)
        .toString()
        .padStart(3, "0");
    return `${c} ${s.slice(0, -2).replace(/\B(?=(\d{3})+(?!\d))/g, ".")},${s.slice(-2)}`;
}
export function input(n) {
    const s = BigInt(n || 0)
        .toString()
        .padStart(3, "0");
    return `${s.slice(0, -2)}.${s.slice(-2)}`;
}
export function csv(rows) {
    return (
        "\uFEFF" +
        rows
            .map((row) =>
                row
                    .map(
                        (v) =>
                            '"' +
                            String(v ?? "")
                                .replace(/^[=+@\-\t\r]/, "'$&")
                                .replaceAll('"', '""') +
                            '"',
                    )
                    .join(","),
            )
            .join("\r\n")
    );
}
