'use client';

import { FormEvent, useState } from 'react';
import { useRouter } from 'next/navigation';
import { api, setToken } from '@/lib/api';

export default function LoginPage() {
  const router = useRouter();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [busy, setBusy] = useState(false);

  async function submit(e: FormEvent) {
    e.preventDefault();
    setBusy(true); setError('');
    try {
      const res = await api<{ token: string }>('/login', { method: 'POST', body: { email, password } });
      setToken(res.token);
      router.replace('/');
    } catch (err: any) {
      setError(err.message);
    } finally {
      setBusy(false);
    }
  }

  return (
    <div className="login card">
      <h1>4axiz SLA Admin</h1>
      {error && <div className="error">{error}</div>}
      <form onSubmit={submit}>
        <p><label>Email</label><input type="email" value={email} onChange={(e) => setEmail(e.target.value)} required /></p>
        <p><label>Password</label><input type="password" value={password} onChange={(e) => setPassword(e.target.value)} required /></p>
        <button disabled={busy}>{busy ? 'Signing in…' : 'Sign in'}</button>
      </form>
    </div>
  );
}
