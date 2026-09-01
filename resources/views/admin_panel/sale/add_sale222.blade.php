@extends('admin_panel.layout.app')

@section('content')
    <!-- Loader Overlay -->
    <div id="pageLoader"
        class="{{ isset($sale) ? '' : 'd-none' }} position-fixed top-0 start-0 w-100 h-100 d-flex flex-column gap-3 justify-content-center align-items-center"
        style="background: rgba(255,255,255,0.9); z-index: 1055;">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Loading...</span>
        </div>
        <div class="fw-bold text-primary fs-5">Loading Sale Data...</div>
    </div>
    <link href="{{ asset('assets/vendors/bootstrap5/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendors/select2/css/select2.min.css') }}" rel="stylesheet" />
    <style>
        /* ================= MODERN PROFESSIONAL POS & ERP UI ================= */
        :root {
            --pos-bg: #f8fafc;
            --pos-card-bg: #ffffff;
            --pos-border: #e2e8f0;
            --pos-border-focus: #3b82f6;
            --pos-primary: #2563eb;
            --pos-primary-hover: #1d4ed8;
            --pos-success: #10b981;
            --pos-success-hover: #059669;
            --pos-danger: #ef4444;
            --pos-text-main: #0f172a;
            --pos-text-muted: #64748b;
            --pos-radius: 8px;
            --pos-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.06), 0 1px 2px -1px rgba(0, 0, 0, 0.04);
            --pos-shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.07), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
        }

        body {
            background-color: var(--pos-bg) !important;
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
            color: var(--pos-text-main) !important;
            -webkit-font-smoothing: antialiased;
        }

        .main-container {
            border: 1px solid var(--pos-border) !important;
            border-radius: var(--pos-radius) !important;
            box-shadow: var(--pos-shadow) !important;
            background-color: var(--pos-card-bg) !important;
            padding: 10px !important;
            max-width: 100%;
        }

        /* Modern Top Information Card */
        .top-info-card {
            background-color: #f8fafc !important;
            border: 1px solid var(--pos-border) !important;
            border-radius: var(--pos-radius) !important;
            padding: 10px 12px !important;
            box-shadow: inset 0 1px 2px rgba(0,0,0,0.02);
        }

        .meta-label {
            font-size: 0.7rem !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5px !important;
            color: #475569 !important;
            margin-bottom: 4px !important;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .card-panel {
            background-color: #ffffff !important;
            border: 1px solid var(--pos-border) !important;
            border-radius: var(--pos-radius) !important;
            padding: 10px !important;
            box-shadow: var(--pos-shadow) !important;
        }

        /* Section Header Titles */
        .section-header-title {
            font-weight: 700 !important;
            font-size: 0.82rem !important;
            color: var(--pos-text-main) !important;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* Uniform Form Inputs in Top Bar */
        .form-control,
        .form-select {
            border: 1px solid var(--pos-border) !important;
            border-radius: 6px !important;
            padding: 4px 8px !important;
            font-weight: 500 !important;
            color: var(--pos-text-main) !important;
            background-color: #ffffff !important;
            transition: all 0.15s ease-in-out !important;
            height: 32px !important;
            font-size: 0.8rem !important;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--pos-border-focus) !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15) !important;
            outline: none !important;
            background-color: #ffffff !important;
        }

        .input-readonly {
            background-color: #f1f5f9 !important;
            border-color: var(--pos-border) !important;
            color: #475569 !important;
            font-weight: 600 !important;
            cursor: not-allowed !important;
        }

        /* Invoice Series Input Group */
        .invoice-group .btn-prefix {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%) !important;
            border: 1px solid #0284c7 !important;
            color: #ffffff !important;
            font-weight: 700 !important;
            border-top-left-radius: 6px !important;
            border-bottom-left-radius: 6px !important;
            height: 32px !important;
            padding: 0 10px !important;
            font-size: 0.78rem !important;
        }

        .invoice-group .btn-refresh {
            background: #f1f5f9 !important;
            border: 1px solid var(--pos-border) !important;
            border-left: none !important;
            color: #475569 !important;
            border-top-right-radius: 6px !important;
            border-bottom-right-radius: 6px !important;
            height: 32px !important;
            padding: 0 10px !important;
            transition: all 0.15s;
        }
        .invoice-group .btn-refresh:hover {
            background: #e2e8f0 !important;
            color: var(--pos-primary) !important;
        }

        /* Top Save Sale Button */
        .btn-top-save {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
            border: none !important;
            color: #ffffff !important;
            font-weight: 700 !important;
            height: 32px !important;
            border-radius: 6px !important;
            box-shadow: 0 2px 6px rgba(16, 185, 129, 0.25) !important;
            transition: all 0.15s ease !important;
        }
        .btn-top-save:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%) !important;
            transform: translateY(-1px);
        }

        /* Select2 Alignment */
        #customerInputWrapper .select2-container--default .select2-selection--single {
            height: 32px !important;
            min-height: 32px !important;
            padding: 0 !important;
            display: flex !important;
            align-items: center !important;
            border: 1px solid var(--pos-border) !important;
            border-radius: 6px !important;
            background-color: #ffffff !important;
        }
        #customerInputWrapper .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 30px !important;
            padding-left: 8px !important;
            font-size: 0.8rem !important;
            color: var(--pos-text-main) !important;
            font-weight: 500 !important;
        }
        #customerInputWrapper .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 30px !important;
            top: 0 !important;
            right: 4px !important;
        }
        #customerInputWrapper .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: var(--pos-border-focus) !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15) !important;
        }

        /* Transaction Grid / Table */
        .table-responsive {
            border: 1px solid var(--pos-border) !important;
            border-radius: 6px !important;
            overflow-x: auto !important;
            background-color: #ffffff;
            box-shadow: none !important;
        }

        .sales-table {
            border-collapse: collapse !important;
            margin-bottom: 0 !important;
            width: 100%;
            min-width: 880px;
        }

        .sales-table thead th {
            background-color: #f1f5f9 !important;
            color: #334155 !important;
            font-weight: 700 !important;
            text-transform: uppercase;
            font-size: 11px !important;
            letter-spacing: 0.4px;
            padding: 6px 6px !important;
            border: 1px solid var(--pos-border) !important;
            border-bottom: 2px solid #cbd5e1 !important;
            vertical-align: middle !important;
            text-align: center;
            white-space: nowrap;
        }

        .sales-table thead th.col-product {
            text-align: left !important;
            padding-left: 8px !important;
        }

        .sales-table tbody td {
            border: 1px solid var(--pos-border) !important;
            padding: 0 !important;
            background-color: #ffffff;
            vertical-align: middle !important;
        }

        .sales-table tbody tr:hover td {
            background-color: #f8fafc !important;
        }

        /* Table Inputs */
        .sales-table tbody .form-control,
        .sales-table tbody .form-select {
            border: none !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            height: 30px !important;
            margin: 0 !important;
            padding: 2px 6px !important;
            width: 100% !important;
            background-color: transparent !important;
            text-align: center;
            color: var(--pos-text-main) !important;
            font-weight: 500 !important;
            font-size: 0.78rem !important;
        }

        .sales-table tbody td.col-product .form-select {
            text-align: left !important;
            padding-left: 8px !important;
        }

        .sales-table tbody .input-readonly,
        .sales-table tbody input[readonly],
        .sales-table tbody select[disabled] {
            background-color: #f8fafc !important;
            cursor: not-allowed !important;
            color: #475569 !important;
            font-weight: 600 !important;
        }

        .sales-table tbody .form-control:focus,
        .sales-table tbody .form-select:focus {
            outline: none !important;
            background-color: #ffffff !important;
            box-shadow: inset 0 0 0 2px var(--pos-border-focus) !important;
        }

        /* Select2 inside Table */
        .sales-table tbody .select2-container--default .select2-selection--single {
            height: 30px !important;
            padding: 0 !important;
            border: none !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            background-color: transparent !important;
            display: flex;
            align-items: center;
        }
        .sales-table tbody .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 30px !important;
            padding-left: 8px !important;
            padding-right: 16px !important;
            font-size: 0.78rem !important;
            color: var(--pos-text-main) !important;
            font-weight: 500 !important;
            text-align: left !important;
        }
        .sales-table tbody .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 30px !important;
            right: 4px !important;
        }
        .sales-table tbody .select2-container--default.select2-container--focus .select2-selection--single {
            background-color: #ffffff !important;
            box-shadow: inset 0 0 0 2px var(--pos-border-focus) !important;
        }

        /* Discount Input + Button Toggle */
        .sales-table tbody .discount-wrapper {
            display: flex !important;
            align-items: stretch !important;
            width: 100% !important;
            height: 30px !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        .sales-table tbody .discount-wrapper .discount-value {
            flex-grow: 1 !important;
            border: none !important;
            border-radius: 0 !important;
            height: 100% !important;
            text-align: center;
            background-color: transparent !important;
            padding: 2px 4px !important;
        }
        .sales-table tbody .discount-wrapper .discount-toggle {
            border: none !important;
            border-left: 1px solid var(--pos-border) !important;
            border-radius: 0 !important;
            background-color: #f1f5f9 !important;
            color: #475569 !important;
            font-weight: 700 !important;
            font-size: 0.72rem !important;
            width: 24px !important;
            min-width: 24px !important;
            height: 100% !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 0 !important;
            cursor: pointer !important;
            transition: all 0.15s;
        }
        .sales-table tbody .discount-wrapper .discount-toggle:hover {
            background-color: #e2e8f0 !important;
            color: var(--pos-text-main) !important;
        }

        .sales-table tfoot td {
            background-color: #f8fafc !important;
            border: 1px solid var(--pos-border) !important;
            border-top: 2px solid #cbd5e1 !important;
            padding: 6px 8px !important;
            font-weight: 700 !important;
            color: #334155 !important;
            font-size: 0.8rem !important;
        }

        /* Row Delete Button */
        .btn-del-row {
            width: 24px;
            height: 24px;
            padding: 0;
            border-radius: 4px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #fef2f2;
            color: #ef4444;
            border: 1px solid #fecdd3;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.15s;
        }
        .btn-del-row:hover {
            background: #ef4444;
            color: #ffffff;
            border-color: #ef4444;
        }

        /* Retail Mode Row Toggle Badge */
        .price-mode-row-toggle {
            height: 24px !important;
            min-width: 20px !important;
            font-size: 0.65rem !important;
            font-weight: 700 !important;
            border-radius: 4px !important;
            padding: 0 4px !important;
        }

        /* Summary Card Styling */
        .summary-card {
            background: #ffffff !important;
            border: 1px solid var(--pos-border) !important;
            border-radius: var(--pos-radius) !important;
            padding: 12px 14px !important;
            box-shadow: var(--pos-shadow) !important;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 0;
            border-bottom: 1px dashed #f1f5f9;
            font-size: 0.8rem;
        }
        .summary-row:last-child {
            border-bottom: none;
        }
        .summary-val-net {
            font-weight: 800;
            color: var(--pos-primary);
            font-size: 1.1rem;
        }
        .summary-val-change {
            font-weight: 800;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 1rem;
            background: #fef2f2;
            color: #ef4444;
        }
        .summary-val-change.text-success {
            background: #f0fdf4 !important;
            color: #16a34a !important;
        }

        /* Payment Methods Card */
        .payment-methods-card {
            background: #ffffff !important;
            border: 1px solid var(--pos-border) !important;
            border-radius: var(--pos-radius) !important;
            padding: 12px 14px !important;
            box-shadow: var(--pos-shadow) !important;
        }

        /* Bottom Summary Strip */
        .bottom-summary-strip {
            background: #ffffff;
            border: 1px solid var(--pos-border);
            border-radius: var(--pos-radius);
            padding: 8px 16px;
            margin-top: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            box-shadow: var(--pos-shadow);
        }

        .btn-save-complete {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
            color: #ffffff !important;
            font-weight: 700 !important;
            border-radius: 6px !important;
            padding: 8px 20px !important;
            font-size: 0.85rem !important;
            border: none !important;
            box-shadow: 0 2px 8px rgba(16, 185, 129, 0.25) !important;
            transition: all 0.2s ease !important;
            cursor: pointer;
        }
        .btn-save-complete:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%) !important;
            transform: translateY(-1px);
        }

        /* Quick Products Drawer Cards */
        .pos-product-card {
            background: #ffffff;
            border: 1px solid var(--pos-border);
            border-radius: 8px;
            padding: 8px 10px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.15s ease;
        }
        .pos-product-card:hover {
            border-color: var(--pos-border-focus);
            box-shadow: 0 2px 6px rgba(59, 130, 246, 0.1);
        }
        .pos-product-img {
            width: 36px;
            height: 36px;
            border-radius: 6px;
            object-fit: cover;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .pos-product-info {
            flex: 1;
            margin-left: 8px;
            margin-right: 8px;
            overflow: hidden;
        }
        .pos-product-name {
            font-size: 0.78rem;
            font-weight: 700;
            color: var(--pos-text-main);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .pos-product-sub {
            font-size: 0.68rem;
            color: var(--pos-text-muted);
        }
        .pos-product-price {
            font-size: 0.78rem;
            font-weight: 700;
            color: var(--pos-text-main);
            text-align: right;
        }
        .pos-product-add-btn {
            width: 26px;
            height: 26px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            background: var(--pos-primary);
            color: #fff;
            border: none;
            font-size: 0.8rem;
            cursor: pointer;
        }
        .pos-product-add-btn:hover {
            background: var(--pos-primary-hover);
        }

        .badge-stock-green {
            background-color: #dcfce7 !important;
            color: #166534 !important;
            font-weight: 700 !important;
            border: 1px solid #bbf7d0 !important;
            padding: 2px 6px !important;
            border-radius: 4px !important;
            font-size: 0.7rem !important;
        }
    </style>

    <div class="container-fluid py-2 px-2">
        <div class="main-container bg-white border mx-auto p-3 rounded-3">

            <div id="alertBox" class="alert d-none mb-2" role="alert" style="padding:6px 12px; font-size:0.8rem;"></div>

            <form id="saleForm" autocomplete="off">
                @csrf
                <input type="hidden" id="booking_id" name="booking_id" value="">
                <input type="hidden" id="action" name="action" value="sale">
                <input type="hidden" name="cash" value="0">
                <input type="hidden" id="totalBalance" value="0">

                {{-- TOP HEADER BAR --}}
                <div class="d-flex justify-content-between align-items-center mb-2 px-1">
                    <div class="d-flex align-items-center gap-2">
                        <a href="{{ route('sale.index') }}" class="btn btn-sm btn-light border rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Back"><i class="fas fa-arrow-left text-secondary"></i></a>
                        <div>
                            <h5 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2" style="font-size: 1.05rem;">
                                <i class="fas fa-shopping-cart text-primary"></i> New Sale
                            </h5>
                            <small class="text-muted" style="font-size: 0.72rem;">Create a new invoice & manage checkout</small>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-light border rounded-2 text-secondary px-2 py-1" title="Calculator"><i class="fas fa-calculator"></i></button>
                        <button type="button" class="btn btn-sm btn-light border rounded-2 text-secondary px-2 py-1" title="Toggle Theme"><i class="fas fa-moon"></i></button>
                        <button type="button" class="btn btn-sm btn-light border rounded-2 text-secondary px-2 py-1" title="Fullscreen" onclick="document.documentElement.requestFullscreen()"><i class="fas fa-expand"></i></button>
                    </div>
                </div>

                <!-- TOP INFORMATION PANEL -->
                <div class="top-info-card mb-3">
                    <div class="row g-2 align-items-end w-100 m-0">
                        <!-- Invoice No with Prefix Dropdown & Refresh -->
                        <div class="col-sm-6 col-md-3 col-lg-2">
                            <label class="meta-label"><i class="fas fa-receipt text-primary"></i> Invoice No.</label>
                            <div class="input-group input-group-sm invoice-group">
                                <button class="btn btn-prefix dropdown-toggle d-flex align-items-center gap-1" 
                                        type="button" 
                                        id="btnInvoicePrefix" 
                                        data-bs-toggle="dropdown" 
                                        aria-expanded="false">
                                    <span id="activePrefixLabel">{{ $activePrefix ?? 'INV' }}</span>
                                </button>
                                <ul class="dropdown-menu shadow-lg p-1 border-0" id="dropdownInvoiceSeriesList" aria-labelledby="btnInvoicePrefix" style="min-width: 155px; font-size: 0.8rem; z-index: 1050;">
                                    @if(isset($allSeries) && count($allSeries) > 0)
                                        @foreach($allSeries as $s)
                                            <li>
                                                <a class="dropdown-item fw-bold {{ ($activePrefix ?? 'INV') == $s->prefix ? 'text-success active bg-light' : '' }}" 
                                                   href="#" 
                                                   data-prefix="{{ $s->prefix }}" 
                                                   data-next="{{ $s->next_number }}" 
                                                   data-padding="{{ $s->padding }}">
                                                    @if(($activePrefix ?? 'INV') == $s->prefix) <i class="fas fa-check text-success me-1"></i> @endif 
                                                    {{ $s->prefix }} <span class="text-muted small font-monospace">({{ $s->padding }}d)</span>
                                                </a>
                                            </li>
                                        @endforeach
                                    @else
                                        <li><a class="dropdown-item fw-bold text-success active bg-light" href="#" data-prefix="INV"><i class="fas fa-check text-success me-1"></i> INV (4d)</a></li>
                                    @endif
                                    <li><hr class="dropdown-divider my-1"></li>
                                    <li>
                                        <a class="dropdown-item fw-bold text-success d-flex align-items-center gap-1" href="#" id="btnOpenAddSeriesModal">
                                            <i class="fas fa-plus-circle me-1"></i> Add Series
                                        </a>
                                    </li>
                                </ul>

                                <input type="text" class="form-control text-center fw-bold input-readonly" name="Invoice_no" id="inputInvoiceNo" value="{{ $nextInvoiceNumber }}" readonly style="font-family: 'JetBrains Mono', monospace; font-size: 0.8rem;">

                                <button class="btn btn-refresh" 
                                        type="button" 
                                        id="btnRefreshInvoiceNo" 
                                        title="Regenerate Invoice Number">
                                    <i class="fas fa-sync-alt" id="iconRefreshInvoice"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Date -->
                        <div class="col-sm-6 col-md-2 col-lg-2">
                            <label class="meta-label"><i class="far fa-calendar-alt text-primary"></i> Date</label>
                            <input type="text" name="sale_date" class="form-control datepicker-custom text-center fw-bold" id="displayDateInput" value="{{ date('Y-m-d') }}">
                        </div>

                        <!-- Cr. Days -->
                        <div class="col-sm-6 col-md-1 col-lg-1">
                            <label class="meta-label"><i class="fas fa-clock text-muted"></i> Cr. Days</label>
                            <input type="number" class="form-control text-center fw-bold" name="credit_days" placeholder="0" min="0" value="{{ $sale->credit_days ?? '0' }}">
                        </div>

                        <!-- Remarks -->
                        <div class="col-sm-6 col-md-2 col-lg-2">
                            <label class="meta-label"><i class="far fa-comment-dots text-muted"></i> Remarks</label>
                            <input type="text" class="form-control" name="reference" id="remarks" placeholder="Notes / Ref...">
                        </div>

                        <!-- Customer Type -->
                        <div class="col-sm-6 col-md-2 col-lg-2" id="customerTypeCol">
                            <label class="meta-label"><i class="fas fa-user-tag text-primary"></i> Customer Type</label>
                            <select class="form-select fw-bold" id="partyTypeSelect" name="partyType">
                                @foreach(\App\Models\CustomerType::orderBy('name')->get() as $type)
                                    <option value="{{ $type->name }}" {{ $type->name === 'Main Customer' ? 'selected' : '' }}>{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Customer & Walk-in Toggle -->
                        <div class="col-sm-6 col-md-2 col-lg-2">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="meta-label mb-0"><i class="fas fa-user text-primary"></i> Customer</label>
                                <button type="button" class="btn btn-sm btn-outline-success py-0 px-2 rounded-pill fw-bold" id="btnOpenAddCustomerModal" data-bs-toggle="modal" data-bs-target="#addCustomerModal" style="font-size: 0.65rem; height: 18px; line-height: 1;">
                                    <i class="fas fa-plus"></i> New
                                </button>
                            </div>
                            <div id="customerInputWrapper">
                                <input type="text" class="form-control fw-bold d-none" name="walkin_name" id="walkinNameInput" value="Walk-in Customer" placeholder="Enter Walk-in Name...">
                                <select class="form-select" id="customerSelect" name="customer" style="width:100%">
                                    <option value=""></option>
                                </select>
                            </div>
                        </div>

                        <!-- Save Sale Button -->
                        <div class="col-sm-12 col-md-12 col-lg-1 d-flex align-items-end">
                            <input type="hidden" name="is_walkin" id="is_walkin" value="0">
                            <button type="button" class="btn btn-top-save w-100 fw-bold d-flex align-items-center justify-content-center gap-1" id="btnHeaderSaveSale" style="font-size: 0.75rem;">
                                <i class="fas fa-check"></i> Save
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Hidden fields for backend --}}
                <input type="hidden" id="address" name="address">
                <input type="hidden" id="tel" name="tel">
                <input type="hidden" id="previousBalance" value="0">
                <input type="hidden" id="rangeBalance" value="0">

                <!-- 2-COLUMN RESPONSIVE POS LAYOUT -->
                <div class="row g-3 align-items-stretch">
                    <!-- LEFT MAIN AREA: Items Grid Table (col-lg-8 col-xl-9) -->
                    <div class="col-lg-8 col-xl-9">
                        <div class="card-panel d-flex flex-column h-100 p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="section-header-title">
                                        <i class="fas fa-list-check text-primary"></i> Order Items
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-0" style="font-size:0.7rem;" id="itemsRowCount">0</span>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2 rounded-2 fw-semibold d-flex align-items-center gap-1" data-bs-toggle="offcanvas" data-bs-target="#quickProductsOffcanvas" style="font-size:0.75rem;">
                                        <i class="fas fa-th"></i> Quick Products
                                    </button>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-success btn-sm py-1 px-3 rounded-2 fw-bold d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#quickAddProductModal" style="font-size:0.75rem;">
                                        <i class="fas fa-bolt text-warning"></i> Create Product
                                    </button>
                                    <button type="button" class="btn btn-primary btn-sm py-1 px-3 rounded-2 fw-bold d-flex align-items-center gap-1 shadow-sm" id="btnAdd" style="font-size:0.75rem;">
                                        <i class="fas fa-plus"></i> Add Row
                                    </button>
                                </div>
                            </div>

                            <div class="table-responsive flex-grow-1">
                                <table class="table table-bordered sales-table mb-0">
                                    <thead>
                                        <tr>
                                            <th style="width:30px;" class="text-center">#</th>
                                            <th class="col-product" style="min-width: 160px;">PRODUCT</th>
                                            <th class="col-stock" style="width: 60px;">STOCK</th>
                                            <th class="col-qty" style="width: 85px;">QTY</th>
                                            <th class="col-size" style="width: 55px;">SIZE</th>
                                            <th class="col-color" style="width: 65px;">COLOR</th>
                                            <th class="col-pieces" style="width: 55px;">PCS</th>
                                            <th class="col-price-p" style="width: 85px;">PRICE</th>
                                            <th class="col-disc" style="width: 85px;">DISCOUNT</th>
                                            <th class="col-amount" style="width: 95px;">AMOUNT</th>
                                            <th class="col-action" style="width: 34px;">×</th>
                                        </tr>
                                    </thead>
                                    <tbody id="salesTableBody">
                                        <tr>
                                            <!-- # ROW INDEX -->
                                            <td class="text-center fw-bold text-muted row-index" style="vertical-align:middle; font-size:0.75rem;">1</td>

                                            <!-- PRODUCT -->
                                            <td class="col-product">
                                                <select class="form-select product" style="width:100%">
                                                    <option value=""></option>
                                                </select>
                                                <input type="hidden" class="product-id-hidden" name="product_id[]">
                                                <input type="hidden" class="variant-data-hidden" name="color[]">
                                                <input type="hidden" class="item-code-display">
                                                <input type="hidden" class="size-h">
                                                <input type="hidden" class="size-w">
                                                <input type="hidden" class="size-mode-text">
                                            </td>

                                            <!-- STOCK -->
                                            <td class="col-stock">
                                                <input type="text" class="form-control stock text-center input-readonly" readonly tabindex="-1">
                                                <input type="hidden" class="warehouse" name="warehouse_id[]" value="{{ auth()->user()->warehouse_id ?? 1 }}">
                                                <input type="hidden" class="variant-stock-value">
                                            </td>

                                            <!-- Qty cell with Sub-Unit toggle -->
                                            <td style="width:85px;" class="col-qty-wrapper">
                                                <div class="d-flex align-items-center gap-1">
                                                    <input type="number" step="any" class="form-control carton-qty text-start fw-bold" name="carton_qty[]" placeholder="0" min="0" value="" style="flex: 1; min-width: 0; padding-left: 6px;">
                                                    <button type="button" class="btn btn-sm btn-outline-primary qty-unit-toggle px-1 py-0 d-none" 
                                                            data-unit-mode="main" title="Toggle Unit" style="font-size: 0.65rem; height: 26px; min-width: 28px; font-weight: 700; border-radius: 4px; flex-shrink: 0;">
                                                        Kg
                                                    </button>
                                                </div>
                                                <input type="hidden" class="hidden-sub-unit-mode" name="sub_unit_mode[]" value="main">
                                            </td>

                                            <!-- Loose Pieces -->
                                            <td style="width:70px;" class="d-none">
                                                <input type="number" class="form-control loose-pcs-input text-end" name="loose_qty[]" placeholder="" min="0" value="">
                                            </td>

                                            <!-- Size -->
                                            <td class="col-size">
                                                <input type="text" class="form-control size-display text-center" name="size_display[]" placeholder="-">
                                                <input type="hidden" class="pack-qty" name="pack_qty[]" value="1">
                                            </td>

                                            <!-- Color (Display - readonly) -->
                                            <td class="col-color">
                                                <input type="text" class="form-control color-display text-center input-readonly" readonly tabindex="-1" placeholder="-">
                                            </td>

                                            <!-- Total Pieces (Calculated) -->
                                            <td class="col-pieces">
                                                <input type="text" class="form-control total-pieces text-end input-readonly fw-semibold" name="total_pieces[]" readonly placeholder="0" tabindex="-1">
                                                <input type="hidden" class="sales-qty" name="qty[]" value="0">
                                            </td>
                                         
                                            <!-- Price/Piece (EDITABLE) -->
                                            <td class="col-price-p">
                                                <div class="d-flex align-items-center gap-1">
                                                    <input type="text" class="form-control visible-price text-end fw-semibold" name="visible_price[]" placeholder="0" style="flex: 1; min-width: 0;">
                                                    <button type="button" class="btn btn-sm btn-outline-primary price-mode-row-toggle px-1 py-0" 
                                                            data-mode="retail" title="Retail Mode">
                                                        R
                                                    </button>
                                                </div>
                                                <input type="hidden" class="price-per-piece" name="price_per_piece[]">
                                                <input type="hidden" class="retail-price">
                                                <input type="hidden" class="wholesale-price">
                                                <input type="hidden" class="weight-per-piece">
                                            </td>

                                            <!-- SINGLE DISCOUNT COLUMN -->
                                            <td class="col-disc">
                                                <div class="discount-wrapper">
                                                    <input type="number"
                                                           class="form-control discount-value text-end"
                                                           name="item_disc[]"
                                                           placeholder="0">
                                                    <input type="hidden" class="discount-type-hidden" name="discount_type[]" value="percent">
                                                    <button type="button"
                                                            class="btn btn-outline-secondary discount-toggle"
                                                            data-type="percent" tabindex="-1">%</button>
                                                </div>
                                                <input type="hidden" class="discount-amount" value="0">
                                            </td>

                                            <!-- NET AMOUNT -->
                                            <td class="col-amount">
                                                <input type="text" class="form-control sales-amount text-end input-readonly fw-bold text-dark" name="total[]" value="0" readonly tabindex="-1">
                                                <input type="hidden" class="gross-amount" name="gross_amount[]">
                                            </td>

                                            <!-- ACTION -->
                                            <td class="col-action text-center">
                                                <button type="button" class="btn-del-row del-row" tabindex="-1" title="Delete Row">&times;</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="9" class="text-end fw-bold text-uppercase text-secondary" style="font-size:0.8rem;">GRID TOTAL:</td>
                                            <td class="text-end fw-bold text-success fs-6"><span id="totalAmount">0.00</span></td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT PANEL: Summary & Payment Methods (col-lg-4 col-xl-3) -->
                    <div class="col-lg-4 col-xl-3">
                        <div class="d-flex flex-column h-100 gap-3">
                            <!-- Executive Summary Card -->
                            <div class="summary-card">
                                <div class="d-flex justify-content-between align-items-center pb-2 mb-2 border-bottom">
                                    <span class="fw-bold text-dark d-flex align-items-center gap-1" style="font-size:0.85rem;"><i class="fas fa-calculator text-primary"></i> Summary</span>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-0" style="font-size:0.7rem;">Live</span>
                                </div>
                                
                                <div class="summary-row">
                                    <span class="text-muted">Total Amount</span>
                                    <span class="fw-bold text-dark" id="tGross">0.00</span>
                                </div>
                                <div class="summary-row">
                                    <span class="text-muted">Line Discount</span>
                                    <span class="fw-bold text-danger" id="tLineDisc">0.00</span>
                                </div>
                                <div class="summary-row">
                                    <span class="fw-bold text-dark">Net Total</span>
                                    <span class="summary-val-net" id="tSub">0.00</span>
                                </div>
                                <div class="summary-row">
                                    <span class="text-muted">Total Paid</span>
                                    <span class="fw-bold text-success fs-6" id="receiptsTotal">0.00</span>
                                    <span id="receiptsTotalBadge" style="display:none;">0.00</span>
                                </div>
                                <div class="summary-row pt-1">
                                    <span class="fw-bold text-dark">Change</span>
                                    <span class="summary-val-change" id="walkinChange">-0.00</span>
                                </div>
                                <div class="summary-row pt-2 align-items-center" id="changeAccountRow" style="display: none; border-top: 1px dashed #f1aeb5; background: #fff8f8; padding: 6px 8px; border-radius: 6px;">
                                    <span class="text-danger fw-bold d-flex align-items-center gap-1" style="font-size:0.76rem;"><i class="fas fa-hand-holding-usd"></i> Change A/C</span>
                                    <select class="form-select form-select-sm bg-white fw-bold text-danger border-danger" name="change_account_id" id="changeAccountId" style="width: 135px; font-size:0.75rem; height: 28px; padding: 2px 6px;">
                                        @foreach ($accounts as $acc)
                                            <option value="{{ $acc->id }}" {{ str_contains(strtolower($acc->title), 'cash') ? 'selected' : '' }}>{{ $acc->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Payment Methods Card -->
                            <div class="payment-methods-card flex-grow-1 d-flex flex-column">
                                <div class="d-flex align-items-center justify-content-between pb-2 mb-2 border-bottom">
                                    <span class="fw-bold text-dark d-flex align-items-center gap-1" style="font-size:0.85rem;"><i class="fas fa-wallet text-success"></i> Payment Methods</span>
                                    <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 rounded-2 fw-bold" id="btnAddRV" style="font-size:0.7rem;"><i class="fas fa-plus me-1"></i>Add Account</button>
                                </div>

                                <div id="rvWrapper" class="mb-3">
                                    <div class="d-flex gap-2 align-items-center mb-2 rv-row">
                                        <select class="form-select form-select-sm rv-account bg-light fw-bold" name="receipt_account_id[]" style="font-size:0.78rem;">
                                            @foreach ($accounts as $acc)
                                                <option value="{{ $acc->id }}" {{ str_contains(strtolower($acc->title), 'cash') || str_contains(strtolower($acc->title), 'easypaisa') ? 'selected' : '' }}>{{ $acc->title }}</option>
                                            @endforeach
                                        </select>
                                        <input type="number" step="0.01" class="form-control form-control-sm text-end rv-amount fw-bold" name="receipt_amount[]" placeholder="0.00" style="width: 110px; font-size:0.8rem;">
                                    </div>
                                </div>

                                <button type="button" class="btn btn-save-complete w-100 mt-auto py-2 d-flex align-items-center justify-content-center gap-2" id="btnSaveAndComplete">
                                    <i class="fas fa-check-circle"></i> Save & Complete (F9)
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BOTTOM SUMMARY STRIP -->
                <div class="bottom-summary-strip">
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted fw-bold" style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.5px;">Net Total</span>
                        <span class="fs-5 fw-bolder text-primary" id="walkinNetTotal">0.00</span>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted fw-bold" style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.5px;">Discount (Rs.)</span>
                        <div class="input-group input-group-sm" style="width: 120px;">
                            <input type="number" class="form-control text-end fw-bold text-danger" id="walkinDiscountRs" value="0" placeholder="0">
                            <span class="input-group-text bg-light text-muted fw-bold" style="font-size:0.75rem;">%</span>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted fw-bold" style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.5px;">Payments</span>
                        <span class="fs-6 fw-bold text-success" id="bottomPaymentsTotal">0.00</span>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted fw-bold" style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.5px;">Change</span>
                        <span class="fs-6 fw-bold text-danger" id="bottomChangeVal">-0.00</span>
                    </div>

                    <div class="align-items-center gap-1" id="bottomChangeAccountWrapper" style="display: none;">
                        <span class="text-danger fw-bold" style="font-size:0.76rem;">Change A/C:</span>
                        <select class="form-select form-select-sm bg-light fw-bold text-danger border-danger" id="bottomChangeAccountId" style="width: 135px; font-size:0.75rem; height: 30px; padding: 2px 6px;">
                            @foreach ($accounts as $acc)
                                <option value="{{ $acc->id }}" {{ str_contains(strtolower($acc->title), 'cash') ? 'selected' : '' }}>{{ $acc->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="button" class="btn btn-save-complete d-flex align-items-center gap-2" id="btnSaveAndComplete2">
                        <i class="fas fa-check-circle"></i> Save & Complete (F9)
                    </button>
                </div>

                {{-- ACTION BUTTONS ROW --}}
                <div class="d-flex flex-wrap gap-2 justify-content-center py-2 px-3 mt-3 border-top bg-light rounded-3">
                    <button type="button" class="btn btn-outline-primary btn-sm px-3 fw-bold rounded-2 d-flex align-items-center gap-1" id="btnSave"><i class="fas fa-bookmark"></i> Booking</button>
                    <button type="button" class="btn btn-primary btn-sm px-4 fw-bold rounded-2 d-flex align-items-center gap-1 shadow-sm" id="btnPosted" disabled><i class="fas fa-shopping-cart"></i> Sale</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3 fw-bold rounded-2 d-flex align-items-center gap-1" id="btnPrint"><i class="fas fa-print"></i> A4 Print</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3 fw-bold rounded-2 d-flex align-items-center gap-1" id="btnEstimate"><i class="fas fa-file-invoice"></i> Estimate</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3 fw-bold rounded-2 d-flex align-items-center gap-1" id="btnPrint2"><i class="fas fa-receipt"></i> Thermal Print</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3 fw-bold rounded-2 d-flex align-items-center gap-1" id="btnDcThermal"><i class="fas fa-truck"></i> DC</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Quick Products Offcanvas Drawer -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="quickProductsOffcanvas" style="width: 360px;">
        <div class="offcanvas-header bg-light py-2 border-bottom">
            <h6 class="offcanvas-title fw-bold text-dark mb-0"><i class="fas fa-th text-primary me-2"></i>Quick Products Panel</h6>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-2">
            <div class="input-group input-group-sm mb-2">
                <input type="text" class="form-control" id="sidebarProductSearch" placeholder="Search product by name, barcode or SKU...">
                <button class="btn btn-primary px-2" type="button"><i class="fas fa-search"></i></button>
            </div>
            <div class="overflow-auto pe-1" id="sidebarProductContainer" style="max-height: calc(100vh - 120px);">
                @if(isset($recentProducts) && count($recentProducts) > 0)
                    @foreach($recentProducts as $prod)
                        <div class="pos-product-card">
                            <div class="pos-product-img">
                                <i class="fas fa-box text-secondary fs-5"></i>
                            </div>
                            <div class="pos-product-info">
                                <div class="pos-product-name" title="{{ $prod->item_name }}">{{ $prod->item_name }}</div>
                                <div class="pos-product-sub">
                                    <span class="badge-stock-green">{{ $prod->total_pieces ?? 0 }} Pcs</span> Stock
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <div class="pos-product-price">{{ number_format($prod->retail_price ?? 0, 2) }}</div>
                                <button type="button" class="pos-product-add-btn add-product-direct-btn" data-id="{{ $prod->id }}" title="Add to Grid"><i class="fas fa-plus"></i></button>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>

    <!-- Add Customer Modal -->
    <div class="modal fade" id="addCustomerModal" tabindex="-1" aria-labelledby="addCustomerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow border-0" style="border-radius: 12px; overflow: hidden;">
                <div class="modal-header bg-primary text-white py-2 px-3">
                    <h6 class="modal-title fw-bold" id="addCustomerModalLabel">
                        <i class="fas fa-user-plus me-1"></i> Quick Add Customer
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3">
                    <form id="ajaxAddCustomerForm">
                        @csrf
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small mb-1">Customer Type <span class="text-danger">*</span></label>
                                <select class="form-select form-select-sm" name="customer_type" id="ajax_customer_type" required>
                                    @foreach(\App\Models\CustomerType::orderBy('name')->get() as $type)
                                        <option value="{{ $type->name }}" {{ $type->name === 'Main Customer' ? 'selected' : '' }}>{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small mb-1">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" name="customer_name" id="ajax_customer_name" required placeholder="Enter customer name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small mb-1">Mobile / Phone</label>
                                <input type="text" class="form-control form-control-sm" name="mobile" placeholder="0300-1234567">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small mb-1">Opening Balance (Rs)</label>
                                <input type="number" step="0.01" class="form-control form-control-sm" name="opening_balance" value="0">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold small mb-1">Address / City</label>
                                <input type="text" class="form-control form-control-sm" name="address" placeholder="Enter address or city">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer py-2 px-3 bg-light border-top-0 d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-sm btn-primary fw-bold" id="btnSaveAjaxCustomer">
                        <i class="fas fa-check me-1"></i> Save & Select
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== QUICK ADD PRODUCT MODAL ===== --}}
<!-- <div class="modal fade" id="quickAddProductModal" tabindex="-1" aria-labelledby="quickAddProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light border-bottom-0 pb-2">
                <h5 class="modal-title fw-bold" id="quickAddProductModalLabel">
                    <i class="fa fa-plus-circle text-primary me-2"></i>Quick Add Product
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="quickAddProductForm">
                @csrf
                <div class="modal-body pt-2">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold small text-muted">Product Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="product_name" required placeholder="Enter product name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Category <span class="text-danger">*</span></label>
                            <select class="form-select" name="category_id" id="qap_category" required>
                                <option value="">Select Category</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Sub Category</label>
                            <select class="form-select" name="sub_category_id" id="qap_subcategory">
                                <option value="">Select Sub Category</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Brand <span class="text-danger">*</span></label>
                            <select class="form-select" name="brand_id" id="qap_brand" required>
                                <option value="">Select Brand</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Model / Series</label>
                            <input type="text" class="form-control" name="model" placeholder="Optional">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Size Mode <span class="text-danger">*</span></label>
                            <select class="form-select" name="size_mode" id="qap_size_mode" required>
                                <option value="by_cartons" selected>By Cartons</option>
                                <option value="by_pieces">By Pieces</option>
                            </select>
                        </div>
                        <div class="col-md-6" id="qap_ppb_wrap">
                            <label class="form-label fw-bold small text-muted">Pieces Per Box</label>
                            <input type="number" class="form-control" name="pieces_per_box" id="qap_ppb" value="1" min="1" placeholder="e.g. 12">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Low Stock (Cartons)</label>
                            <input type="number" class="form-control" name="alert_carton_quantity" min="0" placeholder="e.g. 5">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Purchase Price /pc</label>
                            <input type="number" step="0.01" class="form-control" name="purchase_price_per_piece" value="0" placeholder="0.00">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Sale Price /pc</label>
                            <input type="number" step="0.01" class="form-control" name="sale_price_per_box" value="0" placeholder="0.00">
                        </div>
                    </div>
                    {{-- Hidden defaults for validation --}}
                    <input type="hidden" name="boxes_quantity" value="0">
                    <input type="hidden" name="loose_pieces" value="0">
                    <input type="hidden" name="piece_quantity" value="0">
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold" id="btnQuickSaveProduct">
                        <i class="fa fa-save me-1"></i>Save Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
-->

    {{-- Quick Add Product Modal --}}
    @include('admin_panel.partials.quick_add_product_modal')
@endsection

@section('js')
    @include('admin_panel.sale.scripts.shared_logic')

    <script>
        $(document).ready(function() {
            // --- Initial Setup ---
            $('#salesTableBody tr').each(function() {
                initProductSelect2($(this).find('.product'));
            });
            if ($('#salesTableBody tr').length === 0) {
                addNewRow();
            }
            updateGrandTotals();
            refreshPostedState();

            // --- Check if URL is for Booking Flow ---
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('type') === 'booking') {
                $('.header-text').html('<i class="fas fa-bookmark text-primary me-2"></i>Add Booking');
                $('#action').val('booking');
                $('#btnPosted').addClass('d-none');
                $('#btnHeaderPosted').addClass('d-none');
            }

            // ============================================================
            // CUSTOMER SELECT2 AJAX SEARCH (Name or Code)
            // ============================================================
            function getPartyType() {
                return $('#partyTypeSelect').val() || 'Main Customer';
            }

            $('#customerSelect').select2({
                placeholder: 'Search by Name or Code...',
                allowClear: true,
                width: '100%',
                minimumInputLength: 0,
                ajax: {
                    url: '{{ route('salecustomers.index') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            type: getPartyType(),
                            search: params.term || ''
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.map(function(c) {
                                return {
                                    id: c.id,
                                    text: (c.customer_id || '') + ' — ' + c.customer_name,
                                    customer: c
                                };
                            })
                        };
                    },
                    cache: false
                },
                templateResult: function(item) {
                    if (item.loading) return item.text;
                    if (!item.customer) return item.text;
                    const c = item.customer;
                    return $(`<div>
                        <strong>${c.customer_name}</strong>
                        <small class="text-muted ms-2">${c.customer_id || ''}</small>
                        ${c.mobile ? '<br><small class="text-muted">' + c.mobile + '</small>' : ''}
                    </div>`);
                },
                templateSelection: function(item) {
                    if (!item.customer) return item.text;
                    return item.customer.customer_id + ' — ' + item.customer.customer_name;
                }
            });

            // Set initial visibility state of Customer Select / Walk-in input
            $('#partyTypeSelect').trigger('change');

            // Party type change → reset customer
            $(document).on('change', '#partyTypeSelect', function() {
                $('#customerSelect').val(null).trigger('change');
                clearCustomerInfo();
            });

            // Customer selected → load details
            $('#customerSelect').on('select2:select', function(e) {
                const id = e.params.data.id;
                if (!id) return;

                $.get("{{ url('sale/customers') }}/" + id + "?t=" + new Date().getTime(), function(d) {
                    // Fill hidden fields
                    $('#address').val(d.address || '');
                    $('#tel').val(d.mobile || '');
                    const prev = parseFloat(d.previous_balance || 0);
                    const range = parseFloat(d.balance_range || 0);
                    $('#previousBalance').val(prev.toFixed(2));
                    $('#rangeBalance').val(range.toFixed(2));

                    // Fill info card
                    $('#ci_code').text(d.customer_id || '—');
                    $('#ci_name').text(d.customer_name || '—');
                    $('#ci_mobile').text(d.mobile || '—');
                    $('#ci_address').text(d.address || '—');
                    $('#ci_prev_bal').text(prev.toFixed(2));
                    $('#ci_range_bal').text(range.toFixed(2));
                    $('#customerInfoCard').removeClass('d-none');

                    // Auto-fill Sales Officer if customer has one
                    if (d.sales_officer_id) {
                        $('#salesOfficerSelect').val(d.sales_officer_id);
                    }

                    if (typeof updateGrandTotals === 'function') updateGrandTotals();
                }).fail(function() {
                    showAlert('error', 'Failed to load customer details');
                });
            });

            // Customer cleared
            $('#customerSelect').on('select2:clear', function() {
                clearCustomerInfo();
                if (typeof updateGrandTotals === 'function') updateGrandTotals();
            });

            function clearCustomerInfo() {
                $('#address, #tel').val('');
                $('#previousBalance, #rangeBalance').val('0');
                $('#ci_code, #ci_name, #ci_mobile, #ci_address').text('—');
                $('#ci_prev_bal, #ci_range_bal').text('0.00');
                $('#customerInfoCard').addClass('d-none');
                $('#salesOfficerSelect').val('');
            }

            $('#clearCustomerData').on('click', function() {
                $('#customerSelect').val(null).trigger('change');
                clearCustomerInfo();
                if (typeof updateGrandTotals === 'function') updateGrandTotals();
            });

            $('#btnPrint').on('click', function() {
                ensureSaved().then(id => window.open('{{ url('sales') }}/' + id + '/invoice', '_blank'));
            });
            $('#btnEstimate').on('click', function() {
                ensureSaved().then(id => window.open('{{ url('sales') }}/' + id + '/invoice?type=estimate', '_blank'));
            });
            $('#btnPrint2').on('click', function() {
                ensureSaved().then(id => window.open('{{ url('sales') }}/' + id + '/recepit', '_blank'));
            });
            $('#btnDcThermal').on('click', function() {
                ensureSaved().then(id => window.open('{{ url('sales') }}/' + id + '/dc-thermal', '_blank'));
            });

            // Open Customer Modal
            $(document).on('click', '#btnOpenAddCustomerModal', function(e) {
                e.preventDefault();
                $('#addCustomerModal').modal('show');
                setTimeout(function() {
                    $('#ajax_customer_name').focus();
                }, 350);
            });

            // AJAX Customer Submit
            $('#btnSaveAjaxCustomer').on('click', function() {
                let form = $('#ajaxAddCustomerForm');
                if (!form[0].checkValidity()) {
                    form[0].reportValidity();
                    return;
                }
                
                let btn = $(this);
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Saving...');
                
                $.ajax({
                    url: '{{ route('customers.store') }}',
                    type: 'POST',
                    data: form.serialize(),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(res) {
                        btn.prop('disabled', false).html('<i class="fas fa-check me-1"></i> Save & Select');
                        if (res.success && res.customer) {
                            $('#addCustomerModal').modal('hide');
                            form[0].reset();
                            
                            // Auto select new customer
                            let displayText = (res.customer.customer_id ? res.customer.customer_id + ' — ' : '') + res.customer.customer_name;
                            let newOption = new Option(displayText, res.customer.id, true, true);
                            $('#customerSelect').append(newOption).trigger('change');
                            
                            // trigger select2 API selection to load customer details like Prev Bal
                            $('#customerSelect').trigger({
                                type: 'select2:select',
                                params: {
                                    data: {
                                        id: res.customer.id,
                                        text: displayText,
                                        previous_balance: res.customer.opening_balance || 0
                                    }
                                }
                            });
                            
                            if (typeof showAlert === 'function') {
                                showAlert('success', 'Customer ' + res.customer.customer_name + ' added successfully!');
                            }
                        } else {
                            alert('Customer could not be created.');
                        }
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).html('<i class="fas fa-check me-1"></i> Save & Select');
                        let errMsg = 'Error adding customer. Check inputs.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errMsg = xhr.responseJSON.message;
                        }
                        if (typeof showAlert === 'function') {
                            showAlert('danger', errMsg);
                        } else {
                            alert(errMsg);
                        }
                    }
                });
            });

            // ══════════════════════════════════════════════════════════════
            // DYNAMIC INVOICE SERIES & PREFIX GENERATOR LOGIC (INSTANT 0ms)
            // ══════════════════════════════════════════════════════════════
            let currentInvoicePrefix = "{{ $activePrefix ?? 'INV' }}";

            function fetchNextInvoiceNo(prefix) {
                $('#iconRefreshInvoice').addClass('fa-spin');
                $.ajax({
                    url: "{{ route('invoice_series.generate_no') }}",
                    type: "GET",
                    data: { prefix: prefix },
                    success: function(res) {
                        $('#iconRefreshInvoice').removeClass('fa-spin');
                        if (res.invoice_no) {
                            $('#inputInvoiceNo').val(res.invoice_no);
                        }
                    },
                    error: function() {
                        $('#iconRefreshInvoice').removeClass('fa-spin');
                    }
                });
            }

            // Prefix Selection Handler
            $(document).on('click', '#dropdownInvoiceSeriesList a[data-prefix]', function(e) {
                e.preventDefault();
                let prefix = $(this).data('prefix');
                if (!prefix) return;

                currentInvoicePrefix = prefix;
                $('#activePrefixLabel').text(prefix);

                // Update active highlight in dropdown instantly
                $('#dropdownInvoiceSeriesList a[data-prefix]').removeClass('text-success active bg-light').find('i.fa-check').remove();
                $(this).addClass('text-success active bg-light').prepend('<i class="fas fa-check text-success me-1"></i>');

                fetchNextInvoiceNo(prefix);
            });

            // Refresh Invoice No Handler
            $(document).on('click', '#btnRefreshInvoiceNo', function() {
                fetchNextInvoiceNo(currentInvoicePrefix);
            });

            // Open Add Series Modal
            $(document).on('click', '#btnOpenAddSeriesModal', function(e) {
                e.preventDefault();
                $('#modalAddInvoiceSeries').modal('show');
            });

            // Submit Add Series Form via AJAX
            $('#formAddInvoiceSeries').on('submit', function(e) {
                e.preventDefault();
                let formData = $(this).serialize();
                let btn = $('#btnSaveSeries');

                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Saving…');

                $.ajax({
                    url: "{{ route('invoice_series.store') }}",
                    type: "POST",
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(res) {
                        btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save &amp; Select');
                        
                        if (res.success) {
                            $('#modalAddInvoiceSeries').modal('hide');
                            $('#formAddInvoiceSeries')[0].reset();

                            currentInvoicePrefix = res.prefix;
                            $('#activePrefixLabel').text(res.prefix);
                            $('#inputInvoiceNo').val(res.invoice_no);

                            // Update or insert item in dropdown list instantly
                            let existingItem = $(`#dropdownInvoiceSeriesList a[data-prefix="${res.prefix}"]`);
                            if (existingItem.length > 0) {
                                existingItem.data('next', res.series.next_number).data('padding', res.series.padding);
                            } else {
                                let newItemHtml = `<li>
                                    <a class="dropdown-item fw-bold text-success active bg-light" href="#" data-prefix="${res.prefix}" data-next="${res.series.next_number}" data-padding="${res.series.padding}">
                                        <i class="fas fa-check text-success me-1"></i> ${res.prefix} <span class="text-muted small font-monospace">(${res.series.padding}d)</span>
                                    </a>
                                </li>`;
                                $('#dropdownInvoiceSeriesList li:has(hr)').before(newItemHtml);
                            }

                            // Update active highlight state
                            $('#dropdownInvoiceSeriesList a[data-prefix]').removeClass('text-success active bg-light').find('i.fa-check').remove();
                            $(`#dropdownInvoiceSeriesList a[data-prefix="${res.prefix}"]`).addClass('text-success active bg-light').prepend('<i class="fas fa-check text-success me-1"></i>');

                            if (typeof showAlert === 'function') {
                                showAlert('success', res.message);
                            } else if (typeof Swal !== 'undefined') {
                                Swal.fire({ icon: 'success', title: 'Saved!', text: res.message, timer: 1500, showConfirmButton: false });
                            } else {
                                alert(res.message);
                            }
                        }
                    },
                    error: function(err) {
                        btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save &amp; Select');
                        let msg = 'Error saving series. Please check form inputs.';
                        if (err.responseJSON && err.responseJSON.errors) {
                            msg = Object.values(err.responseJSON.errors).flat().join('\n');
                        } else if (err.responseJSON && err.responseJSON.message) {
                            msg = err.responseJSON.message;
                        }
                        
                        if (typeof showAlert === 'function') {
                            showAlert('error', msg);
                        } else if (typeof Swal !== 'undefined') {
                            Swal.fire('Error', msg, 'error');
                        } else {
                            alert(msg);
                        }
                    }
                });
            });
        });
    </script>

    <!-- Modal: Add / Manage Invoice Series -->
    <div class="modal fade" id="modalAddInvoiceSeries" tabindex="-1" aria-labelledby="modalAddInvoiceSeriesLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-bottom bg-light px-3 py-2">
                    <h6 class="modal-title fw-bold text-dark mb-0" id="modalAddInvoiceSeriesLabel">
                        <i class="fas fa-barcode text-success me-1"></i> Add Invoice Series
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formAddInvoiceSeries">
                    @csrf
                    <div class="modal-body p-3">
                        <div class="mb-2">
                            <label class="form-label small fw-bold text-secondary mb-1">Prefix (e.g., SQ, POS, INV)</label>
                            <input type="text" name="prefix" id="seriesPrefixInput" class="form-control form-control-sm text-uppercase fw-bold" placeholder="e.g. SQ" required style="letter-spacing: 1px;">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-bold text-secondary mb-1">Starting Number (Counter)</label>
                            <input type="number" name="next_number" id="seriesNextNumInput" class="form-control form-control-sm fw-bold text-primary" placeholder="e.g. 50" min="1" value="50" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-bold text-secondary mb-1">Padding Length (Zero Digits)</label>
                            <select name="padding" id="seriesPaddingSelect" class="form-select form-select-sm fw-bold">
                                <option value="4">4 Digits (e.g., 0050)</option>
                                <option value="6" selected>6 Digits (e.g., 000050)</option>
                                <option value="8">8 Digits (e.g., 00000050)</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top p-2 px-3">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success btn-sm fw-bold px-3" id="btnSaveSeries">
                            <i class="fas fa-save me-1"></i> Save &amp; Select
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
