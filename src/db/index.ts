import { drizzle as drizzleMysql, type MySql2Database } from "drizzle-orm/mysql2";
import { drizzle as drizzlePg } from "drizzle-orm/node-postgres";
import mysql from "mysql2/promise";
import { Pool as PgPool } from "pg";

const databaseUrl = process.env.DATABASE_URL;

if (!databaseUrl) {
  throw new Error("DATABASE_URL is required");
}

const globalForDb = globalThis as typeof globalThis & {
  __nobatSalamatDb?: MySql2Database<Record<string, never>>;
};

function createDb(): MySql2Database<Record<string, never>> {
  // MySQL برای deployment واقعی (nobatesalamat.ir)
  if (databaseUrl!.startsWith("mysql")) {
    const pool = mysql.createPool({
      uri: databaseUrl,
      connectionLimit: 10,
      charset: "utf8mb4",
      timezone: "+03:30",
    });
    return drizzleMysql(pool) as unknown as MySql2Database<Record<string, never>>;
  }

  // PostgreSQL پیش‌نمایش این محیط
  const pool = new PgPool({ connectionString: databaseUrl });
  return drizzlePg(pool) as unknown as MySql2Database<Record<string, never>>;
}

export const db = globalForDb.__nobatSalamatDb ?? createDb();

if (process.env.NODE_ENV !== "production") {
  globalForDb.__nobatSalamatDb = db;
}
