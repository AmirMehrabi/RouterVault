<!DOCTYPE html>
<html lang="en">
<body style="margin:0;background:#f8fafc;font-family:Arial,sans-serif;color:#0f172a">
    <div style="max-width:640px;margin:0 auto;padding:32px 16px">
        <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;padding:24px">
            <p style="margin:0 0 8px;color:#64748b;font-size:12px;text-transform:uppercase;letter-spacing:.08em">RouterVault configuration alert</p>
            <h1 style="margin:0;font-size:20px">{{ $alert->summary }}</h1>
            <table style="width:100%;margin-top:20px;border-collapse:collapse;font-size:14px">
                <tr><td style="padding:8px 0;color:#64748b">Router</td><td style="padding:8px 0;text-align:right;font-weight:600">{{ $alert->router?->name ?? 'Unknown' }}</td></tr>
                <tr><td style="padding:8px 0;color:#64748b">Severity</td><td style="padding:8px 0;text-align:right;font-weight:600">{{ ucfirst($alert->severity) }}</td></tr>
                <tr><td style="padding:8px 0;color:#64748b">Changes</td><td style="padding:8px 0;text-align:right;font-weight:600">+{{ $alert->added_lines }} / -{{ $alert->removed_lines }}</td></tr>
                <tr><td style="padding:8px 0;color:#64748b">Sections</td><td style="padding:8px 0;text-align:right">{{ implode(', ', $alert->sections ?? []) ?: 'General configuration' }}</td></tr>
            </table>
            <p style="margin:24px 0 0"><a href="{{ route('diff-alerts.show', $alert) }}" style="display:inline-block;background:#2563eb;color:#fff;text-decoration:none;padding:10px 16px;border-radius:8px;font-size:14px;font-weight:600">Review configuration change</a></p>
        </div>
    </div>
</body>
</html>
