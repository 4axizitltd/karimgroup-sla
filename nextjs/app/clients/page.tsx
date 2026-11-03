'use client';

import Link from 'next/link';
import { FormEvent, useEffect, useState } from 'react';
import Shell from '@/components/Shell';
import { api } from '@/lib/api';

const empty = { code: '', name: '', contact_person: '', phone: '', email: '' };

export default function ClientsPage() {
  const [rows, setRows] = useState<any[]>([]);
  const [form, setForm] = useState(empty);
  const [error, setError] = useState('');
  const [busy, setBusy] = useState(false);

  const load = () => api('/clients').then(setRows).catch((e) => setError(e.message));
  useEffect(() => { load(); }, []);

  async function add(e: FormEvent) {
    e.preventDefault();
    setBusy(true); setError('');
    try {
      await api('/clients', { method: 'POST', body: form });
      setForm(empty);
      load();
    } catch (err: any) {
      setError(/already been taken/i.test(err.message) ? `Client code “${form.code}” is already used. Choose a different code.` : err.message);
    } finally { setBusy(false); }
  }

  const set = (k: keyof typeof empty) => (e: any) => setForm({ ...form, [k]: e.target.value });

  return (
    <Shell>
      <div className="page-head">
        <div>
          <h1>Clients</h1>
          <p className="page-sub">{rows.length} client{rows.length === 1 ? '' : 's'} on SLA</p>
        </div>
      </div>
      {error && <div className="error">{error}</div>}

      <div className="card flush table-wrap">
        <table>
          <thead><tr><th>Code</th><th>Name</th><th>Contact</th><th>Phone</th><th>Applications</th><th>Status</th><th></th></tr></thead>
          <tbody>
            {rows.map((c) => (
              <tr key={c.id}>
                <td><code>{c.code}</code></td>
                <td><Link href={`/clients/${c.id}`}><strong>{c.name}</strong></Link></td>
                <td>{c.contact_person || '—'}</td>
                <td>{c.phone || '—'}</td>
                <td>{c.subscriptions_count}</td>
                <td><span className={`badge ${c.is_active ? 'b-active' : 'b-expired'}`}>{c.is_active ? 'Active' : 'Disabled'}</span></td>
                <td><Link href={`/clients/${c.id}`}>Open →</Link></td>
              </tr>
            ))}
            {!rows.length && <tr><td colSpan={7} className="empty">No clients yet. Add the first one below.</td></tr>}
          </tbody>
        </table>
      </div>

      <div className="card">
        <h2>Add client</h2>
        <form className="inline" onSubmit={add}>
          <div><label>Code (e.g. KG)</label><input value={form.code} onChange={set('code')} required maxLength={20} /></div>
          <div><label>Name</label><input value={form.name} onChange={set('name')} required /></div>
          <div><label>Contact person</label><input value={form.contact_person} onChange={set('contact_person')} /></div>
          <div><label>Phone</label><input value={form.phone} onChange={set('phone')} /></div>
          <div><label>Email</label><input type="email" value={form.email} onChange={set('email')} /></div>
          <div><button disabled={busy}>{busy ? 'Adding…' : 'Add client'}</button></div>
        </form>
      </div>
    </Shell>
  );
}
