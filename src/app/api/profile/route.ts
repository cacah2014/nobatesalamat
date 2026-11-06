import { eq } from "drizzle-orm";
import { z } from "zod";
import { db } from "@/db";
import { users } from "@/db/schema";
import { getCurrentUser } from "@/lib/auth";

const schema = z.object({ fullName: z.string().trim().min(3).max(120) });

export async function PATCH(request: Request) {
  const user = await getCurrentUser();
  if (!user) return Response.json({ error: "وارد حساب کاربری شوید." }, { status: 401 });
  const parsed = schema.safeParse(await request.json());
  if (!parsed.success) return Response.json({ error: "نام واردشده معتبر نیست." }, { status: 400 });
  await db.update(users).set({ fullName: parsed.data.fullName, updatedAt: new Date() }).where(eq(users.id, user.id));
  return Response.json({ ok: true });
}
