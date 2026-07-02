<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\PurchaseOrder;
use App\Models\GoodsReceivedNote;
use App\Models\MaterialRequest;
use App\Models\Dispatch;
use App\Models\Supplier;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\ProductionRun;
use Illuminate\Http\Request;

class UniversalSearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $results = [];

        // 1. Materials
        $materials = Material::where('name', 'like', "%{$query}%")
            ->orWhere('sku', 'like', "%{$query}%")
            ->limit(5)
            ->get();
        if ($materials->isNotEmpty()) {
            foreach ($materials as $m) {
                $results[] = [
                    'category' => 'Materials',
                    'title' => "{$m->name} ({$m->sku})",
                    'url' => route('materials.show', $m->id),
                    'subtitle' => ucfirst($m->category) . " - Unit: {$m->unit}"
                ];
            }
        }

        // 2. Purchase Orders
        $pos = PurchaseOrder::where('po_number', 'like', "%{$query}%")
            ->with('supplier')
            ->limit(5)
            ->get();
        if ($pos->isNotEmpty()) {
            foreach ($pos as $po) {
                $results[] = [
                    'category' => 'Purchase Orders',
                    'title' => $po->po_number,
                    'url' => route('purchase-orders.show', $po->id),
                    'subtitle' => "Supplier: " . ($po->supplier->name ?? 'N/A') . " - Status: " . ucfirst($po->status)
                ];
            }
        }

        // 3. GRNs
        $grns = GoodsReceivedNote::where('grn_number', 'like', "%{$query}%")
            ->limit(5)
            ->get();
        if ($grns->isNotEmpty()) {
            foreach ($grns as $grn) {
                $results[] = [
                    'category' => 'Goods Received Notes',
                    'title' => $grn->grn_number,
                    'url' => route('grns.show', $grn->id),
                    'subtitle' => "Received Date: " . ($grn->received_date ? $grn->received_date->format('d M Y') : 'N/A')
                ];
            }
        }

        // 4. Material Requests
        $mrs = MaterialRequest::where('mr_number', 'like', "%{$query}%")
            ->limit(5)
            ->get();
        if ($mrs->isNotEmpty()) {
            foreach ($mrs as $mr) {
                $results[] = [
                    'category' => 'Material Requests',
                    'title' => $mr->mr_number,
                    'url' => route('material-requests.show', $mr->id),
                    'subtitle' => "Status: " . ucfirst($mr->status)
                ];
            }
        }

        // 5. Dispatches
        $dispatches = Dispatch::where('dispatch_number', 'like', "%{$query}%")
            ->with('outlet')
            ->limit(5)
            ->get();
        if ($dispatches->isNotEmpty()) {
            foreach ($dispatches as $d) {
                $results[] = [
                    'category' => 'Dispatches',
                    'title' => $d->dispatch_number,
                    'url' => route('dispatches.show', $d->id),
                    'subtitle' => "Outlet: " . ($d->outlet->name ?? 'N/A') . " - Status: " . ucfirst($d->status)
                ];
            }
        }

        // 6. Suppliers
        $suppliers = Supplier::where('name', 'like', "%{$query}%")
            ->limit(5)
            ->get();
        if ($suppliers->isNotEmpty()) {
            foreach ($suppliers as $s) {
                $results[] = [
                    'category' => 'Suppliers',
                    'title' => $s->name,
                    'url' => route('suppliers.show', $s->id),
                    'subtitle' => "Contact: " . ($s->contact_person ?? 'N/A')
                ];
            }
        }

        // 7. Outlets
        $outlets = Outlet::where('name', 'like', "%{$query}%")
            ->limit(5)
            ->get();
        if ($outlets->isNotEmpty()) {
            foreach ($outlets as $o) {
                $results[] = [
                    'category' => 'Outlets',
                    'title' => $o->name,
                    'url' => route('outlets.show', $o->id),
                    'subtitle' => "Type: " . ucfirst($o->type)
                ];
            }
        }

        // 8. Products
        $products = Product::where('name', 'like', "%{$query}%")
            ->orWhere('sku', 'like', "%{$query}%")
            ->limit(5)
            ->get();
        if ($products->isNotEmpty()) {
            foreach ($products as $p) {
                $results[] = [
                    'category' => 'Products',
                    'title' => "{$p->name} ({$p->sku})",
                    'url' => route('products.show', $p->id),
                    'subtitle' => "Price: ₹" . number_format($p->retail_price, 2)
                ];
            }
        }

        // 9. Production Runs
        $runs = ProductionRun::where('run_number', 'like', "%{$query}%")
            ->with('product')
            ->limit(5)
            ->get();
        if ($runs->isNotEmpty()) {
            foreach ($runs as $r) {
                $results[] = [
                    'category' => 'Production Runs',
                    'title' => $r->run_number,
                    'url' => route('production-runs.show', $r->id),
                    'subtitle' => "Product: " . ($r->product->name ?? 'N/A') . " - Status: " . ucfirst($r->status)
                ];
            }
        }

        return response()->json($results);
    }
}
