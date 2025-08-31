<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Medicine
 * 
 * @property int $id
 * @property string $name
 * @property string $titer
 * @property int $category_id
 * @property int $company_id
 * @property float $unit_price
 * @property int $supplier_id
 * @property string|null $Type
 * 
 * @property Category $category
 * @property Company $company
 * @property Supplier $supplier
 * @property Collection|Inventory[] $inventories
 * @property Collection|Purchaseorderitem[] $purchaseorderitems
 * @property Collection|Saleitem[] $saleitems
 *
 * @package App\Models
 */
class Medicine extends Model
{
	protected $table = 'medicine';
	public $timestamps = false;

	protected $casts = [
		'category_id' => 'int',
		'company_id' => 'int',
		'unit_price' => 'float',
		'supplier_id' => 'int'
	];

	protected $fillable = [
		'name',
		'titer',
		'category_id',
		'company_id',
		'unit_price',
		'supplier_id',
		'Type'
	];

	public function category()
	{
		return $this->belongsTo(Category::class);
	}

	public function company()
	{
		return $this->belongsTo(Company::class);
	}

	public function supplier()
	{
		return $this->belongsTo(Supplier::class);
	}

	public function inventories()
	{
		return $this->hasMany(Inventory::class);
	}

	public function purchaseorderitems()
	{
		return $this->hasMany(Purchaseorderitem::class);
	}

	public function saleitems()
	{
		return $this->hasMany(Saleitem::class);
	}
}
