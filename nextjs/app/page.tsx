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
      <h1>Dashboard</h1>
      {error && <div className="error">{error}</div>}
      {d && (
        <>
          <div className="grid">
            <div className="card"><div className="muted">Active</div><div className="stat" style={{ color: 'var(--ok)' }}>{d.counts.active}</div></div>
            <div className="card"><div className="muted">Expiring soon</div><div className="stat" style={{ color: 'var(--warn)' }}>{d.counts.expiring}</div></div>
            <div className="card"><div className="muted">Expired / unpaid</div><div className="stat" style={{ color: 'var(--bad)' }}>{d.counts.expired}</div></div>
            <div className="card"><div className="muted">Collected this year</div><div className="stat">৳ {fmtMoney(d.collected_this_year)}</div></div>
          </div>

          <div className="card">
            <h2>SLA by client</h2>
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
                      <td>{s.last_seen_at ? new Date(s.last_seen_at).toLocaleString() : '—'}</td>
                      <td><Link href={`/clients/${s.client.id}`}>Open →</Link></td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>

          <div className="card">
            <h2>Recent activation activity</h2>
            <LogTable rows={d.recent_logs} />
          </div>
        </>
      )}
    </Shell>
  );
}
