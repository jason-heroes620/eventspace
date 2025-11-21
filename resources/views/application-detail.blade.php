@extends('layout')
@section('content')
<div class="container mt-4">
    @if($eventId)
    <div>
        <a href="{{ route('event-applications', ['page' => $page, 'eventId' => $eventId]) }}" class="btn btn-primary px-4">
            < Back</a>
    </div>
    @else
    <div>
        <a href="{{ route('applications', ['page' => $page]) }}" class="btn btn-primary px-4">
            < Back</a>
    </div>
    @endif

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
                <span class="col-12 col-md-8 py-2 border">{{ $booth->booth_type }} ( {{ $booth_price }} )</span>
            </div>
            <div class="row py-2">
                <span class="col-12 col-md-4"><strong>No. of Booth</strong></span>
                <span class="col-12 col-md-8 py-2 border">{{ $application->booth_qty }}</span>
            </div>
            <div class="row py-2">
                <span class="col-12 col-md-4"><strong>No. of Days</strong></span>
                <span class="col-12 col-md-8 py-2 border">{{ $application->no_of_days }}</span>
            </div>

            @if($application->discount)
            <div class="row py-2">
                <span class="col-12 col-md-4 text-red-500"><strong>Discount (RM)</strong></span>
                <span class="col-12 col-md-8 py-2 border">{{ number_format((float)$application->discount_value, '2','.',',') }}</span>
            </div>
            @endif
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
        <div class="row py-2">
            <span class="col-12 col-md-4"><strong>Reference No.</strong></span>
            <span class="col-12 col-md-8 py-2 border">{{ $payment->reference_no }}</span>
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
        @if($payment->path)
        <div class="row py-2">
            <span class="col-12 col-md-4"><strong>Payment Reference</strong></span>
            <a href={{ $payment->path }} target="_blank"  class="col-12 col-md-8 py-2">View File</a>
        </div>
        @endif

         <div class="row py-2 flex items-center">
            <span class="col-12 col-md-4"><strong>Bank</strong></span>
            <span class="col-12 col-md-8 py-2 border">{{ $payment->bank }}</span>
        </div>
        <div class="row py-2">
            <span class="col-12 col-md-4"><strong>Account Name</strong></span>
            <span class="col-12 col-md-8 py-2 border">{{ $payment->account_name }}</span>
        </div>
        <div class="row py-2">
            <span class="col-12 col-md-4"><strong>IC / Company SSM No.</strong></span>
            <span class="col-12 col-md-8 py-2 border">{{ $payment->bank_id }}</span>
        </div>
         <div class="row py-2">
            <span class="col-12 col-md-4"><strong>Account No.</strong></span>
            <span class="col-12 col-md-8 py-2 border">{{ $payment->account_no }}</span>
        </div>
    </div>
    <hr>
    @endif
    <div class="container bg-light py-2">
        <div class="col-12 btn-group justify-right p-2 justify-content-end gap-6">
            <div class="">
                {{-- @if($application->status === 'A')
                <button id="reject" class="btn btn-danger" type="submit">Reject</button>
                @elseif($application->status === 'R')
                <button id="approve" class="btn btn-success" type="submit">Approve</button>
                @else --}}
                 <button id="cancel" class="btn btn-warning" type="submit">Cancel</button>
                 <button id="reject" class="btn btn-danger" type="submit">Reject</button>
                 <button id="approve" class="btn btn-success" type="submit">Approve</button>
                {{-- @endif --}}
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
                    setTimeout(function() {
                        location.reload()
                    }, 2000)
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

    $("#cancel").click(function(e) {
        data = {
            "status": "cancel"
        }
        e.preventDefault();
        if (confirm("Confirm to 'CANCEL' application?")) {
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
                    setTimeout(function() {
                        location.reload()
                    }, 2000)
                },
                error: function(xhr, status, error) {}
            });
        }
    })
</script>

</html>
@endsection