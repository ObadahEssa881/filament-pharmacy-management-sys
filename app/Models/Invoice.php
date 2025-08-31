<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Invoice
 * 
 * @property int $id
 * @property int $order_id
 * @property int $supplier_id
 * @property Carbon $invoice_date
 * @property float $total_amount
 * @property string $payment_status
 * 
 * @property Purchaseorder $purchaseorder
 * @property Supplier $supplier
 *
 * @package App\Models
 */
class Invoice extends Model
{
	protected $table = 'invoice';
	public $timestamps = false;

	protected $casts = [
		'order_id' => 'int',
		'supplier_id' => 'int',
		'invoice_date' => 'datetime',
		'total_amount' => 'float'
	];

	protected $fillable = [
		'order_id',
		'supplier_id',
		'invoice_date',
		'total_amount',
		'payment_status'
	];

	public function purchaseorder()
	{
		return $this->belongsTo(Purchaseorder::class, 'order_id');
	}

	public function supplier()
	{
		return $this->belongsTo(Supplier::class);
	}
}
