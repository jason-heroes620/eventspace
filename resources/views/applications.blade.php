<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bootstrap demo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <nav class="navbar navbar-expand-lg bg-body-tertiary">
        <div class="container">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="{{ route('home') }}">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="#">Applications</a>
                    </li>
                </ul>
                <form action="{{ route('logout') }}" method="POST" class="d-flex" role="search">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger" type="submit">Logout</button>
                </form>
            </div>
        </div>
    </nav>
    <div class="container mt-5">
        <table class="table table-bordered mb-5">
            <thead>
                <tr class="table-success">
                    <th scope="col">Shop/Brand Name/Educational Institute Name</th>
                    <th scope="col">Contact Person</th>
                    <th scope="col">Contact No.</th>
                    <th scope="col">Email</th>
                    <th scope="col">Date of Submission</th>
                    <th scope="col">Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($applications as $data)
                <tr>
                    <th scope="row">{{ $data->organization }}</th>
                    <td>{{ $data->contact_person }}</td>
                    <td>{{ $data->contact_no }}</td>
                    <td>{{ $data->email }}</td>
                    <td>{{ date('d/m/Y H:i A', strtotime($data->created)) }}</td>
                    @if($data->status == 'N')
                    <td class="text-center bg-info text-black">{{ $data->status }}</td>
                    @elseif ($data->status == 'A')
                    <td class="text-center bg-success text-white">{{ $data->status }}</td>
                    @else
                    <td class="text-center bg-danger text-white">{{ $data->status }}</td>
                    @endif
                    <td><a href="{{ route('application-detail', ['id' => $data->id, 'page' => url()->full()]) }}">View</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="container row col-6 col-md-3 col-lg-2">
            <div class="bg-info">
                <span class="text-black">N = New,</span>
            </div>
            <div class="bg-success">
                <span class="text-white">A = Approved</span>
            </div>
            <div class="bg-danger">
                <span class="text-white"> R = Rejected</span>
            </div>
        </div>
        <div class="d-flex justify-content-center pagination">
            {{ $applications->links() }}
        </div>
    </div>
</body>

</html>