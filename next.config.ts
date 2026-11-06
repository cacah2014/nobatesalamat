import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  // اجازهٔ پیش‌نمایش امن در محیط‌های sandbox/preview (پروکسی e2b)
  allowedDevOrigins: ["*.e2b.app", "*.arena.ai", "localhost", "127.0.0.1"],
};

export default nextConfig;
