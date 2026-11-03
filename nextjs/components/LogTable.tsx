export default function LogTable({ rows }: { rows: any[] }) {
  return (
    <div className="table-wrap">
      <table>
        <thead><tr><th>Time</th><th>Action</th><th>Client</th><th>Code</th><th>Result</th><th>IP</th></tr></thead>
        <tbody>
          {rows.map((l) => (
            <tr key={l.id}>
              <td>{new Date(l.created_at).toLocaleString()}</td>
              <td>{l.action}</td>
              <td>{l.client_code}</td>
              <td>{l.code ? <code>{l.code}</code> : '—'}</td>
              <td><span className={`badge ${l.success ? 'b-active' : 'b-expired'}`}>{l.message}</span></td>
              <td className="muted">{l.ip}</td>
            </tr>
          ))}
          {!rows.length && <tr><td colSpan={6} className="empty">No activity yet.</td></tr>}
        </tbody>
      </table>
    </div>
  );
}
