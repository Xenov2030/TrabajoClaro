export function Brand({ tagline = false }) {
    return (
        <span className="brand-lockup">
            <picture>
                <source
                    media="print"
                    srcSet="/brand/logo-horizontal-mono.svg"
                />
                <img
                    className="brand-logo-image"
                    src="/brand/logo-horizontal.svg"
                    width="316"
                    height="64"
                    alt="TrabajoClaro"
                />
            </picture>
            {tagline && (
                <span className="brand-tagline">
                    Cada trabajo en orden. Cada cobro, claro.
                </span>
            )}
        </span>
    );
}
export function Author() {
    return (
        <a
            className="author-signature"
            href="https://nicolasaguirrescode.com/"
            target="_blank"
            rel="noopener noreferrer"
        >
            DESARROLLADO POR NICOLAS AGUIRRES
        </a>
    );
}
