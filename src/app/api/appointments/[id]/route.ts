import { and, eq } from "drizzle-orm";
import { db } from "@/db";
import { appointments } from "@/db/schema";
import { getCurrentUser } from "@/lib/auth";

type Params = Promise<{ id: string }>;

export async function DELETE(_: Request, { params }: { params: Params }) {
  const user = await getCurrentUser();
  if (!user) return Response.json({ error: "وارد حساب کاربری شوید." }, { status: 401 });
  const { id } = await params;
  const appointmentId = Number(id);
  if (!Number.isInteger(appointmentId)) return Response.json({ error: "شناسه نوبت معتبر نیست." }, { status: 400 });
  const [item] = await db.select().from(appointments).where(and(eq(appointments.id, appointmentId), eq(appointments.userId, user.id))).limit(1);
  if (!item) return Response.json({ error: "نوبت پیدا نشد." }, { status: 404 });
  if (item.status === "cancelled") return Response.json({ ok: true });
  await db.update(appointments).set({ status: "cancelled" }).where(eq(appointments.id, item.id));
  return Response.json({ ok: true });
}
