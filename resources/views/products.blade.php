<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Heroes - Products</title>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.4/dist/jquery.min.js"></script>
</head>

<body>
    <nav class="navbar navbar-expand-lg bg-body-tertiary">
        <div class="container">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNavDropdown">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="{{ route('eventproducts') }}">Events & Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="{{ route('vendors') }}">Vendors</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="#">Products</a>
                    </li>
                </ul>
                <a href="{{ route('login') }}" class="btn btn-primary">Log In</a>
            </div>
        </div>
    </nav>
    <!-- <div class="container pt-4">
        <div class="col-12 col-md-6 col-xl-3">
            <form action="/products" method="POST" role="search">
                <div class="input-group">
                    <input type="text" class="form-control" name="product_name" placeholder="Product"> <span class="input-group-btn">
                        <button type="submit" class="btn btn-default">
                            <span class="glyphicon glyphicon-search"></span>
                        </button>
                    </span>
                </div>
            </form>
        </div>
    </div> -->
    <div class="container mt-2">
        <div class="py-4">
            <table class="table table-condensed table-hover table-sm mb-5 table-responsive">
                <thead>
                    <tr class="table-info">
                        <th>No</th>
                        <th>Image</th>
                        <th scope="col">Product</th>
                        <!-- <th scope="col">Description</th> -->
                        <th scope="col">Price (RM)</th>
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
                            <img src="{{ asset('storage/img/' . $product->product_image) }}" alt="" width="100px" height="auto" class="showEnlargeImage">
                            @endif
                        </td>
                        <td>{{ $product->product_name }}</td>
                        <!-- <td>{{ $product->product_description }}</td> -->
                        <td class="text-end border px-2"><strong>{{ $product->product_price }}</strong></td>
                        <td>{{ $product->organization }}</td>
                        <td class="">
                            <a class="btn btn-sm btn-primary mx-4" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $product->product_code}}" aria-expanded="false" aria-controls="{{ $product->product_code }}">Show QR</button>
                                <!-- @if($event && $vendor)
                                <a class=" btn btn-sm btn-warning" href="{{ route('product-detail', ['id' => $product->id, 'page' => request()->query('page'), 'event' => $event, 'vendor' => $vendor]) }}">view</a>
                                @elseif($event && !$vendor)
                                <a class="btn btn-sm btn-warning" href="{{ route('product-detail', ['id' => $product->id, 'page' => request()->query('page'), 'event' => $event]) }}">view</a>
                                @elseif(!$event && $vendor)
                                <a class="btn btn-sm btn-warning" href="{{ route('product-detail', ['id' => $product->id, 'page' => request()->query('page'), 'vendor' => $vendor]) }}">view</a>
                                @else
                                <a class="btn btn-sm btn-warning" href="{{ route('product-detail', ['id' => $product->id, 'page' => request()->query('page')]) }}">view</a>
                                @endif -->
                        </td>
                    </tr>

                    <tr class="collapse" id="{{$product->product_code}}">
                        <td colspan='6'>
                            <table class="table">
                                <tr>
                                    <td class="" colspan="2">{{ $product->product_description }}</td>
                                    <td class="text-end">{{ $product->qr }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-center pagination">
            {{ $products->appends($_GET)->links() }}
        </div>
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
            $('.imagepreview').attr('src', $(this).attr('src'));
            $('#imagemodal').modal('show');
        });
    });
</script>

</html>