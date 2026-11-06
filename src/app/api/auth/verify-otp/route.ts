import { createSession, findOrCreateUser, isAdmin, isValidIranianPhone, normalizePhone, verifyOtp } from "@/lib/auth";
import { ensureSeedData } from "@/lib/seed";
import { z } from "zod";

const schema = z.object({
  phone: z.string().min(10).max(20),
  code: z.string().min(4).max(8),
});

export async function POST(request: Request) {
  try {
    await ensureSeedData();
    const parsed = schema.safeParse(await request.json());
    if (!parsed.success) return Response.json({ error: "اطلاعات واردشده کامل نیست." }, { status: 400 });
    const phone = normalizePhone(parsed.data.phone);
    if (!isValidIranianPhone(phone)) return Response.json({ error: "شماره موبایل معتبر نیست." }, { status: 400 });
    const isVerified = await verifyOtp(phone, parsed.data.code);
    if (!isVerified) return Response.json({ error: "کد واردشده اشتباه یا منقضی شده است." }, { status: 400 });

    const user = await findOrCreateUser(phone);
    await createSession(user.id);
    return Response.json({ ok: true, redirectTo: isAdmin(user) ? "/admin" : "/panel" });
  } catch (error) {
    console.error("verify otp error", error);
    return Response.json({ error: "ورود انجام نشد. لطفاً دوباره تلاش کنید." }, { status: 500 });
  }
}
