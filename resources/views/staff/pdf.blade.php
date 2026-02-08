<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Staff Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Staff Report</h2>
        <p>Generated on: {{ date('d-m-Y H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Serial No</th>
                <th>Employee ID</th>
                <th>Full Name</th>
                <th>Gender</th>
                <th>Designation</th>
                <th>Contact No</th>
                <th>Email</th>
                <th>Date of Joining</th>
                <th>Qualification</th>
                <th>Caste</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($staff as $s)
                <tr>
                    <td>{{ $s->serial_no ?? '-' }}</td>
                    <td>{{ $s->employee_id ?? '-' }}</td>
                    <td>{{ $s->full_name }}</td>
                    <td>{{ $s->gender ?? '-' }}</td>
                    <td>{{ $s->designation ?? '-' }}</td>
                    <td>{{ $s->contact_no ?? '-' }}</td>
                    <td>{{ $s->email ?? '-' }}</td>
                    <td>{{ $s->date_of_joining ?? '-' }}</td>
                    <td>{{ $s->qualification ?? '-' }}</td>
                    <td>{{ $s->caste ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="text-align: center;">No staff found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
