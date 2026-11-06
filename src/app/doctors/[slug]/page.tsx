import { notFound } from "next/navigation";
import {
  Award,
  BadgeCheck,
  BriefcaseMedical,
  CheckCircle2,
  Clock3,
  GraduationCap,
  MapPin,
  MessageCircleMore,
  Phone,
  ShieldCheck,
  Star,
  Video,
} from "lucide-react";
import { and, eq, ne } from "drizzle-orm";
import { db } from "@/db";
import { doctors, specialties } from "@/db/schema";
import { ensureSeedData } from "@/lib/seed";
import { getCurrentUser } from "@/lib/auth";
import { faNumber } from "@/lib/format";
import { SiteHeader } from "@/components/site-header";
import { SiteFooter } from "@/components/site-footer";
import { DoctorCard } from "@/components/doctor-card";
import { BookingWidget } from "@/components/booking-widget";

export const dynamic = "force-dynamic";

type Params = Promise<{ slug: string }>;

export async function generateMetadata({ params }: { params: Params }) {
  const { slug } = await params;
  try {
    const [row] = await db
      .select({ doctor: doctors, specialtyTitle: specialties.title })
      .from(doctors)
      .innerJoin(specialties, eq(doctors.specialtyId, specialties.id))
      .where(eq(doctors.slug, slug))
      .limit(1);
    if (!row) return { title: "پزشک پیدا نشد" };
    const item = row.doctor;
    const title = `${item.fullName} | رزرو نوبت آنلاین`;
    const description = `${item.title} در ${item.city}؛ امتیاز ${faNumber.format(item.rating)} از ${faNumber.format(item.reviewsCount)} نظر بیماران. مشاهدهٔ اولین نوبت آزاد و رزرو نوبت ${item.isOnline ? "حضوری یا آنلاین" : "حضوری"} در نوبت سلامت.`;
    return {
      title,
      description,
      keywords: [item.fullName, item.title, row.specialtyTitle, `نوبت ${row.specialtyTitle}`, `دکتر ${row.specialtyTitle} در ${item.city}`],
      alternates: { canonical: `/doctors/${item.slug}` },
      openGraph: { title, description, url: `https://nobatesalamat.ir/doctors/${item.slug}`, type: "profile", images: [{ url: item.imageUrl, alt: item.fullName }] },
    };
  } catch {
    return { title: "پروفایل پزشک" };
  }
}

export default async function DoctorProfilePage({ params }: { params: Params }) {
  await ensureSeedData();
  const { slug } = await params;
  const [doctor] = await db
    .select({ doctor: doctors, specialtyTitle: specialties.title, specialtySlug: specialties.slug })
    .from(doctors)
    .innerJoin(specialties, eq(doctors.specialtyId, specialties.id))
    .where(and(eq(doctors.slug, slug), eq(doctors.isActive, true)))
    .limit(1);
  if (!doctor) notFound();
  const user = await getCurrentUser();
  const similar = await db
    .select({
      id: doctors.id, fullName: doctors.fullName, slug: doctors.slug, title: doctors.title, city: doctors.city,
      rating: doctors.rating, reviewsCount: doctors.reviewsCount, visitPrice: doctors.visitPrice,
      nextAvailable: doctors.nextAvailable, imageUrl: doctors.imageUrl, isOnline: doctors.isOnline,
      specialtyTitle: specialties.title,
    })
    .from(doctors)
    .innerJoin(specialties, eq(doctors.specialtyId, specialties.id))
    .where(and(eq(doctors.isActive, true), ne(doctors.id, doctor.doctor.id)))
    .limit(3);

  const item = doctor.doctor;
  return (
    <>
      <SiteHeader />
      <main className="min-h-screen bg-[#f7faf9]">
        <div className="border-b border-[#dceae7] bg-[#e9f8f4] py-3">
          <div className="site-container text-[10px] text-[#718784]">خانه / پزشکان / {doctor.specialtyTitle} / <b className="text-[#315b58]">{item.fullName}</b></div>
        </div>
        <div className="site-container grid items-start gap-7 py-8 lg:grid-cols-[1fr_370px]">
          <div className="space-y-5">
            <section className="overflow-hidden rounded-[26px] border border-[#e0ebe9] bg-white p-5 sm:p-7">
              <div className="flex flex-col gap-6 sm:flex-row">
                <div className="relative mx-auto h-48 w-40 shrink-0 overflow-hidden rounded-[22px] bg-[#dcefeb] sm:mx-0"><img src={item.imageUrl} alt={item.fullName} className="h-full w-full object-cover object-top" />{item.isOnline && <span className="absolute inset-x-2 bottom-2 flex items-center justify-center gap-1 rounded-full bg-[#0d9488]/95 py-1.5 text-[9px] font-bold text-white"><span className="size-1.5 rounded-full bg-[#9ef0d8]" />آنلاین و پاسخ‌گو</span>}</div>
                <div className="min-w-0 flex-1">
                  <div className="flex flex-wrap items-center gap-2"><span className="rounded-full bg-[#e9f8f4] px-2.5 py-1 text-[10px] font-black text-[#0d9488]">{doctor.specialtyTitle}</span><span className="flex items-center gap-1 text-[10px] font-bold text-[#4d7773]"><BadgeCheck size={15} className="fill-[#d3f2ea] text-[#0d9488]" />احراز هویت‌شده</span></div>
                  <h1 className="mt-3 text-2xl font-black text-[#173f3d]">{item.fullName}</h1>
                  <p className="mt-2 text-sm text-[#607976]">{item.title}</p>
                  <div className="mt-5 flex flex-wrap gap-4 text-xs text-[#607976]"><span className="flex items-center gap-1.5"><Star size={16} className="fill-[#f4b83e] text-[#f4b83e]" /><b className="text-[#315b58]">{faNumber.format(item.rating)}</b> از {faNumber.format(item.reviewsCount)} نظر</span><span className="flex items-center gap-1.5"><MapPin size={16} className="text-[#ff6b57]" />{item.city}</span><span className="flex items-center gap-1.5"><BriefcaseMedical size={16} className="text-[#0d9488]" />{faNumber.format(item.experienceYears)} سال تجربه</span></div>
                  <div className="mt-5 flex flex-wrap gap-2"><span className="rounded-xl bg-[#f7faf9] px-3 py-2 text-[10px] font-bold text-[#526f6c]">شماره نظام پزشکی: {item.medicalCode}</span>{item.isOnline && <span className="flex items-center gap-1 rounded-xl bg-[#fff0ed] px-3 py-2 text-[10px] font-bold text-[#dc604c]"><Video size={14} />مشاوره آنلاین</span>}</div>
                </div>
              </div>
            </section>

            <section className="rounded-[24px] border border-[#e0ebe9] bg-white p-6"><h2 className="flex items-center gap-2 font-black"><span className="grid size-9 place-items-center rounded-xl bg-[#e9f8f4] text-[#0d9488]"><BriefcaseMedical size={19} /></span>درباره پزشک</h2><p className="mt-4 text-sm leading-8 text-[#607976]">{item.about}</p><div className="mt-5 grid gap-3 sm:grid-cols-3">{([[GraduationCap,"مدرک تخصصی","دانشگاه علوم پزشکی تهران"],[Award,"عضویت حرفه‌ای","عضو انجمن تخصصی ایران"],[ShieldCheck,"پروانه معتبر","تأییدشده توسط نظام پزشکی"]] as const).map(([Icon,title,text])=>{const RowIcon=Icon as typeof Star;return <div key={String(title)} className="rounded-2xl border border-[#edf2f1] bg-[#fafcfb] p-3"><RowIcon size={18} className="text-[#0d9488]" /><b className="mt-2 block text-xs">{title}</b><small className="mt-1 block text-[9px] leading-5 text-[#7b918e]">{text}</small></div>})}</div></section>

            <section className="rounded-[24px] border border-[#e0ebe9] bg-white p-6"><h2 className="flex items-center gap-2 font-black"><span className="grid size-9 place-items-center rounded-xl bg-[#fff0ed] text-[#ff6b57]"><MapPin size={19} /></span>آدرس مطب و اطلاعات مراجعه</h2><div className="mt-4 rounded-2xl bg-[#f5f9f8] p-4"><p className="text-xs leading-6 text-[#526f6c]">{item.address}</p><div className="mt-3 flex flex-wrap gap-4 text-[10px] text-[#718784]"><span className="flex items-center gap-1"><Clock3 size={14} />شنبه تا چهارشنبه، ۱۶ تا ۲۱</span><span className="flex items-center gap-1"><Phone size={14} />شماره مطب پس از رزرو نمایش داده می‌شود</span></div></div></section>

            <section className="rounded-[24px] border border-[#e0ebe9] bg-white p-6"><div className="flex items-center justify-between"><h2 className="flex items-center gap-2 font-black"><span className="grid size-9 place-items-center rounded-xl bg-amber-50 text-amber-600"><MessageCircleMore size={19} /></span>نظر بیماران</h2><span className="text-xs text-[#718784]">{faNumber.format(item.reviewsCount)} نظر</span></div><div className="mt-5 rounded-2xl border border-[#edf2f1] p-4"><div className="flex justify-between"><div className="flex items-center gap-2"><span className="grid size-9 place-items-center rounded-full bg-[#e9f8f4] text-xs font-black text-[#0d9488]">ا</span><div><b className="block text-xs">احمد رضایی</b><small className="text-[9px] text-[#829592]">مراجعه تأییدشده</small></div></div><div className="flex gap-0.5 text-[#f4b83e]">{Array.from({length:5}).map((_,i)=><Star key={i} size={13} className="fill-current" />)}</div></div><p className="mt-3 text-xs leading-6 text-[#607976]">پزشک بسیار دقیق و خوش‌برخوردی هستند. برای معاینه وقت کافی گذاشتند و روند درمان را کامل توضیح دادند.</p><span className="mt-3 flex items-center gap-1 text-[9px] font-bold text-[#0d9488]"><CheckCircle2 size={12} />این نظر پس از مراجعه ثبت شده است</span></div></section>
          </div>

          <aside className="sticky top-24 rounded-[24px] border border-[#dceae7] bg-white p-5 shadow-[0_18px_45px_rgba(8,77,72,.08)]"><BookingWidget doctorId={item.id} price={item.visitPrice} supportsOnline={item.isOnline} isLoggedIn={Boolean(user)} defaultName={user?.fullName} defaultPhone={user?.phone} /></aside>
        </div>

        {similar.length > 0 && <section className="site-container pb-16"><div className="mb-5"><h2 className="text-xl font-black">پزشکان پیشنهادی دیگر</h2><p className="mt-1 text-xs text-[#718784]">گزینه‌های دیگری که می‌توانید بررسی کنید</p></div><div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">{similar.map((entry)=><DoctorCard key={entry.id} doctor={entry} />)}</div></section>}
      </main>
      <SiteFooter />
      <script
        type="application/ld+json"
        dangerouslySetInnerHTML={{
          __html: JSON.stringify({
            "@context": "https://schema.org",
            "@type": "Physician",
            name: item.fullName,
            image: item.imageUrl,
            url: `https://nobatesalamat.ir/doctors/${item.slug}`,
            description: item.about,
            medicalSpecialty: doctor.specialtyTitle,
            priceRange: item.visitPrice > 0 ? `${item.visitPrice} IRR thousand` : undefined,
            address: {
              "@type": "PostalAddress",
              addressLocality: item.city,
              streetAddress: item.address,
              addressCountry: "IR",
            },
            aggregateRating: {
              "@type": "AggregateRating",
              ratingValue: item.rating,
              reviewCount: item.reviewsCount,
              bestRating: 5,
              worstRating: 1,
            },
          }),
        }}
      />
      <script
        type="application/ld+json"
        dangerouslySetInnerHTML={{
          __html: JSON.stringify({
            "@context": "https://schema.org",
            "@type": "BreadcrumbList",
            itemListElement: [
              { "@type": "ListItem", position: 1, name: "خانه", item: "https://nobatesalamat.ir" },
              { "@type": "ListItem", position: 2, name: "پزشکان", item: "https://nobatesalamat.ir/doctors" },
              { "@type": "ListItem", position: 3, name: doctor.specialtyTitle, item: `https://nobatesalamat.ir/doctors?specialty=${doctor.specialtySlug}` },
              { "@type": "ListItem", position: 4, name: item.fullName, item: `https://nobatesalamat.ir/doctors/${item.slug}` },
            ],
          }),
        }}
      />
    </>
  );
}
