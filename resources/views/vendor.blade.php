<html>

<body>

    {{ $vendor }}

    @foreach($products as $prod)
    {{ $prod->product_name }}
    {{ $prod->product_description }}
    {{ $prod->display_price }}
    @endforeach
</body>

</html>