<html>

<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container">
        <div class="row pt-4 py-2">
            <form action="{{ route('salesreport') }}" method="POST">
                @csrf
                <div class="d-flex row align-items-start">
                    <div class="col col-12 col-md-6 col-lg-3">
                        <select name="salesDate" id="salesDate" class="form-select">
                            @if($salesDate)
                            <option value="{{ $salesDate }}">{{ date('d M', strtotime($salesDate)) }}</option>
                            @else<option value="">Select a date</option>
                            @endif
                            <option value="2024-05-14">14 May</option>
                            <option value="2024-05-15">15 May</option>
                            <option value="2024-05-16">16 May</option>
                            <option value="2024-05-17">17 May</option>
                            <option value="2024-05-18">18 May</option>
                            <option value="2024-05-19">19 May</option>
                            <option value="2024-05-20">20 May</option>
                            <option value="2024-05-21">21 May</option>
                            <option value="2024-05-22">22 May</option>
                        </select>
                    </div>
                    <div class="col"><input type="submit" class="btn btn-info"></div>
                </div>
            </form>
        </div>
        @if($salesDate)
        <div>
            <h5>{{ date('d M, Y', strtotime($salesDate)) }}</h5>
        </div>
        @endif
        <div>
            <table class="table table-condensed">
                <thead class="table-info">
                    <th>Product</th>
                    <th>Vendor</th>
                    <th class="text-end">Quantity</th>
                    <th class="text-end">Total</th>
                </thead>
                <tbody>
                    @foreach($sales as $sale)
                    <tr>
                        <td>{{ $sale->product_name }}</td>
                        <td>{{ $sale->organization }}</td>
                        <td class="text-end">{{ $sale->quantity }}</td>
                        <td class="text-end">{{ $sale->sales }}</td>
                    </tr>
                    @endforeach
                    <tr>
                        <td colspan="2" class="text-end">Total</td>
                        <td class="text-end">{{ $totalQty }}</td>
                        <td class="text-end">{{ $totalSales }}</td>
                    </tr>
                </tbody>
            </table>


        </div>
    </div>
</body>

</html>