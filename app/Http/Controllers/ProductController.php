<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Subcategory;
use App\Models\Unit;
use App\Models\WarehouseStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Milon\Barcode\DNS1D;

class ProductController extends Controller
{
    public function getPrice(Request $request)
    {
        $product = Product::find($request->product_id);

        if (! $product) {
            return response()->json(['retail_price' => 0]);
        }

        $ppb = $product->pieces_per_box > 0 ? (int)$product->pieces_per_box : 1;
        $salePiece = (float)($product->sale_price_per_piece ?? 0);
        $saleBox = (float)($product->sale_price_per_box ?? 0);
        if ($saleBox <= 0 && $salePiece > 0) {
            $saleBox = $salePiece * $ppb;
        }

        $purchPiece = (float)($product->purchase_price_per_piece ?? 0);
        $purchBox = (float)($product->purchase_price_per_box ?? 0);
        if ($purchBox <= 0 && $purchPiece > 0) {
            $purchBox = $purchPiece * $ppb;
        }

        // Determine price based on mode
        $price = 0;
        if ($product->size_mode === 'by_size') {
            $price = $product->price_per_m2;
        } elseif ($product->size_mode === 'by_cartons') {
            $price = $saleBox > 0 ? $saleBox : $salePiece;
        } else {
            $price = $salePiece > 0 ? $salePiece : $saleBox;
        }

        return response()->json([
            'retail_price'          => $price,
            'wholesale_price'       => (float)($product->wholesale_price ?? 0),
            'weight_per_piece'      => (float)($product->weight_per_piece ?? 0),
            'size_mode'             => $product->size_mode,
            'pieces_per_box'        => $ppb,
            'price_per_m2'          => $product->price_per_m2,
            'sale_price_per_box'    => $saleBox,
            'sale_price_per_piece'  => $salePiece,
            'purchase_price_per_box' => $purchBox,
            'purchase_price_per_piece' => $purchPiece,
            'height'                => $product->height,
            'width'                 => $product->width,
            'item_code'             => $product->item_code,
            'purchase_discount_percent' => $product->purchase_discount_percent ?? 0,
            'sale_discount_percent'     => $product->sale_discount_percent ?? 0,
        ]);
    }

    public function productget()
    {
        $products = Product::all();

        return response()->json($products);
    }

    private function upsertStocks(int $productId, float $qtyDelta, int $branchId = 1, int $warehouseId = 1): void
    {
        // Fallback to first warehouse if the requested one doesn't exist
        if ($warehouseId === 1 && !\App\Models\Warehouse::find(1)) {
            $warehouseId = \App\Models\Warehouse::first()->id ?? 1;
        }

        $stock = \App\Models\WarehouseStock::where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->lockForUpdate()
            ->first();

        if ($stock) {
            $stock->quantity += $qtyDelta;
            $stock->save();
        } else {
            \App\Models\WarehouseStock::create([
                'warehouse_id' => $warehouseId,
                'product_id' => $productId,
                'quantity' => $qtyDelta,
                'price' => 0,
            ]);
        }
    }

    // ===== High Performance Select2 Search (Ajax) =====
    public function ajaxSearch(Request $request)
    {
        $term = $request->get('term') ?? $request->get('q') ?? '';

        $query = Product::query()
            ->select('id', 'item_name', 'item_code', 'barcode_path', 'size_mode', 'unit_id', 'height', 'width', 'pieces_per_box', 'sale_price_per_box', 'purchase_price_per_box', 'purchase_price_per_m2', 'purchase_price_per_piece', 'pieces_per_m2', 'purchase_discount_percent', 'sale_discount_percent', 'color', 'sale_price_per_piece', 'wholesale_price', 'weight_per_piece')
            ->with(['unit'])
            ->withSum('warehouseStocks', 'total_pieces') /* Sum PIECES, not boxes */
            ->where('is_active', true) /* Only active products */
            ->where(function ($q) use ($term) {
                $q->where('item_name', 'like', "%{$term}%")
                    ->orWhere('item_code', 'like', "%{$term}%")
                    ->orWhere('barcode_path', 'like', "%{$term}%");
            });

        $products = $query->paginate(10); // Lazy loading (10 per request)

        $results = $products->getCollection()->flatMap(function ($p) {
            $stockPieces = (float) ($p->warehouse_stocks_sum_total_pieces ?? 0);
            $ppb = $p->pieces_per_box > 0 ? $p->pieces_per_box : 1;

            $unitName = $p->unit->name ?? match($p->size_mode) {
                'by_kg' => 'Kg',
                'by_gm' => 'Gm',
                'by_ton' => 'Ton',
                'by_meter' => 'Meter',
                'by_feet' => 'Ft',
                'by_cartons' => 'Carton',
                'by_size' => 'M²',
                default => 'Pcs',
            };

            $stockDisplay = $stockPieces;
            if (($p->size_mode === 'by_cartons' || $p->size_mode === 'by_size') && $ppb > 1) {
                $boxes = floor($stockPieces / $ppb);
                $loose = $stockPieces % $ppb;
                $stockDisplay = $loose > 0 ? "$boxes.$loose" : $boxes;
            }

            $variants = [];
            if ($p->color) {
                try {
                    $parsed = is_string($p->color) ? json_decode($p->color, true) : $p->color;
                    if (is_array($parsed) && count($parsed) > 0 && isset($parsed[0]['name'])) {
                        $variants = $parsed;
                    }
                } catch (\Exception $e) {}
            }

            if (count($variants) > 0) {
                // Fetch all sales and returns for this product to distribute
                $salesList = DB::table('sale_items')
                    ->where('product_id', $p->id)
                    ->select('total_pieces', 'color')
                    ->get();

                // Fetch confirmed web sales
                $webSalesList = DB::table('ecommerce_order_items as eoi')
                    ->join('ecommerce_orders as eo', 'eo.id', '=', 'eoi.ecommerce_order_id')
                    ->where('eoi.product_id', $p->id)
                    ->where('eo.is_stock_deducted', 1)
                    ->select('eoi.quantity as total_pieces', 'eoi.color', 'eoi.size')
                    ->get();

                $salesListArray = $salesList->toArray();
                foreach ($webSalesList as $wItem) {
                    $salesListArray[] = (object) [
                        'total_pieces' => $wItem->total_pieces,
                        'color' => json_encode([
                            'color' => $wItem->color ?: '-',
                            'size' => $wItem->size ?: '-'
                        ])
                    ];
                }
                $salesList = collect($salesListArray);

                $returnsList = DB::table('sale_return_items as sri')
                    ->join('sale_returns as sr', 'sr.id', '=', 'sri.sale_return_id')
                    ->where('sri.product_id', $p->id)
                    ->select('sri.qty', 'sri.color', 'sr.sale_id')
                    ->get();

                $saleIds = $returnsList->pluck('sale_id')->unique()->toArray();
                $saleItemsMap = [];
                if (!empty($saleIds)) {
                    $siList = DB::table('sale_items')
                        ->whereIn('sale_id', $saleIds)
                        ->where('product_id', $p->id)
                        ->select('sale_id', 'color')
                        ->get();
                    foreach ($siList as $si) {
                        $saleItemsMap[$si->sale_id][] = $si->color;
                    }
                }

                // Fetch all approved/returned purchases
                $purchasesList = DB::table('purchase_items as pi')
                    ->join('purchases as pur', 'pur.id', '=', 'pi.purchase_id')
                    ->where('pi.product_id', $p->id)
                    ->whereIn('pur.status_purchase', ['approved', 'Returned', 'Partial'])
                    ->select('pi.qty as total_pieces', 'pi.color')
                    ->get();

                // Fetch all purchase returns
                $purchaseReturnsList = DB::table('purchase_return_items as pri')
                    ->where('pri.product_id', $p->id)
                    ->select('pri.qty', 'pri.color')
                    ->get();

                $expanded = [];
                foreach ($variants as $v) {
                    $size = (isset($v['size']) && $v['size'] !== '-') ? " {$v['size']}" : '';
                    $color = (isset($v['color']) && $v['color'] !== '-') ? " ({$v['color']})" : '';
                    $vName = ($v['name'] ?? $p->item_name) . $size . $color;
                    
                    $vRawStock = (string) ($v['stock'] ?? '0');
                    if (($p->size_mode === 'by_cartons' || $p->size_mode === 'by_size') && $ppb > 1) {
                        if (strpos($vRawStock, '.') !== false) {
                            $parts = explode('.', $vRawStock);
                            $boxes = (int) ($parts[0] ?? 0);
                            $loose = (int) ($parts[1] ?? 0);
                            $initial = ($boxes * $ppb) + $loose;
                        } else {
                            $initial = (float) $vRawStock * $ppb;
                        }
                    } else {
                        $initial = (float) $vRawStock;
                    }
                    $vBalance = 0;

                    if (isset($v['conv_factor']) && $p->size_mode === 'by_kg') {
                        // Weight Unit: Calculate integer pieces based on total Kg divided by conversion factor
                        $factor = (float) $v['conv_factor'];
                        $factor = $factor > 0 ? $factor : 1;
                        if ($factor == 1) {
                            $vBalance = max(0, $stockPieces);
                        } else {
                            $vBalance = (int) floor(max(0, $stockPieces) / $factor);
                        }
                    } else {
                        // Calculate Purchased variant qty
                        $purchased = 0;
                        foreach ($purchasesList as $pItem) {
                            if ($this->matchSaleItemToVariant($pItem, $v)) {
                                $purchased += (float) $pItem->total_pieces;
                            }
                        }

                        // Calculate Purchase Returned variant qty
                        $pReturned = 0;
                        foreach ($purchaseReturnsList as $prItem) {
                            if ($this->matchSaleItemToVariant($prItem, $v)) {
                                $pReturned += (float) $prItem->qty;
                            }
                        }
                        
                        // Calculate Sold variant qty
                        $sold = 0;
                        foreach ($salesList as $sItem) {
                            if ($this->matchSaleItemToVariant($sItem, $v)) {
                                $sold += (float) $sItem->total_pieces;
                            }
                        }

                        // Calculate Returned variant qty
                        $returnedQty = 0;
                        foreach ($returnsList as $rItem) {
                            $rColor = $rItem->color;
                            if (empty($rColor)) {
                                $saleColors = $saleItemsMap[$rItem->sale_id] ?? [];
                                $rColor = !empty($saleColors) ? $saleColors[0] : '';
                            }
                            $rItemCopy = (object)[
                                'qty' => $rItem->qty,
                                'color' => $rColor
                            ];
                            if ($this->matchSaleItemToVariant($rItemCopy, $v)) {
                                $returnedQty += (float) $rItem->qty;
                            }
                        }

                        $vBalance = max(0, $initial + $purchased - $sold + $returnedQty - $pReturned);
                    }

                    $vStockDisplay = $vBalance;
                    $vUnitName = $v['unit'] ?? $unitName;

                    if (isset($v['conv_factor']) && $p->size_mode === 'by_kg') {
                        $factor = (float) $v['conv_factor'];
                        if ($factor != 1 && !isset($v['unit'])) {
                            $vUnitName = 'Pcs';
                        }
                        
                        if ($factor == 1) {
                            if ($vBalance < 0.001) {
                                $vStockDisplay = "0 {$vUnitName} (0 Gm)";
                            } elseif ($vBalance < 1 && $vBalance > 0) {
                                $gmVal = (int) round($vBalance * 1000);
                                $vStockDisplay = "{$vBalance} {$vUnitName} ({$gmVal} Gm)";
                            } else {
                                $vStockDisplay = "{$vBalance} {$vUnitName}";
                            }
                        } else {
                            $pcsCount = (int) floor($vBalance);
                            $vStockDisplay = "{$pcsCount}";
                        }
                    } elseif (($p->size_mode === 'by_cartons' || $p->size_mode === 'by_size') && $ppb > 1) {
                        $vBoxes = floor($vBalance / $ppb);
                        $vLoose = $vBalance % $ppb;
                        $vStockDisplay = $vLoose > 0 ? "$vBoxes.$vLoose" : $vBoxes;
                    }

                    $v['current_stock'] = $vStockDisplay;
                    $variantJson = json_encode($v);

                    $vSalePc = (float)($v['sale_price'] ?? $p->sale_price_per_piece ?? 0);
                    $vPurchPc = (float)($v['purch_price'] ?? $p->purchase_price_per_piece ?? 0);
                    $vSaleBox = $p->size_mode === 'by_cartons' ? ($vSalePc * $ppb) : $vSalePc;
                    $vPurchBox = $p->size_mode === 'by_cartons' ? ($vPurchPc * $ppb) : $vPurchPc;

                    $expanded[] = [
                        'id' => $p->id . '|variant|' . base64_encode($variantJson),
                        'text' => $vName." (SKU: {$p->item_code})",
                        'sku' => $p->item_code ?? '',
                        'stock' => $vStockDisplay,
                        'stock_pieces' => $vBalance,
                        'name' => $vName,
                        'size_mode' => $p->size_mode,
                        'unit_name' => $vUnitName,
                        'pieces_per_box' => $ppb,
                        'ppb' => $ppb,
                        'trade_price' => $p->size_mode === 'by_cartons' ? $vPurchBox : $vPurchPc,
                        'retail_price' => $p->size_mode === 'by_cartons' ? $vSaleBox : $vSalePc,
                        'wholesale_price' => $v['wholesale_price'] ?? $p->wholesale_price ?? 0,
                        'weight_per_piece' => $v['weight_per_piece'] ?? $p->weight_per_piece ?? 0,
                        'sale_price_per_piece' => $vSalePc,
                        'sale_price_per_box' => $vSaleBox,
                        'purchase_price_per_piece' => $vPurchPc,
                        'purchase_price_per_box' => $vPurchBox,
                        'purchase_price_per_m2' => $p->purchase_price_per_m2 ?? 0,
                        'sale_discount_percent' => $p->sale_discount_percent ?? 0,
                        'variant_data' => base64_encode($variantJson)
                    ];
                }
                return $expanded;
            }

            $pSalePc = (float)($p->sale_price_per_piece ?? 0);
            $pPurchPc = (float)($p->purchase_price_per_piece ?? 0);
            $pSaleBox = (float)($p->sale_price_per_box ?? 0);
            if ($pSaleBox <= 0 && $pSalePc > 0) $pSaleBox = $pSalePc * $ppb;
            $pPurchBox = (float)($p->purchase_price_per_box ?? 0);
            if ($pPurchBox <= 0 && $pPurchPc > 0) $pPurchBox = $pPurchPc * $ppb;

            return [[
                'id' => $p->id,
                'text' => $p->item_name." (SKU: {$p->item_code})",
                'sku' => $p->item_code ?? '',
                'stock' => $stockDisplay,
                'stock_pieces' => $stockPieces,
                'name' => $p->item_name,
                'size_mode' => $p->size_mode,
                'unit_name' => $unitName,
                'pieces_per_box' => $ppb,
                'ppb' => $ppb,
                'trade_price' => $p->size_mode === 'by_cartons' ? $pPurchBox : $pPurchPc,
                'retail_price' => $p->size_mode === 'by_cartons' ? $pSaleBox : $pSalePc,
                'wholesale_price' => $p->wholesale_price ?? 0,
                'weight_per_piece' => $p->weight_per_piece ?? 0,
                'sale_price_per_piece' => $pSalePc,
                'sale_price_per_box' => $pSaleBox,
                'purchase_price_per_piece' => $pPurchPc,
                'purchase_price_per_box' => $pPurchBox,
                'purchase_price_per_m2' => $p->purchase_price_per_m2 ?? 0,
                'sale_discount_percent' => $p->sale_discount_percent ?? 0,
                'variant_data' => ''
            ]];
        });

        return response()->json([
            'results' => $results,
            'pagination' => ['more' => $products->hasMorePages()],
        ]);
    }

    // ===== Product search (general) =====
    public function searchProducts(Request $request)
    {
        $term = $request->get('q', '');

        $products = Product::with('category_relation', 'sub_category_relation', 'brand')
            ->withSum('warehouseStocks', 'total_pieces')
            ->where('is_active', true) /* Only active products */
            ->when($term, function ($query) use ($term) {
                $query->where('item_name', 'like', "%{$term}%")
                    ->orWhere('item_code', 'like', "%{$term}%")
                    ->orWhereHas('category_relation', fn ($q) => $q->where('name', 'like', "%{$term}%"))
                    ->orWhereHas('sub_category_relation', fn ($q) => $q->where('name', 'like', "%{$term}%"))
                    ->orWhereHas('brand', fn ($q) => $q->where('name', 'like', "%{$term}%"));
            })
            ->limit(500) // limit for performance
            ->get();

        return response()->json($products->map(function ($p, $key) {
            $stockPieces = (float) ($p->warehouse_stocks_sum_total_pieces ?? 0);

            // Calculate Stock Display (Boxes vs Pieces)
            $stockDisplay = $stockPieces;
            $ppb = $p->pieces_per_box > 0 ? $p->pieces_per_box : 1;

            if (($p->size_mode === 'by_cartons' || $p->size_mode === 'by_size') && $p->pieces_per_box > 0) {
                $boxes = floor($stockPieces / $ppb);
                $loose = $stockPieces % $ppb;
                $stockDisplay = $loose > 0 ? "$boxes.$loose" : $boxes;
            }

            return [
                'id' => $p->id,
                'item_code' => $p->item_code,
                'item_name' => $p->item_name,
                'image' => $p->image ? asset('uploads/products/'.$p->image) : null,
                'category_name' => $p->category_relation->name ?? '-',
                'sub_category_name' => $p->sub_category_relation->name ?? '-',
                'height' => $p->height ?? null,
                'width' => $p->width ?? null,
                'pieces_per_box' => $ppb,
                'size_mode' => $p->size_mode,
                'stock' => $stockDisplay,
                'trade_price' => $p->purchase_price_per_piece ?? 0,
                'total_m2' => number_format($p->total_m2 ?? 0, 2),
                'price_per_m2' => number_format($p->price_per_m2 ?? 0, 2),
                'total_price' => number_format($p->total_price ?? 0, 2),
                'brand_name' => $p->brand->name ?? '-',
            ];
        }));
    }

    // ===== List page =====
    public function product(Request $request)
    {
        $query = Product::with([
            'category_relation',
            'sub_category_relation',
            'unit',
            'brand',
        ])->withSum('warehouseStocks', 'total_pieces');

        // ── Filters ──
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('item_name', 'like', "%$s%")
                  ->orWhere('item_code', 'like', "%$s%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active' ? 1 : 0);
        }

        $products   = $query->latest()->paginate(20)->withQueryString();
        $categories = Category::orderBy('name')->get();
        $brands     = Brand::orderBy('name')->get();

        return view('admin_panel.product.index', compact('products', 'categories', 'brands'));
    }

    public function productview($id)
    {
        $product = Product::with([
            'category_relation',
            'sub_category_relation',
            'brand',
            'unit',
            'warehouseStocks',
        ])->find($id);

        if (! $product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        // Calculate derived fields
        $totalPieces = $product->warehouseStocks->sum('total_pieces');
        $ppb = $product->pieces_per_box > 0 ? $product->pieces_per_box : 1;

        $boxes = 0;
        $loose = 0;

        if ($product->size_mode === 'by_cartons' || $product->size_mode === 'by_size') {
            $boxes = floor($totalPieces / $ppb);
            $loose = $totalPieces % $ppb;
        } else {
            // For by_pieces, boxes is essentially the piece count if we treat it largely
            // But strict interpretation:
            $boxes = $totalPieces;
            $loose = 0;
        }

        // Append these purely for the view (not saved in DB)
        $product->setAttribute('calculated_total_stock_qty', $totalPieces);
        $product->setAttribute('calculated_boxes_quantity', $boxes);
        $product->setAttribute('calculated_loose_pieces', $loose);

        return response()->json($product);
    }

    // //////////////////////

    // /////////////////////////

    // ===== Create page =====
    public function view_store()
    {
        $categories = Category::select('id', 'name')->get();
        $units = Unit::select('id', 'name')->get();
        $brands = Brand::select('id', 'name')->get();

        return view('admin_panel.product.create', compact('categories', 'units', 'brands'));
    }

    // ===== Dependent subcategories =====
    public function getSubcategories($category_id)
    {
        $subcategories = Subcategory::where('category_id', $category_id)->get();

        return response()->json($subcategories);
    }

    public function getAllSubcategoriesJson()
    {
        return response()->json(Subcategory::orderBy('name')->get(['id', 'name']));
    }

    public function getCategoriesJson()
    {
        return response()->json(Category::orderBy('name')->get(['id', 'name']));
    }

    public function getBrandsJson()
    {
        return response()->json(Brand::orderBy('name')->get(['id', 'name']));
    }

    // ===== Barcode =====
    public function generateBarcode(Request $request)
    {
        $barcodeNumber = $request->filled('code') ? $request->code : rand(100000000000, 999999999999);
        $barcodePNG = (new DNS1D)->getBarcodePNG($barcodeNumber, 'C39', 3, 50);
        $barcodeImage = 'data:image/png;base64,'.$barcodePNG;

        return response()->json([
            'barcode_number' => $barcodeNumber,
            'barcode_image' => $barcodeImage,
        ]);
    }

    // ===== Store product =====
    // ===== Store product =====
    public function store_product(Request $request)
    {
        if (! Auth::id()) {
            return $request->wantsJson()
                ? response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401)
                : redirect()->route('login');
        }

        // 1. Validate
        $validation = $this->validateProductRequest($request);
        if ($validation->fails()) {
            if ($request->wantsJson()) {
                return response()->json(['status' => 'error', 'errors' => $validation->errors()], 422);
            }

            return redirect()->back()->withErrors($validation)->withInput();
        }

        $mode = $request->size_mode;

        // Initialize variables
        $height = 0;
        $width = 0;
        $piecesPerBox = 0;
        $boxesQuantity = 0;
        $loosePieces = 0;
        $pieceQuantity = 0;

        $totalM2 = 0;
        $totalStockQty = 0;
        $m2PerPiece = 0;
        $piecesPerM2 = 0; // New: How many pieces fit in 1 m²

        // Pricing Vars
        $pricePerM2 = 0;
        $purchasePricePerM2 = 0;

        $salePricePerBox = 0;
        $purchasePricePerPiece = 0;
        $purchasePricePerBox = 0;
        $salePricePerPiece = 0;

        $totalPrice = 0;
        $totalPurchasePrice = 0;

        if ($mode === 'by_size') {
            // By Size Mode
            $height = (float) $request->height;
            $width = (float) $request->width;
            $piecesPerBox = (int) $request->pieces_per_box;
            $boxesQuantity = (int) $request->boxes_quantity;
            // No loose pieces in by_size usually, but if needed add here

            // Pricing inputs
            $pricePerM2 = (float) $request->price_per_m2;
            $purchasePricePerM2 = (float) $request->purchase_price_per_m2;

            $m2PerPiece = ($height * $width) / 10000;
            $m2PerBox = $m2PerPiece * $piecesPerBox;
            $totalM2 = $m2PerBox; // Storing m2 per box as requested instead of total stock m2

            // Store m2 per piece (0.72) directly as requested, even though column name is pieces_per_m2
            $piecesPerM2 = $m2PerPiece;

            $totalStockQty = $boxesQuantity * $piecesPerBox; // Store in Pieces

            // Prices calculated from m²
            $salePricePerPiece = $m2PerPiece * $pricePerM2;
            $salePricePerBox = $m2PerBox * $pricePerM2;
            $purchasePricePerPiece = $m2PerPiece * $purchasePricePerM2;
            $purchasePricePerBox = $m2PerBox * $purchasePricePerM2;

        } elseif ($mode === 'by_cartons') {
            // By Cartons Mode
            $piecesPerBox = (int) $request->pieces_per_box;
            if ($piecesPerBox <= 0) $piecesPerBox = 1;
            
            $boxesQuantityRaw = (string) $request->boxes_quantity;
            $loosePieces = (int) $request->loose_pieces;
            
            if (strpos($boxesQuantityRaw, '.') !== false) {
                $parts = explode('.', $boxesQuantityRaw);
                $boxesQuantity = (int)($parts[0] ?? 0);
                $looseFromDec = (int)($parts[1] ?? 0);
                $totalStockQty = ($piecesPerBox * $boxesQuantity) + $looseFromDec + $loosePieces;
            } else {
                $boxesQuantity = (int) $request->boxes_quantity;
                $totalStockQty = ($piecesPerBox * $boxesQuantity) + $loosePieces;
            }

            $inputSalePc = (float) $request->sale_price_per_box; // Actually per piece input in this mode
            $inputPurchPc = (float) $request->purchase_price_per_piece;

            $salePricePerPiece = $inputSalePc;
            $salePricePerBox = $inputSalePc * $piecesPerBox;

            $purchasePricePerPiece = $inputPurchPc;
            $purchasePricePerBox = $inputPurchPc * $piecesPerBox;

        } else {
            // Treat by_pieces, by_kg, by_meter, by_gm as piece-based mode
            $pieceQuantity = (int) $request->piece_quantity;
            $piecesPerBox = 1;
            $boxesQuantity = $pieceQuantity;
            $totalStockQty = $pieceQuantity;

            $inputSalePc = (float) $request->sale_price_per_box;
            $inputPurchPc = (float) $request->purchase_price_per_piece;

            $salePricePerPiece = $inputSalePc;
            $salePricePerBox = $inputSalePc;
            $purchasePricePerPiece = $inputPurchPc;
            $purchasePricePerBox = $inputPurchPc;
        }

        $userId = Auth::id();

        // Auto item_code
        $lastProduct = Product::orderBy('id', 'desc')->first();
        $nextCode = $lastProduct ? ('ITEM-'.str_pad($lastProduct->id + 1, 4, '0', STR_PAD_LEFT)) : 'ITEM-0001';

        // Image upload
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time().'.'.$file->getClientOriginalExtension();
            $file->move(public_path('uploads/products'), $filename);
            $imagePath = $filename;
        } else {
            $imagePath = null;
        }

        DB::transaction(function () use ($request, $userId, $nextCode, $imagePath, $mode, $height, $width, $piecesPerBox, $boxesQuantity,
            $totalM2, $pricePerM2, $purchasePricePerM2, $totalStockQty, $piecesPerM2,
            $salePricePerPiece, $salePricePerBox, $purchasePricePerPiece, $purchasePricePerBox) {

            $variants = [];
            if ($request->has('variant_name')) {
                $names = $request->variant_name;
                $sizes = $request->variant_size;
                $colors = $request->variant_color;
                $stocks = $request->variant_stock;
                $sale_prices = $request->variant_sale_price;
                $wholesale_prices = $request->variant_wholesale_price;
                $weight_factors = $request->variant_weight_per_piece;
                $purch_prices = $request->variant_purchase_price;
                $alerts = $request->variant_alert_qty;
                $barcodes = $request->variant_barcode;
                $conv_factors = $request->variant_conv_factor;
                $is_bases = $request->variant_is_base;
                $units = $request->variant_unit;

                // Validate Conv Factors if Weight Unit is selected
                if (in_array($mode, ['by_kg', 'by_gm', 'by_ton'])) {
                    $factors = [];
                    $baseCount = 0;
                    for ($i = 0; $i < count($names); $i++) {
                        if (!empty($names[$i])) {
                            $factor = (float)($conv_factors[$i] ?? 0);
                            $isBase = (int)($is_bases[$i] ?? 0);
                            if ($factor <= 0) {
                                throw new \Exception("Conversion Factor must be greater than 0.");
                            }
                            if ($isBase === 1) {
                                $baseCount++;
                                if ($factor != 1) {
                                    throw new \Exception("Base variant must have Conversion Factor exactly equal to 1.");
                                }
                                // Base factor is not added to duplicate list
                            } else {
                                // Non‑base variant: ensure uniqueness
                                if (in_array((string)$factor, $factors, true)) {
                                    throw new \Exception("Duplicate Conversion Factor found: " . $factor);
                                }
                                $factors[] = (string)$factor;
                            }
                        }
                    }
                    if ($baseCount !== 1) {
                        throw new \Exception("Exactly one Base Variant is required for weight units.");
                    }
                }

                $variantStockSum = 0;
                $baseConvForCarton = null;
                for ($i = 0; $i < count($names); $i++) {
                    if (!empty($names[$i])) {
                        $vStockRaw = (string)($stocks[$i] ?? '0');
                        $vStock = (float)$vStockRaw;
                        $vConvFactor = (float)($conv_factors[$i] ?? 0);
                        $isBase = (int)($is_bases[$i] ?? 0);
                        if ($vConvFactor <= 0) $vConvFactor = 1;

                        if (in_array($mode, ['by_kg', 'by_gm', 'by_ton'])) {
                            if ($isBase === 1) {
                                $variantStockSum += $vStock;
                            }
                        } elseif ($mode === 'by_cartons') {
                            if ($isBase === 1 || $baseConvForCarton === null) {
                                $baseConvForCarton = $vConvFactor;
                            }
                            if (strpos($vStockRaw, '.') !== false) {
                                $parts = explode('.', $vStockRaw);
                                $boxes = (int)($parts[0] ?? 0);
                                $loose = (int)($parts[1] ?? 0);
                                $variantStockSum += ($boxes * $vConvFactor) + $loose;
                            } else {
                                $variantStockSum += ($vStock * $vConvFactor);
                            }
                        } else {
                            $variantStockSum += $vStock;
                        }

                        $variants[] = [
                            'name' => $names[$i],
                            'size' => $sizes[$i] ?? '-',
                            'color' => $colors[$i] ?? '-',
                            'stock' => $vStock,
                            'sale_price' => $sale_prices[$i] ?? 0,
                            'wholesale_price' => $wholesale_prices[$i] ?? 0,
                            'weight_per_piece' => $weight_factors[$i] ?? 0,
                            'purch_price' => $purch_prices[$i] ?? 0,
                            'alert' => $alerts[$i] ?? 0,
                            'barcode' => $barcodes[$i] ?? '',
                            'conv_factor' => $vConvFactor,
                            'is_base_variant' => $is_bases[$i] ?? 0,
                            'unit' => $units[$i] ?? 'Pcs',
                        ];
                    }
                }
                
                if (count($variants) > 0) {
                    $totalStockQty = $variantStockSum;
                    if ($mode === 'by_cartons' && $baseConvForCarton) {
                        $piecesPerBox = (int)$baseConvForCarton;
                    }
                    $boxesQuantity = $piecesPerBox > 0 ? $totalStockQty / $piecesPerBox : $totalStockQty;
                }
            }

            // Create product
            $product = Product::create([
                'creater_id' => $userId,
                'category_id' => $request->category_id,
                'sub_category_id' => $request->sub_category_id,
                'item_code' => $nextCode,
                'item_name' => $request->product_name,
                'barcode_path' => $request->barcode_path ?? rand(100000000000, 999999999999),
                'unit_id' => $request->unit,
                'brand_id' => $request->brand_id,
                'model' => $request->model,
                'image' => $imagePath,
                'color' => count($variants) > 0 ? json_encode($variants) : ($request->color ? json_encode($request->color) : null),
                'purchase_discount_percent' => $request->purchase_discount_percent ?? 0,
                'sale_discount_percent' => $request->sale_discount_percent ?? 0,
                'alert_quantity' => $request->alert_quantity,
                'alert_carton_quantity' => $request->alert_carton_quantity,

                // New Fields
                'size_mode' => $mode,
                'height' => $height,
                'width' => $width,
                'pieces_per_box' => $piecesPerBox,
                'pieces_per_m2' => $piecesPerM2,
                // 'boxes_quantity' => $boxesQuantity, // Removed: Not in DB
                // 'loose_pieces' => $loosePieces,     // Removed: Not in DB
                // 'piece_quantity' => $pieceQuantity, // Removed: Not in DB
                // 'total_stock_qty' => $totalStockQty, // Removed: Not in DB

                'total_m2' => $totalM2,

                // Prices
                'price_per_m2' => $pricePerM2,
                'purchase_price_per_m2' => $purchasePricePerM2,

                'sale_price_per_box' => $salePricePerBox,
                'sale_price_per_piece' => $salePricePerPiece,
                'wholesale_price' => $request->wholesale_price ?? 0,
                'weight_per_piece' => $request->weight_per_piece ?? 0,
                'purchase_price_per_piece' => $purchasePricePerPiece,
                'purchase_price_per_box' => $purchasePricePerBox,

                'is_part' => 0,
                'is_assembled' => 0,
                'is_web_visible' => $request->has('is_web_visible') ? 1 : 0,
                'show_on_homepage' => $request->has('show_on_homepage') ? 1 : 0,
                'auto_hide_out_of_stock' => $request->has('auto_hide_out_of_stock') ? 1 : 0,
                'promo_tag' => $request->promo_tag,
                'web_sale_price' => $request->web_sale_price,
                'meta_title' => $request->meta_title,
                'meta_description' => $request->meta_description,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create Warehouse Stock
            WarehouseStock::create([
                'warehouse_id' => $request->warehouse_id ?? (\App\Models\Warehouse::first()->id ?? 1), // Default to first warehouse if not selected
                'product_id' => $product->id,
                'quantity' => $boxesQuantity ?? 0,
                'total_pieces' => $totalStockQty,
                'remarks' => 'Initial Stock',
            ]);

            // Log Stock Movement (Initial)
            if ($totalStockQty > 0) {
                StockMovement::create([
                    'product_id' => $product->id,
                    'type' => 'adjustment',
                    'qty' => $totalStockQty,
                    'ref_type' => 'INIT',
                    'note' => 'Initial Stock',
                ]);
            }

            // Upload Website Gallery Images
            if ($request->hasFile('web_images')) {
                foreach ($request->file('web_images') as $index => $file) {
                    $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('uploads/products'), $fileName);
                    \App\Models\ProductWebImage::create([
                        'product_id' => $product->id,
                        'image_path' => $fileName,
                        'sort_order' => $index
                    ]);
                }
            }
        });

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Product created successfully']);
        }

        return redirect()->back()->with('success', 'Product created successfully');
    }

    /*
    // ===== Parts search (for BOM modal) with real available qty =====
        public function searchPartName(Request $request)
    {
        $q = $request->get('q', '');

        $parts = Product::where('is_part', 1)
            ->leftJoin('stocks', 'stocks.product_id', '=', 'products.id')
            ->where(function ($x) use ($q) {
                $x->where('products.item_name', 'like', "%{$q}%")
                  ->orWhere('products.item_code', 'like', "%{$q}%");
            })
            ->groupBy('products.id', 'products.item_name', 'products.item_code', 'products.unit_id')
            ->selectRaw('products.id, products.item_name, products.item_code, products.unit_id, COALESCE(SUM(stocks.qty),0) as available_qty')
            ->limit(20)
            ->get();

        return response()->json($parts->map(function ($p) {
            return [
                'id'            => $p->id,
                'item_name'     => $p->item_name,
                'item_code'     => $p->item_code,
                'unit'          => optional(Unit::find($p->unit_id))->name ?? '',
                'available_qty' => (float)$p->available_qty,
            ];
        }));
    }
    */

    // ===== Update product =====
    public function update(Request $request, $id)
    {
        $userId = auth()->id();

        if ($request->wantsJson()) {
            $validation = $this->validateProductRequest($request);
            if ($validation->fails()) {
                return response()->json(['status' => 'error', 'errors' => $validation->errors()], 422);
            }
            $validated = $validation->validated();
        } else {
            $validation = $this->validateProductRequest($request);
            $validation->validate();
        }

        $mode = $request->size_mode;

        // Initialize variables (defaults)
        $height = 0;
        $width = 0;
        $piecesPerBox = 0;
        $boxesQuantity = 0;
        $loosePieces = 0;
        $pieceQuantity = 0;

        $totalM2 = 0;
        $totalStockQty = 0;
        $m2PerPiece = 0;
        $piecesPerM2 = 0; // New: How many pieces fit in 1 m²

        $pricePerM2 = 0;
        $purchasePricePerM2 = 0;

        $salePricePerPiece = 0;
        $salePricePerBox = 0;
        $purchasePricePerPiece = 0;
        $purchasePricePerBox = 0;

        $totalPrice = 0;
        $totalPurchasePrice = 0;

        if ($mode === 'by_size') {
            $height = (float) $request->height;
            $width = (float) $request->width;
            $piecesPerBox = (int) $request->pieces_per_box;
            $boxesQuantity = (int) $request->boxes_quantity;

            // Pricing
            $pricePerM2 = (float) $request->price_per_m2;
            $purchasePricePerM2 = (float) $request->purchase_price_per_m2;

            $m2PerPiece = ($height * $width) / 10000;
            $m2PerBox = $m2PerPiece * $piecesPerBox;
            $totalM2 = $m2PerBox * $boxesQuantity;

            // Calculate pieces per m²
            $piecesPerM2 = $m2PerPiece > 0 ? (1 / $m2PerPiece) : 0;
            $piecesPerM2 = $m2PerPiece > 0 ? (1 / $m2PerPiece) : 0;

            // Logic validation
            if ($totalM2 <= 0) {
                if ($request->wantsJson()) {
                    return response()->json(['status' => 'error', 'errors' => ['total_m2' => ['Total m² cannot be zero.']]], 422);
                }

                return redirect()->back()->withErrors(['total_m2' => 'Total m² cannot be zero.']);
            }

            $totalPrice = $totalM2 * $pricePerM2;
            $totalPurchasePrice = $totalM2 * $purchasePricePerM2;

            // Set total stock qty for by_size
            $totalStockQty = $boxesQuantity;

            $salePricePerPiece = (float) $request->sale_price_per_box;
            $salePricePerBox = $salePricePerPiece * $piecesPerBox;
            $purchasePricePerPiece = (float) $request->purchase_price_per_piece;
            $purchasePricePerBox = $purchasePricePerPiece * $piecesPerBox;

            $totalPrice = $totalStockQty * $salePricePerBox;
            $totalPurchasePrice = $totalStockQty * $purchasePricePerPiece;

        } elseif ($mode === 'by_cartons') {
            $piecesPerBox = (int) $request->pieces_per_box;
            if ($piecesPerBox <= 0) $piecesPerBox = 1;

            $boxesQuantityRaw = (string) $request->boxes_quantity;
            $loosePieces = (int) $request->loose_pieces;

            if (strpos($boxesQuantityRaw, '.') !== false) {
                $parts = explode('.', $boxesQuantityRaw);
                $boxesQuantity = (int)($parts[0] ?? 0);
                $looseFromDec = (int)($parts[1] ?? 0);
                $totalStockQty = ($piecesPerBox * $boxesQuantity) + $looseFromDec + $loosePieces;
            } else {
                $boxesQuantity = (int) $request->boxes_quantity;
                $totalStockQty = ($piecesPerBox * $boxesQuantity) + $loosePieces;
            }

            $salePricePerPiece = (float) ($request->sale_price_per_piece ?: $request->sale_price_per_box);
            $salePricePerBox = $salePricePerPiece * $piecesPerBox;

            $purchasePricePerPiece = (float) $request->purchase_price_per_piece;
            $purchasePricePerBox = $purchasePricePerPiece * $piecesPerBox;

            $totalPrice = $totalStockQty * $salePricePerPiece;
            $totalPurchasePrice = $totalStockQty * $purchasePricePerPiece;

        } else {
            // Treat by_pieces, by_kg, by_meter, by_gm as piece-based mode
            $pieceQuantity = (int) $request->piece_quantity;

            // Pricing
            $salePricePerBox = (float) $request->sale_price_per_box;
            $purchasePricePerPiece = (float) $request->purchase_price_per_piece;

            $totalStockQty = $pieceQuantity;

            $salePricePerPiece = $salePricePerBox;
            $salePricePerBox = $salePricePerPiece;
            $purchasePricePerPiece = $purchasePricePerPiece;
            $purchasePricePerBox = $purchasePricePerPiece;

            $totalPrice = $totalStockQty * $salePricePerBox;
            $totalPurchasePrice = $totalStockQty * $purchasePricePerPiece;
        }

        // image handle
        $imagePath = Product::where('id', $id)->value('image');
        if ($request->hasFile('image')) {
            $imageName = time().'.'.$request->image->extension();
            $request->image->move(public_path('uploads/products'), $imageName);
            $imagePath = $imageName;
        }

        DB::transaction(function () use ($request, $id, $userId, $imagePath, $mode, $height, $width, $piecesPerBox,
            $boxesQuantity, $loosePieces, $pieceQuantity,
            $totalM2, $pricePerM2, $purchasePricePerM2, $salePricePerBox, $purchasePricePerPiece, $piecesPerM2,
            $salePricePerPiece, $purchasePricePerBox) {

            $variants = [];
            if ($request->has('variant_name')) {
                $names = $request->variant_name;
                $sizes = $request->variant_size;
                $colors = $request->variant_color;
                $stocks = $request->variant_stock;
                $sale_prices = $request->variant_sale_price;
                $wholesale_prices = $request->variant_wholesale_price;
                $weight_factors = $request->variant_weight_per_piece;
                $purch_prices = $request->variant_purchase_price;
                $alerts = $request->variant_alert_qty;
                $barcodes = $request->variant_barcode;
                $conv_factors = $request->variant_conv_factor;
                $is_bases = $request->variant_is_base;
                $units = $request->variant_unit;

                // Validate Conv Factors if Weight Unit is selected
                if (in_array($mode, ['by_kg', 'by_gm', 'by_ton'])) {
                    $factors = [];
                    $baseCount = 0;
                    for ($i = 0; $i < count($names); $i++) {
                        if (!empty($names[$i])) {
                            $factor = (float)($conv_factors[$i] ?? 0);
                            $isBase = (int)($is_bases[$i] ?? 0);
                            if ($factor <= 0) {
                                throw new \Exception("Conversion Factor must be greater than 0.");
                            }
                            if ($isBase === 1) {
                                $baseCount++;
                                if ($factor != 1) {
                                    throw new \Exception("Base variant must have Conversion Factor exactly equal to 1.");
                                }
                                // do not add base factor to $factors array
                            } else {
                                // Non‑base variant: ensure uniqueness
                                if (in_array((string)$factor, $factors, true)) {
                                    throw new \Exception("Duplicate Conversion Factor found: " . $factor);
                                }
                                $factors[] = (string)$factor;
                            }
                        }
                    }
                    if ($baseCount !== 1) {
                        throw new \Exception("Exactly one Base Variant is required for weight units.");
                    }
                }

                $baseConvForCarton = null;
                for ($i = 0; $i < count($names); $i++) {
                    if (!empty($names[$i])) {
                        $vConvFactor = (float)($conv_factors[$i] ?? 0);
                        $isBase = (int)($is_bases[$i] ?? 0);
                        if ($vConvFactor <= 0) $vConvFactor = 1;
                        if ($mode === 'by_cartons' && ($isBase === 1 || $baseConvForCarton === null)) {
                            $baseConvForCarton = $vConvFactor;
                        }

                        $variants[] = [
                            'name' => $names[$i],
                            'size' => $sizes[$i] ?? '-',
                            'color' => $colors[$i] ?? '-',
                            'stock' => $stocks[$i] ?? 0,
                            'sale_price' => $sale_prices[$i] ?? 0,
                            'wholesale_price' => $wholesale_prices[$i] ?? 0,
                            'weight_per_piece' => $weight_factors[$i] ?? 0,
                            'purch_price' => $purch_prices[$i] ?? 0,
                            'alert' => $alerts[$i] ?? 0,
                            'barcode' => $barcodes[$i] ?? '',
                            'conv_factor' => $vConvFactor,
                            'is_base_variant' => $is_bases[$i] ?? 0,
                            'unit' => $units[$i] ?? 'Pcs',
                        ];
                    }
                }
                if ($mode === 'by_cartons' && $baseConvForCarton) {
                    $piecesPerBox = (int)$baseConvForCarton;
                }
            }

            // color update logic
            $final_color = null;
            if (count($variants) > 0) {
                $final_color = json_encode($variants);
            } else if ($request->has('color')) {
                $final_color = json_encode($request->color);
            } else {
                // keep old color if not submitted
                $final_color = Product::where('id', $id)->value('color');
            }

            Product::where('id', $id)->update([
                'creater_id' => $userId,
                'category_id' => $request->category_id,
                'sub_category_id' => $request->sub_category_id,
                'item_code' => $request->item_code ?? Product::where('id', $id)->value('item_code'),
                'item_name' => $request->product_name,
                'barcode_path' => $request->barcode_path ?? rand(100000000000, 999999999999),
                'unit_id' => $request->unit,
                'brand_id' => $request->brand_id,
                'model' => $request->model,
                'image' => $imagePath,
                'color' => $final_color,
                'purchase_discount_percent' => $request->purchase_discount_percent ?? 0,
                'sale_discount_percent' => $request->sale_discount_percent ?? 0,
                'alert_quantity' => $request->alert_quantity,
                'alert_carton_quantity' => $request->alert_carton_quantity,

                // New Fields
                'size_mode' => $mode,
                'height' => $height,
                'width' => $width,
                'pieces_per_box' => $piecesPerBox,
                'pieces_per_m2' => $piecesPerM2,
                // 'boxes_quantity' => $boxesQuantity,
                // 'loose_pieces' => $loosePieces,
                // 'piece_quantity' => $pieceQuantity,
                // 'total_stock_qty' => $totalStockQty,

                'total_m2' => $totalM2,

                // Prices
                'price_per_m2' => $pricePerM2,
                'purchase_price_per_m2' => $purchasePricePerM2,

                'sale_price_per_box' => $salePricePerBox,
                'sale_price_per_piece' => $salePricePerPiece,
                'wholesale_price' => $request->wholesale_price ?? 0,
                'weight_per_piece' => $request->weight_per_piece ?? 0,
                'purchase_price_per_piece' => $purchasePricePerPiece,
                'purchase_price_per_box' => $purchasePricePerBox,

                // 'total_price' => $totalPrice, // Removed: Not in DB
                // 'total_purchase_price' => $totalPurchasePrice, // Removed: Not in DB

                'is_part' => 0,
                'is_assembled' => 0,
                'is_web_visible' => $request->has('is_web_visible') ? 1 : 0,
                'show_on_homepage' => $request->has('show_on_homepage') ? 1 : 0,
                'auto_hide_out_of_stock' => $request->has('auto_hide_out_of_stock') ? 1 : 0,
                'promo_tag' => $request->promo_tag,
                'web_sale_price' => $request->web_sale_price,
                'meta_title' => $request->meta_title,
                'meta_description' => $request->meta_description,
                'updated_at' => now(),
            ]);

            // BOM re-save logic removed as table does not exist
            // DB::table('product_boms')->where('product_id', $id)->delete();

            // Upload Website Gallery Images
            if ($request->hasFile('web_images')) {
                // Remove old images if new ones are uploaded
                $oldImages = \App\Models\ProductWebImage::where('product_id', $id)->get();
                foreach($oldImages as $oldImg) {
                    if(file_exists(public_path('uploads/products/'.$oldImg->image_path))) {
                        unlink(public_path('uploads/products/'.$oldImg->image_path));
                    }
                    $oldImg->delete();
                }

                foreach ($request->file('web_images') as $index => $file) {
                    $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('uploads/products'), $fileName);
                    \App\Models\ProductWebImage::create([
                        'product_id' => $id,
                        'image_path' => $fileName,
                        'sort_order' => $index
                    ]);
                }
            }

            // ✅ Update WarehouseStock box quantity based on new pieces_per_box (preserve total_pieces)
            $warehouseStock = \App\Models\WarehouseStock::where('product_id', $id)->first();
            
            $ppb = $piecesPerBox > 0 ? $piecesPerBox : 1;

            if ($warehouseStock) {
                // Keep the actual pieces we have, just update the box display approximation
                $warehouseStock->quantity = round($warehouseStock->total_pieces / $ppb, 2);
                $warehouseStock->save();
            }

            // Manual stock adjustment (extra on top)
            if ($request->filled('stock_adjust') && (float) $request->stock_adjust != 0) {
                $adjQty = (float) $request->stock_adjust;

                StockMovement::create([
                    'product_id' => $id,
                    'type'       => 'adjustment',
                    'qty'        => $adjQty,
                    'ref_type'   => 'ADJ',
                    'note'       => 'Manual stock adjustment',
                ]);

                $this->upsertStocks($id, $adjQty, 1, 1);
            }
        });

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Product updated successfully']);
        }

        return redirect()->back()->with('success', 'Product updated successfully');
    }

    public function bulkUpdate(Request $request)
    {
        $userId = auth()->id();
        $productsData = $request->input('products', []);

        if (empty($productsData)) {
            return response()->json(['status' => 'error', 'message' => 'No products selected for update.'], 400);
        }

        try {
            DB::transaction(function () use ($productsData, $userId) {
                foreach ($productsData as $id => $data) {
                    $product = Product::findOrFail($id);
                    $updateData = [
                        'creater_id' => $userId,
                        'updated_at' => now(),
                    ];

                    // 1. Category
                    if (array_key_exists('category_id', $data)) {
                        $updateData['category_id'] = $data['category_id'];
                    }

                    // 2. Alert quantity (Min Qty in Cartons)
                    if (array_key_exists('alert_carton_quantity', $data)) {
                        $val = $data['alert_carton_quantity'];
                        $updateData['alert_carton_quantity'] = ($val !== null && $val !== '') ? (int)$val : null;
                        
                        // Sync alert_quantity (pieces) for legacy compatibility
                        $ppb = $product->pieces_per_box > 0 ? $product->pieces_per_box : 1;
                        $updateData['alert_quantity'] = ($val !== null && $val !== '') ? ((int)$val * $ppb) : null;
                    }

                    // 3. Discounts
                    if (array_key_exists('purchase_discount_percent', $data)) {
                        $updateData['purchase_discount_percent'] = (float)$data['purchase_discount_percent'];
                    }
                    if (array_key_exists('sale_discount_percent', $data)) {
                        $updateData['sale_discount_percent'] = (float)$data['sale_discount_percent'];
                    }

                    // 4. Prices
                    $tradePrice = null;
                    $retailPrice = null;

                    if (array_key_exists('purchase_price_per_piece', $data)) {
                        $tradePrice = (float)$data['purchase_price_per_piece'];
                    }
                    if (array_key_exists('sale_price_per_piece', $data)) {
                        $retailPrice = (float)$data['sale_price_per_piece'];
                    }

                    if ($product->size_mode === 'by_size') {
                        $m2PerPiece = ($product->height * $product->width) / 10000;
                        $m2PerBox = $m2PerPiece * ($product->pieces_per_box ?: 1);

                        if ($tradePrice !== null) {
                            $updateData['purchase_price_per_piece'] = $tradePrice;
                            $purchasePricePerM2 = $m2PerPiece > 0 ? ($tradePrice / $m2PerPiece) : 0;
                            $updateData['purchase_price_per_m2'] = $purchasePricePerM2;
                            $updateData['purchase_price_per_box'] = $m2PerBox * $purchasePricePerM2;
                        }

                        if ($retailPrice !== null) {
                            $updateData['sale_price_per_piece'] = $retailPrice;
                            $pricePerM2 = $m2PerPiece > 0 ? ($retailPrice / $m2PerPiece) : 0;
                            $updateData['price_per_m2'] = $pricePerM2;
                            $updateData['sale_price_per_box'] = $m2PerBox * $pricePerM2;
                        }
                    } elseif ($product->size_mode === 'by_cartons') {
                        $ppb = $product->pieces_per_box > 0 ? $product->pieces_per_box : 1;

                        if ($tradePrice !== null) {
                            $updateData['purchase_price_per_piece'] = $tradePrice;
                            $updateData['purchase_price_per_box'] = $tradePrice * $ppb;
                        }

                        if ($retailPrice !== null) {
                            $updateData['sale_price_per_piece'] = $retailPrice;
                            $updateData['sale_price_per_box'] = $retailPrice * $ppb;
                        }
                    } else { // by_pieces / fallback
                        if ($tradePrice !== null) {
                            $updateData['purchase_price_per_piece'] = $tradePrice;
                            $updateData['purchase_price_per_box'] = $tradePrice;
                        }

                        if ($retailPrice !== null) {
                            $updateData['sale_price_per_piece'] = $retailPrice;
                            $updateData['sale_price_per_box'] = $retailPrice;
                        }
                    }

                    $product->update($updateData);
                }
            });

            return response()->json(['status' => 'success', 'message' => 'Products bulk updated successfully.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // ===== Edit view =====
    public function edit($id)
    {
        $product = Product::with('category_relation', 'sub_category_relation', 'unit', 'brand', 'warehouseStocks')
            ->findOrFail($id);
        $categories = Category::all();
        $subcategories = SubCategory::all();
        $brands = Brand::all();

        // Calculate current stock from WarehouseStock (the real source of truth)
        $totalPieces = $product->warehouseStocks->sum('total_pieces');
        $ppb = $product->pieces_per_box > 0 ? $product->pieces_per_box : 1;

        if ($product->size_mode === 'by_cartons' || $product->size_mode === 'by_size') {
            $product->boxes_quantity = (int) floor($totalPieces / $ppb);
            $product->loose_pieces   = (int) ($totalPieces % $ppb);
        } elseif ($product->size_mode === 'by_pieces') {
            $product->piece_quantity  = (int) $totalPieces;
            $product->boxes_quantity  = 0;
            $product->loose_pieces    = 0;
        }

        // Robustly parse variants from $product->color
        $variants = [];
        if (!empty($product->color)) {
            $raw = $product->color;
            if (is_string($raw)) {
                // Check if base64 encoded
                $b64 = base64_decode($raw, true);
                if ($b64 !== false && (str_starts_with(trim($b64), '[') || str_starts_with(trim($b64), '{'))) {
                    $raw = $b64;
                }
                $decoded = json_decode($raw, true);
                // Handle double json encoded strings
                if (is_string($decoded)) {
                    $decoded2 = json_decode($decoded, true);
                    if (is_array($decoded2)) {
                        $decoded = $decoded2;
                    }
                }
            } else {
                $decoded = $raw;
            }

            if (is_array($decoded)) {
                if (isset($decoded['name'])) {
                    $variants = [$decoded];
                } elseif (isset($decoded[0])) {
                    if (is_array($decoded[0])) {
                        $variants = $decoded;
                    } elseif (is_string($decoded[0])) {
                        foreach ($decoded as $cName) {
                            $variants[] = [
                                'name' => $product->item_name . ($cName ? ' - ' . $cName : ''),
                                'size' => '',
                                'color' => $cName,
                                'unit' => 'Pcs',
                                'stock' => 0,
                                'sale_price' => $product->sale_price_per_piece ?? $product->sale_price_per_box ?? 0,
                                'wholesale_price' => $product->wholesale_price ?? 0,
                                'purch_price' => $product->purchase_price_per_piece ?? 0,
                                'alert' => $product->alert_quantity ?? 0,
                                'barcode' => '',
                                'conv_factor' => 1,
                                'is_base_variant' => 0
                            ];
                        }
                    }
                }
            }
        }

        return view('admin_panel.product.edit', compact('product', 'categories', 'subcategories', 'brands', 'variants'));
    }

    // ===== Barcode view =====
    public function barcode($id)
    {
        $product = Product::findOrFail($id);

        return view('admin_panel.product.barcode', compact('product'));
    }

    // Shared validation rules
    private function validateProductRequest(Request $request)
    {
        $rules = [
            'product_name' => 'required|string|max:255',
            'category_id' => 'required',
            'sub_category_id' => 'nullable',
            'brand_id' => 'required',
            'unit' => 'nullable',
            'model' => 'nullable', // Made nullable
            'size_mode' => 'required|in:by_size,by_cartons,by_pieces,by_kg,by_meter,by_gm',
            'purchase_discount_percent' => 'nullable|numeric|min:0|max:100',
            'sale_discount_percent' => 'nullable|numeric|min:0|max:100',
            'alert_quantity' => 'nullable|integer|min:0',
            'alert_carton_quantity' => 'nullable|integer|min:0',
        ];

        // Conditional rules logic
        $mode = $request->size_mode;

        if ($mode === 'by_size') {
            $rules = array_merge($rules, [
                'height' => 'required|numeric|gt:0',
                'width' => 'required|numeric|gt:0',
                'pieces_per_box' => 'required|integer|gt:0',
                'boxes_quantity' => 'required|integer|min:0', // Allowed 0 stock
                'price_per_m2' => 'required|numeric|min:0', // Allowed 0 price
                'purchase_price_per_m2' => 'required|numeric|min:0',
            ]);
        } elseif ($mode === 'by_cartons') {
            $rules = array_merge($rules, [
                'pieces_per_box' => 'required|integer|min:1',
                'boxes_quantity' => 'required|integer|min:0',
                'loose_pieces' => 'nullable|integer|min:0',
                'sale_price_per_box' => 'required|numeric|min:0',
                'purchase_price_per_piece' => 'required|numeric|min:0',
            ]);
        } else {
            $rules = array_merge($rules, [
                'piece_quantity' => 'required|integer|min:0', // Allowed 0 stock
                'sale_price_per_box' => 'required|numeric|min:0',
                'purchase_price_per_piece' => 'required|numeric|min:0',
            ]);
        }

        return \Illuminate\Support\Facades\Validator::make($request->all(), $rules);
    }

    // AJAX Validation Endpoint
    public function validateForm(Request $request)
    {
        $validator = $this->validateProductRequest($request);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        return response()->json(['status' => 'success', 'message' => 'Valid']);
    }

    // ===== Toggle Product Active/Inactive =====
    public function toggleActive($id)
    {
        $product = Product::findOrFail($id);
        $product->is_active = !$product->is_active;
        $product->save();

        return response()->json([
            'success'   => true,
            'is_active' => $product->is_active,
            'message'   => $product->is_active ? 'Product activated successfully.' : 'Product deactivated successfully.',
        ]);
    }

    /**
     * Match a sale item to a specific variant based on size and color stored in color field.
     */
    private function matchSaleItemToVariant($saleItem, $variant)
    {
        $itemColor = $saleItem->color;
        if (empty($itemColor)) {
            return false;
        }

        $itemVariant = [];
        $b64Decoded = base64_decode($itemColor, true);
        if ($b64Decoded !== false) {
            $json = json_decode($b64Decoded, true);
            if (is_array($json)) {
                $itemVariant = $json;
            }
        }
        if (empty($itemVariant)) {
            $json = json_decode($itemColor, true);
            if (is_array($json)) {
                $itemVariant = $json;
            }
        }

        if (empty($itemVariant)) {
            // Simple string comparison
            return strtolower(trim($itemColor)) === strtolower(trim($variant['color'] ?? ''));
        }

        // Compare name, color and size
        $vColor = strtolower(trim($variant['color'] ?? '-'));
        $vSize = strtolower(trim($variant['size'] ?? '-'));
        $vName = strtolower(trim($variant['name'] ?? ''));

        $itemVColor = strtolower(trim($itemVariant['color'] ?? ($itemVariant['color_val'] ?? '-')));
        $itemVSize = strtolower(trim($itemVariant['size'] ?? ($itemVariant['size_val'] ?? '-')));
        $itemVName = strtolower(trim($itemVariant['name'] ?? ''));

        if ($vColor === '') $vColor = '-';
        if ($vSize === '') $vSize = '-';
        if ($itemVColor === '') $itemVColor = '-';
        if ($itemVSize === '') $itemVSize = '-';

        $colorSizeMatch = ($vColor === $itemVColor && $vSize === $itemVSize);

        if ($vName !== '' && $itemVName !== '') {
            return $colorSizeMatch && ($vName === $itemVName);
        }

        return $colorSizeMatch;
    }
}
