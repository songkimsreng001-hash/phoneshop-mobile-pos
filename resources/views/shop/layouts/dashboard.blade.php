@extends('shop.layouts.main')
@section('page_title', 'Dashboard Screen')

@section('header_styles')

    <!--begin::Page Vendor Stylesheets(used by this page)-->
    <link href="{{asset('admin/assets/plugins/custom/datatables/datatables.bundle.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('admin/assets/plugins/custom/vis-timeline/vis-timeline.bundle.css')}}" rel="stylesheet" type="text/css" />
    <!--end::Page Vendor Stylesheets-->
@endsection

@section('header_scripts')

@endsection
@php
    $productsCount = isset($products) ? count($products) : 0;
    $salesCount = isset($salesCount) ? $salesCount : 0;
    $purchasesCount = isset($purchasesCount) ? $purchasesCount : 0;
    $stockValue = isset($stockValue) ? $stockValue : 0;
    $monthlySales = isset($monthlySales) ? $monthlySales : 0;
@endphp


@section('content')


    <!--begin::Toolbar-->
    <div class="toolbar py-5 py-lg-5" id="kt_toolbar">
        <!--begin::Container-->
        <div id="kt_toolbar_container" class="container-xxl d-flex flex-stack flex-wrap">
            <!--begin::Page title-->
            <div class="page-title d-flex flex-column me-3">
                <!--begin::Title-->
                <h1 class="d-flex text-dark fw-bolder my-1 fs-3">Dashboard</h1>
                <!--end::Title-->
            </div>
            <!--end::Page title-->

        </div>
        <!--end::Container-->
    </div>
    <!--end::Toolbar-->
    <!--begin::Container-->
    <div id="kt_content_container" class="d-flex flex-column-fluid align-items-start container-xxl">
        <!--begin::Post-->
        <div class="content flex-row-fluid" id="kt_content">

            <!--begin::Row-->
            <div class="row g-5 g-xl-10">
                <!--begin::Col-->
                <div class="col-xl-12 mb-5 mb-xl-10">
                    <!--begin::Lists Widget 19-->
                    <div class="card card-flush h-xl-100">
                        <!--begin::Heading-->
                        <div class="card-header justify-content-center rounded bgi-no-repeat bgi-size-cover bgi-position-y-bottom bgi-position-x-center align-items-start h-250px" style="background-image:url('{{asset("admin/assets/media/patterns/pattern-1.jpg")}}')">
                            <!--begin::Title-->
                            <h3 class="card-title align-items-start flex-column text-white pt-15 mb-10 text-center ">
                                <span class="d-block fs-2x fw-bolder mb-3 w-100">Hello, {{auth('web')->user()->name}}</span>
                                <div class="d-block fs-3tx text-white mb-3 w-100">
                                    Welcome To Dashboard
                                </div>
                            </h3>
                            <!--end::Title-->

                        </div>
                        <!--end::Heading-->
                        <!--begin::Body-->
                        <div class="card-body mt-n10">
                            <!--begin::Stats-->
                            <div class="mt-n20 position-relative">
                                <!--begin::Row-->
                                <div class="row g-3 g-lg-6 justify-content-center">
                                    <div class="col-md-3">
                                        <div class="bg-gray-100 bg-opacity-70 rounded-2 px-6 py-5 ">
                                            <div class="d-flex justify-content-between">
                                                <div class="symbol symbol-30px me-5 mb-8">
                                                    <span class="symbol-label"><span class="fs-2qx fas fa-box text-primary"></span></span>
                                                </div>
                                                <div class="symbol me-5 mb-8">
                                                    <span class="text-dark fw-boldest d-block fs-2qx lh-1 mb-1">{{$productsCount}}</span>
                                                </div>
                                            </div>
                                            <div class="mt-10">
                                                <span class="text-gray-700 fw-bold fs-2">Products</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="bg-gray-100 bg-opacity-70 rounded-2 px-6 py-5 ">
                                            <div class="d-flex justify-content-between">
                                                <div class="symbol symbol-30px me-5 mb-8">
                                                    <span class="symbol-label"><span class="fs-2qx fas fa-receipt text-success"></span></span>
                                                </div>
                                                <div class="symbol me-5 mb-8">
                                                    <span class="text-dark fw-boldest d-block fs-2qx lh-1 mb-1">{{$salesCount}}</span>
                                                </div>
                                            </div>
                                            <div class="mt-10">
                                                <span class="text-gray-700 fw-bold fs-2">Sales</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="bg-gray-100 bg-opacity-70 rounded-2 px-6 py-5 ">
                                            <div class="d-flex justify-content-between">
                                                <div class="symbol symbol-30px me-5 mb-8">
                                                    <span class="symbol-label"><span class="fs-2qx fas fa-truck text-info"></span></span>
                                                </div>
                                                <div class="symbol me-5 mb-8">
                                                    <span class="text-dark fw-boldest d-block fs-2qx lh-1 mb-1">{{$purchasesCount}}</span>
                                                </div>
                                            </div>
                                            <div class="mt-10">
                                                <span class="text-gray-700 fw-bold fs-2">Purchases</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="bg-gray-100 bg-opacity-70 rounded-2 px-6 py-5 ">
                                            <div class="d-flex justify-content-between">
                                                <div class="symbol symbol-30px me-5 mb-8">
                                                    <span class="symbol-label"><span class="fs-2qx fas fa-warehouse text-warning"></span></span>
                                                </div>
                                                <div class="symbol me-5 mb-8">
                                                    <span class="text-dark fw-boldest d-block fs-2qx lh-1 mb-1">{{$stockValue}}</span>
                                                </div>
                                            </div>
                                            <div class="mt-10">
                                                <span class="text-gray-700 fw-bold fs-2">Stock Qty</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-3 g-lg-6 mt-5 justify-content-center">
                                    <div class="col-md-6">
                                        <div class="bg-white rounded-2 border p-6">
                                            <h5 class="fw-bold mb-3">This Month</h5>
                                            <p class="text-muted mb-0">Monthly sales: <strong>${{ number_format($monthlySales, 2) }}</strong></p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="bg-white rounded-2 border p-6">
                                            <h5 class="fw-bold mb-3">Shop Scope</h5>
                                            <p class="text-muted mb-0">You can manage only your own shop data.</p>
                                        </div>
                                    </div>
                                </div>
                                <!--end::Row-->
                            </div>
                            <!--end::Stats-->
                        </div>
                        <!--end::Body-->
                    </div>
                    <!--end::Lists Widget 19-->
                </div>
                <!--end::Col-->

            </div>
            <!--end::Row-->



        </div>
        <!--end::Post-->
    </div>
    <!--end::Container-->


@endsection




@section('footer_modals')

@endsection

@section('footer_scripts')

    <!--begin::Page Custom Javascript(used by this page)-->
    <script src="{{asset('admin/assets/js/widgets.bundle.js')}}"></script>
    <script src="{{asset('admin/assets/js/custom/widgets.js')}}"></script>
    <!--end::Page Custom Javascript-->

    <!--begin::Page Vendors Javascript(used by this page)-->
    <script src="{{asset('admin/assets/plugins/custom/datatables/datatables.bundle.js')}}"></script>
    <script src="{{asset('admin/assets/plugins/custom/vis-timeline/vis-timeline.bundle.js')}}"></script>
    <script src="{{url('https://cdn.amcharts.com/lib/5/index.js')}}"></script>
    <script src="{{url('https://cdn.amcharts.com/lib/5/geodata/worldLow.js')}}"></script>
    <script src="{{url('https://cdn.amcharts.com/lib/5/geodata/continentsLow.js')}}"></script>
    <script src="{{url('https://cdn.amcharts.com/lib/5/geodata/usaLow.js')}}"></script>
    <script src="{{url('https://cdn.amcharts.com/lib/5/geodata/worldTimeZonesLow.js')}}"></script>
    <script src="{{url('https://cdn.amcharts.com/lib/5/geodata/worldTimeZoneAreasLow.js')}}"></script>
    <script src="{{url('https://cdn.amcharts.com/lib/5/themes/Animated.js')}}"></script>
    <script src="{{url('https://cdn.amcharts.com/lib/5/xy.js')}}"></script>
    <script src="{{url('https://cdn.amcharts.com/lib/5/percent.js')}}"></script>
    <script src="{{url('https://cdn.amcharts.com/lib/5/radar.js')}}"></script>
    <!--end::Page Vendors Javascript-->

    <script>
        @if($message = Session::get('success'))
        Swal.fire({
            text: "{{$message}}",
            icon: "success",
            buttonsStyling: false,
            showConfirmButton: false,
            timer: 2800
        });
        @endif
        @if($message = Session::get('error'))
        Swal.fire({
            text: "{{$message}}",
            icon: "error",
            buttonsStyling: false,
            confirmButtonText: "Ok, got it!",
            customClass: {
                confirmButton: "btn btn-primary"
            }
        });
        @endif
    </script>


    <script src="https://cdn.amcharts.com/lib/5/hierarchy.js"></script>
    <!-- Chart code -->
    <script>
        am5.ready(function() {

// Create root element
// https://www.amcharts.com/docs/v5/getting-started/#Root_element
            var root = am5.Root.new("chartdiv");

// Set themes
// https://www.amcharts.com/docs/v5/concepts/themes/
            root.setThemes([
                am5themes_Animated.new(root)
            ]);

            var data = {
                value: 0,
                children: []
            };


// Create wrapper container
            var container = root.container.children.push(am5.Container.new(root, {
                width: am5.percent(100),
                height: am5.percent(100),
                layout: root.verticalLayout
            }));

// Create series
// https://www.amcharts.com/docs/v5/charts/hierarchy/#Adding
            var series = container.children.push(am5hierarchy.ForceDirected.new(root, {
                singleBranchOnly: false,
                downDepth: 3,
                topDepth: 1,
                initialDepth: 0,
                valueField: "value",
                categoryField: "name",
                childDataField: "children",
                idField: "name",
                linkWithField: "linkWith",
                manyBodyStrength: -10,
                centerStrength: 0.5,
            }));

            series.get("colors").setAll({
                step: 6
            });

            series.links.template.set("strength", 0.5);

            series.data.setAll([data]);

            series.set("selectedDataItem", series.dataItems[0]);
// Make stuff animate on load
            series.appear(1000, 100);

        }); // end am5.ready()

    </script>


@endsection
