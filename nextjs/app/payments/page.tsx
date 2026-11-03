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
      <h1>Payments</h1>
      <p className="muted">To record a new payment, open the client and use “Record payment &amp; generate code”.</p>
      {error && <div className="error">{error}</div>}
      <div className="card table-wrap">
        <table>
          <thead><tr><th>Paid on</th><th>Client</th><th>App</th><th>Period</th><th>Amount</th><th>Method</th><th>Code</th></tr></thead>
          <tbody>
            {rows.map((p) => (
              <tr key={p.id}>
                <td>{fmtDate(p.paid_at)}</td>
                <td><Link href={`/clients/${p.subscription.client.id}`}>{p.subscription.client.name}</Link></td>
                <td>{p.subscription.application.code}</td>
                <td>{fmtDate(p.period_from)} – {fmtDate(p.period_to)}</td>
                <td>৳ {fmtMoney(p.amount)}</td>
                <td>{p.method || '—'}</td>
                <td>{p.activation_code ? <code>{p.activation_code.code}</code> : '—'}</td>
              </tr>
            ))}
            {!rows.length && <tr><td colSpan={7} className="muted">No payments yet.</td></tr>}
          </tbody>
        </table>
      </div>
    </Shell>
  );
}
