<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Warehouse
 * 
 * @property int $id
 * @property string $name
 * @property string $address
 * @property int|null $owner_id
 * @property string $contact_number
 * 
 * @property Collection|Inventory[] $inventories
 * @property Collection|Supplier[] $suppliers
 *
 * @package App\Models
 */
class Warehouse extends Model
{
	protected $table = 'warehouse';
	public $timestamps = false;

	protected $casts = [
		'owner_id' => 'int'
	];

	protected $fillable = [
		'name',
		'address',
		'owner_id',
		'contact_number'
	];

	public function inventories()
	{
		return $this->hasMany(Inventory::class);
	}

	public function suppliers()
	{
		return $this->hasMany(Supplier::class, 'warehouseId');
	}
}
