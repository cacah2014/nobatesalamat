import type { Metadata } from "next";
import Link from "next/link";
import { notFound } from "next/navigation";
import {
  BookOpen,
  CircleDot,
  Clock,
  Code,
  Eye,
  File,
  GitFork,
  GitPullRequest,
  History,
  Link2,
  Play,
  ShieldCheck,
  Star,
} from "lucide-react";
import { RepoBrowser } from "@/components/repo-browser";
import {
  formatFileSize,
  normalizeDirPath,
  parentPath,
  pathKind,
  readRepoFile,
  readRepoSnapshot,
} from "@/lib/repo-browser";

export const dynamic = "force-dynamic";

export const metadata: Metadata = {
  title: "مرور فایل‌های پروژه",
  description: "نمای شبیه گیت‌هاب از همهٔ فایل‌ها و پوشه‌های ریپوی نوبت سلامت، همراه با پیام و تاریخ آخرین کامیت هر فایل.",
  robots: { index: false, follow: false },
};

const githubFont =
  '-apple-system, BlinkMacSystemFont, "Segoe UI", "Noto Sans", Helvetica, Arial, sans-serif';

const numberFormat = new Intl.NumberFormat("en-US");

const tabs = [
  { label: "Code", icon: Code, active: true },
  { label: "Issues", icon: CircleDot, active: false },
  { label: "Pull requests", icon: GitPullRequest, active: false },
  { label: "Actions", icon: Play, active: false },
  { label: "Projects", icon: BookOpen, active: false },
  { label: "Security", icon: ShieldCheck, active: false },
  { label: "Insights", icon: History, active: false },
];

function Breadcrumb({ dirPath, fileMode }: { dirPath: string; fileMode: boolean }) {
  const segments = dirPath ? dirPath.split("/") : [];
  const displaySegments = fileMode ? segments.slice(0, -1) : segments;
  const fileSegment = fileMode ? segments[segments.length - 1] : null;

  return (
    <nav className="flex flex-wrap items-center gap-1 text-[15px] font-semibold" aria-label="مسیر پوشه">
      <Link href="/repo" className="rounded px-1 text-[#0969da] hover:underline">
        nobatesalamat
      </Link>
      {displaySegments.map((segment, index) => {
        const href = `/repo/${displaySegments.slice(0, index + 1).map(encodeURIComponent).join("/")}`;
        return (
          <span key={href} className="flex items-center gap-1">
            <span className="text-[#8c959f]">/</span>
            <Link href={href} className="rounded px-1 text-[#0969da] hover:underline">
              {segment}
            </Link>
          </span>
        );
      })}
      {fileSegment && (
        <span className="flex items-center gap-1">
          <span className="text-[#8c959f]">/</span>
          <span className="px-1 text-[#1f2328]">{fileSegment}</span>
        </span>
      )}
    </nav>
  );
}

export default async function RepoPage({ params }: { params: Promise<{ path?: string[] }> }) {
  const resolved = await params;
  const dirPath = normalizeDirPath(resolved.path);
  const kind = dirPath ? pathKind(dirPath) : "dir";

  if (!kind) notFound();

  const snapshot = readRepoSnapshot(kind === "dir" ? dirPath : parentPath(dirPath));

  if (kind === "file") {
    const file = readRepoFile(dirPath);
    const lines = file.content.split(/\r\n|\r|\n/);

    return (
      <RepoLayout snapshot={snapshot} dirPath={dirPath} fileMode>
        <div
          dir="ltr"
          style={{ fontFamily: githubFont }}
          className="overflow-hidden rounded-md border border-[#d1d9e0] bg-white"
        >
          <div className="flex flex-wrap items-center gap-x-3 gap-y-1 border-b border-[#d1d9e0] bg-[#f6f8fa] px-3 py-2 text-xs text-[#59636e]">
            <span className="font-semibold text-[#1f2328]">{file.name}</span>
            <span className="flex items-center gap-1">
              <File size={13} /> {formatFileSize(file.size)}
            </span>
            {!file.isBinary && (
              <span className="flex items-center gap-1">
                <Code size={13} /> {numberFormat.format(file.lines)} lines
              </span>
            )}
            <span className="ml-auto flex items-center gap-1">
              <Clock size={13} />
              {file.commitTime}
            </span>
          </div>

          {file.isBinary ? (
            <p className="px-4 py-10 text-center text-sm text-[#59636e]">این فایل باینری است و نمایش داده نمی‌شود.</p>
          ) : (
            <div className="max-h-[72vh] overflow-auto">
              <table className="w-full border-collapse text-[12.5px] leading-5">
                <tbody>
                  {lines.map((line, index) => (
                    <tr key={index} className="hover:bg-[#f6f8fa]">
                      <td className="w-12 select-none border-r border-[#d1d9e0] bg-[#f6f8fa]/60 px-2 text-right align-top font-mono text-[11px] text-[#8c959f]">
                        {index + 1}
                      </td>
                      <td className="whitespace-pre-wrap break-words px-3 align-top font-mono text-[#1f2328]">
                        {line || "\u00a0"}
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}

          {file.truncated && (
            <p className="border-t border-[#d1d9e0] bg-[#fff8c5] px-3 py-2 text-xs text-[#7d4e00]">
              نمایش فایل به ۱۶۰ کیلوبایت محدود شده است.
            </p>
          )}
        </div>
      </RepoLayout>
    );
  }

  return (
    <RepoLayout snapshot={snapshot} dirPath={dirPath} fileMode={false}>
      <RepoBrowser snapshot={snapshot} />
    </RepoLayout>
  );
}

function RepoLayout({
  snapshot,
  dirPath,
  fileMode,
  children,
}: {
  snapshot: Awaited<ReturnType<typeof readRepoSnapshot>>;
  dirPath: string;
  fileMode: boolean;
  children: React.ReactNode;
}) {
  return (
    <div className="min-h-screen bg-white text-[#1f2328]" style={{ fontFamily: githubFont }} dir="ltr">
      <header className="border-b border-[#d1d9e0] bg-[#f6f8fa]">
        <div className="mx-auto flex max-w-[1280px] items-center gap-3 px-4 py-3 lg:px-6">
          <span className="grid size-8 place-items-center rounded-full bg-[#0d9488] text-sm font-black text-white">ن</span>
          <div className="min-w-0">
            <p className="flex flex-wrap items-center gap-1 text-[15px] leading-5">
              <span className="text-[#0969da]">{snapshot.owner}</span>
              <span className="text-[#8c959f]">/</span>
              <span className="font-semibold">{snapshot.repoName}</span>
              <span className="ml-1 rounded-full border border-[#d1d9e0] px-2 py-[1px] text-[11px] font-medium text-[#59636e]">
                Public
              </span>
            </p>
            <p className="mt-0.5 flex flex-wrap items-center gap-2 text-[11px] text-[#59636e]">
              <span className="flex items-center gap-1">
                <Clock size={11} /> {numberFormat.format(snapshot.totalCommits)} commits
              </span>
              <span>·</span>
              <span className="flex items-center gap-1">
                <File size={11} /> {numberFormat.format(snapshot.totalFiles)} files
              </span>
              <span>·</span>
              <span>{snapshot.branch}</span>
            </p>
          </div>
          <Link
            href="/"
            dir="rtl"
            className="ml-auto rounded-md border border-[#d1d9e0] bg-white px-3 py-1.5 text-xs font-semibold text-[#1f2328] shadow-sm transition hover:bg-[#f6f8fa]"
          >
            بازگشت به نوبت سلامت
          </Link>
        </div>
        <div className="mx-auto max-w-[1280px] overflow-x-auto px-4 lg:px-6">
          <ul className="flex items-center gap-1 text-sm">
            {tabs.map((tab) => (
              <li key={tab.label}>
                <span
                  className={`flex items-center gap-1.5 whitespace-nowrap rounded-t-md border-b-2 px-2.5 py-2 ${
                    tab.active
                      ? "border-[#fd8c73] font-semibold text-[#1f2328]"
                      : "border-transparent text-[#59636e]"
                  }`}
                >
                  <tab.icon size={14} className={tab.active ? "text-[#59636e]" : "text-[#8c959f]"} />
                  {tab.label}
                </span>
              </li>
            ))}
          </ul>
        </div>
      </header>

      <main className="mx-auto max-w-[1280px] px-4 py-5 lg:px-6">
        <div className="grid gap-6 lg:grid-cols-[minmax(0,1fr)_296px]">
          <div className="min-w-0">
            <div className="mb-3">
              <Breadcrumb dirPath={dirPath} fileMode={fileMode} />
            </div>
            {children}
          </div>

          <aside className="space-y-4 text-sm" dir="rtl">
            <section className="rounded-md border border-[#d1d9e0] p-4">
              <h2 className="mb-2 text-[15px] font-semibold text-[#1f2328]">درباره</h2>
              <p className="text-xs leading-6 text-[#59636e]">
                نوبت سلامت؛ سامانهٔ نوبت‌دهی آنلاین پزشکان با Next.js و نسخهٔ Laravel، شامل پنل مدیریت، رزرو نوبت،
                ورود با OTP و سئوی کامل. این صفحه همهٔ فایل‌های پروژه را با همان چیدمان صفحهٔ گیت‌هاب نشان می‌دهد.
              </p>
              <a
                href="https://nobatesalamat.ir"
                target="_blank"
                rel="noreferrer"
                className="mt-3 flex items-center gap-1.5 text-xs font-semibold text-[#0969da] hover:underline"
                dir="ltr"
              >
                <Link2 size={13} /> nobatesalamat.ir
              </a>
              <div className="mt-3 flex flex-wrap gap-1.5">
                {["nextjs", "laravel", "mysql", "drizzle", "healthcare", "rtl"].map((topic) => (
                  <span
                    key={topic}
                    className="rounded-full bg-[#ddf4ff] px-2.5 py-0.5 text-[11px] font-medium text-[#0969da]"
                    dir="ltr"
                  >
                    {topic}
                  </span>
                ))}
              </div>
              <div className="mt-4 flex items-center gap-4 border-t border-[#d1d9e0] pt-3 text-xs text-[#59636e]" dir="ltr">
                <span className="flex items-center gap-1">
                  <Star size={13} /> {numberFormat.format(snapshot.totalCommits)}
                </span>
                <span className="flex items-center gap-1">
                  <GitFork size={13} /> {numberFormat.format(snapshot.totalDirs)}
                </span>
                <span className="flex items-center gap-1">
                  <Eye size={13} /> {numberFormat.format(snapshot.totalFiles)}
                </span>
              </div>
            </section>

            <section className="rounded-md border border-[#d1d9e0] p-4">
              <h2 className="mb-2 flex items-center justify-between text-[15px] font-semibold text-[#1f2328]">
                نسخه‌ها
                <span className="text-xs font-normal text-[#0969da]">Releases</span>
              </h2>
              <ul className="space-y-2 text-xs text-[#59636e]">
                <li className="flex items-center justify-between gap-2">
                  <span className="rounded-full border border-[#1a7f37]/30 px-2 py-[1px] font-semibold text-[#1a7f37]">v1.0.0</span>
                  <span>نسخهٔ پایدار فعلی</span>
                </li>
                <li className="flex items-center justify-between gap-2">
                  <span className="rounded-full border border-[#d1d9e0] px-2 py-[1px] font-semibold text-[#59636e]">beta</span>
                  <span>نسخهٔ Laravel پنل مدیریت</span>
                </li>
              </ul>
            </section>

            <section className="rounded-md border border-[#d1d9e0] p-4">
              <h2 className="mb-2 text-[15px] font-semibold text-[#1f2328]">راهنمای سریع</h2>
              <ul className="space-y-1.5 text-xs leading-6 text-[#59636e]">
                <li>• روی هر پوشه بزنید تا محتوایش باز شود.</li>
                <li>• با کادر «Go to file» بین فایل‌ها جست‌وجو کنید.</li>
                <li>• روی هر فایل بزنید تا کد آن نمایش داده شود.</li>
                <li>• ستون آخر، تاریخ آخرین کامیت همان فایل است.</li>
              </ul>
            </section>
          </aside>
        </div>
      </main>
    </div>
  );
}
