import { z } from "zod";
import { db } from "@/db";
import { doctors, specialties } from "@/db/schema";
import { requirePermission } from "@/lib/admin";
import { eq } from "drizzle-orm";

const schema = z.object({
  fullName: z.string().trim().min(3).max(120),
  title: z.string().trim().min(3).max(160),
  specialtyId: z.number().int().positive(),
  city: z.string().trim().min(2).max(80),
  address: z.string().max(1000).default(""),
  visitPrice: z.number().int().min(0),
  experienceYears: z.number().int().min(0).max(80),
  medicalCode: z.string().max(30).default(""),
  imageUrl: z.string().url().max(2000),
  about: z.string().max(3000).default(""),
  nextAvailable: z.string().max(80).default("امروز"),
  isOnline: z.boolean().default(false),
});

export async function POST(request: Request) {
  const admin = await requirePermission("doctors");
  if (!admin) return Response.json({ error: "دسترسی به مدیریت پزشکان را ندارید." }, { status: 403 });
  const parsed = schema.safeParse(await request.json());
  if (!parsed.success) return Response.json({ error: "مشخصات پزشک کامل یا معتبر نیست." }, { status: 400 });
  const [specialty] = await db.select({ id: specialties.id }).from(specialties).where(eq(specialties.id, parsed.data.specialtyId)).limit(1);
  if (!specialty) return Response.json({ error: "تخصص انتخاب‌شده وجود ندارد." }, { status: 400 });
  const slug = `doctor-${Date.now().toString(36)}`;
  await db.insert(doctors).values({ ...parsed.data, slug });
  return Response.json({ ok: true });
}
