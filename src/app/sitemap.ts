import type { MetadataRoute } from "next";
import { eq } from "drizzle-orm";
import { db } from "@/db";
import { doctors } from "@/db/schema";
import { ensureSeedData } from "@/lib/seed";

export const dynamic = "force-dynamic";
export const revalidate = 3600;

export default async function sitemap(): Promise<MetadataRoute.Sitemap> {
  const now = new Date();
  const staticRoutes: MetadataRoute.Sitemap = [
    { url: "https://nobatesalamat.ir", lastModified: now, changeFrequency: "daily", priority: 1 },
    { url: "https://nobatesalamat.ir/doctors", lastModified: now, changeFrequency: "hourly", priority: 0.9 },
    { url: "https://nobatesalamat.ir/doctors?visit=online", lastModified: now, changeFrequency: "daily", priority: 0.8 },
    { url: "https://nobatesalamat.ir/login", lastModified: now, changeFrequency: "monthly", priority: 0.4 },
  ];

  try {
    await ensureSeedData();
    const rows = await db
      .select({ slug: doctors.slug, updatedAt: doctors.updatedAt })
      .from(doctors)
      .where(eq(doctors.isActive, true));
    const doctorRoutes: MetadataRoute.Sitemap = rows.map((doctor) => ({
      url: `https://nobatesalamat.ir/doctors/${doctor.slug}`,
      lastModified: doctor.updatedAt,
      changeFrequency: "weekly",
      priority: 0.7,
    }));
    return [...staticRoutes, ...doctorRoutes];
  } catch {
    return staticRoutes;
  }
}
