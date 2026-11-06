import { eq } from "drizzle-orm";
import { z } from "zod";
import { db } from "@/db";
import { users } from "@/db/schema";
import { requireSuperAdmin } from "@/lib/admin";
import { ADMIN_PERMISSIONS } from "@/lib/auth";

type Params = Promise<{ id: string }>;

const permissionsSchema = z.object({ permissions: z.array(z.string()).max(20) });

export async function PATCH(request: Request, { params }: { params: Params }) {
  const admin = await requireSuperAdmin();
  if (!admin) return Response.json({ error: "فقط مدیر ارشد می‌تواند دسترسی‌ها را تغییر دهد." }, { status: 403 });
  const { id } = await params;
  const userId = Number(id);
  const parsed = permissionsSchema.safeParse(await request.json());
  if (!Number.isInteger(userId) || !parsed.success) return Response.json({ error: "اطلاعات معتبر نیست." }, { status: 400 });
  const [target] = await db.select().from(users).where(eq(users.id, userId)).limit(1);
  if (!target) return Response.json({ error: "مدیر موردنظر پیدا نشد." }, { status: 404 });
  if (target.role === "super_admin") return Response.json({ error: "دسترسی مدیر ارشد قابل تغییر نیست." }, { status: 400 });
  const allowed = parsed.data.permissions.filter((item): item is (typeof ADMIN_PERMISSIONS)[number] =>
    (ADMIN_PERMISSIONS as readonly string[]).includes(item),
  );
  await db.update(users).set({ permissions: JSON.stringify(allowed), updatedAt: new Date() }).where(eq(users.id, userId));
  return Response.json({ ok: true });
}

export async function DELETE(_: Request, { params }: { params: Params }) {
  const admin = await requireSuperAdmin();
  if (!admin) return Response.json({ error: "فقط مدیر ارشد مجاز است." }, { status: 403 });
  const { id } = await params;
  const userId = Number(id);
  if (!Number.isInteger(userId) || userId === admin.id) return Response.json({ error: "امکان حذف این مدیر وجود ندارد." }, { status: 400 });
  const [target] = await db.select().from(users).where(eq(users.id, userId)).limit(1);
  if (!target || target.role !== "admin") return Response.json({ error: "مدیر موردنظر پیدا نشد." }, { status: 404 });
  await db.update(users).set({ role: "user", updatedAt: new Date() }).where(eq(users.id, userId));
  return Response.json({ ok: true });
}
