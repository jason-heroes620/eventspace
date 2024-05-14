<html>

<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container">
        <div class="row pt-4 py-2">
            <form action="{{ route('salesreport') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-12 col-md-6 col-lg-3">
                        <select name="salesDate" id="salesDate" class="form-select">
                            <option value="">Select a date</option>
                            <option value="2024-05-14">14/5</option>
                            <option value="2024-05-15">15/5</option>
                            <option value="2024-05-16">16/5</option>
                            <option value="2024-05-17">17/5</option>
                            <option value="2024-05-18">18/5</option>
                            <option value="2024-05-19">19/5</option>
                            <option value="2024-05-20">20/5</option>
                            <option value="2024-05-21">21/5</option>
                            <option value="2024-05-22">22/5</option>
                        </select>
                    </div>
                    <div><input type="submit" class="btn btn-info"></div>
                </div>
            </form>
        </div>
        <div>
            @if($salesDate)
            <h5>{{ $salesDate }}</h5>
            @endif
        </div>
        <div>
            <table class="table table-condensed">
                <thead>
                    <th>Product</th>
                    <th>Vendor</th>
                    <th>Quantity</th>
                    <th>Total</th>

                </thead>
                <tbody>
                    @foreach($sales as $sale)
                    <tr>
                        <td>{{ $sale->product_name }}</td>
                        <td>{{ $sale->organization }}</td>
                        <td>{{ $sale->quantity }}</td>
                        <td>{{ $sale->sales }}</td>

                    </tr>

                    @endforeach
                    <tr>
                        <td colspan="2">Total</td>
                        <td>{{ $totalQty }}</td>
                        <td>{{ $totalSales }}</td>
                    </tr>
                </tbody>
            </table>


        </div>
    </div>
</body>

</html>