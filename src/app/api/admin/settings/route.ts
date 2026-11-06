import { eq } from "drizzle-orm";
import { z } from "zod";
import { db } from "@/db";
import { siteSettings } from "@/db/schema";
import { requirePermission } from "@/lib/admin";

const schema = z.object({ settings: z.record(z.string().max(80), z.string().max(3000)) });

export async function PATCH(request: Request) {
  const admin = await requirePermission("content");
  if (!admin) return Response.json({ error: "دسترسی به بخش محتوای سایت را ندارید." }, { status: 403 });
  const parsed = schema.safeParse(await request.json());
  if (!parsed.success) return Response.json({ error: "مقادیر تنظیمات معتبر نیستند." }, { status: 400 });
  const existing = await db.select({ key: siteSettings.key }).from(siteSettings);
  const allowed = new Set(existing.map((item) => item.key));
  await db.transaction(async (tx) => {
    for (const [key, value] of Object.entries(parsed.data.settings)) {
      if (allowed.has(key)) await tx.update(siteSettings).set({ value, updatedAt: new Date() }).where(eq(siteSettings.key, key));
    }
  });
  return Response.json({ ok: true });
}
