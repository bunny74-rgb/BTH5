<?php

namespace App\Http\Controllers;

use App\Models\ProductReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;

class ProductReportController extends Controller
{
    // GET /api/reports/products
    public function index()
    {
        $reports = ProductReport::with('orderReport')->get();
        return response()->json($reports, 200);
    }

    // GET /api/reports/products/{id}
    public function show($id)
    {
        $report = ProductReport::with('orderReport')->find($id);
        if (!$report) {
            return response()->json(['message' => 'Product report not found'], 404);
        }
        return response()->json($report, 200);
    }

    // POST /api/reports/products
    public function store(Request $request)
    {
        $request->validate([
            'product_id'      => 'required|integer',
            'order_report_id' => 'nullable|exists:orders_reports,id',
            'total_sold'      => 'required|integer|min:1',
            'price'           => 'nullable|numeric|min:0',
            'cost_price'      => 'nullable|numeric|min:0',
        ]);

        $token = $request->bearerToken();
        $productUrl = rtrim(config('services.product.url', env('PRODUCT_SERVICE_URL')), '/') . '/products/' . $request->product_id;
        $productData = [];
        $serviceUnavailable = false;

        try {
            $response = Http::withToken($token)
                ->acceptJson()
                ->timeout(3)
                ->get($productUrl);

            if ($response->successful()) {
                $productData = $response->json() ?? [];
            } else {
                $serviceUnavailable = true;
            }
        } catch (ConnectionException) {
            $productData = [];
            $serviceUnavailable = true;
        }

        $hasPriceFallback = $request->has('price');
        $hasCostFallback = $request->has('cost_price');

        if ($serviceUnavailable && (!$hasPriceFallback || !$hasCostFallback)) {
            return response()->json([
                'message' => 'Product Service unavailable',
                'product_url' => $productUrl,
            ], 503);
        }

        // Tính toán nghiệp vụ (điều chỉnh tên trường price/cost theo Product Service của bạn)
        $unitPrice = $productData['price'] ?? $request->input('price', 0);
        $unitCost  = $productData['cost_price'] ?? $request->input('cost_price', 0);
        $totalSold = $request->total_sold;

        $revenue = $unitPrice * $totalSold;
        $cost    = $unitCost * $totalSold;
        $profit  = $revenue - $cost;

        $report = ProductReport::create([
            'order_report_id' => $request->order_report_id,
            'product_id'      => $request->product_id,
            'total_sold'      => $totalSold,
            'revenue'         => $revenue,
            'cost'            => $cost,
            'profit'          => $profit,
        ]);

        return response()->json($report, 201);
    }

    // DELETE /api/reports/products/{id}
    public function destroy($id)
    {
        $report = ProductReport::find($id);
        if (!$report) {
            return response()->json(['message' => 'Product report not found'], 404);
        }

        $report->delete();
        return response()->json(['message' => 'Deleted successfully'], 200);
    }
}