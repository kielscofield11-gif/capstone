<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Barangay Clearance</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #333;
            padding: 40px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px double #1e3a5f;
            padding-bottom: 15px;
        }
        .header h1 {
            font-size: 20px;
            color: #1e3a5f;
            margin: 0 0 4px 0;
            text-transform: uppercase;
        }
        .header h2 {
            font-size: 16px;
            color: #1e3a5f;
            margin: 0 0 4px 0;
        }
        .header p {
            font-size: 10px;
            color: #666;
            margin: 0;
        }
        .title {
            text-align: center;
            margin: 25px 0;
        }
        .title h3 {
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 2px;
            border: 2px solid #1e3a5f;
            display: inline-block;
            padding: 6px 30px;
            margin: 0;
        }
        .body-text {
            line-height: 2;
            margin: 20px 0;
            text-align: justify;
        }
        .info-table {
            width: 100%;
            margin: 15px 0;
        }
        .info-table td {
            padding: 4px 8px;
            vertical-align: top;
        }
        .info-table .label {
            width: 150px;
            font-weight: bold;
        }
        .signature-area {
            margin-top: 50px;
            text-align: right;
        }
        .signature-area .name {
            font-weight: bold;
            margin-top: 50px;
        }
        .signature-area .title-line {
            font-size: 10px;
            color: #666;
        }
        .footer {
            text-align: center;
            font-size: 8px;
            color: #999;
            margin-top: 30px;
            border-top: 1px solid #e5e7eb;
            padding-top: 8px;
        }
        .clearance-number {
            text-align: right;
            font-size: 10px;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="clearance-number">Clearance No. {{ str_pad($resident->id, 6, '0', STR_PAD_LEFT) }}</div>

    <div class="header">
        <img src="{{ public_path('images/logo.png') }}" alt="Barangay Logo" style="width: 60px; height: 60px; margin-bottom: 8px; object-fit: contain;">
        <h1>Republic of the Philippines</h1>
        <h2>Barangay {{ config('app.barangay_name', $resident->purok ?? 'N/A') }}</h2>
        <p>{{ config('app.name') }}</p>
    </div>

    <div class="title">
        <h3>Barangay Clearance</h3>
    </div>

    <div class="body-text">
        <p>TO WHOM IT MAY CONCERN:</p>
        <p>This is to certify that <strong>{{ $resident->full_name }}</strong>, of legal age, {{ $resident->civil_status }}, Filipino, and a resident of Barangay {{ config('app.barangay_name', $resident->purok ?? 'N/A') }}, has been known to be a person of good moral character and has not been involved in any criminal activities that would impair the safety and welfare of the community.</p>
        <p>This clearance is issued upon the request of the above-named person for <strong>general purposes</strong> and is valid for a period of six (6) months from the date of issue.</p>
    </div>

    <table class="info-table">
        <tr>
            <td class="label">Name:</td>
            <td>{{ $resident->full_name }}</td>
        </tr>
        <tr>
            <td class="label">Birth Date:</td>
            <td>{{ $resident->birth_date->format('F d, Y') }}</td>
        </tr>
        <tr>
            <td class="label">Place of Birth:</td>
            <td>{{ $resident->birthplace ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Gender:</td>
            <td>{{ ucfirst($resident->gender) }}</td>
        </tr>
        <tr>
            <td class="label">Civil Status:</td>
            <td>{{ ucfirst($resident->civil_status) }}</td>
        </tr>
        <tr>
            <td class="label">Address:</td>
            <td>{{ $resident->street_address ?? $resident->purok ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Date Issued:</td>
            <td>{{ now()->format('F d, Y') }}</td>
        </tr>
    </table>

    <div class="signature-area">
        <p class="title-line">Certified by:</p>
        <p class="name">{{ auth()->user()?->name ?? 'Barangay Official' }}</p>
        <p class="title-line">{{ ucfirst(auth()->user()?->role ?? 'Official') }}</p>
    </div>

    <div class="footer">
        This is a system-generated clearance. Valid with official receipt and dry seal.<br>
        Barangay Concepcion Information Management System
    </div>
</body>
</html>
