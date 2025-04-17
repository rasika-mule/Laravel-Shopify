<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
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

            <h1 class="mb-4">Dashboard</h1>

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if($uploads->isEmpty())
                <div class="alert alert-info">
                    No uploads found. <a href="{{ route('upload-form') }}">Upload a CSV</a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Original File Name</th>
                            <th>Processed / Total</th>
                            <th>Status</th>
                            <th>Logs</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($uploads as $u)
                            <tr>
                                <td>{{ $u->id }}</td>
                                <td>{{ $u->original_name }}</td>
                                <td>{{ $u->processed_rows }} / {{ $u->total_rows }}</td>
                                <td>{{ $u->status }}</td>
                                <td>
                                    <a
                                        class="btn btn-sm btn-primary"
                                        href="{{ route('dashboard.logs', $u->id) }}"
                                    >
                                        View Logs
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <div class="mt-3 d-grid d-sm-flex justify-content-sm-start gap-2">
                <a class="btn btn-success" href="{{ route('upload-form') }}">Upload Another CSV</a>
            </div>

        </div>
    </div>
</div>

</body>
</html>
