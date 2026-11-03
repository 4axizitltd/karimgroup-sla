'use client';

import { useParams } from 'next/navigation';
import { FormEvent, useEffect, useState } from 'react';
import Shell from '@/components/Shell';
import { api, fmtDate, fmtMoney } from '@/lib/api';

export default function ClientDetail() {
  const { id } = useParams<{ id: string }>();
  const [client, setClient] = useState<any>(null);
  const [apps, setApps] = useState<any[]>([]);
  const [error, setError] = useState('');

  const load = () => api(`/clients/${id}`).then(setClient).catch((e) => setError(e.message));
  useEffect(() => {
    load();
    api('/applications').then(setApps).catch(() => {});
  }, [id]);

  if (!client) return <Shell>{error ? <div className="error">{error}</div> : <p className="muted">Loading…</p>}</Shell>;

  const usedAppIds = client.subscriptions.map((s: any) => s.application_id);

  return (
    <Shell>
      <h1>{client.name} <span className="muted">({client.code})</span></h1>
      {error && <div className="error">{error}</div>}
      <p className="muted">{[client.contact_person, client.phone, client.email].filter(Boolean).join(' · ') || 'No contact details'}</p>

      {client.subscriptions.map((s: any) => (
        <Subscription key={s.id} sub={s} clientCode={client.code} onChange={load} onError={setError} />
      ))}

      <AddSubscription
        clientId={client.id}
        apps={apps.filter((a) => !usedAppIds.includes(a.id))}
        onAppsChange={() => api('/applications').then(setApps)}
        onDone={load}
        onError={setError}
      />
    </Shell>
  );
}

function Subscription({ sub, clientCode, onChange, onError }: any) {
  const [issued, setIssued] = useState<any>(null);
  const [offline, setOffline] = useState<Record<number, string>>({});

  async function act(fn: () => Promise<any>) {
    onError('');
    try { await fn(); onChange(); } catch (e: any) { onError(e.message); }
  }

  const revoke = (code: any) => {
    const reason = window.prompt(`Cancel code ${code.code}? Reason (optional):`, '');
    if (reason === null) return;
    act(() => api(`/codes/${code.id}/revoke`, { method: 'POST', body: { reason } }));
  };

  const showOffline = async (code: any) => {
    try {
      const r = await api(`/codes/${code.id}/offline-key`);
      setOffline({ ...offline, [code.id]: r.offline_key });
    } catch (e: any) { onError(e.message); }
  };

  return (
    <div className="card">
      <div className="row" style={{ justifyContent: 'space-between' }}>
        <h2 style={{ margin: 0 }}>{sub.application.name} <span className="muted">({sub.application.code})</span></h2>
        <div className="row">
          <span className="muted">Covered until <strong>{fmtDate(sub.covered_until)}</strong></span>
          <span className={`badge ${sub.is_active ? 'b-active' : 'b-expired'}`}>{sub.is_active ? 'Enabled' : 'Disabled'}</span>
        </div>
      </div>
      <p className="muted">
        Per quarter: ৳ {fmtMoney(sub.quarterly_amount)} · Installation ID: <code>{sub.install_id || 'not activated yet'}</code>
      </p>
      <div className="row" style={{ marginBottom: 16 }}>
        <button className="secondary" onClick={() => act(() => api(`/subscriptions/${sub.id}`, { method: 'PATCH', body: { is_active: !sub.is_active } }))}>
          {sub.is_active ? 'Disable' : 'Enable'}
        </button>
        {sub.install_id && (
          <button className="secondary" onClick={() => {
            if (window.confirm('Client moved to a new server? This lets the current code be activated on a different installation.'))
              act(() => api(`/subscriptions/${sub.id}/reset-install`, { method: 'POST' }));
          }}>Reset installation</button>
        )}
      </div>

      <PaymentForm sub={sub} onIssued={(r: any) => { setIssued(r); onChange(); }} onError={onError} />

      {issued && <IssuedCode issued={issued} clientCode={clientCode} appName={sub.application.name} />}

      <h2 style={{ marginTop: 20 }}>Activation codes</h2>
      <div className="table-wrap">
        <table>
          <thead><tr><th>Code</th><th>Period</th><th>Status</th><th>Activated</th><th>Last online</th><th></th></tr></thead>
          <tbody>
            {sub.activation_codes.map((c: any) => (
              <tr key={c.id}>
                <td>
                  <code>{c.code}</code>
                  {offline[c.id] && <div className="key" style={{ maxWidth: 420, whiteSpace: 'normal', marginTop: 6 }}>{offline[c.id]}</div>}
                </td>
                <td>{fmtDate(c.valid_from)} – {fmtDate(c.valid_until)}</td>
                <td><span className={`badge b-${c.status}`}>{c.status.replace('_', ' ')}</span>{c.revoke_reason && <div className="muted">{c.revoke_reason}</div>}</td>
                <td>{c.activated_at ? new Date(c.activated_at).toLocaleString() : '—'}{c.hostname && <div className="muted">{c.hostname}</div>}</td>
                <td>{c.last_seen_at ? new Date(c.last_seen_at).toLocaleString() : '—'}</td>
                <td className="row">
                  {!c.revoked_at && <button className="secondary" onClick={() => showOffline(c)}>Offline key</button>}
                  {!c.revoked_at && <button className="danger" onClick={() => revoke(c)}>Cancel</button>}
                </td>
              </tr>
            ))}
            {!sub.activation_codes.length && <tr><td colSpan={6} className="muted">No codes yet.</td></tr>}
          </tbody>
        </table>
      </div>

      <h2 style={{ marginTop: 20 }}>Payments</h2>
      <div className="table-wrap">
        <table>
          <thead><tr><th>Paid on</th><th>Period</th><th>Amount</th><th>Method</th><th>Reference</th></tr></thead>
          <tbody>
            {sub.payments.map((p: any) => (
              <tr key={p.id}>
                <td>{fmtDate(p.paid_at)}</td>
                <td>{fmtDate(p.period_from)} – {fmtDate(p.period_to)}</td>
                <td>৳ {fmtMoney(p.amount)}</td>
                <td>{p.method || '—'}</td>
                <td>{p.reference || '—'}</td>
              </tr>
            ))}
            {!sub.payments.length && <tr><td colSpan={5} className="muted">No payments yet.</td></tr>}
          </tbody>
        </table>
      </div>
    </div>
  );
}

function PaymentForm({ sub, onIssued, onError }: any) {
  const today = new Date().toISOString().slice(0, 10);
  const [f, setF] = useState<any>(null);
  const [busy, setBusy] = useState(false);

  async function open() {
    try {
      const s = await api(`/subscriptions/${sub.id}/suggest-period`);
      setF({ period_from: s.period_from, period_to: s.period_to, amount: s.amount, paid_at: today, method: 'Bank', reference: '', note: '' });
    } catch (e: any) { onError(e.message); }
  }

  async function submit(e: FormEvent) {
    e.preventDefault();
    setBusy(true); onError('');
    try {
      const r = await api('/payments', { method: 'POST', body: { ...f, subscription_id: sub.id } });
      setF(null);
      onIssued(r);
    } catch (err: any) { onError(err.message); } finally { setBusy(false); }
  }

  if (!f) return <button onClick={open}>Record payment &amp; generate code</button>;

  const set = (k: string) => (e: any) => setF({ ...f, [k]: e.target.value });
  return (
    <form className="inline card" style={{ background: '#f8fafc' }} onSubmit={submit}>
      <div><label>SLA from</label><input type="date" value={f.period_from} onChange={set('period_from')} required /></div>
      <div><label>SLA until</label><input type="date" value={f.period_to} onChange={set('period_to')} required /></div>
      <div><label>Amount (৳)</label><input type="number" step="0.01" min="0" value={f.amount} onChange={set('amount')} required /></div>
      <div><label>Paid on</label><input type="date" value={f.paid_at} onChange={set('paid_at')} required /></div>
      <div><label>Method</label>
        <select value={f.method} onChange={set('method')}>
          {['Bank', 'Cash', 'Cheque', 'bKash', 'Nagad', 'Other'].map((m) => <option key={m}>{m}</option>)}
        </select>
      </div>
      <div><label>Reference / Txn ID</label><input value={f.reference} onChange={set('reference')} /></div>
      <div className="row">
        <button disabled={busy}>{busy ? 'Saving…' : 'Save & generate code'}</button>
        <button type="button" className="secondary" onClick={() => setF(null)}>Cancel</button>
      </div>
    </form>
  );
}

function IssuedCode({ issued, appName }: any) {
  const c = issued.code;
  const message =
    `Dear Sir/Madam,\nThank you for your SLA payment. Your ${appName} activation code is:\n\n${c.code}\n\n` +
    `Valid: ${fmtDate(c.valid_from)} to ${fmtDate(c.valid_until)}\n` +
    `Please go to Setting > SLA Activation in the software and enter this code.\n\n- 4axiz`;
  const copy = (t: string) => navigator.clipboard?.writeText(t);

  return (
    <div className="success" style={{ marginTop: 14 }}>
      <div>Payment saved. Activation code:</div>
      <div className="row" style={{ margin: '8px 0' }}>
        <span className="code">{c.code}</span>
        <button type="button" onClick={() => copy(c.code)}>Copy code</button>
        <button type="button" className="secondary" onClick={() => copy(message)}>Copy message for client</button>
      </div>
      <div className="muted">Valid {fmtDate(c.valid_from)} – {fmtDate(c.valid_until)}. The client enters it at Setting → SLA Activation (needs internet).</div>
      <details style={{ marginTop: 8 }}>
        <summary>Offline key (if the client server has no internet)</summary>
        <div className="key" style={{ marginTop: 6 }}>{issued.offline_key}</div>
        <button type="button" className="secondary" style={{ marginTop: 6 }} onClick={() => copy(issued.offline_key)}>Copy offline key</button>
      </details>
    </div>
  );
}

function AddSubscription({ clientId, apps, onAppsChange, onDone, onError }: any) {
  const [appId, setAppId] = useState('');
  const [amount, setAmount] = useState('');
  const [newApp, setNewApp] = useState({ code: '', name: '' });

  async function add(e: FormEvent) {
    e.preventDefault();
    onError('');
    try {
      await api('/subscriptions', { method: 'POST', body: { client_id: clientId, application_id: Number(appId), quarterly_amount: Number(amount) } });
      setAppId(''); setAmount('');
      onDone();
    } catch (err: any) { onError(err.message); }
  }

  async function addApp(e: FormEvent) {
    e.preventDefault();
    onError('');
    try {
      await api('/applications', { method: 'POST', body: newApp });
      setNewApp({ code: '', name: '' });
      onAppsChange();
    } catch (err: any) { onError(err.message); }
  }

  return (
    <div className="card">
      <h2>Add application for this client</h2>
      <form className="inline" onSubmit={add}>
        <div><label>Application</label>
          <select value={appId} onChange={(e) => setAppId(e.target.value)} required>
            <option value="">Select…</option>
            {apps.map((a: any) => <option key={a.id} value={a.id}>{a.name} ({a.code})</option>)}
          </select>
        </div>
        <div><label>Amount per quarter (৳)</label><input type="number" min="0" step="0.01" value={amount} onChange={(e) => setAmount(e.target.value)} required /></div>
        <div><button>Add</button></div>
      </form>
      <details style={{ marginTop: 12 }}>
        <summary className="muted">Application not in the list? Create it</summary>
        <form className="inline" style={{ marginTop: 8 }} onSubmit={addApp}>
          <div><label>Code (e.g. HRM)</label><input value={newApp.code} onChange={(e) => setNewApp({ ...newApp, code: e.target.value })} required /></div>
          <div><label>Name</label><input value={newApp.name} onChange={(e) => setNewApp({ ...newApp, name: e.target.value })} required /></div>
          <div><button className="secondary">Create application</button></div>
        </form>
      </details>
    </div>
  );
}
