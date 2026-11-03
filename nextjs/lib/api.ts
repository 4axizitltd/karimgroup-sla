const BASE = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api';
const TOKEN_KEY = 'sla_admin_token';

export function getToken(): string | null {
  try { return localStorage.getItem(TOKEN_KEY); } catch { return null; }
}
export function setToken(t: string | null) {
  try { t ? localStorage.setItem(TOKEN_KEY, t) : localStorage.removeItem(TOKEN_KEY); } catch {}
}

export class ApiError extends Error {
  constructor(message: string, public status: number) { super(message); }
}

export async function api<T = any>(path: string, opts: { method?: string; body?: unknown } = {}): Promise<T> {
  const token = getToken();
  const res = await fetch(`${BASE}/admin${path}`, {
    method: opts.method || 'GET',
    headers: {
      Accept: 'application/json',
      ...(opts.body !== undefined ? { 'Content-Type': 'application/json' } : {}),
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
    },
    body: opts.body !== undefined ? JSON.stringify(opts.body) : undefined,
  });

  if (res.status === 401) {
    setToken(null);
    if (typeof window !== 'undefined' && !location.pathname.startsWith('/login')) location.href = '/login';
    throw new ApiError('Session expired', 401);
  }
  const data = await res.json().catch(() => ({}));
  if (!res.ok) {
    const first = data?.errors ? (Object.values(data.errors)[0] as string[])[0] : null;
    throw new ApiError(first || data?.message || `Request failed (${res.status})`, res.status);
  }
  return data as T;
}

export const fmtDate = (d?: string | null) =>
  d ? new Date(d.slice(0, 10) + 'T00:00:00').toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : '—';

export const fmtMoney = (n: number | string) =>
  Number(n || 0).toLocaleString('en-IN', { maximumFractionDigits: 2 });
