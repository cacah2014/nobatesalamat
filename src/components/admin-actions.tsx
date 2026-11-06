"use client";

import { FormEvent, useState } from "react";
import { useRouter } from "next/navigation";
import {
  CalendarDays,
  Check,
  ChevronDown,
  CircleAlert,
  Database,
  FilePenLine,
  GitPullRequest,
  KeyRound,
  LoaderCircle,
  PackageCheck,
  Play,
  RefreshCw,
  Save,
  ShieldPlus,
  Stethoscope,
  Trash2,
  UploadCloud,
  UserPlus,
} from "lucide-react";

type Setting = { key: string; value: string; label: string; group: string };
type Admin = { id: number; fullName: string; phone: string; role: string; createdAt: Date; permissions: string[] };
type Specialty = { id: number; title: string };
type Doctor = {
  id: number; fullName: string; title: string; specialtyId: number; city: string; address: string;
  visitPrice: number; experienceYears: number; medicalCode: string; imageUrl: string; about: string;
  nextAvailable: string; isOnline: boolean; isActive: boolean;
};

const PERMISSION_OPTIONS = [
  { key: "content", label: "ویرایش محتوای سایت", description: "متن‌ها، آمار و اطلاعات تماس صفحات", icon: FilePenLine },
  { key: "doctors", label: "مدیریت پزشکان", description: "افزودن، ویرایش و غیرفعال‌سازی پزشک", icon: Stethoscope },
  { key: "appointments", label: "مدیریت نوبت‌ها", description: "مشاهده و تغییر وضعیت رزروها", icon: CalendarDays },
  { key: "updates", label: "به‌روزرسانی پروژه", description: "اجرای git pull و فرایند چهارمرحله‌ای آپدیت", icon: RefreshCw },
] as const;

function Notice({ message, error = false }: { message: string; error?: boolean }) {
  if (!message) return null;
  return (
    <p className={`mt-3 rounded-xl px-3 py-2 text-[10px] font-bold ${error ? "bg-red-50 text-red-600" : "bg-emerald-50 text-emerald-700"}`}>
      {message}
    </p>
  );
}

/* ----------------------------- محتوای سایت ----------------------------- */

export function SettingsForm({ rows }: { rows: Setting[] }) {
  const router = useRouter();
  const [values, setValues] = useState<Record<string, string>>(Object.fromEntries(rows.map((row) => [row.key, row.value])));
  const [loading, setLoading] = useState(false);
  const [message, setMessage] = useState("");

  async function save(event: FormEvent) {
    event.preventDefault();
    setLoading(true);
    setMessage("");
    const response = await fetch("/api/admin/settings", {
      method: "PATCH",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ settings: values }),
    });
    const data = await response.json();
    setLoading(false);
    setMessage(response.ok ? "تغییرات سایت منتشر شد." : data.error || "ذخیره انجام نشد.");
    if (response.ok) router.refresh();
  }

  const groups = Object.groupBy(rows, (row) => row.group);
  const groupNames: Record<string, string> = { general: "تنظیمات عمومی", homepage: "صفحه اصلی", footer: "فوتر", social: "شبکه‌های اجتماعی" };

  return (
    <form onSubmit={save} className="space-y-6">
      {Object.entries(groups).map(([group, items]) => (
        <fieldset key={group} className="rounded-[20px] border border-[#e0ebe9] bg-white p-5">
          <legend className="px-2 text-sm font-black text-[#173f3d]">{groupNames[group] || group}</legend>
          <div className="grid gap-4 sm:grid-cols-2">
            {items?.map((row) => (
              <label key={row.key} className={row.value.length > 80 ? "sm:col-span-2" : ""}>
                <span className="form-label">{row.label}</span>
                {row.value.length > 80 ? (
                  <textarea value={values[row.key]} onChange={(event) => setValues({ ...values, [row.key]: event.target.value })} className="form-input min-h-24 resize-y text-xs" />
                ) : (
                  <input value={values[row.key]} onChange={(event) => setValues({ ...values, [row.key]: event.target.value })} className="form-input text-xs" />
                )}
              </label>
            ))}
          </div>
        </fieldset>
      ))}
      <div className="flex items-center justify-end gap-3">
        <Notice message={message} error={message.includes("نشد") || message.includes("ندارید")} />
        <button disabled={loading} className="primary-button text-xs">
          {loading ? <LoaderCircle className="animate-spin" size={16} /> : <Save size={16} />}ذخیره و انتشار
        </button>
      </div>
    </form>
  );
}

/* ------------------------------ مدیران ------------------------------ */

export function AdminManager({ admins, canManage }: { admins: Admin[]; canManage: boolean }) {
  const router = useRouter();
  const [phone, setPhone] = useState("");
  const [fullName, setFullName] = useState("");
  const [loading, setLoading] = useState(false);
  const [message, setMessage] = useState("");

  async function add(event: FormEvent) {
    event.preventDefault();
    setLoading(true);
    setMessage("");
    const response = await fetch("/api/admin/users", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ phone, fullName }),
    });
    const data = await response.json();
    setLoading(false);
    setMessage(response.ok ? "مدیر جدید اضافه شد؛ حالا دسترسی‌های او را تعیین کنید." : data.error || "عملیات ناموفق بود.");
    if (response.ok) {
      setPhone("");
      setFullName("");
      router.refresh();
    }
  }

  async function remove(id: number) {
    if (!confirm("دسترسی مدیریت این شخص به‌طور کامل حذف شود؟")) return;
    const response = await fetch(`/api/admin/users/${id}`, { method: "DELETE" });
    const data = await response.json();
    setMessage(response.ok ? "دسترسی مدیر حذف شد." : data.error || "حذف انجام نشد.");
    if (response.ok) router.refresh();
  }

  return (
    <div className="grid items-start gap-5 lg:grid-cols-[1fr_340px]">
      <div className="rounded-[20px] border border-[#e0ebe9] bg-white p-5">
        <div className="mb-4 flex items-center justify-between border-b border-[#edf2f1] pb-3">
          <h3 className="text-sm font-black">مدیران فعال و سطح دسترسی</h3>
          <span className="rounded-lg bg-[#e9f8f4] px-2 py-1 text-[10px] font-bold text-[#0d9488]">{admins.length} نفر</span>
        </div>
        <div className="space-y-3">
          {admins.map((admin) => (
            <AdminAccessCard key={admin.id} admin={admin} canManage={canManage} onRemove={remove} />
          ))}
        </div>
      </div>

      <form onSubmit={add} className="rounded-[20px] border border-[#e0ebe9] bg-white p-5">
        <span className="mb-4 grid size-10 place-items-center rounded-xl bg-[#e9f8f4] text-[#0d9488]"><ShieldPlus size={20} /></span>
        <h3 className="text-sm font-black">افزودن مدیر جدید</h3>
        <p className="mt-1 text-[10px] leading-5 text-[#7b918e]">مدیر با شماره موبایل خود وارد می‌شود و فقط به بخش‌هایی دسترسی دارد که شما تعیین کنید.</p>
        {canManage ? (
          <div className="mt-4 space-y-3">
            <input className="form-input text-xs" placeholder="نام و نام خانوادگی" value={fullName} onChange={(e) => setFullName(e.target.value)} />
            <input className="form-input text-xs" dir="ltr" placeholder="09121234567" value={phone} onChange={(e) => setPhone(e.target.value)} />
            <button disabled={loading} className="primary-button w-full !py-2.5 text-xs">
              {loading ? <LoaderCircle className="animate-spin" size={15} /> : <UserPlus size={15} />}افزودن مدیر
            </button>
          </div>
        ) : (
          <p className="mt-4 rounded-xl bg-amber-50 p-3 text-[10px] leading-5 text-amber-700">فقط مدیر ارشد می‌تواند مدیر دیگری اضافه یا حذف کند و دسترسی‌ها را تعیین کند.</p>
        )}
        <Notice message={message} error={message.includes("نشد") || message.includes("فقط")} />
      </form>
    </div>
  );
}

function AdminAccessCard({ admin, canManage, onRemove }: { admin: Admin; canManage: boolean; onRemove: (id: number) => void }) {
  const router = useRouter();
  const [selected, setSelected] = useState<string[]>(admin.permissions);
  const [saving, setSaving] = useState(false);
  const [saved, setSaved] = useState(false);
  const isSuper = admin.role === "super_admin";

  function toggle(key: string) {
    setSelected((current) => (current.includes(key) ? current.filter((item) => item !== key) : [...current, key]));
    setSaved(false);
  }

  async function savePermissions() {
    setSaving(true);
    const response = await fetch(`/api/admin/users/${admin.id}`, {
      method: "PATCH",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ permissions: selected }),
    });
    setSaving(false);
    setSaved(response.ok);
    if (response.ok) router.refresh();
  }

  return (
    <div className="rounded-2xl border border-[#e1ecea] bg-white">
      <div className="flex items-center gap-3 p-3.5">
        <span className={`grid size-10 place-items-center rounded-xl font-black ${isSuper ? "bg-gradient-to-br from-amber-400 to-orange-500 text-white" : "bg-[#e9f8f4] text-[#0d9488]"}`}>
          {admin.fullName.slice(0, 1)}
        </span>
        <div className="min-w-0 flex-1">
          <b className="block truncate text-xs">{admin.fullName}</b>
          <span dir="ltr" className="block text-right text-[10px] text-[#7b918e]">{admin.phone}</span>
        </div>
        <span className={`rounded-full px-2 py-1 text-[9px] font-bold ${isSuper ? "bg-amber-50 text-amber-700" : "bg-[#edf8f5] text-[#0d9488]"}`}>
          {isSuper ? "مدیر ارشد" : "مدیر"}
        </span>
        {canManage && !isSuper && (
          <button onClick={() => onRemove(admin.id)} className="grid size-8 shrink-0 place-items-center rounded-lg text-red-400 transition hover:bg-red-50 hover:text-red-600" aria-label="حذف دسترسی مدیر">
            <Trash2 size={15} />
          </button>
        )}
      </div>

      {isSuper ? (
        <div className="mx-3.5 mb-3.5 flex items-center gap-2 rounded-xl bg-gradient-to-l from-emerald-50 to-teal-50 px-3 py-2.5 text-[10px] font-bold text-emerald-700">
          <KeyRound size={14} /> دسترسی کامل به همهٔ بخش‌های سایت
        </div>
      ) : (
        <div className="mx-3.5 mb-3.5 rounded-xl border border-[#edf2f1] bg-[#fafcfb] p-3">
          <p className="mb-2.5 text-[9px] font-black text-[#718784]">بخش‌های مجاز برای این مدیر</p>
          <div className="grid gap-2 sm:grid-cols-2">
            {PERMISSION_OPTIONS.map((option) => {
              const active = selected.includes(option.key);
              const Icon = option.icon;
              return (
                <button
                  key={option.key}
                  type="button"
                  disabled={!canManage}
                  onClick={() => toggle(option.key)}
                  className={`flex items-center gap-2.5 rounded-xl border p-2.5 text-right transition ${
                    active ? "border-[#0d9488] bg-[#e9f8f4]" : "border-[#e5eeec] bg-white hover:border-[#b6d9d4]"
                  } ${!canManage ? "cursor-not-allowed opacity-70" : ""}`}
                >
                  <span className={`grid size-8 shrink-0 place-items-center rounded-lg ${active ? "bg-[#0d9488] text-white" : "bg-[#eef3f2] text-[#7b918e]"}`}>
                    <Icon size={15} />
                  </span>
                  <span className="min-w-0 flex-1">
                    <b className={`block text-[10px] ${active ? "text-[#0b7c71]" : "text-[#526f6c]"}`}>{option.label}</b>
                    <small className="block truncate text-[8px] text-[#93a6a3]">{option.description}</small>
                  </span>
                  <span className={`grid size-4 shrink-0 place-items-center rounded-full border ${active ? "border-[#0d9488] bg-[#0d9488] text-white" : "border-[#cbd9d7] text-transparent"}`}>
                    <Check size={10} strokeWidth={3} />
                  </span>
                </button>
              );
            })}
          </div>
          {canManage && (
            <div className="mt-3 flex items-center gap-2">
              <button onClick={savePermissions} disabled={saving} className="primary-button !px-4 !py-2 text-[10px]">
                {saving ? <LoaderCircle className="animate-spin" size={13} /> : <Save size={13} />}ذخیره دسترسی‌ها
              </button>
              {saved && <span className="flex items-center gap-1 text-[9px] font-bold text-emerald-600"><Check size={12} />دسترسی‌ها به‌روز شد</span>}
              {!canManage && null}
              {selected.length === 0 && <span className="text-[9px] text-[#93a6a3]">بدون دسترسی: فقط «نمای کلی»</span>}
            </div>
          )}
        </div>
      )}
    </div>
  );
}

/* ------------------------------ پزشکان ------------------------------ */

const emptyDoctor = { fullName: "", title: "", specialtyId: 0, city: "تهران", address: "", visitPrice: 250000, experienceYears: 5, medicalCode: "", imageUrl: "", about: "", nextAvailable: "امروز", isOnline: false };

export function DoctorManager({ doctors, specialties }: { doctors: Doctor[]; specialties: Specialty[] }) {
  const router = useRouter();
  const [form, setForm] = useState({ ...emptyDoctor, specialtyId: specialties[0]?.id || 0 });
  const [loading, setLoading] = useState(false);
  const [message, setMessage] = useState("");

  async function create(event: FormEvent) {
    event.preventDefault();
    setLoading(true);
    setMessage("");
    const response = await fetch("/api/admin/doctors", { method: "POST", headers: { "Content-Type": "application/json" }, body: JSON.stringify(form) });
    const data = await response.json();
    setLoading(false);
    setMessage(response.ok ? "پزشک جدید اضافه شد." : data.error || "ثبت انجام نشد.");
    if (response.ok) {
      setForm({ ...emptyDoctor, specialtyId: specialties[0]?.id || 0 });
      router.refresh();
    }
  }

  async function update(id: number, values: Partial<Doctor>) {
    const response = await fetch(`/api/admin/doctors/${id}`, { method: "PATCH", headers: { "Content-Type": "application/json" }, body: JSON.stringify(values) });
    const data = await response.json();
    setMessage(response.ok ? "اطلاعات پزشک به‌روز شد." : data.error || "ویرایش انجام نشد.");
    if (response.ok) router.refresh();
  }

  async function remove(id: number) {
    if (!confirm("این پزشک از سایت غیرفعال شود؟")) return;
    await fetch(`/api/admin/doctors/${id}`, { method: "DELETE" });
    router.refresh();
  }

  function input<K extends keyof typeof form>(key: K, value: (typeof form)[K]) {
    setForm({ ...form, [key]: value });
  }

  return (
    <div className="space-y-5">
      <details className="rounded-[20px] border border-[#cfe4e0] bg-white p-5" open>
        <summary className="flex cursor-pointer list-none items-center justify-between text-sm font-black">
          <span className="flex items-center gap-2"><UserPlus size={18} className="text-[#0d9488]" />افزودن پزشک جدید</span>
          <ChevronDown size={17} />
        </summary>
        <form onSubmit={create} className="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
          <input className="form-input text-xs" placeholder="نام کامل پزشک" value={form.fullName} onChange={(e) => input("fullName", e.target.value)} />
          <input className="form-input text-xs" placeholder="عنوان تخصصی" value={form.title} onChange={(e) => input("title", e.target.value)} />
          <select className="form-input text-xs" value={form.specialtyId} onChange={(e) => input("specialtyId", Number(e.target.value))}>
            {specialties.map((s) => <option key={s.id} value={s.id}>{s.title}</option>)}
          </select>
          <input className="form-input text-xs" placeholder="شهر" value={form.city} onChange={(e) => input("city", e.target.value)} />
          <input className="form-input text-xs" placeholder="شماره نظام پزشکی" value={form.medicalCode} onChange={(e) => input("medicalCode", e.target.value)} />
          <input className="form-input text-xs" type="number" placeholder="هزینه ویزیت" value={form.visitPrice} onChange={(e) => input("visitPrice", Number(e.target.value))} />
          <input className="form-input text-xs" type="number" placeholder="سابقه" value={form.experienceYears} onChange={(e) => input("experienceYears", Number(e.target.value))} />
          <input className="form-input text-xs" placeholder="اولین نوبت" value={form.nextAvailable} onChange={(e) => input("nextAvailable", e.target.value)} />
          <input className="form-input text-xs" dir="ltr" placeholder="آدرس URL تصویر" value={form.imageUrl} onChange={(e) => input("imageUrl", e.target.value)} />
          <input className="form-input text-xs sm:col-span-2" placeholder="آدرس مطب" value={form.address} onChange={(e) => input("address", e.target.value)} />
          <label className="flex items-center gap-2 rounded-xl border border-[#e0ebe9] px-3 text-xs">
            <input type="checkbox" checked={form.isOnline} onChange={(e) => input("isOnline", e.target.checked)} />مشاوره آنلاین
          </label>
          <textarea className="form-input min-h-20 text-xs sm:col-span-2 lg:col-span-3" placeholder="درباره پزشک" value={form.about} onChange={(e) => input("about", e.target.value)} />
          <div className="flex items-center gap-3 lg:col-span-3">
            <button disabled={loading} className="primary-button text-xs">{loading ? <LoaderCircle className="animate-spin" size={15} /> : <UserPlus size={15} />}ثبت پزشک</button>
            <Notice message={message} />
          </div>
        </form>
      </details>
      <div className="space-y-3">
        {doctors.map((doctor) => <DoctorEditor key={doctor.id} doctor={doctor} specialties={specialties} onUpdate={update} onRemove={remove} />)}
      </div>
    </div>
  );
}

function DoctorEditor({ doctor, specialties, onUpdate, onRemove }: { doctor: Doctor; specialties: Specialty[]; onUpdate: (id: number, v: Partial<Doctor>) => Promise<void>; onRemove: (id: number) => void }) {
  const [value, setValue] = useState(doctor);
  const [saving, setSaving] = useState(false);
  async function save() {
    setSaving(true);
    await onUpdate(doctor.id, value);
    setSaving(false);
  }
  return (
    <details className="rounded-[18px] border border-[#e0ebe9] bg-white">
      <summary className="flex cursor-pointer list-none items-center gap-3 p-3.5">
        <img src={doctor.imageUrl} alt="" className="size-11 rounded-xl bg-[#eaf2f0] object-cover object-top" />
        <div className="min-w-0 flex-1">
          <b className="block truncate text-xs">{doctor.fullName}</b>
          <small className="text-[9px] text-[#7b918e]">{doctor.title} · {doctor.city}</small>
        </div>
        <span className={`rounded-full px-2 py-1 text-[9px] font-bold ${doctor.isActive ? "bg-emerald-50 text-emerald-600" : "bg-red-50 text-red-500"}`}>{doctor.isActive ? "فعال" : "غیرفعال"}</span>
        <ChevronDown size={16} />
      </summary>
      <div className="grid gap-3 border-t border-[#edf2f1] p-4 sm:grid-cols-2 lg:grid-cols-3">
        <input className="form-input text-xs" value={value.fullName} onChange={(e) => setValue({ ...value, fullName: e.target.value })} />
        <input className="form-input text-xs" value={value.title} onChange={(e) => setValue({ ...value, title: e.target.value })} />
        <select className="form-input text-xs" value={value.specialtyId} onChange={(e) => setValue({ ...value, specialtyId: Number(e.target.value) })}>
          {specialties.map((s) => <option key={s.id} value={s.id}>{s.title}</option>)}
        </select>
        <input className="form-input text-xs" value={value.city} onChange={(e) => setValue({ ...value, city: e.target.value })} />
        <input className="form-input text-xs" value={value.medicalCode} onChange={(e) => setValue({ ...value, medicalCode: e.target.value })} />
        <input className="form-input text-xs" type="number" value={value.visitPrice} onChange={(e) => setValue({ ...value, visitPrice: Number(e.target.value) })} />
        <input className="form-input text-xs" value={value.nextAvailable} onChange={(e) => setValue({ ...value, nextAvailable: e.target.value })} />
        <input className="form-input text-xs" dir="ltr" value={value.imageUrl} onChange={(e) => setValue({ ...value, imageUrl: e.target.value })} />
        <label className="flex items-center gap-2 rounded-xl border border-[#e0ebe9] px-3 text-xs">
          <input type="checkbox" checked={value.isOnline} onChange={(e) => setValue({ ...value, isOnline: e.target.checked })} />ویزیت آنلاین
        </label>
        <textarea className="form-input min-h-16 text-xs sm:col-span-2 lg:col-span-3" value={value.about} onChange={(e) => setValue({ ...value, about: e.target.value })} />
        <div className="flex gap-2 lg:col-span-3">
          <button onClick={save} disabled={saving} className="primary-button !py-2 text-xs">{saving ? <LoaderCircle className="animate-spin" size={14} /> : <Save size={14} />}ذخیره</button>
          <button onClick={() => onRemove(doctor.id)} className="secondary-button !py-2 text-xs !text-red-500"><Trash2 size={14} />غیرفعال‌سازی</button>
        </div>
      </div>
    </details>
  );
}

/* ------------------------------ نوبت‌ها ------------------------------ */

export function AppointmentStatus({ id, initial }: { id: number; initial: string }) {
  const router = useRouter();
  const [loading, setLoading] = useState(false);
  async function change(status: string) {
    setLoading(true);
    await fetch(`/api/admin/appointments/${id}`, { method: "PATCH", headers: { "Content-Type": "application/json" }, body: JSON.stringify({ status }) });
    setLoading(false);
    router.refresh();
  }
  return (
    <div className="relative">
      {loading && <LoaderCircle className="absolute left-2 top-2.5 animate-spin" size={13} />}
      <select defaultValue={initial} onChange={(e) => change(e.target.value)} className="rounded-lg border border-[#dde9e7] bg-white px-2 py-1.5 text-[9px] font-bold outline-none">
        <option value="confirmed">تأییدشده</option>
        <option value="completed">انجام‌شده</option>
        <option value="cancelled">لغوشده</option>
      </select>
    </div>
  );
}

/* ---------------------------- به‌روزرسانی ---------------------------- */

export function UpdatePanel({ enabled }: { enabled: boolean }) {
  const [running, setRunning] = useState(false);
  const [result, setResult] = useState<{ ok: boolean; message: string; output: string[] } | null>(null);
  const steps = [
    [PackageCheck, "۱. Dependency", "نصب و هماهنگ‌سازی پکیج‌ها"],
    [Database, "۲. Database", "اعمال تغییرات ساختار دیتابیس"],
    [UploadCloud, "۳. Code", "ساخت نسخه بهینه کد"],
    [RefreshCw, "۴. Update", "فعال‌سازی نسخه جدید"],
  ] as const;

  async function update() {
    if (!confirm("ابتدا git pull و سپس چهار مرحله به‌روزرسانی اجرا شود؟")) return;
    setRunning(true);
    setResult(null);
    try {
      const response = await fetch("/api/admin/update", { method: "POST" });
      const data = await response.json();
      setResult({ ok: response.ok, message: response.ok ? "به‌روزرسانی با موفقیت کامل شد." : data.error || "به‌روزرسانی ناموفق بود.", output: data.output || [] });
    } catch {
      setResult({ ok: false, message: "ارتباط با سرور قطع شد.", output: [] });
    } finally {
      setRunning(false);
    }
  }

  return (
    <div className="space-y-5">
      <div className="rounded-[22px] border border-[#dce9e7] bg-white p-6">
        <div className="flex items-start gap-4">
          <span className="grid size-12 shrink-0 place-items-center rounded-2xl bg-[#e9f8f4] text-[#0d9488]"><GitPullRequest size={24} /></span>
          <div>
            <h3 className="text-sm font-black">به‌روزرسانی امن از مخزن Git</h3>
            <p className="mt-1 text-[10px] leading-6 text-[#718784]">پیش از هر مرحله، دستور <code dir="ltr" className="rounded bg-[#edf2f1] px-1">git pull --ff-only</code> اجرا می‌شود.</p>
          </div>
        </div>
        <div className="mt-6 grid gap-3 sm:grid-cols-4">
          {steps.map(([Icon, title, text]) => (
            <div key={title} className={`rounded-2xl border p-3 ${running ? "border-[#9fd2cb] bg-[#f0faf7]" : "border-[#e5eeec]"}`}>
              <Icon size={19} className={running ? "animate-pulse text-[#0d9488]" : "text-[#718784]"} />
              <b className="mt-3 block text-[11px]">{title}</b>
              <small className="mt-1 block text-[9px] leading-4 text-[#849895]">{text}</small>
              {result?.ok && <Check className="mt-2 text-emerald-500" size={15} />}
            </div>
          ))}
        </div>
        {!enabled && (
          <div className="mt-5 flex gap-2 rounded-xl bg-amber-50 p-3 text-[10px] leading-5 text-amber-800">
            <CircleAlert className="shrink-0" size={17} />برای فعال‌سازی، متغیر محیطی ALLOW_WEB_UPDATES=true را روی سرور تنظیم کنید.
          </div>
        )}
        <button onClick={update} disabled={running || !enabled} className="primary-button mt-5 text-xs">
          {running ? <LoaderCircle className="animate-spin" size={16} /> : <Play size={16} />}
          {running ? "در حال اجرای به‌روزرسانی..." : "شروع به‌روزرسانی پروژه"}
        </button>
        {result && <Notice message={result.message} error={!result.ok} />}
      </div>
      {result && result.output.length > 0 && (
        <pre dir="ltr" className="max-h-96 overflow-auto whitespace-pre-wrap rounded-[18px] bg-[#102c2b] p-4 text-left text-[10px] leading-5 text-[#b8e0da]">
          {result.output.join("\n\n")}
        </pre>
      )}
    </div>
  );
}
