@extends('admin.layouts.main')

@section('page_title', 'Purchases')

@section('content')
<div class="toolbar py-5 py-lg-5" id="kt_toolbar">
    <div class="container-xxl d-flex flex-stack flex-wrap">
        <div class="page-title d-flex flex-column me-3">
            <h1 class="d-flex text-dark fw-bolder my-1 fs-3">Purchases</h1>
        </div>
    </div>
</div>

<div id="kt_content_container" class="d-flex flex-column-fluid align-items-start container-xxl">
    <div class="content flex-row-fluid" id="kt_content">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Purchase History</h3>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Supplier</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Grand Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchases as $purchase)
                            <tr>
                                <td>{{ $purchase->reference_no }}</td>
                                <td>{{ $purchase->supplier->name ?? 'N/A' }}</td>
                                <td>{{ $purchase->purchase_date }}</td>
                                <td>{{ $purchase->status }}</td>
                                <td>{{ $purchase->grand_total }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
