<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Warehouse;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportingController extends Controller
{
    public function onhand()
    {
        $rows = Product::leftJoin('v_stock_onhand as soh', 'soh.product_id', '=', 'products.id')
            ->leftJoin('brands', 'brands.id', '=', 'products.brand_id')
            ->leftJoin('units', 'units.id', '=', 'products.unit_id')
            ->selectRaw('
                products.id,
                products.item_code,
                products.item_name,
                COALESCE(brands.name, "") as brand_name,
                COALESCE(units.name, "") as unit_name,
                COALESCE(soh.onhand_qty, 0) as onhand_qty
            ')
            ->orderBy('products.item_name')
            ->get();

        return view('admin_panel.Reporting.onhand', compact('rows'));
    }

    public function item_stock_report()
    {
        $user = auth()->user();
        $categories = Category::orderBy('name')->get();
        $units      = Unit::orderBy('name')->get();

        // Warehouse Permission Filter: Show warehouses only if user has warehouse permission
        if ($user && ($user->email === 'admin@admin.com' || $user->hasRole('Super Admin') || $user->hasRole('Admin') || $user->can('warehouse.view') || $user->can('warehouse.stock.view'))) {
            $warehouses = Warehouse::orderBy('warehouse_name')->get();
        } else {
            $warehouses = collect();
        }

        return view('admin_panel.reporting.item_stock_report', compact('categories', 'warehouses', 'units'));
    }

    // AJAX endpoint to fetch report rows
    public function fetchItemStock(Request $request)
    {
        $productId   = $request->product_id;
        $categoryId  = $request->category_id;
        $warehouseId = $request->warehouse_id;
        $unitType    = $request->unit_type; // 'all', 'cartons_pcs', 'weight_kg', 'area_m2'
        $reportMode  = $request->report_mode ?: 'summary'; // 'summary' vs 'ledger'
        $dateFrom    = $request->date_from;
        $dateTo      = $request->date_to;

        $productsQuery = Product::with(['warehouseStocks', 'unit', 'category_relation']);

        if ($productId && $productId !== 'all') {
            $productsQuery->where('id', $productId);
        }
        if ($categoryId && $categoryId !== 'all') {
            $productsQuery->where('category_id', $categoryId);
        }

        // Unit Mode Filter
        if ($unitType && $unitType !== 'all') {
            if ($unitType === 'weight_kg') {
                $productsQuery->whereIn('size_mode', ['by_kg', 'by_gm', 'by_ton']);
            } elseif ($unitType === 'area_m2') {
                $productsQuery->whereIn('size_mode', ['by_size', 'by_m2']);
            } elseif ($unitType === 'cartons_pcs') {
                $productsQuery->whereIn('size_mode', ['by_cartons', 'by_pieces', 'std']);
            }
        }

        $products = $productsQuery->orderBy('item_name')->get();

        $rows = [];
        $grandTotalValue   = 0;
        $totalCurrentStock = 0;
        $totalAdjustments  = 0;
        $totalSoldAmount   = 0;

        foreach ($products as $product) {
            // Determine default purchase price per piece
            $productPurchPrice = 0;
            if ($product->size_mode === 'by_size' || $product->size_mode === 'by_m2') {
                $m2PerPiece         = (float) ($product->pieces_per_m2 ?? 0);
                $purchPerM2         = (float) ($product->purchase_price_per_m2 ?? 0);
                $productPurchPrice  = $m2PerPiece * $purchPerM2;
            } else {
                $productPurchPrice = (float) ($product->purchase_price_per_piece ?? 0);
            }

            // Determine size mode & unit display label
            $unitName = $product->unit->name ?? 'Pcs';
            $sizeMode = $product->size_mode ?: 'std';

            // Check if product has variants
            $parsedVariants = [];
            if ($product->color) {
                try {
                    $decoded = json_decode($product->color, true);
                    if (is_array($decoded) && count($decoded) > 0 && isset($decoded[0]['name'])) {
                        $parsedVariants = $decoded;
                    }
                } catch (\Exception $e) {}
            }

            if (count($parsedVariants) > 0) {
                if ($product->size_mode === 'by_kg') {
                    // Fetch parent level weight stock and transactions
                    if ($warehouseId && $warehouseId !== 'all') {
                        $parentClosing = (float) $product->warehouseStocks->where('warehouse_id', $warehouseId)->sum('total_pieces');
                    } else {
                        $parentClosing = (float) $product->warehouseStocks->sum('total_pieces');
                    }

                    [$parentPurchased, $parentPurchaseAmount] = $this->getPurchasedQtyAndNetAmount($product->id, ['from' => $dateFrom, 'to' => $dateTo], $warehouseId);

                    // Parent Sold qty & amount
                    $saleStatsQuery = DB::table('sale_items')->where('product_id', $product->id);
                    if ($warehouseId && $warehouseId !== 'all') $saleStatsQuery->where('warehouse_id', $warehouseId);
                    if ($dateFrom) $saleStatsQuery->whereDate('created_at', '>=', $dateFrom);
                    if ($dateTo)   $saleStatsQuery->whereDate('created_at', '<=', $dateTo);
                    $saleStats = $saleStatsQuery->selectRaw('COALESCE(SUM(total_pieces),0) as total_qty, COALESCE(SUM(total),0) as total_amount')->first();
                    $parentSold       = (float) $saleStats->total_qty;
                    $parentSaleAmount = (float) $saleStats->total_amount;

                    // Parent Returned qty
                    $retQuery = DB::table('stock_movements')
                        ->where('product_id', $product->id)
                        ->where('type', 'sale_return');
                    if ($warehouseId && $warehouseId !== 'all') {
                        $retQuery->where('note', 'like', "%Warehouse #{$warehouseId}%");
                    }
                    if ($dateFrom) $retQuery->whereDate('created_at', '>=', $dateFrom);
                    if ($dateTo)   $retQuery->whereDate('created_at', '<=', $dateTo);
                    $parentReturnedQty = (float) $retQuery->sum('qty');

                    // Parent Purchase Returned qty
                    $pRetQuery = DB::table('purchase_return_items as pri')
                        ->join('purchase_returns as pr', 'pr.id', '=', 'pri.purchase_return_id')
                        ->where('pri.product_id', $product->id);
                    if ($warehouseId && $warehouseId !== 'all') {
                        $pRetQuery->where('pr.warehouse_id', $warehouseId);
                    }
                    if ($dateFrom) $pRetQuery->whereDate('pr.created_at', '>=', $dateFrom);
                    if ($dateTo)   $pRetQuery->whereDate('pr.created_at', '<=', $dateTo);
                    $parentPReturned = (float) $pRetQuery->sum('pri.qty');

                    // Parent Adjustments
                    $adjQuery = DB::table('stock_movements')
                        ->where('product_id', $product->id)
                        ->where('type', 'adjustment')
                        ->where(function($q) {
                            $q->whereNull('ref_type')->orWhere('ref_type', '!=', 'INIT');
                        })
                        ->where(function($q) {
                            $q->whereNull('note')->orWhere('note', 'not like', '%Initial Stock%');
                        });
                    if ($warehouseId && $warehouseId !== 'all') {
                        $adjQuery->where('note', 'like', "%Warehouse #{$warehouseId}%");
                    }
                    if ($dateFrom) $adjQuery->whereDate('created_at', '>=', $dateFrom);
                    if ($dateTo)   $adjQuery->whereDate('created_at', '<=', $dateTo);
                    $parentAdjustments = (float) $adjQuery->sum('qty');

                    // Parent Opening stock
                    $parentOpening = max(0, $parentClosing - $parentPurchased + $parentSold - $parentReturnedQty + $parentPReturned - $parentAdjustments);
                } else {
                    // Fetch all sales and returns for this product to distribute
                    $salesQuery = DB::table('sale_items')->where('product_id', $product->id);
                    if ($warehouseId && $warehouseId !== 'all') {
                        $salesQuery->where('warehouse_id', $warehouseId);
                    }
                    if ($dateFrom) $salesQuery->whereDate('created_at', '>=', $dateFrom);
                    if ($dateTo)   $salesQuery->whereDate('created_at', '<=', $dateTo);
                    $salesList = $salesQuery->select('total_pieces', 'total', 'color')->get();

                    // Fetch confirmed web sales
                    $webSalesQuery = DB::table('ecommerce_order_items as eoi')
                        ->join('ecommerce_orders as eo', 'eo.id', '=', 'eoi.ecommerce_order_id')
                        ->where('eoi.product_id', $product->id)
                        ->where('eo.is_stock_deducted', 1);

                    if ($warehouseId && $warehouseId !== 'all' && $warehouseId != 1) {
                        $webSalesQuery->whereRaw('1 = 0');
                    }
                    if ($dateFrom) $webSalesQuery->whereDate('eo.created_at', '>=', $dateFrom);
                    if ($dateTo)   $webSalesQuery->whereDate('eo.created_at', '<=', $dateTo);

                    $webSalesList = $webSalesQuery->select('eoi.quantity as total_pieces', 'eoi.total', 'eoi.color', 'eoi.size')->get();

                    $salesListArray = $salesList->toArray();
                    foreach ($webSalesList as $wItem) {
                        $salesListArray[] = (object) [
                            'total_pieces' => $wItem->total_pieces,
                            'total' => $wItem->total,
                            'color' => json_encode([
                                'color' => $wItem->color ?: '-',
                                'size' => $wItem->size ?: '-'
                            ])
                        ];
                    }
                    $salesList = collect($salesListArray);

                    $returnsQuery = DB::table('sale_return_items as sri')
                        ->join('sale_returns as sr', 'sr.id', '=', 'sri.sale_return_id')
                        ->where('sri.product_id', $product->id);
                    if ($warehouseId && $warehouseId !== 'all') {
                        $returnsQuery->where('sri.warehouse_id', $warehouseId);
                    }
                    if ($dateFrom) $returnsQuery->whereDate('sr.created_at', '>=', $dateFrom);
                    if ($dateTo)   $returnsQuery->whereDate('sr.created_at', '<=', $dateTo);
                    $returnsList = $returnsQuery->select('sri.qty', 'sri.color', 'sr.sale_id')->get();

                    // Fetch all approved/returned purchases
                    $purchasesQuery = DB::table('purchase_items as pi')
                        ->join('purchases as pur', 'pur.id', '=', 'pi.purchase_id')
                        ->where('pi.product_id', $product->id)
                        ->whereIn('pur.status_purchase', ['approved', 'Returned', 'Partial']);
                    if ($warehouseId && $warehouseId !== 'all') {
                        $purchasesQuery->where('pur.warehouse_id', $warehouseId);
                    }
                    if ($dateFrom) $purchasesQuery->whereDate('pur.created_at', '>=', $dateFrom);
                    if ($dateTo)   $purchasesQuery->whereDate('pur.created_at', '<=', $dateTo);
                    $purchasesList = $purchasesQuery->select('pi.qty as total_pieces', 'pi.line_total', 'pi.color')->get();

                    // Fetch all purchase returns
                    $pReturnsQuery = DB::table('purchase_return_items as pri')->where('pri.product_id', $product->id);
                    if ($dateFrom) $pReturnsQuery->whereDate('pri.created_at', '>=', $dateFrom);
                    if ($dateTo)   $pReturnsQuery->whereDate('pri.created_at', '<=', $dateTo);
                    $purchaseReturnsList = $pReturnsQuery->select('pri.qty', 'pri.line_total', 'pri.color')->get();

                    // Fetch Stock Adjustments
                    $adjQuery = DB::table('stock_movements')
                        ->where('product_id', $product->id)
                        ->where('type', 'adjustment')
                        ->where(function($q) {
                            $q->whereNull('ref_type')->orWhere('ref_type', '!=', 'INIT');
                        })
                        ->where(function($q) {
                            $q->whereNull('note')->orWhere('note', 'not like', '%Initial Stock%');
                        });
                    if ($warehouseId && $warehouseId !== 'all') {
                        $adjQuery->where('note', 'like', "%Warehouse #{$warehouseId}%");
                    }
                    if ($dateFrom) $adjQuery->whereDate('created_at', '>=', $dateFrom);
                    if ($dateTo)   $adjQuery->whereDate('created_at', '<=', $dateTo);
                    $adjList = $adjQuery->select('qty', 'note')->get();

                    $saleIds = $returnsList->pluck('sale_id')->unique()->toArray();
                    $saleItemsMap = [];
                    if (!empty($saleIds)) {
                        $siList = DB::table('sale_items')
                            ->whereIn('sale_id', $saleIds)
                            ->where('product_id', $product->id)
                            ->select('sale_id', 'color')
                            ->get();
                        foreach ($siList as $si) {
                            $saleItemsMap[$si->sale_id][] = $si->color;
                        }
                    }
                }
                foreach ($parsedVariants as $v) {
                    $vName = $v['name'] ?? $product->item_name;
                    $vSize = $v['size'] ?? '-';
                    $vColor = $v['color'] ?? '-';

                    // Variant Unit Logic
                    $vUnitName = $v['unit'] ?? $unitName;
                    $isCartonMode = ($product->size_mode === 'by_cartons' || strtolower($vUnitName) === 'carton');

                    // Cartons / Loose / Unit Formatting
                    $ppb = (float) ($product->pieces_per_box ?? 1);
                    if ($isCartonMode) {
                        $vConv = (float) ($v['conv_factor'] ?? 0);
                        if ($vConv > 0) $ppb = $vConv;
                    }

                    if ($product->size_mode === 'by_kg') {
                        $factor = isset($v['conv_factor']) ? (float)$v['conv_factor'] : 1.0;
                        $factor = $factor > 0 ? $factor : 1.0;

                        $initial        = $parentOpening / $factor;
                        $purchased      = $parentPurchased / $factor;
                        $purchaseAmount = $parentPurchaseAmount;
                        $sold           = $parentSold / $factor;
                        $saleAmount     = $parentSaleAmount;
                        $returnedQty    = $parentReturnedQty / $factor;
                        $pReturned      = $parentPReturned / $factor;
                        $adjustments    = $parentAdjustments / $factor;
                        $balance        = $parentClosing / $factor;
                    } else {
                        // Initial Stock in Pieces
                        $vRawStock = (string) ($v['stock'] ?? '0');
                        if ($isCartonMode && $ppb > 1) {
                            if (strpos($vRawStock, '.') !== false) {
                                $parts = explode('.', $vRawStock);
                                $boxes = (int) ($parts[0] ?? 0);
                                $looseP = (int) ($parts[1] ?? 0);
                                $initial = ($boxes * $ppb) + $looseP;
                            } else {
                                $initial = (float) $vRawStock * $ppb;
                            }
                        } else {
                            $initial = (float) $vRawStock;
                        }

                        // Purchased for variant
                        $purchased = 0; $purchaseAmount = 0;
                        foreach ($purchasesList as $pItem) {
                            if ($this->matchSaleItemToVariant($pItem, $v)) {
                                $purchased += (float) $pItem->total_pieces;
                                $purchaseAmount += (float) $pItem->line_total;
                            }
                        }

                        // Purchase Returned for variant
                        $pReturned = 0; $pReturnAmount = 0;
                        foreach ($purchaseReturnsList as $prItem) {
                            if ($this->matchSaleItemToVariant($prItem, $v)) {
                                $pReturned += (float) $prItem->qty;
                                $pReturnAmount += (float) $prItem->line_total;
                            }
                        }

                        // Sold for variant
                        $sold = 0; $saleAmount = 0;
                        foreach ($salesList as $sItem) {
                            if ($this->matchSaleItemToVariant($sItem, $v)) {
                                $sold += (float) $sItem->total_pieces;
                                $saleAmount += (float) $sItem->total;
                            }
                        }

                        // Returned for variant
                        $returnedQty = 0;
                        foreach ($returnsList as $rItem) {
                            $rColor = $rItem->color;
                            if (empty($rColor)) {
                                $saleColors = $saleItemsMap[$rItem->sale_id] ?? [];
                                $rColor = !empty($saleColors) ? $saleColors[0] : '';
                            }
                            $rItemCopy = (object)['qty' => $rItem->qty, 'color' => $rColor];
                            if ($this->matchSaleItemToVariant($rItemCopy, $v)) {
                                $returnedQty += (float) $rItem->qty;
                            }
                        }

                        // Adjustments for variant
                        $adjustments = 0;
                        foreach ($adjList as $adjItem) {
                            if ($this->matchAdjustmentToVariant($adjItem, $v)) {
                                $adjustments += (float) $adjItem->qty;
                            }
                        }

                        // Balance in Total Pieces = Initial + Purchased - Sold + Returned - Purchased Returned + Adjustments
                        $balance = max(0, $initial + $purchased - $sold + $returnedQty - $pReturned + $adjustments);
                    }

                    // Weighted Average Purchase Price
                    $vPurchPrice = (float) ($v['purch_price'] ?? $productPurchPrice);
                    $initialAmount = $initial * $vPurchPrice;
                    $totalQtyIn = $initial + $purchased;
                    $totalAmountIn = $initialAmount + $purchaseAmount;
                    $averagePrice = $totalQtyIn > 0 ? ($totalAmountIn / $totalQtyIn) : $vPurchPrice;

                    $stockValue = $balance * $averagePrice;
                    $grandTotalValue += $stockValue;
                    $totalCurrentStock += $balance;
                    $totalAdjustments  += $adjustments;
                    $totalSoldAmount   += $saleAmount;

                    if ($isCartonMode && $ppb > 1) {
                        $cartons = floor($balance / $ppb);
                        $loose   = $balance % $ppb;
                        $formattedStock = number_format($balance, 0) . " Pcs";
                        $cartonDisplay = ($loose > 0) ? "{$cartons} Ctn + {$loose} Pcs <span class='text-muted small'>({$ppb} pcs/ctn)</span>" : "{$cartons} Ctn <span class='text-muted small'>({$ppb} pcs/ctn)</span>";
                    } elseif ($ppb > 1 && $product->size_mode === 'by_size') {
                        $cartons = floor($balance / $ppb);
                        $loose   = $balance % $ppb;
                        $formattedStock = number_format($balance, 0) . " Pcs";
                        $cartonDisplay = ($loose > 0) ? "{$cartons} Box + {$loose} Pcs" : "{$cartons} Box";
                    } else {
                        $cartons = '-';
                        $loose   = $balance;
                        $formattedStock = number_format($balance, (in_array($product->size_mode, ['by_kg','by_meter','by_feet']) ? 2 : 0)) . " {$vUnitName}";
                        $cartonDisplay = '—';
                    }

                    // Stock Status Badge
                    $status = 'healthy';
                    if ($balance <= 0) $status = 'out_of_stock';
                    elseif ($product->alert_quantity && $balance < $product->alert_quantity) $status = 'low_stock';

                    $rows[] = [
                        'id'              => $product->id,
                        'item_code'       => $product->item_code,
                        'item_name'       => $vName . ' (' . $vSize . ' | ' . $vColor . ')',
                        'category_name'   => $product->category_relation->name ?? 'Standard',
                        'unit_name'       => $vUnitName,
                        'size_mode'       => $sizeMode,
                        'initial_stock'   => $initial,
                        'purchased'       => $purchased,
                        'purchase_amount' => $purchaseAmount,
                        'sold'            => $sold,
                        'sale_amount'     => $saleAmount,
                        'returned_qty'    => $returnedQty,
                        'purch_returned_qty' => $pReturned,
                        'adjustments'     => $adjustments,
                        'balance'         => $balance,
                        'formatted_stock' => $formattedStock,
                        'carton_display'  => $cartonDisplay,
                        'cartons'         => $cartons,
                        'loose'           => $loose,
                        'average_price'   => $averagePrice,
                        'stock_value'     => $stockValue,
                        'status'          => $status,
                    ];
                }
            } else {
                // Product has no variants
                if ($warehouseId && $warehouseId !== 'all') {
                    $balance = (float) $product->warehouseStocks->where('warehouse_id', $warehouseId)->sum('total_pieces');
                } else {
                    $balance = (float) $product->warehouseStocks->sum('total_pieces');
                }

                [$purchased, $purchaseAmount] = $this->getPurchasedQtyAndNetAmount($product->id, ['from' => $dateFrom, 'to' => $dateTo], $warehouseId);

                // Sold qty & amount
                $saleStatsQuery = DB::table('sale_items')->where('product_id', $product->id);
                if ($warehouseId && $warehouseId !== 'all') $saleStatsQuery->where('warehouse_id', $warehouseId);
                if ($dateFrom) $saleStatsQuery->whereDate('created_at', '>=', $dateFrom);
                if ($dateTo)   $saleStatsQuery->whereDate('created_at', '<=', $dateTo);
                $saleStats = $saleStatsQuery->selectRaw('COALESCE(SUM(total_pieces),0) as total_qty, COALESCE(SUM(total),0) as total_amount')->first();

                $sold       = (float) $saleStats->total_qty;
                $saleAmount = (float) $saleStats->total_amount;

                // Returned qty
                $retQuery = DB::table('stock_movements')
                    ->where('product_id', $product->id)
                    ->where('type', 'sale_return');
                if ($warehouseId && $warehouseId !== 'all') {
                    $retQuery->where('note', 'like', "%Warehouse #{$warehouseId}%");
                }
                if ($dateFrom) $retQuery->whereDate('created_at', '>=', $dateFrom);
                if ($dateTo)   $retQuery->whereDate('created_at', '<=', $dateTo);
                $returnedQty = (float) $retQuery->sum('qty');

                // Purchase Returned qty
                $pRetQuery = DB::table('purchase_return_items as pri')
                    ->join('purchase_returns as pr', 'pr.id', '=', 'pri.purchase_return_id')
                    ->where('pri.product_id', $product->id);
                if ($warehouseId && $warehouseId !== 'all') {
                    $pRetQuery->where('pr.warehouse_id', $warehouseId);
                }
                if ($dateFrom) $pRetQuery->whereDate('pr.created_at', '>=', $dateFrom);
                if ($dateTo)   $pRetQuery->whereDate('pr.created_at', '<=', $dateTo);
                $pReturned = (float) $pRetQuery->sum('pri.qty');

                // Stock Adjustments
                $adjQuery = DB::table('stock_movements')
                    ->where('product_id', $product->id)
                    ->where('type', 'adjustment')
                    ->where(function($q) {
                        $q->whereNull('ref_type')->orWhere('ref_type', '!=', 'INIT');
                    })
                    ->where(function($q) {
                        $q->whereNull('note')->orWhere('note', 'not like', '%Initial Stock%');
                    });
                if ($warehouseId && $warehouseId !== 'all') {
                    $adjQuery->where('note', 'like', "%Warehouse #{$warehouseId}%");
                }
                if ($dateFrom) $adjQuery->whereDate('created_at', '>=', $dateFrom);
                if ($dateTo)   $adjQuery->whereDate('created_at', '<=', $dateTo);
                $adjustments = (float) $adjQuery->sum('qty');

                // Opening stock
                $initial = max(0, $balance - $purchased + $sold - $returnedQty + $pReturned - $adjustments);

                // Weighted Average Purchase Price
                $initialAmount  = $initial * $productPurchPrice;
                $totalQtyIn     = $initial + $purchased;
                $totalAmountIn  = $initialAmount + $purchaseAmount;
                $averagePrice   = $totalQtyIn > 0 ? ($totalAmountIn / $totalQtyIn) : $productPurchPrice;

                $stockValue        = $balance * $averagePrice;
                $grandTotalValue  += $stockValue;
                $totalCurrentStock += $balance;
                $totalAdjustments  += $adjustments;
                $totalSoldAmount   += $saleAmount;

                // Cartons / Loose
                $isCartonMode = ($product->size_mode === 'by_cartons' || strtolower($unitName) === 'carton');
                $ppb = (float) ($product->pieces_per_box ?? 1);
                if ($isCartonMode && $ppb > 1) {
                    $cartons = floor($balance / $ppb);
                    $loose   = $balance % $ppb;
                    $formattedStock = number_format($balance, 0) . " Pcs";
                    $cartonDisplay = ($loose > 0) ? "{$cartons} Ctn + {$loose} Pcs <span class='text-muted small'>({$ppb} pcs/ctn)</span>" : "{$cartons} Ctn <span class='text-muted small'>({$ppb} pcs/ctn)</span>";
                } elseif ($ppb > 1 && $product->size_mode === 'by_size') {
                    $cartons = floor($balance / $ppb);
                    $loose   = $balance % $ppb;
                    $formattedStock = number_format($balance, 0) . " Pcs";
                    $cartonDisplay = ($loose > 0) ? "{$cartons} Box + {$loose} Pcs" : "{$cartons} Box";
                } else {
                    $cartons = '-';
                    $loose   = $balance;
                    $formattedStock = number_format($balance, (in_array($product->size_mode, ['by_kg','by_meter','by_feet']) ? 2 : 0)) . " {$unitName}";
                    $cartonDisplay = '—';
                }

                // Stock Status Badge
                $status = 'healthy';
                if ($balance <= 0) $status = 'out_of_stock';
                elseif ($product->alert_quantity && $balance < $product->alert_quantity) $status = 'low_stock';

                $rows[] = [
                    'id'              => $product->id,
                    'item_code'       => $product->item_code,
                    'item_name'       => $product->item_name,
                    'category_name'   => $product->category_relation->name ?? 'Standard',
                    'unit_name'       => $unitName,
                    'size_mode'       => $sizeMode,
                    'initial_stock'   => $initial,
                    'purchased'       => $purchased,
                    'purchase_amount' => $purchaseAmount,
                    'sold'            => $sold,
                    'sale_amount'     => $saleAmount,
                    'returned_qty'    => $returnedQty,
                    'purch_returned_qty' => $pReturned,
                    'adjustments'     => $adjustments,
                    'balance'         => $balance,
                    'formatted_stock' => $formattedStock,
                    'carton_display'  => $cartonDisplay,
                    'cartons'         => $cartons,
                    'loose'           => $loose,
                    'average_price'   => $averagePrice,
                    'stock_value'     => $stockValue,
                    'status'          => $status,
                ];
            }
        }

        return response()->json([
            'data'                  => $rows,
            'grand_total'           => $grandTotalValue,
            'total_current_stock'   => $totalCurrentStock,
            'total_adjustments_qty' => $totalAdjustments,
            'total_sold_amount'     => $totalSoldAmount,
        ]);
    }

    /**
     * AJAX endpoint to fetch full chronological movement timeline history for a product
     */
    public function fetchProductHistory(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);

        $movements = DB::table('stock_movements')
            ->where('product_id', $productId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($m) {
                $typeBadge = 'info';
                $typeLabel = strtoupper($m->type);
                if ($m->type === 'in' || $m->type === 'assembly_in') {
                    $typeBadge = 'success';
                    $typeLabel = 'INWARD (+)';
                } elseif ($m->type === 'out' || $m->type === 'assembly_out') {
                    $typeBadge = 'danger';
                    $typeLabel = 'OUTWARD (-)';
                } elseif ($m->type === 'adjustment') {
                    $typeBadge = 'warning';
                    $typeLabel = 'ADJUSTMENT (' . ($m->qty >= 0 ? '+' : '') . ')';
                }

                return [
                    'id'          => $m->id,
                    'date'        => date('d M Y h:i A', strtotime($m->created_at)),
                    'type'        => $typeLabel,
                    'type_badge'  => $typeBadge,
                    'qty'         => (float) $m->qty,
                    'ref_type'    => $m->ref_type ?: 'GENERAL',
                    'note'        => $m->note ?: 'N/A',
                ];
            });

        return response()->json([
            'success'      => true,
            'product_name' => $product->item_name,
            'item_code'    => $product->item_code,
            'history'      => $movements
        ]);
    }

    public function profit_loss_report()
    {
        $products = Product::orderBy('item_name')->get();
        $categories = Category::orderBy('name')->get();
        $customers = DB::table('customers')->orderBy('customer_name')->get();

        return view('admin_panel.reporting.profit_loss_report', compact('products', 'categories', 'customers'));
    }

    public function fetchProfitLoss(Request $request)
    {
        $start = $request->start_date ?: now()->startOfDay()->toDateTimeString();
        $end = $request->end_date ?: now()->endOfDay()->toDateTimeString();

        if (strlen($start) === 10) {
            $start .= ' 00:00:00';
        } elseif (strlen($start) === 16) {
            $start .= ':00';
        }

        if (strlen($end) === 10) {
            $end .= ' 23:59:59';
        } elseif (strlen($end) === 16) {
            $end .= ':59';
        }

        $productId = $request->product_id;
        $categoryId = $request->category_id;
        $customerId = $request->customer_id;

        $productsQuery = Product::query();
        if ($productId && $productId !== 'all') {
            $productsQuery->where('id', $productId);
        }
        if ($categoryId && $categoryId !== 'all') {
            $productsQuery->where('category_id', $categoryId);
        }
        
        $products = $productsQuery->get();
        $productStats = [];
        $totalGrossProfit = 0;
        $avgPriceMap = [];

        foreach ($products as $product) {
            // Determine default purchase price per piece
            $productPurchPrice = 0;
            if ($product->size_mode === 'by_size') {
                $m2PerPiece = (float) ($product->pieces_per_m2 ?? 0);
                $purchPerM2 = (float) ($product->purchase_price_per_m2 ?? 0);
                $productPurchPrice = $m2PerPiece * $purchPerM2;
            } else {
                $productPurchPrice = (float) ($product->purchase_price_per_piece ?? 0);
            }

            // Calculate overall product average price (for fallback)
            $initial = (float) DB::table('stock_movements')
                ->where('product_id', $product->id)
                ->where('ref_type', 'INIT')
                ->sum('qty');

            [$purchased, $purchaseAmount] = $this->getPurchasedQtyAndNetAmount($product->id);

            $initialAmount = $initial * $productPurchPrice;
            $totalQtyIn = $initial + $purchased;
            $totalAmountIn = $initialAmount + $purchaseAmount;
            $averagePrice = $totalQtyIn > 0 ? ($totalAmountIn / $totalQtyIn) : $productPurchPrice;
            $avgPriceMap[$product->id] = $averagePrice;

            // Check if product has variants
            $parsedVariants = [];
            if ($product->color) {
                try {
                    $decoded = json_decode($product->color, true);
                    if (is_array($decoded) && count($decoded) > 0 && isset($decoded[0]['name'])) {
                        $parsedVariants = $decoded;
                    }
                } catch (\Exception $e) {}
            }

            if (count($parsedVariants) > 0) {
                // Fetch all sales and returns for this product to distribute
                $saleQuery = DB::table('sale_items')
                    ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
                    ->where('sale_items.product_id', $product->id);
                
                if ($start && $end) {
                    $saleQuery->whereBetween('sales.created_at', [$start, $end]);
                }
                if ($customerId && $customerId !== 'all') {
                    $saleQuery->where('sales.customer_id', $customerId);
                }

                $salesList = $saleQuery->select(
                    'sale_items.total_pieces',
                    'sale_items.qty',
                    'sale_items.total',
                    'sale_items.color',
                    'sales.total_extradiscount',
                    'sales.total_bill_amount'
                )->get();

                // Fetch confirmed web sales
                $webSalesQuery = DB::table('ecommerce_order_items as eoi')
                    ->join('ecommerce_orders as eo', 'eo.id', '=', 'eoi.ecommerce_order_id')
                    ->where('eoi.product_id', $product->id)
                    ->where('eo.is_stock_deducted', 1);

                if ($start && $end) {
                    $webSalesQuery->whereBetween('eo.created_at', [$start, $end]);
                }
                if ($customerId && $customerId !== 'all') {
                    $webSalesQuery->whereRaw('1 = 0');
                }

                $webSalesList = $webSalesQuery->select(
                    'eoi.quantity as total_pieces',
                    'eoi.quantity as qty',
                    'eoi.total',
                    'eoi.color',
                    'eoi.size',
                    'eo.discount as total_extradiscount',
                    'eo.subtotal as total_bill_amount'
                )->get();

                $salesListArray = $salesList->toArray();
                foreach ($webSalesList as $wItem) {
                    $salesListArray[] = (object) [
                        'total_pieces' => $wItem->total_pieces,
                        'qty' => $wItem->qty,
                        'total' => $wItem->total,
                        'total_extradiscount' => $wItem->total_extradiscount,
                        'total_bill_amount' => $wItem->total_bill_amount,
                        'color' => json_encode([
                            'color' => $wItem->color ?: '-',
                            'size' => $wItem->size ?: '-'
                        ])
                    ];
                }
                $salesList = collect($salesListArray);

                $returnQuery = DB::table('sale_return_items as sri')
                    ->join('sale_returns as sr', 'sr.id', '=', 'sri.sale_return_id')
                    ->where('sri.product_id', $product->id);
                
                if ($start && $end) {
                    $returnQuery->whereBetween('sr.created_at', [$start, $end]);
                }
                $returnsList = $returnQuery->select('sri.qty', 'sri.color', 'sr.sale_id', 'sri.line_total')
                    ->get();

                $saleIds = $returnsList->pluck('sale_id')->unique()->toArray();
                $saleItemsMap = [];
                if (!empty($saleIds)) {
                    $siList = DB::table('sale_items')
                        ->whereIn('sale_id', $saleIds)
                        ->where('product_id', $product->id)
                        ->select('sale_id', 'color')
                        ->get();
                    foreach ($siList as $si) {
                        $saleItemsMap[$si->sale_id][] = $si->color;
                    }
                }

                foreach ($parsedVariants as $v) {
                    $vName = $v['name'] ?? $product->item_name;
                    $vSize = $v['size'] ?? '-';
                    $vColor = $v['color'] ?? '-';

                    $soldQty = 0;
                    $soldQtyPieces = 0;
                    $soldAmount = 0;
                    foreach ($salesList as $sItem) {
                        if ($this->matchSaleItemToVariant($sItem, $v)) {
                            $soldQty += (float) $sItem->qty;
                            $soldQtyPieces += (float) $sItem->total_pieces;
                            
                            $itemNet = (float) $sItem->total;
                            if ($sItem->total_bill_amount > 0 && $sItem->total_extradiscount > 0) {
                                $proportion = $itemNet / (float) $sItem->total_bill_amount;
                                $itemNet -= ($sItem->total_extradiscount * $proportion);
                            }
                            $soldAmount += $itemNet;
                        }
                    }

                    $returnedQtyPieces = 0;
                    $returnedAmount = 0;
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
                            $returnedQtyPieces += (float) $rItem->qty;
                            $returnedAmount += (float) $rItem->line_total;
                        }
                    }

                    $vAveragePrice = (float) ($v['purch_price'] ?? $productPurchPrice);
                    $netSoldAmount = $soldAmount - $returnedAmount;
                    $netQtyPieces = $soldQtyPieces - $returnedQtyPieces;
                    $costOfGoodsSold = $netQtyPieces * $vAveragePrice;
                    $grossProfit = $netSoldAmount - $costOfGoodsSold;

                    if ($soldQty > 0 || $returnedQtyPieces > 0) {
                        $productStats[] = [
                            'item_code' => $product->item_code,
                            'item_name' => $vName . ' (' . $vSize . ' | ' . $vColor . ')',
                            'sold_qty' => $soldQty,
                            'returned_qty' => $returnedQtyPieces,
                            'revenue' => $netSoldAmount,
                            'avg_cost' => $vAveragePrice,
                            'cogs' => $costOfGoodsSold,
                            'profit' => $grossProfit
                        ];
                        $totalGrossProfit += $grossProfit;
                    }
                }
            } else {
                // Product has no variants: original logic
                $saleQuery = DB::table('sale_items')
                    ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
                    ->where('sale_items.product_id', $product->id);
                
                if ($start && $end) {
                    $saleQuery->whereBetween('sales.created_at', [$start, $end]);
                }

                if ($customerId && $customerId !== 'all') {
                    $saleQuery->where('sales.customer_id', $customerId);
                }

                $salesList = $saleQuery->select(
                    'sale_items.total_pieces',
                    'sale_items.qty',
                    'sale_items.total',
                    'sales.total_extradiscount',
                    'sales.total_bill_amount'
                )->get();

                $soldQty = 0;
                $soldQtyPieces = 0;
                $soldAmount = 0;
                foreach ($salesList as $sItem) {
                    $soldQty += (float) $sItem->qty;
                    $soldQtyPieces += (float) $sItem->total_pieces;
                    
                    $itemNet = (float) $sItem->total;
                    if ($sItem->total_bill_amount > 0 && $sItem->total_extradiscount > 0) {
                        $proportion = $itemNet / (float) $sItem->total_bill_amount;
                        $itemNet -= ($sItem->total_extradiscount * $proportion);
                    }
                    $soldAmount += $itemNet;
                }
                
                $returnQuery = DB::table('sale_return_items as sri')
                    ->join('sale_returns as sr', 'sr.id', '=', 'sri.sale_return_id')
                    ->where('sri.product_id', $product->id);
                
                if ($start && $end) {
                    $returnQuery->whereBetween('sr.created_at', [$start, $end]);
                }
                $returnsList = $returnQuery->select('sri.qty', 'sri.line_total')->get();

                $returnedQtyPieces = 0;
                $returnedAmount = 0;
                foreach ($returnsList as $rItem) {
                    $returnedQtyPieces += (float) $rItem->qty;
                    $returnedAmount += (float) $rItem->line_total;
                }

                $netSoldAmount = $soldAmount - $returnedAmount;
                $netQtyPieces = $soldQtyPieces - $returnedQtyPieces;
                $costOfGoodsSold = $netQtyPieces * $averagePrice;
                $grossProfit = $netSoldAmount - $costOfGoodsSold;

                if ($soldQty > 0 || $returnedQtyPieces > 0) {
                     $productStats[] = [
                        'item_code' => $product->item_code,
                        'item_name' => $product->item_name,
                        'sold_qty' => $soldQty,
                        'returned_qty' => $returnedQtyPieces,
                        'revenue' => $netSoldAmount,
                        'avg_cost' => $averagePrice,
                        'cogs' => $costOfGoodsSold,
                        'profit' => $grossProfit
                    ];
                    $totalGrossProfit += $grossProfit;
                }
            }
        }

        // 2b. Manual Products P&L
        $manualSaleQuery = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sale_items.is_manual', 1);

        if ($start && $end) {
            $manualSaleQuery->whereBetween('sales.created_at', [$start, $end]);
        }
        if ($customerId && $customerId !== 'all') {
            $manualSaleQuery->where('sales.customer_id', $customerId);
        }

        $manualSalesList = $manualSaleQuery->select(
            'sale_items.product_name',
            'sale_items.total_pieces',
            'sale_items.qty',
            'sale_items.total',
            'sale_items.purchase_price',
            'sales.total_extradiscount',
            'sales.total_bill_amount'
        )->get();

        if ($manualSalesList->isNotEmpty()) {
            $manualGrouped = $manualSalesList->groupBy('product_name');

            foreach ($manualGrouped as $mName => $mItems) {
                $soldQty = 0;
                $soldQtyPieces = 0;
                $soldAmount = 0;
                $cogs = 0;

                foreach ($mItems as $sItem) {
                    $soldQty += (float) $sItem->qty;
                    $soldQtyPieces += (float) $sItem->total_pieces;
                    $cogs += ((float) $sItem->total_pieces) * ((float) $sItem->purchase_price);

                    $itemNet = (float) $sItem->total;
                    if ($sItem->total_bill_amount > 0 && $sItem->total_extradiscount > 0) {
                        $proportion = $itemNet / (float) $sItem->total_bill_amount;
                        $itemNet -= ($sItem->total_extradiscount * $proportion);
                    }
                    $soldAmount += $itemNet;
                }

                $manualRetQuery = DB::table('sale_return_items')
                    ->join('sale_returns', 'sale_returns.id', '=', 'sale_return_items.sale_return_id')
                    ->where('sale_return_items.is_manual', 1)
                    ->where('sale_return_items.product_name', $mName);

                if ($start && $end) {
                    $manualRetQuery->whereBetween('sale_returns.created_at', [$start, $end]);
                }
                if ($customerId && $customerId !== 'all') {
                    $manualRetQuery->where('sale_returns.customer_id', $customerId);
                }

                $manualReturns = $manualRetQuery->select(
                    'sale_return_items.qty',
                    'sale_return_items.line_total',
                    'sale_return_items.purchase_price'
                )->get();

                $returnedQtyPieces = 0;
                foreach ($manualReturns as $mr) {
                    $returnedQtyPieces += (float) $mr->qty;
                    $soldAmount -= (float) $mr->line_total;
                    $cogs -= ((float) $mr->qty) * ((float) $mr->purchase_price);
                }

                $grossProfit = $soldAmount - $cogs;
                $averageCost = $soldQtyPieces > 0 ? ($cogs / $soldQtyPieces) : 0;

                if ($soldQty > 0 || $returnedQtyPieces > 0) {
                     $productStats[] = [
                        'item_code' => 'MANUAL',
                        'item_name' => $mName ? $mName . ' (Manual)' : 'Manual Product',
                        'sold_qty' => $soldQty,
                        'returned_qty' => $returnedQtyPieces,
                        'revenue' => $soldAmount,
                        'avg_cost' => $averageCost,
                        'cogs' => $cogs,
                        'profit' => $grossProfit
                    ];
                    $totalGrossProfit += $grossProfit;
                }
            }
        }

        // 3. Calculate Expenses
        $expenseQueryV1 = DB::table('expense_vouchers');
        $expenseQueryV2 = DB::table('voucher_masters')->where('voucher_type', 'expense');

        if ($start && $end) {
            $expenseQueryV1->whereBetween('entry_date', [$start, $end]);
            $expenseQueryV2->whereBetween('date', [$start, $end]);
        }

        $totalExpenses = $expenseQueryV1->sum('total_amount') + $expenseQueryV2->sum('total_amount');

        // 4. Top 10 Customers by Profit
        $allCustomers = DB::table('customers')->get();
        $customerProfits = [];

        $balanceService = app(\App\Services\BalanceService::class);
        foreach ($allCustomers as $customer) {
            $custSaleQuery = DB::table('sale_items')
                ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
                ->where('sales.customer_id', $customer->id);

            if ($start && $end) {
                $custSaleQuery->whereBetween('sales.created_at', [$start, $end]);
            }

            $custSaleItems = $custSaleQuery->select(
                'sale_items.product_id', 
                'sale_items.is_manual',
                'sale_items.purchase_price',
                'sale_items.color',
                'sale_items.total_pieces',
                'sale_items.total',
                'sales.total_extradiscount',
                'sales.total_bill_amount'
            )->get();

            $custRevenue = 0;
            $custCogs = 0;

            foreach ($custSaleItems as $item) {
                $avgPrice = 0;
                
                if ($item->is_manual) {
                    $avgPrice = (float) $item->purchase_price;
                } else {
                    $product = $products->firstWhere('id', $item->product_id);
                    if ($product) {
                        $itemColor = $item->color;
                        $itemVariant = null;
                        if (!empty($itemColor)) {
                            $b64Decoded = base64_decode($itemColor, true);
                            if ($b64Decoded !== false) {
                                $itemVariant = json_decode($b64Decoded, true);
                            }
                            if (empty($itemVariant)) {
                                $itemVariant = json_decode($itemColor, true);
                            }
                        }

                        if (is_array($itemVariant) && isset($itemVariant['color'])) {
                            $vColor = strtolower(trim($itemVariant['color'] ?? '-'));
                            $vSize = strtolower(trim($itemVariant['size'] ?? '-'));
                            if ($vColor === '') $vColor = '-';
                            if ($vSize === '') $vSize = '-';

                            $decoded = json_decode($product->color, true);
                            if (is_array($decoded)) {
                                foreach ($decoded as $v) {
                                    $vC = strtolower(trim($v['color'] ?? '-'));
                                    $vS = strtolower(trim($v['size'] ?? '-'));
                                    if ($vC === '') $vC = '-';
                                    if ($vS === '') $vS = '-';

                                    if ($vC === $vColor && $vS === $vSize) {
                                        $avgPrice = (float) ($v['purch_price'] ?? 0);
                                        break;
                                    }
                                }
                            }
                        }

                        if ($avgPrice <= 0) {
                            $avgPrice = $avgPriceMap[$item->product_id] ?? 0;
                        }
                    }
                }

                $itemNet = (float) $item->total;
                if ($item->total_bill_amount > 0 && $item->total_extradiscount > 0) {
                    $proportion = $itemNet / (float) $item->total_bill_amount;
                    $itemNet -= ($item->total_extradiscount * $proportion);
                }

                $custRevenue += $itemNet;
                $custCogs += (float) ($item->total_pieces ?? 0) * $avgPrice;
            }

            $custProfit = $custRevenue - $custCogs;

            if ($custRevenue > 0) {
                $customerProfits[] = [
                    'id' => $customer->id,
                    'name' => $customer->customer_name,
                    'balance' => $balanceService->getCustomerBalance($customer->id),
                    'revenue' => round($custRevenue, 2),
                    'cogs' => round($custCogs, 2),
                    'profit' => round($custProfit, 2),
                ];
            }
        }

        // Sort by profit descending and take top 10
        usort($customerProfits, function($a, $b) {
            return $b['profit'] <=> $a['profit'];
        });
        $topCustomers = array_slice($customerProfits, 0, 10);

        return response()->json([
            'products' => $productStats,
            'total_gross_profit' => round($totalGrossProfit, 2),
            'total_expenses' => round($totalExpenses, 2),
            'net_profit' => round($totalGrossProfit - $totalExpenses, 2),
            'top_customers' => $topCustomers
        ]);
    }

    public function purchase_report()
    {
        $products = \App\Models\Product::orderBy('item_name')->get();
        $vendors = \App\Models\Vendor::orderBy('name')->get();
        return view('admin_panel.reporting.purchase_report', compact('products', 'vendors'));
    }

    public function fetchPurchaseReport(Request $request)
    {
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $productId = $request->product_id;
        $vendorId = $request->vendor_id;

        $query = DB::table('purchases')
            ->join('purchase_items', 'purchases.id', '=', 'purchase_items.purchase_id')
            ->join('products', 'purchase_items.product_id', '=', 'products.id')
            ->join('vendors', 'purchases.vendor_id', '=', 'vendors.id') // join vendor table
            ->leftJoin('units', 'products.unit_id', '=', 'units.id')
            ->select(
                'purchases.purchase_date',
                'purchases.invoice_no',
                'vendors.name as vendor_name', // vendor name
                'products.item_code',
                'products.item_name',
                'purchase_items.qty',
                DB::raw('COALESCE(units.name, purchase_items.unit, "-") as unit'), // Fix null unit
                'purchase_items.price',
                'purchase_items.item_discount',
                'purchase_items.line_total',
                'purchases.subtotal',
                'purchases.discount',
                'purchases.extra_cost',
                'purchases.net_amount',
                'purchases.paid_amount',
                'purchases.due_amount'
            );

        if ($startDate && $endDate) {
            $query->whereBetween('purchases.purchase_date', [$startDate, $endDate]);
        }
        if ($productId && $productId !== 'all') {
            $query->where('purchase_items.product_id', $productId);
        }
        if ($vendorId && $vendorId !== 'all') {
            $query->where('purchases.vendor_id', $vendorId);
        }

        $results = $query->orderBy('purchases.purchase_date', 'asc')->get();

        // Attach returns to each row
        $rows = $results->map(function ($row) {
            $returns = DB::table('purchase_return_items')
                ->join('purchase_returns', 'purchase_returns.id', '=', 'purchase_return_items.purchase_return_id')
                ->join('products', 'products.id', '=', 'purchase_return_items.product_id')
                ->where('purchase_returns.purchase_id', DB::table('purchases')->where('invoice_no', $row->invoice_no)->value('id'))
                ->where('purchase_return_items.product_id', DB::table('products')->where('item_code', $row->item_code)->value('id'))
                ->select('products.item_name', 'purchase_return_items.qty', 'purchase_return_items.line_total')
                ->get();

            $row->returns = $returns;
            return $row;
        });

        // Add manual/outsourced purchases
        $manualQuery = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->leftJoin('vendors', 'sale_items.vendor_id', '=', 'vendors.id')
            ->where('sale_items.is_manual', 1)
            ->select(
                'sales.created_at as purchase_date',
                'sales.invoice_no',
                DB::raw('COALESCE(vendors.name, "Unknown Vendor") as vendor_name'),
                DB::raw('"MANUAL" as item_code'),
                DB::raw('CONCAT(sale_items.product_name, " (Outsourced)") as item_name'),
                'sale_items.qty',
                DB::raw('"pc" as unit'),
                'sale_items.purchase_price as price',
                DB::raw('0 as item_discount'),
                DB::raw('(sale_items.qty * sale_items.purchase_price) as line_total'),
                DB::raw('(sale_items.qty * sale_items.purchase_price) as subtotal'),
                DB::raw('0 as discount'),
                DB::raw('0 as extra_cost'),
                DB::raw('(sale_items.qty * sale_items.purchase_price) as net_amount'),
                DB::raw('(sale_items.qty * sale_items.purchase_price) as paid_amount'), // Assumed fully paid or handled in ledger
                DB::raw('0 as due_amount')
            );

        if ($startDate && $endDate) {
            $manualQuery->whereBetween('sales.created_at', [
                \Carbon\Carbon::parse($startDate)->startOfDay()->format('Y-m-d H:i:s'),
                \Carbon\Carbon::parse($endDate)->endOfDay()->format('Y-m-d H:i:s')
            ]);
        }
        if ($vendorId && $vendorId !== 'all') {
            $manualQuery->where('sale_items.vendor_id', $vendorId);
        }

        if (!$productId || $productId === 'all') {
            $manualPurchases = $manualQuery->get();
            $manualPurchases = $manualPurchases->map(function ($row) {
                // Check if any returns exist for this manual product in this sale
                $returns = DB::table('sale_return_items')
                    ->join('sale_returns', 'sale_returns.id', '=', 'sale_return_items.sale_return_id')
                    ->where('sale_returns.sale_id', DB::table('sales')->where('invoice_no', $row->invoice_no)->value('id'))
                    ->where('sale_return_items.is_manual', 1)
                    ->where('sale_return_items.product_name', str_replace(' (Outsourced)', '', $row->item_name))
                    ->select(
                        DB::raw('CONCAT(sale_return_items.product_name, " (Outsourced)") as item_name'),
                        'sale_return_items.qty',
                        DB::raw('(sale_return_items.qty * sale_return_items.purchase_price) as line_total')
                    )
                    ->get();
                $row->returns = $returns;
                return $row;
            });
            
            $rows = $rows->concat($manualPurchases);
        }

        // Sort by date after merging
        $rows = $rows->sortBy('purchase_date')->values();

        return response()->json([
            'data' => $rows
        ]);
    }

    public function sale_report()
    {
        return view('admin_panel.reporting.sale_report');
    }

    public function fetchsaleReport(Request $request)
    {
        if ($request->ajax()) {
            $start = $request->start_date;
            $end = $request->end_date;

            // Use Eloquent to handle relations and new table structure
            $query = \App\Models\Sale::with(['customer_relation', 'items.product', 'returns']);

            if ($start && $end) {
                $query->whereBetween('created_at', [
                    \Carbon\Carbon::parse($start)->format('Y-m-d H:i:s'),
                    \Carbon\Carbon::parse($end)->format('Y-m-d H:i:s')
                ]);
            }

            $sales = $query->orderBy('created_at', 'asc')->get();

            // Transform to match the structure expected by the frontend (CSV strings)
            $transformed = $sales->map(function ($sale) {
                // Construct comma-separated strings for legacy frontend support
                $productNames = $sale->items->map(function ($item) {
                    if (!$item->product) {
                        return $item->is_manual ? ($item->product_name ?? 'Manual Product') . ' (Manual)' : 'Unknown';
                    }
                    
                    $vStr = '';
                    if (!empty($item->color)) {
                        $itemVariant = [];
                        $b64Decoded = base64_decode($item->color, true);
                        if ($b64Decoded !== false) {
                            $json = json_decode($b64Decoded, true);
                            if (is_array($json)) {
                                $itemVariant = $json;
                            }
                        }
                        if (empty($itemVariant)) {
                            $json = json_decode($item->color, true);
                            if (is_array($json)) {
                                $itemVariant = $json;
                            }
                        }

                        if (!empty($itemVariant)) {
                            $sizeVal = $itemVariant['size'] ?? ($itemVariant['size_val'] ?? '');
                            $colorVal = $itemVariant['color'] ?? ($itemVariant['color_val'] ?? '');
                            $vParts = array_filter([$sizeVal, $colorVal]);
                            if (count($vParts) > 0) {
                                $vStr = ' (' . implode(' | ', $vParts) . ')';
                            }
                        } else {
                            $vStr = ' (' . $item->color . ')';
                        }
                    }
                    return $item->product->item_name . $vStr;
                })->implode(',');

                // Use SKU or Name as per preference, usually Name for reports
                $productCodes = $sale->items->map(function ($item) {
                    return $item->product ? $item->product->item_code : ($item->is_manual ? 'MANUAL' : '-');
                })->implode(',');

                // Use formatted string for display, and decimal for calculation
                $qtys = $sale->items->map(function ($item) {
                    $ppb = $item->product && $item->product->pieces_per_box > 0 ? (int) $item->product->pieces_per_box : 1;
                    $tp = (int) $item->total_pieces;
                    $mode = $item->product ? $item->product->size_mode : 'by_pieces';
                    
                    if ($mode == 'by_pieces') {
                        return $tp . ' Pcs';
                    } else {
                        $b = floor($tp / $ppb);
                        $l = $tp % $ppb;
                        $uom = $mode == 'by_cartons' ? 'Ctn' : 'Box';
                        if ($b > 0 && $l > 0) {
                            return $b . ' ' . $uom . ' + ' . $l . ' Pcs';
                        } elseif ($b > 0) {
                            return $b . ' ' . $uom;
                        } else {
                            return $l . ' Pcs';
                        }
                    }
                })->implode(',');
                
                $qty_decimals = $sale->items->pluck('qty')->implode(',');
                $total_pieces_arr = $sale->items->pluck('total_pieces')->implode(',');
                $prices = $sale->items->pluck('price')->implode(','); // Unit Price
                $totals = $sale->items->pluck('total')->implode(','); // Line Total
                $isExchange = \Illuminate\Support\Str::startsWith($sale->reference ?? '', 'Exchange for');
                $netAmount = (float)$sale->total_net;

                if ($isExchange) {
                    $collected = (float)$sale->cash - (float)$sale->change;
                    if ($collected > 0) {
                        $netAmount = $collected;
                    } elseif ($collected <= 0) {
                        $refundPayment = \App\Models\CustomerPayment::where('note', 'Refund Paid for POS Exchange #'.$sale->invoice_no)->first();
                        if ($refundPayment) {
                            $netAmount = -(float)$refundPayment->amount;
                        } else {
                            $netAmount = 0;
                        }
                    }
                }

                return [
                    'id' => $sale->id,
                    'reference' => $sale->reference ?? '-',
                    'product' => $productNames,      // Names
                    'product_code' => $productCodes, // Codes
                    'brand' => '-',                  // Could extract from items if needed
                    'unit' => '-',                   // Could extract
                    'per_price' => $prices,
                    'per_discount' => 0,             
                    'qty' => $qtys,
                    'qty_decimal' => $qty_decimals,
                    'total_pieces' => $total_pieces_arr,
                    'per_total' => $totals,
                    'total_net' => $netAmount,
                    'created_at' => $sale->created_at->format('Y-m-d h:i:s A'),
                    'customer_name' => $sale->customer_relation ? $sale->customer_relation->customer_name : 'Walk-in',
                    'returns' => $sale->returns->map(function($ret) {
                         // Robust return display handling both legacy strings and new relation items
                         $retItems = $ret->items;
                         if ($retItems && $retItems->count() > 0) {
                             $pNames = $retItems->map(function($i) {
                                 if ($i->is_manual) {
                                     return $i->product_name . ' (Manual)';
                                 }
                                 if (!$i->product) return 'Unknown';
                                 
                                 $vStr = '';
                                 if (!empty($i->color)) {
                                     $itemVariant = [];
                                     $b64Decoded = base64_decode($i->color, true);
                                     if ($b64Decoded !== false) {
                                         $json = json_decode($b64Decoded, true);
                                         if (is_array($json)) {
                                             $itemVariant = $json;
                                         }
                                     }
                                     if (empty($itemVariant)) {
                                         $json = json_decode($i->color, true);
                                         if (is_array($json)) {
                                             $itemVariant = $json;
                                         }
                                     }

                                     if (!empty($itemVariant)) {
                                         $sizeVal = $itemVariant['size'] ?? ($itemVariant['size_val'] ?? '');
                                         $colorVal = $itemVariant['color'] ?? ($itemVariant['color_val'] ?? '');
                                         $vParts = array_filter([$sizeVal, $colorVal]);
                                         if (count($vParts) > 0) {
                                             $vStr = ' (' . implode(' | ', $vParts) . ')';
                                         }
                                     }
                                 }
                                 return $i->product->item_name . $vStr;
                             })->implode(', ');
                             
                             $pQtys = $retItems->pluck('qty')->implode(', ');
                             $pTotal = $retItems->sum('line_total');
                         } else {
                             $pNames = $ret->product ?? '-';
                             $pQtys = $ret->qty ?? 0;
                             $pTotal = $ret->net_amount ?? 0;
                         }

                         return [
                            'product' => $pNames,
                            'qty' => $pQtys,
                            'per_total' => $pTotal
                         ];
                    })
                ];
            });

            // Calculate Expenses for date range
            $expenseQueryV1 = DB::table('expense_vouchers');
            $expenseQueryV2 = DB::table('voucher_masters')->where('voucher_type', 'expense');

            if ($start && $end) {
                $startDt = \Carbon\Carbon::parse($start)->format('Y-m-d H:i:s');
                $endDt   = \Carbon\Carbon::parse($end)->format('Y-m-d H:i:s');
                $expenseQueryV1->whereBetween('entry_date', [$startDt, $endDt]);
                $expenseQueryV2->whereBetween('date', [$startDt, $endDt]);
            }

            $totalExpenses = (float) $expenseQueryV1->sum('total_amount') + (float) $expenseQueryV2->sum('total_amount');

            // Calculate Total COGS for fetched sales
            $totalCogs = 0;
            foreach ($sales as $sale) {
                foreach ($sale->items as $item) {
                    $purchPrice = 0;
                    if (isset($item->purchase_price) && (float)$item->purchase_price > 0) {
                        $purchPrice = (float) $item->purchase_price;
                    } elseif ($item->product) {
                        $purchPrice = (float) ($item->product->purchase_price_per_piece ?? 0);
                    }
                    $totalCogs += ((float) $item->total_pieces) * $purchPrice;
                }
            }

            return response()->json([
                'sales' => $transformed,
                'summary' => [
                    'expenses' => $totalExpenses,
                    'cogs'     => $totalCogs,
                ]
            ]);
        }

        return view('admin_panel.reporting.sale_report');
    }

    public function customer_ledger_report()
    {
        $customers = DB::table('customers')->select('id', 'customer_name', 'zone')->get();
        $zones = \App\Models\Zone::orderBy('zone')->get();

        return view('admin_panel.reporting.customer_ledger_report', compact('customers', 'zones'));
    }

    public function fetch_customer_ledger(Request $request)
    {
        $customerId = $request->customer_id;
        $zoneId = $request->zone_id;
        $start = $request->start_date ?: '2000-01-01';
        $end = $request->end_date ?: date('Y-m-d');

        $balanceService = app(\App\Services\BalanceService::class);

        // If "all" or empty, fetch for ALL customers
        if (!$customerId || $customerId === 'all') {
            // Get all customers who have journal entries
            $customerIds = \App\Models\JournalEntry::where('party_type', \App\Models\Customer::class)
                ->distinct()
                ->pluck('party_id')
                ->toArray();

            // Also include customers with opening balance
            $obCustomerIds = \App\Models\Customer::where('opening_balance', '>', 0)
                ->pluck('id')
                ->toArray();

            $allIds = array_unique(array_merge($customerIds, $obCustomerIds));

            // Apply zone filter if provided
            if ($zoneId) {
                // Filter allIds to only those customers who belong to the selected zone
                $validZoneIds = \App\Models\Customer::where('zone', $zoneId)->pluck('id')->toArray();
                $allIds = array_intersect($allIds, $validZoneIds);
            }

            $allTransactions = [];
            $totalOpening = 0;
            $totalClosing = 0;

            foreach ($allIds as $cid) {
                $ledgerData = $balanceService->getCustomerLedger($cid, $start, $end);
                $customerName = $ledgerData['customer']->customer_name ?? 'Unknown';
                $totalOpening += $ledgerData['opening_balance'];

                foreach ($ledgerData['transactions'] as $row) {
                    $desc = $row['description'] ?? '';

                    // Try to find payment account name for receipt entries
                    $accountName = '';
                    if ($row['credit'] > 0 && $row['source_type']) {
                        $accountName = $this->getPaymentAccountName($row['source_type'], $row['source_id']);
                    }
                    if ($accountName) {
                        $desc .= ' [A/C: ' . $accountName . ']';
                    }

                    $ref = '-';
                    if (preg_match('/Invoice #(\S+)/', $desc, $matches)) {
                        $ref = $matches[1];
                    } elseif (preg_match('/Receipt #(\S+)/', $desc, $matches)) {
                        $ref = $matches[1];
                    }

                    $entryDate = $row['date'];
                    if ($entryDate instanceof \Carbon\Carbon) {
                        $formattedDate = $entryDate->format('d-M-Y');
                        $sortDate = $entryDate->format('Y-m-d');
                    } else {
                        $formattedDate = \Carbon\Carbon::parse($entryDate)->format('d-M-Y');
                        $sortDate = \Carbon\Carbon::parse($entryDate)->format('Y-m-d');
                    }

                    $allTransactions[] = [
                        'sort_date' => $sortDate,
                        'date' => $formattedDate,
                        'invoice' => $ref,
                        'description' => $desc,
                        'customer_name' => $customerName,
                        'debit' => $row['debit'] ?? 0,
                        'credit' => $row['credit'] ?? 0,
                        'balance' => $row['balance'] ?? 0,
                    ];
                }

                $totalClosing += $ledgerData['closing_balance'] ?? $ledgerData['opening_balance'];
            }

            // Sort by date
            usort($allTransactions, function ($a, $b) {
                return strcmp($a['sort_date'], $b['sort_date']);
            });

            // Recalculate running balance across all
            $running = $totalOpening;
            foreach ($allTransactions as &$t) {
                $running += ($t['debit'] - $t['credit']);
                $t['balance'] = $running;
            }

            return response()->json([
                'customer' => (object)['customer_name' => 'All Customers'],
                'opening_balance' => $totalOpening,
                'closing_balance' => $totalClosing,
                'transactions' => $allTransactions,
                'report_period' => "$start to $end",
            ]);
        }

        // Single customer
        $customer = DB::table('customers')->where('id', $customerId)->first();
        if (!$customer) {
            return response()->json(['error' => 'Customer not found'], 400);
        }

        $ledgerData = $balanceService->getCustomerLedger($customerId, $start, $end);

        $transactions = collect($ledgerData['transactions'])->map(function ($row) {
            $desc = $row['description'] ?? '';

            // Try to find payment account name for receipt entries
            $accountName = '';
            if ($row['credit'] > 0 && ($row['source_type'] ?? null)) {
                $accountName = $this->getPaymentAccountName($row['source_type'], $row['source_id']);
            }
            if ($accountName) {
                $desc .= ' [A/C: ' . $accountName . ']';
            }

            $ref = '-';
            if (preg_match('/Invoice #(\S+)/', $desc, $matches)) {
                $ref = $matches[1];
            } elseif (preg_match('/Receipt #(\S+)/', $desc, $matches)) {
                $ref = $matches[1];
            }

            $entryDate = $row['date'];
            if ($entryDate instanceof \Carbon\Carbon) {
                $formattedDate = $entryDate->format('d-M-Y');
            } else {
                $formattedDate = \Carbon\Carbon::parse($entryDate)->format('d-M-Y');
            }

            return [
                'date' => $formattedDate,
                'invoice' => $ref,
                'description' => $desc,
                'debit' => $row['debit'] ?? 0,
                'credit' => $row['credit'] ?? 0,
                'balance' => $row['balance'] ?? 0,
            ];
        });

        return response()->json([
            'customer' => $customer,
            'opening_balance' => $ledgerData['opening_balance'],
            'closing_balance' => $ledgerData['closing_balance'] ?? $ledgerData['opening_balance'],
            'transactions' => $transactions,
            'report_period' => "$start to $end",
        ]);
    }

    /**
     * Get the payment account name from voucher source
     */
    private function getPaymentAccountName($sourceType, $sourceId)
    {
        try {
            if ($sourceType === \App\Models\VoucherMaster::class && $sourceId) {
                // Look at VoucherDetail for the debit side (cash/bank account)
                $voucherDetail = \App\Models\VoucherDetail::where('voucher_master_id', $sourceId)
                    ->where('debit', '>', 0)
                    ->first();
                if ($voucherDetail && $voucherDetail->account_id) {
                    $account = \App\Models\Account::find($voucherDetail->account_id);
                    return $account ? $account->title : '';
                }
            } elseif ($sourceType === \App\Models\PaymentVoucher::class && $sourceId) {
                $pv = \App\Models\PaymentVoucher::find($sourceId);
                if ($pv && $pv->row_account_id) {
                    $account = \App\Models\Account::find($pv->row_account_id);
                    return $account ? $account->title : '';
                }
            }
        } catch (\Exception $e) {
            // Silently fail
        }
        return '';
    }

    public function vendor_ledger_report()
    {
        $vendors = DB::table('vendors')->select('id', 'name')->orderBy('name')->get();

        return view('admin_panel.reporting.vendor_ledger_report', compact('vendors'));
    }

    public function fetch_vendor_ledger(Request $request)
    {
        $vendorId = $request->vendor_id;
        $start = $request->start_date ?: '2000-01-01';
        $end = $request->end_date ?: date('Y-m-d');

        $balanceService = app(\App\Services\BalanceService::class);

        // If "all" or empty, fetch for ALL vendors
        if (!$vendorId || $vendorId === 'all') {
            $vendorIds = \App\Models\JournalEntry::where('party_type', \App\Models\Vendor::class)
                ->distinct()
                ->pluck('party_id')
                ->toArray();

            // Also include vendors with opening balance
            $obVendorIds = \App\Models\Vendor::where('opening_balance', '>', 0)
                ->pluck('id')
                ->toArray();

            $allIds = array_unique(array_merge($vendorIds, $obVendorIds));

            $allTransactions = [];
            $totalOpening = 0;
            $totalClosing = 0;

            foreach ($allIds as $vid) {
                $ledgerData = $balanceService->getVendorLedger($vid, $start, $end);
                $vendorName = $ledgerData['vendor']->name ?? 'Unknown';
                $totalOpening += $ledgerData['opening_balance'];

                foreach ($ledgerData['transactions'] as $row) {
                    $desc = $row['description'] ?? '';

                    $accountName = '';
                    if ($row['debit'] > 0 && ($row['source_type'] ?? null)) {
                        $accountName = $this->getPaymentAccountName($row['source_type'], $row['source_id']);
                    }
                    if ($accountName) {
                        $desc .= ' [A/C: ' . $accountName . ']';
                    }

                    $ref = '-';
                    if (preg_match('/PUR-(\S+)/', $desc, $matches)) {
                        $ref = 'PUR-' . $matches[1];
                    } elseif (preg_match('/Payment #(\S+)/', $desc, $matches)) {
                        $ref = $matches[1];
                    } elseif (preg_match('/Purchase #(\S+)/', $desc, $matches)) {
                        $ref = $matches[1];
                    }

                    $entryDate = $row['date'];
                    if ($entryDate instanceof \Carbon\Carbon) {
                        $formattedDate = $entryDate->format('d-M-Y');
                        $sortDate = $entryDate->format('Y-m-d');
                    } else {
                        $formattedDate = \Carbon\Carbon::parse($entryDate)->format('d-M-Y');
                        $sortDate = \Carbon\Carbon::parse($entryDate)->format('Y-m-d');
                    }

                    $allTransactions[] = [
                        'sort_date' => $sortDate,
                        'date' => $formattedDate,
                        'invoice' => $ref,
                        'description' => $desc,
                        'vendor_name' => $vendorName,
                        'debit' => $row['debit'] ?? 0,
                        'credit' => $row['credit'] ?? 0,
                        'balance' => $row['balance'] ?? 0,
                    ];
                }

                $totalClosing += $ledgerData['closing_balance'] ?? $ledgerData['opening_balance'];
            }

            // Sort by date
            usort($allTransactions, function ($a, $b) {
                return strcmp($a['sort_date'], $b['sort_date']);
            });

            // Recalculate running balance across all
            $running = $totalOpening;
            foreach ($allTransactions as &$t) {
                $running += ($t['credit'] - $t['debit']);
                $t['balance'] = $running;
            }

            return response()->json([
                'vendor' => (object)['name' => 'All Vendors'],
                'opening_balance' => $totalOpening,
                'closing_balance' => $totalClosing,
                'transactions' => $allTransactions,
                'report_period' => "$start to $end",
            ]);
        }

        // Single vendor
        $vendor = DB::table('vendors')->where('id', $vendorId)->first();
        if (!$vendor) {
            return response()->json(['error' => 'Vendor not found'], 400);
        }

        $ledgerData = $balanceService->getVendorLedger($vendorId, $start, $end);

        $transactions = collect($ledgerData['transactions'])->map(function ($row) {
            $desc = $row['description'] ?? '';

            $accountName = '';
            if ($row['debit'] > 0 && ($row['source_type'] ?? null)) {
                $accountName = $this->getPaymentAccountName($row['source_type'], $row['source_id']);
            }
            if ($accountName) {
                $desc .= ' [A/C: ' . $accountName . ']';
            }

            $ref = '-';
            if (preg_match('/PUR-(\S+)/', $desc, $matches)) {
                $ref = 'PUR-' . $matches[1];
            } elseif (preg_match('/Payment #(\S+)/', $desc, $matches)) {
                $ref = $matches[1];
            } elseif (preg_match('/Purchase #(\S+)/', $desc, $matches)) {
                $ref = $matches[1];
            }

            $entryDate = $row['date'];
            if ($entryDate instanceof \Carbon\Carbon) {
                $formattedDate = $entryDate->format('d-M-Y');
            } else {
                $formattedDate = \Carbon\Carbon::parse($entryDate)->format('d-M-Y');
            }

            return [
                'date' => $formattedDate,
                'invoice' => $ref,
                'description' => $desc,
                'debit' => $row['debit'] ?? 0,
                'credit' => $row['credit'] ?? 0,
                'balance' => $row['balance'] ?? 0,
            ];
        });

        return response()->json([
            'vendor' => $vendor,
            'opening_balance' => $ledgerData['opening_balance'],
            'closing_balance' => $ledgerData['closing_balance'] ?? $ledgerData['opening_balance'],
            'transactions' => $transactions,
            'report_period' => "$start to $end",
        ]);
    }

    public function balance_sheet_report()
    {
        return view('admin_panel.reporting.balance_sheet');
    }

    public function fetch_balance_sheet(Request $request)
    {
        $date = $request->date ?: date('Y-m-d');
        // Let's get "As Of Date" balances. If a future date is given, it still acts up to that point.
        // We will include ending day inclusive so we add time OR use <=
        $dateEnd = $date . ' 23:59:59';

        $balanceService = app(\App\Services\BalanceService::class);

        // 1. Current Assets
        // Cash in Hand (head_id = 1)
        // Cash at Bank (Usually head_id = 1, we can split them if we can identify them, but we'll show all assets)
        $assetAccounts = DB::table('accounts')->whereIn('head_id', [1, 2])->get();
        $cashAccounts = [];
        $totalCashBank = 0;

        foreach ($assetAccounts as $acc) {
            // Need balance up to Date from Journal Entries
            $balance = DB::table('journal_entries')
                ->where('account_id', $acc->id)
                ->where('entry_date', '<=', $dateEnd)
                ->selectRaw('COALESCE(SUM(debit) - SUM(credit), 0) as total')
                ->value('total') ?? 0;
            
            $computed_balance = $acc->opening_balance + $balance;
            if ($computed_balance != 0) {
                $cashAccounts[] = [
                    'name' => $acc->title,
                    'balance' => $computed_balance
                ];
                $totalCashBank += $computed_balance;
            }
        }

        // Account Receivables
        $customers = DB::table('customers')->get();
        $totalReceivables = 0;
        foreach ($customers as $c) {
            $bal = $balanceService->getCustomerBalanceBeforeDate($c->id, $dateEnd);
            $totalReceivables += $bal;
        }

        // Stock in Trade
        // Current Stock = Initial + Purchased - Sold
        $products = DB::table('products')->get();
        $totalInventory = 0;
        foreach ($products as $p) {
            // Initial
            $initial = (float) DB::table('stock_movements')
                ->where('product_id', $p->id)
                ->where('ref_type', 'INIT')
                ->where('created_at', '<=', $dateEnd)
                ->sum('qty');

            // Purchased
            [$purchased, $purchaseAmount] = $this->getPurchasedQtyAndNetAmount($p->id, ['before' => $dateEnd]);

            // Sold
            $saleStats = DB::table('sale_items')
                ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
                ->where('sale_items.product_id', $p->id)
                ->where('sales.created_at', '<=', $dateEnd)
                ->selectRaw('COALESCE(SUM(sale_items.qty),0) as total_qty')
                ->first();

            $sold = (float) $saleStats->total_qty;

            $balance = $initial + $purchased - $sold;

            // Average Cost
            $productPurchPrice = 0;
            if ($p->size_mode === 'by_size') {
                $m2PerPiece = (float) ($p->pieces_per_m2 ?? 0);
                $purchPerM2 = (float) ($p->purchase_price_per_m2 ?? 0);
                $productPurchPrice = $m2PerPiece * $purchPerM2;
            } else {
                $productPurchPrice = (float) ($p->purchase_price_per_piece ?? 0);
            }

            $initialAmount = $initial * $productPurchPrice;
            $totalQtyIn = $initial + $purchased;
            $avgCost = $totalQtyIn > 0 ? ($initialAmount + $purchaseAmount) / $totalQtyIn : $productPurchPrice;

            if ($balance > 0) {
                $totalInventory += ($balance * $avgCost);
            }
        }

        $currentAssetsTotal = $totalCashBank + $totalReceivables + $totalInventory;
        $fixedAssetsTotal = 0; // if you have fixed assets head, you can add here
        $totalAssets = $currentAssetsTotal + $fixedAssetsTotal;

        // 2. Liabilities
        $vendors = DB::table('vendors')->get();
        $totalPayables = 0;
        foreach ($vendors as $v) {
            $bal = $balanceService->getVendorBalanceBeforeDate($v->id, $dateEnd);
            $totalPayables += $bal; // Vendor Balance (Cr is positive from BalanceService view)
        }
        $currentLiabilitiesTotal = $totalPayables;

        // 3. Owner's Equity (Equity = Assets - Liabilities)
        $equityTotal = $totalAssets - $currentLiabilitiesTotal;
        $totalLiabilitiesAndEquity = $currentLiabilitiesTotal + $equityTotal;

        return response()->json([
            'date' => date('d-M-Y', strtotime($date)),
            'assets' => [
                'cash_bank' => $cashAccounts,
                'total_cash_bank' => $totalCashBank,
                'receivables' => $totalReceivables,
                'inventory' => $totalInventory,
                'current_total' => $currentAssetsTotal,
                'fixed_total' => $fixedAssetsTotal,
                'total' => $totalAssets
            ],
            'liabilities' => [
                'payables' => $totalPayables,
                'current_total' => $currentLiabilitiesTotal,
                'equity' => $equityTotal,
                'total' => $totalLiabilitiesAndEquity
            ]
        ]);
    }

    public function recovery_report()
    {
        return view('admin_panel.reporting.recovery_report');
    }

    public function fetch_recovery(Request $request)
    {
        $startDate = $request->start_date ?: '2000-01-01';
        $endDate = $request->end_date ?: date('Y-m-d');
        $dateEndParams = $endDate . ' 23:59:59';
        
        $balanceService = app(\App\Services\BalanceService::class);
        $customers = DB::table('customers')->orderBy('customer_name')->get();
        
        $rows = [];
        $totalOpening = 0;
        $totalSales = 0;
        $totalReceived = 0;
        $totalFinal = 0;

        foreach ($customers as $index => $c) {
            $initialOpening = (float) ($c->opening_balance ?? 0);

            $priorStats = DB::table('journal_entries')
                ->where('party_type', \App\Models\Customer::class)
                ->where('party_id', $c->id)
                ->where('entry_date', '<', $startDate)
                ->where('description', 'NOT LIKE', 'Opening Balance%')
                ->selectRaw('COALESCE(SUM(debit), 0) as debits, COALESCE(SUM(credit), 0) as credits')
                ->first();

            $priorDebits = (float) ($priorStats->debits ?? 0);
            $priorCredits = (float) ($priorStats->credits ?? 0);

            $opening = $initialOpening + $priorDebits - $priorCredits;

            $periodStats = DB::table('journal_entries')
                ->where('party_type', \App\Models\Customer::class)
                ->where('party_id', $c->id)
                ->whereBetween('entry_date', [$startDate, $dateEndParams])
                ->where('description', 'NOT LIKE', 'Opening Balance%')
                ->selectRaw('COALESCE(SUM(debit), 0) as debits, COALESCE(SUM(credit), 0) as credits')
                ->first();

            $sales = (float) ($periodStats->debits ?? 0);
            $received = (float) ($periodStats->credits ?? 0);

            $final = $opening + $sales - $received;

            if (abs($opening) > 0 || abs($sales) > 0 || abs($received) > 0 || abs($final) > 0) {
                $rows[] = [
                    'sr' => count($rows) + 1,
                    'party' => $c->customer_name,
                    'opening' => $opening,
                    'sales' => $sales,
                    'received' => $received,
                    'final' => $final
                ];

                $totalOpening += $opening;
                $totalSales += $sales;
                $totalReceived += $received;
                $totalFinal += $final;
            }
        }

        return response()->json([
            'date_range' => date('d-m-Y', strtotime($startDate)) . ' to ' . date('d-m-Y', strtotime($endDate)),
            'rows' => $rows,
            'totals' => [
                'opening' => $totalOpening,
                'sales' => $totalSales,
                'received' => $totalReceived,
                'final' => $totalFinal
            ]
        ]);
    }

    public function payable_report()
    {
        return view('admin_panel.reporting.payable_report');
    }

    public function fetch_payable(Request $request)
    {
        $startDate = $request->start_date ?: '2000-01-01';
        $endDate = $request->end_date ?: date('Y-m-d');
        $dateEndParams = $endDate . ' 23:59:59';
        
        $balanceService = app(\App\Services\BalanceService::class);
        $vendors = DB::table('vendors')->orderBy('name')->get();
        $apId = $balanceService->getAccountsPayableId();
        
        $rows = [];
        $totalOpening = 0;
        $totalPurchases = 0;
        $totalPaid = 0;
        $totalFinal = 0;

        foreach ($vendors as $v) {
            $initialOpening = (float) ($v->opening_balance ?? 0);

            $priorStats = DB::table('journal_entries')
                ->where('party_type', \App\Models\Vendor::class)
                ->where('party_id', $v->id)
                ->where('entry_date', '<', $startDate)
                ->where('description', 'NOT LIKE', 'Opening Balance%')
                ->selectRaw('COALESCE(SUM(credit), 0) as credits, COALESCE(SUM(debit), 0) as debits')
                ->first();

            $priorCredits = (float) ($priorStats->credits ?? 0);
            $priorDebits = (float) ($priorStats->debits ?? 0);

            $opening = $initialOpening + $priorCredits - $priorDebits;
            
            $purchasesRaw = DB::table('purchases')
                ->where('vendor_id', $v->id)
                ->where('status_purchase', 'approved')
                ->whereBetween('purchase_date', [$startDate, $endDate])
                ->sum('net_amount');

            $returnsRaw = DB::table('purchase_returns')
                ->where('vendor_id', $v->id)
                ->whereBetween('return_date', [$startDate, $endDate])
                ->sum('net_amount');
            
            $purchases = (float) $purchasesRaw - (float) $returnsRaw;

            $paid = (float) \App\Models\JournalEntry::where('party_type', \App\Models\Vendor::class)
                ->where('party_id', $v->id)
                ->where('account_id', $apId)
                ->whereBetween('entry_date', [$startDate, $dateEndParams])
                ->where('description', 'NOT LIKE', 'Opening Balance%')
                ->sum('debit');
            
            $final = $opening + $purchases - $paid;

            if (abs($opening) > 0.01 || abs($purchases) > 0.01 || abs($paid) > 0.01 || abs($final) > 0.01) {
                $rows[] = [
                    'sr' => count($rows) + 1,
                    'party' => $v->name,
                    'opening' => $opening,
                    'purchases' => $purchases,
                    'paid' => $paid,
                    'final' => $final
                ];

                $totalOpening += $opening;
                $totalPurchases += $purchases;
                $totalPaid += $paid;
                $totalFinal += $final;
            }
        }

        return response()->json([
            'date_range' => date('d-m-Y', strtotime($startDate)) . ' to ' . date('d-m-Y', strtotime($endDate)),
            'rows' => $rows,
            'totals' => [
                'opening' => $totalOpening,
                'purchases' => $totalPurchases,
                'paid' => $totalPaid,
                'final' => $totalFinal
            ]
        ]);
    }

    public function parties_balance_report()
    {
        return view('admin_panel.reporting.parties_balance_report');
    }

    public function fetch_parties_balance(Request $request)
    {
        $reportType = $request->report_type ?: 'BOTH'; // RECEIVABLE, PAYABLE, BOTH
        $showZero = $request->show_zero == 'true';
        $searchParty = $request->party_name;
        $searchMobile = $request->mobile;
        
        $balanceService = app(\App\Services\BalanceService::class);
        $apId = $balanceService->getAccountsPayableId();
        
        $parties = [];
        
        // Fetch Customers in 1 Batch Query
        if ($reportType == 'BOTH' || $reportType == 'RECEIVABLE') {
            $custBalances = DB::table('journal_entries')
                ->where('party_type', \App\Models\Customer::class)
                ->selectRaw('party_id, COALESCE(SUM(debit) - SUM(credit), 0) as balance')
                ->groupBy('party_id')
                ->pluck('balance', 'party_id');

            $customers = DB::table('customers')->get();
            foreach ($customers as $c) {
                if ($searchParty && stripos($c->customer_name, $searchParty) === false) continue;
                if ($searchMobile && stripos($c->mobile, $searchMobile) === false) continue;
                
                $balance = (float) ($custBalances[$c->id] ?? 0);
                $parties[] = [
                    'code' => sprintf("C%04d", $c->id),
                    'title' => $c->customer_name,
                    'mobile' => $c->mobile,
                    'balance' => $balance,
                    'type' => 'customer'
                ];
            }
        }

        // Fetch Vendors in 1 Batch Query
        if ($reportType == 'BOTH' || $reportType == 'PAYABLE') {
            $vendorBalances = DB::table('journal_entries')
                ->where('party_type', \App\Models\Vendor::class)
                ->where('account_id', $apId)
                ->selectRaw('party_id, COALESCE(SUM(credit) - SUM(debit), 0) as balance')
                ->groupBy('party_id')
                ->pluck('balance', 'party_id');

            $vendors = DB::table('vendors')->get();
            foreach ($vendors as $v) {
                if ($searchParty && stripos($v->name, $searchParty) === false) continue;
                if ($searchMobile && stripos($v->phone, $searchMobile) === false) continue;
                
                $balance = (float) ($vendorBalances[$v->id] ?? 0);
                $parties[] = [
                    'code' => sprintf("V%04d", $v->id),
                    'title' => $v->name,
                    'mobile' => $v->phone,
                    'balance' => -$balance,
                    'type' => 'vendor'
                ];
            }
        }

        $rows = [];
        $totalReceivable = 0;
        $totalPayable = 0;
        $sr = 1;

        foreach ($parties as $p) {
            $bal = $p['balance'];
            
            $receivable = 0;
            $payable = 0;

            if ($bal > 0) {
                $receivable = $bal;
            } elseif ($bal < 0) {
                $payable = abs($bal);
            }

            // Apply strict type filters if there's any overflow
            if ($reportType == 'RECEIVABLE' && $receivable == 0 && !$showZero) continue;
            if ($reportType == 'PAYABLE' && $payable == 0 && !$showZero) continue;
            
            if (!$showZero && $receivable == 0 && $payable == 0) continue;

            $rows[] = [
                'sr' => $sr++,
                'code' => $p['code'],
                'title' => $p['title'],
                'mobile' => $p['mobile'] ?? '-',
                'receivable' => $receivable,
                'payable' => $payable,
                'notes' => ''
            ];

            $totalReceivable += $receivable;
            $totalPayable += $payable;
        }

        return response()->json([
            'rows' => $rows,
            'totals' => [
                'receivable' => $totalReceivable,
                'payable' => $totalPayable
            ]
        ]);
    }
    public function aging_report()
    {
        return view('admin_panel.reporting.aging_report');
    }

    public function fetch_aging(Request $request)
    {
        $type     = $request->type ?: 'receivable'; // receivable | payable
        $asOfDate = $request->as_of_date ?: date('Y-m-d');
        $today    = \Carbon\Carbon::parse($asOfDate);

        $balanceService = app(\App\Services\BalanceService::class);

        $rows = [];
        $grandTotal     = 0;
        $grandCurrent   = 0;
        $grand15        = 0;
        $grand30        = 0;
        $grand45        = 0;
        $grand60        = 0;
        $grand75        = 0;
        $grand90plus    = 0;

        if ($type === 'receivable') {
            // Customer Aging: Each Sale invoice outstanding
            $customers = DB::table('customers')->get();

            foreach ($customers as $c) {
                $totalBalance = $balanceService->getCustomerBalance($c->id);
                if ($totalBalance <= 0) continue; // skip if no balance

                // Get all sale invoices for this customer
                $invoices = DB::table('sales')
                    ->where('customer_id', $c->id)
                    ->where('created_at', '<=', $asOfDate . ' 23:59:59')
                    ->select('id', 'invoice_no', 'total_net', 'created_at')
                    ->orderBy('created_at')
                    ->get();

                // Distribute total balance across invoices by age (FIFO)
                $remaining = $totalBalance;

                $bucket_current = 0;
                $bucket_15 = 0;
                $bucket_30 = 0;
                $bucket_45 = 0;
                $bucket_60 = 0;
                $bucket_75 = 0;
                $bucket_90plus = 0;

                foreach ($invoices as $inv) {
                    if ($remaining <= 0) break;
                    $invAmt  = min((float) $inv->total_net, $remaining);
                    $days    = (int) \Carbon\Carbon::parse($inv->created_at)->diffInDays($today);
                    $remaining -= $invAmt;

                    if ($days == 0)      $bucket_current += $invAmt;
                    elseif ($days <= 15) $bucket_15      += $invAmt;
                    elseif ($days <= 30) $bucket_30      += $invAmt;
                    elseif ($days <= 45) $bucket_45      += $invAmt;
                    elseif ($days <= 60) $bucket_60      += $invAmt;
                    elseif ($days <= 75) $bucket_75      += $invAmt;
                    else                 $bucket_90plus  += $invAmt;
                }

                // Any remaining (from opening balance) goes to 90+
                if ($remaining > 0) $bucket_90plus += $remaining;

                $rows[] = [
                    'name'      => $c->customer_name,
                    'mobile'    => $c->mobile ?? '',
                    'total'     => $totalBalance,
                    'current'   => $bucket_current,
                    '15d'       => $bucket_15,
                    '30d'       => $bucket_30,
                    '45d'       => $bucket_45,
                    '60d'       => $bucket_60,
                    '75d'       => $bucket_75,
                    '90plus'    => $bucket_90plus,
                ];

                $grandTotal   += $totalBalance;
                $grandCurrent += $bucket_current;
                $grand15      += $bucket_15;
                $grand30      += $bucket_30;
                $grand45      += $bucket_45;
                $grand60      += $bucket_60;
                $grand75      += $bucket_75;
                $grand90plus  += $bucket_90plus;
            }
        } else {
            // Vendor Aging (Payable)
            $vendors = DB::table('vendors')->get();

            foreach ($vendors as $v) {
                $totalBalance = $balanceService->getVendorBalance($v->id);
                if ($totalBalance <= 0) continue; // we owe them

                $invoices = DB::table('purchases')
                    ->where('vendor_id', $v->id)
                    ->where('purchase_date', '<=', $asOfDate)
                    ->where('status_purchase', 'approved')
                    ->select('id', 'invoice_no', 'net_amount', 'purchase_date')
                    ->orderBy('purchase_date')
                    ->get();

                $remaining = $totalBalance;

                $bucket_current = 0;
                $bucket_15 = 0;
                $bucket_30 = 0;
                $bucket_45 = 0;
                $bucket_60 = 0;
                $bucket_75 = 0;
                $bucket_90plus = 0;

                foreach ($invoices as $inv) {
                    if ($remaining <= 0) break;
                    $invAmt  = min((float) $inv->net_amount, $remaining);
                    $days    = (int) \Carbon\Carbon::parse($inv->purchase_date)->diffInDays($today);
                    $remaining -= $invAmt;

                    if ($days == 0)      $bucket_current += $invAmt;
                    elseif ($days <= 15) $bucket_15      += $invAmt;
                    elseif ($days <= 30) $bucket_30      += $invAmt;
                    elseif ($days <= 45) $bucket_45      += $invAmt;
                    elseif ($days <= 60) $bucket_60      += $invAmt;
                    elseif ($days <= 75) $bucket_75      += $invAmt;
                    else                 $bucket_90plus  += $invAmt;
                }

                if ($remaining > 0) $bucket_90plus += $remaining;

                $rows[] = [
                    'name'      => $v->name,
                    'mobile'    => $v->phone ?? '',
                    'total'     => $totalBalance,
                    'current'   => $bucket_current,
                    '15d'       => $bucket_15,
                    '30d'       => $bucket_30,
                    '45d'       => $bucket_45,
                    '60d'       => $bucket_60,
                    '75d'       => $bucket_75,
                    '90plus'    => $bucket_90plus,
                ];

                $grandTotal   += $totalBalance;
                $grandCurrent += $bucket_current;
                $grand15      += $bucket_15;
                $grand30      += $bucket_30;
                $grand45      += $bucket_45;
                $grand60      += $bucket_60;
                $grand75      += $bucket_75;
                $grand90plus  += $bucket_90plus;
            }
        }

        return response()->json([
            'as_of_date' => $today->format('d-M-Y'),
            'rows'       => $rows,
            'totals'     => [
                'total'     => $grandTotal,
                'current'   => $grandCurrent,
                '15d'       => $grand15,
                '30d'       => $grand30,
                '45d'       => $grand45,
                '60d'       => $grand60,
                '75d'       => $grand75,
                '90plus'    => $grand90plus,
            ]
        ]);
    }
    public function executive_report()
    {
        return view('admin_panel.reporting.executive_report');
    }

    public function fetch_executive_report(Request $request)
    {
        $today = date('Y-m-d');
        $startOfMonth = date('Y-m-01');
        $endOfMonth = date('Y-m-t');

        // Sales
        $salesToday = DB::table('sales')->whereDate('created_at', $today)->sum('total_net');
        $salesMonth = DB::table('sales')->whereBetween('created_at', [$startOfMonth . ' 00:00:00', $endOfMonth . ' 23:59:59'])->sum('total_net');

        // Purchases
        $purchasesToday = DB::table('purchases')->whereDate('purchase_date', $today)->sum('net_amount');
        $purchasesMonth = DB::table('purchases')->whereBetween('purchase_date', [$startOfMonth, $endOfMonth])->sum('net_amount');

        // Expenses
        $expensesTodayV1 = DB::table('expense_vouchers')->where('entry_date', $today)->sum('total_amount');
        $expensesTodayV2 = DB::table('voucher_masters')->where('voucher_type', 'expense')->where('date', $today)->sum('total_amount');
        $expensesToday = $expensesTodayV1 + $expensesTodayV2;

        $expensesMonthV1 = DB::table('expense_vouchers')->whereBetween('entry_date', [$startOfMonth, $endOfMonth])->sum('total_amount');
        $expensesMonthV2 = DB::table('voucher_masters')->where('voucher_type', 'expense')->whereBetween('date', [$startOfMonth, $endOfMonth])->sum('total_amount');
        $expensesMonth = $expensesMonthV1 + $expensesMonthV2;

        // Cash & Bank Balances
        $cashAccounts = DB::table('accounts')
            ->join('account_heads', 'accounts.head_id', '=', 'account_heads.id')
            ->where('account_heads.name', 'like', '%Cash%')
            ->select('accounts.title', 'accounts.current_balance')
            ->get();

        $bankAccounts = DB::table('accounts')
            ->join('account_heads', 'accounts.head_id', '=', 'account_heads.id')
            ->where('account_heads.name', 'like', '%Bank%')
            ->select('accounts.title', 'accounts.current_balance')
            ->get();

        $balanceService = app(\App\Services\BalanceService::class);
        
        // Receivables (Customers) in Single Query
        $totalReceivables = DB::table('journal_entries')
            ->where('party_type', \App\Models\Customer::class)
            ->selectRaw('COALESCE(SUM(debit) - SUM(credit), 0) as total')
            ->value('total') ?? 0;

        // Payables (Vendors) in Single Query
        $apId = $balanceService->getAccountsPayableId();
        $totalPayables = DB::table('journal_entries')
            ->where('party_type', \App\Models\Vendor::class)
            ->where('account_id', $apId)
            ->selectRaw('COALESCE(SUM(credit) - SUM(debit), 0) as total')
            ->value('total') ?? 0;

        // Top 10 Customers by Profit
        $customers = DB::table('customers')->get();
        $customerProfits = [];
        foreach ($customers as $c) {
            $saleStats = DB::table('sale_items')
                ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
                ->join('products', 'products.id', '=', 'sale_items.product_id')
                ->where('sales.customer_id', $c->id)
                ->selectRaw('
                    SUM(sale_items.total) as revenue, 
                    SUM(sale_items.total_pieces * products.purchase_price_per_piece) as cogs
                ')
                ->first();

            $revenue = (float) ($saleStats->revenue ?? 0);
            $cogs = (float) ($saleStats->cogs ?? 0);
            $profit = $revenue - $cogs;

            if ($revenue > 0) {
                $customerProfits[] = [
                    'id' => $c->id,
                    'name' => $c->customer_name,
                    'profit' => $profit,
                    'revenue' => $revenue,
                    'balance' => $balanceService->getCustomerBalance($c->id)
                ];
            }
        }

        // Sort by profit descending
        usort($customerProfits, fn($a, $b) => $b['profit'] <=> $a['profit']);
        $topCustomers = array_slice($customerProfits, 0, 10);

        return response()->json([
            'sales' => [
                'today' => $salesToday,
                'month' => $salesMonth,
            ],
            'purchases' => [
                'today' => $purchasesToday,
                'month' => $purchasesMonth,
            ],
            'expenses' => [
                'today' => $expensesToday,
                'month' => $expensesMonth,
            ],
            'accounts' => [
                'cash' => $cashAccounts,
                'bank' => $bankAccounts,
            ],
            'receivables' => $totalReceivables,
            'payables' => $totalPayables,
            'top_customers' => $topCustomers,
        ]);
    }

    private function getPurchasedQtyAndNetAmount(int $productId, $dateFilter = [], $warehouseId = null): array
    {
        $query = DB::table('purchase_items')
            ->join('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
            ->where('purchase_items.product_id', $productId)
            ->whereIn('purchases.status_purchase', ['approved', 'posted', 'Returned', 'Partial']);

        if ($warehouseId && $warehouseId !== 'all') {
            $query->where('purchases.warehouse_id', $warehouseId);
        }

        if (is_array($dateFilter) && !empty($dateFilter)) {
            if (isset($dateFilter['before'])) {
                $query->where('purchases.purchase_date', '<=', $dateFilter['before']);
            }
            if (isset($dateFilter['from']) && !empty($dateFilter['from'])) {
                $query->whereDate('purchases.created_at', '>=', $dateFilter['from']);
            }
            if (isset($dateFilter['to']) && !empty($dateFilter['to'])) {
                $query->whereDate('purchases.created_at', '<=', $dateFilter['to']);
            }
        }

        $result = $query->select(DB::raw("
            COALESCE(SUM(purchase_items.qty), 0) as total_qty,
            COALESCE(SUM(
                CASE
                    WHEN COALESCE(purchases.subtotal, 0) > 0
                    THEN purchase_items.line_total / purchases.subtotal * purchases.net_amount
                    ELSE purchase_items.line_total
                END
            ), 0) as total_net_amount
        "))->first();

        return [(float) $result->total_qty, (float) $result->total_net_amount];
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

    /**
     * Match a stock adjustment note to a specific variant based on size and color.
     */
    private function matchAdjustmentToVariant($adjItem, $variant)
    {
        $note = strtolower($adjItem->note ?? '');
        if (empty($note)) {
            return false;
        }

        $vSize  = strtolower(trim($variant['size'] ?? '-'));
        $vColor = strtolower(trim($variant['color'] ?? '-'));
        $vName  = strtolower(trim($variant['name'] ?? ''));

        $sizeMatch = true;
        if ($vSize !== '-' && !empty($vSize)) {
            $pattern = '/\b' . preg_quote($vSize, '/') . '\b/i';
            $sizeMatch = preg_match($pattern, $note) === 1;
        }

        $colorMatch = true;
        if ($vColor !== '-' && !empty($vColor)) {
            $pattern = '/\b' . preg_quote($vColor, '/') . '\b/i';
            $colorMatch = preg_match($pattern, $note) === 1;
        }

        return $sizeMatch && $colorMatch;
    }

    /**
     * Product Sale Customer Wise Report View
     */
    public function product_sale_customer_wise_report(Request $request)
    {
        $customers  = \App\Models\Customer::orderBy('customer_name')->get();
        $products   = \App\Models\Product::orderBy('item_name')->get();
        $categories = \App\Models\Category::orderBy('name')->get();
        $brands     = \App\Models\Brand::orderBy('name')->get();

        return view('admin_panel.reporting.product_sale_customer_wise_report', compact('customers', 'products', 'categories', 'brands'));
    }

    /**
     * Fetch Product Sale Customer Wise Report Data (AJAX with Multi-Select)
     */
    public function fetchProductSaleCustomerWise(Request $request)
    {
        $fromDate = $request->from_date;
        $toDate   = $request->to_date;

        // Normalize filters into arrays for Multi-Select support
        $customerIds = is_array($request->customer_id) ? $request->customer_id : ($request->customer_id && $request->customer_id !== 'all' ? [$request->customer_id] : []);
        $productIds  = is_array($request->product_id) ? $request->product_id : ($request->product_id && $request->product_id !== 'all' ? [$request->product_id] : []);
        $categoryIds = is_array($request->category_id) ? $request->category_id : ($request->category_id && $request->category_id !== 'all' ? [$request->category_id] : []);
        $brandIds    = is_array($request->brand_id) ? $request->brand_id : ($request->brand_id && $request->brand_id !== 'all' ? [$request->brand_id] : []);

        // Filter out 'all' values
        $customerIds = array_filter($customerIds, fn($v) => $v !== 'all' && !empty($v));
        $productIds  = array_filter($productIds, fn($v) => $v !== 'all' && !empty($v));
        $categoryIds = array_filter($categoryIds, fn($v) => $v !== 'all' && !empty($v));
        $brandIds    = array_filter($brandIds, fn($v) => $v !== 'all' && !empty($v));

        // 1. Query Sales Items
        $salesQuery = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->leftJoin('customers', 'sales.customer_id', '=', 'customers.id')
            ->leftJoin('products', 'sale_items.product_id', '=', 'products.id')
            ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->select(
                'sales.customer_id',
                DB::raw("COALESCE(customers.customer_name, 'Walking Customer') as customer_name"),
                DB::raw("COALESCE(customers.customer_id, 'N/A') as customer_code"),
                'customers.mobile as customer_mobile',
                'customers.address as customer_city',
                'sale_items.product_id',
                DB::raw("COALESCE(products.item_code, 'MANUAL') as product_code"),
                DB::raw("COALESCE(sale_items.product_name, products.item_name) as product_name"),
                DB::raw("COALESCE(brands.name, '-') as brand_name"),
                DB::raw("COALESCE(categories.name, '-') as category_name"),
                DB::raw("SUM(sale_items.total_pieces) as sold_qty"),
                DB::raw("SUM(sale_items.total) as gross_amount"),
                DB::raw("COUNT(DISTINCT sales.id) as invoice_count")
            )
            ->whereIn('sales.sale_status', ['posted', 'completed', 'booked', 'draft']);

        if ($fromDate) {
            $salesQuery->whereDate('sales.created_at', '>=', $fromDate);
        }
        if ($toDate) {
            $salesQuery->whereDate('sales.created_at', '<=', $toDate);
        }
        if (!empty($customerIds)) {
            $salesQuery->whereIn('sales.customer_id', $customerIds);
        }
        if (!empty($productIds)) {
            $salesQuery->whereIn('sale_items.product_id', $productIds);
        }
        if (!empty($categoryIds)) {
            $salesQuery->whereIn('products.category_id', $categoryIds);
        }
        if (!empty($brandIds)) {
            $salesQuery->whereIn('products.brand_id', $brandIds);
        }

        $salesData = $salesQuery->groupBy(
            'sales.customer_id',
            'customers.customer_name',
            'customers.customer_id',
            'customers.mobile',
            'customers.address',
            'sale_items.product_id',
            'products.item_code',
            'sale_items.product_name',
            'products.item_name',
            'brands.name',
            'categories.name'
        )->get();

        // 2. Query Returns Data
        $returnQuery = DB::table('sale_return_items')
            ->join('sale_returns', 'sale_return_items.sale_return_id', '=', 'sale_returns.id')
            ->select(
                'sale_returns.customer_id',
                'sale_return_items.product_id',
                DB::raw("SUM(sale_return_items.qty) as return_qty"),
                DB::raw("SUM(sale_return_items.line_total) as return_amount")
            )
            ->whereIn('sale_returns.status', ['approved', 'posted', 'completed']);

        if ($fromDate) {
            $returnQuery->whereDate('sale_returns.created_at', '>=', $fromDate);
        }
        if ($toDate) {
            $returnQuery->whereDate('sale_returns.created_at', '<=', $toDate);
        }
        if (!empty($customerIds)) {
            $returnQuery->whereIn('sale_returns.customer_id', $customerIds);
        }
        if (!empty($productIds)) {
            $returnQuery->whereIn('sale_return_items.product_id', $productIds);
        }

        $returnsData = $returnQuery->groupBy('sale_returns.customer_id', 'sale_return_items.product_id')->get();

        // Build Lookup key for returns: "cust_id_prod_id"
        $returnsLookup = [];
        foreach ($returnsData as $r) {
            $key = ($r->customer_id ?? 0) . '_' . ($r->product_id ?? 0);
            $returnsLookup[$key] = [
                'return_qty' => (float) $r->return_qty,
                'return_amount' => (float) $r->return_amount,
            ];
        }

        // 3. Group by Customer
        $grouped = [];
        $totalInvoicesCount = 0;
        $totalSoldQty = 0;
        $totalReturnQty = 0;
        $totalNetQty = 0;
        $totalGrossAmount = 0;
        $totalReturnAmount = 0;
        $totalNetSaleAmount = 0;

        foreach ($salesData as $row) {
            $custId = $row->customer_id ?? 0;
            $custName = $row->customer_name ?: 'Walking Customer';

            if (!isset($grouped[$custId])) {
                $grouped[$custId] = [
                    'customer_id' => $custId,
                    'customer_name' => $custName,
                    'customer_code' => $row->customer_code,
                    'customer_mobile' => $row->customer_mobile ?: '-',
                    'customer_city' => $row->customer_city ?: '-',
                    'gross_amount' => 0,
                    'return_amount' => 0,
                    'net_amount' => 0,
                    'sold_qty' => 0,
                    'return_qty' => 0,
                    'net_qty' => 0,
                    'products' => [],
                ];
            }

            $prodId = $row->product_id ?? 0;
            $lookupKey = $custId . '_' . $prodId;
            $retQty = isset($returnsLookup[$lookupKey]) ? $returnsLookup[$lookupKey]['return_qty'] : 0;
            $retAmt = isset($returnsLookup[$lookupKey]) ? $returnsLookup[$lookupKey]['return_amount'] : 0;

            $soldQty = (float) $row->sold_qty;
            $grossAmt = (float) $row->gross_amount;
            $netQty = $soldQty - $retQty;
            $netAmt = $grossAmt - $retAmt;
            $avgPrice = $soldQty > 0 ? ($grossAmt / $soldQty) : 0;

            $grouped[$custId]['products'][] = [
                'product_id' => $prodId,
                'product_code' => $row->product_code,
                'product_name' => $row->product_name,
                'brand_name' => $row->brand_name,
                'category_name' => $row->category_name,
                'sold_qty' => $soldQty,
                'avg_price' => $avgPrice,
                'gross_amount' => $grossAmt,
                'return_qty' => $retQty,
                'return_amount' => $retAmt,
                'net_qty' => $netQty,
                'net_amount' => $netAmt,
            ];

            $grouped[$custId]['gross_amount'] += $grossAmt;
            $grouped[$custId]['return_amount'] += $retAmt;
            $grouped[$custId]['net_amount'] += $netAmt;
            $grouped[$custId]['sold_qty'] += $soldQty;
            $grouped[$custId]['return_qty'] += $retQty;
            $grouped[$custId]['net_qty'] += $netQty;

            $totalSoldQty += $soldQty;
            $totalReturnQty += $retQty;
            $totalNetQty += $netQty;
            $totalGrossAmount += $grossAmt;
            $totalReturnAmount += $retAmt;
            $totalNetSaleAmount += $netAmt;
            $totalInvoicesCount += (int) $row->invoice_count;
        }

        $totalCustomersCount = count($grouped);

        $summary = [
            'total_customers' => $totalCustomersCount,
            'total_invoices' => $totalInvoicesCount,
            'total_qty' => number_format($totalSoldQty, 2),
            'total_return_qty' => number_format($totalReturnQty, 2),
            'net_qty' => number_format($totalNetQty, 2),
            'gross_amount' => 'Rs ' . number_format($totalGrossAmount, 2),
            'return_amount' => 'Rs ' . number_format($totalReturnAmount, 2),
            'net_sale_amount' => 'Rs ' . number_format($totalNetSaleAmount, 2),
        ];

        return response()->json([
            'summary' => $summary,
            'customers' => array_values($grouped)
        ]);
    }
}
