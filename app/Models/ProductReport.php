<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductReport extends Model
{
    protected $table = 'product_reports';

    protected $fillable = [
        'order_report_id',
        'product_id',
        'total_sold',
        'revenue',
        'cost',
        'profit',
    ];

    public function orderReport()
    {
        return $this->belongsTo(OrderReport::class, 'order_report_id');
    }
}