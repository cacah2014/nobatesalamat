import { eq } from "drizzle-orm";
import { z } from "zod";
import { db } from "@/db";
import { users } from "@/db/schema";
import { requireSuperAdmin } from "@/lib/admin";
import { isValidIranianPhone, normalizePhone } from "@/lib/auth";

const schema = z.object({ phone: z.string().min(10).max(20), fullName: z.string().trim().min(3).max(120) });

export async function POST(request: Request) {
  const admin = await requireSuperAdmin();
  if (!admin) return Response.json({ error: "فقط مدیر ارشد مجاز است." }, { status: 403 });
  const parsed = schema.safeParse(await request.json());
  if (!parsed.success) return Response.json({ error: "نام و شماره موبایل را صحیح وارد کنید." }, { status: 400 });
  const phone = normalizePhone(parsed.data.phone);
  if (!isValidIranianPhone(phone)) return Response.json({ error: "شماره موبایل معتبر نیست." }, { status: 400 });
  const [existing] = await db.select().from(users).where(eq(users.phone, phone)).limit(1);
  if (existing?.role === "admin" || existing?.role === "super_admin") return Response.json({ error: "این شماره از قبل مدیر است." }, { status: 409 });
  if (existing) await db.update(users).set({ fullName: parsed.data.fullName, role: "admin", status: "active", updatedAt: new Date() }).where(eq(users.id, existing.id));
  else await db.insert(users).values({ phone, fullName: parsed.data.fullName, role: "admin", permissions: "[]" });
  return Response.json({ ok: true });
}
