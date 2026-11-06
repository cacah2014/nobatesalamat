import Link from "next/link";
import { ArrowUpLeft, BriefcaseBusiness, Camera, HeartPulse, LockKeyhole, Mail, MapPin, PhoneCall } from "lucide-react";

const links = [
  { title: "خدمات", items: ["جست‌وجوی پزشک", "نوبت حضوری", "مشاوره آنلاین", "تخصص‌های پزشکی"] },
  { title: "نوبت سلامت", items: ["درباره ما", "تماس با ما", "همکاری با پزشکان", "فرصت‌های شغلی"] },
  { title: "راهنما", items: ["سؤالات متداول", "راهنمای دریافت نوبت", "قوانین و حریم خصوصی", "لغو و بازپرداخت"] },
];

export function SiteFooter() {
  return (
    <footer className="mt-20 bg-gradient-to-br from-[#0d4d49] to-[#0b3230] text-white">
      <div className="site-container grid gap-10 py-14 lg:grid-cols-[1.35fr_2fr_1fr]">
        <div>
          <Link href="/" className="flex items-center gap-2.5">
            <span className="grid size-11 place-items-center rounded-[15px] bg-white text-[#0d9488]"><HeartPulse size={25} /></span>
            <span className="text-xl font-black">نوبت سلامت</span>
          </Link>
          <p className="mt-5 max-w-sm text-sm leading-7 text-[#c5d9d7]">سامانه هوشمند انتخاب پزشک و دریافت نوبت آنلاین؛ همیشه و همه‌جا کنار شما برای یک انتخاب آگاهانه.</p>
          <div className="mt-5 flex gap-2">
            {[Camera, BriefcaseBusiness, Mail].map((Icon, index) => (
              <a key={index} href="#" className="grid size-10 place-items-center rounded-xl bg-white/10 text-[#d7e8e6] transition hover:bg-[#ff6b57] hover:text-white"><Icon size={18} /></a>
            ))}
          </div>
        </div>

        <div className="grid grid-cols-2 gap-8 sm:grid-cols-3">
          {links.map((section) => (
            <div key={section.title}>
              <h3 className="mb-4 text-sm font-black text-white">{section.title}</h3>
              <ul className="space-y-3 text-xs text-[#b9cfcc]">
                {section.items.map((item) => <li key={item}><a href="#" className="transition hover:text-white">{item}</a></li>)}
              </ul>
            </div>
          ))}
        </div>

        <div>
          <h3 className="mb-4 text-sm font-black">پاسخ‌گوی شما هستیم</h3>
          <a href="tel:02191099090" className="flex items-center gap-3 rounded-2xl bg-white/10 p-3.5 transition hover:bg-white/15">
            <span className="grid size-10 place-items-center rounded-xl bg-[#ff6b57] text-white"><PhoneCall size={19} /></span>
            <span><small className="block text-[10px] text-[#bfd2d0]">همه‌روزه ۸ تا ۲۲</small><b dir="ltr" className="text-sm">۰۲۱-۹۱۰۹۹۰۹۰</b></span>
          </a>
          <p className="mt-3 flex items-start gap-2 text-xs leading-6 text-[#b9cfcc]"><MapPin className="mt-1 shrink-0" size={15} />تهران، میدان ونک، خیابان ملاصدرا</p>
        </div>
      </div>
      <div className="border-t border-white/10">
        <div className="site-container flex flex-col items-center justify-between gap-3 py-5 text-[11px] text-[#a8c2bf] sm:flex-row">
          <p>© ۱۴۰۵ نوبت سلامت؛ تمام حقوق محفوظ است.</p>
          <div className="flex items-center gap-4">
            <Link href="/login" className="flex items-center gap-1 transition hover:text-white">
              <LockKeyhole size={12} /> ورود مدیران
            </Link>
            <a href="https://nobatesalamat.ir" className="flex items-center gap-1 hover:text-white">nobatesalamat.ir <ArrowUpLeft size={13} /></a>
          </div>
        </div>
      </div>
    </footer>
  );
}
