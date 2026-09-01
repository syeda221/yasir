@extends('admin_panel.layout.app')

@section('content')
    {{--
        SUCCESS: Horizontal 2-Column Compact Layout Redesign
        Features:
        - Left Column: Product Identity & Spec inputs
        - Right Column: live preview stats panel and submit actions
        - Compact size & low scroll footprint.
    --}}

    {{-- External Resources --}}
     <link href="{{ asset('assets/vendors/bootstrap5/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendors/select2/css/select2.min.css') }}" rel="stylesheet" />
    {{-- line-awesome replaced by Font Awesome 6 (local) --}}
    <link rel="stylesheet" href="{{ asset('assets/fonts/inter/inter.css') }}">

    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --primary-light: #eef2ff;
            --bg-body: #f1f5f9;
            --bg-card: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --radius-md: 10px;
            --radius-lg: 16px;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
        }

        .page-container {
            max-width: 1350px;
            margin: 0 auto;
            padding: 15px;
        }

        .section-card {
            background: var(--bg-card);
            border-radius: var(--radius-lg);
            box-shadow: 0 1px 3px rgba(0,0,0,0.05), 0 1px 2px rgba(0,0,0,0.06);
            border: 1px solid var(--border-color);
            margin-bottom: 20px;
        }

        .card-header-pro {
            padding: 12px 20px;
            border-bottom: 1px solid var(--border-color);
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-title-pro {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--text-main);
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0;
        }

        .card-body-pro {
            padding: 20px;
        }

        /* --- Form Styling --- */
        .form-label-pro {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 4px;
            letter-spacing: 0.02em;
        }

        .form-control-pro {
            display: block;
            width: 100%;
            padding: 8px 12px;
            font-size: 0.88rem;
            font-weight: 500;
            color: var(--text-main);
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        .form-control-pro:focus {
            border-color: var(--primary);
            outline: 0;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .form-select-pro {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 16px 12px;
        }

        /* --- Image Uploader --- */
        .img-uploader {
            width: 100%;
            border: 2px dashed #cbd5e1;
            border-radius: var(--radius-md);
            background: #f8fafc;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: all 0.2s;
        }

        .img-uploader:hover {
            border-color: var(--primary);
            background: var(--primary-light);
        }

        .img-uploader img {
            max-width: 100%;
            object-fit: contain;
            padding: 5px;
        }

        /* Stats Box */
        .stats-summary-box {
            background: #f8fafc;
            border-radius: var(--radius-md);
            padding: 16px;
            border: 1px solid var(--border-color);
        }
        .stat-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .stat-label { font-size: 0.8rem; color: var(--text-muted); font-weight: 600; }
        .stat-value { font-size: 1.4rem; font-weight: 800; color: var(--text-main); }

        .total-value-display {
            background: #0f172a;
            color: #fff;
            padding: 20px;
            border-radius: var(--radius-md);
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        /* Clean modern styling for variants table */
        #variantsTable input[type=number]::-webkit-inner-spin-button, 
        #variantsTable input[type=number]::-webkit-outer-spin-button { 
            -webkit-appearance: none; 
            margin: 0; 
        }
        #variantsTable input[type=number] {
            -moz-appearance: textfield;
        }
        #variantsTable .form-control-pro, 
        #variantsTable .form-select {
            height: 30px;
            padding: 2px 6px;
            font-size: 12px;
            border-radius: 4px;
            border: 1px solid #ced4da;
            background-color: #ffffff;
            box-shadow: none;
        }
        #variantsTable .form-control-pro:focus, 
        #variantsTable .form-select:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.15);
        }
        #variantsTable .input-group-sm > .form-control-pro {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }
        #variantsTable .input-group-sm > .input-group-text {
            height: 30px;
            font-size: 11px;
            padding: 0 5px;
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
            border-color: #ced4da;
            background-color: #f8fafc;
            color: #64748b;
        }
        #variantsTable th {
            background-color: #f1f5f9 !important;
            color: #334155;
            font-weight: 600;
            font-size: 11px !important;
            padding: 6px 4px !important;
            vertical-align: middle;
            white-space: nowrap;
        }
        #variantsTable td {
            padding: 4px 3px !important;
            vertical-align: middle;
        }

        /* =============================================
           MOBILE RESPONSIVE STYLES
           ============================================= */
        :root {
            --mob-primary: #5B4CF7;
            --mob-primary-light: #EEF2FF;
            --mob-primary-dark: #4338ca;
            --mob-success: #10b981;
            --mob-danger: #ef4444;
            --mob-border: #e2e8f0;
            --mob-radius: 12px;
            --mob-shadow: 0 2px 8px rgba(91,76,247,0.08), 0 1px 3px rgba(0,0,0,0.06);
        }

        /* --- Mobile sticky action bar --- */
        .mobile-action-bar {
            display: none;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 1050;
            background: #fff;
            border-top: 1px solid var(--mob-border);
            padding: 12px 16px;
            box-shadow: 0 -4px 20px rgba(0,0,0,0.1);
            gap: 10px;
        }

        /* --- Mobile Variant Cards --- */
        #mobileVariantsContainer {
            display: none;
        }

        .mob-variant-card {
            background: #fff;
            border-radius: var(--mob-radius);
            border: 1.5px solid var(--mob-border);
            margin-bottom: 10px;
            box-shadow: var(--mob-shadow);
            overflow: hidden;
            transition: all 0.2s ease;
        }

        .mob-variant-card.is-base {
            border-color: var(--mob-primary);
            box-shadow: 0 0 0 2px rgba(91,76,247,0.12), var(--mob-shadow);
        }

        .mob-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 14px;
            background: #f8fafc;
            cursor: pointer;
            user-select: none;
            transition: background 0.15s;
            border-bottom: 1px solid transparent;
        }

        .mob-card-header:active { background: #f1f5f9; }

        .mob-variant-card.is-open .mob-card-header {
            background: var(--mob-primary-light);
            border-bottom-color: var(--mob-border);
        }

        .mob-card-title {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 700;
            font-size: 0.88rem;
            color: #1e293b;
            flex: 1;
            min-width: 0;
        }

        .mob-card-title span {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .mob-base-badge {
            font-size: 9px;
            font-weight: 800;
            letter-spacing: 0.05em;
            background: var(--mob-primary);
            color: #fff;
            padding: 2px 7px;
            border-radius: 20px;
            text-transform: uppercase;
            flex-shrink: 0;
        }

        .mob-chevron {
            color: #94a3b8;
            transition: transform 0.25s cubic-bezier(0.4,0,0.2,1);
            flex-shrink: 0;
        }

        .mob-variant-card.is-open .mob-chevron {
            transform: rotate(180deg);
            color: var(--mob-primary);
        }

        .mob-card-body {
            display: none;
            padding: 14px;
            animation: slideDown 0.2s ease;
        }

        .mob-variant-card.is-open .mob-card-body {
            display: block;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-6px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* --- Mobile Form Fields --- */
        .mob-field-group {
            margin-bottom: 10px;
        }

        .mob-label {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .mob-label .req { color: #ef4444; }

        .mob-input {
            width: 100%;
            height: 42px;
            padding: 0 12px;
            font-size: 14px;
            font-weight: 500;
            color: #0f172a;
            background: #fff;
            border: 1.5px solid var(--mob-border);
            border-radius: 10px;
            outline: none;
            transition: border-color 0.15s, box-shadow 0.15s;
            box-sizing: border-box;
        }

        .mob-input:focus {
            border-color: var(--mob-primary);
            box-shadow: 0 0 0 3px rgba(91,76,247,0.12);
        }

        .mob-input.auto-field {
            background: #f8f9ff;
            color: var(--mob-primary);
            font-weight: 700;
        }

        .mob-input.conv-field {
            background: #f0fdf4;
            color: #059669;
            font-weight: 700;
            border-color: #10b981;
        }

        .mob-input.conv-field:focus {
            border-color: #059669;
            box-shadow: 0 0 0 3px rgba(16,185,129,0.12);
        }

        .mob-select {
            width: 100%;
            height: 42px;
            padding: 0 36px 0 12px;
            font-size: 14px;
            font-weight: 500;
            color: #0f172a;
            background: #fff url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%235B4CF7' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e") no-repeat right 12px center;
            background-size: 14px;
            border: 1.5px solid var(--mob-border);
            border-radius: 10px;
            outline: none;
            appearance: none;
            -webkit-appearance: none;
            transition: border-color 0.15s, box-shadow 0.15s;
        }

        .mob-select:focus {
            border-color: var(--mob-primary);
            box-shadow: 0 0 0 3px rgba(91,76,247,0.12);
        }

        /* inline g suffix */
        .mob-suffix-wrap {
            position: relative;
        }
        .mob-suffix-wrap .mob-input { padding-right: 30px; }
        .mob-suffix-wrap .mob-suffix {
            position: absolute;
            right: 11px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 11px;
            font-weight: 700;
            color: #059669;
            pointer-events: none;
        }

        /* row dividers in card */
        .mob-section-divider {
            height: 1px;
            background: var(--mob-border);
            margin: 10px 0;
        }

        .mob-section-label {
            font-size: 9px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--mob-primary);
            margin-bottom: 8px;
        }

        /* Delete button inside card */
        .mob-delete-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            width: 100%;
            padding: 10px;
            background: #fff5f5;
            border: 1.5px solid #fecaca;
            border-radius: 10px;
            color: #ef4444;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.15s;
            margin-top: 6px;
        }

        .mob-delete-btn:active { background: #fee2e2; }

        /* Add Variant Button mobile */
        .mob-add-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 12px;
            background: var(--mob-primary-light);
            border: 2px dashed var(--mob-primary);
            border-radius: var(--mob-radius);
            color: var(--mob-primary);
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.15s;
            margin-top: 4px;
        }

        .mob-add-btn:active { background: #e0e7ff; }

        /* Mobile Info Section */
        .mob-info-section {
            display: none;
        }

        /* Responsive breakpoints */
        @media (max-width: 767px) {
            body { padding-bottom: 80px; }

            .page-container { padding: 12px; }

            /* Hide desktop table + show mobile cards */
            #variantsContainer { display: none !important; }
            #mobileVariantsContainer { display: block; }
            .mobile-action-bar { display: flex; }
            /* Hide desktop action buttons row */
            .desktop-action-bar { display: none !important; }
            /* Mobile info section */
            .mob-info-section { display: block; }

            /* Product identity card responsive */
            .card-body-pro { padding: 14px; }
            .card-header-pro { padding: 10px 14px; }
        }

        @media (min-width: 768px) and (max-width: 991px) {
            /* Tablet: 2-col inputs */
            .card-body-pro .col-md-3 { flex: 0 0 50%; max-width: 50%; }
        }
    </style>

    <div class="page-container">

        {{-- Page Title --}}
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('product') }}" class="btn btn-white border shadow-sm rounded-circle p-0" style="width: 36px; height: 36px; display: grid; place-items: center;">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h5 class="fw-bold mb-0 text-dark">Create Product</h5>
                    <small class="text-muted" style="font-size:0.8rem;">Add carton or piece based items to inventory</small>
                </div>
            </div>
        </div>

        <form id="productForm" action="{{ route('store-product') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="row g-3">

                {{-- MAIN COLUMN: Full Width --}}
                <div class="col-12">

                    {{-- CARD 1: Identity & Categorization --}}
                    <div class="section-card">
                        <div class="card-header-pro">
                            <h5 class="card-title-pro"><i class="fas fa-tag text-primary"></i> Product Identity</h5>
                        </div>
                        <div class="card-body-pro">
                            <div class="row g-3">

                                {{-- Sub-grid for left content, image on the right --}}
                                <div class="col-md-9">
                                    <div class="row g-3">
                                        <div class="col-md-12">
                                            <label class="form-label-pro">Product Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control-pro fs-6 fw-bold" name="product_name" required placeholder="e.g. Ceramic Floor Tile 60x60">
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label-pro">Category <span class="text-danger">*</span></label>
                                            <div class="d-flex gap-1">
                                                <select class="form-select form-control-pro form-select-pro" id="category-dropdown" name="category_id" required>
                                                    <option value="">Select...</option>
                                                    @foreach ($categories as $cat)
                                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                                    @endforeach
                                                </select>
                                                <button type="button" class="btn btn-light border px-2 shadow-sm" data-toggle="modal" data-target="#categoryModal">+</button>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label-pro">Sub Category</label>
                                            <div class="d-flex gap-1">
                                                <select class="form-select form-control-pro form-select-pro" id="subcategory-dropdown" name="sub_category_id">
                                                    <option value="">Select...</option>
                                                </select>
                                                <button type="button" class="btn btn-light border px-2 shadow-sm" data-toggle="modal" data-target="#subcategoryModal">+</button>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label-pro">Brand</label>
                                            <div class="d-flex gap-1">
                                                <select class="form-select form-control-pro form-select-pro" name="brand_id" required>
                                                    <option value="">Select...</option>
                                                    @foreach ($brands as $brand)
                                                        <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                                    @endforeach
                                                </select>
                                                <button type="button" class="btn btn-light border px-2 shadow-sm" data-toggle="modal" data-target="#brandModal">+</button>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label-pro">Unit</label>
                                            <select class="form-select form-control-pro form-select-pro fw-bold" name="size_mode" id="unit-dropdown">
                                                <option value="by_pieces">Pcs</option>
                                                <option value="by_cartons">Carton</option>
                                                <option value="by_meter">Meter</option>
                                                <option value="by_feet">Ft (Feet)</option>
                                                <option value="by_kg">Kg</option>
                                                <option value="by_gm">Gm</option>
                                                <option value="by_ton">Ton</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                {{-- Image Uploader side-by-side (Right) --}}
                                <div class="col-md-3">
                                    <label class="form-label-pro">Product Image</label>
                                    <input type="file" id="imageInput" name="image" class="d-none" accept="image/*">
                                    <div class="img-uploader" style="height: 110px;" onclick="document.getElementById('imageInput').click()">
                                        <button type="button" id="clearImageBtn" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 d-none rounded-circle" style="width:20px;height:20px;padding:0;z-index:10; font-size:12px; line-height:1;">&times;</button>
                                        <img id="preview" class="d-none" style="max-height: 100px;">
                                        <div id="uploadPlaceholder" class="text-center p-2">
                                            <i class="fas fa-camera fs-3 text-primary"></i>
                                            <div class="fw-bold" style="font-size: 11px;">Upload</div>
                                        </div>
                                    </div>
                                </div>



                                <div class="col-12 mt-3 pt-3 border-top">
                                    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                                        <h6 class="form-label-pro text-primary mb-0"><i class="fas fa-cubes me-1"></i>Product Variants & Units</h6>
                                        <button type="button" class="btn btn-sm btn-primary" id="enableVariantsBtn"><i class="fas fa-plus me-1"></i>Add Variant Row</button>
                                    </div>
                                    <div id="variantsContainer">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-sm align-middle mb-1" id="variantsTable">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th class="text-uppercase text-muted p-1" style="min-width: 140px; font-size: 10px;">Variant Name</th>
                                                        <th class="text-uppercase text-muted p-1" style="width: 80px; font-size: 10px;">Size</th>
                                                        <th class="text-uppercase text-muted p-1" style="width: 80px; font-size: 10px;">Color</th>
                                                        <th class="text-uppercase text-muted p-1" style="width: 75px; font-size: 10px;">Unit</th>
                                                        <th class="text-uppercase text-muted p-1 text-center" style="width: 90px; font-size: 10px;">Initial Stock</th>
                                                        <th class="text-uppercase text-muted p-1 text-center conv-col" id="convFactorHeader" style="width: 95px; font-size: 10px;">Pcs / Carton</th>
                                                        <th class="text-uppercase text-muted p-1 text-center piece-wt-only-col" style="width: 90px; font-size: 10px;">Piece Wt (g)</th>
                                                        <th class="text-uppercase text-muted p-1" style="width: 90px; font-size: 10px;">Sale Price</th>
                                                        <th class="text-uppercase text-muted p-1" style="width: 90px; font-size: 10px;">Wholesale</th>
                                                        <th class="text-uppercase text-muted p-1" style="width: 90px; font-size: 10px;">Purch Price</th>
                                                        <th class="text-uppercase text-muted p-1" style="width: 55px; font-size: 10px;">Alert</th>
                                                        <th class="text-uppercase text-muted p-1" style="width: 100px; font-size: 10px;">Barcode</th>
                                                        <th class="text-uppercase text-muted p-1 text-center" style="width: 50px; font-size: 10px;">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="variantsBody">
                                                    <!-- rows injected by JS -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    
                                    {{-- MOBILE VARIANTS CONTAINER --}}
                                    <div id="mobileVariantsContainer" style="display: none;">
                                        <div id="mobileVariantsBody" class="d-flex flex-column gap-2 mb-2"></div>
                                        <button type="button" class="mob-add-btn" onclick="mobileAddVariant()">
                                            <i class="fas fa-plus"></i> Add Variant
                                        </button>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>



                    {{-- INLINE ACTIONS ROW (desktop only) --}}
                    <div class="d-flex justify-content-end align-items-center bg-white p-3 rounded shadow-sm border mb-4 gap-2 desktop-action-bar">
                        <a href="{{ route('product') }}" class="btn btn-outline-secondary px-4 py-2" style="border-radius: var(--radius-md); font-size: 0.9rem;">Cancel</a>
                        <button type="submit" id="desktopSubmitBtn" class="btn btn-primary px-5 py-2 fw-bold" style="background: var(--primary); border: none; border-radius: var(--radius-md); font-size: 0.9rem;">
                            <i class="fas fa-check-circle me-1"></i> SAVE PRODUCT
                        </button>
                    </div>



            {{-- HIDDEN FORM CONTROLS FOR BACKEND VALIDATION COMPATIBILITY --}}
            <div style="display:none !important;">
                <input type="number" name="height" id="height" step="0.01" value="0">
                <input type="number" name="width" id="width" step="0.01" value="0">
                <input type="number" name="price_per_m2" id="price_per_m2" step="0.01" value="0">
                <input type="number" name="purchase_price_per_m2" id="purchase_price_per_m2" step="0.01" value="0">

                <input type="number" name="piece_quantity" id="piece_quantity" value="0">
                <input type="number" name="pieces_per_box" id="pieces_per_box" value="1">
                <input type="number" name="boxes_quantity" id="boxes_quantity" value="0">
                <input type="number" name="loose_pieces" id="loose_pieces" value="0">
                <input type="number" name="sale_price_per_box" id="sale_price_per_box" step="0.01" value="0">
                <input type="number" name="wholesale_price" id="wholesale_price" step="0.01" value="0">
                <input type="number" name="weight_per_piece" id="weight_per_piece" step="0.0001" value="0">
                <input type="number" name="purchase_price_per_piece" id="purchase_price_per_piece" step="0.01" value="0">
                <input type="number" name="purchase_price_per_box" id="purchase_price_per_box" step="0.01" value="0">
                <input type="number" name="alert_carton_quantity" id="alert_carton_quantity" value="0">
            </div>

        </form>

        {{-- Modals --}}
        <div id="categoryModal" class="modal fade" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content border-0 shadow-lg" style="border-radius: var(--radius-md);">
                    <form action="{{ route('store.category') }}" method="POST">
                        @csrf
                        <div class="modal-header border-0 pb-0">
                            <h6 class="modal-title fw-bold">New Category</h6>
                            <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="page" value="product_page">
                            <div class="mb-3">
                                <label class="form-label-pro">Category Name</label>
                                <input type="text" name="name" class="form-control-pro" required placeholder="e.g. Ceramics">
                            </div>
                            <button type="submit" class="btn btn-primary w-100 rounded-pill">Create Category</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div id="subcategoryModal" class="modal fade" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content border-0 shadow-lg" style="border-radius: var(--radius-md);">
                    <form action="{{ route('store.subcategory') }}" method="POST">
                        @csrf
                        <div class="modal-header border-0 pb-0">
                            <h6 class="modal-title fw-bold">New Subcategory</h6>
                            <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="page" value="product_page">
                            <div class="mb-3">
                                <label class="form-label-pro">Parent Category</label>
                                <select name="category_id" class="form-select form-control-pro">
                                    @foreach ($categories as $c)
                                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label-pro">Name</label>
                                <input type="text" name="name" class="form-control-pro" required placeholder="e.g. Floor Tiles">
                            </div>
                            <button type="submit" class="btn btn-primary w-100 rounded-pill">Create Subcategory</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Brand Modal --}}
        <div id="brandModal" class="modal fade" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content border-0 shadow-lg" style="border-radius: var(--radius-md);">
                    <form action="{{ route('store.Brand') }}" method="POST">
                        @csrf
                        <div class="modal-header border-0 pb-0">
                            <h6 class="modal-title fw-bold">New Brand</h6>
                            <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="page" value="product_page">
                            <div class="mb-3">
                                <label class="form-label-pro">Brand Name</label>
                                <input type="text" name="name" class="form-control-pro" required placeholder="e.g. Johnson">
                            </div>
                            <button type="submit" class="btn btn-primary w-100 rounded-pill">Create Brand</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>{{-- end page-container --}}

    {{-- MOBILE STICKY ACTION BAR (shown only on mobile via CSS) --}}
    <div class="mobile-action-bar">
        <a href="{{ route('product') }}" class="btn btn-outline-secondary fw-semibold" style="flex:1;border-radius:10px;height:46px;display:flex;align-items:center;justify-content:center;font-size:15px;">
            <i class="fas fa-times me-2"></i>Cancel
        </a>
        <button type="button" id="mobileSubmitBtn" onclick="document.getElementById('productForm').requestSubmit()" style="flex:2;background:linear-gradient(135deg,#5B4CF7,#4338ca);border:none;border-radius:10px;height:46px;color:#fff;font-weight:700;font-size:15px;display:flex;align-items:center;justify-content:center;gap:8px;">
            <i class="fas fa-check-circle"></i> Save Product
        </button>
    </div>

@endsection

@section('js')
    <script>
        var variantMode = 'standard';
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('productForm');
            const unitDropdown = document.getElementById('unit-dropdown');

            // Image Handler
            const imgInput = document.getElementById('imageInput');
            const preview = document.getElementById('preview');
            const ph = document.getElementById('uploadPlaceholder');
            const clr = document.getElementById('clearImageBtn');

            imgInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const r = new FileReader();
                    r.onload = (e) => {
                        preview.src = e.target.result;
                        preview.classList.remove('d-none');
                        ph.classList.add('d-none');
                        clr.classList.remove('d-none');
                    };
                    r.readAsDataURL(this.files[0]);
                }
            });

            clr.addEventListener('click', (e) => {
                e.stopPropagation();
                imgInput.value = '';
                preview.classList.add('d-none');
                ph.classList.remove('d-none');
                clr.classList.add('d-none');
            });

            // AJAX Submission
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                // --- Sync Variants data to hidden main fields for backend compatibility ---
                const vStocks = document.querySelectorAll('input[name="variant_stock[]"]');
                const vSale = document.querySelectorAll('input[name="variant_sale_price[]"]');
                const vWholesale = document.querySelectorAll('input[name="variant_wholesale_price[]"]');
                const vWeight = document.querySelectorAll('input[name="variant_weight_per_piece[]"]');
                const vPurch = document.querySelectorAll('input[name="variant_purchase_price[]"]');
                const vAlert = document.querySelectorAll('input[name="variant_alert_qty[]"]');

                let totalStock = 0;
                vStocks.forEach(el => totalStock += (parseFloat(el.value) || 0));

                let firstSale = vSale.length > 0 ? (parseFloat(vSale[0].value) || 0) : 0;
                let firstWholesale = vWholesale.length > 0 ? (parseFloat(vWholesale[0].value) || 0) : 0;
                let firstWeight = vWeight.length > 0 ? (parseFloat(vWeight[0].value) || 0) : 0;
                let firstPurch = vPurch.length > 0 ? (parseFloat(vPurch[0].value) || 0) : 0;
                let firstAlert = vAlert.length > 0 ? (parseFloat(vAlert[0].value) || 0) : 0;

                const setElVal = (id, val) => {
                    const el = document.getElementById(id);
                    if (el) el.value = val;
                };

                const mode = unitDropdown ? unitDropdown.value : 'by_pieces';
                const vConv = document.querySelectorAll('input[name="variant_conv_factor[]"]');
                let firstConv = vConv.length > 0 ? (parseFloat(vConv[0].value) || 1) : 1;
                if (firstConv <= 0) firstConv = 1;

                if(mode === 'by_cartons') {
                    setElVal('boxes_quantity', totalStock);
                    setElVal('pieces_per_box', firstConv);
                    setElVal('loose_pieces', 0);
                    setElVal('piece_quantity', 0);
                    setElVal('sale_price_per_box', (firstSale * firstConv).toFixed(2));
                    setElVal('purchase_price_per_piece', firstPurch);
                    setElVal('purchase_price_per_box', (firstPurch * firstConv).toFixed(2));
                } else {
                    setElVal('piece_quantity', totalStock);
                    setElVal('boxes_quantity', 0);
                    setElVal('pieces_per_box', 1);
                    setElVal('sale_price_per_box', firstSale);
                    setElVal('purchase_price_per_piece', firstPurch);
                    setElVal('purchase_price_per_box', firstPurch);
                }
                setElVal('wholesale_price', firstWholesale);
                setElVal('weight_per_piece', firstWeight);
                setElVal('alert_carton_quantity', firstAlert);
                // ------------------------------------------------------------------------

                const btn = document.querySelector('button[type="submit"]');
                const originalContent = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
                btn.disabled = true;

                const formData = new FormData(form);
                fetch(form.action, {
                    method: 'POST',
                    headers: {'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json'},
                    body: formData
                })
                .then(r => r.json().then(data => ({status: r.status, body: data})))
                .then(({status, body}) => {
                    if (status === 200 || body.status === 'success') {
                         Swal.fire({
                            icon: 'success', title: 'Saved!',
                            text: 'Product created successfully', timer: 1500, showConfirmButton: false
                         }).then(() => window.location.href = "{{ route('product') }}");
                    } else {
                        const msg = body.errors ? Object.values(body.errors).flat().join('<br>') : (body.message || 'Error');
                        Swal.fire({icon: 'error', title: 'Error', html: msg});
                    }
                })
                .catch(err => Swal.fire({icon: 'error', title: 'Error', text: 'Server Error'}))
                .finally(() => {
                    btn.innerHTML = originalContent;
                    btn.disabled = false;
                });
            });

            $('#category-dropdown').on('change', function() {
                var cid = $(this).val();
                if (cid) {
                    $.get('/get-subcategories/' + cid, function(d) {
                        $('#subcategory-dropdown').empty().append('<option value="">Select...</option>');
                        $.each(d, function(_, v) {
                            $('#subcategory-dropdown').append('<option value="' + v.id + '">' + v.name + '</option>');
                        });
                    });
                }
            });

            function handleQuickAdd(modalId, selectSelector) {
                $('#' + modalId + ' form').on('submit', function(e) {
                    e.preventDefault();
                    let form = $(this);
                    let btn = form.find('button[type="submit"]');
                    let originalText = btn.text();
                    btn.text('Saving...').prop('disabled', true);

                    $.ajax({
                        url: form.attr('action'),
                        method: 'POST',
                        data: form.serialize(),
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        success: function(res) {
                            if(res.success) {
                                $(selectSelector).append(new Option(res.name, res.id, true, true)).trigger('change');
                                $('#' + modalId).modal('hide');
                                form[0].reset();
                                Swal.fire({icon: 'success', title: 'Added successfully', toast: true, position: 'top-end', showConfirmButton: false, timer: 1500});
                            }
                        },
                        error: function() {
                            Swal.fire({icon: 'error', title: 'Error', text: 'Something went wrong!'});
                        },
                        complete: function() {
                            btn.text(originalText).prop('disabled', false);
                        }
                    });
                });
            }

            handleQuickAdd('categoryModal', '#category-dropdown, #subcategoryModal select[name="category_id"]');
            handleQuickAdd('subcategoryModal', '#subcategory-dropdown');
            handleQuickAdd('brandModal', 'select[name="brand_id"]');

            const enableVariantsBtn = document.getElementById('enableVariantsBtn');
            const variantsContainer = document.getElementById('variantsContainer');
            const variantsBody = document.getElementById('variantsBody');

            const weightUnits = ['by_kg', 'by_gm', 'by_ton'];
            variantMode = 'standard';
            let manualPrices = {}; // Keep track of manually overridden prices
            let manualNames = {}; // Keep track of manually overridden names

            function isWeightUnit(unit) {
                return weightUnits.includes(unit);
            }

            function updateVariantMode() {
                const currentUnit = unitDropdown ? unitDropdown.value : 'by_pieces';
                const newMode = isWeightUnit(currentUnit) ? 'weight' : 'standard';
                
                if (variantMode !== newMode) {
                    if (variantsBody.children.length > 0) {
                        Swal.fire({
                            title: 'Change Unit?',
                            text: 'This product contains variants. Changing the unit will remove the current variant settings from this form. Do you want to continue?',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Continue',
                            cancelButtonText: 'Cancel'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                variantMode = newMode;
                                variantsBody.innerHTML = '';
                                manualPrices = {};
                                manualNames = {};
                                applyVariantModeUI();
                                if (variantMode === 'weight' && !variantsContainer.classList.contains('d-none')) {
                                    addBaseVariantRow();
                                }
                            } else {
                                // Revert dropdown
                                unitDropdown.value = (variantMode === 'weight') ? 'by_kg' : 'by_pieces';
                            }
                        });
                    } else {
                        variantMode = newMode;
                        applyVariantModeUI();
                    }
                }
            }

            function applyVariantModeUI() {
                const stdCols = document.querySelectorAll('.std-col');
                const weightCols = document.querySelectorAll('.weight-col');
                
                if (variantMode === 'weight') {
                    stdCols.forEach(col => col.classList.add('d-none'));
                    weightCols.forEach(col => col.classList.remove('d-none'));
                } else {
                    stdCols.forEach(col => col.classList.remove('d-none'));
                    weightCols.forEach(col => col.classList.add('d-none'));
                }
                toggleFactorColumns(); // To handle the legacy factor cols if needed
            }

            function generateRandomBarcode() {
                return Math.floor(100000 + Math.random() * 900000).toString();
            }

            // Sync base variant name with product name
            const productNameInput = document.querySelector('input[name="product_name"]');
            if (productNameInput) {
                productNameInput.addEventListener('input', function() {
                    const baseInput = document.querySelector('input.base-name-input');
                    if (baseInput && !manualNames[baseInput.dataset.vid]) {
                        baseInput.value = this.value;
                    }
                });
            }

            function addBaseVariantRow() {
                const tr = document.createElement('tr');
                const productName = productNameInput.value || '';
                const baseUnitName = unitDropdown ? unitDropdown.options[unitDropdown.selectedIndex].text : 'Kg';
                const isCartonMode = unitDropdown && unitDropdown.value === 'by_cartons';
                const vid = 'base_' + Date.now();
                tr.innerHTML = `
                    <td class="p-1">
                        <input type="text" class="form-control-pro form-control-sm base-name-input fw-bold" name="variant_name[]" value="${productName}" placeholder="Name" data-vid="${vid}">
                        <input type="hidden" name="variant_is_base[]" value="1">
                    </td>
                    <td class="p-1"><input type="text" class="form-control-pro form-control-sm" name="variant_size[]" placeholder="Size"></td>
                    <td class="p-1"><input type="text" class="form-control-pro form-control-sm" name="variant_color[]" placeholder="Color"></td>
                    <td class="p-1">
                        <select class="form-select form-select-sm fw-bold text-primary px-1" name="variant_unit[]" style="font-size:11px;">
                            <option value="Carton" ${isCartonMode||baseUnitName.includes('Carton')?'selected':''}>Carton</option>
                            <option value="Pcs" ${(!isCartonMode && (baseUnitName.includes('Pcs')||baseUnitName.includes('Pieces')))?'selected':''}>Pcs</option>
                            <option value="Kg" ${baseUnitName.includes('Kg')?'selected':''}>Kg</option>
                            <option value="Gm" ${baseUnitName.includes('Gm')?'selected':''}>Gm</option>
                            <option value="Ft" ${baseUnitName.includes('Ft')?'selected':''}>Ft</option>
                            <option value="Meter" ${baseUnitName.includes('Meter')?'selected':''}>Mtr</option>
                            <option value="Box">Box</option>
                            <option value="Dozen">Dzn</option>
                        </select>
                    </td>
                    <td class="p-1">
                        <input type="number" class="form-control-pro form-control-sm text-center fw-bold text-primary" name="variant_stock[]" step="any" value="0" placeholder="0" title="${isCartonMode ? 'Initial Stock (Cartons)' : 'Initial Stock'}">
                    </td>
                    <td class="p-0 conv-col">
                        <input type="number" class="form-control-pro form-control-sm conv-factor-input text-center fw-bold ${isCartonMode ? 'text-primary' : ''}" name="variant_conv_factor[]" step="any" value="${isCartonMode ? '0' : '1'}" ${isCartonMode ? '' : 'readonly'} placeholder="0" title="${isCartonMode ? 'Pieces per Carton' : 'Base Conv Factor = 1'}" style="border-radius:0; border:1px solid #dee2e6; height:30px; ${isCartonMode ? 'background:#fff;' : 'background:#f8f8f8;'}">
                    </td>
                    <td class="p-0 piece-wt-only-col">
                        <div style="position:relative;">
                            <input type="number" class="form-control-pro form-control-sm" name="variant_weight_per_piece[]" step="any" value="1000" readonly title="Auto from Conv Factor" style="padding-right:18px; border-radius:0; border:1px solid #dee2e6; height:30px; background:#f8f8f8;">
                            <span style="position:absolute;right:5px;top:50%;transform:translateY(-50%);font-size:9px;color:#999;pointer-events:none;font-weight:600;">g</span>
                        </div>
                    </td>
                    <td class="p-1"><input type="number" class="form-control-pro form-control-sm base-sale-input" name="variant_sale_price[]" step="any" placeholder="0.00" required></td>
                    <td class="p-1"><input type="number" class="form-control-pro form-control-sm" name="variant_wholesale_price[]" step="any" placeholder="0.00" value="0"></td>
                    <td class="p-1"><input type="number" class="form-control-pro form-control-sm base-purch-input" name="variant_purchase_price[]" step="any" placeholder="0.00" required></td>
                    <td class="p-1"><input type="number" class="form-control-pro form-control-sm" name="variant_alert_qty[]" value="0" placeholder="0"></td>
                    <td class="p-1"><input type="text" class="form-control-pro form-control-sm" name="variant_barcode[]" value="${generateRandomBarcode()}"></td>
                    <td class="p-1 text-center">
                        <span class="badge bg-primary px-2 py-1">Base</span>
                    </td>
                `;
                variantsBody.appendChild(tr);

                const baseSaleInp = tr.querySelector('.base-sale-input');
                const basePurchInp = tr.querySelector('.base-purch-input');
                const baseWholesaleInp = tr.querySelector('input[name="variant_wholesale_price[]"]');
                const baseStockInp = tr.querySelector('input[name="variant_stock[]"]');

                if (baseSaleInp) baseSaleInp.addEventListener('input', updatePriceSuggestions);
                if (basePurchInp) basePurchInp.addEventListener('input', updatePriceSuggestions);
                if (baseWholesaleInp) baseWholesaleInp.addEventListener('input', updatePriceSuggestions);
                if (baseStockInp) baseStockInp.addEventListener('input', updateVariantStocksFromBase);
                
                tr.querySelector('.base-name-input').addEventListener('input', function() {
                    manualNames[vid] = true;
                });
                toggleFactorColumns();
            }

            function updateVariantStocksFromBase() {
                if (variantMode !== 'weight') return;

                const baseRow = variantsBody.querySelector('tr');
                if (!baseRow) return;

                const baseStockInp = baseRow.querySelector('input[name="variant_stock[]"]');
                const baseStock = parseFloat(baseStockInp?.value || 0);

                const rows = variantsBody.querySelectorAll('tr');
                rows.forEach((row, index) => {
                    if (index === 0) return;

                    const factorInp = row.querySelector('.conv-factor-input');
                    const pieceWtInp = row.querySelector('input[name="variant_weight_per_piece[]"]');
                    const stockInp = row.querySelector('input[name="variant_stock[]"]');

                    let factor = parseFloat(factorInp?.value || 0);
                    let pieceWt = parseFloat(pieceWtInp?.value || 0);

                    if (baseStock > 0 && factor > 0 && stockInp) {
                        const calcPcs = Math.round(baseStock / factor);
                        stockInp.value = calcPcs;
                    }
                });
            }

            function updatePriceSuggestions() {
                if (variantMode === 'pcs') return;

                const baseRow = variantsBody.querySelector('tr');
                if (!baseRow) return;

                const baseSale = parseFloat(baseRow.querySelector('.base-sale-input')?.value || 0);
                const basePurch = parseFloat(baseRow.querySelector('.base-purch-input')?.value || 0);
                const baseWholesale = parseFloat(baseRow.querySelector('input[name="variant_wholesale_price[]"]')?.value || 0);

                const rows = variantsBody.querySelectorAll('tr');
                rows.forEach((row, index) => {
                    if (index === 0) return;
                    
                    const vid = row.dataset.vid;
                    const factorInput = row.querySelector('.conv-factor-input');
                    const factor = parseFloat(factorInput?.value || 0);
                    
                    const saleInp = row.querySelector('.sale-price-input');
                    const purchInp = row.querySelector('.purch-price-input');
                    const wholesaleInp = row.querySelector('input[name="variant_wholesale_price[]"]');
                    const pieceWtInp = row.querySelector('input[name="variant_weight_per_piece[]"]');

                    if (variantMode === 'weight' && factor > 0) {
                        if (saleInp && (!vid || !manualPrices[vid + '_sale'])) {
                            saleInp.value = (baseSale * factor).toFixed(2);
                        }
                        if (purchInp && (!vid || !manualPrices[vid + '_purch'])) {
                            purchInp.value = (basePurch * factor).toFixed(2);
                        }
                        if (wholesaleInp && (!vid || !manualPrices[vid + '_wholesale'])) {
                            wholesaleInp.value = (baseWholesale * factor).toFixed(2);
                        }
                        if (pieceWtInp) {
                            pieceWtInp.value = (factor * 1000).toFixed(0);
                        }
                    }
                });

                updateVariantStocksFromBase();
            }

            function addVariantRow(weightGrams = null) {
                const tr = document.createElement('tr');
                const vid = 'var_' + Date.now();
                tr.dataset.vid = vid;
                
                const isCartonMode = unitDropdown && unitDropdown.value === 'by_cartons';
                let factor = 1;
                let suggestedName = productNameInput.value || '';
                if (weightGrams) {
                    factor = parseFloat(weightGrams) > 10 ? parseFloat((weightGrams / 1000).toFixed(6)) : parseFloat(weightGrams);
                    suggestedName += ` - ${weightGrams}g`;
                }
                
                const baseRow = variantsBody.querySelector('tr');
                const baseSale = parseFloat(baseRow?.querySelector('.base-sale-input')?.value || 0);
                const basePurch = parseFloat(baseRow?.querySelector('.base-purch-input')?.value || 0);
                const baseWholesale = parseFloat(baseRow?.querySelector('input[name="variant_wholesale_price[]"]')?.value || 0);
                const baseConv = baseRow?.querySelector('.conv-factor-input')?.value || (isCartonMode ? '0' : '1');

                let suggSale = '';
                let suggPurch = '';
                let suggWholesale = '';

                if (variantMode === 'weight') {
                    suggSale = (baseSale * factor).toFixed(2);
                    suggPurch = (basePurch * factor).toFixed(2);
                    suggWholesale = (baseWholesale * factor).toFixed(2);
                } else if (isCartonMode) {
                    suggSale = baseSale ? baseSale.toFixed(2) : '';
                    suggPurch = basePurch ? basePurch.toFixed(2) : '';
                    suggWholesale = baseWholesale ? baseWholesale.toFixed(2) : '';
                }

                const initPieceWt = weightGrams || (factor < 10 ? (factor * 1000).toFixed(1).replace(/\.0$/, '') : factor);
                const randBarcode = generateRandomBarcode();

                tr.innerHTML = `
                    <td class="p-1">
                        <input type="text" class="form-control-pro form-control-sm var-name-input" name="variant_name[]" value="${suggestedName}" placeholder="Name">
                        <input type="hidden" name="variant_is_base[]" value="0">
                    </td>
                    <td class="p-1"><input type="text" class="form-control-pro form-control-sm" name="variant_size[]" placeholder="Size (e.g. Small, 30cm)"></td>
                    <td class="p-1"><input type="text" class="form-control-pro form-control-sm" name="variant_color[]" placeholder="Color"></td>
                    <td class="p-1">
                        <select class="form-select form-select-sm px-1 fw-bold text-dark" name="variant_unit[]" style="font-size:11px;">
                            <option value="Carton" ${isCartonMode ? 'selected' : ''}>Carton</option>
                            <option value="Pcs" ${!isCartonMode ? 'selected' : ''}>Pcs</option>
                            <option value="Kg">Kg</option>
                            <option value="Gm">Gm</option>
                            <option value="Ft">Ft</option>
                            <option value="Meter">Mtr</option>
                            <option value="Box">Box</option>
                            <option value="Dozen">Dzn</option>
                        </select>
                    </td>
                    <td class="p-1">
                        <input type="number" class="form-control-pro form-control-sm text-center fw-bold text-primary stock-input" name="variant_stock[]" step="any" value="0" placeholder="0" title="${isCartonMode ? 'Initial Stock (Cartons)' : 'Initial Stock'}" ${variantMode === 'weight' ? 'readonly style="background:#f8f9ff;color:#0d6efd;font-weight:bold;"' : ''}>
                    </td>
                    <td class="p-0 conv-col">
                        <input type="text" inputmode="decimal" class="form-control-pro form-control-sm conv-factor-input text-center fw-bold text-success" name="variant_conv_factor[]" value="${isCartonMode ? '0' : ''}" placeholder="0" title="${isCartonMode ? 'Pieces per Carton' : 'Conv Factor: weight per Pcs in base unit'}" style="border-radius:0; border:1px solid #198754; height:30px; border-width:1.5px;">
                    </td>
                    <td class="p-0 piece-wt-only-col">
                        <div style="position:relative;">
                            <input type="number" class="form-control-pro form-control-sm piece-wt-display" name="variant_weight_per_piece[]" step="any" value="" placeholder="—" readonly title="Auto = Conv Factor × 1000" style="padding-right:18px; border-radius:0; border:1px solid #dee2e6; height:30px; background:#f0fff4; color:#198754; font-weight:600;">
                            <span style="position:absolute;right:5px;top:50%;transform:translateY(-50%);font-size:9px;color:#198754;pointer-events:none;font-weight:700;">g</span>
                        </div>
                    </td>
                    <td class="p-1"><input type="number" class="form-control-pro form-control-sm sale-price-input" name="variant_sale_price[]" step="any" value="${suggSale}" placeholder="0.00" required></td>
                    <td class="p-1"><input type="number" class="form-control-pro form-control-sm" name="variant_wholesale_price[]" step="any" placeholder="0.00" value="${suggWholesale}"></td>
                    <td class="p-1"><input type="number" class="form-control-pro form-control-sm purch-price-input" name="variant_purchase_price[]" step="any" value="${suggPurch}" placeholder="0.00" required></td>
                    <td class="p-1"><input type="number" class="form-control-pro form-control-sm" name="variant_alert_qty[]" value="0" placeholder="0"></td>
                    <td class="p-1"><input type="text" class="form-control-pro form-control-sm" name="variant_barcode[]" value="${randBarcode}"></td>
                    <td class="p-1 text-center">
                        <button type="button" class="btn btn-sm btn-outline-danger remove-var-btn p-1 px-2" title="Remove"><i class="fas fa-trash"></i></button>
                    </td>
                `;
                variantsBody.appendChild(tr);

                const saleInput = tr.querySelector('.sale-price-input');
                const purchInput = tr.querySelector('.purch-price-input');
                const wholesaleInput = tr.querySelector('input[name="variant_wholesale_price[]"]');
                const convInput = tr.querySelector('.conv-factor-input');
                const pieceWtInput = tr.querySelector('input[name="variant_weight_per_piece[]"]');

                if (saleInput) saleInput.addEventListener('input', () => { manualPrices[vid + '_sale'] = true; });
                if (purchInput) purchInput.addEventListener('input', () => { manualPrices[vid + '_purch'] = true; });
                if (wholesaleInput) wholesaleInput.addEventListener('input', () => { manualPrices[vid + '_wholesale'] = true; });

                if (convInput) {
                    let convTimer = null;
                    convInput.addEventListener('input', function() {
                        clearTimeout(convTimer);
                        const self = this;
                        let fImmediate = parseFloat(self.value);
                        if (isNaN(fImmediate)) fImmediate = 0;
                        if (pieceWtInput && fImmediate > 0 && variantMode === 'weight') {
                            pieceWtInput.value = (fImmediate * 1000).toFixed(0);
                        } else if (pieceWtInput && fImmediate === 0) {
                            pieceWtInput.value = '';
                        }
                        convTimer = setTimeout(() => {
                            updatePriceSuggestions();
                            updateVariantStocksFromBase();
                        }, 300);
                    });
                }
                toggleFactorColumns();
                updateVariantStocksFromBase();
            }

            function toggleFactorColumns() {
                if (!unitDropdown) return;
                const mode = unitDropdown.value;
                const isWeight = (mode === 'by_kg' || mode === 'by_gm' || mode === 'by_ton');
                const isCarton = (mode === 'by_cartons');

                const headerEl = document.getElementById('convFactorHeader');
                if (headerEl) {
                    if (isCarton) {
                        headerEl.textContent = 'Pcs / Carton';
                    } else if (isWeight) {
                        headerEl.textContent = 'Conv Factor';
                    } else {
                        headerEl.textContent = 'Conv / Pack';
                    }
                }

                const convCols = document.querySelectorAll('.conv-col');
                const pieceWtOnlyCols = document.querySelectorAll('.piece-wt-only-col');

                if (isCarton) {
                    convCols.forEach(c => c.classList.remove('d-none'));
                    pieceWtOnlyCols.forEach(c => c.classList.add('d-none'));
                    
                    const baseRow = variantsBody.querySelector('tr');
                    if (baseRow) {
                        const baseConv = baseRow.querySelector('.conv-factor-input');
                        if (baseConv) {
                            baseConv.readOnly = false;
                            baseConv.style.background = '#ffffff';
                            if (!baseConv.value || baseConv.value === '1') {
                                baseConv.value = '0';
                            }
                            baseConv.placeholder = '0';
                            baseConv.title = 'Pieces per Carton';
                        }
                        const baseUnit = baseRow.querySelector('select[name="variant_unit[]"]');
                        if (baseUnit) {
                            baseUnit.value = 'Carton';
                        }
                    }
                } else if (isWeight) {
                    convCols.forEach(c => c.classList.remove('d-none'));
                    pieceWtOnlyCols.forEach(c => c.classList.remove('d-none'));
                    const baseRow = variantsBody.querySelector('tr');
                    if (baseRow) {
                        const baseConv = baseRow.querySelector('.conv-factor-input');
                        if (baseConv) {
                            baseConv.readOnly = true;
                            baseConv.value = '1';
                            baseConv.style.background = '#f8f8f8';
                            baseConv.placeholder = '1';
                            baseConv.title = 'Base Conv Factor = 1';
                        }
                    }
                } else {
                    convCols.forEach(c => c.classList.add('d-none'));
                    pieceWtOnlyCols.forEach(c => c.classList.add('d-none'));
                }
            }

            if (unitDropdown) {
                $(unitDropdown).on('change', function() {
                    updateVariantMode();
                    toggleFactorColumns();
                });
                // Initialize mode on load
                updateVariantMode();
                toggleFactorColumns();
            }

            enableVariantsBtn.addEventListener('click', function() {
                if (variantsBody.children.length === 0) {
                    addBaseVariantRow();
                } else {
                    addVariantRow();
                }
                if (typeof isMobile === 'function' && isMobile()) {
                    if (typeof rebuildMobileCards === 'function') rebuildMobileCards();
                    const cards = document.querySelectorAll('.mob-variant-card');
                    document.querySelectorAll('.mob-variant-card.is-open').forEach(c => c.classList.remove('is-open'));
                    if (cards.length) cards[cards.length - 1].classList.add('is-open');
                }
            });

            // Always ensure the button reads + Add Variant Row
            enableVariantsBtn.innerHTML = '<i class="fas fa-plus me-1"></i>Add Variant Row';
            enableVariantsBtn.className = 'btn btn-sm btn-primary';

            variantsBody.addEventListener('click', function(e) {
                const addBtn = e.target.closest('.add-var-btn');
                const remBtn = e.target.closest('.remove-var-btn');
                const genBtn = e.target.closest('.gen-var-barcode');

                if (addBtn) {
                    addVariantRow();
                } else if (remBtn) {
                    const row = remBtn.closest('tr');
                    // In weight mode, base variant cannot be deleted
                    if (row.querySelector('.base-name-input')) {
                        Swal.fire({icon: 'error', title: 'Cannot Delete', text: 'The base variant cannot be deleted.'});
                        return;
                    }

                    if (variantsBody.children.length > 1) {
                        row.remove();
                    } else {
                        // If it's the last row, clear inputs instead of removing
                        row.querySelectorAll('input:not([type="hidden"])').forEach(inp => inp.value = '');
                        const bc = row.querySelector('input[name="variant_barcode[]"]');
                        if (bc) bc.value = generateRandomBarcode();
                    }
                } else if (genBtn) {
                    const input = genBtn.closest('td').querySelector('input');
                    input.value = generateRandomBarcode();
                }
            });

            // Prevent duplicate Conv Factors on form submit
            form.addEventListener('submit', function(e) {
                if (variantMode === 'weight' && !variantsContainer.classList.contains('d-none')) {
                    const factors = Array.from(document.querySelectorAll('input[name="variant_conv_factor[]"]')).map(el => parseFloat(el.value));
                    const uniqueFactors = new Set(factors);
                    if (factors.length !== uniqueFactors.size) {
                        e.preventDefault();
                        Swal.fire({icon: 'error', title: 'Validation Error', text: 'A variant with the same Conv Factor already exists.'});
                        return;
                    }
                    if (factors.some(f => f <= 0 || isNaN(f))) {
                        e.preventDefault();
                        Swal.fire({icon: 'error', title: 'Validation Error', text: 'Conv Factor must be greater than 0.'});
                        return;
                    }
                }
            });
        });
        // ============================================================
        //  MOBILE VARIANT CARDS SYSTEM
        //  Two-way sync: desktop table <-> mobile accordion cards
        // ============================================================

        const isMobile = () => window.innerWidth < 768;
        let mobVariantIndex = 0;

        // ---- Build a mobile card HTML for a given table row ----
        function buildMobCard(tr, idx, isBase) {
            const card = document.createElement('div');
            card.className = 'mob-variant-card' + (isBase ? ' is-base' : '') + (isBase ? ' is-open' : '');
            card.dataset.mobIdx = idx;

            // Mirror the hidden inputs from tr into card as hidden (for shared form state)
            const isBaseVal = isBase ? '1' : '0';

            const nameVal    = tr.querySelector('[name="variant_name[]"]')?.value || '';
            const sizeVal    = tr.querySelector('[name="variant_size[]"]')?.value || '';
            const colorVal   = tr.querySelector('[name="variant_color[]"]')?.value || '';
            const unitVal    = tr.querySelector('[name="variant_unit[]"]')?.value || 'Pcs';
            const stockInp   = tr.querySelector('[name="variant_stock[]"]');
            const stockVal   = stockInp?.value || '0';
            const convVal    = tr.querySelector('[name="variant_conv_factor[]"]')?.value || '';
            const pieceWtVal = tr.querySelector('[name="variant_weight_per_piece[]"]')?.value || '';
            const saleVal    = tr.querySelector('[name="variant_sale_price[]"]')?.value || '';
            const wsaleVal   = tr.querySelector('[name="variant_wholesale_price[]"]')?.value || '';
            const purchVal   = tr.querySelector('[name="variant_purchase_price[]"]')?.value || '';
            const alertVal   = tr.querySelector('[name="variant_alert_qty[]"]')?.value || '0';
            const barcodeVal = tr.querySelector('[name="variant_barcode[]"]')?.value || '';
            const stockReadonly = stockInp?.hasAttribute('readonly') ? 'readonly' : '';

            const isWeightMode = variantMode === 'weight';

            const label = nameVal || (isBase ? 'Base Variant' : 'Variant ' + (idx + 1));

            card.innerHTML = `
                <div class="mob-card-header" onclick="mobToggleCard(this)">
                    <div class="mob-card-title">
                        <i class="fas fa-layer-group" style="color:${isBase ? '#5B4CF7' : '#94a3b8'};font-size:14px;flex-shrink:0;"></i>
                        <span class="mob-card-label">${label}</span>
                        ${isBase ? '<span class="mob-base-badge">Base</span>' : ''}
                    </div>
                    <svg class="mob-chevron" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                </div>
                <div class="mob-card-body">
                    <div class="mob-section-label"><i class="fas fa-info-circle me-1"></i>Basic Info</div>
                    <div class="mob-field-group">
                        <div class="mob-label">Variant Name ${isBase ? '<span class="req">*</span>' : ''}</div>
                        <input type="text" class="mob-input mob-sync" data-field="variant_name[]" value="${nameVal}" placeholder="e.g. Red - XL" autocomplete="off">
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                        <div class="mob-field-group">
                            <div class="mob-label">Size</div>
                            <input type="text" class="mob-input mob-sync" data-field="variant_size[]" value="${sizeVal}" placeholder="XL, M...">
                        </div>
                        <div class="mob-field-group">
                            <div class="mob-label">Color</div>
                            <input type="text" class="mob-input mob-sync" data-field="variant_color[]" value="${colorVal}" placeholder="Red, Blue...">
                        </div>
                    </div>
                    <div class="mob-field-group">
                        <div class="mob-label">Unit</div>
                        <select class="mob-select mob-sync" data-field="variant_unit[]">
                            <option value="Carton" ${unitVal==='Carton'?'selected':''}>Carton</option>
                            <option value="Pcs" ${unitVal==='Pcs'?'selected':''}>Pcs</option>
                            <option value="Kg" ${unitVal==='Kg'?'selected':''}>Kg</option>
                            <option value="Gm" ${unitVal==='Gm'?'selected':''}>Gm</option>
                            <option value="Ft" ${unitVal==='Ft'?'selected':''}>Ft</option>
                            <option value="Meter" ${unitVal==='Meter'?'selected':''}>Mtr</option>
                            <option value="Box" ${unitVal==='Box'?'selected':''}>Box</option>
                            <option value="Dozen" ${unitVal==='Dozen'?'selected':''}>Dzn</option>
                        </select>
                    </div>

                    <div class="mob-section-divider"></div>
                    <div class="mob-section-label"><i class="fas fa-boxes me-1"></i>Stock & Pricing</div>

                    <div class="mob-field-group">
                        <div class="mob-label">Initial Stock ${isBase ? '' : (isWeightMode ? '🔵 Auto' : '')}</div>
                        <input type="number" class="mob-input mob-sync ${!isBase && isWeightMode ? 'auto-field mob-stock-auto' : ''}" data-field="variant_stock[]" value="${stockVal}" placeholder="${!isBase && isWeightMode ? 'Auto' : '0'}" ${!isBase && isWeightMode ? 'readonly' : ''} step="any">
                    </div>

                    ${unitDropdown && unitDropdown.value === 'by_cartons' ? `
                    <div class="mob-field-group">
                        <div class="mob-label" style="color:#0284c7;font-weight:700;">📦 Pieces Per Carton</div>
                        <input type="number" class="mob-input conv-field mob-sync mob-conv-inp" data-field="variant_conv_factor[]" value="${convVal || '0'}" placeholder="0" autocomplete="off" style="border:1.5px solid #0284c7;">
                    </div>
                    ` : ''}

                    ${isWeightMode && !isBase ? `
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                        <div class="mob-field-group">
                            <div class="mob-label" style="color:#059669;">🔢 Conv Factor</div>
                            <input type="text" inputmode="decimal" class="mob-input conv-field mob-sync mob-conv-inp" data-field="variant_conv_factor[]" value="${convVal}" placeholder="0.000" autocomplete="off">
                        </div>
                        <div class="mob-field-group">
                            <div class="mob-label" style="color:#059669;">⚖ Piece Wt (g)</div>
                            <div class="mob-suffix-wrap">
                                <input type="number" class="mob-input mob-sync mob-piecewt" data-field="variant_weight_per_piece[]" value="${pieceWtVal}" placeholder="—" readonly style="background:#f0fdf4;color:#059669;font-weight:700;">
                                <span class="mob-suffix">g</span>
                            </div>
                        </div>
                    </div>
                    ` : (isWeightMode && isBase ? `
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                        <div class="mob-field-group">
                            <div class="mob-label">Conv Factor</div>
                            <input type="text" class="mob-input" value="1" readonly style="background:#f8fafc;color:#94a3b8;">
                        </div>
                        <div class="mob-field-group">
                            <div class="mob-label">Piece Wt (g)</div>
                            <div class="mob-suffix-wrap">
                                <input type="number" class="mob-input" value="1000" readonly style="background:#f8fafc;color:#94a3b8;">
                                <span class="mob-suffix">g</span>
                            </div>
                        </div>
                    </div>
                    ` : '')}

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                        <div class="mob-field-group">
                            <div class="mob-label">Sale Price <span class="req">*</span></div>
                            <input type="number" class="mob-input mob-sync" data-field="variant_sale_price[]" value="${saleVal}" placeholder="0.00" step="any" required>
                        </div>
                        <div class="mob-field-group">
                            <div class="mob-label">Wholesale Price</div>
                            <input type="number" class="mob-input mob-sync" data-field="variant_wholesale_price[]" value="${wsaleVal}" placeholder="0.00" step="any">
                        </div>
                    </div>
                    <div class="mob-field-group">
                        <div class="mob-label">Purchase Price <span class="req">*</span></div>
                        <input type="number" class="mob-input mob-sync" data-field="variant_purchase_price[]" value="${purchVal}" placeholder="0.00" step="any" required>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                        <div class="mob-field-group">
                            <div class="mob-label">Alert Qty</div>
                            <input type="number" class="mob-input mob-sync" data-field="variant_alert_qty[]" value="${alertVal}" placeholder="0" step="any">
                        </div>
                        <div class="mob-field-group">
                            <div class="mob-label">Barcode</div>
                            <input type="text" class="mob-input mob-sync" data-field="variant_barcode[]" value="${barcodeVal}" placeholder="Scan or type...">
                        </div>
                    </div>

                    ${!isBase ? `<button type="button" class="mob-delete-btn" onclick="mobDeleteCard(this)">
                        <i class="fas fa-trash"></i> Remove Variant
                    </button>` : ''}
                </div>
            `;

            // Event: sync mob input → desktop table
            card.querySelectorAll('.mob-sync').forEach(inp => {
                inp.addEventListener('input', () => mobSyncToDesktop(card, idx));
            });
            card.querySelectorAll('.mob-sync').forEach(sel => {
                if (sel.tagName === 'SELECT') sel.addEventListener('change', () => mobSyncToDesktop(card, idx));
            });

            // Conv factor → auto piece wt + stock + prices on mobile
            const convInp = card.querySelector('.mob-conv-inp');
            if (convInp) {
                convInp.addEventListener('input', () => {
                    let f = parseFloat(convInp.value);
                    if (isNaN(f)) f = 0;
                    
                    const pieceWt = card.querySelector('.mob-piecewt');
                    const stockAutoInp = card.querySelector('.mob-stock-auto');
                    const saleInp2 = card.querySelector('[data-field="variant_sale_price[]"]');
                    const purchInp2 = card.querySelector('[data-field="variant_purchase_price[]"]');
                    const wsInp2 = card.querySelector('[data-field="variant_wholesale_price[]"]');

                    if (f > 0) {
                        if (pieceWt) pieceWt.value = (f * 1000).toFixed(0);

                        // Auto stock from base row
                        const baseRow = variantsBody.querySelector('tr');
                        const baseStockInp = baseRow?.querySelector('[name="variant_stock[]"]');
                        const baseStock = parseFloat(baseStockInp?.value || 0);
                        if (stockAutoInp && baseStock > 0) stockAutoInp.value = Math.round(baseStock / f);

                        // Auto prices from base row
                        const baseSale = parseFloat(baseRow?.querySelector('.base-sale-input')?.value || 0);
                        const basePurch = parseFloat(baseRow?.querySelector('.base-purch-input')?.value || 0);
                        const baseWs = parseFloat(baseRow?.querySelector('[name="variant_wholesale_price[]"]')?.value || 0);
                        if (saleInp2) saleInp2.value = (baseSale * f).toFixed(2);
                        if (purchInp2) purchInp2.value = (basePurch * f).toFixed(2);
                        if (wsInp2) wsInp2.value = (baseWs * f).toFixed(2);
                    } else {
                        if (pieceWt) pieceWt.value = '';
                        if (stockAutoInp) stockAutoInp.value = '';
                    }
                    mobSyncToDesktop(card, idx);
                });
            }

            // Live update card title from name input
            const nameInp = card.querySelector('[data-field="variant_name[]"]');
            if (nameInp) {
                nameInp.addEventListener('input', () => {
                    const lbl = card.querySelector('.mob-card-label');
                    if (lbl) lbl.textContent = nameInp.value || (isBase ? 'Base Variant' : 'Variant ' + (idx + 1));
                });
            }

            return card;
        }

        // ---- Sync mobile card fields → desktop table row ----
        function mobSyncToDesktop(card, idx) {
            const tr = variantsBody.children[idx];
            if (!tr) return;
            card.querySelectorAll('.mob-sync').forEach(inp => {
                const field = inp.dataset.field;
                if (!field) return;
                const desktopEl = tr.querySelector(`[name="${field}"]`);
                if (desktopEl) desktopEl.value = inp.value;
            });
            // trigger desktop recalc
            updateVariantStocksFromBase();
        }

        // ---- Toggle accordion (one open at a time) ----
        function mobToggleCard(headerEl) {
            const card = headerEl.closest('.mob-variant-card');
            const isOpen = card.classList.contains('is-open');
            // Close all
            document.querySelectorAll('.mob-variant-card.is-open').forEach(c => c.classList.remove('is-open'));
            // Toggle this
            if (!isOpen) card.classList.add('is-open');
        }

        // ---- Delete card + corresponding desktop row ----
        function mobDeleteCard(btn) {
            const card = btn.closest('.mob-variant-card');
            const idx = parseInt(card.dataset.mobIdx);
            const tr = variantsBody.children[idx];
            if (tr) {
                if (variantsBody.children.length <= 1) return;
                tr.remove();
            }
            card.remove();
            // Re-index remaining cards
            document.querySelectorAll('.mob-variant-card').forEach((c, i) => {
                c.dataset.mobIdx = i;
            });
        }

        // ---- Add a new variant (mobile) ----
        function mobileAddVariant() {
            // Trigger desktop add (reuse logic)
            addVariantRow();
            // Rebuild mobile cards from updated desktop rows
            rebuildMobileCards();
            // Open last card
            const cards = document.querySelectorAll('.mob-variant-card');
            document.querySelectorAll('.mob-variant-card.is-open').forEach(c => c.classList.remove('is-open'));
            if (cards.length) cards[cards.length - 1].classList.add('is-open');
        }

        // ---- Rebuild all mobile cards from desktop rows ----
        function rebuildMobileCards() {
            const body = document.getElementById('mobileVariantsBody');
            if (!body) return;
            body.innerHTML = '';
            Array.from(variantsBody.children).forEach((tr, idx) => {
                const isBase = !!tr.querySelector('.base-name-input');
                const card = buildMobCard(tr, idx, isBase);
                body.appendChild(card);
            });
        }

        // ---- Initial render of mobile cards (after desktop rows exist) ----
        // Observe desktop table for row additions
        const desktopObserver = new MutationObserver(() => {
            if (isMobile()) rebuildMobileCards();
        });
        desktopObserver.observe(variantsBody, { childList: true });

        // Also rebuild when window resizes to mobile
        window.addEventListener('resize', () => {
            if (isMobile()) rebuildMobileCards();
        });

        // Make mobileAddVariant global
        window.mobileAddVariant = mobileAddVariant;
        window.mobToggleCard = mobToggleCard;
        window.mobDeleteCard = mobDeleteCard;

    </script>
@endsection
