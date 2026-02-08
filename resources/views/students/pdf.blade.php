<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Students Report</title>
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
        <h2>Students Report</h2>
        <p>Generated on: {{ date('d-m-Y H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>SL No</th>
                <th>Admission Date</th>
                <th>Admission No</th>
                <th>Class</th>
                <th>Student ID</th>
                <th>Student Name</th>
                <th>Father Name</th>
                <th>Mobile No</th>
                <th>Caste</th>
                <th>DOB</th>
                <th>Age</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($students as $student)
                <tr>
                    <td>{{ $student->sl_no ?? '-' }}</td>
                    <td>{{ $student->admission_date ?? '-' }}</td>
                    <td>{{ $student->admission_no ?? '-' }}</td>
                    <td>{{ $student->class ?? '-' }}</td>
                    <td>{{ $student->student_id ?? '-' }}</td>
                    <td>{{ $student->student_name }}</td>
                    <td>{{ $student->father_name ?? '-' }}</td>
                    <td>{{ $student->mobile_no ?? '-' }}</td>
                    <td>{{ $student->caste ?? '-' }}</td>
                    <td>{{ $student->dob ?? '-' }}</td>
                    <td>{{ $student->age ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" style="text-align: center;">No students found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
