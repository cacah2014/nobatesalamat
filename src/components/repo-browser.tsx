"use client";

import Link from "next/link";
import { useMemo, useState } from "react";
import { ChevronDown, ChevronRight, Clock, Code, File, Folder, GitBranch, Search } from "lucide-react";
import type { RepoEntry, RepoSnapshot } from "@/lib/repo-browser";

const githubFont =
  '-apple-system, BlinkMacSystemFont, "Segoe UI", "Noto Sans", Helvetica, Arial, sans-serif';

const numberFormat = new Intl.NumberFormat("en-US");

function nameParts(entry: RepoEntry) {
  const segments = entry.path.split("/");
  const parent = segments.slice(0, -1).join("/");
  return { parent, name: entry.name };
}

export function RepoBrowser({ snapshot }: { snapshot: RepoSnapshot }) {
  const [query, setQuery] = useState("");
  const [open, setOpen] = useState(false);

  const entries = useMemo(() => {
    const term = query.trim().toLowerCase();
    if (!term) return snapshot.entries;
    return snapshot.entries.filter((entry) => entry.path.toLowerCase().includes(term));
  }, [query, snapshot.entries]);

  const folders = snapshot.entries.filter((entry) => entry.kind === "dir").length;
  const files = snapshot.entries.length - folders;

  return (
    <div style={{ fontFamily: githubFont }}>
      {/* نوار ابزار: انتخاب شاخه، جست‌وجوی فایل، افزودن فایل و دکمهٔ Code */}
      <div className="flex flex-wrap items-center gap-2 border-b border-[#d1d9e0] pb-4">
        <button
          type="button"
          onClick={() => setOpen((value) => !value)}
          className="relative flex h-8 items-center gap-1.5 rounded-md border border-[#d1d9e0] bg-[#f6f8fa] px-3 text-sm font-semibold text-[#1f2328] shadow-[0_1px_0_rgba(31,35,40,.04)] hover:bg-[#eff2f5]"
        >
          <GitBranch size={14} className="text-[#59636e]" />
          {snapshot.branch}
          <ChevronDown size={13} className="text-[#59636e]" />
          {open && (
            <span className="absolute left-0 top-9 z-20 w-56 rounded-md border border-[#d1d9e0] bg-white p-2 text-left text-xs font-normal shadow-lg">
              <span className="block rounded px-2 py-1.5 text-[#59636e]">Switch branches/tags</span>
              <span className="mt-1 flex items-center justify-between rounded bg-[#f6f8fa] px-2 py-1.5 font-semibold text-[#1f2328]">
                {snapshot.branch}
                <span className="text-[10px] font-normal text-[#59636e]">default</span>
              </span>
            </span>
          )}
        </button>

        <div className="relative min-w-0 flex-1 sm:max-w-xs">
          <Search size={14} className="pointer-events-none absolute left-2.5 top-1/2 -translate-y-1/2 text-[#59636e]" />
          <input
            value={query}
            onChange={(event) => setQuery(event.target.value)}
            placeholder="Go to file"
            className="h-8 w-full rounded-md border border-[#d1d9e0] bg-[#f6f8fa] pl-8 pr-3 text-sm text-[#1f2328] outline-none placeholder:text-[#59636e] focus:border-[#0969da] focus:bg-white focus:ring-2 focus:ring-[#0969da]/20"
          />
        </div>

        <div className="ml-auto flex items-center gap-2">
          <button
            type="button"
            className="hidden h-8 items-center gap-1.5 rounded-md border border-[#d1d9e0] bg-[#f6f8fa] px-3 text-sm font-semibold text-[#1f2328] hover:bg-[#eff2f5] sm:flex"
          >
            Add file
            <ChevronDown size={13} className="text-[#59636e]" />
          </button>
          <button
            type="button"
            className="flex h-8 items-center gap-1.5 rounded-md border border-[#1a7f37]/40 bg-[#1f883d] px-3 text-sm font-semibold text-white shadow-[0_1px_0_rgba(31,35,40,.1)] hover:bg-[#1a7f37]"
          >
            <Code size={14} />
            Code
            <ChevronDown size={13} className="opacity-80" />
          </button>
        </div>
      </div>

      {/* جدول فایل‌ها با همان چیدمان تصویر */}
      <div className="mt-4 overflow-hidden rounded-md border border-[#d1d9e0] bg-white shadow-[0_1px_0_rgba(31,35,40,.04)]">
        <div className="flex items-center gap-2 border-b border-[#d1d9e0] bg-[#f6f8fa] px-3 py-2.5 text-xs text-[#59636e]">
          <span className="grid size-5 shrink-0 place-items-center overflow-hidden rounded-full bg-[#8250df] text-[10px] font-bold text-white">
            {(snapshot.latestCommit?.author || "S").slice(0, 1)}
          </span>
          <span className="truncate">
            <strong className="font-semibold text-[#1f2328]">{snapshot.latestCommit?.author ?? "—"}</strong>{" "}
            <span className="truncate">{snapshot.latestCommit?.subject ?? "—"}</span>
          </span>
          <span className="hidden shrink-0 items-center gap-1 rounded-full border border-[#1a7f37]/30 px-1.5 py-[1px] text-[10px] font-medium text-[#1a7f37] sm:flex">
            <span className="grid size-3 place-items-center rounded-full bg-[#1a7f37] text-[7px] text-white">✓</span>
            Verified
          </span>
          <span className="ml-auto flex shrink-0 items-center gap-3">
            <span className="hidden text-[#59636e] md:inline">
              {snapshot.latestCommit?.shortSha}
              <span className="mx-1.5">·</span>
              {snapshot.latestCommit?.time}
            </span>
            <span className="flex items-center gap-1 text-[#59636e]">
              <Clock size={13} />
              {numberFormat.format(snapshot.totalCommits)} Commits
            </span>
          </span>
        </div>

        <div className="flex items-center border-b border-[#d1d9e0] bg-[#f6f8fa] px-3 py-1.5 text-xs text-[#59636e]">
          <span className="min-w-0 flex-1">Name</span>
          <span className="hidden min-w-0 flex-[1.15] sm:block">Last commit message</span>
          <span className="w-[104px] shrink-0 text-right sm:w-[132px]">Last commit date</span>
        </div>

        {snapshot.dirPath && (
          <Link
            href={snapshot.dirPath.split("/").length > 1 ? `/repo/${snapshot.dirPath.split("/").slice(0, -1).map(encodeURIComponent).join("/")}` : "/repo"}
            className="flex items-center gap-2 border-b border-[#d1d9e0] px-3 py-[7px] text-sm text-[#0969da] hover:bg-[#f6f8fa]"
          >
            <Folder size={16} className="text-[#54aeff]" fill="#54aeff" fillOpacity={0.25} />
            ..
          </Link>
        )}

        {entries.map((entry) => {
          const { parent } = nameParts(entry);
          return (
            <div
              key={entry.path}
              className="flex items-center border-b border-[#d1d9e0] text-sm last:border-b-0 hover:bg-[#f6f8fa]"
            >
              <span className="flex min-w-0 flex-1 items-center gap-2 px-3 py-[7px]">
                {entry.kind === "dir" ? (
                  <Folder size={16} className="shrink-0 text-[#54aeff]" fill="#54aeff" fillOpacity={0.25} />
                ) : (
                  <File size={16} className="shrink-0 text-[#59636e]" />
                )}
                <Link
                  href={entry.href ?? "#"}
                  className="min-w-0 truncate text-[#1f2328] hover:text-[#0969da] hover:underline"
                >
                  {parent && <span className="text-[#59636e]">{parent}/</span>}
                  {entry.name}
                </Link>
              </span>

              <span className="hidden min-w-0 flex-[1.15] items-center gap-1 pr-2 text-xs text-[#59636e] sm:flex">
                <Link
                  href={entry.href ?? "#"}
                  className="min-w-0 truncate hover:text-[#0969da] hover:underline"
                  title={entry.commitMessage}
                >
                  {entry.commitMessage}
                </Link>
              </span>

              <span className="w-[104px] shrink-0 truncate px-3 text-right text-xs text-[#59636e] sm:w-[132px]">
                {entry.commitIso ? (
                  <time dateTime={entry.commitIso} title={entry.commitIso}>
                    {entry.commitTime}
                  </time>
                ) : (
                  entry.commitTime
                )}
              </span>
            </div>
          );
        })}

        {entries.length === 0 && (
          <p className="px-3 py-6 text-center text-sm text-[#59636e]">
            {query ? `هیچ فایلی با «${query}» پیدا نشد.` : "این پوشه خالی است."}
          </p>
        )}
      </div>

      <p className="mt-3 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-[#59636e]">
        <span className="flex items-center gap-1">
          <Folder size={13} /> {numberFormat.format(folders)} پوشه
        </span>
        <span className="flex items-center gap-1">
          <File size={13} /> {numberFormat.format(files)} فایل
        </span>
        <span className="text-[#8c959f]">·</span>
        <span>
          کل پروژه: {numberFormat.format(snapshot.totalFiles)} فایل و {numberFormat.format(snapshot.totalDirs)} پوشه
        </span>
        {query && (
          <span className="flex items-center gap-1 text-[#0969da]">
            <ChevronRight size={12} /> فیلتر فعال
          </span>
        )}
      </p>
    </div>
  );
}
