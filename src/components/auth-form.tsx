"use client";

import { FormEvent, useEffect, useRef, useState } from "react";
import { ArrowLeft, CheckCircle2, Edit3, LoaderCircle, Phone, RotateCcw, ShieldCheck } from "lucide-react";

export function AuthForm() {
  const [step, setStep] = useState<"phone" | "code">("phone");
  const [phone, setPhone] = useState("");
  const [code, setCode] = useState("");
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(false);
  const [seconds, setSeconds] = useState(0);
  const [devCode, setDevCode] = useState("");
  const codeInput = useRef<HTMLInputElement>(null);

  useEffect(() => {
    if (seconds <= 0) return;
    const timer = window.setInterval(() => setSeconds((value) => value - 1), 1000);
    return () => window.clearInterval(timer);
  }, [seconds]);

  useEffect(() => {
    if (step === "code") codeInput.current?.focus();
  }, [step]);

  async function requestCode(event?: FormEvent) {
    event?.preventDefault();
    setError("");
    setLoading(true);
    try {
      const response = await fetch("/api/auth/request-otp", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ phone }),
      });
      const data = await response.json();
      if (!response.ok) throw new Error(data.error || "ارسال کد انجام نشد.");
      setDevCode(data.devCode || "");
      setStep("code");
      setSeconds(60);
    } catch (requestError) {
      setError(requestError instanceof Error ? requestError.message : "خطایی رخ داده است.");
    } finally {
      setLoading(false);
    }
  }

  async function verifyCode(event: FormEvent) {
    event.preventDefault();
    setError("");
    setLoading(true);
    try {
      const response = await fetch("/api/auth/verify-otp", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ phone, code }),
      });
      const data = await response.json();
      if (!response.ok) throw new Error(data.error || "تأیید کد انجام نشد.");
      window.location.href = data.redirectTo || "/panel";
    } catch (verifyError) {
      setError(verifyError instanceof Error ? verifyError.message : "خطایی رخ داده است.");
      setLoading(false);
    }
  }

  if (step === "phone") {
    return (
      <form onSubmit={requestCode} className="mt-8">
        <label className="form-label">شماره موبایل</label>
        <div className="relative">
          <Phone className="absolute right-4 top-1/2 -translate-y-1/2 text-[#7d9592]" size={19} />
          <input
            value={phone}
            onChange={(event) => setPhone(event.target.value)}
            inputMode="tel"
            dir="ltr"
            autoComplete="tel"
            className="form-input !py-3.5 !pr-12 text-left font-bold tracking-wider"
            placeholder="0912 123 4567"
            maxLength={14}
            autoFocus
          />
        </div>
        {error && <p className="mt-3 rounded-xl bg-red-50 px-3 py-2 text-xs font-bold text-red-600">{error}</p>}
        <button disabled={loading} className="primary-button mt-5 w-full !py-3.5">
          {loading ? <LoaderCircle className="animate-spin" size={19} /> : <ArrowLeft size={19} />}
          دریافت کد ورود
        </button>
        <p className="mt-4 text-center text-[10px] leading-5 text-[#829592]">با ورود به نوبت سلامت، قوانین استفاده و حریم خصوصی را می‌پذیرید.</p>
      </form>
    );
  }

  return (
    <form onSubmit={verifyCode} className="mt-8">
      <div className="mb-5 rounded-2xl bg-[#edf8f5] p-3.5 text-xs leading-6 text-[#315b58]">
        <div className="flex items-center justify-between gap-3">
          <span><CheckCircle2 className="ml-1 inline text-[#0d9488]" size={17} />کد تأیید برای <b dir="ltr">{phone}</b> ارسال شد.</span>
          <button type="button" onClick={() => { setStep("phone"); setCode(""); setError(""); }} className="shrink-0 text-[#0d9488]" aria-label="ویرایش شماره"><Edit3 size={16} /></button>
        </div>
      </div>
      <label className="form-label">کد پنج‌رقمی</label>
      <input
        ref={codeInput}
        value={code}
        onChange={(event) => setCode(event.target.value.replace(/\D/g, "").slice(0, 5))}
        inputMode="numeric"
        dir="ltr"
        autoComplete="one-time-code"
        className="form-input !py-3.5 text-center text-xl font-black tracking-[.5em]"
        placeholder="•••••"
      />
      {devCode && <button type="button" onClick={() => setCode(devCode)} className="mt-3 w-full rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800">کد ورود آزمایشی: <b dir="ltr" className="text-sm">{devCode}</b> — برای درج کلیک کنید</button>}
      {error && <p className="mt-3 rounded-xl bg-red-50 px-3 py-2 text-xs font-bold text-red-600">{error}</p>}
      <button disabled={loading || code.length < 5} className="primary-button mt-5 w-full !py-3.5">
        {loading ? <LoaderCircle className="animate-spin" size={19} /> : <ShieldCheck size={19} />}
        تأیید و ورود امن
      </button>
      <div className="mt-4 text-center text-xs text-[#718784]">
        {seconds > 0 ? <span>ارسال مجدد تا <b className="text-[#0d9488]">{seconds}</b> ثانیه دیگر</span> : <button type="button" onClick={() => requestCode()} disabled={loading} className="inline-flex items-center gap-1 font-black text-[#0d9488]"><RotateCcw size={14} />ارسال دوباره کد</button>}
      </div>
    </form>
  );
}
