import Link from "next/link";
import { redirect } from "next/navigation";
import { ArrowRight, CalendarCheck2, CheckCircle2, HeartPulse, ShieldCheck, Sparkles } from "lucide-react";
import { AuthForm } from "@/components/auth-form";
import { ensureSeedData } from "@/lib/seed";
import { getCurrentUser, isAdmin } from "@/lib/auth";

export const metadata = {
  title: "ورود یا ثبت‌نام با شماره موبایل",
  description: "ورود امن به نوبت سلامت با کد تأیید پیامکی؛ بدون نیاز به رمز عبور.",
  robots: { index: false, follow: false },
};
export const dynamic = "force-dynamic";

export default async function LoginPage() {
  await ensureSeedData();
  const user = await getCurrentUser();
  if (user) redirect(isAdmin(user) ? "/admin" : "/panel");

  return (
    <main className="grid min-h-screen bg-white lg:grid-cols-[.95fr_1.05fr]">
      <section className="relative flex min-h-screen items-center justify-center px-5 py-10">
        <Link href="/" className="absolute right-6 top-6 flex items-center gap-2 text-xs font-bold text-[#607976] transition hover:text-[#0d9488]"><ArrowRight size={17} />بازگشت به خانه</Link>
        <div className="w-full max-w-[420px]">
          <Link href="/" className="mb-9 inline-flex items-center gap-2.5">
            <span className="grid size-11 place-items-center rounded-[15px] bg-[#0d9488] text-white"><HeartPulse size={25} /></span>
            <span><strong className="block text-lg font-black">نوبت سلامت</strong><small className="block text-[9px] font-bold tracking-[.14em] text-[#78908e]">NOBAT SALAMAT</small></span>
          </Link>
          <span className="inline-flex items-center gap-1.5 rounded-full bg-[#eef8f5] px-3 py-1.5 text-[10px] font-black text-[#0d9488]"><ShieldCheck size={14} />ورود امن و بدون رمز عبور</span>
          <h1 className="mt-4 text-3xl font-black tracking-[-.8px] text-[#173f3d]">به نوبت سلامت خوش آمدید</h1>
          <p className="mt-3 text-sm leading-7 text-[#718784]">برای ورود یا ساخت حساب، شماره موبایل خود را وارد کنید.</p>
          <AuthForm />
          <div className="mt-8 flex items-center justify-center gap-5 border-t border-[#e7efed] pt-5 text-[10px] text-[#829592]"><span className="flex items-center gap-1"><CheckCircle2 size={13} className="text-[#0d9488]" />بدون نیاز به رمز</span><span className="flex items-center gap-1"><CheckCircle2 size={13} className="text-[#0d9488]" />حفظ حریم خصوصی</span></div>
        </div>
      </section>

      <section className="hero-grid relative hidden min-h-screen overflow-hidden dark-gradient p-10 text-white lg:flex lg:items-center lg:justify-center">
        <div className="absolute -left-28 -top-28 size-96 rounded-full bg-[#1e716b] blur-3xl" />
        <div className="absolute -bottom-32 -right-20 size-80 rounded-full bg-[#ff6b57]/20 blur-3xl" />
        <div className="relative max-w-lg">
          <div className="relative mx-auto mb-10 h-[330px] w-[390px]">
            <div className="absolute inset-x-7 inset-y-0 overflow-hidden rounded-[36px_36px_90px_36px] border-4 border-white/15 bg-[#d9eee9]"><img src="https://images.pexels.com/photos/6749765/pexels-photo-6749765.jpeg?auto=compress&cs=tinysrgb&fit=crop&h=900&w=800" alt="پزشک نوبت سلامت" className="h-full w-full object-cover object-top" /></div>
            <div className="absolute -right-2 top-12 rounded-2xl border border-white/15 bg-white/95 p-3 text-[#173f3d] shadow-xl"><CalendarCheck2 className="mb-2 text-[#0d9488]" size={23} /><b className="block text-xs">نوبت‌دهی آسان</b><small className="text-[9px] text-[#718784]">در هر ساعت از شبانه‌روز</small></div>
            <div className="absolute -bottom-3 left-0 rounded-2xl border border-white/15 bg-[#ff6b57] p-3 text-white shadow-xl"><Sparkles className="mb-2" size={22} /><b className="block text-xs">انتخاب آگاهانه</b><small className="text-[9px] text-white/75">بر اساس نظر بیماران</small></div>
          </div>
          <h2 className="text-center text-2xl font-black">همهٔ نوبت‌های پزشکی، یک‌جا</h2>
          <p className="mx-auto mt-3 max-w-md text-center text-sm leading-7 text-[#bcd1cf]">پزشک مناسب را پیدا کنید، اولین زمان آزاد را ببینید و بدون تماس تلفنی نوبت بگیرید.</p>
          <div className="mt-7 grid grid-cols-3 gap-3 text-center"><div className="rounded-2xl bg-white/[.07] p-3"><b className="block text-lg text-[#7de0d2]">۲۴/۷</b><small className="text-[9px] text-[#b5ccca]">نوبت‌دهی</small></div><div className="rounded-2xl bg-white/[.07] p-3"><b className="block text-lg text-[#7de0d2]">+۲۴۰۰</b><small className="text-[9px] text-[#b5ccca]">پزشک معتبر</small></div><div className="rounded-2xl bg-white/[.07] p-3"><b className="block text-lg text-[#7de0d2]">۹۶٪</b><small className="text-[9px] text-[#b5ccca]">رضایت کاربران</small></div></div>
        </div>
      </section>
    </main>
  );
}
