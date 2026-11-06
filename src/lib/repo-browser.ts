import { execFileSync } from "node:child_process";
import fs from "node:fs";
import path from "node:path";

export type RepoEntryKind = "dir" | "file";

export type RepoEntry = {
  name: string;
  kind: RepoEntryKind;
  /** مسیر نسبی از ریشهٔ ریپو */
  path: string;
  /** آدرس صفحهٔ متناظر */
  href: string | null;
  size: number;
  commitMessage: string;
  commitTime: string;
  commitIso: string | null;
};

export type RepoCommit = {
  author: string;
  subject: string;
  shortSha: string;
  time: string;
  iso: string | null;
};

export type RepoSnapshot = {
  repoRoot: string;
  repoName: string;
  owner: string;
  branch: string;
  totalCommits: number;
  latestCommit: RepoCommit | null;
  entries: RepoEntry[];
  dirPath: string;
  totalFiles: number;
  totalDirs: number;
};

const IGNORED = new Set([".git", "node_modules", ".next", ".turbo", ".vercel", ".arena", "coverage", "dist", "out"]);

export const REPO_ROOT = process.env.REPO_ROOT ? path.resolve(process.env.REPO_ROOT) : process.cwd();

function git(args: string[], cwd = REPO_ROOT): string | null {
  try {
    return execFileSync("git", args, {
      cwd,
      encoding: "utf8",
      stdio: ["ignore", "pipe", "ignore"],
      timeout: 4000,
      maxBuffer: 4 * 1024 * 1024,
    }).trim();
  } catch {
    return null;
  }
}

const commitCache = new Map<string, { value: { message: string; time: string; iso: string | null }; at: number }>();

function lastCommitFor(relPath: string): { message: string; time: string; iso: string | null } {
  const cached = commitCache.get(relPath);
  if (cached && Date.now() - cached.at < 60_000) return cached.value;

  const raw = git(["log", "-1", "--format=%s%x1f%cr%x1f%cI", "--", relPath || "."]);
  let value = { message: "—", time: "—", iso: null as string | null };
  if (raw) {
    const [message, time, iso] = raw.split("\x1f");
    if (message) value = { message, time: time || "—", iso: iso || null };
  }
  commitCache.set(relPath, { value, at: Date.now() });
  return value;
}

function resolveInsideRoot(relPath: string): string {
  const target = path.resolve(REPO_ROOT, relPath);
  if (target !== REPO_ROOT && !target.startsWith(REPO_ROOT + path.sep)) {
    throw new Error("مسیر خارج از ریشهٔ پروژه است");
  }
  return target;
}

export function normalizeDirPath(segments: string[] | undefined): string {
  if (!segments?.length) return "";
  return segments
    .map((segment) => decodeURIComponent(segment))
    .filter((segment) => segment && segment !== "." && segment !== "..")
    .join("/");
}

export function entryHref(relPath: string, kind: RepoEntryKind): string | null {
  const encoded = relPath
    .split("/")
    .map((part) => encodeURIComponent(part))
    .join("/");
  // پوشه‌ها در همین مرورگر باز می‌شوند؛ فایل‌ها در گیت‌هاب واقعی باز می‌شوند.
  return kind === "dir" ? `/repo/${encoded}` : `/repo/${encoded}`;
}

export function readRepoSnapshot(dirPath: string, options: { commitLimit?: number } = {}): RepoSnapshot {
  const absolute = resolveInsideRoot(dirPath);
  let stat: fs.Stats;
  try {
    stat = fs.statSync(absolute);
  } catch {
    throw new Error("این مسیر در پروژه پیدا نشد");
  }
  if (!stat.isDirectory()) throw new Error("این مسیر یک پوشه نیست");

  // مثل گیت‌هاب، فایل‌های نقطه‌دار (.env.example، .gitattributes …) هم نمایش داده می‌شوند.
  const names = fs.readdirSync(absolute).filter((name) => !IGNORED.has(name));

  const commitLimit = options.commitLimit ?? 120;

  const entries: RepoEntry[] = names
    .map((name) => {
      const relPath = dirPath ? `${dirPath}/${name}` : name;
      let isDir = false;
      let size = 0;
      try {
        const itemStat = fs.statSync(path.join(absolute, name));
        isDir = itemStat.isDirectory();
        size = itemStat.size;
      } catch {
        return null;
      }
      const kind: RepoEntryKind = isDir ? "dir" : "file";
      return { name, kind, relPath, size };
    })
    .filter((item): item is { name: string; kind: RepoEntryKind; relPath: string; size: number } => item !== null)
    .sort((a, b) => {
      if (a.kind !== b.kind) return a.kind === "dir" ? -1 : 1;
      return a.name.localeCompare(b.name, "en", { sensitivity: "base", numeric: true });
    })
    .slice(0, 400)
    .map((item, index) => {
      const commit =
        index < commitLimit
          ? lastCommitFor(item.relPath)
          : { message: "—", time: "—", iso: null };
      return {
        name: item.name,
        kind: item.kind,
        path: item.relPath,
        href: entryHref(item.relPath, item.kind),
        size: item.size,
        commitMessage: commit.message,
        commitTime: commit.time,
        commitIso: commit.iso,
      };
    });

  const branch = git(["rev-parse", "--abbrev-ref", "HEAD"]) ?? "main";
  const totalCommitsRaw = git(["rev-list", "--count", "HEAD"]);
  const latestRaw = git(["log", "-1", "--format=%an%x1f%s%x1f%h%x1f%cr%x1f%cI"]);

  let latestCommit: RepoCommit | null = null;
  if (latestRaw) {
    const [author, subject, shortSha, time, iso] = latestRaw.split("\x1f");
    latestCommit = {
      author: author || "—",
      subject: subject || "—",
      shortSha: shortSha || "—",
      time: time || "—",
      iso: iso || null,
    };
  }

  const remote = git(["config", "--get", "remote.origin.url"]);
  let owner = "cacah2014";
  let repoName = path.basename(REPO_ROOT);
  if (remote) {
    const match = remote.match(/[/:]([^/:]+)\/([^/]+?)(?:\.git)?$/);
    if (match) {
      owner = match[1];
      repoName = match[2];
    }
  }

  let totalFiles = 0;
  let totalDirs = 0;
  const walk = (dir: string) => {
    let items: fs.Dirent[] = [];
    try {
      items = fs.readdirSync(dir, { withFileTypes: true });
    } catch {
      return;
    }
    for (const item of items) {
      if (IGNORED.has(item.name)) continue;
      if (item.isDirectory()) {
        totalDirs += 1;
        if (totalDirs < 2000) walk(path.join(dir, item.name));
      } else {
        totalFiles += 1;
      }
    }
  };
  walk(REPO_ROOT);

  return {
    repoRoot: REPO_ROOT,
    repoName,
    owner,
    branch,
    totalCommits: Number(totalCommitsRaw || 0),
    latestCommit,
    entries,
    dirPath,
    totalFiles,
    totalDirs,
  };
}

export type RepoFileView = {
  path: string;
  name: string;
  size: number;
  lines: number;
  content: string;
  isBinary: boolean;
  truncated: boolean;
  commitMessage: string;
  commitTime: string;
  commitIso: string | null;
};

export function pathKind(relPath: string): RepoEntryKind | null {
  try {
    const absolute = resolveInsideRoot(relPath);
    const stat = fs.statSync(absolute);
    if (stat.isDirectory()) return "dir";
    if (stat.isFile()) return "file";
    return null;
  } catch {
    return null;
  }
}

const MAX_FILE_BYTES = 160 * 1024;

export function readRepoFile(relPath: string): RepoFileView {
  const absolute = resolveInsideRoot(relPath);
  const stat = fs.statSync(absolute);
  if (!stat.isFile()) throw new Error("این مسیر یک فایل نیست");

  const buffer = fs.readFileSync(absolute);
  const slice = buffer.subarray(0, MAX_FILE_BYTES);
  const isBinary = slice.includes(0);
  const content = isBinary ? "" : new TextDecoder("utf-8", { fatal: false }).decode(slice);
  const commit = lastCommitFor(relPath);

  return {
    path: relPath,
    name: path.basename(relPath),
    size: stat.size,
    lines: isBinary ? 0 : content.split(/\r\n|\r|\n/).length,
    content,
    isBinary,
    truncated: buffer.length > MAX_FILE_BYTES,
    commitMessage: commit.message,
    commitTime: commit.time,
    commitIso: commit.iso,
  };
}

export function parentPath(relPath: string): string {
  const parts = relPath.split("/").filter(Boolean);
  parts.pop();
  return parts.join("/");
}

export function formatFileSize(bytes: number): string {
  if (bytes < 1024) return `${bytes} B`;
  if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
  return `${(bytes / 1024 / 1024).toFixed(1)} MB`;
}
