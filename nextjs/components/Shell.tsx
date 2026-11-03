'use client';

import Link from 'next/link';
import { usePathname, useRouter } from 'next/navigation';
import { ReactNode, useEffect, useState } from 'react';
import { api, getToken, setToken } from '@/lib/api';

export default function Shell({ children }: { children: ReactNode }) {
  const router = useRouter();
  const path = usePathname();
  const [ready, setReady] = useState(false);

  useEffect(() => {
    if (!getToken()) router.replace('/login');
    else setReady(true);
  }, [router]);

  async function logout() {
    try { await api('/logout', { method: 'POST' }); } catch {}
    setToken(null);
    router.replace('/login');
  }

  if (!ready) return null;

  const nav = [
    ['/', 'Dashboard'],
    ['/clients', 'Clients'],
    ['/payments', 'Payments'],
    ['/logs', 'Activation log'],
  ];

  return (
    <>
      <header className="top">
        <span className="brand">4axiz SLA</span>
        {nav.map(([href, label]) => (
          <Link key={href} href={href} className={(href === '/' ? path === '/' : path.startsWith(href)) ? 'active' : ''}>
            {label}
          </Link>
        ))}
        <span className="spacer" />
        <a href="#" onClick={(e) => { e.preventDefault(); logout(); }}>Logout</a>
      </header>
      <main>{children}</main>
    </>
  );
}
