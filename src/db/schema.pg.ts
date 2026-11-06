import {
  boolean,
  integer,
  pgTable,
  real,
  serial,
  text,
  timestamp,
  uniqueIndex,
  varchar,
} from "drizzle-orm/pg-core";

export const users = pgTable(
  "users",
  {
    id: serial("id").primaryKey(),
    phone: varchar("phone", { length: 16 }).notNull(),
    fullName: varchar("full_name", { length: 120 }).notNull().default("کاربر نوبت سلامت"),
    role: varchar("role", { length: 24 }).notNull().default("user"),
    status: varchar("status", { length: 20 }).notNull().default("active"),
    permissions: text("permissions").notNull(),
    createdAt: timestamp("created_at", { withTimezone: true, mode: "date" }).notNull().defaultNow(),
    updatedAt: timestamp("updated_at", { withTimezone: true, mode: "date" }).notNull().defaultNow(),
  },
  (table) => [uniqueIndex("users_phone_unique").on(table.phone)],
);

export const sessions = pgTable(
  "sessions",
  {
    id: varchar("id", { length: 64 }).primaryKey(),
    userId: integer("user_id")
      .notNull()
      .references(() => users.id, { onDelete: "cascade" }),
    tokenHash: varchar("token_hash", { length: 64 }).notNull(),
    expiresAt: timestamp("expires_at", { withTimezone: true, mode: "date" }).notNull(),
    createdAt: timestamp("created_at", { withTimezone: true, mode: "date" }).notNull().defaultNow(),
  },
  (table) => [uniqueIndex("sessions_token_hash_unique").on(table.tokenHash)],
);

export const otpCodes = pgTable("otp_codes", {
  id: serial("id").primaryKey(),
  phone: varchar("phone", { length: 16 }).notNull(),
  codeHash: varchar("code_hash", { length: 64 }).notNull(),
  attempts: integer("attempts").notNull().default(0),
  expiresAt: timestamp("expires_at", { withTimezone: true, mode: "date" }).notNull(),
  createdAt: timestamp("created_at", { withTimezone: true, mode: "date" }).notNull().defaultNow(),
});

export const specialties = pgTable(
  "specialties",
  {
    id: serial("id").primaryKey(),
    title: varchar("title", { length: 100 }).notNull(),
    slug: varchar("slug", { length: 120 }).notNull(),
    icon: varchar("icon", { length: 32 }).notNull().default("stethoscope"),
    color: varchar("color", { length: 32 }).notNull().default("teal"),
    description: text("description").notNull(),
    isActive: boolean("is_active").notNull().default(true),
    sortOrder: integer("sort_order").notNull().default(0),
  },
  (table) => [uniqueIndex("specialties_slug_unique").on(table.slug)],
);

export const doctors = pgTable(
  "doctors",
  {
    id: serial("id").primaryKey(),
    fullName: varchar("full_name", { length: 120 }).notNull(),
    slug: varchar("slug", { length: 140 }).notNull(),
    specialtyId: integer("specialty_id")
      .notNull()
      .references(() => specialties.id, { onDelete: "restrict" }),
    title: varchar("title", { length: 160 }).notNull(),
    city: varchar("city", { length: 80 }).notNull().default("تهران"),
    address: text("address").notNull(),
    rating: real("rating").notNull().default(5),
    reviewsCount: integer("reviews_count").notNull().default(0),
    visitPrice: integer("visit_price").notNull().default(0),
    experienceYears: integer("experience_years").notNull().default(1),
    nextAvailable: varchar("next_available", { length: 80 }).notNull().default("امروز"),
    imageUrl: text("image_url").notNull(),
    isOnline: boolean("is_online").notNull().default(false),
    isActive: boolean("is_active").notNull().default(true),
    about: text("about").notNull(),
    medicalCode: varchar("medical_code", { length: 30 }).notNull().default(""),
    createdAt: timestamp("created_at", { withTimezone: true, mode: "date" }).notNull().defaultNow(),
    updatedAt: timestamp("updated_at", { withTimezone: true, mode: "date" }).notNull().defaultNow(),
  },
  (table) => [uniqueIndex("doctors_slug_unique").on(table.slug)],
);

export const appointments = pgTable(
  "appointments",
  {
    id: serial("id").primaryKey(),
    userId: integer("user_id")
      .notNull()
      .references(() => users.id, { onDelete: "cascade" }),
    doctorId: integer("doctor_id")
      .notNull()
      .references(() => doctors.id, { onDelete: "restrict" }),
    appointmentDate: varchar("appointment_date", { length: 30 }).notNull(),
    appointmentTime: varchar("appointment_time", { length: 10 }).notNull(),
    visitType: varchar("visit_type", { length: 20 }).notNull().default("in_person"),
    status: varchar("status", { length: 24 }).notNull().default("confirmed"),
    price: integer("price").notNull().default(0),
    trackingCode: varchar("tracking_code", { length: 20 }).notNull(),
    patientName: varchar("patient_name", { length: 120 }).notNull(),
    patientPhone: varchar("patient_phone", { length: 16 }).notNull(),
    note: text("note").notNull(),
    createdAt: timestamp("created_at", { withTimezone: true, mode: "date" }).notNull().defaultNow(),
  },
  (table) => [uniqueIndex("appointments_tracking_unique").on(table.trackingCode)],
);

export const siteSettings = pgTable("site_settings", {
  key: varchar("key", { length: 80 }).primaryKey(),
  value: text("value").notNull(),
  label: varchar("label", { length: 120 }).notNull(),
  group: varchar("group_name", { length: 40 }).notNull().default("general"),
  updatedAt: timestamp("updated_at", { withTimezone: true, mode: "date" }).notNull().defaultNow(),
});

export const articles = pgTable(
  "articles",
  {
    id: serial("id").primaryKey(),
    title: varchar("title", { length: 180 }).notNull(),
    slug: varchar("slug", { length: 180 }).notNull(),
    excerpt: text("excerpt").notNull(),
    imageUrl: text("image_url").notNull(),
    readTime: integer("read_time").notNull().default(5),
    isPublished: boolean("is_published").notNull().default(true),
    createdAt: timestamp("created_at", { withTimezone: true, mode: "date" }).notNull().defaultNow(),
  },
  (table) => [uniqueIndex("articles_slug_unique").on(table.slug)],
);

export const updateLogs = pgTable("update_logs", {
  id: serial("id").primaryKey(),
  adminId: integer("admin_id").references(() => users.id, { onDelete: "set null" }),
  status: varchar("status", { length: 24 }).notNull().default("running"),
  currentStep: varchar("current_step", { length: 40 }).notNull().default("git_pull"),
  output: text("output").notNull(),
  startedAt: timestamp("started_at", { withTimezone: true, mode: "date" }).notNull().defaultNow(),
  finishedAt: timestamp("finished_at", { withTimezone: true, mode: "date" }),
});
