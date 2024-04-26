<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bootstrap demo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
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
                        <a class="nav-link active" aria-current="page" href="#">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="{{ route('applications')}}">Applications</a>
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
    <div class="container py-2">
        <h3> Welcome, {{ Auth::user()->name }}</h3>
    </div>
    <div class="container mx-auto row justify-content-evenly py-4">
        @foreach($applications as $app)

        @if ($app->status == 'A')
        <div class="col-8 col-md-3 border bg-success">
            <div class="row justify-content-center px-4 py-2 align-items-center">
                <span class="col-10 text-left text-white py-2 bg-success">
                    <h6>APPROVED</h6>
                </span>
                <span class="col-2 text-center text-white py-2">
                    <h4>{{ $app->total }}</h4>
                </span>
            </div>
        </div>
        @elseif ($app->status == 'R')
        <div class="col-8 col-md-3 border bg-danger ">
            <div class="row justify-content-center px-4 py-2 align-items-center">
                <span class="col-10 text-left text-white py-2 bg-danger">
                    <h6>REJECTED</h6>
                </span>
                <span class="col-2 text-center text-white py-2">
                    <h4>{{ $app->total }}</h4>
                </span>
            </div>
        </div>
        @else
        <div class="col-8 col-md-3 border bg-info ">
            <div class="row justify-content-center px-4 py-2 align-items-center">
                <span class="col-10 text-left text-white py-2 bg-info">
                    <h6>NEW</h6>
                </span>
                <span class="col-2 text-center text-white py-2">
                    <h4>{{ $app->total }}</h4>
                </span>
            </div>
        </div>
        @endif
        @endforeach
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
</body>

</html>