<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Purchaseorderitem
 * 
 * @property int $id
 * @property int $order_id
 * @property int $medicine_id
 * @property int $quantity
 * @property float $unit_price
 * 
 * @property Medicine $medicine
 * @property Purchaseorder $purchaseorder
 *
 * @package App\Models
 */
class Purchaseorderitem extends Model
{
	protected $table = 'purchaseorderitem';
	public $timestamps = false;

	protected $casts = [
		'order_id' => 'int',
		'medicine_id' => 'int',
		'quantity' => 'int',
		'unit_price' => 'float'
	];

	protected $fillable = [
		'order_id',
		'medicine_id',
		'quantity',
		'unit_price'
	];

	public function medicine()
	{
		return $this->belongsTo(Medicine::class);
	}

	public function purchaseorder()
	{
		return $this->belongsTo(Purchaseorder::class, 'order_id');
	}
}
