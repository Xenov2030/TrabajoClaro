import { test } from "node:test";
import assert from "node:assert/strict";
import { decimal, quote, money, csv } from "../../src/lib/money.mjs";
const base = {
    items: [{ description: "A", quantity: "1.005", unit_price: "1.00" }],
    discount: "0",
    tax_rate: "0",
    advance: "0",
};
test("redondea cada ítem mitad arriba sin float", () => {
    assert.equal(quote(base).total_minor, 101);
    assert.equal(decimal("0.10"), 10n);
});
test("descuento antes de impuesto", () => {
    assert.equal(
        quote({
            ...base,
            items: [{ quantity: "2", unit_price: "10.00" }],
            discount: "1",
            tax_rate: "21",
        }).total_minor,
        2299,
    );
});
test("rechaza importes y cantidades inválidas", () => {
    for (const v of ["-1", "1,2", "1e2", "0.001", "NaN"])
        assert.throws(() => decimal(v));
    assert.throws(() =>
        quote({ ...base, items: [{ quantity: "0", unit_price: "1" }] }),
    );
    assert.throws(() => quote({ ...base, discount: "99" }));
    assert.throws(() => quote({ ...base, tax_rate: "101" }));
    assert.throws(() => quote({ ...base, advance: "99" }));
});
test("límite de documento", () =>
    assert.throws(() =>
        quote({
            ...base,
            items: [{ quantity: "999999999", unit_price: "999999999" }],
        }),
    ));
test("formato y CSV seguro", () => {
    assert.equal(money(150050), "ARS 1.500,50");
    assert.match(csv([["=1+1", "+formula", 'a"b']]), /"'=1\+1"/);
    assert.match(csv([['a"b']]), /a""b/);
});
