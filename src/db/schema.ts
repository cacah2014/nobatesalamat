import {
  boolean,
  double,
  int,
  mysqlTable,
  text,
  timestamp,
  uniqueIndex,
  varchar,
} from "drizzle-orm/mysql-core";

export const users = mysqlTable(
  "users",
  {
    id: int("id").autoincrement().primaryKey(),
    phone: varchar("phone", { length: 16 }).notNull(),
    fullName: varchar("full_name", { length: 120 }).notNull().default("کاربر نوبت سلامت"),
    role: varchar("role", { length: 24 }).notNull().default("user"),
    status: varchar("status", { length: 20 }).notNull().default("active"),
    permissions: text("permissions").notNull(),
    createdAt: timestamp("created_at", { mode: "date" }).notNull().defaultNow(),
    updatedAt: timestamp("updated_at", { mode: "date" }).notNull().defaultNow(),
  },
  (table) => [uniqueIndex("users_phone_unique").on(table.phone)],
);

export const sessions = mysqlTable(
  "sessions",
  {
    id: varchar("id", { length: 64 }).primaryKey(),
    userId: int("user_id")
      .notNull()
      .references(() => users.id, { onDelete: "cascade" }),
    tokenHash: varchar("token_hash", { length: 64 }).notNull(),
    expiresAt: timestamp("expires_at", { mode: "date" }).notNull(),
    createdAt: timestamp("created_at", { mode: "date" }).notNull().defaultNow(),
  },
  (table) => [uniqueIndex("sessions_token_hash_unique").on(table.tokenHash)],
);

export const otpCodes = mysqlTable("otp_codes", {
  id: int("id").autoincrement().primaryKey(),
  phone: varchar("phone", { length: 16 }).notNull(),
  codeHash: varchar("code_hash", { length: 64 }).notNull(),
  attempts: int("attempts").notNull().default(0),
  expiresAt: timestamp("expires_at", { mode: "date" }).notNull(),
  createdAt: timestamp("created_at", { mode: "date" }).notNull().defaultNow(),
});

export const specialties = mysqlTable(
  "specialties",
  {
    id: int("id").autoincrement().primaryKey(),
    title: varchar("title", { length: 100 }).notNull(),
    slug: varchar("slug", { length: 120 }).notNull(),
    icon: varchar("icon", { length: 32 }).notNull().default("stethoscope"),
    color: varchar("color", { length: 32 }).notNull().default("teal"),
    description: text("description").notNull(),
    isActive: boolean("is_active").notNull().default(true),
    sortOrder: int("sort_order").notNull().default(0),
  },
  (table) => [uniqueIndex("specialties_slug_unique").on(table.slug)],
);

export const doctors = mysqlTable(
  "doctors",
  {
    id: int("id").autoincrement().primaryKey(),
    fullName: varchar("full_name", { length: 120 }).notNull(),
    slug: varchar("slug", { length: 140 }).notNull(),
    specialtyId: int("specialty_id")
      .notNull()
      .references(() => specialties.id, { onDelete: "restrict" }),
    title: varchar("title", { length: 160 }).notNull(),
    city: varchar("city", { length: 80 }).notNull().default("تهران"),
    address: text("address").notNull(),
    rating: double("rating").notNull().default(5),
    reviewsCount: int("reviews_count").notNull().default(0),
    visitPrice: int("visit_price").notNull().default(0),
    experienceYears: int("experience_years").notNull().default(1),
    nextAvailable: varchar("next_available", { length: 80 }).notNull().default("امروز"),
    imageUrl: text("image_url").notNull(),
    isOnline: boolean("is_online").notNull().default(false),
    isActive: boolean("is_active").notNull().default(true),
    about: text("about").notNull(),
    medicalCode: varchar("medical_code", { length: 30 }).notNull().default(""),
    createdAt: timestamp("created_at", { mode: "date" }).notNull().defaultNow(),
    updatedAt: timestamp("updated_at", { mode: "date" }).notNull().defaultNow(),
  },
  (table) => [uniqueIndex("doctors_slug_unique").on(table.slug)],
);

export const appointments = mysqlTable(
  "appointments",
  {
    id: int("id").autoincrement().primaryKey(),
    userId: int("user_id")
      .notNull()
      .references(() => users.id, { onDelete: "cascade" }),
    doctorId: int("doctor_id")
      .notNull()
      .references(() => doctors.id, { onDelete: "restrict" }),
    appointmentDate: varchar("appointment_date", { length: 30 }).notNull(),
    appointmentTime: varchar("appointment_time", { length: 10 }).notNull(),
    visitType: varchar("visit_type", { length: 20 }).notNull().default("in_person"),
    status: varchar("status", { length: 24 }).notNull().default("confirmed"),
    price: int("price").notNull().default(0),
    trackingCode: varchar("tracking_code", { length: 20 }).notNull(),
    patientName: varchar("patient_name", { length: 120 }).notNull(),
    patientPhone: varchar("patient_phone", { length: 16 }).notNull(),
    note: text("note").notNull(),
    createdAt: timestamp("created_at", { mode: "date" }).notNull().defaultNow(),
  },
  (table) => [uniqueIndex("appointments_tracking_unique").on(table.trackingCode)],
);

export const siteSettings = mysqlTable("site_settings", {
  key: varchar("key", { length: 80 }).primaryKey(),
  value: text("value").notNull(),
  label: varchar("label", { length: 120 }).notNull(),
  group: varchar("group_name", { length: 40 }).notNull().default("general"),
  updatedAt: timestamp("updated_at", { mode: "date" }).notNull().defaultNow(),
});

export const articles = mysqlTable(
  "articles",
  {
    id: int("id").autoincrement().primaryKey(),
    title: varchar("title", { length: 180 }).notNull(),
    slug: varchar("slug", { length: 180 }).notNull(),
    excerpt: text("excerpt").notNull(),
    imageUrl: text("image_url").notNull(),
    readTime: int("read_time").notNull().default(5),
    isPublished: boolean("is_published").notNull().default(true),
    createdAt: timestamp("created_at", { mode: "date" }).notNull().defaultNow(),
  },
  (table) => [uniqueIndex("articles_slug_unique").on(table.slug)],
);

export const updateLogs = mysqlTable("update_logs", {
  id: int("id").autoincrement().primaryKey(),
  adminId: int("admin_id").references(() => users.id, { onDelete: "set null" }),
  status: varchar("status", { length: 24 }).notNull().default("running"),
  currentStep: varchar("current_step", { length: 40 }).notNull().default("git_pull"),
  output: text("output").notNull(),
  startedAt: timestamp("started_at", { mode: "date" }).notNull().defaultNow(),
  finishedAt: timestamp("finished_at", { mode: "date" }),
});

export type User = typeof users.$inferSelect;
export type Doctor = typeof doctors.$inferSelect;
export type Appointment = typeof appointments.$inferSelect;
