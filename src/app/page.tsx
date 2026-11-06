import Link from "next/link";
import {
  Activity,
  ArrowLeft,
  Baby,
  BadgeCheck,
  Bone,
  BrainCircuit,
  CalendarCheck2,
  Check,
  ChevronLeft,
  Clock3,
  Eye,
  HeartPulse,
  Headphones,
  MapPin,
  MessageCircleMore,
  PhoneCall,
  Play,
  Search,
  ShieldCheck,
  Sparkles,
  Star,
  Stethoscope,
  UserRoundSearch,
  Venus,
  Video,
} from "lucide-react";
import { db } from "@/db";
import { articles, doctors, siteSettings, specialties } from "@/db/schema";
import { and, asc, eq } from "drizzle-orm";
import { ensureSeedData } from "@/lib/seed";
import { SiteHeader } from "@/components/site-header";
import { SiteFooter } from "@/components/site-footer";
import { DoctorCard } from "@/components/doctor-card";
import { faNumber } from "@/lib/format";

export const dynamic = "force-dynamic";

const specialtyStyles: Record<string, string> = {
  rose: "bg-rose-50 text-rose-600 border-rose-100",
  amber: "bg-amber-50 text-amber-600 border-amber-100",
  violet: "bg-violet-50 text-violet-600 border-violet-100",
  pink: "bg-pink-50 text-pink-600 border-pink-100",
  sky: "bg-sky-50 text-sky-600 border-sky-100",
  blue: "bg-blue-50 text-blue-600 border-blue-100",
  cyan: "bg-cyan-50 text-cyan-600 border-cyan-100",
  teal: "bg-teal-50 text-teal-600 border-teal-100",
};

const faqs = [
  {
    question: "چطور از نوبت سلامت نوبت آنلاین بگیرم؟",
    answer: "کافی است پزشک یا تخصص موردنظر را جست‌وجو کنید، از میان زمان‌های آزاد یکی را انتخاب کنید و با شماره موبایل خود وارد شوید. نوبت شما در چند دقیقه قطعی می‌شود و کد رهگیری آن در پنل کاربری شما نمایش داده خواهد شد.",
  },
  {
    question: "آیا برای ورود به حساب کاربری، رمز عبور لازم است؟",
    answer: "خیر. در نوبت سلامت ورود فقط با شماره موبایل و کد تأیید پیامکی انجام می‌شود؛ امن‌تر و سریع‌تر از رمز عبور.",
  },
  {
    question: "امکان لغو نوبت وجود دارد؟",
    answer: "بله؛ تا قبل از زمان ویزیت می‌توانید از بخش «نوبت‌های من» در پنل کاربری، نوبت خود را لغو کنید.",
  },
  {
    question: "مشاوره آنلاین چگونه برگزار می‌شود؟",
    answer: "پزشکانی که نشان «مشاوره آنلاین» دارند، امکان پاسخ‌گویی غیرحضوری را فراهم کرده‌اند. پس از ثبت نوبت آنلاین، جزئیات ارتباط برای شما ارسال می‌شود.",
  },
  {
    question: "هزینه ویزیت هر پزشک چگونه مشخص می‌شود؟",
    answer: "هزینهٔ ویزیت به‌صورت شفاف روی پروفایل هر پزشک نمایش داده می‌شود و قبل از ثبت نهایی نوبت، مبلغ دقیق را می‌بینید.",
  },
  {
    question: "اطلاعات پزشکان چگونه بررسی و تأیید می‌شود؟",
    answer: "مدارک تخصصی و شماره نظام پزشکی پزشکان پیش از فعال شدن پروفایل، توسط تیم نوبت سلامت بررسی می‌شود و نظرات فقط از مراجعان تأییدشده ثبت می‌شود.",
  },
];

function SpecialtyIcon({ icon }: { icon: string }) {
  const props = { size: 25, strokeWidth: 1.8 };
  if (icon === "heart") return <HeartPulse {...props} />;
  if (icon === "sparkles") return <Sparkles {...props} />;
  if (icon === "brain") return <BrainCircuit {...props} />;
  if (icon === "flower") return <Venus {...props} />;
  if (icon === "baby") return <Baby {...props} />;
  if (icon === "bone") return <Bone {...props} />;
  if (icon === "eye") return <Eye {...props} />;
  return <Stethoscope {...props} />;
}

export default async function HomePage() {
  await ensureSeedData();
  const [settingsRows, specialtyRows, doctorRows, articleRows] = await Promise.all([
    db.select().from(siteSettings),
    db.select().from(specialties).where(eq(specialties.isActive, true)).orderBy(asc(specialties.sortOrder)),
    db
      .select({
        id: doctors.id,
        fullName: doctors.fullName,
        slug: doctors.slug,
        title: doctors.title,
        city: doctors.city,
        rating: doctors.rating,
        reviewsCount: doctors.reviewsCount,
        visitPrice: doctors.visitPrice,
        nextAvailable: doctors.nextAvailable,
        imageUrl: doctors.imageUrl,
        isOnline: doctors.isOnline,
        specialtyTitle: specialties.title,
      })
      .from(doctors)
      .innerJoin(specialties, eq(doctors.specialtyId, specialties.id))
      .where(and(eq(doctors.isActive, true), eq(specialties.isActive, true)))
      .limit(4),
    db.select().from(articles).where(eq(articles.isPublished, true)).limit(3),
  ]);
  const settings = Object.fromEntries(settingsRows.map((item) => [item.key, item.value]));

  const structuredData = {
    "@context": "https://schema.org",
    "@type": "MedicalBusiness",
    name: "نوبت سلامت",
    url: "https://nobatesalamat.ir",
    telephone: "+982191099090",
    address: settings.address,
  };

  return (
    <>
      <SiteHeader />
      <main>
        <section className="hero-vibrant relative overflow-hidden py-10 lg:py-14">
          <div className="dot-pattern absolute inset-0 opacity-55" />
          <div className="absolute -left-20 -top-28 size-80 rounded-full bg-[#c3eee4]/70 blur-3xl" />
          <div className="absolute -bottom-28 right-[38%] size-72 rounded-full bg-[#f8c8bc]/25 blur-3xl" />
          <div className="site-container relative grid items-center gap-10 lg:grid-cols-[1.08fr_.92fr]">
            <div className="fade-up">
              <div className="mb-5 inline-flex items-center gap-2 rounded-full border border-[#b9ddd7] bg-white/80 px-3 py-1.5 text-xs font-black text-[#0d9488] shadow-sm backdrop-blur">
                <span className="grid size-6 place-items-center rounded-full bg-[#d3f2ea]"><Activity size={14} /></span>
                {settings.hero_badge}
              </div>
              <h1 className="text-balance max-w-2xl text-[clamp(2.2rem,5vw,4rem)] font-black leading-[1.22] tracking-[-1.8px] text-[#0f3538]">
                پزشک مناسب،
                <span className="title-gradient relative mx-2 inline-block">
                  نزدیک‌تر
                  <svg className="absolute -bottom-2 right-0 w-full text-[#ff6b57]" height="8" viewBox="0 0 180 8" fill="none"><path d="M2 6C46 1 124 1 178 4" stroke="currentColor" strokeWidth="4" strokeLinecap="round" /></svg>
                </span>
                از همیشه
              </h1>
              <p className="mt-6 max-w-xl text-[15px] leading-8 text-[#536f6c] lg:text-base">{settings.hero_description}</p>

              <form action="/doctors" className="soft-shadow mt-7 grid max-w-2xl gap-2 rounded-[20px] border border-white bg-white p-2.5 md:grid-cols-[1fr_160px_auto]">
                <label className="flex min-w-0 items-center gap-3 rounded-xl bg-[#f7faf9] px-3.5 py-2.5">
                  <Search className="shrink-0 text-[#0d9488]" size={20} />
                  <span className="min-w-0 flex-1">
                    <small className="block text-[9px] font-bold text-[#829693]">پزشک یا تخصص</small>
                    <input name="q" className="w-full bg-transparent text-xs font-bold text-[#244d4a] outline-none placeholder:font-normal placeholder:text-[#90a4a1]" placeholder="مثلاً متخصص قلب" />
                  </span>
                </label>
                <label className="flex items-center gap-2 border-[#edf2f1] px-3.5 py-2 md:border-r">
                  <MapPin className="text-[#ff6b57]" size={19} />
                  <select name="city" className="w-full bg-transparent text-xs font-bold text-[#315b58] outline-none">
                    <option value="">همه شهرها</option><option>تهران</option><option>شیراز</option><option>اصفهان</option>
                  </select>
                </label>
                <button className="primary-button min-h-12 !rounded-xl !px-6"><Search size={18} />جست‌وجو</button>
              </form>

              <div className="mt-7 flex flex-wrap items-center gap-x-7 gap-y-3 text-xs text-[#587470]">
                <span className="flex items-center gap-2"><span className="flex -space-x-2 space-x-reverse">{doctorRows.slice(0, 3).map((doctor) => <img key={doctor.id} src={doctor.imageUrl} alt="" className="size-7 rounded-full border-2 border-white object-cover" />)}</span><b className="text-[#244d4a]">+۲۴۰۰ پزشک</b></span>
                <span className="flex items-center gap-1.5"><ShieldCheck size={17} className="text-[#0d9488]" />پزشکان احراز هویت‌شده</span>
                <span className="flex items-center gap-1.5"><Clock3 size={17} className="text-[#0d9488]" />نوبت‌دهی ۲۴ ساعته</span>
              </div>
            </div>

            <div className="relative hidden min-h-[500px] lg:block">
              <div className="float-animation absolute left-8 top-4 h-[430px] w-[340px] overflow-hidden rounded-[44px_44px_120px_44px] border-[7px] border-white bg-[#bddfd9] shadow-[0_30px_70px_rgba(14,84,78,.2)]">
                <img src="https://images.pexels.com/photos/6749773/pexels-photo-6749773.jpeg?auto=compress&cs=tinysrgb&fit=crop&h=1000&w=760" alt="پزشک نوبت سلامت" className="h-full w-full object-cover object-top" />
              </div>
              <div className="absolute right-2 top-20 rounded-2xl border border-white bg-white/95 p-3.5 shadow-[0_15px_35px_rgba(20,80,75,.14)] backdrop-blur fade-up delay-2">
                <div className="flex items-center gap-3"><span className="grid size-10 place-items-center rounded-xl bg-[#e9f8f4] text-[#0d9488]"><CalendarCheck2 size={21} /></span><div><small className="block text-[9px] text-[#7c9390]">نوبت شما قطعی شد</small><b className="text-xs">امروز، ساعت ۱۸:۳۰</b></div></div>
              </div>
              <div className="absolute bottom-16 left-0 rounded-2xl border border-white bg-white/95 p-3.5 shadow-[0_15px_35px_rgba(20,80,75,.14)] backdrop-blur fade-up delay-3">
                <div className="flex items-center gap-3"><span className="relative grid size-10 place-items-center rounded-xl bg-[#fff0ed] text-[#ff6b57]"><Video size={21} /><i className="absolute -left-1 -top-1 size-2.5 rounded-full border-2 border-white bg-green-500" /></span><div><small className="block text-[9px] text-[#7c9390]">مشاوره آنلاین</small><b className="text-xs">بدون نیاز به مراجعه</b></div></div>
              </div>
              <div className="absolute bottom-3 right-7 grid size-28 place-items-center rounded-full border border-[#abd8d1] bg-[#d9f2ec]/70"><div className="grid size-20 place-items-center rounded-full border border-dashed border-[#5aa89e] text-[#0d9488]"><HeartPulse size={34} /></div></div>
            </div>
          </div>
        </section>

        <section className="border-y border-[#e5eeec] bg-white">
          <div className="site-container grid grid-cols-3 divide-x divide-x-reverse divide-[#e5eeec] py-6">
            {[
              [settings.stats_doctors, "پزشک و درمانگر"],
              [settings.stats_appointments, "نوبت موفق"],
              [settings.stats_satisfaction, "رضایت کاربران"],
            ].map(([value, label]) => <div key={label} className="text-center"><strong className="block text-xl font-black text-[#0d9488] sm:text-2xl">{value}</strong><span className="mt-1 block text-[10px] text-[#718784] sm:text-xs">{label}</span></div>)}
          </div>
        </section>

        <section className="site-container mt-12">
          <div className="banner-gradient grid overflow-hidden rounded-[26px] text-white shadow-[0_24px_60px_rgba(13,148,136,.22)] md:grid-cols-[1fr_auto]">
            <div className="hero-grid flex items-center gap-5 p-6 md:p-8">
              <span className="relative grid size-14 shrink-0 place-items-center rounded-2xl bg-[#ff6b57] text-white"><PhoneCall size={27} /><i className="pulse-ring absolute -left-1 -top-1 size-3 rounded-full bg-[#8ef0ce]" /></span>
              <div><span className="mb-1 inline-block rounded-full bg-white/10 px-2 py-0.5 text-[9px] font-bold text-[#bce4dd]">پزشک فوری</span><h2 className="text-lg font-black">برای یک راهنمایی سریع آماده‌ای؟</h2><p className="mt-1 text-xs leading-6 text-[#c7dad8]">با پزشک عمومی آنلاین صحبت کن و مسیر درست درمان را پیدا کن.</p></div>
            </div>
            <Link href="/doctors?visit=online" className="m-5 flex items-center justify-center gap-2 rounded-2xl bg-white px-6 py-3 text-sm font-black text-[#123f3d] transition hover:bg-[#ff6b57] hover:text-white md:m-7">شروع مشاوره <ArrowLeft size={18} /></Link>
          </div>
        </section>

        <section id="specialties" className="site-container py-20">
          <div className="mb-8 flex items-end justify-between gap-4">
            <div><span className="text-xs font-black text-[#ff6b57]">مسیر سریع‌تر</span><h2 className="mt-2 text-2xl font-black tracking-[-.5px] text-[#173f3d] sm:text-3xl">بر اساس تخصص پیدا کن</h2><p className="mt-2 text-sm text-[#718784]">تخصص موردنظرت را انتخاب کن تا بهترین پزشکان را ببینی.</p></div>
            <Link href="/doctors" className="desktop-only flex items-center gap-1 text-sm font-black text-[#0d9488]">همه تخصص‌ها <ChevronLeft size={17} /></Link>
          </div>
          <div className="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-8">
            {specialtyRows.map((specialty) => (
              <Link key={specialty.id} href={`/doctors?specialty=${specialty.slug}`} className="card-hover group rounded-[20px] border border-[#e2ecea] bg-white p-4 text-center">
                <span className={`mx-auto grid size-12 place-items-center rounded-2xl border ${specialtyStyles[specialty.color] || specialtyStyles.teal}`}><SpecialtyIcon icon={specialty.icon} /></span>
                <b className="mt-3 block text-xs text-[#315b58] group-hover:text-[#0d9488]">{specialty.title}</b>
                <small className="mt-1 block text-[9px] text-[#8aa09d]">مشاهده پزشکان</small>
              </Link>
            ))}
          </div>
        </section>

        <section className="bg-[#f0f7f5] py-20">
          <div className="site-container">
            <div className="mb-8 flex items-end justify-between gap-4">
              <div><span className="text-xs font-black text-[#ff6b57]">انتخاب کاربران</span><h2 className="mt-2 text-2xl font-black text-[#173f3d] sm:text-3xl">پزشکان محبوب این هفته</h2><p className="mt-2 text-sm text-[#718784]">بر اساس امتیاز و تجربه واقعی مراجعان</p></div>
              <Link href="/doctors" className="secondary-button desktop-only !text-xs">مشاهده همه <ArrowLeft size={16} /></Link>
            </div>
            <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">{doctorRows.map((doctor) => <DoctorCard key={doctor.id} doctor={doctor} />)}</div>
          </div>
        </section>

        <section className="site-container py-20">
          <div className="grid items-center gap-12 lg:grid-cols-[.9fr_1.1fr]">
            <div className="relative mx-auto w-full max-w-md">
              <div className="absolute -inset-5 -z-10 rounded-[40px] bg-[#e7f5f1] -rotate-3" />
              <div className="overflow-hidden rounded-[30px] bg-[#dcefeb]"><img src="https://images.pexels.com/photos/7195310/pexels-photo-7195310.jpeg?auto=compress&cs=tinysrgb&fit=crop&h=850&w=900" alt="دریافت نوبت آنلاین" className="h-[410px] w-full object-cover" /></div>
              <button aria-label="پخش ویدیو" className="absolute inset-0 m-auto grid size-16 place-items-center rounded-full border-4 border-white bg-[#ff6b57] text-white shadow-xl"><Play className="mr-1 fill-white" size={24} /></button>
              <div className="absolute -bottom-5 -left-4 rounded-2xl bg-white p-4 shadow-xl"><strong className="block text-xl font-black text-[#0d9488]">کمتر از ۳ دقیقه</strong><small className="text-[10px] text-[#718784]">تا قطعی شدن نوبت شما</small></div>
            </div>
            <div>
              <span className="text-xs font-black text-[#ff6b57]">ساده و سریع</span><h2 className="mt-2 text-2xl font-black leading-snug text-[#173f3d] sm:text-3xl">فقط سه قدم تا دیدار پزشک</h2><p className="mt-3 max-w-xl text-sm leading-7 text-[#718784]">بدون تماس تلفنی و انتظار پشت خط، زمان مناسب خودت را انتخاب و نوبت را قطعی کن.</p>
              <div className="mt-8 space-y-4">
                {([
                  [UserRoundSearch, "۱", "پزشک مناسب را پیدا کن", "بر اساس تخصص، شهر و نظر بیماران جست‌وجو کن."],
                  [CalendarCheck2, "۲", "روز و ساعت را انتخاب کن", "از میان زمان‌های آزاد، بهترین گزینه را بردار."],
                  [BadgeCheck, "۳", "نوبتت را قطعی کن", "پیامک تأیید و یادآوری نوبت را دریافت کن."],
                ] as const).map(([Icon, number, title, text]) => {
                  const StepIcon = Icon as typeof Search;
                  return <div key={String(number)} className="flex gap-4 rounded-2xl border border-transparent p-3 transition hover:border-[#dcebe8] hover:bg-white"><span className="relative grid size-12 shrink-0 place-items-center rounded-2xl bg-[#e9f7f3] text-[#0d9488]"><StepIcon size={23} /><i className="absolute -right-1 -top-1 grid size-5 place-items-center rounded-full bg-[#ff6b57] text-[9px] font-black not-italic text-white">{number}</i></span><div><h3 className="text-sm font-black">{title}</h3><p className="mt-1 text-xs leading-6 text-[#758b88]">{text}</p></div></div>;
                })}
              </div>
            </div>
          </div>
        </section>

        <section className="dark-gradient py-16 text-white">
          <div className="site-container grid gap-8 lg:grid-cols-[1fr_1.2fr]">
            <div><span className="text-xs font-black text-[#79d8cb]">چرا نوبت سلامت؟</span><h2 className="mt-3 text-3xl font-black leading-snug">آرامش خاطر، از انتخاب تا درمان</h2><p className="mt-4 max-w-md text-sm leading-7 text-[#bcd1cf]">ما اطلاعات لازم برای انتخاب درست را شفاف در اختیارت می‌گذاریم و در تمام مسیر کنار تو می‌مانیم.</p><Link href="/about" className="mt-6 inline-flex items-center gap-2 text-sm font-black text-white">داستان ما <ArrowLeft size={17} /></Link></div>
            <div className="grid gap-3 sm:grid-cols-2">
              {([[ShieldCheck,"اطلاعات امن","حفاظت از اطلاعات پزشکی و شخصی"],[BadgeCheck,"پزشکان معتبر","بررسی مدارک و شماره نظام پزشکی"],[MessageCircleMore,"نظرات واقعی","فقط از مراجعان تأییدشده"],[Headphones,"پشتیبانی همراه","پاسخ‌گویی همه‌روزه تا ساعت ۲۲"]] as const).map(([Icon,title,text]) => { const ItemIcon=Icon as typeof Search; return <div key={String(title)} className="rounded-2xl border border-white/10 bg-white/[.06] p-4"><span className="grid size-10 place-items-center rounded-xl bg-white/10 text-[#79d8cb]"><ItemIcon size={21} /></span><b className="mt-3 block text-sm">{title}</b><small className="mt-1 block text-[10px] text-[#a9c2bf]">{text}</small></div>; })}
            </div>
          </div>
        </section>

        <section className="site-container py-20">
          <div className="text-center"><span className="text-xs font-black text-[#ff6b57]">تجربه بیماران</span><h2 className="mt-2 text-2xl font-black sm:text-3xl">اعتماد شما، ارزشمندترین دارایی ماست</h2></div>
          <div className="mx-auto mt-8 max-w-3xl rounded-[28px] border border-[#dceae7] bg-white p-6 text-center shadow-[0_18px_50px_rgba(8,77,72,.07)] sm:p-10">
            <div className="flex justify-center gap-1 text-[#f3b633]">{Array.from({length: 5}).map((_,i)=><Star key={i} size={17} className="fill-current" />)}</div>
            <blockquote className="mx-auto mt-5 max-w-2xl text-sm leading-8 text-[#3f605d] sm:text-base">«برای مادرم دنبال متخصص قلب بودم. توضیحات کامل پزشک‌ها و نظر بیماران خیلی کمک کرد انتخاب مطمئنی داشته باشم. کل فرایند نوبت گرفتن واقعاً ساده بود.»</blockquote>
            <div className="mt-5 flex items-center justify-center gap-3"><span className="grid size-10 place-items-center rounded-full bg-[#e9f8f4] font-black text-[#0d9488]">م</span><div className="text-right"><b className="block text-xs">مریم رضایی</b><small className="text-[10px] text-[#849895]">کاربر نوبت سلامت از تهران</small></div></div>
          </div>
        </section>

        <section id="health-mag" className="border-y border-[#e4eeec] bg-white py-20">
          <div className="site-container">
            <div className="mb-8 flex items-end justify-between"><div><span className="text-xs font-black text-[#ff6b57]">دانستنی‌های کاربردی</span><h2 className="mt-2 text-2xl font-black sm:text-3xl">مجله سلامت</h2></div><a href="#" className="desktop-only flex items-center gap-1 text-xs font-black text-[#0d9488]">همه مطالب <ArrowLeft size={16} /></a></div>
            <div className="grid gap-5 md:grid-cols-3">{articleRows.map((article) => <article key={article.id} className="group overflow-hidden rounded-[22px] border border-[#e1ebe9] bg-[#fbfdfc]"><div className="h-44 overflow-hidden"><img src={article.imageUrl} alt={article.title} className="h-full w-full object-cover transition duration-500 group-hover:scale-105" /></div><div className="p-5"><span className="text-[10px] font-bold text-[#0d9488]">{faNumber.format(article.readTime)} دقیقه مطالعه</span><h3 className="mt-2 text-sm font-black leading-6 text-[#244d4a]">{article.title}</h3><p className="mt-2 line-clamp-2 text-xs leading-6 text-[#718784]">{article.excerpt}</p><a href="#" className="mt-4 flex items-center gap-1 text-xs font-black text-[#0d9488]">ادامه مطلب <ChevronLeft size={14} /></a></div></article>)}</div>
          </div>
        </section>

        <section className="site-container pb-4 pt-20">
          <div className="grid items-start gap-10 lg:grid-cols-[.85fr_1.15fr]">
            <div>
              <span className="text-xs font-black text-[#ff6b57]">پاسخ سریع به شما</span>
              <h2 className="mt-2 text-2xl font-black leading-snug text-[#173f3d] sm:text-3xl">سؤالات متداول کاربران</h2>
              <p className="mt-3 max-w-md text-sm leading-7 text-[#718784]">هر آنچه پیش از اولین نوبت‌گرفتن با نوبت سلامت باید بدانید؛ شفاف و بدون پیچیدگی.</p>
              <a href="tel:02191099090" className="primary-button mt-6 !px-5 !py-3 text-xs"><PhoneCall size={16} />سؤال بیشتری دارید؟ تماس بگیرید</a>
            </div>
            <div className="space-y-3">
              {faqs.map((faq, index) => (
                <details key={faq.question} className={`group rounded-2xl border border-[#e0ebe9] bg-white transition open:border-[#7fd4c8] open:shadow-[0_14px_35px_rgba(13,148,136,.1)]`}>
                  <summary className="flex cursor-pointer list-none items-center gap-3 p-4 text-sm font-bold text-[#245049]">
                    <span className={`grid size-8 shrink-0 place-items-center rounded-xl text-xs font-black transition ${index % 2 === 0 ? "bg-[#e9f8f4] text-[#0d9488]" : "bg-[#fff0ec] text-[#ff6b57]"} group-open:bg-[#0d9488] group-open:text-white`}>{faNumber.format(index + 1)}</span>
                    <span className="flex-1">{faq.question}</span>
                    <ChevronLeft className="shrink-0 text-[#93a6a3] transition group-open:-rotate-90 group-open:text-[#0d9488]" size={17} />
                  </summary>
                  <p className="mx-4 -mt-1 border-t border-dashed border-[#e3efec] px-1 pb-4 pt-3 text-xs leading-7 text-[#5f7c7a]">{faq.answer}</p>
                </details>
              ))}
            </div>
          </div>
        </section>

        <section className="site-container py-16">
          <div className="cta-gradient relative overflow-hidden rounded-[30px] px-6 py-10 text-white shadow-[0_24px_60px_rgba(255,107,87,.3)] sm:px-12">
            <div className="absolute -left-10 -top-20 size-60 rounded-full border-[35px] border-white/10" /><div className="absolute -bottom-20 right-1/3 size-48 rounded-full bg-white/10" />
            <div className="relative flex flex-col items-start justify-between gap-7 md:flex-row md:items-center"><div><h2 className="text-2xl font-black">سلامتی را به فردا موکول نکن</h2><p className="mt-2 text-sm text-white/85">همین حالا پزشک موردنظرت را پیدا کن و اولین نوبت آزاد را بگیر.</p></div><Link href="/doctors" className="flex items-center gap-2 rounded-2xl bg-white px-6 py-3.5 text-sm font-black text-[#d55440] shadow-lg">جست‌وجوی پزشک <ArrowLeft size={18} /></Link></div>
          </div>
        </section>
      </main>
      <SiteFooter />
      <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: JSON.stringify(structuredData) }} />
      <script
        type="application/ld+json"
        dangerouslySetInnerHTML={{
          __html: JSON.stringify({
            "@context": "https://schema.org",
            "@type": "WebSite",
            name: "نوبت سلامت",
            url: "https://nobatesalamat.ir",
            potentialAction: {
              "@type": "SearchAction",
              target: { "@type": "EntryPoint", urlTemplate: "https://nobatesalamat.ir/doctors?q={search_term_string}" },
              "query-input": "required name=search_term_string",
            },
          }),
        }}
      />
      <script
        type="application/ld+json"
        dangerouslySetInnerHTML={{
          __html: JSON.stringify({
            "@context": "https://schema.org",
            "@type": "FAQPage",
            mainEntity: faqs.map((faq) => ({
              "@type": "Question",
              name: faq.question,
              acceptedAnswer: { "@type": "Answer", text: faq.answer },
            })),
          }),
        }}
      />
    </>
  );
}
