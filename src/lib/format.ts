export const faNumber = new Intl.NumberFormat("fa-IR");
export const faMoney = new Intl.NumberFormat("fa-IR");
export const faDate = new Intl.DateTimeFormat("fa-IR", {
  year: "numeric",
  month: "long",
  day: "numeric",
});

export function formatPrice(value: number) {
  return `${faMoney.format(value)} تومان`;
}

export function maskPhone(phone: string) {
  return phone.replace(/(09\d{2})\d{3}(\d{4})/, "$1***$2");
}
