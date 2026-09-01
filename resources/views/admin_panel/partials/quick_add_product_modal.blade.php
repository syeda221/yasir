{{-- ===== QUICK ADD PRODUCT MODAL ===== --}}
<div class="modal fade" id="quickAddProductModal" tabindex="-1" aria-labelledby="quickAddProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow border-0" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header bg-primary text-white py-2 px-3">
                <h6 class="modal-title fw-bold" id="quickAddProductModalLabel">
                    <i class="fas fa-bolt text-warning me-1"></i> Quick Add Product (Carton &amp; Pieces)
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="quickAddProductForm">
                @csrf
                <div class="modal-body p-3">
                    <div class="row g-2">
                        <!-- Product Name -->
                        <div class="col-md-8">
                            <label class="form-label fw-semibold small mb-1">Product Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" name="product_name" id="qap_product_name" required placeholder="Enter product name">
                        </div>

                        <!-- Unit / Size Mode -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small mb-1">Unit Type <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm fw-bold text-primary" name="size_mode" id="qap_size_mode" required>
                                <option value="by_cartons" selected>Carton (With Loose Pcs)</option>
                                <option value="by_pieces">Pieces / Standard (Pcs)</option>
                            </select>
                        </div>

                        <!-- Category -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small mb-1">Category <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm" name="category_id" id="qap_category" required>
                                <option value="">Select Category</option>
                            </select>
                        </div>

                        <!-- Sub Category -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small mb-1">Sub Category</label>
                            <select class="form-select form-select-sm" name="sub_category_id" id="qap_subcategory">
                                <option value="">Select Sub Category</option>
                            </select>
                        </div>

                        <!-- Brand -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small mb-1">Brand <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm" name="brand_id" id="qap_brand" required>
                                <option value="">Select Brand</option>
                            </select>
                        </div>

                        <!-- Carton Conversion (Pieces Per Box) -->
                        <div class="col-md-4" id="qap_ppb_wrap">
                            <label class="form-label fw-semibold small mb-1 text-primary">Pieces Per Carton (Pcs/Ctn) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control form-control-sm border-primary fw-bold" name="pieces_per_box" id="qap_ppb" value="12" min="1" placeholder="e.g. 12">
                        </div>

                        <!-- Purchase Prices -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small mb-1">Purchase Price / Pc (Rs)</label>
                            <input type="number" step="0.01" class="form-control form-control-sm" name="purchase_price_per_piece" id="qap_purch_price_pc" value="0" placeholder="0.00">
                        </div>
                        <div class="col-md-4" id="qap_purch_box_wrap">
                            <label class="form-label fw-semibold small mb-1 text-secondary">Purchase Price / Carton (Rs)</label>
                            <input type="number" step="0.01" class="form-control form-control-sm" name="purchase_price_per_box" id="qap_purch_price_box" value="0" placeholder="0.00">
                        </div>

                        <!-- Sale Prices -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small mb-1">Sale Price / Pc (Rs) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control form-control-sm fw-semibold" name="sale_price_per_piece" id="qap_sale_price_pc" value="0" placeholder="0.00" required>
                        </div>
                        <div class="col-md-4" id="qap_sale_box_wrap">
                            <label class="form-label fw-semibold small mb-1 text-success">Sale Price / Carton (Rs)</label>
                            <input type="number" step="0.01" class="form-control form-control-sm fw-bold text-success border-success" name="sale_price_per_box" id="qap_sale_price_box" value="0" placeholder="0.00">
                        </div>

                        <!-- Initial Stock (Cartons) -->
                        <div class="col-md-4" id="qap_boxes_wrap">
                            <label class="form-label fw-semibold small mb-1 text-primary">Initial Stock (Cartons)</label>
                            <input type="number" step="any" class="form-control form-control-sm border-primary text-primary fw-bold" name="boxes_quantity" id="qap_boxes_quantity" value="0" placeholder="e.g. 5 or 5.2">
                            <small class="text-muted" style="font-size: 0.68rem;">Use <code>5.2</code> for 5 cartons + 2 loose pcs</small>
                        </div>

                        <!-- Loose Pieces (Extra) -->
                        <div class="col-md-4" id="qap_loose_wrap">
                            <label class="form-label fw-semibold small mb-1 text-warning">Extra Loose Pcs</label>
                            <input type="number" class="form-control form-control-sm border-warning fw-bold" name="loose_pieces" id="qap_loose_pieces" value="0" placeholder="0">
                        </div>

                        <!-- Initial Stock for Pieces Mode -->
                        <div class="col-md-4" id="qap_pieces_wrap" style="display: none;">
                            <label class="form-label fw-semibold small mb-1 text-primary">Initial Stock (Pieces)</label>
                            <input type="number" step="any" class="form-control form-control-sm border-primary text-primary fw-bold" name="piece_quantity" id="qap_piece_quantity" value="0" placeholder="0">
                        </div>

                        <!-- Low Stock Alert -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small mb-1 text-muted">Low Stock Alert</label>
                            <input type="number" class="form-control form-control-sm" name="alert_carton_quantity" id="qap_alert_qty" value="0" placeholder="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2 px-3 bg-light border-top-0 d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary fw-bold" id="btnQuickSaveProduct">
                        <i class="fas fa-check me-1"></i> Save &amp; Add to Invoice
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Two-way price synchronization helpers
    function getPpb() {
        return Math.max(1, parseFloat($('#qap_ppb').val()) || 1);
    }

    // When ppb changes, recalc box prices
    $('#qap_ppb').on('input change', function() {
        let ppb = getPpb();
        let sPc = parseFloat($('#qap_sale_price_pc').val()) || 0;
        let pPc = parseFloat($('#qap_purch_price_pc').val()) || 0;
        if (sPc > 0) $('#qap_sale_price_box').val((sPc * ppb).toFixed(2));
        if (pPc > 0) $('#qap_purch_price_box').val((pPc * ppb).toFixed(2));
    });

    // Sale Price / Pc change -> update Sale Price / Box
    $('#qap_sale_price_pc').on('input', function() {
        if ($('#qap_size_mode').val() === 'by_cartons') {
            let ppb = getPpb();
            let pc = parseFloat($(this).val()) || 0;
            $('#qap_sale_price_box').val((pc * ppb).toFixed(2));
        }
    });

    // Sale Price / Box change -> update Sale Price / Pc
    $('#qap_sale_price_box').on('input', function() {
        if ($('#qap_size_mode').val() === 'by_cartons') {
            let ppb = getPpb();
            let box = parseFloat($(this).val()) || 0;
            $('#qap_sale_price_pc').val((box / ppb).toFixed(2));
        }
    });

    // Purchase Price / Pc change -> update Purchase Price / Box
    $('#qap_purch_price_pc').on('input', function() {
        if ($('#qap_size_mode').val() === 'by_cartons') {
            let ppb = getPpb();
            let pc = parseFloat($(this).val()) || 0;
            $('#qap_purch_price_box').val((pc * ppb).toFixed(2));
        }
    });

    // Purchase Price / Box change -> update Purchase Price / Pc
    $('#qap_purch_price_box').on('input', function() {
        if ($('#qap_size_mode').val() === 'by_cartons') {
            let ppb = getPpb();
            let box = parseFloat($(this).val()) || 0;
            $('#qap_purch_price_pc').val((box / ppb).toFixed(2));
        }
    });

    // Toggle fields based on size mode
    $('#qap_size_mode').on('change', function() {
        let mode = $(this).val();
        if (mode === 'by_pieces') {
            $('#qap_ppb_wrap').hide();
            $('#qap_purch_box_wrap').hide();
            $('#qap_sale_box_wrap').hide();
            $('#qap_boxes_wrap').hide();
            $('#qap_loose_wrap').hide();
            $('#qap_pieces_wrap').show();
            $('#qap_ppb').val(1);
        } else {
            $('#qap_ppb_wrap').show();
            $('#qap_purch_box_wrap').show();
            $('#qap_sale_box_wrap').show();
            $('#qap_boxes_wrap').show();
            $('#qap_loose_wrap').show();
            $('#qap_pieces_wrap').hide();
            if (parseFloat($('#qap_ppb').val()) <= 1) {
                $('#qap_ppb').val(12);
            }
        }
    });

    // Load categories, brands, and subcategories
    var $catSelect = $('#qap_category');
    var $brandSelect = $('#qap_brand');
    var $subCatSelect = $('#qap_subcategory');

    // Load categories if empty
    if ($catSelect.find('option').length <= 1) {
        $.get("{{ url('/get-categories') }}", function(data) {
            (data || []).forEach(function(cat) {
                $catSelect.append('<option value="'+ cat.id +'">'+ cat.name +'</option>');
            });
        });
    }

    // Load brands if empty
    if ($brandSelect.find('option').length <= 1) {
        $.get("{{ url('/get-brands') }}", function(data) {
            (data || []).forEach(function(brand) {
                $brandSelect.append('<option value="'+ brand.id +'">'+ brand.name +'</option>');
            });
        });
    }

    // Load subcategories when category changes
    $('#qap_category').on('change', function() {
        var categoryId = $(this).val();
        $subCatSelect.html('<option value="">Select Sub Category</option>');
        if (categoryId) {
            $.get("{{ url('/get-subcategories') }}/" + categoryId, function(data) {
                (data || []).forEach(function(sub) {
                    $subCatSelect.append('<option value="'+ sub.id +'">'+ sub.name +'</option>');
                });
            });
        }
    });

    // Focus product name on modal show
    $('#quickAddProductModal').on('shown.bs.modal', function () {
        $('#qap_product_name').focus();
    });

    // Submit Quick Add Product Form
    $('#quickAddProductForm').on('submit', function(e) {
        e.preventDefault();
        var $btn = $('#btnQuickSaveProduct');
        var originalHtml = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');

        $.ajax({
            url: "{{ route('store-product') }}",
            method: "POST",
            data: $(this).serialize(),
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            success: function(response) {
                $btn.prop('disabled', false).html(originalHtml);
                $('#quickAddProductForm')[0].reset();
                $('#quickAddProductModal').modal('hide');

                let p = response.product || {};
                let pName = p.item_name || 'Product';
                let pId = p.id;
                let pCode = p.item_code || '';

                // If on Sale page, auto-select newly created product
                if ($('#salesTableBody').length && pId) {
                    let $emptyRow = null;
                    $('#salesTableBody tr').each(function() {
                        let val = $(this).find('.product-id-hidden').val() || $(this).find('.product').val();
                        if (!val && !$emptyRow) {
                            $emptyRow = $(this);
                        }
                    });

                    if (!$emptyRow && typeof addSaleRow === 'function') {
                        addSaleRow();
                        $emptyRow = $('#salesTableBody tr:last');
                    } else if (!$emptyRow && typeof $('#btnAdd').trigger === 'function') {
                        $('#btnAdd').trigger('click');
                        $emptyRow = $('#salesTableBody tr:last');
                    }

                    if ($emptyRow) {
                        let displayText = pName + (pCode ? ' (SKU: ' + pCode + ')' : '');
                        let newOption = new Option(displayText, pId, true, true);
                        $emptyRow.find('.product').append(newOption).trigger('change');
                    }
                }

                // If on Purchase page, auto-select newly created product
                if ($('#purchaseItemsTable').length && pId) {
                    let $emptyRow = null;
                    $('#purchaseItemsTable tbody tr').each(function() {
                        let val = $(this).find('.product-select').val();
                        if (!val && !$emptyRow) {
                            $emptyRow = $(this);
                        }
                    });

                    if (!$emptyRow && typeof addPurchaseRow === 'function') {
                        addPurchaseRow();
                        $emptyRow = $('#purchaseItemsTable tbody tr:last');
                    }

                    if ($emptyRow) {
                        let displayText = pName + (pCode ? ' (SKU: ' + pCode + ')' : '');
                        let newOption = new Option(displayText, pId, true, true);
                        $emptyRow.find('.product-select').append(newOption).trigger('change');
                    }
                }

                if (typeof showAlert === 'function') {
                    showAlert('success', 'Product "' + pName + '" created and added successfully!');
                } else {
                    alert('Product "' + pName + '" created successfully!');
                }
            },
            error: function(xhr) {
                $btn.prop('disabled', false).html(originalHtml);
                var msg = 'Error adding product.';
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    msg = Object.values(xhr.responseJSON.errors).flat().join('\n');
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                alert('Error: ' + msg);
            }
        });
    });
});
</script>
@endpush

