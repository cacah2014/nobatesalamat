import { execFile, exec as execCallback } from "child_process";
import { promisify } from "util";
import { eq } from "drizzle-orm";
import { db } from "@/db";
import { updateLogs } from "@/db/schema";
import { requirePermission } from "@/lib/admin";

export const runtime = "nodejs";
export const maxDuration = 300;

const execFileAsync = promisify(execFile);
const execAsync = promisify(execCallback);
let updateRunning = false;

async function runFile(command: string, args: string[]) {
  const result = await execFileAsync(command, args, {
    cwd: process.cwd(),
    timeout: 240_000,
    maxBuffer: 1024 * 1024 * 5,
    env: process.env,
  });
  return `${result.stdout || ""}${result.stderr || ""}`.trim();
}

export async function POST() {
  const admin = await requirePermission("updates");
  if (!admin) return Response.json({ error: "دسترسی به بخش به‌روزرسانی را ندارید." }, { status: 403 });
  if (process.env.ALLOW_WEB_UPDATES !== "true") {
    return Response.json({ error: "به‌روزرسانی وب غیرفعال است. متغیر ALLOW_WEB_UPDATES را روی true قرار دهید." }, { status: 503 });
  }
  if (updateRunning) return Response.json({ error: "یک فرایند به‌روزرسانی در حال اجراست." }, { status: 409 });

  updateRunning = true;
  const [log] = await db.insert(updateLogs).values({ adminId: admin.id, output: "" }).$returningId();
  const output: string[] = [];
  const save = async (step: string, text: string) => {
    output.push(`\n[${step}]\n${text || "انجام شد"}`);
    await db.update(updateLogs).set({ currentStep: step, output: output.join("\n") }).where(eq(updateLogs.id, log.id));
  };

  try {
    await save("git_pull", await runFile("git", ["pull", "--ff-only"]));
    await save("dependency", await runFile("npm", ["ci"]));
    await save("database", await runFile("npx", ["drizzle-kit", "push"]));
    await save("code", await runFile("npm", ["run", "build"]));

    const finalCommand = process.env.UPDATE_FINAL_COMMAND;
    if (finalCommand) {
      const result = await execAsync(finalCommand, { cwd: process.cwd(), timeout: 120_000, maxBuffer: 1024 * 1024 * 2, env: process.env });
      await save("update", `${result.stdout || ""}${result.stderr || ""}`.trim());
    } else {
      await save("update", "نسخه جدید آماده شد؛ راه‌اندازی مجدد توسط سرویس میزبانی انجام می‌شود.");
    }

    await db.update(updateLogs).set({ status: "success", currentStep: "completed", output: output.join("\n"), finishedAt: new Date() }).where(eq(updateLogs.id, log.id));
    return Response.json({ ok: true, logId: log.id, output });
  } catch (error) {
    const message = error instanceof Error ? error.message : "خطای ناشناخته";
    output.push(`\n[ERROR]\n${message}`);
    await db.update(updateLogs).set({ status: "failed", output: output.join("\n"), finishedAt: new Date() }).where(eq(updateLogs.id, log.id));
    return Response.json({ error: "فرایند به‌روزرسانی ناموفق بود.", output }, { status: 500 });
  } finally {
    updateRunning = false;
  }
}
