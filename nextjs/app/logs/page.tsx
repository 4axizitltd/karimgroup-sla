'use client';

import { useEffect, useState } from 'react';
import Shell from '@/components/Shell';
import LogTable from '@/components/LogTable';
import { api } from '@/lib/api';

export default function LogsPage() {
  const [rows, setRows] = useState<any[]>([]);
  const [error, setError] = useState('');

  useEffect(() => { api('/logs').then((r) => setRows(r.data)).catch((e) => setError(e.message)); }, []);

  return (
    <Shell>
      <div className="page-head">
        <div>
          <h1>Activation log</h1>
          <p className="page-sub">Every activation and daily check made by client software.</p>
        </div>
      </div>
      {error && <div className="error">{error}</div>}
      <div className="card flush"><LogTable rows={rows} /></div>
    </Shell>
  );
}
