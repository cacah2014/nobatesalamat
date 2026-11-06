import Link from "next/link";
import { redirect } from "next/navigation";
import { desc, eq } from "drizzle-orm";
import {
  CalendarCheck2,
  CalendarDays,
  CircleUserRound,
  Clock3,
  HeartPulse,
  Home,
  LogOut,
  MapPin,
  Search,
  Settings,
  Stethoscope,
  Video,
} from "lucide-react";
import { db } from "@/db";
import { appointments, doctors } from "@/db/schema";
import { getCurrentUser } from "@/lib/auth";
import { ensureSeedData } from "@/lib/seed";
import { faDate, faNumber, formatPrice } from "@/lib/format";
import { CancelAppointment, ProfileForm } from "@/components/panel-actions";

export const metadata = {
  title: "پنل کاربری",
  robots: { index: false, follow: false },
};
export const dynamic = "force-dynamic";

export default async function UserPanelPage() {
  await ensureSeedData();
  const user = await getCurrentUser();
  if (!user) redirect("/login");
  const rows = await db
    .select({ appointment: appointments, doctor: doctors })
    .from(appointments)
    .innerJoin(doctors, eq(appointments.doctorId, doctors.id))
    .where(eq(appointments.userId, user.id))
    .orderBy(desc(appointments.createdAt));
  const active = rows.filter((item) => item.appointment.status !== "cancelled");
  const cancelled = rows.length - active.length;

  return (
    <main className="min-h-screen bg-[#f4f8f7]">
      <div className="admin-grid">
        <aside className="admin-sidebar flex flex-col border-l border-[#dce9e7] bg-gradient-to-br from-[#0d4d49] to-[#0b3230] p-5 text-white">
          <Link href="/" className="flex items-center gap-2.5"><span className="grid size-10 place-items-center rounded-[13px] bg-white text-[#0d9488]"><HeartPulse size={23} /></span><span><b className="block text-base">نوبت سلامت</b><small className="text-[9px] text-[#a9c5c2]">پنل کاربری من</small></span></Link>
          <nav className="mt-10 space-y-1 text-xs font-bold"><a href="#appointments" className="flex items-center gap-3 rounded-xl bg-white/10 px-3 py-3 text-white"><CalendarDays size={18} />نوبت‌های من</a><a href="#profile" className="flex items-center gap-3 rounded-xl px-3 py-3 text-[#b9cecb] transition hover:bg-white/5 hover:text-white"><CircleUserRound size={18} />اطلاعات حساب</a><Link href="/doctors" className="flex items-center gap-3 rounded-xl px-3 py-3 text-[#b9cecb] transition hover:bg-white/5 hover:text-white"><Search size={18} />یافتن پزشک</Link><Link href="/" className="flex items-center gap-3 rounded-xl px-3 py-3 text-[#b9cecb] transition hover:bg-white/5 hover:text-white"><Home size={18} />صفحه اصلی</Link></nav>
          <div className="mt-auto rounded-2xl bg-white/[.07] p-3"><small className="block text-[9px] text-[#a9c5c2]">وارد شده با</small><b className="mt-1 block text-xs">{user.fullName}</b><span dir="ltr" className="mt-1 block text-right text-[10px] text-[#a9c5c2]">{user.phone}</span><form action="/api/auth/logout" method="post"><button className="mt-3 flex items-center gap-1 text-[10px] font-bold text-[#ffb0a2]"><LogOut size={13} />خروج از حساب</button></form></div>
        </aside>

        <section className="min-w-0 p-4 sm:p-7 lg:p-9">
          <header className="banner-gradient mb-7 flex items-center justify-between gap-4 rounded-[24px] p-5 text-white shadow-[0_18px_45px_rgba(13,148,136,.22)] sm:p-6">
            <div className="flex items-center gap-3.5">
              <span className="grid size-12 shrink-0 place-items-center rounded-2xl bg-white/15 text-xl font-black backdrop-blur">{user.fullName.slice(0, 1)}</span>
              <div>
                <p className="text-[10px] text-[#c8ece6]">سلام، {user.fullName === "کاربر نوبت سلامت" ? "دوست عزیز" : user.fullName}؛ به نوبت سلامت خوش آمدی</p>
                <h1 className="mt-0.5 text-xl font-black sm:text-2xl">داشبورد سلامتی من</h1>
              </div>
            </div>
            <div className="flex gap-2">
              <Link href="/" className="grid size-10 place-items-center rounded-xl bg-white/15 text-white transition hover:bg-white/25 lg:hidden"><Home size={18} /></Link>
              <Link href="/doctors" className="flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-xs font-black text-[#0b6e63] shadow-lg transition hover:-translate-y-0.5"><Search size={16} />دریافت نوبت جدید</Link>
            </div>
          </header>

          <div className="grid gap-4 sm:grid-cols-3">
            <div className="rounded-[20px] border border-[#dce9e7] bg-white p-4"><span className="grid size-10 place-items-center rounded-xl bg-gradient-to-br from-teal-500 to-emerald-600 text-white shadow-md shadow-teal-500/25"><CalendarCheck2 size={20} /></span><b className="mt-3 block text-2xl text-[#173f3d]">{faNumber.format(active.length)}</b><small className="text-[10px] text-[#718784]">نوبت فعال</small></div>
            <div className="rounded-[20px] border border-[#dce9e7] bg-white p-4"><span className="grid size-10 place-items-center rounded-xl bg-gradient-to-br from-orange-400 to-rose-500 text-white shadow-md shadow-orange-500/25"><Stethoscope size={20} /></span><b className="mt-3 block text-2xl text-[#173f3d]">{faNumber.format(new Set(rows.map((item) => item.doctor.id)).size)}</b><small className="text-[10px] text-[#718784]">پزشک مراجعه‌شده</small></div>
            <div className="rounded-[20px] border border-[#dce9e7] bg-white p-4"><span className="grid size-10 place-items-center rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 text-white shadow-md shadow-amber-500/25"><Clock3 size={20} /></span><b className="mt-3 block text-2xl text-[#173f3d]">{faNumber.format(cancelled)}</b><small className="text-[10px] text-[#718784]">نوبت لغوشده</small></div>
          </div>

          <div className="mt-6 grid items-start gap-6 xl:grid-cols-[1fr_310px]">
            <section id="appointments" className="rounded-[22px] border border-[#dce9e7] bg-white p-4 sm:p-5">
              <div className="mb-4 flex items-center justify-between"><div><h2 className="font-black">نوبت‌های من</h2><p className="mt-1 text-[10px] text-[#829592]">مدیریت و پیگیری نوبت‌های پزشکی</p></div><span className="rounded-lg bg-[#edf8f5] px-2 py-1 text-[10px] font-bold text-[#0d9488]">{faNumber.format(rows.length)} نوبت</span></div>
              {rows.length ? <div className="space-y-3">{rows.map(({ appointment, doctor }) => <article key={appointment.id} className={`rounded-2xl border p-3.5 ${appointment.status === "cancelled" ? "border-[#ebeeee] bg-[#f8f9f9] opacity-70" : "border-[#e1ecea] bg-white"}`}><div className="flex gap-3"><img src={doctor.imageUrl} alt={doctor.fullName} className="size-16 rounded-2xl bg-[#e9f4f1] object-cover object-top" /><div className="min-w-0 flex-1"><div className="flex flex-wrap items-start justify-between gap-2"><div><Link href={`/doctors/${doctor.slug}`} className="text-sm font-black text-[#173f3d] hover:text-[#0d9488]">{doctor.fullName}</Link><p className="mt-1 truncate text-[10px] text-[#718784]">{doctor.title}</p></div><span className={`rounded-full px-2 py-1 text-[9px] font-bold ${appointment.status === "cancelled" ? "bg-red-50 text-red-500" : "bg-emerald-50 text-emerald-600"}`}>{appointment.status === "cancelled" ? "لغوشده" : "تأییدشده"}</span></div><div className="mt-3 flex flex-wrap gap-x-4 gap-y-2 text-[10px] text-[#607976]"><span className="flex items-center gap-1"><CalendarDays size={13} />{appointment.appointmentDate}</span><span className="flex items-center gap-1"><Clock3 size={13} />{appointment.appointmentTime}</span><span className="flex items-center gap-1">{appointment.visitType === "online" ? <Video size={13} /> : <MapPin size={13} />}{appointment.visitType === "online" ? "آنلاین" : doctor.city}</span></div></div></div><div className="mt-3 flex items-center justify-between border-t border-[#edf2f1] pt-3"><span className="text-[9px] text-[#7c918e]">کد رهگیری: <b dir="ltr" className="text-[#315b58]">{appointment.trackingCode}</b> · {formatPrice(appointment.price)}</span>{appointment.status !== "cancelled" && <CancelAppointment id={appointment.id} />}</div></article>)}</div> : <div className="grid min-h-64 place-items-center text-center"><div><span className="mx-auto grid size-16 place-items-center rounded-2xl bg-[#edf8f5] text-[#0d9488]"><CalendarDays size={28} /></span><h3 className="mt-4 text-sm font-black">هنوز نوبتی ثبت نکرده‌اید</h3><p className="mt-2 text-xs text-[#718784]">پزشک مناسب را پیدا کنید و اولین نوبت خود را بگیرید.</p><Link href="/doctors" className="primary-button mt-4 !py-2.5 text-xs">جست‌وجوی پزشک</Link></div></div>}
            </section>

            <aside id="profile" className="space-y-5"><div className="rounded-[22px] border border-[#dce9e7] bg-white p-5"><div className="mb-4 flex items-center gap-2 border-b border-[#edf2f1] pb-3"><Settings size={18} className="text-[#0d9488]" /><h2 className="text-sm font-black">اطلاعات حساب</h2></div><ProfileForm initialName={user.fullName} phone={user.phone} /></div><div className="rounded-[22px] bg-gradient-to-br from-[#0d4d49] to-[#0b3230] p-5 text-white"><b className="text-sm">نیاز به راهنمایی دارید؟</b><p className="mt-2 text-[10px] leading-6 text-[#b9cfcc]">تیم پشتیبانی همه‌روزه از ساعت ۸ تا ۲۲ پاسخ‌گوی شماست.</p><a href="tel:02191099090" className="mt-3 block text-xs font-black text-[#83e0d3]">۰۲۱-۹۱۰۹۹۰۹۰</a></div></aside>
          </div>
          <p className="mt-8 text-center text-[9px] text-[#94a5a3]">عضویت از {faDate.format(user.createdAt)} · اطلاعات شما نزد نوبت سلامت محفوظ است.</p>
        </section>
      </div>
    </main>
  );
}
