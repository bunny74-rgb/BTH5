<?php

namespace App\Http\Controllers;

use App\Models\OrderReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;

class OrderReportController extends Controller
{
    // GET /api/reports/orders
    public function index()
    {
        $reports = OrderReport::with('productReports')->get();
        return response()->json($reports, 200);
    }

    // GET /api/reports/orders/{id}
    public function show($id)
    {
        $report = OrderReport::with('productReports')->find($id);
        if (!$report) {
            return response()->json(['message' => 'Order report not found'], 404);
        }
        return response()->json($report, 200);
    }

    // POST /api/reports/orders
    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer',
            'total_revenue' => 'nullable|numeric|min:0',
            'total_cost' => 'nullable|numeric|min:0',
        ]);

        $token = $request->bearerToken();
        $orderUrl = rtrim(config('services.order.url', env('ORDER_SERVICE_URL')), '/') . '/orders/' . $request->order_id;
        $orderData = [];

        try {
            $response = Http::withToken($token)
                ->acceptJson()
                ->timeout(3)
                ->get($orderUrl);

            if ($response->successful()) {
                $orderData = $response->json() ?? [];
            }
        } catch (ConnectionException) {
            $orderData = [];
        }

        // Tính toán doanh thu, chi phí, lợi nhuận (điều chỉnh key theo JSON thực tế của Order Service)
        $totalRevenue = $orderData['total_price'] ?? $request->input('total_revenue', 0);
        $totalCost    = $orderData['total_cost'] ?? $request->input('total_cost', 0);
        $totalProfit  = $totalRevenue - $totalCost;

        $report = OrderReport::create([
            'order_id'      => $request->order_id,
            'total_revenue' => $totalRevenue,
            'total_cost'    => $totalCost,
            'total_profit'  => $totalProfit,
        ]);

        return response()->json($report, 201);
    }

    // DELETE /api/reports/orders/{id}
    public function destroy($id)
    {
        $report = OrderReport::find($id);
        if (!$report) {
            return response()->json(['message' => 'Order report not found'], 404);
        }

        $report->delete();
        return response()->json(['message' => 'Deleted successfully'], 200);
    }
}