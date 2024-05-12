<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Heroes - Vendors</title>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.4/dist/jquery.min.js"></script>
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
                        <a class="nav-link" aria-current="page" href="{{ route('eventproducts') }}">Events & Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="#">Vendors</a>
                    </li>
                    <!-- <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="{{ route('products') }}">Products</a>
                    </li> -->
                </ul>
                <a href="{{ route('login') }}" class="btn btn-primary">Log In</a>
            </div>
        </div>
    </nav>
    <div class="container mt-2 py-2">
        <div class="d-flex justify-content-start flex-row gap-2">
            @foreach($shorts as $short)
            <div class="row">
                <form action="{{ route('vendors', ['s' => $short->vendor_short]) }}" method="POST">
                    @csrf
                    <input hidden type="text" name="short" class="form-control" id="short" value="{{ $short->vendor_short }}">
                    @if($s == $short->vendor_short)
                    <button class="btn btn-sm border btn-info">{{ $short->vendor_short }}</button>
                    @else
                    <button class="btn btn-sm border">{{ $short->vendor_short }}</button>
                    @endif
                </form>
            </div>
            @endforeach
        </div>
        <div class="py-2">
            <form action="{{ route('vendors') }}" method="POST">
                @csrf
                <button class="btn btn-sm border bg-warning">Clear Filter</button>
            </form>
        </div>
    </div>
    <div class="container mt-2">
        <div class="py-4">
            <table class="table table-condensed table-hover table-sm mb-5 table-responsive">
                <thead>
                    <tr class="table-info">
                        <th>No</th>
                        <th scope="col" colspan="3">Vendor</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody class="small">
                    @foreach($vendors as $index => $vendor)
                    <tr class="align-middle">
                        <td>{{ $index + $vendors->firstItem() }}</td>
                        <td colspan="3">{{ $vendor->organization }}</td>
                        <td><a class="btn btn-sm btn-primary mx-4" href="{{ route('vendor', ['id' => $vendor->id]) }}" target='_blank'>Show Product List</a></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="d-flex justify-content-center pagination">
        {{ $vendors->withQueryString()->links() }}
    </div>
    <div class="container">
        <div class="modal modal-lg fade" id="imagemodal" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog" data-dismiss="modal">
                <div class="modal-content">
                    <div class="modal-body">
                        <img src="" class="imagepreview" style="width: 100%;">
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
<script>
    $(function() {
        $('.showEnlargeImage').on('click', function() {
            $('.imagepreview').attr('src', $(this).attr('data-src'));
            $('#imagemodal').modal('show');
        });
    });
</script>

</html>