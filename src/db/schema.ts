// انتخاب اسکیمای فعال بر اساس نوع DATABASE_URL:
//   mysql://...   ← deployment واقعی (MySQL/MariaDB)
//   postgres://... ← پیش‌نمایش این محیط (PostgreSQL پلتفرم)
// نام و ساختار جداول در هر دو یکسان است.
import * as mysqlSchema from "./schema.mysql";
import * as pgSchema from "./schema.pg";

const databaseUrl = process.env.DATABASE_URL ?? "";
const isMysql = databaseUrl.startsWith("mysql");

const active = isMysql ? mysqlSchema : pgSchema;

export const users = active.users as unknown as typeof mysqlSchema.users;
export const sessions = active.sessions as unknown as typeof mysqlSchema.sessions;
export const otpCodes = active.otpCodes as unknown as typeof mysqlSchema.otpCodes;
export const specialties = active.specialties as unknown as typeof mysqlSchema.specialties;
export const doctors = active.doctors as unknown as typeof mysqlSchema.doctors;
export const appointments = active.appointments as unknown as typeof mysqlSchema.appointments;
export const siteSettings = active.siteSettings as unknown as typeof mysqlSchema.siteSettings;
export const articles = active.articles as unknown as typeof mysqlSchema.articles;
export const updateLogs = active.updateLogs as unknown as typeof mysqlSchema.updateLogs;

export type User = typeof mysqlSchema.users.$inferSelect;
export type Doctor = typeof mysqlSchema.doctors.$inferSelect;
export type Appointment = typeof mysqlSchema.appointments.$inferSelect;
