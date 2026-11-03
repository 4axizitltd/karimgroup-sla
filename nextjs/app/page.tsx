'use client';

import Link from 'next/link';
import { useEffect, useState } from 'react';
import Shell from '@/components/Shell';
import LogTable from '@/components/LogTable';
import { api, fmtDate, fmtMoney } from '@/lib/api';

const LABEL: Record<string, string> = { active: 'Active', expiring: 'Expiring soon', expired: 'Expired / unpaid' };

export default function Dashboard() {
  const [d, setD] = useState<any>(null);
  const [error, setError] = useState('');

  useEffect(() => { api('/dashboard').then(setD).catch((e) => setError(e.message)); }, []);

  return (
    <Shell>
      <div className="page-head">
        <div>
          <h1>Dashboard</h1>
          <p className="page-sub">SLA status across all clients at a glance.</p>
        </div>
        <Link href="/clients"><button className="secondary">Manage clients</button></Link>
      </div>
      {error && <div className="error">{error}</div>}
      {!d && !error && <p className="muted">Loading…</p>}
      {d && (
        <>
          <div className="grid">
            <div className="card stat-card" style={{ ['--accent' as any]: 'var(--ok)' }}><div className="stat-label">Active</div><div className="stat">{d.counts.active}</div></div>
            <div className="card stat-card" style={{ ['--accent' as any]: 'var(--warn)' }}><div className="stat-label">Expiring soon</div><div className="stat">{d.counts.expiring}</div></div>
            <div className="card stat-card" style={{ ['--accent' as any]: 'var(--bad)' }}><div className="stat-label">Expired / unpaid</div><div className="stat">{d.counts.expired}</div></div>
            <div className="card stat-card" style={{ ['--accent' as any]: 'var(--primary)' }}><div className="stat-label">Collected this year</div><div className="stat">৳ {fmtMoney(d.collected_this_year)}</div></div>
          </div>

          <div className="card flush">
            <div className="card-head" style={{ padding: '18px 22px 0', marginBottom: 8 }}><h2>SLA by client</h2></div>
            <div className="table-wrap">
              <table>
                <thead><tr><th>Client</th><th>Application</th><th>Per quarter</th><th>Covered until</th><th>Status</th><th>Last online</th><th></th></tr></thead>
                <tbody>
                  {d.subscriptions.map((s: any) => (
                    <tr key={s.id}>
                      <td><strong>{s.client.name}</strong> <span className="muted">({s.client.code})</span></td>
                      <td>{s.application.name}</td>
                      <td>৳ {fmtMoney(s.quarterly_amount)}</td>
                      <td>{fmtDate(s.covered_until)}</td>
                      <td><span className={`badge b-${s.state}`}>{LABEL[s.state]}</span></td>
                      <td className="muted">{s.last_seen_at ? new Date(s.last_seen_at).toLocaleString() : '—'}</td>
                      <td><Link href={`/clients/${s.client.id}`}>Open →</Link></td>
                    </tr>
                  ))}
                  {!d.subscriptions.length && <tr><td colSpan={7} className="empty">No subscriptions yet. Add a client and an application to begin.</td></tr>}
                </tbody>
              </table>
            </div>
          </div>

          <div className="card flush">
            <div className="card-head" style={{ padding: '18px 22px 0', marginBottom: 8 }}><h2>Recent activation activity</h2></div>
            <LogTable rows={d.recent_logs} />
          </div>
        </>
      )}
    </Shell>
  );
}
