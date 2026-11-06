import { db } from "@/db";
import { otpCodes } from "@/db/schema";
import { createOtp, isValidIranianPhone, normalizePhone } from "@/lib/auth";
import { ensureSeedData } from "@/lib/seed";
import { desc, eq } from "drizzle-orm";
import { z } from "zod";

const schema = z.object({ phone: z.string().min(10).max(20) });

export async function POST(request: Request) {
  try {
    await ensureSeedData();
    const parsed = schema.safeParse(await request.json());
    if (!parsed.success) return Response.json({ error: "شماره موبایل معتبر نیست." }, { status: 400 });
    const phone = normalizePhone(parsed.data.phone);
    if (!isValidIranianPhone(phone)) {
      return Response.json({ error: "شماره موبایل را به‌صورت ۰۹xxxxxxxxx وارد کنید." }, { status: 400 });
    }

    const [recent] = await db
      .select({ createdAt: otpCodes.createdAt })
      .from(otpCodes)
      .where(eq(otpCodes.phone, phone))
      .orderBy(desc(otpCodes.createdAt))
      .limit(1);
    if (recent && Date.now() - recent.createdAt.getTime() < 55_000) {
      return Response.json({ error: "لطفاً برای ارسال مجدد کمی صبر کنید." }, { status: 429 });
    }

    const code = await createOtp(phone);
    const apiKey = process.env.KAVENEGAR_API_KEY;
    if (apiKey) {
      const template = process.env.KAVENEGAR_TEMPLATE || "login";
      const url = new URL(`https://api.kavenegar.com/v1/${apiKey}/verify/lookup.json`);
      url.searchParams.set("receptor", phone);
      url.searchParams.set("token", code);
      url.searchParams.set("template", template);
      const smsResponse = await fetch(url, { cache: "no-store" });
      if (!smsResponse.ok) throw new Error("SMS provider rejected the request");
    }

    return Response.json({
      ok: true,
      message: apiKey ? "کد تأیید پیامک شد." : "کد آزمایشی ایجاد شد.",
      ...(!apiKey ? { devCode: code } : {}),
    });
  } catch (error) {
    console.error("request otp error", error);
    return Response.json({ error: "ارسال کد با خطا روبه‌رو شد. دوباره تلاش کنید." }, { status: 500 });
  }
}
