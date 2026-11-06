import { eq } from "drizzle-orm";
import { z } from "zod";
import { db } from "@/db";
import { appointments } from "@/db/schema";
import { requirePermission } from "@/lib/admin";

type Params = Promise<{ id: string }>;
const schema = z.object({ status: z.enum(["confirmed", "completed", "cancelled"]) });

export async function PATCH(request: Request, { params }: { params: Params }) {
  const admin = await requirePermission("appointments");
  if (!admin) return Response.json({ error: "دسترسی به مدیریت نوبت‌ها را ندارید." }, { status: 403 });
  const parsed = schema.safeParse(await request.json());
  const { id } = await params;
  const appointmentId = Number(id);
  if (!parsed.success || !Number.isInteger(appointmentId)) return Response.json({ error: "اطلاعات معتبر نیست." }, { status: 400 });
  await db.update(appointments).set({ status: parsed.data.status }).where(eq(appointments.id, appointmentId));
  return Response.json({ ok: true });
}
