import "./globals.css";
import { InstallProvider } from "../components/install";
export const metadata = {
    title: { default: "TrabajoClaro · Beta", template: "%s · TrabajoClaro" },
    description: "Cada trabajo en orden. Cada cobro, claro.",
    applicationName: "TrabajoClaro",
    appleWebApp: {
        capable: true,
        title: "TrabajoClaro",
        statusBarStyle: "default",
    },
    manifest: "/site.webmanifest",
    icons: {
        icon: [
            { url: "/favicon.svg", type: "image/svg+xml" },
            { url: "/favicon.ico" },
            { url: "/favicon-32x32.png", sizes: "32x32" },
        ],
        apple: "/apple-touch-icon.png",
    },
};
export const viewport = {
    width: "device-width",
    initialScale: 1,
    themeColor: "#16634f",
};
export default function Layout({ children }) {
    return (
        <html lang="es-AR">
            <body>
                <InstallProvider>{children}</InstallProvider>
            </body>
        </html>
    );
}
