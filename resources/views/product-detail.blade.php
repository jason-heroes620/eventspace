<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Heroes - Product Detail - {{ $product->product_name }}</title>
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
                        <a class="nav-link active" aria-current="page" href="{{ route('products') }}">Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="{{ route('vendors') }}">Vendors</a>
                    </li>
                </ul>
                <a href="{{ route('login') }}" class="btn btn-primary">Log In</a>
            </div>
        </div>
    </nav>
    <div class="container mx-auto">
        <div class="py-4">
            @if($event && $vendor)
            <div>
                <a href=" {{ route('products-event-vendor', ['page' => $page, 'event' => $event, 'vendor' => $vendor]) }}" class="btn btn-primary">
                    < Back</a>
            </div>
            @elseif($event && !$vendor)
            <div>
                <a href=" {{ route('products-event', ['page' => $page, 'event' => $event]) }}" class="btn btn-primary">
                    < Back</a>
            </div>
            elseif(!$event && $vendor)
            <div>
                <a href=" {{ route('products-vendor', ['page' => $page, 'vendor' => $vendor]) }}" class="btn btn-primary">
                    < Back</a>
            </div>
            @else
            <div>
                <a href=" {{ url()->previous() }}" class="btn btn-primary">
                    < Back</a>
            </div>
            @endif
            {{ $vendor_info->organization }}
            {{ $product->product_name }}
            {{ $product->product_description }}
            {{ $product->display_price }}
        </div>
        <div>
            {{ $qr }}
        </div>
    </div>
</body>

</html>