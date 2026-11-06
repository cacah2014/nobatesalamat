import Link from "next/link";
import {
  CalendarDays,
  HeartPulse,
  Home,
  LayoutDashboard,
  Menu,
  PhoneCall,
  Search,
  Stethoscope,
  UserRound,
} from "lucide-react";
import { getCurrentUser, isAdmin } from "@/lib/auth";

export async function SiteHeader() {
  const user = await getCurrentUser();
  const panelUrl = isAdmin(user) ? "/admin" : "/panel";

  return (
    <>
      <div className="desktop-only border-b border-[#e6efed] bg-[#f5faf8] text-xs text-[#52716e]">
        <div className="site-container flex h-9 items-center justify-between">
          <p>انتخاب آگاهانه، درمان مطمئن</p>
          <div className="flex items-center gap-5">
            <Link href="/about" className="transition hover:text-[#0d9488]">درباره ما</Link>
            <Link href="/contact" className="transition hover:text-[#0d9488]">همکاری با پزشکان</Link>
            <span className="flex items-center gap-1.5 font-bold"><PhoneCall size={14} /> پشتیبانی: ۰۲۱-۹۱۰۹۹۰۹۰</span>
          </div>
        </div>
      </div>
      <header className="sticky top-0 z-40 border-b border-[#e6efed]/90 bg-[#fbfaf7]/95 backdrop-blur-xl">
        <div className="site-container flex h-[76px] items-center justify-between gap-6">
          <Link href="/" className="group flex shrink-0 items-center gap-2.5" aria-label="صفحه اصلی نوبت سلامت">
            <span className="grid size-11 place-items-center rounded-[15px] bg-[#0d9488] text-white shadow-[0_8px_20px_rgba(8,127,117,.2)] transition group-hover:-rotate-3">
              <HeartPulse size={25} strokeWidth={2.4} />
            </span>
            <span>
              <strong className="block text-[19px] font-black leading-5 tracking-[-.5px] text-[#123b3a]">نوبت سلامت</strong>
              <small className="text-[10px] font-bold tracking-[.08em] text-[#78908e]">NOBAT SALAMAT</small>
            </span>
          </Link>

          <nav className="desktop-only flex items-center gap-8 text-sm font-bold text-[#3e5f5d]">
            <Link href="/doctors" className="transition hover:text-[#0d9488]">پزشکان</Link>
            <Link href="/#specialties" className="transition hover:text-[#0d9488]">تخصص‌ها</Link>
            <Link href="/doctors?visit=online" className="transition hover:text-[#0d9488]">مشاوره آنلاین</Link>
            <Link href="/#health-mag" className="transition hover:text-[#0d9488]">مجله سلامت</Link>
          </nav>

          <div className="flex items-center gap-2.5">
            <Link href="/doctors" className="desktop-only grid size-11 place-items-center rounded-xl border border-[#dce9e7] bg-white text-[#315b58] transition hover:border-[#5ecfc1] hover:text-[#0d9488]" aria-label="جستجوی پزشک">
              <Search size={20} />
            </Link>
            {user ? (
              <Link href={panelUrl} className="primary-button !px-4 !py-[10px]">
                {isAdmin(user) ? <LayoutDashboard size={18} /> : <UserRound size={18} />}
                <span className="desktop-only">{isAdmin(user) ? "مدیریت سایت" : "پنل من"}</span>
                <span className="sm:hidden">پنل</span>
              </Link>
            ) : (
              <Link href="/login" className="secondary-button !border-[#7fd4c8] !px-4 !py-[10px] !text-[#0d9488]">
                <UserRound size={18} />
                <span>ورود / عضویت</span>
              </Link>
            )}
            <details className="relative lg:hidden">
              <summary className="grid size-10 cursor-pointer list-none place-items-center rounded-xl border border-[#dce9e7] bg-white"><Menu size={20} /></summary>
              <div className="absolute left-0 top-12 w-52 rounded-2xl border border-[#e1ecea] bg-white p-2 shadow-xl">
                <Link href="/doctors" className="block rounded-xl px-4 py-3 text-sm font-bold hover:bg-[#edf8f5]">پزشکان</Link>
                <Link href="/#specialties" className="block rounded-xl px-4 py-3 text-sm font-bold hover:bg-[#edf8f5]">تخصص‌ها</Link>
                <Link href="/doctors?visit=online" className="block rounded-xl px-4 py-3 text-sm font-bold hover:bg-[#edf8f5]">مشاوره آنلاین</Link>
              </div>
            </details>
          </div>
        </div>
      </header>

      <nav className="mobile-bottom-nav fixed inset-x-3 bottom-2 z-50 grid-cols-4 rounded-2xl border border-[#dce9e7] bg-white/95 px-2 py-2 shadow-[0_8px_35px_rgba(15,73,69,.18)] backdrop-blur-xl sm:hidden">
        <Link href="/" className="flex flex-col items-center gap-1 text-[10px] font-bold text-[#0d9488]"><Home size={19} />خانه</Link>
        <Link href="/doctors" className="flex flex-col items-center gap-1 text-[10px] font-bold text-[#66807e]"><Stethoscope size={19} />پزشکان</Link>
        <Link href={user ? "/panel" : "/login"} className="flex flex-col items-center gap-1 text-[10px] font-bold text-[#66807e]"><CalendarDays size={19} />نوبت‌های من</Link>
        <Link href={user ? panelUrl : "/login"} className="flex flex-col items-center gap-1 text-[10px] font-bold text-[#66807e]"><UserRound size={19} />حساب من</Link>
      </nav>
    </>
  );
}
