<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderReport extends Model
{
    protected $table = 'orders_reports';

    protected $fillable = [
        'order_id',
        'total_revenue',
        'total_cost',
        'total_profit',
    ];

    public function productReports()
    {
        return $this->hasMany(ProductReport::class, 'order_report_id');
    }
}