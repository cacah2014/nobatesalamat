"use client";

import Link from "next/link";
import { CalendarDays, CheckCircle2, ChevronLeft, LoaderCircle, LockKeyhole, Video } from "lucide-react";
import { useMemo, useState } from "react";
import { formatPrice } from "@/lib/format";

type Props = {
  doctorId: number;
  price: number;
  supportsOnline: boolean;
  isLoggedIn: boolean;
  defaultName?: string;
  defaultPhone?: string;
};

export function BookingWidget({ doctorId, price, supportsOnline, isLoggedIn, defaultName = "", defaultPhone = "" }: Props) {
  const dates = useMemo(() => Array.from({ length: 5 }, (_, index) => {
    const date = new Date();
    date.setDate(date.getDate() + index);
    return {
      value: new Intl.DateTimeFormat("fa-IR-u-ca-persian", { year: "numeric", month: "2-digit", day: "2-digit" }).format(date),
      weekday: index === 0 ? "امروز" : new Intl.DateTimeFormat("fa-IR", { weekday: "short" }).format(date),
      day: new Intl.DateTimeFormat("fa-IR", { day: "numeric" }).format(date),
    };
  }), []);
  const [date, setDate] = useState(dates[0].value);
  const [time, setTime] = useState("18:30");
  const [visitType, setVisitType] = useState<"in_person" | "online">("in_person");
  const [name, setName] = useState(defaultName === "کاربر نوبت سلامت" ? "" : defaultName);
  const [phone, setPhone] = useState(defaultPhone);
  const [note, setNote] = useState("");
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");
  const [trackingCode, setTrackingCode] = useState("");

  async function book() {
    setError("");
    setLoading(true);
    try {
      const response = await fetch("/api/appointments", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ doctorId, appointmentDate: date, appointmentTime: time, visitType, patientName: name, patientPhone: phone, note }),
      });
      const data = await response.json();
      if (response.status === 401) {
        window.location.href = "/login";
        return;
      }
      if (!response.ok) throw new Error(data.error || "ثبت نوبت انجام نشد.");
      setTrackingCode(data.trackingCode);
    } catch (bookingError) {
      setError(bookingError instanceof Error ? bookingError.message : "خطایی رخ داده است.");
    } finally {
      setLoading(false);
    }
  }

  if (trackingCode) {
    return (
      <div className="rounded-[22px] border border-emerald-200 bg-emerald-50 p-6 text-center">
        <span className="mx-auto grid size-14 place-items-center rounded-full bg-emerald-100 text-emerald-600"><CheckCircle2 size={28} /></span>
        <h3 className="mt-4 font-black text-emerald-900">نوبت شما با موفقیت ثبت شد</h3>
        <p className="mt-2 text-xs leading-6 text-emerald-800">کد رهگیری: <b dir="ltr" className="text-sm">{trackingCode}</b></p>
        <Link href="/panel" className="primary-button mt-5 !py-2.5 text-xs">مشاهده در پنل من <ChevronLeft size={15} /></Link>
      </div>
    );
  }

  return (
    <div>
      <div className="mb-4 flex items-center justify-between"><h2 className="font-black">انتخاب زمان نوبت</h2><span className="rounded-lg bg-[#e9f8f4] px-2 py-1 text-[10px] font-bold text-[#0d9488]">نوبت قطعی</span></div>
      <div className="grid grid-cols-5 gap-1.5">
        {dates.map((item) => <button key={item.value} type="button" onClick={() => setDate(item.value)} className={`rounded-xl border px-1 py-2.5 text-center transition ${date === item.value ? "border-[#0d9488] bg-[#0d9488] text-white" : "border-[#e0ebe9] bg-white text-[#607976] hover:border-[#90cbc3]"}`}><small className="block text-[9px]">{item.weekday}</small><b className="mt-1 block text-sm">{item.day}</b></button>)}
      </div>
      <div className="mt-4 grid grid-cols-4 gap-2">
        {["16:00", "17:15", "18:30", "19:45"].map((item) => <button key={item} type="button" onClick={() => setTime(item)} dir="ltr" className={`rounded-xl border py-2 text-xs font-bold transition ${time === item ? "border-[#ff6b57] bg-[#fff0ed] text-[#d85843]" : "border-[#e0ebe9] text-[#607976] hover:border-[#f3b5a9]"}`}>{item}</button>)}
      </div>

      {supportsOnline && <div className="mt-4 grid grid-cols-2 gap-2"><button type="button" onClick={() => setVisitType("in_person")} className={`rounded-xl border p-2.5 text-xs font-bold ${visitType === "in_person" ? "border-[#0d9488] bg-[#edf8f5] text-[#0d9488]" : "border-[#e0ebe9]"}`}>ویزیت حضوری</button><button type="button" onClick={() => setVisitType("online")} className={`flex items-center justify-center gap-1 rounded-xl border p-2.5 text-xs font-bold ${visitType === "online" ? "border-[#0d9488] bg-[#edf8f5] text-[#0d9488]" : "border-[#e0ebe9]"}`}><Video size={15} />مشاوره آنلاین</button></div>}

      {isLoggedIn ? <div className="mt-5 space-y-3 border-t border-[#edf2f1] pt-4"><div><label className="form-label">نام و نام خانوادگی بیمار</label><input className="form-input !py-2.5 text-xs" value={name} onChange={(event) => setName(event.target.value)} placeholder="نام بیمار" /></div><div><label className="form-label">شماره موبایل بیمار</label><input className="form-input !py-2.5 text-xs" dir="ltr" value={phone} onChange={(event) => setPhone(event.target.value)} placeholder="09121234567" /></div><div><label className="form-label">توضیح برای پزشک (اختیاری)</label><textarea className="form-input min-h-16 resize-none text-xs" value={note} onChange={(event) => setNote(event.target.value)} placeholder="شرح کوتاه مشکل یا علائم" /></div></div> : <div className="mt-5 rounded-xl bg-[#fff8e9] p-3 text-xs leading-6 text-[#765b23]"><LockKeyhole className="ml-1 inline" size={15} />برای ثبت نوبت، ابتدا با شماره موبایل وارد شوید.</div>}

      <div className="mt-5 flex items-center justify-between border-t border-[#edf2f1] pt-4"><div><small className="block text-[9px] text-[#7d918e]">مبلغ قابل پرداخت</small><b className="text-sm">{formatPrice(price)}</b></div>{isLoggedIn ? <button onClick={book} disabled={loading || !name || !phone} className="primary-button !px-5 !py-2.5 text-xs">{loading ? <LoaderCircle className="animate-spin" size={16} /> : <CalendarDays size={16} />}ثبت نهایی نوبت</button> : <Link href="/login" className="primary-button !px-5 !py-2.5 text-xs">ورود و ادامه</Link>}</div>
      {error && <p className="mt-3 rounded-xl bg-red-50 p-2.5 text-xs font-bold text-red-600">{error}</p>}
    </div>
  );
}
