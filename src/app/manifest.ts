import type { MetadataRoute } from "next";

export default function manifest(): MetadataRoute.Manifest {
  return {
    name: "نوبت سلامت | نوبت‌دهی آنلاین پزشکان",
    short_name: "نوبت سلامت",
    description: "جست‌وجوی پزشک، مشاهده نظرات بیماران و دریافت نوبت حضوری یا آنلاین از پزشکان معتبر سراسر ایران.",
    start_url: "/",
    scope: "/",
    display: "standalone",
    dir: "rtl",
    lang: "fa",
    theme_color: "#0d9488",
    background_color: "#fbfaf6",
    icons: [
      {
        src: "/icon.svg",
        sizes: "any",
        type: "image/svg+xml",
        purpose: "any",
      },
    ],
  };
}
