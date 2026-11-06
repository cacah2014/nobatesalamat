import { db } from "@/db";
import { articles, doctors, siteSettings, specialties, users } from "@/db/schema";
import { eq } from "drizzle-orm";

const initialSettings = [
  ["site_name", "نوبت سلامت", "نام وب‌سایت", "general"],
  ["support_phone", "۰۲۱-۹۱۰۹۹۰۹۰", "شماره پشتیبانی", "general"],
  ["hero_badge", "همراه مطمئن سلامتی شما", "نشان بالای صفحه", "homepage"],
  ["hero_title", "پزشک مناسب، نزدیک‌تر از همیشه", "عنوان اصلی", "homepage"],
  ["hero_description", "از میان پزشکان معتبر جست‌وجو کنید، نظرات بیماران را بخوانید و نوبت خود را در چند دقیقه قطعی کنید.", "توضیح اصلی", "homepage"],
  ["stats_doctors", "+۲,۴۰۰", "تعداد پزشکان", "homepage"],
  ["stats_appointments", "+۱۸۰ هزار", "تعداد نوبت‌ها", "homepage"],
  ["stats_satisfaction", "۹۶٪", "رضایت کاربران", "homepage"],
  ["footer_description", "سامانه هوشمند انتخاب پزشک و دریافت نوبت آنلاین؛ همیشه و همه‌جا کنار شما.", "توضیح فوتر", "footer"],
  ["instagram", "nobatesalamat", "اینستاگرام", "social"],
  ["address", "تهران، میدان ونک، خیابان ملاصدرا", "نشانی دفتر", "general"],
] as const;

const initialSpecialties = [
  { title: "قلب و عروق", slug: "cardiology", icon: "heart", color: "rose", description: "فشار خون، آریتمی و سلامت قلب", sortOrder: 1 },
  { title: "پوست و مو", slug: "dermatology", icon: "sparkles", color: "amber", description: "درمان پوست، مو و زیبایی", sortOrder: 2 },
  { title: "روان‌شناسی", slug: "psychology", icon: "brain", color: "violet", description: "مشاوره فردی و سلامت روان", sortOrder: 3 },
  { title: "زنان و زایمان", slug: "gynecology", icon: "flower", color: "pink", description: "سلامت بانوان و بارداری", sortOrder: 4 },
  { title: "کودکان", slug: "pediatrics", icon: "baby", color: "sky", description: "رشد و سلامت کودک", sortOrder: 5 },
  { title: "ارتوپدی", slug: "orthopedics", icon: "bone", color: "blue", description: "مفاصل، ستون فقرات و آسیب‌ها", sortOrder: 6 },
  { title: "چشم‌پزشکی", slug: "ophthalmology", icon: "eye", color: "cyan", description: "بینایی و بیماری‌های چشم", sortOrder: 7 },
  { title: "داخلی", slug: "internal", icon: "stethoscope", color: "teal", description: "تشخیص و درمان بیماری‌های داخلی", sortOrder: 8 },
];

const initialDoctors = [
  {
    fullName: "دکتر آرمان فرهمند",
    slug: "dr-arman-farahmand",
    specialtySlug: "cardiology",
    title: "متخصص قلب و عروق و فشار خون",
    city: "تهران",
    address: "تهران، سعادت‌آباد، میدان کاج",
    rating: 4.9,
    reviewsCount: 286,
    visitPrice: 285000,
    experienceYears: 18,
    nextAvailable: "امروز، ساعت ۱۸:۳۰",
    imageUrl: "https://images.pexels.com/photos/37870065/pexels-photo-37870065.jpeg?auto=compress&cs=tinysrgb&fit=crop&h=800&w=620",
    isOnline: true,
    about: "عضو انجمن قلب ایران با بیش از ۱۸ سال تجربه در تشخیص و درمان بیماری‌های قلبی.",
    medicalCode: "۱۲۸۴۵۶",
  },
  {
    fullName: "دکتر نگار آذری",
    slug: "dr-negar-azari",
    specialtySlug: "dermatology",
    title: "متخصص پوست، مو و زیبایی",
    city: "تهران",
    address: "تهران، پاسداران، بوستان هفتم",
    rating: 4.8,
    reviewsCount: 194,
    visitPrice: 320000,
    experienceYears: 12,
    nextAvailable: "فردا، ساعت ۱۰:۰۰",
    imageUrl: "https://images.pexels.com/photos/6749765/pexels-photo-6749765.jpeg?auto=compress&cs=tinysrgb&fit=crop&h=800&w=620",
    isOnline: false,
    about: "پزشک متخصص پوست و مو با رویکرد درمانی مبتنی بر شواهد و تجربه درمان موفق مراجعان.",
    medicalCode: "۱۵۳۲۱۰",
  },
  {
    fullName: "دکتر سارا نیک‌پی",
    slug: "dr-sara-nikpey",
    specialtySlug: "psychology",
    title: "روان‌شناس بالینی و مشاور خانواده",
    city: "شیراز",
    address: "شیراز، بلوار چمران، مجتمع پزشکی چمران",
    rating: 4.9,
    reviewsCount: 321,
    visitPrice: 260000,
    experienceYears: 14,
    nextAvailable: "امروز، ساعت ۲۰:۰۰",
    imageUrl: "https://images.pexels.com/photos/32115905/pexels-photo-32115905.jpeg?auto=compress&cs=tinysrgb&fit=crop&h=800&w=620",
    isOnline: true,
    about: "روان‌شناس بالینی، درمانگر اضطراب و مشاور خانواده با جلسات حضوری و آنلاین.",
    medicalCode: "ر-۸۶۳۱",
  },
  {
    fullName: "دکتر فرزاد نادری",
    slug: "dr-farzad-naderi",
    specialtySlug: "orthopedics",
    title: "جراح و متخصص ارتوپدی",
    city: "اصفهان",
    address: "اصفهان، چهارباغ بالا، مجتمع سپهر",
    rating: 4.7,
    reviewsCount: 168,
    visitPrice: 295000,
    experienceYears: 16,
    nextAvailable: "شنبه، ساعت ۱۶:۱۵",
    imageUrl: "https://images.pexels.com/photos/6129573/pexels-photo-6129573.jpeg?auto=compress&cs=tinysrgb&fit=crop&h=800&w=620",
    isOnline: false,
    about: "جراح ارتوپدی با تمرکز بر درمان آسیب‌های ورزشی، زانو و ستون فقرات.",
    medicalCode: "۱۱۷۹۰۲",
  },
];

const initialArticles = [
  { title: "چطور پزشک مناسب خود را انتخاب کنیم؟", slug: "choose-right-doctor", excerpt: "۵ معیار ساده برای انتخاب آگاهانه پزشک و داشتن یک تجربه درمانی بهتر.", imageUrl: "https://images.pexels.com/photos/5452201/pexels-photo-5452201.jpeg?auto=compress&cs=tinysrgb&fit=crop&h=520&w=900", readTime: 6 },
  { title: "راهنمای چکاپ‌های ضروری سالانه", slug: "annual-checkups", excerpt: "آزمایش‌ها و بررسی‌هایی که متناسب با سن بهتر است هر سال انجام شوند.", imageUrl: "https://images.pexels.com/photos/4226256/pexels-photo-4226256.jpeg?auto=compress&cs=tinysrgb&fit=crop&h=520&w=900", readTime: 8 },
  { title: "ویزیت آنلاین برای چه مواقعی مناسب است؟", slug: "online-visit-guide", excerpt: "چه مشکلاتی را می‌توان با مشاوره آنلاین پیگیری کرد و چه زمانی باید حضوری مراجعه کنیم؟", imageUrl: "https://images.pexels.com/photos/7195310/pexels-photo-7195310.jpeg?auto=compress&cs=tinysrgb&fit=crop&h=520&w=900", readTime: 5 },
];

let seedPromise: Promise<void> | null = null;

async function runSeed() {
  const existingSettings = await db.select({ key: siteSettings.key }).from(siteSettings).limit(1);
  if (existingSettings.length === 0) {
    await db.insert(siteSettings).values(
      initialSettings.map(([key, value, label, group]) => ({ key, value, label, group })),
    );
  }

  const existingSpecialties = await db.select({ id: specialties.id }).from(specialties).limit(1);
  if (existingSpecialties.length === 0) {
    await db.insert(specialties).values(initialSpecialties);
  }

  const existingDoctors = await db.select({ id: doctors.id }).from(doctors).limit(1);
  if (existingDoctors.length === 0) {
    const specialtyRows = await db.select().from(specialties);
    const specialtyMap = new Map(specialtyRows.map((item) => [item.slug, item.id]));
    await db.insert(doctors).values(
      initialDoctors.map(({ specialtySlug, ...doctor }) => ({
        ...doctor,
        specialtyId: specialtyMap.get(specialtySlug)!,
      })),
    );
  }

  const existingArticles = await db.select({ id: articles.id }).from(articles).limit(1);
  if (existingArticles.length === 0) {
    await db.insert(articles).values(initialArticles);
  }

  const adminPhone = process.env.ADMIN_PHONE || "09120000000";
  const existingAdmin = await db.select().from(users).where(eq(users.phone, adminPhone)).limit(1);
  if (existingAdmin.length === 0) {
    await db.insert(users).values({
      phone: adminPhone,
      fullName: "مدیر ارشد نوبت سلامت",
      role: "super_admin",
      permissions: "[]",
    });
  }
}

export function ensureSeedData() {
  if (!seedPromise) seedPromise = runSeed().catch((error) => {
    seedPromise = null;
    throw error;
  });
  return seedPromise;
}
