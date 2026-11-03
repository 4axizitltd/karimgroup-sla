'use client';

import Link from 'next/link';
import { useEffect, useState } from 'react';
import Shell from '@/components/Shell';
import { api, fmtDate, fmtMoney } from '@/lib/api';

export default function PaymentsPage() {
  const [rows, setRows] = useState<any[]>([]);
  const [error, setError] = useState('');

  useEffect(() => { api('/payments').then((r) => setRows(r.data)).catch((e) => setError(e.message)); }, []);

  return (
    <Shell>
      <div className="page-head">
        <div>
          <h1>Payments</h1>
          <p className="page-sub">To record a new payment, open the client and use “Record payment &amp; generate code”.</p>
        </div>
      </div>
      {error && <div className="error">{error}</div>}
      <div className="card flush table-wrap">
        <table>
          <thead><tr><th>Paid on</th><th>Client</th><th>App</th><th>Period</th><th>Amount</th><th>Method</th><th>Code</th></tr></thead>
          <tbody>
            {rows.map((p) => (
              <tr key={p.id}>
                <td>{fmtDate(p.paid_at)}</td>
                <td><Link href={`/clients/${p.subscription.client.id}`}><strong>{p.subscription.client.name}</strong></Link></td>
                <td>{p.subscription.application.code}</td>
                <td>{fmtDate(p.period_from)} – {fmtDate(p.period_to)}</td>
                <td><strong>৳ {fmtMoney(p.amount)}</strong></td>
                <td>{p.method || '—'}</td>
                <td>{p.activation_code ? <code>{p.activation_code.code}</code> : '—'}</td>
              </tr>
            ))}
            {!rows.length && <tr><td colSpan={7} className="empty">No payments yet.</td></tr>}
          </tbody>
        </table>
      </div>
    </Shell>
  );
}
