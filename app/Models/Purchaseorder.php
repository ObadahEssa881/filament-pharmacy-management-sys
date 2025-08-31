<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class Purchaseorder
 *
 * @property int $id
 * @property int $supplier_id
 * @property int $pharmacy_id
 * @property Carbon $order_date
 * @property Carbon|null $delivery_date
 * @property string $status
 *
 * @property Pharmacy $pharmacy
 * @property Supplier $supplier
 * @property Invoice|null $invoice
 * @property Collection|Purchaseorderitem[] $purchaseorderitems
 *
 * @package App\Models
 */
class Purchaseorder extends Model
{
	protected $table = 'purchaseorder';
	public $timestamps = false;

	protected $casts = [
		'supplier_id' => 'int',
		'pharmacy_id' => 'int',
		'order_date' => 'datetime',
		'delivery_date' => 'datetime'
	];

	protected $fillable = [
		'supplier_id',
		'pharmacy_id',
		'order_date',
		'delivery_date',
		'status'
	];

	public function pharmacy()
	{
		return $this->belongsTo(Pharmacy::class);
	}

	public function supplier()
	{
		return $this->belongsTo(Supplier::class);
	}

	public function invoice()
	{
		return $this->hasOne(Invoice::class, 'order_id');
	}

	public function purchaseorderitems()
	{
		return $this->hasMany(Purchaseorderitem::class, 'order_id');
	}

	public static function booted()
	{
		static::creating(function ($model) {
			$model->pharmacy_id = auth()->user()->pharmacy_id;
		});
	}
	/** Scopes */
	public function scopeDateBetween(Builder $q, ?string $start, ?string $end): Builder
	{
		if ($start) $q->where('order_date', '>=', \Carbon\Carbon::parse($start)->startOfDay());
		if ($end)   $q->where('order_date', '<=', \Carbon\Carbon::parse($end)->endOfDay());
		return $q;
	}

	public function scopePharmacy(Builder $q, ?int $pharmacyId): Builder
	{
		return $pharmacyId ? $q->where('pharmacy_id', $pharmacyId) : $q;
	}

	public function scopeSupplier(Builder $q, ?int $supplierId): Builder
	{
		return $supplierId ? $q->where('supplier_id', $supplierId) : $q;
	}

	public function scopeWarehouse(Builder $q, ?int $warehouseId): Builder
	{
		if ($warehouseId) {
			$q->whereHas('supplier', function ($sub) use ($warehouseId) {
				$sub->where('warehouseId', $warehouseId);
			});
		}
		return $q;
	}
	public function scopeStatuses(Builder $q, ?array $statuses): Builder
	{
		return ($statuses && count($statuses)) ? $q->whereIn('status', $statuses) : $q;
	}
}
