import type { SQL } from "drizzle-orm";
import Link from "next/link";
import { and, asc, eq, ilike, or } from "drizzle-orm";
import { Filter, Search, SlidersHorizontal, Stethoscope } from "lucide-react";
import { db } from "@/db";
import { doctors, specialties } from "@/db/schema";
import { ensureSeedData } from "@/lib/seed";
import { faNumber } from "@/lib/format";
import { DoctorCard } from "@/components/doctor-card";
import { SiteHeader } from "@/components/site-header";
import { SiteFooter } from "@/components/site-footer";

export const metadata = {
  title: "جست‌وجو و رزرو نوبت پزشکان",
  description: "فهرست پزشکان متخصص و مورد اعتماد؛ جست‌وجو بر اساس تخصص و شهر، مشاهدهٔ امتیاز و نظرات بیماران و رزرو اولین نوبت آزاد، حضوری یا آنلاین.",
  alternates: { canonical: "/doctors" },
  openGraph: {
    title: "جست‌وجوی پزشکان | نوبت سلامت",
    description: "پزشک مناسب خود را بر اساس تخصص، شهر و نظرات واقعی بیماران پیدا کنید.",
    url: "https://nobatesalamat.ir/doctors",
  },
};
export const dynamic = "force-dynamic";

type SearchParams = Promise<Record<string, string | string[] | undefined>>;

export default async function DoctorsPage({ searchParams }: { searchParams: SearchParams }) {
  await ensureSeedData();
  const params = await searchParams;
  const q = typeof params.q === "string" ? params.q.trim() : "";
  const city = typeof params.city === "string" ? params.city : "";
  const specialty = typeof params.specialty === "string" ? params.specialty : "";
  const visit = typeof params.visit === "string" ? params.visit : "";
  const conditions: SQL[] = [eq(doctors.isActive, true), eq(specialties.isActive, true)];
  if (q) conditions.push(or(ilike(doctors.fullName, `%${q}%`), ilike(doctors.title, `%${q}%`), ilike(specialties.title, `%${q}%`))!);
  if (city) conditions.push(eq(doctors.city, city));
  if (specialty) conditions.push(eq(specialties.slug, specialty));
  if (visit === "online") conditions.push(eq(doctors.isOnline, true));

  const [doctorRows, specialtyRows] = await Promise.all([
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
      .where(and(...conditions))
      .orderBy(asc(doctors.id)),
    db.select().from(specialties).where(eq(specialties.isActive, true)).orderBy(asc(specialties.sortOrder)),
  ]);

  return (
    <>
      <SiteHeader />
      <main className="min-h-[70vh]">
        <section className="border-b border-[#dceae7] bg-[#e9f8f4] py-9">
          <div className="site-container">
            <span className="text-xs font-black text-[#ff6b57]">پیدا کردن پزشک</span>
            <h1 className="mt-2 text-2xl font-black text-[#173f3d] sm:text-3xl">پزشک مناسب شما</h1>
            <p className="mt-2 text-sm text-[#68807e]">بین پزشکان معتبر جست‌وجو و نزدیک‌ترین نوبت را رزرو کنید.</p>
            <form className="soft-shadow mt-6 grid gap-2 rounded-2xl bg-white p-2 sm:grid-cols-[1fr_170px_auto]">
              <label className="flex items-center gap-2.5 rounded-xl bg-[#f7faf9] px-3"><Search size={19} className="text-[#0d9488]" /><input name="q" defaultValue={q} className="min-h-12 w-full bg-transparent text-xs font-bold outline-none" placeholder="نام پزشک یا تخصص" /></label>
              <select name="city" defaultValue={city} className="min-h-12 rounded-xl border border-[#e2ecea] bg-white px-3 text-xs font-bold outline-none"><option value="">همه شهرها</option><option>تهران</option><option>شیراز</option><option>اصفهان</option></select>
              {specialty && <input type="hidden" name="specialty" value={specialty} />}
              {visit && <input type="hidden" name="visit" value={visit} />}
              <button className="primary-button !rounded-xl"><Search size={17} />جست‌وجو</button>
            </form>
          </div>
        </section>

        <section className="site-container grid gap-7 py-10 lg:grid-cols-[230px_1fr]">
          <aside>
            <div className="sticky top-28 rounded-[20px] border border-[#e0ebe9] bg-white p-4">
              <div className="mb-4 flex items-center gap-2 border-b border-[#edf2f1] pb-3"><SlidersHorizontal size={18} className="text-[#0d9488]" /><b className="text-sm">فیلتر نتایج</b></div>
              <p className="mb-2 text-[10px] font-black text-[#718784]">تخصص</p>
              <div className="space-y-1">
                <Link href={`/doctors${city ? `?city=${city}` : ""}`} className={`block rounded-xl px-3 py-2 text-xs ${!specialty ? "bg-[#e9f8f4] font-black text-[#0d9488]" : "text-[#526f6c] hover:bg-[#f5f9f8]"}`}>همه تخصص‌ها</Link>
                {specialtyRows.map((item) => <Link key={item.id} href={`/doctors?specialty=${item.slug}${city ? `&city=${city}` : ""}`} className={`block rounded-xl px-3 py-2 text-xs ${specialty === item.slug ? "bg-[#e9f8f4] font-black text-[#0d9488]" : "text-[#526f6c] hover:bg-[#f5f9f8]"}`}>{item.title}</Link>)}
              </div>
              <div className="mt-5 border-t border-[#edf2f1] pt-4">
                <Link href={visit === "online" ? "/doctors" : "/doctors?visit=online"} className={`flex items-center justify-between rounded-xl border px-3 py-2.5 text-xs font-bold ${visit === "online" ? "border-[#a6d8d1] bg-[#e9f8f4] text-[#0d9488]" : "border-[#e2ecea]"}`}><span>فقط مشاوره آنلاین</span><span className={`h-5 w-9 rounded-full p-0.5 ${visit === "online" ? "bg-[#0d9488]" : "bg-[#ccd9d7]"}`}><i className={`block size-4 rounded-full bg-white transition ${visit === "online" ? "mr-4" : ""}`} /></span></Link>
              </div>
            </div>
          </aside>

          <div>
            <div className="mb-5 flex items-center justify-between"><div><h2 className="font-black">پزشکان پیدا شده</h2><p className="mt-1 text-[10px] text-[#7b908d]">{faNumber.format(doctorRows.length)} پزشک مطابق جست‌وجوی شما</p></div><button className="secondary-button lg:hidden !px-3 !py-2 text-xs"><Filter size={15} />فیلتر</button></div>
            {doctorRows.length ? <div className="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">{doctorRows.map((doctor) => <DoctorCard key={doctor.id} doctor={doctor} />)}</div> : <div className="grid min-h-80 place-items-center rounded-[24px] border border-dashed border-[#cbdedb] bg-white p-8 text-center"><div><span className="mx-auto grid size-16 place-items-center rounded-2xl bg-[#edf8f5] text-[#0d9488]"><Stethoscope size={29} /></span><h3 className="mt-4 font-black">پزشکی پیدا نشد</h3><p className="mt-2 text-xs text-[#718784]">فیلترها را تغییر دهید یا عبارت دیگری جست‌وجو کنید.</p><Link href="/doctors" className="primary-button mt-5 !py-2.5 text-xs">پاک کردن فیلترها</Link></div></div>}
          </div>
        </section>
      </main>
      <SiteFooter />
    </>
  );
}
