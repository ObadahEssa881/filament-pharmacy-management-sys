<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Sale
 *
 * @property int $id
 * @property int $pharmacy_id
 * @property string|null $customer_name
 * @property Carbon $sale_date
 * @property float $total_amount
 * @property string $payment_mode
 *
 * @property Pharmacy $pharmacy
 * @property Collection|Saleitem[] $saleitems
 *
 * @package App\Models
 */
class Sale extends Model
{
	protected $table = 'sale';
	public $timestamps = false;

	protected $casts = [
		'pharmacy_id' => 'int',
		'sale_date' => 'datetime',
		'total_amount' => 'float'
	];

	protected $fillable = [
		'pharmacy_id',
		'customer_name',
		'sale_date',
		'total_amount',
		'payment_mode'
	];

	public function pharmacy()
	{
		return $this->belongsTo(Pharmacy::class);
	}

	public function saleitems()
	{
		return $this->hasMany(Saleitem::class, 'sale_id');
	}

	public static function booted()
	{
		static::creating(function ($model) {
			$model->pharmacy_id = auth()->user()->pharmacy_id;
		});
	}
}
