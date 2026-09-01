

<script>
    let lastSelectedPriceMode = 'retail';
    /* =========================================
       SHARED SALES LOGIC (Add/Edit)
       ========================================= */

    // --- Helpers ---
    function pad(n) {
        return n < 10 ? '0' + n : n
    }

    function setNowStamp() {
        if ($('#entryDateTime').length) {
            const d = new Date();
            const dt =
                `${pad(d.getDate())}-${pad(d.getMonth()+1)}-${String(d.getFullYear()).slice(-2)} ${pad(d.getHours())}:${pad(d.getMinutes())}`;
            const dOnly = `${pad(d.getDate())}-${pad(d.getMonth()+1)}-${String(d.getFullYear()).slice(-2)}`;
            $('#entryDateTime').text('Entry Date_Time: ' + dt);
            $('#entryDate').text('Date: ' + dOnly);
        }
    }

    setNowStamp();
    setInterval(setNowStamp, 60 * 1000);

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('input[name="_token"]').val()
        }
    });

    function showAlert(type, msg) {
        const el = $('#alertBox');
        if (el.length) {
            el.removeClass('d-none alert-success alert-danger alert-warning').addClass('alert-' + type).text(msg)
                .show();
            setTimeout(() => el.addClass('d-none'), 3000);
        } else {
            // fallback if alertBox missing
            if (type === 'error') Swal.fire('Error', msg, 'error');
            else if (type === 'warning') Swal.fire('Warning', msg, 'warning');
            else Swal.fire('Info', msg, 'info');
        }
    }

    function toNum(v) {
        if (typeof v === 'number') return v;
        if (!v) return 0;
        // Handle thousands separator (comma)
        const str = v.toString().replace(/,/g, '');
        return parseFloat(str) || 0;
    }

    /* =========================================
       PRODUCT SELECT2
       ========================================= */
    function initProductSelect2($el) {
        $el.select2({
            placeholder: 'Search Product (Name / SKU / Barcode)',
            allowClear: true,
            width: '100%',
            ajax: {
                url: '{{ route('products.ajax.search') }}',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        term: params.term,
                        page: params.page || 1
                    };
                },
                processResults: function(data, params) {
                    params.page = params.page || 1;
                    return {
                        results: data.results,
                        pagination: {
                            more: data.pagination.more
                        }
                    };
                },
                cache: true
            },
            minimumInputLength: 0,
            templateResult: formatProduct,
            templateSelection: formatSelection
        });
    }

    function formatProduct(repo) {
        if (repo.loading) return repo.text;
        let stock = repo.stock !== undefined ? repo.stock : 0;
        let sku = repo.sku || 'N/A';
        let stockVal = parseFloat(repo.stock_pieces !== undefined ? repo.stock_pieces : repo.stock) || 0;
        let badgeClass = stockVal > 0 ? 'bg-success' : 'bg-danger';

        return $(`
        <div class="clearfix">
            <div class="float-start">
                <div class="fw-bold">${repo.name || repo.text}</div>
                <small class="text-muted">SKU: ${sku}</small>
            </div>
            <div class="float-end">
                <span class="badge ${badgeClass} rounded-pill">Stock: ${stock}</span>
            </div>
        </div>
    `);
    }

    function formatSelection(repo) {
        return repo.name || repo.text;
    }


    /* =========================================
       CORE LOGIC
       ========================================= */

    function updateRowIndexes() {
        $('#salesTableBody tr').each(function(index) {
            $(this).find('.row-index').text(index + 1);
        });
    }

    function addNewRow() {
        const isWholesale = lastSelectedPriceMode === 'wholesale';
        const btnText = isWholesale ? 'W' : 'R';
        const btnClass = isWholesale ? 'btn-outline-info' : 'btn-outline-success';
        const btnTitle = isWholesale ? 'Wholesale Mode' : 'Retail Mode';

        const rowHtml = `
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

    <!-- Qty cell with Sub-Unit toggle on Right and Left-Aligned Cursor -->
    <td style="width:95px;min-width:95px;" class="col-qty-wrapper">
      <div class="d-flex align-items-center gap-1">
        <input type="number" step="any" class="form-control carton-qty text-start" name="carton_qty[]" placeholder="0" min="0" value="" style="flex: 1; min-width: 0; height: 26px; font-size: 0.85rem; padding-left: 6px;">
        <button type="button" class="btn btn-sm btn-outline-primary qty-unit-toggle px-1 py-0 d-none" 
                data-unit-mode="main" title="Toggle Unit (Ctn ↔ Pcs / Kg ↔ Gm / Ft ↔ In)" style="font-size: 0.65rem; height: 26px; min-width: 28px; font-weight: 700; border-radius: 4px; flex-shrink: 0;">
          Kg
        </button>
      </div>
      <input type="hidden" class="hidden-sub-unit-mode" name="sub_unit_mode[]" value="main">
    </td>

    <!-- Loose Pieces -->
    <td style="width:70px;min-width:70px;" class="d-none">
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
      <input type="text" class="form-control total-pieces text-end input-readonly" name="total_pieces[]" readonly placeholder="0" tabindex="-1">
      <!-- Hidden qty field for backend compatibility -->
      <input type="hidden" class="sales-qty" name="qty[]" value="0">
    </td>
 
    <!-- Price/Piece (EDITABLE) -->
    <td class="col-price-p">
      <div class="d-flex align-items-center gap-1">
        <input type="text" class="form-control visible-price text-end" name="visible_price[]" placeholder="0" style="flex: 1; min-width: 0;">
        <button type="button" class="btn btn-sm ${btnClass} price-mode-row-toggle px-1 py-0" 
                data-mode="${lastSelectedPriceMode}" title="${btnTitle}" style="font-size: 0.65rem; height: 24px; min-width: 20px; font-weight: bold;">
          ${btnText}
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
      <input type="text" class="form-control sales-amount text-end input-readonly" name="total[]" value="0" readonly tabindex="-1">
      <input type="hidden" class="gross-amount">
    </td>

    <!-- ACTION -->
    <td class="col-action text-center">
      <button type="button" class="btn btn-sm btn-outline-danger del-row" tabindex="-1">&times;</button>
    </td>
  </tr>`;

        const $row = $(rowHtml);
        $('#salesTableBody').append($row);
        initProductSelect2($row.find('.product'));
        updateRowIndexes();
    }

    // --- Loading Data for Rows ---

    function fetchProductPrice($row, productId) {
        console.log('Fetching price for product:', productId);
        $.get('{{ route('get-price') }}', {
            product_id: productId
        }).done(function(pRes) {
            // Fill Item Code
            $row.find('.item-code-display').val(pRes.item_code || '');

            // Populate Fields
            // Store retail price (box price) in hidden field and visible if needed
             $row.find('.retail-price').val(pRes.retail_price || 0);
             $row.find('.wholesale-price').val(pRes.wholesale_price || 0);
             $row.find('.weight-per-piece').val(pRes.weight_per_piece || 0);
             
             let rowMode = $row.find('.price-mode-row-toggle').attr('data-mode') || 'retail';
             let wsPrice = parseFloat(pRes.wholesale_price) || 0;
             let rate = (rowMode === 'wholesale' && wsPrice > 0) ? wsPrice : (pRes.retail_price || 0);

             // Visible price logic: cartons defaults to carton price, others to unit price
             if (pRes.size_mode == "by_cartons") {
                 $row.find('.visible-price').val(pRes.sale_price_per_box || rate || (pRes.sale_price_per_piece * (pRes.pieces_per_box || 1)) || 0);
             } else if (pRes.size_mode == "by_pieces" || pRes.size_mode == "by_kg" || pRes.size_mode == "by_gm" || pRes.size_mode == "by_meter") {
                 $row.find('.visible-price').val(pRes.sale_price_per_piece || rate || 0);
             } else {
                 $row.find('.visible-price').val(pRes.price_per_m2 || rate || 0);
             }

             $row.find('.pack-qty').val(pRes.pieces_per_box || 1);
             $row.find('.price-per-piece').val($row.find('.visible-price').val() || 0);

            $row.find('.size-h').val(pRes.height || '-');
            $row.find('.size-w').val(pRes.width || '-');
            $row.find('.size-mode-text').val(pRes.size_mode || '-');

            // Set default discount
            $row.find('.discount-value').val(pRes.sale_discount_percent || 0);

            $row.data('size_mode', pRes.size_mode);
            $row.data('pieces_per_box', pRes.pieces_per_box || 1);
            $row.data('price_per_m2', pRes.price_per_m2 || 0);

            setupRowQtyToggle($row, pRes.size_mode);

            computeRow($row);
        }).fail(function(err) {
            console.error('Price fetch failed', err);
        });
    }

    function loadWarehousesForProduct($row, productId, preSelectId = null) {
        var $whInput = $row.find('.warehouse');
        if (!$whInput.val()) {
            $whInput.val("{{ auth()->user()->warehouse_id ?? 1 }}");
        }

        const variantStockVal = $row.find('.variant-stock-value').val();
        if (variantStockVal !== '' && variantStockVal !== undefined) {
            $row.find('.stock').val(variantStockVal);
            return;
        }

        $.get('{{ route('warehouses.get') }}', {
                product_id: productId
            })
            .done(function(warehouses) {
                var targetWhId = preSelectId || $whInput.val() || "{{ auth()->user()->warehouse_id ?? 1 }}";
                var validWarehouses = Array.isArray(warehouses) ? warehouses : [];
                var matched = validWarehouses.find(function(w) { return w.warehouse_id == targetWhId; });
                if (!matched && validWarehouses.length > 0) {
                    matched = validWarehouses[0];
                }

                if (matched) {
                    $whInput.val(matched.warehouse_id);
                    let disp;
                    const ppb = parseFloat(matched.ppb) || 1;

                    if ((matched.size_mode === 'by_cartons' || matched.size_mode === 'by_size') && ppb > 1) {
                        const boxes = Math.floor(matched.boxes || 0);
                        const loose = matched.stock % ppb;
                        disp = loose > 0 ? `${boxes}.${loose}` : boxes;
                    } else if (matched.size_mode === 'by_kg') {
                        const s = parseFloat(matched.stock) || 0;
                        if (s > 0 && s < 1) {
                            const gm = (s * 1000).toFixed(1);
                            disp = `${s.toFixed(2)} Kg (${gm} Gm)`;
                        } else {
                            disp = `${s.toFixed(2)} Kg`;
                        }
                    } else {
                        disp = matched.stock;
                    }
                    if (!$row.find('.stock').val()) {
                        $row.find('.stock').val(disp);
                    }
                }
            })
            .fail(function(xhr) {
                console.error('Warehouse fetch error:', xhr);
            });
    }


    // --- Calculation ---

    function computeRow($row, isManual = false) {
        const rp = toNum($row.find('.retail-price').val()); // Box Price
        const visiblePrice = toNum($row.find('.visible-price').val());
        const weightPerPiece = parseFloat($row.find('.weight-per-piece').val()) || 0;

        const m2_per_piece = parseFloat($row.find('.size-h').val() * $row.find('.size-w').val() / 10000);

        const sizeMode = $row.data('size_mode') || $row.find('.size-mode-text').val();
        const packQty = parseFloat($row.find('.pack-qty').val()) || 1;

        const rawQtyStr = ($row.find('.carton-qty').val() || '').toString().trim();
        const rawQty = parseFloat(rawQtyStr) || 0;
        const loosePcs = parseFloat($row.find('.loose-pcs-input').val()) || 0;
        const unitMode = $row.find('.qty-unit-toggle').attr('data-unit-mode') || 'main';

        // Unit Price from visible-price
        let unitPrice = toNum($row.find('.visible-price').val());

        let baseQty = rawQty;
        let pcsDisplay = rawQty;
        let gross = 0;

        if (sizeMode === 'by_cartons') {
            if (unitMode === 'pcs') {
                // Selling in Pieces: unitPrice is price per piece
                pcsDisplay = rawQty;
                baseQty = packQty > 0 ? (rawQty / packQty) : rawQty;
                gross = rawQty * unitPrice;
            } else {
                // Selling in Cartons: unitPrice is price per carton
                if (rawQtyStr.includes('.')) {
                    const parts = rawQtyStr.split('.');
                    const boxes = parseInt(parts[0]) || 0;
                    const loose = parseInt(parts[1]) || 0;
                    pcsDisplay = (boxes * packQty) + loose + loosePcs;
                    baseQty = packQty > 0 ? (pcsDisplay / packQty) : rawQty;
                    const piecePrice = packQty > 0 ? (unitPrice / packQty) : unitPrice;
                    gross = (boxes * unitPrice) + (loose * piecePrice) + (loosePcs * piecePrice);
                } else {
                    pcsDisplay = (rawQty * packQty) + loosePcs;
                    baseQty = rawQty;
                    const piecePrice = packQty > 0 ? (unitPrice / packQty) : unitPrice;
                    gross = (rawQty * unitPrice) + (loosePcs * piecePrice);
                }
            }
        } else if (sizeMode === 'by_kg' || sizeMode === 'by_gm') {
            if (unitMode === 'gm') {
                baseQty = rawQty / 1000;
                pcsDisplay = baseQty;
                gross = rawQty * unitPrice; // unitPrice is per Gm
            } else {
                baseQty = rawQty;
                pcsDisplay = rawQty;
                gross = rawQty * unitPrice; // unitPrice is per Kg
            }
        } else if (sizeMode === 'by_feet' || sizeMode === 'by_meter') {
            if (unitMode === 'in') {
                baseQty = rawQty / 12;
            } else {
                baseQty = rawQty;
            }
            pcsDisplay = baseQty.toFixed(3);
            gross = rawQty * unitPrice;
        } else if (sizeMode === 'by_size') {
            gross = m2_per_piece * baseQty * unitPrice;
            if (!m2_per_piece) gross = 0;
        } else {
            gross = baseQty * unitPrice;
        }

        // Sync hidden qty and total-pieces field for backend
        $row.find('.sales-qty').val(baseQty);
        $row.find('.total-pieces').val(pcsDisplay);
        $row.find('.price-per-piece').val(unitPrice);

        const discValue = toNum($row.find('.discount-value').val());
        const discType = $row.find('.discount-toggle').data('type');
        let dam = toNum($row.find('.discount-amount').val());

        // Discount Calculation
        if (discType === 'percent') {
            dam = discValue > 0 ? (gross * discValue) / 100 : 0;
        } else {
            dam = discValue > 0 ? discValue : 0;
        }
        $row.find('.discount-amount').val(dam.toFixed(2));

        const netRow = Math.max(0, gross - dam);
        $row.find('.gross-amount').val(gross.toFixed(2));
        $row.find('.sales-amount').val(netRow.toFixed(2));

        if (typeof updateGrandTotals === 'function') {
            updateGrandTotals();
        }
    }

    function updateGrandTotals() {
        let tQty = 0;
        let tGross = 0;
        let tLineDisc = 0;
        let tNet = 0;

        $('#salesTableBody tr').each(function() {
            const $r = $(this);
            let gross = toNum($r.find('.gross-amount').val());
            const net = toNum($r.find('.sales-amount').val());
            const dam = toNum($r.find('.discount-amount').val());
            
            if (gross <= 0 && net > 0) gross = net + dam;

            // Piece calc: use total-pieces field (already computed)
            const pieces = parseInt($r.find('.total-pieces').val()) || 0;

            tQty += pieces;
            tGross += gross;
            tLineDisc += dam;
            tNet += net;
        });

        const isWalkin = $('#is_walkin').val() === '1';
        
        let orderDisc = 0;
        if (isWalkin) {
            orderDisc = toNum($('#walkinDiscountRs').val());
            $('#discountPercent').val(0); // clear percent
        } else {
            const orderPct = toNum($('#discountPercent').val());
            orderDisc = (tNet * orderPct) / 100;
        }

        const prev = toNum($('#previousBalance').val());
        const receipts = toNum($('#receiptsTotal').text());
        const payable = Math.max(0, tNet - orderDisc + prev - receipts);
        const currentInvoiceTotal = Math.max(0, tNet - orderDisc);

        $('#tQty').text(tQty.toFixed(0));
        $('#tGross').text(tGross.toFixed(2));
        $('#tLineDisc').text(tLineDisc.toFixed(2));
        $('#tSub').text(tNet.toFixed(2));
        $('#tOrderDisc').text(orderDisc.toFixed(2));
        $('#tPrev').text(prev.toFixed(2));
        $('#tPayable').text(payable.toFixed(2));
        $('#totalAmount').text(tNet.toFixed(2));
        $('#walkinNetTotal').text(currentInvoiceTotal.toFixed(2));
        $('#bottomPaymentsTotal').text(receipts.toFixed(2));
        $('#receiptsTotalBadge').text(receipts.toFixed(2));
        $('#itemsRowCount').text($('#salesTableBody tr').length);

        // Display current bill total after all discounts
        $('#tCurrentBill').text(currentInvoiceTotal.toFixed(2));

        const walkinPaid = receipts;
        const change = walkinPaid - currentInvoiceTotal;
        const changeStr = change.toFixed(2);
        $('#walkinChange').text(changeStr);
        $('#bottomChangeVal').text(changeStr);
        $('#backendChange').val(change > 0 ? change.toFixed(2) : 0);
        if (change > 0) {
            $('#walkinChange, #bottomChangeVal').removeClass('text-warning text-danger').addClass('text-success');
            $('#changeAccountRow, #bottomChangeAccountWrapper, #editChangeAccountContainer').show().css('display', 'flex');
        } else if (change === 0) {
            $('#walkinChange, #bottomChangeVal').removeClass('text-warning text-danger').addClass('text-success');
            $('#changeAccountRow, #bottomChangeAccountWrapper, #editChangeAccountContainer').hide();
        } else {
            $('#walkinChange, #bottomChangeVal').removeClass('text-success text-warning').addClass('text-danger');
            $('#changeAccountRow, #bottomChangeAccountWrapper, #editChangeAccountContainer').hide();
        }

        $('#subTotal1').val(tGross.toFixed(2));
        $('#subTotal2').val(tNet.toFixed(2));
        $('#discountAmount').val(orderDisc.toFixed(2));
        $('#totalBalance').val(currentInvoiceTotal.toFixed(2));
        $('input[name="cash"]').val(receipts.toFixed(2));

        // Update Premium Horizontal Card Values
        // 1. Customer Name
        let customerName = "Select Customer";
        const customerVal = $('#customerSelect').val();
        if (customerVal) {
            const customerData = $('#customerSelect').select2 ? $('#customerSelect').select2('data') : null;
            if (customerData && customerData.length > 0 && customerData[0].customer) {
                customerName = customerData[0].customer.customer_name;
            } else {
                const text = $('#customerSelect').find(':selected').text() || '';
                const parts = text.split(' — ');
                customerName = parts.length > 1 ? parts[1] : (text || "Select Customer");
            }
        }
        $('#cc_customer_name').text(customerName);

        // 2. Previous Balance
        const prevAbs = Math.abs(prev);
        const prevSuffix = prev >= 0 ? 'Dr' : 'Cr';
        $('#cc_prev_bal_val').text('Rs ' + prevAbs.toLocaleString(undefined, {minimumFractionDigits: 0, maximumFractionDigits: 2}));
        $('#cc_prev_bal_suffix').text(prevSuffix);
        if (prev >= 0) {
            $('#cc_prev_bal_val, #cc_prev_bal_suffix').removeClass('text-success').addClass('text-danger');
        } else {
            $('#cc_prev_bal_val, #cc_prev_bal_suffix').removeClass('text-danger').addClass('text-success');
        }

        // 3. Current Bill
        $('#cc_current_bill').text('Rs ' + currentInvoiceTotal.toLocaleString(undefined, {minimumFractionDigits: 0, maximumFractionDigits: 2}));

        // 4. Paid Now
        $('#cc_paid_now').text('Rs ' + receipts.toLocaleString(undefined, {minimumFractionDigits: 0, maximumFractionDigits: 2}));

        // 5. Closing Balance
        const actualClosingBal = currentInvoiceTotal + prev - receipts;
        const closingAbs = Math.abs(actualClosingBal);
        const closingSuffix = actualClosingBal >= 0 ? 'Dr' : 'Cr';
        $('#cc_closing_bal_val').text('Rs ' + closingAbs.toLocaleString(undefined, {minimumFractionDigits: 0, maximumFractionDigits: 2}));
        $('#cc_closing_bal_suffix').text(closingSuffix);
        if (actualClosingBal >= 0) {
            $('#cc_closing_bal_val, #cc_closing_bal_suffix').removeClass('text-success').addClass('text-danger');
        } else {
            $('#cc_closing_bal_val, #cc_closing_bal_suffix').removeClass('text-danger').addClass('text-success');
        }
    }


    /* =========================================
       VALIDATION & SAVE
       ========================================= */

    function serializeForm() {
        return $('#saleForm').serialize();
    }

    function canPost() {
        let ok = false;
        $('#salesTableBody tr').each(function() {
            const pid = $(this).find('.product-id-hidden').val() || $(this).find('.product').val();
            const cartons = parseFloat($(this).find('.carton-qty').val()) || 0;
            const loose   = parseFloat($(this).find('.loose-pcs-input').val()) || 0;
            const pieces  = parseFloat($(this).find('.total-pieces').val()) || 0;
            const qty     = parseFloat($(this).find('.sales-qty').val()) || 0;
            if (pid && (cartons > 0 || loose > 0 || pieces > 0 || qty > 0)) {
                ok = true;
                return false;
            }
        });
        return ok;
    }

    function refreshPostedState() {
        const state = canPost();
        $('#btnPosted, #btnHeaderPosted').prop('disabled', !state);
    }

    function ensureSaved() {
        return new Promise(function(resolve, reject) {
            const existing = $('#booking_id').val();
            let url = '{{ route('sales.store') }}';
            let method = 'POST';
            if (existing) {
                url = '{{ route('sales.update', ':id') }}'.replace(':id', existing);
                method = 'PUT';
            }

            $('#btnSave, #btnHeaderPosted, #btnPosted').prop('disabled', true);

            $.ajax({
                url: url,
                type: method,
                data: serializeForm(),
                success: function(res) {
                    $('#btnSave, #btnHeaderPosted, #btnPosted').prop('disabled', false);
                    if (res?.ok) {
                        const bid = res.booking_id || existing;
                        $('#booking_id').val(bid);
                        if ($('#action').val() === 'booking') {
                            Swal.fire('Saved', 'Sale saved as booking successfully', 'success');
                        }
                        resolve(res);
                    } else {
                        Swal.fire('Error', res.msg || 'Save failed', 'error');
                        reject(res);
                    }
                },
                error: function(xhr) {
                    $('#btnSave, #btnHeaderPosted, #btnPosted').prop('disabled', false);
                    let errMsg = 'Save error';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errMsg = xhr.responseJSON.message;
                    } else if (xhr.responseText) {
                        try {
                            let resp = JSON.parse(xhr.responseText);
                            if (resp.message) errMsg = resp.message;
                        } catch(e) {}
                    }
                    Swal.fire('Error', errMsg, 'error');
                    reject(xhr);
                }
            });
        });
    }

    function postNow() {
        let formData = $('#saleForm').serializeArray();
        formData = formData.filter(item => item.name !== '_method');

        $.post('{{ route('sales.post_final') }}', $.param(formData))
            .done(function(res) {
                if (res?.ok) {
                    window.open(res.invoice_url, '_blank');
                    Swal.fire({
                        title: 'Success!',
                        text: 'Posted & invoice opened',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    setTimeout(() => window.location.href = "{{ route('sale.index') }}", 2000);
                } else {
                    Swal.fire('Post Failed', res.msg || 'Post failed', 'error');
                }
            })
            .fail(function(xhr) {
                let errMsg = 'Post error';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errMsg = xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    try {
                        let resp = JSON.parse(xhr.responseText);
                        if (resp.message) errMsg = resp.message;
                    } catch(e) {}
                }
                Swal.fire('Error', errMsg, 'error');
            });
    }

    // Validation Utils
    function markInvalid($el) {
        $el.addClass('invalid-input invalid-select');
        $el.closest('td').addClass('invalid-cell');
    }

    function clearInvalid($el) {
        $el.removeClass('invalid-input invalid-select');
        $el.closest('td').removeClass('invalid-cell');
    }

    function clearAllInvalids() {
        $('.invalid-input, .invalid-select').removeClass('invalid-input invalid-select');
        $('.invalid-cell').removeClass('invalid-cell');
    }

    function cleanupEmptyRows() {
        $('#salesTableBody tr').each(function() {
            const $r = $(this);
            const prod = $r.find('.product-id-hidden').val();
            const wh = $r.find('.warehouse').val();
            const cartons = parseInt($r.find('.carton-qty').val()) || 0;
            const loose   = parseInt($r.find('.loose-pcs-input').val()) || 0;
            const qty = cartons + loose;
            if ((qty <= 0) || ((!prod || prod === '') && (!wh || wh === ''))) {
                if ($('#salesTableBody tr').length > 1) {
                    $r.remove();
                } else {
                    // clear last row if needed
                    $r.find('select').val('');
                    $r.find('input').val('');
                    $r.find('.carton-qty').val(0);
                    $r.find('.loose-pcs-input').val(0);
                    $r.find('.stock').val('');
                    $r.find('.sales-amount').val('0');
                }
            }
        });
        if ($('#salesTableBody tr').length === 0) addNewRow();
    }

    function validateHeader() {
        let ok = true;
        let firstMessage = null;
        let firstEl = null;

        const isWalkin = $('#is_walkin').val() === '1';

        if (isWalkin) {
            const walkinName = $('#walkinNameInput').val().trim();
            if (!walkinName) {
                ok = false;
                firstMessage = 'Please enter a Walk-in Customer Name';
                firstEl = $('#walkinNameInput');
                markInvalid($('#walkinNameInput'));
            } else {
                $('#walkinNameInput').removeClass('is-invalid border-danger');
            }
        } else {
            const cust = $('#customerSelect').val();
            if (!cust) {
                ok = false;
                if (!firstMessage) {
                    firstMessage = 'Please select a Customer';
                    firstEl = $('#customerSelect');
                }
                markInvalid($('#customerSelect'));
            }
        }

        return {
            ok,
            firstMessage,
            firstEl
        };
    }

    function validateRows() {
        let ok = true;
        let firstMessage = null;
        let firstEl = null;

        $('#salesTableBody tr').each(function(rowIndex) {
            const $row = $(this);
            const $wh = $row.find('.warehouse');
            const $prod = $row.find('.product');
            const $cartonQtyInput = $row.find('.carton-qty');
            const $loosePcsInput = $row.find('.loose-pcs-input');
            const hiddenPid = $row.find('.product-id-hidden').val();
            const prodVal = $prod.val();

            if (!$wh.val()) {
                $wh.val("{{ auth()->user()->warehouse_id ?? 1 }}");
            }

            // If this row has no product selected:
            if (!prodVal && !hiddenPid) {
                // If there are other items in the table, remove this unselected row automatically
                if ($('#salesTableBody tr').length > 1) {
                    $row.remove();
                    return;
                }
                ok = false;
                if (!firstMessage) {
                    firstMessage = 'Bara-e-karam sale mein item select karein (Row ' + (rowIndex + 1) + ')';
                    firstEl = $prod;
                }
                markInvalid($prod);
                return;
            }

            const qtyVal = (parseFloat($cartonQtyInput.val()) || 0) + (parseFloat($loosePcsInput.val()) || 0);
            if (qtyVal <= 0) {
                ok = false;
                if (!firstMessage) {
                    firstMessage = 'Qty daalen row ' + (rowIndex + 1) + ' mein';
                    firstEl = $cartonQtyInput;
                }
                markInvalid($cartonQtyInput);
            }
        });

        return {
            ok,
            firstMessage,
            firstEl
        };
    }

    function validateReceipts() {
        let ok = true;
        let firstMessage = null;
        let firstEl = null;

        $('#rvWrapper .rv-row').each(function(i) {
            const $row = $(this);
            const $acc = $row.find('.rv-account');
            const $amt = $row.find('.rv-amount');
            const amtVal = parseFloat($amt.val() || '0') || 0;

            if (amtVal > 0 && (!$acc.val() || $acc.val() === "")) {
                ok = false;
                if (!firstMessage) {
                    firstMessage = 'Please select Account for receipt row ' + (i + 1);
                    firstEl = $acc;
                }
                markInvalid($acc);
            }
        });

        return {
            ok,
            firstMessage,
            firstEl
        };
    }

    function validateFormAll() {
        clearAllInvalids();

        const h = validateHeader();
        if (!h.ok) return {
            ok: false,
            message: h.firstMessage,
            el: h.firstEl
        };

        const r = validateRows();
        if (!r.ok) return {
            ok: false,
            message: r.firstMessage,
            el: r.firstEl
        };

        const rec = validateReceipts();
        if (!rec.ok) return {
            ok: false,
            message: rec.firstMessage,
            el: rec.firstEl
        };

        return {
            ok: true
        };
    }


    /* =========================================
       EVENT BINDINGS
    ========================================= */

    // Customer Type change logic -> controls Walk-in behavior
    function handleCustomerTypeChange() {
        const type = $('#partyTypeSelect').val();
        const isWalkin = (type === 'Walking Customer');
        
        // Update hidden field value
        $('#is_walkin').val(isWalkin ? '1' : '0');
        
        if (isWalkin) {
            $('#customerSelect').addClass('d-none').next('.select2-container').addClass('d-none');
            $('#walkinNameInput').removeClass('d-none');
            
            $('#receiptVouchersSection').hide();
            $('#totalsSection').removeClass('col-lg-5').addClass('col-lg-12');
            $('#totalsCustomerView').addClass('d-none').removeClass('d-flex');
            $('#totalsWalkinView').removeClass('d-none').addClass('d-flex');
            $('#rvWrapper').appendTo('#walkinReceiptsContainer');
        } else {
            $('#walkinNameInput').addClass('d-none');
            $('#customerSelect').removeClass('d-none').next('.select2-container').removeClass('d-none');
            
            $('#receiptVouchersSection').show();
            $('#totalsSection').removeClass('col-lg-12').addClass('col-lg-5');
            $('#totalsWalkinView').addClass('d-none').removeClass('d-flex');
            $('#totalsCustomerView').removeClass('d-none').addClass('d-flex');
            $('#rvWrapper').appendTo('#receiptVouchersSection .card-body');
        }
        if (typeof updateGrandTotals === 'function') updateGrandTotals();
    }

    $(document).on('change', '#partyTypeSelect', function() {
        handleCustomerTypeChange();
    });


    $(document).ready(function() {

        // Walk-in UI Event Bindings
        $(document).on('input', '#walkinDiscountRs', function() {
            updateGrandTotals();
        });
        
        // Ensure initial UI setup on page load
        handleCustomerTypeChange();

        // Remove invalid classes on input
        $(document).on('input change', 'select, input, textarea', function() {
            clearInvalid($(this));
        });

        // Product change
        $('#salesTableBody').on('select2:select', '.product', function(e) {
            console.log("product change in shared logic");
            if (window.isEditModeLoading) return; // Block during edit load
            
            const data = e.params.data;
            if (!data.id) return;
            
            const $row = $(this).closest('tr');
            
            let pid = data.id.toString().split('|')[0];
            $row.find('.product-id-hidden').val(pid);
            $row.find('.variant-data-hidden').val(data.variant_data || '');
            
            // Parse variant data for size/color display
            let variantSize = '-';
            let variantColor = '-';
            let variantStock = null;
            if (data.variant_data) {
                try {
                    const vd = JSON.parse(atob(data.variant_data));
                    variantSize = (vd.size && vd.size !== '-') ? vd.size : '-';
                    variantColor = (vd.color && vd.color !== '-') ? vd.color : '-';
                    // Prefer vd.current_stock from parsed variant_data (highly reliable), fallback to data.stock or vd.stock
                    variantStock = vd.current_stock !== undefined ? vd.current_stock : (data.stock !== undefined ? data.stock : (vd.stock !== undefined ? vd.stock : null));
                } catch(ex) {}
            }
            
            // Set size and color display
            $row.find('.size-display').val(variantSize !== '-' ? variantSize : '');
            $row.find('.color-display').val(variantColor);
            
            // Store variant stock for later use (after warehouse loads)
            if (variantStock !== null) {
                $row.find('.variant-stock-value').val(variantStock);
                $row.find('.stock').val(variantStock);
            } else {
                $row.find('.variant-stock-value').val('');
            }
            
            loadWarehousesForProduct($row, pid);
            
            // Set properties directly
            $row.find('.item-code-display').val(data.sku || '');
            $row.find('.retail-price').val(data.retail_price || data.trade_price || 0);
            $row.find('.wholesale-price').val(data.wholesale_price || 0);
            $row.find('.weight-per-piece').val(data.weight_per_piece || 0);
            
            let rowMode = $row.find('.price-mode-row-toggle').attr('data-mode') || 'retail';
            let wsPrice = parseFloat(data.wholesale_price) || 0;
            let rate = (rowMode === 'wholesale' && wsPrice > 0) ? wsPrice : (data.retail_price || data.trade_price || 0);

            $row.find('.visible-price').val(rate);
            $row.find('.pack-qty').val(data.pieces_per_box || 1);
            $row.find('.price-per-piece').val(rate);
            
            $row.find('.size-h').val(data.height || '-');
            $row.find('.size-w').val(data.width || '-');
            $row.find('.size-mode-text').val(data.size_mode || '-');
            
            $row.find('.discount-value').val(data.sale_discount_percent || 0);
            
            $row.data('size_mode', data.size_mode);
            $row.data('pieces_per_box', data.pieces_per_box || 1);
            
            setupRowQtyToggle($row, data.size_mode);

            computeRow($row);
        });

    function setupRowQtyToggle($row, sizeMode) {
        const $toggleBtn = $row.find('.qty-unit-toggle');
        if (sizeMode === 'by_cartons') {
            $toggleBtn.removeClass('d-none')
                      .attr('data-unit-mode', 'ctn')
                      .text('Ctn')
                      .removeClass('btn-outline-primary btn-outline-info btn-outline-warning')
                      .addClass('btn-outline-success');
            $row.find('.hidden-sub-unit-mode').val('ctn');
            $row.find('.carton-qty').attr('placeholder', '0');
        } else if (sizeMode === 'by_kg') {
            $toggleBtn.removeClass('d-none')
                      .attr('data-unit-mode', 'kg')
                      .text('Kg')
                      .removeClass('btn-outline-info btn-outline-warning btn-outline-success')
                      .addClass('btn-outline-primary');
            $row.find('.hidden-sub-unit-mode').val('kg');
            $row.find('.carton-qty').attr('placeholder', '0');
        } else if (sizeMode === 'by_gm') {
            $toggleBtn.removeClass('d-none')
                      .attr('data-unit-mode', 'gm')
                      .text('Gm')
                      .removeClass('btn-outline-primary btn-outline-warning btn-outline-success')
                      .addClass('btn-outline-info');
            $row.find('.hidden-sub-unit-mode').val('gm');
            $row.find('.carton-qty').attr('placeholder', '0');
        } else if (sizeMode === 'by_feet') {
            $toggleBtn.removeClass('d-none')
                      .attr('data-unit-mode', 'ft')
                      .text('Ft')
                      .removeClass('btn-outline-info btn-outline-warning btn-outline-success')
                      .addClass('btn-outline-primary');
            $row.find('.hidden-sub-unit-mode').val('ft');
            $row.find('.carton-qty').attr('placeholder', '0');
        } else if (sizeMode === 'by_meter') {
            $toggleBtn.removeClass('d-none')
                      .attr('data-unit-mode', 'm')
                      .text('Mtr')
                      .removeClass('btn-outline-info btn-outline-warning btn-outline-success')
                      .addClass('btn-outline-primary');
            $row.find('.hidden-sub-unit-mode').val('m');
            $row.find('.carton-qty').attr('placeholder', '0');
        } else {
            $toggleBtn.addClass('d-none').attr('data-unit-mode', 'main');
            $row.find('.hidden-sub-unit-mode').val('main');
            $row.find('.carton-qty').attr('placeholder', '0');
        }
    }

    // Toggle Qty Sub-Unit (Ctn ↔ Pcs / Kg ↔ Gm / Ft ↔ In)
    $(document).on('click', '.qty-unit-toggle', function () {
        const $btn = $(this);
        const $row = $btn.closest('tr');
        const sizeMode = $row.data('size_mode') || $row.find('.size-mode-text').val();
        let currentMode = $btn.attr('data-unit-mode');

        if (sizeMode === 'by_cartons') {
            const packQty = parseFloat($row.find('.pack-qty').val()) || 1;
            const $priceInp = $row.find('.visible-price');
            let curPrice = parseFloat($priceInp.val()) || 0;

            if (currentMode === 'ctn') {
                currentMode = 'pcs';
                $btn.text('Pcs').removeClass('btn-outline-success').addClass('btn-outline-info');
                $row.find('.carton-qty').attr('placeholder', '0');
                if (packQty > 1 && curPrice > 0) {
                    let piecePrice = curPrice / packQty;
                    $priceInp.val(piecePrice % 1 === 0 ? piecePrice : piecePrice.toFixed(2));
                    $row.find('.price-per-piece').val($priceInp.val());
                }
            } else {
                currentMode = 'ctn';
                $btn.text('Ctn').removeClass('btn-outline-info').addClass('btn-outline-success');
                $row.find('.carton-qty').attr('placeholder', '0');
                if (packQty > 1 && curPrice > 0) {
                    let cartonPrice = curPrice * packQty;
                    $priceInp.val(cartonPrice % 1 === 0 ? cartonPrice : cartonPrice.toFixed(2));
                    $row.find('.price-per-piece').val($priceInp.val());
                }
            }
        } else if (sizeMode === 'by_kg' || sizeMode === 'by_gm') {
            const $priceInp = $row.find('.visible-price');
            let curPrice = parseFloat($priceInp.val()) || 0;

            if (currentMode === 'kg') {
                currentMode = 'gm';
                $btn.text('Gm').removeClass('btn-outline-primary').addClass('btn-outline-info');
                $row.find('.carton-qty').attr('placeholder', 'Gm');
                if (curPrice > 0) {
                    let gmPrice = curPrice / 1000;
                    $priceInp.val(gmPrice.toFixed(4).replace(/\.?0+$/, ''));
                    $row.find('.price-per-piece').val($priceInp.val());
                }
            } else {
                currentMode = 'kg';
                $btn.text('Kg').removeClass('btn-outline-info').addClass('btn-outline-primary');
                $row.find('.carton-qty').attr('placeholder', 'Kg');
                if (curPrice > 0) {
                    let kgPrice = curPrice * 1000;
                    $priceInp.val(kgPrice % 1 === 0 ? kgPrice : kgPrice.toFixed(2));
                    $row.find('.price-per-piece').val($priceInp.val());
                }
            }
        } else if (sizeMode === 'by_feet' || sizeMode === 'by_meter') {
            if (currentMode === 'ft' || currentMode === 'm') {
                currentMode = 'in';
                $btn.text('In').removeClass('btn-outline-primary').addClass('btn-outline-warning');
                $row.find('.carton-qty').attr('placeholder', 'In');
            } else {
                currentMode = (sizeMode === 'by_feet') ? 'ft' : 'm';
                $btn.text(sizeMode === 'by_feet' ? 'Ft' : 'Mtr').removeClass('btn-outline-warning').addClass('btn-outline-primary');
                $row.find('.carton-qty').attr('placeholder', sizeMode === 'by_feet' ? 'Ft' : 'Mtr');
            }
        }

        $btn.attr('data-unit-mode', currentMode);
        $row.find('.hidden-sub-unit-mode').val(currentMode);
        computeRow($row);
        if (typeof updateGrandTotals === 'function') updateGrandTotals();
        if (typeof refreshPostedState === 'function') refreshPostedState();
    });

        // Warehouse change -> stock (respect variant stock)
        $('#salesTableBody').on('change', '.warehouse', function() {
            const $row = $(this).closest('tr');
            const variantStockVal = $row.find('.variant-stock-value').val();
            
            // If variant stock exists, use that instead of warehouse total
            if (variantStockVal !== '' && variantStockVal !== undefined) {
                $row.find('.stock').val(variantStockVal);
            } else {
                const stockPieces = $(this).find(':selected').data('ppb') || 0;
                $row.find('.stock').val(stockPieces);
            }
        });

        // Inputs -> Calc
        // --- Helper for Box Conversion ---
        window.normalizeQtyInput = function($input, $row) {
            const val = $input.val();
            const sizeMode = $row.data('size_mode');
            const ppb = parseFloat($row.find('.pack-qty').val()) || 1;

            if ((sizeMode === 'by_cartons' || sizeMode === 'by_size') && ppb > 1 && val.includes('.')) {
                const parts = val.split('.');
                const boxes = parseInt(parts[0]) || 0;
                const looseStr = parts[1];

                if (looseStr && looseStr !== '') {
                    const loose = parseInt(looseStr);
                    if (loose >= ppb) {
                        const extraBoxes = Math.floor(loose / ppb);
                        const newLoose = loose % ppb;
                        const newBoxes = boxes + extraBoxes;

                        let newVal = newBoxes.toString();
                        if (newLoose > 0) {
                            newVal += '.' + newLoose;
                        } // re-run
                        $input.val(newVal);
                    }
                }
            }
        };

        // $(document).ready(function() {

        // ... existing bindings ...

        // Inputs -> Calc
        $(document).on('input', '.carton-qty, .loose-pcs-input, .pack-qty, .discount-value, .visible-price',
            function() {
                // If user manually changes the visible price, also update the hidden price-per-piece
                if ($(this).hasClass('visible-price')) {
                    const $row = $(this).closest('tr');
                    const newPrice = toNum($(this).val());
                    $row.find('.price-per-piece').val(newPrice);
                    $row.find('.retail-price').val(newPrice);
                }

                computeRow($(this).closest('tr'));
                updateGrandTotals();
                refreshPostedState();
            });

        $(document).on('input', '.discount-amount', function() {
            computeRow($(this).closest('tr'), true);
            updateGrandTotals();
            refreshPostedState();
        });

        // Size Display Input Sync
        $(document).on('input change', '.size-display', function() {
            const $row = $(this).closest('tr');
            const newSize = $(this).val().trim();
            let variantData = {};
            const existingVd = $row.find('.variant-data-hidden').val();
            if (existingVd) {
                try {
                    const decoded = atob(existingVd);
                    variantData = JSON.parse(decoded);
                } catch(e) {
                    try {
                        variantData = JSON.parse(existingVd);
                    } catch(e2) {
                        variantData = { color: $row.find('.color-display').val() || '-' };
                    }
                }
            } else {
                variantData = { color: $row.find('.color-display').val() || '-' };
            }
            if (typeof variantData !== 'object' || variantData === null) {
                variantData = { color: $row.find('.color-display').val() || '-' };
            }
            variantData.size = newSize;
            $row.find('.variant-data-hidden').val(btoa(JSON.stringify(variantData)));
        });

        // Delete Row
        $(document).on('click', '.del-row', function() {
            if ($('#salesTableBody tr').length > 1) {
                $(this).closest('tr').remove();
                updateRowIndexes();
                updateGrandTotals();
                refreshPostedState();
            }
        });

        // Add Row Button
        $('#btnAdd').click(addNewRow);

        // Row Pricing Mode Toggle Handler
        $(document).on('click', '.price-mode-row-toggle', function() {
            const $btn = $(this);
            const $row = $btn.closest('tr');
            const currentMode = $btn.attr('data-mode') || 'retail';
            const newMode = currentMode === 'retail' ? 'wholesale' : 'retail';
            
            $btn.attr('data-mode', newMode);
            if (newMode === 'wholesale') {
                $btn.removeClass('btn-outline-success').addClass('btn-outline-info').text('W').attr('title', 'Wholesale Mode');
            } else {
                $btn.removeClass('btn-outline-info').addClass('btn-outline-success').text('R').attr('title', 'Retail Mode');
            }
            
            lastSelectedPriceMode = newMode;
            
            let retailPrice = parseFloat($row.find('.retail-price').val()) || 0;
            let wholesalePrice = parseFloat($row.find('.wholesale-price').val()) || 0;
            
            let rate = (newMode === 'wholesale' && wholesalePrice > 0) ? wholesalePrice : retailPrice;
            $row.find('.visible-price').val(rate);
            $row.find('.price-per-piece').val(rate);
            
            computeRow($row);
            updateGrandTotals();
        });

        // Enter on any editable input -> compute row, add new row & open product select
        $('#salesTableBody').on('keydown', '.carton-qty, .loose-pcs-input, .discount-value, .discount-amount, .visible-price', function(e) {
            if (e.key === 'Enter' || e.keyCode === 13) {
                e.preventDefault();
                const $current = $(this).closest('tr');
                computeRow($current);
                updateGrandTotals();

                // Add new row and open product dropdown
                addNewRow();
                setTimeout(() => $('#salesTableBody tr:last-child .product').select2('open'), 50);
            }
        });

        // Discount Toggle: % <-> PKR
        $(document).on('click', '.discount-toggle', function() {
            const $btn = $(this);
            const currentType = $btn.data('type');
            const newType = currentType === 'percent' ? 'pkr' : 'percent';
            $btn.data('type', newType).text(newType === 'percent' ? '%' : 'PKR');
            // Sync hidden input so form submission carries correct type
            $btn.closest('.discount-wrapper').find('.discount-type-hidden').val(newType);
            computeRow($btn.closest('tr'));
            updateGrandTotals();
        });

        // Buttons: Booking (Save)
        $('#btnSave').off('click').on('click', function() {
            cleanupEmptyRows();
            updateGrandTotals();
            refreshPostedState();

            const v = validateFormAll();
            if (!v.ok) {
                showAlert('warning', v.message);
                if (v.el && v.el.length) {
                    v.el.focus();
                    if (v.el.hasClass('js-customer')) v.el.select2?.('open');
                }
                return;
            }
            $('#action').val('booking');
            ensureSaved();
        });

        // Buttons: Sale (Post)
        $('#btnPosted, #btnHeaderPosted').off('click').on('click', function() {
            $('#action').val('sale');
            cleanupEmptyRows();
            updateGrandTotals();
            refreshPostedState();

            const v = validateFormAll();
            if (!v.ok) {
                showAlert('warning', v.message);
                if (v.el && v.el.length) {
                    v.el.focus();
                    if (v.el.hasClass('js-customer')) v.el.select2?.('open');
                }
                return;
            }

            // Walk-in 100% Payment Validation Check
            const isWalkin = $('#is_walkin').val() === '1';
            const invoiceNet = toNum($('#totalBalance').val());
            const paidNow = toNum($('#receiptsTotal').text());

            if (isWalkin) {
                // For walk-in, they must pay 100% upfront (with a small floating point tolerance)
                if (paidNow < (invoiceNet - 0.05)) {
                    Swal.fire({
                        icon: 'error',
                        title: '100% Payment Required',
                        text: 'Walk-in customers do not have a ledger. You must receive full payment to post this sale.',
                        confirmButtonColor: '#d33'
                    });
                    return;
                }
            }

            // Credit Limit Check
            // Credit Limit Check
            const rangeBal = toNum($('#rangeBalance').val());
            if (rangeBal > 0) {
                const prevBal = toNum($('#previousBalance').val());

                // Invoice Net Amount (Subtotal - Extra Discount)
                const invoiceNet = toNum($('#totalBalance').val());
                // Amount Paid Now
                const paidNow = toNum($('#receiptsTotal').text());

                // Projected Balance: Previous + New Debt - Payment
                const projectedBalance = prevBal + invoiceNet - paidNow;

                // Validate
                if (projectedBalance > rangeBal) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Credit Limit Exceeded',
                        html: `Projected Balance (<strong>${projectedBalance.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong>) exceeds Credit Limit (<strong>${rangeBal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong>)`,
                        confirmButtonColor: '#d33'
                    });
                    return;
                }
            }

            if (!canPost()) {
                showAlert('warning', 'No valid item lines to post.');
                return;
            }

            ensureSaved().then(function(res) {
                const id = (res && res.booking_id) ? res.booking_id : res;
                const thermalUrl = (res && res.receipt_url) ? res.receipt_url : ('{{ url('sales') }}/' + id + '/recepit');

                // Open thermal sale receipt directly
                window.open(thermalUrl, '_blank');

                Swal.fire({
                    title: 'Success!',
                    text: 'Sale Posted & Receipt Opened Successfully',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                });
                setTimeout(() => window.location.href = "{{ route('sale.index') }}", 1500);
            });
        });

        // Receipts Logic
        $(document).on('input', '.rv-amount', function() {
            if (typeof window.recomputeReceipts === 'function') window.recomputeReceipts();
        });

        $('#btnAddRV').on('click', function() {
            const row = `
              <div class="d-flex gap-2 align-items-center mb-2 rv-row">
                <select class="form-select rv-account" name="receipt_account_id[]" style="max-width:320px">
                  <option value="">Select account</option>
                </select>
                <input type="text" class="form-control text-end rv-amount" name="receipt_amount[]" placeholder="0.00" style="max-width:160px">
                <button type="button" class="btn btn-outline-danger btn-sm btnRemRV">&times;</button>
              </div>`;
            $('#rvWrapper').append(row);
            loadAccountsInto($('#rvWrapper .rv-account:last'));
        });

        $(document).on('click', '.btnRemRV', function() {
            $(this).closest('.rv-row').remove();
            if (typeof window.recomputeReceipts === 'function') window.recomputeReceipts();
        });

        // Sync Change Account Dropdowns
        $(document).on('change', '#changeAccountId', function() {
            const val = $(this).val();
            $('#bottomChangeAccountId, #editChangeAccountId').val(val);
        });
        $(document).on('change', '#bottomChangeAccountId', function() {
            const val = $(this).val();
            $('#changeAccountId, #editChangeAccountId').val(val);
        });
        $(document).on('change', '#editChangeAccountId', function() {
            const val = $(this).val();
            $('#changeAccountId, #bottomChangeAccountId').val(val);
        });


        // --- Customers & Accounts ---
        // We leave accountData here as a helper if available, but parent should ideally provide it.
        const accountData =
            @if (isset($accounts))
                @json($accounts)
            @else
                []
            @endif ;

        function loadAccountsInto($select, customerId) {
            const currentVal = $select.val();
            let options = '<option value="">Select account</option>';
            accountData.forEach(acc => {
                options += `<option value="${acc.id}">${acc.title}</option>`;
            });
            $select.html(options);
            if (currentVal) $select.val(currentVal);
        }

        window.recomputeReceipts = function() {
            let sum = 0;
            $('.rv-amount').each(function() {
                sum += toNum($(this).val());
            });
            $('#receiptsTotal').text(sum.toFixed(2));
            updateGrandTotals();
        }

        // --- Sidebar Direct Add Product ---
        $(document).on('click', '.add-product-direct-btn', function() {
            const prodId = $(this).data('id');
            if (!prodId) return;

            let $targetRow = $('#salesTableBody tr:last');
            const lastProdVal = $targetRow.find('.product').val();

            if (!$targetRow.length || lastProdVal) {
                addNewRow();
                $targetRow = $('#salesTableBody tr:last');
            }

            const $select = $targetRow.find('.product');
            const prodName = $(this).closest('.pos-product-card').find('.pos-product-name').text();
            if ($select.find(`option[value="${prodId}"]`).length === 0) {
                $select.append(new Option(prodName, prodId, true, true));
            } else {
                $select.val(prodId).trigger('change.select2');
            }
            $select.trigger('change');
        });

        // --- Sidebar Live Search ---
        $('#sidebarProductSearch').on('input', function() {
            const q = $(this).val().trim();
            if (q.length < 1) return;
            $.get('{{ route('search-product-name') }}', { q: q }).done(function(res) {
                let html = '';
                if (res && res.length > 0) {
                    res.forEach(p => {
                        const stockText = p.wh_stock ? `${p.wh_stock} Pcs` : '0 Pcs';
                        const priceText = p.retail_price ? parseFloat(p.retail_price).toFixed(2) : '0.00';
                        html += `
                            <div class="pos-product-card">
                                <div class="pos-product-img">
                                    <i class="fas fa-box text-secondary fs-5"></i>
                                </div>
                                <div class="pos-product-info">
                                    <div class="pos-product-name" title="${p.item_name}">${p.item_name}</div>
                                    <div class="pos-product-sub">
                                        <span class="badge-stock-green">${stockText}</span> SKU: ${p.item_code || '-'}
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="pos-product-price">${priceText}<br><small class="text-muted fs-7">PCS</small></div>
                                    <button type="button" class="pos-product-add-btn add-product-direct-btn" data-id="${p.id}" title="Add to Grid"><i class="fas fa-plus"></i></button>
                                </div>
                            </div>`;
                    });
                } else {
                    html = '<div class="text-center text-muted py-3" style="font-size:0.75rem;">No products found</div>';
                }
                $('#sidebarProductContainer').html(html);
            });
        });

        // --- Save & Complete (F9) Handler ---
        $('#btnSaveAndComplete, #btnHeaderSaveSale').on('click', function() {
            $('#btnPosted').trigger('click');
        });

        $(document).on('keydown', function(e) {
            if (e.key === 'F9') {
                e.preventDefault();
                $('#btnSaveAndComplete').trigger('click');
            }
        });

        // Failsafe trigger for Quick Add Product Modal
        $(document).on('click', '[data-bs-target="#quickAddProductModal"], [data-target="#quickAddProductModal"]', function(e) {
            e.preventDefault();
            $('#quickAddProductModal').modal('show');
        });

        // Initialize Posted Button State
        refreshPostedState();
    }); // Close $(document).ready
</script>
