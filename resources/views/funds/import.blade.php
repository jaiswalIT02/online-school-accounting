@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Import Funds</h4>
        <div>
            <a class="btn btn-secondary" href="{{ route('funds.index') }}">Back to Funds</a>
        </div>
    </div>

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Upload Excel/CSV File</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('funds.processImport') }}" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label for="file" class="form-label">Select File (.xlsx, .xls, or .csv)</label>
                    <input type="file" class="form-control @error('file') is-invalid @enderror" 
                           id="file" name="file" accept=".xlsx,.xls,.csv" required>
                    @error('file')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="alert alert-info">
                    <h6 class="alert-heading">File Format Requirements:</h6>
                    <ul class="mb-0">
                        <li><strong>All fields are optional:</strong> You can import data with any combination of fields</li>
                        <li><strong>All columns will be automatically mapped:</strong>
                            <ul>
                                <li>Columns matching database field names will be imported</li>
                                <li>Columns with variations (spaces, underscores, case) will be matched automatically</li>
                                <li>Unmatched columns will be ignored (no error)</li>
                                <li>Examples: <code>date</code>/<code>fund_date</code>/<code>fund date</code>, <code>component name</code>/<code>component_name</code>, <code>amount</code>, <code>remark</code>/<code>remarks</code>, etc.</li>
                            </ul>
                        </li>
                        <li><strong>Date Format:</strong> Dates can be in <code>dd/mm/yyyy</code>, <code>dd-mm-yyyy</code>, <code>yyyy-mm-dd</code>, or Excel date format - will be automatically converted to <code>dd/mm/yyyy</code></li>
                        <li><strong>First row should contain column headers</strong></li>
                        <li><strong>Note:</strong> You can include all columns from your Excel file - only matching columns will be imported, others will be safely ignored</li>
                    </ul>
                </div>

                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0">Available Field Names for Excel Import</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-primary">Date Fields:</h6>
                                <ul class="list-unstyled">
                                    <li><code>fund_date</code> (or: date, fund date, funddate, transaction date)</li>
                                </ul>
                                
                                <h6 class="text-primary mt-3">Component Information:</h6>
                                <ul class="list-unstyled">
                                    <li><code>component_name</code> (or: component name, componentname, fund for, article name, name)</li>
                                    <li><code>component_code</code> (or: component code, componentcode, code, acode, article code)</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary">Financial Information:</h6>
                                <ul class="list-unstyled">
                                    <li><code>amount</code> (or: total, value, fund amount)</li>
                                </ul>
                                
                                <h6 class="text-primary mt-3">Remarks:</h6>
                                <ul class="list-unstyled">
                                    <li><code>remark</code> (or: remarks, note, notes, description, comment, comments)</li>
                                </ul>
                            </div>
                        </div>
                        <div class="alert alert-warning mt-3 mb-0">
                            <small><strong>Note:</strong> Field names are case-insensitive. You can use spaces, underscores, or no separators. The system will automatically match variations. For example: "Fund Date", "fund_date", "funddate" all work.</small>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Upload and Import
                    </button>
                    <a href="{{ route('funds.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    @if (session('import_result'))
        @php
            $result = session('import_result');
        @endphp
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0">Import Report</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h3 class="mb-0">{{ $result['total_rows'] }}</h3>
                                <small>Total Rows</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h3 class="mb-0">{{ $result['inserted'] }}</h3>
                                <small>Inserted</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <h3 class="mb-0">{{ $result['skipped'] }}</h3>
                                <small>Skipped</small>
                            </div>
                        </div>
                    </div>
                </div>

                @if (!empty($result['skipped_rows']))
                    <h6>Skipped Rows:</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>Row #</th>
                                    <th>Reason</th>
                                    <th>Data</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($result['skipped_rows'] as $skipped)
                                    <tr>
                                        <td>{{ $skipped['row'] }}</td>
                                        <td class="text-danger">{{ $skipped['reason'] }}</td>
                                        <td>
                                            <small>
                                                @foreach ($skipped['data'] as $key => $value)
                                                    <strong>{{ $key }}:</strong> {{ $value }}<br>
                                                @endforeach
                                            </small>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection
