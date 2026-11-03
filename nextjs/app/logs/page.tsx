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
      <h1>Activation log</h1>
      {error && <div className="error">{error}</div>}
      <div className="card"><LogTable rows={rows} /></div>
    </Shell>
  );
}
