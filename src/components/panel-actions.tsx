"use client";

import { LoaderCircle, Save, Trash2 } from "lucide-react";
import { useRouter } from "next/navigation";
import { useState } from "react";

export function ProfileForm({ initialName, phone }: { initialName: string; phone: string }) {
  const router = useRouter();
  const [name, setName] = useState(initialName === "کاربر نوبت سلامت" ? "" : initialName);
  const [loading, setLoading] = useState(false);
  const [message, setMessage] = useState("");

  async function save() {
    setLoading(true);
    setMessage("");
    const response = await fetch("/api/profile", { method: "PATCH", headers: { "Content-Type": "application/json" }, body: JSON.stringify({ fullName: name }) });
    const data = await response.json();
    setLoading(false);
    setMessage(response.ok ? "اطلاعات با موفقیت ذخیره شد." : data.error || "ذخیره انجام نشد.");
    if (response.ok) router.refresh();
  }

  return (
    <div className="space-y-3">
      <div><label className="form-label">نام و نام خانوادگی</label><input className="form-input text-xs" value={name} onChange={(event) => setName(event.target.value)} placeholder="نام شما" /></div>
      <div><label className="form-label">شماره موبایل</label><input className="form-input bg-[#f5f8f7] text-xs text-[#718784]" value={phone} dir="ltr" disabled /></div>
      <button onClick={save} disabled={loading || name.trim().length < 3} className="primary-button !px-4 !py-2.5 text-xs">{loading ? <LoaderCircle className="animate-spin" size={15} /> : <Save size={15} />}ذخیره تغییرات</button>
      {message && <p className="text-[10px] font-bold text-[#0d9488]">{message}</p>}
    </div>
  );
}

export function CancelAppointment({ id }: { id: number }) {
  const router = useRouter();
  const [loading, setLoading] = useState(false);
  async function cancel() {
    if (!window.confirm("آیا از لغو این نوبت مطمئن هستید؟")) return;
    setLoading(true);
    const response = await fetch(`/api/appointments/${id}`, { method: "DELETE" });
    setLoading(false);
    if (response.ok) router.refresh();
  }
  return <button onClick={cancel} disabled={loading} className="flex items-center gap-1 text-[10px] font-bold text-red-500 transition hover:text-red-700">{loading ? <LoaderCircle className="animate-spin" size={13} /> : <Trash2 size={13} />}لغو نوبت</button>;
}
