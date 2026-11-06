import { getCurrentUser, hasPermission, isAdmin, type AdminPermission } from "@/lib/auth";

export async function requireAdmin() {
  const user = await getCurrentUser();
  if (!isAdmin(user)) return null;
  return user;
}

export async function requireSuperAdmin() {
  const user = await getCurrentUser();
  if (!user || user.role !== "super_admin") return null;
  return user;
}

export async function requirePermission(permission: AdminPermission) {
  const user = await getCurrentUser();
  if (!hasPermission(user, permission)) return null;
  return user;
}
