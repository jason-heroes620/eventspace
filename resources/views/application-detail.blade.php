<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bootstrap demo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/izitoast@1.4.0/dist/css/iziToast.min.css" rel="stylesheet">
    <link href="{{ asset('css/loading.css') }}" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/izitoast/dist/js/iziToast.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
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
                        <a class="nav-link active" aria-current="page" href="{{ route('applications') }}">Applications</a>
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
    <div class="container mt-4">
        <div>
            <a href="{{ route('applications', ['page' => $page]) }}" class="btn btn-primary px-4">
                < Back</a>
        </div>

        <div class="p-4">
            <section id="loading">
                <div id="loading-content"></div>
            </section>
            <div class="container row">
                <div class="row py-2">
                    <span class="col-12 col-md-4"><strong>Shop/Brand Name/Educational Institute Name</strong></span>
                    <span class="col-12 col-md-8 py-2 border">{{ $application->organization }}</span>
                </div>
                <div class="row py-2">
                    <span class="col-12 col-md-4"><strong>Contact Person</strong></span>
                    <span class="col-12 col-md-8 py-2 border">{{ $application->contact_person }}</span>
                </div>
                <div class="row py-2">
                    <span class="col-12 col-md-4"><strong>Contact No.</strong></span>
                    <span class="col-12 col-md-8 py-2 border">{{ $application->contact_no }}</span>
                </div>
                <div class="row py-2">
                    <span class="col-12 col-md-4"><strong>Email</strong></span>
                    <span class="col-12 col-md-8 py-2 border">{{ $application->email }}</span>
                </div>
                <div class="row py-2">
                    <span class="col-12 col-md-4"><strong>Company Registration No. / IC No.</strong></span>
                    <span class="col-12 col-md-8 py-2 border">{{ $application->registration }}</span>
                </div>
                <div class="row py-2">
                    <span class="col-12 col-md-4"><strong>No. of Participants</strong></span>
                    <span class="col-12 col-md-8 py-2 border">{{ $application->participants }}</span>
                </div>
                <div class="row py-2">
                    <span class="col-12"><strong>Sustainability Category</strong></span>
                    <ul>
                        @foreach($categories as $data)
                        <li class="col-12 p-2">{{ $data->category }}</li>
                        @endforeach
                    </ul>
                </div>
                <div class="row py-2">
                    <span class="col-12 col-md-4"><strong>Product Description</strong></span>
                    <textarea class="col-12 col-md-8 border" rows="4" disabled>{{ $application->description }}</textarea>
                </div>
                <div class="row py-2">
                    <span class="col-12 col-md-4"><strong>Social Media Accounts</strong></span>
                    <span class="col-12 col-md-8 py-2 border">{{ $application->social_media_accounts }}</span>
                </div>
                <div class="row py-2">
                    <span class="col-12 col-md-4"><strong>Requirements</strong></span>
                    <textarea class="col-12 col-md-8 border" rows="4" disabled>{{ $application->requirements }}</textarea>
                </div>
                <div class="row py-2">
                    <span class="col-12 col-md-4"><strong>Plug Points</strong></span>
                    <span class="col-12 col-md-8 py-2 border">{{ $application->plug }}</span>
                </div>
                <div class="row py-2">
                    <span class="col-12 col-md-4"><strong>Booth</strong></span>
                    <span class="col-12 col-md-8 py-2 border">{{ $booth->booth_type }} ( RM{{ $booth_price }} )</span>
                </div>
                <div class="row py-2">
                    <span class="col-12 col-md-4"><strong>No. of Booth</strong></span>
                    <span class="col-12 col-md-8 py-2 border">{{ $application->booth_qty }}</span>
                </div>
                <div class="row py-2">
                    <span class="col-12 col-md-4"><strong>No. of Days</strong></span>
                    <span class="col-12 col-md-8 py-2 border">{{ $application->no_of_days }}</span>
                </div>
                <div class="row py-2">
                    <span class="col-12 col-md-4"><strong>Total (RM)</strong></span>
                    <span class="col-12 col-md-8 py-2 border">{{ number_format((float)$total, '2','.',',') }}</span>
                </div>
                <div class="row py-2">
                    <span class="col-12 col-md-4"><strong>Status</strong></span>
                    @if($application->status == 'N')
                    <span class="col-12 col-md-8 py-2 border bg-info text-white">NEW</span>
                    @elseif($application->status == 'A')
                    <span class="col-12 col-md-8 py-2 border bg-success text-white">APPROVED</span>
                    @else
                    <span class="col-12 col-md-8 py-2 border bg-danger text-white">REJECTED</span>
                    @endif
                </div>
            </div>
        </div>

        @if ($payment)
        <hr>
        <h5>Payment Information</h5>
        <div class="container row">

            <div class="row py-2">
                <span class="col-12 col-md-4"><strong>Payment ID</strong></span>
                <span class="col-12 col-md-8 py-2 border">{{ $payment->id }}</span>
            </div>
            @if($payment_detail)
            <div class="row py-2">
                <span class="col-12 col-md-4"><strong>Payment Total (RM)</strong></span>
                <span class="col-12 col-md-8 py-2 border">{{ number_format((float)$payment->payment_total, '2','.',',') }}</span>
            </div>
            <div class="row py-2">
                <span class="col-12 col-md-4"><strong>Payment Date</strong></span>
                <span class="col-12 col-md-8 py-2 border">{{ date('d/m/Y H:i A', strtotime($payment_detail->created)) }}</span>
            </div>
            <div class="row py-2">
                <span class="col-12 col-md-4"><strong>Payment Method</strong></span>
                <span class="col-12 col-md-8 py-2 border">{{ $payment_detail->payment_method }}</span>
            </div>
            @else
            <div class="row py-2">
                <span class="col-12 col-md-4"><strong>Total (RM)</strong></span>
                <span class="col-12 col-md-8 py-2 border">{{ number_format((float)$payment->payment_total, '2','.',',') }}</span>
            </div>
            @endif
        </div>
        <hr>
        @endif
        <div class="container bg-light py-2">
            <div class="col-12 btn-group justify-right p-2 justify-content-end gap-4">
                <div>
                    <button id="reject" class="btn btn-danger" type="submit">Reject</button>
                    <button id="approve" class="btn btn-success" type="submit">Approve</button>
                </div>
            </div>
        </div>
    </div>
</body>

<script>
    iziToast.settings({
        timeout: 3000, // default timeout
        progressBar: true,
        progressBarColor: '',
        progressBarEasing: 'linear',
        overlay: false,
        overlayClose: false,
        overlayColor: 'rgba(0, 0, 0, 0.6)',
        transitionIn: 'fadeInUp',
        transitionOut: 'fadeOut',
        transitionInMobile: 'fadeInUp',
        transitionOutMobile: 'fadeOutDown',
        position: 'bottomRight',
    });

    function showLoading() {
        document.querySelector('#loading').classList.add('loading');
        document.querySelector('#loading-content').classList.add('loading-content');
    }

    function hideLoading() {
        document.querySelector('#loading').classList.remove('loading');
        document.querySelector('#loading-content').classList.remove('loading-content');
    }

    $("#reject").click(function(e) {
        data = {
            "status": "reject"
        }
        e.preventDefault();
        if (confirm("Confirm to 'REJECT' application?")) {
            showLoading();
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{ route('updateStatus', [$application->id])}}",
                method: "POST",
                data: JSON.stringify(data),
                contentType: 'application/json',
                processData: false,
                success: function(response) {
                    if (!response.success) {
                        var errorMsg = '';
                        $.each(response.error, function(field, errors) {
                            $.each(errors, function(index, error) {
                                errorMsg += error + '<br>';
                            });
                        });
                        iziToast.error({
                            message: errorMsg,
                            position: 'bottomRight'
                        });
                    } else {
                        iziToast.success({
                            id: 'question',
                            zindex: 999,
                            message: response.data.message,
                            position: 'bottomRight'

                        });
                    }
                    hideLoading();
                    // setTimeout(function() {
                    //     location.reload()
                    // }, 2000)
                },
                error: function(xhr, status, error) {}
            });
        }
    })

    $("#approve").click(function(e) {
        data = {
            "status": "approve"
        }
        e.preventDefault();
        if (confirm("Confirm to 'APPROVE' application?")) {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{ route('updateStatus', [$application->id])}}",
                method: "POST",
                data: JSON.stringify(data),
                contentType: 'application/json',
                processData: false,
                success: function(response) {
                    if (!response.success) {
                        var errorMsg = '';
                        $.each(response.error, function(field, errors) {
                            $.each(errors, function(index, error) {
                                errorMsg += error + '<br>';
                            });
                        });
                        iziToast.error({
                            message: errorMsg,
                            position: 'bottomRight'
                        });
                    } else {
                        iziToast.success({
                            id: 'question',
                            zindex: 999,
                            message: response.data.message,
                            position: 'bottomRight'

                        });
                        setTimeout(function() {
                            location.reload();
                        }, 2000)

                    }
                },
                error: function(xhr, status, error) {}
            });
        }
    })
</script>

</html>