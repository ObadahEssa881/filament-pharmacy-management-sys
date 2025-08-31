<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Saleitem
 * 
 * @property int $id
 * @property int $sale_id
 * @property int $medicine_id
 * @property int $quantity
 * @property float $unit_price
 * 
 * @property Medicine $medicine
 * @property Sale $sale
 *
 * @package App\Models
 */
class Saleitem extends Model
{
	protected $table = 'saleitem';
	public $timestamps = false;

	protected $casts = [
		'sale_id' => 'int',
		'medicine_id' => 'int',
		'quantity' => 'int',
		'unit_price' => 'float',
		'cost_price' => 'float'

	];

	protected $fillable = [
		'sale_id',
		'medicine_id',
		'quantity',
		'unit_price',
		'cost_price'
	];

	public function medicine()
	{
		return $this->belongsTo(Medicine::class);
	}

	public function sale()
	{
		return $this->belongsTo(Sale::class, 'sale_id');
	}
}
