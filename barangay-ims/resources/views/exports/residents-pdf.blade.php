<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Residents Report</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #333; }
        h1 { font-size: 18px; text-align: center; margin-bottom: 4px; color: #1e3a5f; }
        .subtitle { text-align: center; font-size: 11px; color: #666; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #1e3a5f; color: white; padding: 6px 8px; text-align: left; font-size: 9px; text-transform: uppercase; }
        td { padding: 5px 8px; border-bottom: 1px solid #e5e7eb; }
        tr:nth-child(even) { background: #f9fafb; }
        .total { text-align: right; font-weight: bold; margin-top: 10px; font-size: 11px; }
        .footer { text-align: center; font-size: 8px; color: #999; margin-top: 20px; border-top: 1px solid #e5e7eb; padding-top: 8px; }
    </style>
</head>
<body>
    <div style="text-align: center; margin-bottom: 10px;">
        <img src="{{ public_path('images/logo.png') }}" alt="Barangay Logo" style="width: 40px; height: 40px; object-fit: contain;">
    </div>
    <h1>Barangay Concepcion — Resident Report</h1>
    <p class="subtitle">Generated {{ now()->format('F d, Y') }}</p>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Gender</th>
                <th>Age</th>
                <th>Civil Status</th>
                <th>Purok</th>
                <th>Voter</th>
                <th>PWD</th>
                <th>Senior</th>
            </tr>
        </thead>
        <tbody>
            @foreach($residents as $r)
                <tr>
                    <td>{{ $r->full_name }}</td>
                    <td>{{ ucfirst($r->gender) }}</td>
                    <td>{{ $r->age }}</td>
                    <td>{{ ucfirst($r->civil_status) }}</td>
                    <td>{{ $r->purok ?? '-' }}</td>
                    <td>{{ $r->is_voter ? 'Yes' : 'No' }}</td>
                    <td>{{ $r->is_pwd ? 'Yes' : 'No' }}</td>
                    <td>{{ $r->is_senior ? 'Yes' : 'No' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <p class="total">Total Residents: {{ $totalCount }}</p>
    <div class="footer">Barangay Concepcion Information Management System — This is a system-generated report.</div>
</body>
</html>
