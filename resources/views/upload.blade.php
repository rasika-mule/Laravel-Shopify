<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload CSV</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <span class="navbar-brand">Laravel CSV-Shopify</span>
    </div>
</nav>

<div class="container my-4">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-8 col-lg-6">

            <h1 class="mb-4 text-center">Upload CSV</h1>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <div class="card shadow-sm">
                <div class="card-body">
                    <form
                        method="POST"
                        action="{{ route('upload-csv') }}"
                        enctype="multipart/form-data"
                    >
                        @csrf
                        <div class="mb-3">
                            <label for="csv_file" class="form-label">CSV File:</label>
                            <input
                                type="file"
                                name="csv_file"
                                id="csv_file"
                                class="form-control"
                                accept=".csv"
                                required
                            >
                        </div>
                        <div class="d-grid gap-2 d-sm-flex justify-content-sm-end flex-column flex-sm-row">
                            <button type="submit" class="btn btn-primary">Upload & Process</button>
                            <a href="{{ route('dashboard') }}" class="btn btn-secondary">Go to Dashboard</a>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>
