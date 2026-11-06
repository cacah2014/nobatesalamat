import { drizzle } from "drizzle-orm/mysql2";
import mysql from "mysql2/promise";

const databaseUrl = process.env.DATABASE_URL;

if (!databaseUrl) {
  throw new Error("DATABASE_URL is required");
}

const globalForDb = globalThis as typeof globalThis & {
  __nobatSalamatMysqlPool?: mysql.Pool;
};

export const pool =
  globalForDb.__nobatSalamatMysqlPool ??
  mysql.createPool({
    uri: databaseUrl,
    connectionLimit: 10,
    charset: "utf8mb4",
    timezone: "+03:30",
  });

if (process.env.NODE_ENV !== "production") {
  globalForDb.__nobatSalamatMysqlPool = pool;
}

export const db = drizzle(pool);
