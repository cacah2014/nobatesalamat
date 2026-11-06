import type { Metadata, Viewport } from "next";
import type { ReactNode } from "react";
import "./globals.css";

export const metadata: Metadata = {
  metadataBase: new URL("https://nobatesalamat.ir"),
  title: {
    default: "نوبت سلامت | نوبت‌دهی آنلاین پزشکان در سراسر ایران",
    template: "%s | نوبت سلامت",
  },
  description: "نوبت سلامت؛ جست‌وجوی پزشک بر اساس تخصص و شهر، مشاهده نظرات واقعی بیماران و دریافت نوبت حضوری یا مشاوره آنلاین در چند دقیقه، بدون رمز عبور.",
  keywords: [
    "نوبت دهی آنلاین",
    "نوبت پزشک",
    "رزرو نوبت دکتر",
    "مشاوره پزشکی آنلاین",
    "پزشک متخصص",
    "نوبت سلامت",
    "nobatesalamat",
  ],
  applicationName: "نوبت سلامت",
  authors: [{ name: "Nobat Salamat", url: "https://nobatesalamat.ir" }],
  creator: "Nobat Salamat",
  publisher: "Nobat Salamat",
  category: "health",
  formatDetection: { telephone: true },
  alternates: {
    canonical: "/",
    languages: { "fa-IR": "/" },
  },
  openGraph: {
    locale: "fa_IR",
    type: "website",
    siteName: "نوبت سلامت",
    title: "نوبت سلامت | پزشک مناسب، نزدیک‌تر از همیشه",
    description: "از میان پزشکان معتبر جست‌وجو کنید، نظرات بیماران را بخوانید و نوبت خود را در چند دقیقه قطعی کنید.",
    url: "https://nobatesalamat.ir",
    images: [{ url: "/og-image.jpg", width: 1200, height: 630, alt: "نوبت سلامت — نوبت‌دهی آنلاین پزشکان" }],
  },
  twitter: {
    card: "summary_large_image",
    title: "نوبت سلامت | نوبت‌دهی آنلاین پزشکان",
    description: "انتخاب آگاهانه پزشک و دریافت نوبت در چند دقیقه؛ حضوری و آنلاین.",
    images: ["/og-image.jpg"],
  },
  icons: {
    icon: [{ url: "/icon.svg", type: "image/svg+xml" }],
    apple: [{ url: "/icon.svg" }],
  },
  robots: { index: true, follow: true, googleBot: { index: true, follow: true, "max-image-preview": "large", "max-snippet": -1 } },
};

export const viewport: Viewport = {
  width: "device-width",
  initialScale: 1,
  maximumScale: 5,
  themeColor: "#0d9488",
};

export default function RootLayout({ children }: { children: ReactNode }) {
  return (
    <html lang="fa" dir="rtl">
      <body>{children}</body>
    </html>
  );
}
