<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Heroes - Event Products</title>
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
                        <a class="nav-link active" aria-current="page" href="#">Events & Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="{{ route('vendors')}}">Vendors</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="{{ route('products') }}">Products</a>
                    </li>
                </ul>
                <a href="{{ route('login') }}" class="btn btn-primary">Log In</a>
            </div>
        </div>
    </nav>

    <div class="container d-flex justify-content-start">
        <div class="py-3">
            <select name="events" id="events" class="form-select col-12 col-md-4">
                <option value="">Select An Event</option>
                @if($eventId)
                @php
                $all = ''
                @endphp
                @else
                @php
                $all = 'selected'
                @endphp
                @endif
                <option {{ $all }} value="{{ route('eventproducts') }}">All</option>
                @foreach($events as $event)
                @if($event->id == $eventId)
                @php
                $selected = 'selected'
                @endphp
                @else
                @php
                $selected = ''
                @endphp
                @endif
                <option {{ $selected }} value="{{ route('eventproducts', ['eventId' => $event->id]) }}">{{$event->event_name}}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="container mt-2 py-2">
        <div class="d-flex justify-content-start flex-row gap-2">
            @foreach($shorts as $short)
            <div class="row">
                <form action="{{ route('eventproducts', ['eventId' => $eventId]) }}" method="POST">
                    @csrf
                    <input hidden type="text" name="short" class="form-control" id="short" value="{{ $short->product_short }}">
                    @if($selectedShort == $short->product_short)
                    <button class="btn btn-sm border btn-info">{{ $short->product_short }}</button>
                    @else
                    <button class="btn btn-sm border">{{ $short->product_short }}</button>
                    @endif
                </form>
            </div>
            @endforeach
        </div>
        <div class="py-2">
            <form action="{{ route('eventproducts', ['eventId' => $eventId]) }}" method="POST">
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
                        <th>Image</th>
                        <th scope="col">Product</th>
                        <!-- <th scope="col">Description</th> -->
                        <th scope="col">Price</th>
                        <th scope="col">Vendor</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody class="small">
                    @foreach($products as $index => $product)
                    <tr class="align-middle">
                        <td>{{ $index + $products->firstItem() }}</td>
                        <td>
                            @if($product->product_image)
                            <img src="{{ asset('/img/' . $product->product_image) }}" alt="" width="100px" height="auto" class="showEnlargeImage">
                            @endif
                        </td>
                        <td>{{ $product->product_name }}</td>
                        <!-- <td>{{ $product->product_description }}</td> -->
                        <td>{{ $product->display_price }}</td>
                        <td>{{ $product->organization->organization }}</td>
                        <td class="border">
                            <a class="btn btn-sm btn-primary mx-4" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $product->product_code}}" aria-expanded="false" aria-controls="{{ $product->product_code }}">Show QR</button>
                        </td>
                    </tr>

                    <tr class="collapse" id="{{$product->product_code}}">
                        <td colspan='6'>
                            <table class="table">
                                <tr>
                                    <td>{{ $product->qr }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-center pagination">
            {{ $products->links() }}
        </div>
    </div>
    <div class="modal modal-lg fade" id="imagemodal" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog" data-dismiss="modal">
            <div class="modal-content">
                <div class="modal-body">
                    <img src="" class="imagepreview" style="width: 100%;">
                </div>
            </div>
        </div>
    </div>
</body>
<script>
    $('#events').on('change', function(e) {
        var link = $("option:selected", this).val();
        if (link) {
            location.href = link;
        }
    });

    $(function() {
        $('.showEnlargeImage').on('click', function() {
            $('.imagepreview').attr('src', $(this).attr('src'));
            $('#imagemodal').modal('show');
        });
    });
</script>

</html>