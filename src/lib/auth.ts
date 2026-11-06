import { createHash, randomBytes, randomInt } from "crypto";
import { cookies } from "next/headers";
import { db } from "@/db";
import { otpCodes, sessions, users, type User } from "@/db/schema";
import { and, desc, eq, gt, lt } from "drizzle-orm";

const SESSION_COOKIE = "nobat_session";
const SESSION_DAYS = 30;

function secret() {
  return process.env.AUTH_SECRET || "nobat-salamat-local-development-secret";
}

export function toEnglishDigits(value: string) {
  return value
    .replace(/[۰-۹]/g, (char) => String("۰۱۲۳۴۵۶۷۸۹".indexOf(char)))
    .replace(/[٠-٩]/g, (char) => String("٠١٢٣٤٥٦٧٨٩".indexOf(char)));
}

export function normalizePhone(input: string) {
  let phone = toEnglishDigits(input).replace(/\D/g, "");
  if (phone.startsWith("0098")) phone = `0${phone.slice(4)}`;
  if (phone.startsWith("98")) phone = `0${phone.slice(2)}`;
  if (phone.startsWith("9") && phone.length === 10) phone = `0${phone}`;
  return phone;
}

export function isValidIranianPhone(phone: string) {
  return /^09\d{9}$/.test(phone);
}

function hash(value: string) {
  return createHash("sha256").update(`${value}:${secret()}`).digest("hex");
}

export async function createOtp(phone: string) {
  const code = String(randomInt(10000, 100000));
  await db.delete(otpCodes).where(eq(otpCodes.phone, phone));
  await db.insert(otpCodes).values({
    phone,
    codeHash: hash(`${phone}:${code}`),
    expiresAt: new Date(Date.now() + 2 * 60 * 1000),
  });
  return code;
}

export async function verifyOtp(phone: string, code: string) {
  const [otp] = await db
    .select()
    .from(otpCodes)
    .where(and(eq(otpCodes.phone, phone), gt(otpCodes.expiresAt, new Date())))
    .orderBy(desc(otpCodes.createdAt))
    .limit(1);

  if (!otp || otp.attempts >= 5) return false;
  if (otp.codeHash !== hash(`${phone}:${toEnglishDigits(code)}`)) {
    await db.update(otpCodes).set({ attempts: otp.attempts + 1 }).where(eq(otpCodes.id, otp.id));
    return false;
  }
  await db.delete(otpCodes).where(eq(otpCodes.id, otp.id));
  return true;
}

export async function findOrCreateUser(phone: string) {
  const [existing] = await db.select().from(users).where(eq(users.phone, phone)).limit(1);
  if (existing) return existing;
  await db.insert(users).values({ phone, permissions: "[]" });
  const [created] = await db.select().from(users).where(eq(users.phone, phone)).limit(1);
  return created;
}

export async function createSession(userId: number) {
  const rawToken = randomBytes(32).toString("hex");
  const id = randomBytes(24).toString("hex");
  const expiresAt = new Date(Date.now() + SESSION_DAYS * 24 * 60 * 60 * 1000);
  await db.delete(sessions).where(lt(sessions.expiresAt, new Date()));
  await db.insert(sessions).values({ id, userId, tokenHash: hash(rawToken), expiresAt });
  const cookieStore = await cookies();
  cookieStore.set(SESSION_COOKIE, `${id}.${rawToken}`, {
    httpOnly: true,
    sameSite: "lax",
    secure: process.env.NODE_ENV === "production",
    path: "/",
    expires: expiresAt,
  });
}

export async function getCurrentUser(): Promise<User | null> {
  const cookieStore = await cookies();
  const value = cookieStore.get(SESSION_COOKIE)?.value;
  if (!value) return null;
  const [id, token] = value.split(".");
  if (!id || !token) return null;
  const [result] = await db
    .select({ user: users, tokenHash: sessions.tokenHash })
    .from(sessions)
    .innerJoin(users, eq(sessions.userId, users.id))
    .where(and(eq(sessions.id, id), gt(sessions.expiresAt, new Date())))
    .limit(1);
  if (!result || result.tokenHash !== hash(token) || result.user.status !== "active") return null;
  return result.user;
}

export async function destroySession() {
  const cookieStore = await cookies();
  const value = cookieStore.get(SESSION_COOKIE)?.value;
  if (value) {
    const [id] = value.split(".");
    if (id) await db.delete(sessions).where(eq(sessions.id, id));
  }
  cookieStore.delete(SESSION_COOKIE);
}

export function isAdmin(user: User | null): user is User {
  return Boolean(user && (user.role === "admin" || user.role === "super_admin"));
}

export const ADMIN_PERMISSIONS = ["content", "doctors", "appointments", "updates"] as const;
export type AdminPermission = (typeof ADMIN_PERMISSIONS)[number];

export function parsePermissions(raw: string | null | undefined): AdminPermission[] {
  try {
    const list = JSON.parse(raw || "[]");
    if (!Array.isArray(list)) return [];
    return list.filter((item): item is AdminPermission => (ADMIN_PERMISSIONS as readonly string[]).includes(item));
  } catch {
    return [];
  }
}

export function hasPermission(user: User | null, permission: AdminPermission): user is User {
  if (!isAdmin(user)) return false;
  if (user.role === "super_admin") return true;
  return parsePermissions(user.permissions).includes(permission);
}
