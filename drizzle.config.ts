import "dotenv/config";
import { defineConfig } from "drizzle-kit";

const url = process.env.DATABASE_URL ?? "";
const isMysql = url.startsWith("mysql");

// npx drizzle-kit push → اسکیما و diaclect بر اساس DATABASE_URL انتخاب می‌شود
export default defineConfig({
  dialect: isMysql ? "mysql" : "postgresql",
  schema: isMysql ? "./src/db/schema.mysql.ts" : "./src/db/schema.pg.ts",
  dbCredentials: { url },
});
