import Link from "next/link";
import { ArrowLeft, Clock3, MapPin, MessageCircleMore, Star } from "lucide-react";
import { formatPrice, faNumber } from "@/lib/format";

export type DoctorCardData = {
  id: number;
  fullName: string;
  slug: string;
  title: string;
  city: string;
  rating: number;
  reviewsCount: number;
  visitPrice: number;
  nextAvailable: string;
  imageUrl: string;
  isOnline: boolean;
  specialtyTitle?: string | null;
};

export function DoctorCard({ doctor }: { doctor: DoctorCardData }) {
  return (
    <article className="card-hover overflow-hidden rounded-[22px] border border-[#e0ecea] bg-white">
      <div className="relative h-48 overflow-hidden bg-[#e9f3f1]">
        <img src={doctor.imageUrl} alt={`تصویر ${doctor.fullName}`} className="h-full w-full object-cover object-[center_25%] transition duration-500 hover:scale-[1.03]" />
        <div className="absolute inset-x-0 bottom-0 h-20 bg-gradient-to-t from-[#123b3a]/55 to-transparent" />
        <span className="absolute right-3 top-3 flex items-center gap-1 rounded-full bg-white/95 px-2.5 py-1 text-[11px] font-black text-[#7a5711] shadow-sm">
          <Star size={13} className="fill-[#f6ba3c] text-[#f6ba3c]" /> {faNumber.format(doctor.rating)}
        </span>
        {doctor.isOnline && (
          <span className="absolute bottom-3 right-3 flex items-center gap-1.5 rounded-full bg-[#0d9488] px-2.5 py-1 text-[10px] font-bold text-white">
            <span className="relative size-1.5 rounded-full bg-[#aaf3dd]" /> مشاوره آنلاین
          </span>
        )}
      </div>
      <div className="p-4.5">
        <div className="flex items-start justify-between gap-2">
          <div>
            <h3 className="font-black text-[#173f3d]">{doctor.fullName}</h3>
            <p className="mt-1 line-clamp-1 text-xs text-[#68807e]">{doctor.title}</p>
          </div>
          <span className="shrink-0 rounded-lg bg-[#eef8f5] px-2 py-1 text-[10px] font-bold text-[#0d9488]">{doctor.specialtyTitle}</span>
        </div>
        <div className="mt-4 grid grid-cols-2 gap-2 text-[11px] text-[#607a78]">
          <span className="flex items-center gap-1.5"><MapPin size={14} className="text-[#ff6b57]" />{doctor.city}</span>
          <span className="flex items-center justify-end gap-1.5"><MessageCircleMore size={14} />{faNumber.format(doctor.reviewsCount)} نظر</span>
        </div>
        <div className="mt-4 flex items-center gap-2 rounded-xl bg-[#f7faf9] p-2.5">
          <Clock3 size={16} className="shrink-0 text-[#0d9488]" />
          <div className="min-w-0 flex-1">
            <small className="block text-[9px] text-[#78908e]">اولین نوبت آزاد</small>
            <b className="block truncate text-[11px] text-[#315b58]">{doctor.nextAvailable}</b>
          </div>
        </div>
        <div className="mt-4 flex items-end justify-between border-t border-[#edf2f1] pt-3.5">
          <div><small className="block text-[9px] text-[#819694]">هزینه ویزیت</small><b className="text-xs text-[#173f3d]">{formatPrice(doctor.visitPrice)}</b></div>
          <Link href={`/doctors/${doctor.slug}`} className="flex items-center gap-1 text-xs font-black text-[#0d9488] transition hover:gap-2">مشاهده و نوبت <ArrowLeft size={15} /></Link>
        </div>
      </div>
    </article>
  );
}
