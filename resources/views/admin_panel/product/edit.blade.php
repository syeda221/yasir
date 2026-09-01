@extends('admin_panel.layout.app')

@section('content')
    {{-- 
        SUCCESS: Horizontal Compact Layout Redesign for Edit Page
        Matches Create Product UI
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
    </style>

    <div class="page-container">
        
        {{-- Page Title --}}
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('product') }}" class="btn btn-white border shadow-sm rounded-circle p-0" style="width: 36px; height: 36px; display: grid; place-items: center;">
                    <i class="las la-arrow-left"></i>
                </a>
                <div>
                    <h5 class="fw-bold mb-0 text-dark">Edit Product</h5>
                    <small class="text-muted" style="font-size:0.8rem;">Update item settings and stock values</small>
                </div>
            </div>
        </div>

        <form id="productForm" action="{{ route('product.update', $product->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row g-3">
                
                {{-- MAIN COLUMN: Full Width --}}
                <div class="col-12">
                    
                    {{-- CARD 1: Identity & Categorization --}}
                    <div class="section-card">
                        <div class="card-header-pro">
                            <h5 class="card-title-pro"><i class="las la-tag text-primary"></i> Product Identity</h5>
                        </div>
                        <div class="card-body-pro">
                            <div class="row g-3">
                                
                                {{-- Sub-grid for left content, image on the right --}}
                                <div class="col-md-9">
                                    <div class="row g-3">
                                        <div class="col-md-8">
                                            <label class="form-label-pro">Product Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control-pro fs-6 fw-bold" name="product_name" required value="{{ $product->item_name }}" placeholder="e.g. Ceramic Floor Tile 60x60">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label-pro">Barcode Auto-Gen</label>
                                            <div class="d-flex">
                                                <input type="text" class="form-control-pro" id="barcodeInput" name="barcode_path" value="{{ $product->barcode_path }}" style="border-top-right-radius: 0; border-bottom-right-radius: 0;">
                                                <button type="button" class="btn btn-light border" id="generateBarcodeBtn" style="border-left: 0; border-top-left-radius: 0; border-bottom-left-radius: 0; border-top-right-radius: var(--radius-md); border-bottom-right-radius: var(--radius-md);"><i class="las la-magic"></i></button>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <label class="form-label-pro">Category <span class="text-danger">*</span></label>
                                            <div class="d-flex gap-1">
                                                <select class="form-select form-control-pro form-select-pro" id="category-dropdown" name="category_id" required>
                                                    <option value="">Select...</option>
                                                    @foreach ($categories as $cat)
                                                        <option value="{{ $cat->id }}" {{ $product->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                                    @endforeach
                                                </select>
                                                <button type="button" class="btn btn-light border px-2 shadow-sm" data-toggle="modal" data-target="#categoryModal">+</button>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label-pro">Sub Category</label>
                                            <div class="d-flex gap-1">
                                                <select class="form-select form-control-pro form-select-pro" id="subcategory-dropdown" name="sub_category_id">
                                                    <option value="">Select...</option>
                                                    @foreach ($subcategories as $subCat)
                                                        @if ($subCat->category_id == $product->category_id)
                                                            <option value="{{ $subCat->id }}" {{ $product->sub_category_id == $subCat->id ? 'selected' : '' }}>{{ $subCat->name }}</option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                                <button type="button" class="btn btn-light border px-2 shadow-sm" data-toggle="modal" data-target="#subcategoryModal">+</button>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label-pro">Brand</label>
                                            <div class="d-flex gap-1">
                                                <select class="form-select form-control-pro form-select-pro" name="brand_id" required>
                                                    <option value="">Select...</option>
                                                    @foreach ($brands as $brand)
                                                        <option value="{{ $brand->id }}" {{ $product->brand_id == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                                    @endforeach
                                                </select>
                                                <button type="button" class="btn btn-light border px-2 shadow-sm" data-toggle="modal" data-target="#brandModal">+</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Image Uploader side-by-side (Right) --}}
                                <div class="col-md-3">
                                    <label class="form-label-pro">Product Image</label>
                                    <input type="file" id="imageInput" name="image" class="d-none" accept="image/*">
                                    <div class="img-uploader" style="height: 110px;" onclick="document.getElementById('imageInput').click()">
                                        <button type="button" id="clearImageBtn" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 {{ $product->image ? '' : 'd-none' }} rounded-circle" style="width:20px;height:20px;padding:0;z-index:10; font-size:12px; line-height:1;">&times;</button>
                                        @if($product->image)
                                            <img id="preview" src="{{ asset('uploads/products/' . $product->image) }}" style="max-height: 100px;">
                                            <div id="uploadPlaceholder" class="text-center p-2 d-none">
                                                <i class="las la-camera fs-3 text-primary"></i>
                                                <div class="fw-bold" style="font-size: 11px;">Upload</div>
                                            </div>
                                        @else
                                            <img id="preview" class="d-none" style="max-height: 100px;">
                                            <div id="uploadPlaceholder" class="text-center p-2">
                                                <i class="las la-camera fs-3 text-primary"></i>
                                                <div class="fw-bold" style="font-size: 11px;">Upload</div>
                                            </div>
                                        @endif
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

                    {{-- CARD 2: Stock Specifications & Pricing --}}
                    <div class="section-card" style="display:none !important;">
                        <div class="card-header-pro d-flex flex-wrap justify-content-between align-items-center gap-3">
                            <h5 class="card-title-pro"><i class="las la-box-open text-info"></i> Stock & Pricing</h5>
                            
                            <div class="d-flex align-items-center flex-wrap gap-3">
                                <div class="bg-light px-3 py-1 rounded border d-flex gap-3 text-nowrap align-items-center" style="font-size: 0.82rem;">
                                    <div>
                                        <span class="text-muted fw-bold">Total Stock:</span>
                                        <strong class="text-primary fs-6" id="total_stock_display">0</strong> <span class="text-muted small">pcs</span>
                                    </div>
                                    <div class="border-start ps-3">
                                        <span class="text-muted fw-bold">Est. Value:</span>
                                        <span class="text-success fw-bold">PKR <span id="sale_total_display">0.00</span></span>
                                    </div>
                                </div>

                                {{-- Unit Select --}}
                                <div class="col-md-4">
                                     <label class="form-label-pro">Unit</label>
                                     <select class="form-select form-control-pro form-select-pro fw-bold" name="size_mode" id="unit-dropdown" style="max-width: 200px;">
                                         <option value="by_pieces" {{ $product->size_mode == 'by_pieces' ? 'selected' : '' }}>Pcs</option>
                                         <option value="by_cartons" {{ $product->size_mode == 'by_cartons' ? 'selected' : '' }}>Carton</option>
                                         <option value="by_meter" {{ $product->size_mode == 'by_meter' ? 'selected' : '' }}>Meter</option>
                                         <option value="by_feet" {{ $product->size_mode == 'by_feet' ? 'selected' : '' }}>Ft (Feet)</option>
                                         <option value="by_kg" {{ $product->size_mode == 'by_kg' ? 'selected' : '' }}>Kg</option>
                                         <option value="by_gm" {{ $product->size_mode == 'by_gm' ? 'selected' : '' }}>Gm</option>
                                         <option value="by_ton" {{ $product->size_mode == 'by_ton' ? 'selected' : '' }}>Ton</option>
                                     </select>
                                </div>
                            </div>
                        </div>

                        <div class="card-body-pro">
                            <div class="row g-3">
                                
                                {{-- Dynamic Stock Fields --}}
                                <div class="col-md-12">
                                    <div class="row g-3">
                                        {{-- Carton Mode Fields --}}
                                        <div class="col-md-3 group-by-size">
                                            <label class="form-label-pro">Pcs / Carton</label>
                                            <input type="number" class="form-control-pro" name="pieces_per_box" id="pieces_per_box" value="{{ $product->pieces_per_box }}" placeholder="0">
                                        </div>
                                        <div class="col-md-3 group-by-size">
                                            <label class="form-label-pro">In-Stock Cartons</label>
                                            <input type="number" class="form-control-pro border-primary text-primary fw-bold" name="boxes_quantity" id="boxes_quantity" value="{{ $product->boxes_quantity }}" placeholder="0">
                                        </div>
                                        <div class="col-md-3 group-loose">
                                            <label class="form-label-pro text-warning">Loose Pieces (Extra)</label>
                                            <input type="number" class="form-control-pro border-warning" name="loose_pieces" id="loose_pieces" value="{{ $product->loose_pieces }}" placeholder="0">
                                        </div>

                                        {{-- Piece Mode Fields --}}
                                        <div class="col-md-6 group-piece-only d-none">
                                            <label class="form-label-pro text-primary">Total In-Stock Quantity (Pieces)</label>
                                            <input type="number" class="form-control-pro border-primary text-primary fw-bold" name="piece_quantity" id="piece_quantity" value="{{ $product->piece_quantity }}" placeholder="0">
                                        </div>

                                        {{-- Common Alert Quantity --}}
                                        <div class="col-md-3">
                                            <label class="form-label-pro text-danger">Low Stock Alert (Cartons)</label>
                                            <input type="number" class="form-control-pro border-danger" name="alert_carton_quantity" id="alert_carton_quantity" min="0" value="{{ $product->alert_carton_quantity }}" placeholder="e.g. 5">
                                        </div>
                                    </div>
                                </div>

                                {{-- Pricing Group --}}
                                <div class="col-md-12 pt-3 border-top">
                                    <h6 class="form-label-pro text-primary mb-2">Unit Price Settings (Rs.)</h6>
                                    <div class="row g-3">
                                         <div class="col-md-2">
                                             <label class="form-label-pro text-success">Sale Price <span class="unit-label text-muted fw-normal">(pc)</span></label>
                                             <input type="number" class="form-control-pro fw-bold text-success" name="sale_price_per_box" id="sale_price_per_box" step="0.01" value="{{ $product->sale_price_per_piece }}" placeholder="0.00">
                                         </div>
                                         <div class="col-md-2">
                                             <label class="form-label-pro text-info">Wholesale Price <span class="unit-label text-muted fw-normal">(pc)</span></label>
                                             <input type="number" class="form-control-pro fw-bold text-info" name="wholesale_price" id="wholesale_price" step="0.01" value="{{ $product->wholesale_price ?? 0 }}" placeholder="0.00">
                                         </div>
                                         <div class="col-md-2">
                                             <label class="form-label-pro text-secondary">Purchase Price <span class="unit-label text-muted fw-normal">(pc)</span></label>
                                             <input type="number" class="form-control-pro text-muted" name="purchase_price_per_piece" id="purchase_price_per_piece" step="0.01" value="{{ $product->purchase_price_per_piece }}" placeholder="0.00">
                                         </div>
                                         <div class="col-md-2 factor-col-main d-none">
                                             <label class="form-label-pro text-warning factor-label">Piece Weight (g)</label>
                                             <input type="number" class="form-control-pro text-warning" name="weight_per_piece" id="weight_per_piece" step="0.0001" value="{{ $product->weight_per_piece ?? 0 }}" placeholder="0.00">
                                         </div>
                                         <div class="col-md-2">
                                             <label class="form-label-pro">Sale Disc (%)</label>
                                             <input type="number" class="form-control-pro" name="sale_discount_percent" step="0.01" value="{{ $product->sale_discount_percent ?? 0 }}">
                                         </div>
                                         <div class="col-md-2">
                                             <label class="form-label-pro">Purch Disc (%)</label>
                                             <input type="number" class="form-control-pro" name="purchase_discount_percent" step="0.01" value="{{ $product->purchase_discount_percent ?? 0 }}">
                                         </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- INLINE ACTIONS ROW --}}
                    <div class="d-flex justify-content-end align-items-center bg-white p-3 rounded shadow-sm border mb-4 gap-2">
                        <a href="{{ route('product') }}" class="btn btn-outline-secondary px-4 py-2" style="border-radius: var(--radius-md); font-size: 0.9rem;">Cancel</a>
                        <button type="submit" class="btn btn-primary px-5 py-2 fw-bold" style="background: var(--primary); border: none; border-radius: var(--radius-md); font-size: 0.9rem;">
                            <i class="las la-check-circle me-1"></i> UPDATE PRODUCT
                        </button>
                    </div>

                </div>

            </div>

            {{-- HIDDEN BY-SIZE FORM CONTROLS FOR MIGRATION BACKWARD COMPATIBILITY --}}
            <div style="display:none !important;">
                <input type="number" name="height" id="height" step="0.01" value="{{ $product->height }}">
                <input type="number" name="width" id="width" step="0.01" value="{{ $product->width }}">
                <input type="number" name="wholesale_price" id="wholesale_price_hidden" step="0.01" value="0">
                <input type="number" name="weight_per_piece" id="weight_per_piece_hidden" step="0.0001" value="0">
                <input type="number" name="price_per_m2" id="price_per_m2" step="0.01" value="{{ $product->price_per_m2 }}">
                <input type="number" name="purchase_price_per_m2" id="purchase_price_per_m2" step="0.01" value="{{ $product->purchase_price_per_m2 }}">
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

    </div>
@endsection

@section('js')
    <script>
        var variantMode = 'standard';
        document.addEventListener('DOMContentLoaded', function() {
            // --- UI Elements ---
            const unitDropdown = document.getElementById('unit-dropdown');
            const form = document.getElementById('productForm');
 
            // Containers
            const grpBySize = document.querySelectorAll('.group-by-size');
            const grpLoose = document.querySelectorAll('.group-loose');
            const grpPieceOnly = document.querySelectorAll('.group-piece-only');
 
            function toggleGroup(els, hide) {
                els.forEach(el => hide ? el.classList.add('d-none') : el.classList.remove('d-none'));
            }
 
            // Labels
            const unitLabels = document.querySelectorAll('.unit-label');
 
            // --- Logic Update Mode ---
            function updateMode() {
                if(!unitDropdown) return;
                const mode = unitDropdown.value;
 
                // Hide ALL dynamic wrappers
                toggleGroup(grpBySize, true);
                toggleGroup(grpLoose, true);
                toggleGroup(grpPieceOnly, true);
 
                if (mode === 'by_cartons') {
                    toggleGroup(grpBySize, false);
                    toggleGroup(grpLoose, false);
                    unitLabels.forEach(l => l.innerText = '(pc)');
 
                    setRequired(['pieces_per_box', 'boxes_quantity', 'sale_price_per_box', 'purchase_price_per_piece'], true);
                    setRequired(['piece_quantity'], false);
 
                } else {
                    toggleGroup(grpPieceOnly, false);
                    unitLabels.forEach(l => l.innerText = '(' + (mode === 'by_pieces' ? 'pc' : (mode === 'by_meter' ? 'm' : 'kg')) + ')');
 
                    setRequired(['piece_quantity', 'sale_price_per_box', 'purchase_price_per_piece'], true);
                    setRequired(['pieces_per_box', 'boxes_quantity'], false);
                }
 
                calculate();
                toggleFactorColumns();
            }
            if (unitDropdown) $(unitDropdown).on('change', updateMode);

            function setRequired(ids, isReq) {
                ids.forEach(id => {
                    const el = document.getElementById(id);
                    if (el) isReq ? el.setAttribute('required', 'required') : el.removeAttribute('required');
                });
            }

            function calculate() {
                if(!unitDropdown) return;
                const mode = unitDropdown.value;

                const v = (id) => parseFloat(document.getElementById(id)?.value) || 0;
                let stock = 0;
                let saleVal = 0;

                if (mode === 'by_cartons') {
                    stock = (v('pieces_per_box') * v('boxes_quantity')) + v('loose_pieces');
                    saleVal = stock * v('sale_price_per_box');

                } else {
                    stock = v('piece_quantity');
                    saleVal = stock * v('sale_price_per_box');
                }

                setText('total_stock_display', stock);
                setText('sale_total_display', saleVal.toLocaleString(undefined, { minimumFractionDigits: 2 }));
            }

            function setText(id, val) {
                const el = document.getElementById(id);
                if (el) el.innerText = val;
            }

            function toggleFactorColumns() {
                if (!unitDropdown) return;
                const mode = unitDropdown.value;
                const showFactor = (mode === 'by_kg' || mode === 'by_meter');
                
                const headers = document.querySelectorAll('.factor-header');
                headers.forEach(h => {
                    if (showFactor && variantMode !== 'weight') {
                        h.classList.remove('d-none');
                        h.textContent = (mode === 'by_kg') ? 'Piece Weight (g)' : 'Piece Length (m)';
                    } else {
                        h.classList.add('d-none');
                    }
                });
                
                const cols = document.querySelectorAll('.factor-col');
                cols.forEach(c => {
                    if (showFactor && variantMode !== 'weight') {
                        c.classList.remove('d-none');
                    } else {
                        c.classList.add('d-none');
                        const inp = c.querySelector('input');
                        if (inp) inp.value = '0';
                    }
                });
 
                const mainFactorCol = document.querySelector('.factor-col-main');
                if (mainFactorCol) {
                    if (showFactor) {
                        mainFactorCol.classList.remove('d-none');
                        const lbl = mainFactorCol.querySelector('.factor-label');
                        if (lbl) lbl.textContent = (mode === 'by_kg') ? 'Piece Weight (g)' : 'Piece Length (m)';
                    } else {
                        mainFactorCol.classList.add('d-none');
                        const inp = mainFactorCol.querySelector('input');
                        if (inp) inp.value = '0';
                    }
                }
            }

            // Events
            form.querySelectorAll('input').forEach(i => i.addEventListener('input', calculate));

            updateMode();

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
                        if(ph) ph.classList.add('d-none');
                        clr.classList.remove('d-none');
                    };
                    r.readAsDataURL(this.files[0]);
                }
            });

            clr.addEventListener('click', (e) => {
                e.stopPropagation();
                imgInput.value = '';
                
                @if($product->image)
                    preview.src = "{{ asset('uploads/products/' . $product->image) }}";
                    preview.classList.remove('d-none');
                    if(ph) ph.classList.add('d-none');
                @else
                    preview.classList.add('d-none');
                    if(ph) ph.classList.remove('d-none');
                    clr.classList.add('d-none');
                @endif
            });

            // AJAX Submission
            form.addEventListener('submit', function(e) {
                e.preventDefault();
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
                 
                 if (vStocks.length > 0) {
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
                 } else {
                     setElVal('wholesale_price_hidden', document.getElementById('wholesale_price')?.value || 0);
                     setElVal('weight_per_piece_hidden', document.getElementById('weight_per_piece')?.value || 0);
                 }

                const btn = document.querySelector('button[type="submit"]');
                const originalContent = btn.innerHTML;
                btn.innerHTML = '<i class="las la-spinner la-spin"></i> Updating...';
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
                            icon: 'success', title: 'Updated!',
                            text: 'Product updated successfully', timer: 1500, showConfirmButton: false
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

            // Barcode
            const barIn = document.getElementById('barcodeInput');
            const barBtn = document.getElementById('generateBarcodeBtn');
            const barcodeUrl = '{{ route('generate-barcode-image') }}';
            
            if(barBtn) barBtn.addEventListener('click', () => fetch(barcodeUrl).then(r => r.json()).then(d => barIn.value = d.barcode_number));
            
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

            // Quick Add AJAX Handlers
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

            function updateVariantModeFromDropdown() {
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

            if (unitDropdown) {
                // Remove the old listener and add a wrapper that does both
                $(unitDropdown).off('change', updateMode);
                $(unitDropdown).on('change', function() {
                    updateMode();
                    updateVariantModeFromDropdown();
                    toggleFactorColumns();
                });
                toggleFactorColumns();
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
            productNameInput.addEventListener('input', function() {
                if (variantMode === 'weight') {
                    const baseInput = document.querySelector('input.base-name-input');
                    if (baseInput && !manualNames[baseInput.dataset.vid]) {
                        baseInput.value = this.value;
                    }
                }
            });

            function escapeHtml(str) {
                if (str === null || str === undefined) return '';
                return String(str)
                    .replace(/&/g, '&amp;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;');
            }

            function addBaseVariantRow(v = null) {
                const tr = document.createElement('tr');
                const productName = productNameInput ? productNameInput.value || '' : '';
                const baseUnitName = unitDropdown ? unitDropdown.options[unitDropdown.selectedIndex].text : 'Kg';
                const isCartonMode = unitDropdown && unitDropdown.value === 'by_cartons';
                const vid = 'base_' + Date.now() + '_' + Math.floor(Math.random() * 1000);
                
                const nameVal = v ? (v.name !== undefined && v.name !== null && v.name !== '' ? v.name : productName) : productName;
                const sizeVal = v ? (v.size || '') : '';
                const colorVal = v ? (v.color || '') : '';
                const unitVal = v ? (v.unit || (isCartonMode ? 'Carton' : baseUnitName)) : (isCartonMode ? 'Carton' : baseUnitName);
                const stockVal = (v && v.stock !== undefined && v.stock !== null && v.stock !== '') ? v.stock : '0';
                const convVal = (v && v.conv_factor !== undefined && v.conv_factor !== null && v.conv_factor !== '') ? v.conv_factor : (isCartonMode ? '0' : '1');
                const weightVal = (v && v.weight_per_piece !== undefined && v.weight_per_piece !== null && v.weight_per_piece !== '') ? v.weight_per_piece : 1000;
                const saleVal = (v && v.sale_price !== undefined && v.sale_price !== null) ? v.sale_price : '';
                const wholesaleVal = (v && v.wholesale_price !== undefined && v.wholesale_price !== null) ? v.wholesale_price : '0';
                const purchVal = (v && v.purch_price !== undefined && v.purch_price !== null) ? v.purch_price : '';
                const alertVal = (v && v.alert !== undefined && v.alert !== null) ? v.alert : '0';
                const barcodeVal = (v && v.barcode !== undefined && v.barcode !== null && v.barcode !== '') ? v.barcode : generateRandomBarcode();
                
                const uNorm = (unitVal || '').toLowerCase();
                
                tr.innerHTML = `
                    <td class="p-1 align-middle">
                        <input type="text" class="form-control-pro form-control-sm base-name-input fw-bold" name="variant_name[]" value="${escapeHtml(nameVal)}" placeholder="Name" data-vid="${vid}">
                        <input type="hidden" name="variant_is_base[]" value="1">
                    </td>
                    <td class="p-1"><input type="text" class="form-control-pro form-control-sm" name="variant_size[]" value="${escapeHtml(sizeVal)}" placeholder="Size"></td>
                    <td class="p-1"><input type="text" class="form-control-pro form-control-sm" name="variant_color[]" value="${escapeHtml(colorVal)}" placeholder="Color"></td>
                    <td class="p-1">
                        <select class="form-select form-select-sm fw-bold text-primary px-1" name="variant_unit[]" style="font-size:11px;">
                            <option value="Carton" ${uNorm==='carton'||(isCartonMode && !v)?'selected':''}>Carton</option>
                            <option value="Pcs" ${(!isCartonMode && (uNorm==='pcs'||uNorm==='piece'||uNorm==='pieces'||uNorm==='pc'))?'selected':''}>Pcs</option>
                            <option value="Kg" ${uNorm==='kg'?'selected':''}>Kg</option>
                            <option value="Gm" ${uNorm==='gm'||uNorm==='g'?'selected':''}>Gm</option>
                            <option value="Ft" ${uNorm==='ft'||uNorm==='feet'?'selected':''}>Ft</option>
                            <option value="Meter" ${uNorm==='meter'||uNorm==='mtr'||uNorm==='m'?'selected':''}>Mtr</option>
                            <option value="Box" ${uNorm==='box'?'selected':''}>Box</option>
                            <option value="Dozen" ${uNorm==='dozen'||uNorm==='dzn'?'selected':''}>Dzn</option>
                        </select>
                    </td>
                    <td class="p-1">
                        <input type="number" class="form-control-pro form-control-sm text-center fw-bold text-primary" name="variant_stock[]" step="any" value="${escapeHtml(stockVal)}" placeholder="0" title="${isCartonMode ? 'Initial Stock (Cartons)' : 'Initial Stock'}">
                    </td>
                    <td class="p-0 conv-col">
                        <input type="number" class="form-control-pro form-control-sm conv-factor-input text-center fw-bold ${isCartonMode ? 'text-primary' : ''}" name="variant_conv_factor[]" step="any" value="${escapeHtml(convVal)}" ${isCartonMode ? '' : 'readonly'} placeholder="0" title="${isCartonMode ? 'Pieces per Carton' : 'Base Conv Factor = 1'}" style="border-radius:0; border:1px solid #dee2e6; height:30px; ${isCartonMode ? 'background:#fff;' : 'background:#f8f8f8;'}">
                    </td>
                    <td class="p-0 piece-wt-only-col">
                        <div style="position:relative;">
                            <input type="number" class="form-control-pro form-control-sm" name="variant_weight_per_piece[]" step="any" value="${escapeHtml(weightVal)}" readonly title="Auto from Conv Factor" style="padding-right:18px; border-radius:0; border:1px solid #dee2e6; height:30px; background:#f8f8f8;">
                            <span style="position:absolute;right:5px;top:50%;transform:translateY(-50%);font-size:9px;color:#999;pointer-events:none;font-weight:600;">g</span>
                        </div>
                    </td>
                    <td class="p-1"><input type="number" class="form-control-pro form-control-sm base-sale-input" name="variant_sale_price[]" step="any" value="${escapeHtml(saleVal)}" placeholder="0.00" required></td>
                    <td class="p-1"><input type="number" class="form-control-pro form-control-sm" name="variant_wholesale_price[]" step="any" value="${escapeHtml(wholesaleVal)}" placeholder="0.00"></td>
                    <td class="p-1"><input type="number" class="form-control-pro form-control-sm base-purch-input" name="variant_purchase_price[]" step="any" value="${escapeHtml(purchVal)}" placeholder="0.00" required></td>
                    <td class="p-1"><input type="number" class="form-control-pro form-control-sm" name="variant_alert_qty[]" value="${escapeHtml(alertVal)}" placeholder="0"></td>
                    <td class="p-1"><input type="text" class="form-control-pro form-control-sm" name="variant_barcode[]" value="${escapeHtml(barcodeVal)}"></td>
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
                if (variantMode !== 'weight' && variantMode !== 'carton') return;

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
                    if (index === 0) return; // Skip base row
                    
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

            function addVariantRow(weightGrams = null, v = null) {
                const tr = document.createElement('tr');
                const vid = 'var_' + Date.now() + '_' + Math.floor(Math.random() * 1000);
                tr.dataset.vid = vid;
                
                const isCartonMode = unitDropdown && unitDropdown.value === 'by_cartons';
                let factor = v ? parseFloat(v.conv_factor || (isCartonMode ? 1000 : 1)) : 1;
                let suggestedName = v ? (v.name || '') : (productNameInput ? productNameInput.value || '' : '');
                
                if (weightGrams && !v) {
                    factor = parseFloat(weightGrams) > 10 ? parseFloat((weightGrams / 1000).toFixed(6)) : parseFloat(weightGrams);
                    suggestedName += ` - ${weightGrams}g`;
                }
                
                const baseRow = variantsBody.querySelector('tr');
                const baseSale = parseFloat(baseRow?.querySelector('.base-sale-input')?.value || document.querySelector('.base-sale-input')?.value || 0);
                const basePurch = parseFloat(baseRow?.querySelector('.base-purch-input')?.value || document.querySelector('.base-purch-input')?.value || 0);
                const baseWholesale = parseFloat(baseRow?.querySelector('input[name="variant_wholesale_price[]"]')?.value || 0);

                let suggSale = '';
                let suggPurch = '';
                let suggWholesale = '';

                if (v) {
                    suggSale = (v.sale_price !== undefined && v.sale_price !== null) ? v.sale_price : '';
                    suggPurch = (v.purch_price !== undefined && v.purch_price !== null) ? v.purch_price : '';
                    suggWholesale = (v.wholesale_price !== undefined && v.wholesale_price !== null) ? v.wholesale_price : '';
                } else if (variantMode === 'weight') {
                    suggSale = (baseSale * factor).toFixed(2);
                    suggPurch = (basePurch * factor).toFixed(2);
                    suggWholesale = (baseWholesale * factor).toFixed(2);
                } else if (isCartonMode) {
                    suggSale = baseSale ? baseSale.toFixed(2) : '';
                    suggPurch = basePurch ? basePurch.toFixed(2) : '';
                    suggWholesale = baseWholesale ? baseWholesale.toFixed(2) : '';
                }

                const sizeVal = v ? (v.size || '') : '';
                const colorVal = v ? (v.color || '') : '';
                const unitVal = v ? (v.unit || (isCartonMode ? 'Carton' : 'Pcs')) : (isCartonMode ? 'Carton' : 'Pcs');
                const stockVal = (v && v.stock !== undefined && v.stock !== null && v.stock !== '') ? v.stock : '0';
                const convVal = (v && v.conv_factor !== undefined && v.conv_factor !== null && v.conv_factor !== '') ? v.conv_factor : (isCartonMode ? '0' : '');
                const weightVal = (v && v.weight_per_piece !== undefined && v.weight_per_piece !== null && v.weight_per_piece !== '') ? v.weight_per_piece : (weightGrams || (factor < 10 ? (factor * 1000).toFixed(1).replace(/\.0$/, '') : factor));
                const alertVal = (v && v.alert !== undefined && v.alert !== null) ? v.alert : '0';
                const barcodeVal = (v && v.barcode !== undefined && v.barcode !== null && v.barcode !== '') ? v.barcode : generateRandomBarcode();
                
                const uNorm = (unitVal || '').toLowerCase();

                tr.innerHTML = `
                    <td class="p-1">
                        <input type="text" class="form-control-pro form-control-sm var-name-input" name="variant_name[]" value="${escapeHtml(suggestedName)}" placeholder="Name">
                        <input type="hidden" name="variant_is_base[]" value="0">
                    </td>
                    <td class="p-1"><input type="text" class="form-control-pro form-control-sm" name="variant_size[]" value="${escapeHtml(sizeVal)}" placeholder="Size (e.g. Small, 30cm)"></td>
                    <td class="p-1"><input type="text" class="form-control-pro form-control-sm" name="variant_color[]" value="${escapeHtml(colorVal)}" placeholder="Color"></td>
                    <td class="p-1">
                        <select class="form-select form-select-sm px-1 fw-bold text-dark" name="variant_unit[]" style="font-size:11px;">
                            <option value="Carton" ${uNorm==='carton'||(isCartonMode && !v)?'selected':''}>Carton</option>
                            <option value="Pcs" ${(!isCartonMode && (uNorm==='pcs'||uNorm==='piece'||uNorm==='pieces'||uNorm==='pc'))?'selected':''}>Pcs</option>
                            <option value="Kg" ${uNorm==='kg'?'selected':''}>Kg</option>
                            <option value="Gm" ${uNorm==='gm'||uNorm==='g'?'selected':''}>Gm</option>
                            <option value="Ft" ${uNorm==='ft'||uNorm==='feet'?'selected':''}>Ft</option>
                            <option value="Meter" ${uNorm==='meter'||uNorm==='mtr'||uNorm==='m'?'selected':''}>Mtr</option>
                            <option value="Box" ${uNorm==='box'?'selected':''}>Box</option>
                            <option value="Dozen" ${uNorm==='dozen'||uNorm==='dzn'?'selected':''}>Dzn</option>
                        </select>
                    </td>
                    <td class="p-1">
                        <input type="number" class="form-control-pro form-control-sm text-center fw-bold text-primary stock-input" name="variant_stock[]" step="any" value="${escapeHtml(stockVal)}" placeholder="0" title="${isCartonMode ? 'Initial Stock (Cartons)' : 'Initial Stock'}" ${variantMode === 'weight' ? 'readonly style="background:#f8f9ff;color:#0d6efd;font-weight:bold;"' : ''}>
                    </td>
                    <td class="p-0 conv-col">
                        <input type="text" inputmode="decimal" class="form-control-pro form-control-sm conv-factor-input text-center fw-bold text-success" name="variant_conv_factor[]" value="${escapeHtml(convVal)}" placeholder="0" title="${isCartonMode ? 'Pieces per Carton' : 'Conv Factor: weight per Pcs in base unit'}" style="border-radius:0; border:1px solid #198754; height:30px; border-width:1.5px;">
                    </td>
                    <td class="p-0 piece-wt-only-col">
                        <div style="position:relative;">
                            <input type="number" class="form-control-pro form-control-sm piece-wt-display" name="variant_weight_per_piece[]" step="any" value="${escapeHtml(weightVal)}" placeholder="—" readonly title="Auto = Conv Factor × 1000" style="padding-right:18px; border-radius:0; border:1px solid #dee2e6; height:30px; background:#f0fff4; color:#198754; font-weight:600;">
                            <span style="position:absolute;right:5px;top:50%;transform:translateY(-50%);font-size:9px;color:#198754;pointer-events:none;font-weight:700;">g</span>
                        </div>
                    </td>
                    <td class="p-1"><input type="number" class="form-control-pro form-control-sm sale-price-input" name="variant_sale_price[]" step="any" value="${escapeHtml(suggSale)}" placeholder="0.00" required></td>
                    <td class="p-1"><input type="number" class="form-control-pro form-control-sm" name="variant_wholesale_price[]" step="any" value="${escapeHtml(suggWholesale)}" placeholder="0.00"></td>
                    <td class="p-1"><input type="number" class="form-control-pro form-control-sm purch-price-input" name="variant_purchase_price[]" step="any" value="${escapeHtml(suggPurch)}" placeholder="0.00" required></td>
                    <td class="p-1"><input type="number" class="form-control-pro form-control-sm" name="variant_alert_qty[]" value="${escapeHtml(alertVal)}" placeholder="0"></td>
                    <td class="p-1"><input type="text" class="form-control-pro form-control-sm" name="variant_barcode[]" value="${escapeHtml(barcodeVal)}"></td>
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
                    if (row.querySelector('.base-name-input')) {
                        Swal.fire({icon: 'error', title: 'Cannot Delete', text: 'The base variant cannot be deleted.'});
                        return;
                    }

                    if (variantsBody.children.length > 1) {
                        row.remove();
                    } else {
                        row.querySelectorAll('input:not([type="hidden"])').forEach(inp => inp.value = '');
                        const bc = row.querySelector('input[name="variant_barcode[]"]');
                        if (bc) bc.value = generateRandomBarcode();
                    }
                } else if (genBtn) {
                    const input = genBtn.closest('td').querySelector('input');
                    input.value = generateRandomBarcode();
                }
            });

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

            // Initialize existing variants safely
            try {
                let parsed = @json($variants ?? []);
                if (typeof parsed === 'string') {
                    try {
                        parsed = JSON.parse(parsed);
                        if (typeof parsed === 'string') {
                            parsed = JSON.parse(parsed);
                        }
                    } catch(e) {
                        parsed = [];
                    }
                }
                
                // If parsed is an object with numeric or string keys, or a single variant
                if (parsed && typeof parsed === 'object' && !Array.isArray(parsed)) {
                    if (parsed.name) {
                        parsed = [parsed];
                    } else {
                        parsed = Object.values(parsed);
                    }
                }

                if (Array.isArray(parsed) && parsed.length > 0) {
                    const currentUnit = unitDropdown ? unitDropdown.value : 'by_pieces';
                    variantMode = isWeightUnit(currentUnit) ? 'weight' : 'standard';
                    
                    applyVariantModeUI();
                    
                    variantsContainer.classList.remove('d-none');
                    variantsBody.innerHTML = ''; // clear default empty row
                    
                    parsed.forEach((v, index) => {
                        if (typeof v === 'string') {
                            v = { name: v, color: v };
                        }
                        if (!v || typeof v !== 'object') return;

                        if (v.is_base_variant == 1 || (index === 0 && (variantMode === 'weight' || v.is_base_variant === undefined || v.is_base_variant === null))) {
                            addBaseVariantRow(v);
                        } else {
                            addVariantRow(null, v);
                        }
                    });
                }
            } catch(e) { console.error('Error loading variants:', e); }

            // Call updateMode to set initial visible states
            updateMode();

            // Mobile initial render if on mobile screen
            if (typeof isMobile === 'function' && isMobile() && typeof rebuildMobileCards === 'function') {
                rebuildMobileCards();
            }

        });

        // ============================================================
        //  MOBILE VARIANT CARDS SYSTEM
        //  Two-way sync: desktop table <-> mobile accordion cards
        // ============================================================

        const isMobile = () => window.innerWidth < 768;
        let mobVariantIndex = 0;

        function escapeHtml(str) {
            if (str === null || str === undefined) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');
        }

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
            const uNorm = (unitVal || '').toLowerCase();

            card.innerHTML = `
                <div class="mob-card-header" onclick="mobToggleCard(this)">
                    <div class="mob-card-title">
                        <i class="fas fa-layer-group" style="color:${isBase ? '#5B4CF7' : '#94a3b8'};font-size:14px;flex-shrink:0;"></i>
                        <span class="mob-card-label">${escapeHtml(label)}</span>
                        ${isBase ? '<span class="mob-base-badge">Base</span>' : ''}
                    </div>
                    <svg class="mob-chevron" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                </div>
                <div class="mob-card-body">
                    <div class="mob-section-label"><i class="fas fa-info-circle me-1"></i>Basic Info</div>
                    <div class="mob-field-group">
                        <div class="mob-label">Variant Name ${isBase ? '<span class="req">*</span>' : ''}</div>
                        <input type="text" class="mob-input mob-sync" data-field="variant_name[]" value="${escapeHtml(nameVal)}" placeholder="e.g. Red - XL" autocomplete="off">
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                        <div class="mob-field-group">
                            <div class="mob-label">Size</div>
                            <input type="text" class="mob-input mob-sync" data-field="variant_size[]" value="${escapeHtml(sizeVal)}" placeholder="XL, M...">
                        </div>
                        <div class="mob-field-group">
                            <div class="mob-label">Color</div>
                            <input type="text" class="mob-input mob-sync" data-field="variant_color[]" value="${escapeHtml(colorVal)}" placeholder="Red, Blue...">
                        </div>
                    </div>
                    <div class="mob-field-group">
                        <div class="mob-label">Unit</div>
                        <select class="mob-select mob-sync" data-field="variant_unit[]">
                            <option value="Carton" ${uNorm==='carton'?'selected':''}>Carton</option>
                            <option value="Pcs" ${uNorm==='pcs'||uNorm==='piece'||uNorm==='pieces'||uNorm==='pc'?'selected':''}>Pcs</option>
                            <option value="Kg" ${uNorm==='kg'?'selected':''}>Kg</option>
                            <option value="Gm" ${uNorm==='gm'||uNorm==='g'?'selected':''}>Gm</option>
                            <option value="Ft" ${uNorm==='ft'||uNorm==='feet'?'selected':''}>Ft</option>
                            <option value="Meter" ${uNorm==='meter'||uNorm==='mtr'||uNorm==='m'?'selected':''}>Mtr</option>
                            <option value="Box" ${uNorm==='box'?'selected':''}>Box</option>
                            <option value="Dozen" ${uNorm==='dozen'||uNorm==='dzn'?'selected':''}>Dzn</option>
                        </select>
                    </div>

                    <div class="mob-section-divider"></div>
                    <div class="mob-section-label"><i class="fas fa-boxes me-1"></i>Stock & Pricing</div>

                    <div class="mob-field-group">
                        <div class="mob-label">Initial Stock ${isBase ? '' : (isWeightMode ? '🔵 Auto' : '')}</div>
                        <input type="number" class="mob-input mob-sync ${!isBase && isWeightMode ? 'auto-field mob-stock-auto' : ''}" data-field="variant_stock[]" value="${escapeHtml(stockVal)}" placeholder="${!isBase && isWeightMode ? 'Auto' : '0'}" ${!isBase && isWeightMode ? 'readonly' : ''} step="any">
                    </div>

                    ${unitDropdown && unitDropdown.value === 'by_cartons' ? `
                    <div class="mob-field-group">
                        <div class="mob-label" style="color:#0284c7;font-weight:700;">📦 Pieces Per Carton</div>
                        <input type="number" class="mob-input conv-field mob-sync mob-conv-inp" data-field="variant_conv_factor[]" value="${escapeHtml(convVal || '0')}" placeholder="0" autocomplete="off" style="border:1.5px solid #0284c7;">
                    </div>
                    ` : ''}

                    ${isWeightMode && !isBase ? `
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                        <div class="mob-field-group">
                            <div class="mob-label" style="color:#059669;">🔢 Conv Factor</div>
                            <input type="text" inputmode="decimal" class="mob-input conv-field mob-sync mob-conv-inp" data-field="variant_conv_factor[]" value="${escapeHtml(convVal)}" placeholder="0.000" autocomplete="off">
                        </div>
                        <div class="mob-field-group">
                            <div class="mob-label" style="color:#059669;">⚖ Piece Wt (g)</div>
                            <div class="mob-suffix-wrap">
                                <input type="number" class="mob-input mob-sync mob-piecewt" data-field="variant_weight_per_piece[]" value="${escapeHtml(pieceWtVal)}" placeholder="—" readonly style="background:#f0fdf4;color:#059669;font-weight:700;">
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
                            <input type="number" class="mob-input mob-sync" data-field="variant_sale_price[]" value="${escapeHtml(saleVal)}" placeholder="0.00" step="any" required>
                        </div>
                        <div class="mob-field-group">
                            <div class="mob-label">Wholesale Price</div>
                            <input type="number" class="mob-input mob-sync" data-field="variant_wholesale_price[]" value="${escapeHtml(wsaleVal)}" placeholder="0.00" step="any">
                        </div>
                    </div>
                    <div class="mob-field-group">
                        <div class="mob-label">Purchase Price <span class="req">*</span></div>
                        <input type="number" class="mob-input mob-sync" data-field="variant_purchase_price[]" value="${escapeHtml(purchVal)}" placeholder="0.00" step="any" required>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                        <div class="mob-field-group">
                            <div class="mob-label">Alert Qty</div>
                            <input type="number" class="mob-input mob-sync" data-field="variant_alert_qty[]" value="${escapeHtml(alertVal)}" placeholder="0" step="any">
                        </div>
                        <div class="mob-field-group">
                            <div class="mob-label">Barcode</div>
                            <input type="text" class="mob-input mob-sync" data-field="variant_barcode[]" value="${escapeHtml(barcodeVal)}" placeholder="Scan or type...">
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
            if (typeof updateVariantStocksFromBase === 'function') {
                updateVariantStocksFromBase();
            }
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
            if (typeof addVariantRow === 'function') {
                addVariantRow();
            }
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
        const variantsBodyEl = document.getElementById('variantsBody');
        if (variantsBodyEl) {
            desktopObserver.observe(variantsBodyEl, { childList: true });
        }

        // Also rebuild when window resizes to mobile
        window.addEventListener('resize', () => {
            if (isMobile()) rebuildMobileCards();
        });

        // Make functions global
        window.mobileAddVariant = mobileAddVariant;
        window.mobToggleCard = mobToggleCard;
        window.mobDeleteCard = mobDeleteCard;
    </script>
@endsection
