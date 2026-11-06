import { eq } from "drizzle-orm";
import { z } from "zod";
import { db } from "@/db";
import { doctors } from "@/db/schema";
import { requirePermission } from "@/lib/admin";

type Params = Promise<{ id: string }>;
const updateSchema = z.object({
  fullName: z.string().trim().min(3).max(120).optional(), title: z.string().trim().min(3).max(160).optional(),
  specialtyId: z.number().int().positive().optional(), city: z.string().trim().min(2).max(80).optional(),
  address: z.string().max(1000).optional(), visitPrice: z.number().int().min(0).optional(),
  experienceYears: z.number().int().min(0).max(80).optional(), medicalCode: z.string().max(30).optional(),
  imageUrl: z.string().url().max(2000).optional(), about: z.string().max(3000).optional(),
  nextAvailable: z.string().max(80).optional(), isOnline: z.boolean().optional(), isActive: z.boolean().optional(),
});

export async function PATCH(request: Request, { params }: { params: Params }) {
  const admin = await requirePermission("doctors");
  if (!admin) return Response.json({ error: "دسترسی به مدیریت پزشکان را ندارید." }, { status: 403 });
  const { id } = await params;
  const doctorId = Number(id);
  const parsed = updateSchema.safeParse(await request.json());
  if (!Number.isInteger(doctorId) || !parsed.success) return Response.json({ error: "اطلاعات معتبر نیست." }, { status: 400 });
  const [existing] = await db.select({ id: doctors.id }).from(doctors).where(eq(doctors.id, doctorId)).limit(1);
  if (!existing) return Response.json({ error: "پزشک پیدا نشد." }, { status: 404 });
  await db.update(doctors).set({ ...parsed.data, updatedAt: new Date() }).where(eq(doctors.id, doctorId));
  return Response.json({ ok: true });
}

export async function DELETE(_: Request, { params }: { params: Params }) {
  const admin = await requirePermission("doctors");
  if (!admin) return Response.json({ error: "دسترسی به مدیریت پزشکان را ندارید." }, { status: 403 });
  const { id } = await params;
  const doctorId = Number(id);
  if (!Number.isInteger(doctorId)) return Response.json({ error: "شناسه معتبر نیست." }, { status: 400 });
  await db.update(doctors).set({ isActive: false, updatedAt: new Date() }).where(eq(doctors.id, doctorId));
  return Response.json({ ok: true });
}
