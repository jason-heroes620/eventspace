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
    <div class="container mx-auto">
        <div class="mt-2">
            <div class="py-4">
                <h4>{{ $vendor->organization }}</h4>
            </div>
        </div>
        <div class="container py-2">
            <table class="table table-condensed table-hover table-sm mb-5 table-responsive">
                <thead class="small">
                    <tr class="table-info">

                        <th>Image</th>
                        <th>Prouduct</th>
                        <th class="text-end">Price (RM)</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $index => $product)
                    <tr>
                        <td>
                            @if($product->product_image)
                            <img src="{{ asset('storage/img/' . $product->compressed_product_image) }}" data-src="{{ asset('storage/img/' . $product->product_image) }}" alt="" width="auto" height="50px" class="showEnlargeImage" loading="lazy">
                            @endif
                        </td>
                        <td>{{ $product->product_name }}</td>
                        <td class="text-end border px-2"><strong>{{ $product->product_price }}</strong></td>
                        <td class="">
                            <a class="btn btn-sm btn-primary mx-4" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $product->product_code }}" aria-expanded="false" aria-controls="{{ $product->product_code }}">Show QR</button>
                        </td>
                    </tr>

                    <tr class="collapse" id="{{$product->product_code}}">
                        <td colspan="2">{{ $product->product_description }}</td>
                        <td colspan='4'>
                            <table class="table">
                                <tr>
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
            {{ $products->withQueryString()->links() }}
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