import Link from "next/link";
import { redirect } from "next/navigation";
import { count, desc, eq, or } from "drizzle-orm";
import {
  Activity,
  CalendarDays,
  ChevronLeft,
  FilePenLine,
  HeartPulse,
  LayoutDashboard,
  LogOut,
  RefreshCw,
  ShieldCheck,
  Stethoscope,
  UserCog,
  UsersRound,
} from "lucide-react";
import { db } from "@/db";
import { appointments, doctors, siteSettings, specialties, updateLogs, users } from "@/db/schema";
import { ensureSeedData } from "@/lib/seed";
import { getCurrentUser, hasPermission, isAdmin, parsePermissions, type AdminPermission } from "@/lib/auth";
import { faDate, faNumber, formatPrice } from "@/lib/format";
import { AdminManager, AppointmentStatus, DoctorManager, SettingsForm, UpdatePanel } from "@/components/admin-actions";

export const metadata = {
  title: "مدیریت سایت",
  robots: { index: false, follow: false },
};
export const dynamic = "force-dynamic";

type SearchParams = Promise<Record<string, string | string[] | undefined>>;
type SectionKey = "overview" | "content" | "doctors" | "appointments" | "admins" | "updates";

const navItems: { key: SectionKey; label: string; icon: typeof LayoutDashboard; permission: AdminPermission | "super" | null }[] = [
  { key: "overview", label: "نمای کلی", icon: LayoutDashboard, permission: null },
  { key: "content", label: "محتوای سایت", icon: FilePenLine, permission: "content" },
  { key: "doctors", label: "مدیریت پزشکان", icon: Stethoscope, permission: "doctors" },
  { key: "appointments", label: "مدیریت نوبت‌ها", icon: CalendarDays, permission: "appointments" },
  { key: "admins", label: "مدیران و دسترسی‌ها", icon: UserCog, permission: "super" },
  { key: "updates", label: "بروزرسانی پروژه", icon: RefreshCw, permission: "updates" },
];

export default async function AdminPage({ searchParams }: { searchParams: SearchParams }) {
  await ensureSeedData();
  const admin = await getCurrentUser();
  if (!isAdmin(admin)) redirect("/login");

  const isSuper = admin.role === "super_admin";
  const allowedNav = navItems.filter((item) => {
    if (item.permission === null) return true;
    if (item.permission === "super") return isSuper;
    return hasPermission(admin, item.permission);
  });

  const params = await searchParams;
  const requestedSection = typeof params.section === "string" ? params.section : "overview";
  const matchedItem = allowedNav.find((item) => item.key === requestedSection);
  if (!matchedItem) redirect("/admin");
  const section = matchedItem.key;
  const sectionTitle = matchedItem.label;

  const [userCountRows, doctorCountRows, appointmentCountRows, settingsRows, specialtyRows, doctorRows, adminRows, appointmentRows, logRows] = await Promise.all([
    db.select({ value: count() }).from(users),
    db.select({ value: count() }).from(doctors).where(eq(doctors.isActive, true)),
    db.select({ value: count() }).from(appointments),
    db.select().from(siteSettings).orderBy(siteSettings.group, siteSettings.key),
    db.select({ id: specialties.id, title: specialties.title }).from(specialties).orderBy(specialties.sortOrder),
    db.select().from(doctors).orderBy(desc(doctors.createdAt)),
    db.select().from(users).where(or(eq(users.role, "admin"), eq(users.role, "super_admin"))).orderBy(users.id),
    db.select({ appointment: appointments, doctor: doctors, user: users }).from(appointments).innerJoin(doctors, eq(appointments.doctorId, doctors.id)).innerJoin(users, eq(appointments.userId, users.id)).orderBy(desc(appointments.createdAt)).limit(100),
    db.select().from(updateLogs).orderBy(desc(updateLogs.startedAt)).limit(5),
  ]);

  const stats = {
    users: Number(userCountRows[0]?.value || 0),
    doctors: Number(doctorCountRows[0]?.value || 0),
    appointments: Number(appointmentCountRows[0]?.value || 0),
    revenue: appointmentRows.filter((row) => row.appointment.status !== "cancelled").reduce((sum, row) => sum + row.appointment.price, 0),
  };

  const adminsWithPermissions = adminRows.map((row) => ({
    id: row.id,
    fullName: row.fullName,
    phone: row.phone,
    role: row.role,
    createdAt: row.createdAt,
    permissions: parsePermissions(row.permissions),
  }));

  return (
    <main className="min-h-screen bg-[#f3f7f6]">
      <div className="admin-grid">
        <aside className="admin-sidebar flex flex-col border-l border-white/10 bg-[#0d3837] p-5 text-white">
          <Link href="/" className="flex items-center gap-2.5">
            <span className="grid size-10 place-items-center rounded-[13px] bg-gradient-to-br from-teal-400 to-emerald-500 text-white"><HeartPulse size={23} /></span>
            <span><b className="block text-base">نوبت سلامت</b><small className="text-[9px] text-[#9fbfbc]">مدیریت یکپارچه سایت</small></span>
          </Link>
          <div className="mt-8 rounded-2xl border border-white/10 bg-white/[.06] p-3">
            <div className="flex items-center gap-2">
              <span className={`grid size-9 shrink-0 place-items-center rounded-xl font-black ${isSuper ? "bg-gradient-to-br from-amber-400 to-orange-500 text-white" : "bg-gradient-to-br from-teal-400 to-emerald-500 text-white"}`}>{admin.fullName.slice(0, 1)}</span>
              <div className="min-w-0"><b className="block truncate text-xs">{admin.fullName}</b><small className="text-[9px] text-[#9fbfbc]">{isSuper ? "مدیر ارشد · دسترسی کامل" : "مدیر سایت"}</small></div>
            </div>
          </div>
          <nav className="mt-6 space-y-1 text-xs font-bold">
            {allowedNav.map((item) => {
              const Icon = item.icon;
              const active = section === item.key;
              return (
                <Link key={item.key} href={item.key === "overview" ? "/admin" : `/admin?section=${item.key}`} className={`flex items-center gap-3 rounded-xl px-3 py-3 transition ${active ? "bg-white text-[#0d3837] shadow-lg" : "text-[#a7c4c1] hover:bg-white/[.06] hover:text-white"}`}>
                  <Icon size={18} />{item.label}{active && <ChevronLeft className="mr-auto" size={15} />}
                </Link>
              );
            })}
          </nav>
          {!isSuper && (
            <p className="mt-4 rounded-xl border border-dashed border-white/15 p-3 text-[9px] leading-5 text-[#8fb1ae]">
              {parsePermissions(admin.permissions).length === 0
                ? "مدیر ارشد هنوز دسترسی بخشی را برای شما تعیین نکرده است."
                : `دسترسی شما به ${faNumber.format(parsePermissions(admin.permissions).length)} بخش فعال است.`}
            </p>
          )}
          <div className="mt-auto space-y-1">
            <Link href="/" className="flex items-center gap-2 rounded-xl px-3 py-2.5 text-[10px] text-[#a7c4c1] hover:bg-white/[.06]"><Activity size={15} />مشاهده سایت</Link>
            <form action="/api/auth/logout" method="post"><button className="flex w-full items-center gap-2 rounded-xl px-3 py-2.5 text-[10px] text-[#ffb0a2] hover:bg-white/[.06]"><LogOut size={15} />خروج امن</button></form>
          </div>
        </aside>

        <section className="min-w-0 p-4 sm:p-7 lg:p-9">
          <header className="mb-7 flex items-center justify-between gap-3">
            <div><p className="text-[10px] font-bold text-[#0d9488]">پنل مدیریت / {sectionTitle}</p><h1 className="mt-1 text-2xl font-black text-[#173f3d]">{sectionTitle}</h1></div>
            <div className="flex items-center gap-2"><span className="hidden rounded-xl border border-[#dce9e7] bg-white px-3 py-2 text-[10px] text-[#607976] sm:block">امروز، {faDate.format(new Date())}</span><Link href="/" className="secondary-button !px-3 !py-2 text-xs">مشاهده سایت</Link></div>
          </header>

          {section === "overview" && <Overview stats={stats} appointments={appointmentRows.slice(0, 5)} logs={logRows} />}
          {section === "content" && (
            <div>
              <div className="mb-5 rounded-[18px] border border-[#cfe5e1] bg-[#edf8f5] p-4 text-xs leading-6 text-[#315b58]"><b>ویرایش زنده محتوای سایت</b><p className="text-[10px]">متن‌های اصلی، آمار، اطلاعات تماس، فوتر و شبکه‌های اجتماعی را از این بخش تغییر دهید.</p></div>
              <SettingsForm rows={settingsRows} />
            </div>
          )}
          {section === "doctors" && <DoctorManager doctors={doctorRows} specialties={specialtyRows} />}
          {section === "appointments" && <AppointmentsTable rows={appointmentRows} />}
          {section === "admins" && <AdminManager admins={adminsWithPermissions} canManage={isSuper} />}
          {section === "updates" && (
            <div>
              <UpdatePanel enabled={process.env.ALLOW_WEB_UPDATES === "true"} />
              {logRows.length > 0 && (
                <div className="mt-5 rounded-[20px] border border-[#e0ebe9] bg-white p-5">
                  <h3 className="mb-3 text-sm font-black">تاریخچه بروزرسانی</h3>
                  <div className="space-y-2">
                    {logRows.map((log) => (
                      <div key={log.id} className="flex items-center justify-between rounded-xl bg-[#f7faf9] p-3 text-[10px]">
                        <span>{faDate.format(log.startedAt)} · {log.currentStep}</span>
                        <span className={`rounded-full px-2 py-1 font-bold ${log.status === "success" ? "bg-emerald-50 text-emerald-600" : log.status === "failed" ? "bg-red-50 text-red-500" : "bg-amber-50 text-amber-600"}`}>{log.status === "success" ? "موفق" : log.status === "failed" ? "ناموفق" : "در حال اجرا"}</span>
                      </div>
                    ))}
                  </div>
                </div>
              )}
            </div>
          )}
        </section>
      </div>
    </main>
  );
}

type AppointmentRow = { appointment: typeof appointments.$inferSelect; doctor: typeof doctors.$inferSelect; user: typeof users.$inferSelect };

function Overview({ stats, appointments: rows, logs }: { stats: { users: number; doctors: number; appointments: number; revenue: number }; appointments: AppointmentRow[]; logs: (typeof updateLogs.$inferSelect)[] }) {
  return (
    <div>
      <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <StatCard icon={UsersRound} label="کل کاربران" value={faNumber.format(stats.users)} color="teal" />
        <StatCard icon={Stethoscope} label="پزشکان فعال" value={faNumber.format(stats.doctors)} color="coral" />
        <StatCard icon={CalendarDays} label="کل نوبت‌ها" value={faNumber.format(stats.appointments)} color="blue" />
        <StatCard icon={Activity} label="ارزش نوبت‌ها" value={formatPrice(stats.revenue)} color="amber" />
      </div>
      <div className="mt-6 grid items-start gap-5 xl:grid-cols-[1fr_310px]">
        <div className="rounded-[20px] border border-[#e0ebe9] bg-white p-5">
          <div className="mb-4 flex items-center justify-between">
            <div><h2 className="text-sm font-black">آخرین نوبت‌ها</h2><p className="mt-1 text-[9px] text-[#7b918e]">جدیدترین رزروهای کاربران</p></div>
            <Link href="/admin?section=appointments" className="text-[10px] font-black text-[#0d9488]">مشاهده همه</Link>
          </div>
          <AppointmentsTable rows={rows} compact />
        </div>
        <div className="space-y-5">
          <div className="rounded-[20px] bg-gradient-to-br from-[#0d4d49] via-[#0d3837] to-[#123030] p-5 text-white">
            <ShieldCheck className="text-[#5eead4]" size={24} />
            <h3 className="mt-3 text-sm font-black">وضعیت سامانه پایدار است</h3>
            <p className="mt-2 text-[10px] leading-5 text-[#b5cfcc]">دیتابیس و سرویس نوبت‌دهی در دسترس هستند.</p>
            <span className="mt-4 inline-flex items-center gap-1 rounded-full bg-emerald-400/10 px-2 py-1 text-[9px] font-bold text-[#8ce9d2]"><i className="size-1.5 rounded-full bg-emerald-400" />همه سرویس‌ها آنلاین</span>
          </div>
          <div className="rounded-[20px] border border-[#e0ebe9] bg-white p-5">
            <h3 className="text-sm font-black">آخرین آپدیت</h3>
            {logs[0] ? (
              <><p className="mt-3 text-[10px] text-[#718784]">{faDate.format(logs[0].startedAt)}</p><span className="mt-2 inline-block rounded-full bg-[#edf8f5] px-2 py-1 text-[9px] font-bold text-[#0d9488]">{logs[0].status}</span></>
            ) : (
              <p className="mt-3 text-[10px] text-[#829592]">هنوز آپدیتی ثبت نشده است.</p>
            )}
            <Link href="/admin?section=updates" className="mt-4 flex items-center gap-1 text-[10px] font-black text-[#0d9488]"><RefreshCw size={13} />مدیریت بروزرسانی</Link>
          </div>
        </div>
      </div>
    </div>
  );
}

function StatCard({ icon: Icon, label, value, color }: { icon: typeof UsersRound; label: string; value: string; color: "teal" | "coral" | "blue" | "amber" }) {
  const colors = {
    teal: "bg-gradient-to-br from-teal-500 to-emerald-600 text-white",
    coral: "bg-gradient-to-br from-orange-400 to-rose-500 text-white",
    blue: "bg-gradient-to-br from-sky-500 to-indigo-500 text-white",
    amber: "bg-gradient-to-br from-amber-400 to-orange-500 text-white",
  };
  return (
    <div className="rounded-[20px] border border-[#e0ebe9] bg-white p-4">
      <div className="flex items-start justify-between"><span className={`grid size-10 place-items-center rounded-xl shadow-md ${colors[color]}`}><Icon size={20} /></span><span className="text-[9px] font-bold text-emerald-600">فعال</span></div>
      <b className="mt-4 block text-xl font-black text-[#173f3d]">{value}</b>
      <small className="text-[10px] text-[#718784]">{label}</small>
    </div>
  );
}

function AppointmentsTable({ rows, compact = false }: { rows: AppointmentRow[]; compact?: boolean }) {
  if (!rows.length) return <div className="grid min-h-40 place-items-center text-xs text-[#829592]">هنوز نوبتی ثبت نشده است.</div>;
  return (
    <div className="overflow-x-auto">
      <table className="w-full min-w-[620px] text-right">
        <thead>
          <tr className="border-b border-[#e7efed] text-[9px] text-[#7b918e]"><th className="pb-3 font-bold">بیمار</th><th className="pb-3 font-bold">پزشک</th><th className="pb-3 font-bold">زمان</th><th className="pb-3 font-bold">مبلغ</th><th className="pb-3 font-bold">وضعیت</th></tr>
        </thead>
        <tbody>
          {rows.map(({ appointment, doctor, user }) => (
            <tr key={appointment.id} className="border-b border-[#f0f4f3] text-[10px]">
              <td className="py-3"><b className="block text-[#315b58]">{appointment.patientName}</b><span dir="ltr" className="text-[#829592]">{user.phone}</span></td>
              <td className="py-3"><div className="flex items-center gap-2"><img src={doctor.imageUrl} alt="" className="size-8 rounded-lg object-cover object-top" /><span>{doctor.fullName}</span></div></td>
              <td className="py-3 text-[#607976]">{appointment.appointmentDate}<br /><span dir="ltr">{appointment.appointmentTime}</span></td>
              <td className="py-3 text-[#607976]">{formatPrice(appointment.price)}</td>
              <td className="py-3"><AppointmentStatus id={appointment.id} initial={appointment.status} /></td>
            </tr>
          ))}
        </tbody>
      </table>
      {!compact && <p className="mt-4 text-[9px] text-[#829592]">نمایش {faNumber.format(rows.length)} نوبت اخیر</p>}
    </div>
  );
}
