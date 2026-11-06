import { destroySession } from "@/lib/auth";

export async function POST(request: Request) {
  await destroySession();
  return Response.redirect(new URL("/", request.url), 303);
}
