<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Endorsement - {{ $endorsement->endorsement_number }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #1a365d;
        }
        .header h2 {
            margin: 5px 0;
            font-size: 16px;
            color: #666;
        }
        .endorsement-type {
            display: inline-block;
            padding: 5px 15px;
            background-color: {{ $type === 'ADDITION' ? '#48bb78' : '#f56565' }};
            color: white;
            border-radius: 4px;
            font-weight: bold;
            margin-top: 10px;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #1a365d;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }
        .info-grid {
            display: table;
            width: 100%;
        }
        .info-row {
            display: table-row;
        }
        .info-label {
            display: table-cell;
            width: 40%;
            padding: 5px 0;
            font-weight: bold;
        }
        .info-value {
            display: table-cell;
            padding: 5px 0;
        }
        table.students-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table.students-table th,
        table.students-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        table.students-table th {
            background-color: #f7fafc;
            font-weight: bold;
        }
        table.students-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .total-row {
            font-weight: bold;
            background-color: #edf2f7 !important;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 10px;
            color: #666;
        }
        .signature-section {
            margin-top: 60px;
            display: table;
            width: 100%;
        }
        .signature-box {
            display: table-cell;
            width: 50%;
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #333;
            width: 200px;
            margin: 0 auto;
            padding-top: 5px;
        }
        .amount {
            color: {{ $type === 'ADDITION' ? '#c53030' : '#2f855a' }};
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>POLICY ENDORSEMENT</h1>
        <h2>{{ $type === 'ADDITION' ? 'Student Addition' : 'Student Removal' }} Certificate</h2>
        <div class="endorsement-type">{{ $type }}</div>
    </div>

    <div class="section">
        <div class="section-title">Endorsement Details</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Endorsement Number:</div>
                <div class="info-value">{{ $endorsement->endorsement_number }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Effective Date:</div>
                <div class="info-value">{{ $endorsement->effective_date->format('d M Y') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Generated On:</div>
                <div class="info-value">{{ $generated_at->format('d M Y H:i:s') }}</div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Policy Information</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Policy Number:</div>
                <div class="info-value">{{ $policy->policy_number }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Insurance Type:</div>
                <div class="info-value">{{ $policy->insurance_type }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Provider:</div>
                <div class="info-value">{{ $policy->provider }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Policy Period:</div>
                <div class="info-value">{{ $policy->start_date->format('d M Y') }} - {{ $policy->end_date->format('d M Y') }}</div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">{{ $type === 'ADDITION' ? 'Students Added' : 'Students Removed' }}</div>
        <table class="students-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Student ID</th>
                    <th>Name</th>
                    <th>{{ $type === 'ADDITION' ? 'Premium' : 'Refund' }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($students as $index => $student)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $student['student_id'] }}</td>
                    <td>{{ $student['name'] }}</td>
                    <td class="amount">₹{{ number_format($type === 'ADDITION' ? $student['premium'] : $student['refund'], 2) }}</td>
                </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="3" style="text-align: right;">Total {{ $type === 'ADDITION' ? 'Debited' : 'Credited' }}:</td>
                    <td class="amount">₹{{ number_format($total_amount, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Transaction Summary</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Number of Students:</div>
                <div class="info-value">{{ count($students) }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Transaction Type:</div>
                <div class="info-value">{{ $type === 'ADDITION' ? 'DEBIT' : 'CREDIT' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Total Amount:</div>
                <div class="info-value amount">₹{{ number_format($total_amount, 2) }}</div>
            </div>
        </div>
    </div>

    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line">Authorized Signatory</div>
        </div>
        <div class="signature-box">
            <div class="signature-line">Company Seal</div>
        </div>
    </div>

    <div class="footer">
        <p>This is a computer-generated document and does not require a physical signature.</p>
        <p>Generated by PolicyZen Insurance Management System on {{ $generated_at->format('d M Y H:i:s') }}</p>
    </div>
</body>
</html>
