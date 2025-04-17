<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Import Logs</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ route('upload-form') }}">Laravel CSV-Shopify</a>
    </div>
</nav>

<div class="container my-4">
    <div class="row">
        <div class="col-12">

            <h1 class="mb-4">Logs for Upload #{{ $upload->id }}</h1>

            @if($logs->isEmpty())
                <div class="alert alert-info">
                    No logs found for this upload.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-dark">
                        <tr>
                            <th>Log ID</th>
                            <th>Product ID</th>
                            <th>Handle</th>
                            <th>Operation</th>
                            <th>Status</th>
                            <th>Message</th>
                            <th>Created At</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($logs as $log)
                            <tr>
                                <td>{{ $log->id }}</td>
                                <td>{{ $log->product ? $log->product->id : 'N/A' }}</td>
                                <td>{{ $log->product->handle }}</td>
                                <td>{{ $log->operation ?? '-' }}</td>
                                <td>
                                    @switch($log->status)
                                        @case('pending')
                                            <span class="badge bg-secondary">Pending</span>
                                            @break
                                        @case('processing')
                                            <span class="badge bg-info text-dark">Processing</span>
                                            @break
                                        @case('successful')
                                            <span class="badge bg-success">Successful</span>
                                            @break
                                        @case('failed')
                                            <span class="badge bg-danger">Failed</span>
                                            @break
                                    @endswitch
                                </td>
                                <td>{{ $log->message }}</td>
                                <td>{{ $log->created_at }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <div class="mt-3 d-grid d-sm-flex justify-content-sm-start gap-2">
                <a class="btn btn-primary" href="{{ route('dashboard') }}">Back to Dashboard</a>
            </div>

        </div>
    </div>
</div>

</body>
</html>
