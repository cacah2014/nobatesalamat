import { randomInt } from "crypto";
import { and, eq } from "drizzle-orm";
import { z } from "zod";
import { db } from "@/db";
import { appointments, doctors } from "@/db/schema";
import { getCurrentUser, isValidIranianPhone, normalizePhone } from "@/lib/auth";

const schema = z.object({
  doctorId: z.number().int().positive(),
  appointmentDate: z.string().min(4).max(30),
  appointmentTime: z.string().regex(/^\d{2}:\d{2}$/),
  visitType: z.enum(["in_person", "online"]),
  patientName: z.string().trim().min(3).max(120),
  patientPhone: z.string().min(10).max(20),
  note: z.string().max(1000).optional().default(""),
});

export async function POST(request: Request) {
  try {
    const user = await getCurrentUser();
    if (!user) return Response.json({ error: "ابتدا وارد حساب کاربری شوید." }, { status: 401 });
    const parsed = schema.safeParse(await request.json());
    if (!parsed.success) return Response.json({ error: "اطلاعات نوبت را کامل و صحیح وارد کنید." }, { status: 400 });
    const phone = normalizePhone(parsed.data.patientPhone);
    if (!isValidIranianPhone(phone)) return Response.json({ error: "شماره موبایل بیمار معتبر نیست." }, { status: 400 });

    const [doctor] = await db.select().from(doctors).where(and(eq(doctors.id, parsed.data.doctorId), eq(doctors.isActive, true))).limit(1);
    if (!doctor) return Response.json({ error: "پزشک موردنظر فعال نیست." }, { status: 404 });
    if (parsed.data.visitType === "online" && !doctor.isOnline) return Response.json({ error: "این پزشک مشاوره آنلاین ندارد." }, { status: 400 });

    const [duplicate] = await db.select({ id: appointments.id }).from(appointments).where(and(
      eq(appointments.userId, user.id),
      eq(appointments.doctorId, doctor.id),
      eq(appointments.appointmentDate, parsed.data.appointmentDate),
      eq(appointments.appointmentTime, parsed.data.appointmentTime),
    )).limit(1);
    if (duplicate) return Response.json({ error: "این نوبت قبلاً برای شما ثبت شده است." }, { status: 409 });

    const trackingCode = `NS${Date.now().toString().slice(-6)}${randomInt(100, 999)}`;
    await db.insert(appointments).values({
      userId: user.id,
      doctorId: doctor.id,
      appointmentDate: parsed.data.appointmentDate,
      appointmentTime: parsed.data.appointmentTime,
      visitType: parsed.data.visitType,
      price: doctor.visitPrice,
      trackingCode,
      patientName: parsed.data.patientName,
      patientPhone: phone,
      note: parsed.data.note,
    });
    return Response.json({ ok: true, trackingCode });
  } catch (error) {
    console.error("appointment error", error);
    return Response.json({ error: "ثبت نوبت با خطا روبه‌رو شد." }, { status: 500 });
  }
}
