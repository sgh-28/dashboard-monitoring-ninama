<!DOCTYPE html>
<html>
<head>
    <title>Integration Test Result</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f8f9fa; padding: 20px; }
        table { width: 100%; border-collapse: collapse; background: white; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #343a40; color: white; }
        .success { color: green; font-weight: bold; }
        .failed { color: red; font-weight: bold; }
        .pending { color: orange; font-weight: bold; }
        a { color: #007bff; text-decoration: none; }
    </style>
</head>
<body>

<h2>🔍 Integration Test Result</h2>

<table>
    <tr>
        <th>Fitur</th>
        <th>Status</th>
        <th>Keterangan</th>
    </tr>

    <tr>
        <td>📅 Google Calendar</td>
        <td class="{{ strtolower($results['calendar']['status']) }}">
            {{ $results['calendar']['status'] }}
        </td>
        <td>
            @if(str_contains($results['calendar']['message'], 'https'))
                <a href="{{ $results['calendar']['message'] }}" target="_blank">📌 Buka Event</a>
            @else
                {{ $results['calendar']['message'] }}
            @endif
        </td>
    </tr>

    <tr>
        <td>📨 WhatsApp Notification</td>
        <td class="{{ strtolower($results['whatsapp']['status']) }}">
            {{ $results['whatsapp']['status'] }}
        </td>
        <td>{{ $results['whatsapp']['message'] }}</td>
    </tr>

    <tr>
        <td>📧 Email Notification</td>
        <td class="{{ strtolower($results['email']['status']) }}">
            {{ $results['email']['status'] }}
        </td>
        <td>{{ $results['email']['message'] }}</td>
    </tr>

</table>

<br>
<a href="/test-integrasi">🔄 Test Again</a>

</body>
</html>
