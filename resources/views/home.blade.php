@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-sm-4 grid-margin">
            <div class="card">
                <div class="card-body">
                    <h5>Customers</h5>
                    <div class="row">
                        <div class="col-8 col-sm-12 col-xl-8 my-auto">
                            <div class="d-flex d-sm-block d-md-flex align-items-center">
                                <h2 class="mb-0">{{ $customer_count }}</h2>
                                {{-- <p class="text-success ms-2 mb-0 font-weight-medium">+3.5%</p> --}}
                            </div>
                            {{-- <h6 class="text-muted font-weight-normal">11.38% Since last month</h6> --}}
                        </div>
                        <div class="col-4 col-sm-12 col-xl-4 text-center text-xl-right">
                            <i class="icon-lg mdi mdi-account-group text-primary ml-auto"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-4 grid-margin">
            <div class="card">
                <div class="card-body">
                    <h5>Daily Quotes</h5>
                    <div class="row">
                        <div class="col-8 col-sm-12 col-xl-8 my-auto">
                            <div class="d-flex d-sm-block d-md-flex align-items-center">
                                <h2 class="mb-0">{{ $daily_quotes }}</h2>
                                {{-- <p class="text-success ms-2 mb-0 font-weight-medium">+8.3%</p> --}}
                            </div>
                            {{-- <h6 class="text-muted font-weight-normal"> 9.61% Since last month</h6> --}}
                        </div>
                        <div class="col-4 col-sm-12 col-xl-4 text-center text-xl-right">
                            <i class="icon-lg mdi mdi-format-quote-close text-success ml-auto"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-4 grid-margin">
            <div class="card">
                <div class="card-body">
                    <h5>HRV</h5>
                    <div class="row">
                        <div class="col-8 col-sm-12 col-xl-8 my-auto">
                            <div class="d-flex d-sm-block d-md-flex align-items-center">
                                <h2 class="mb-0">{{ $hrvs }}</h2>
                                {{-- <p class="text-danger ms-2 mb-0 font-weight-medium">-2.1% </p> --}}
                            </div>
                            {{-- <h6 class="text-muted font-weight-normal">2.27% Since last month</h6> --}}
                        </div>
                        <div class="col-4 col-sm-12 col-xl-4 text-center text-xl-right">
                            <i class="icon-lg mdi mdi-heart text-danger ml-auto"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
