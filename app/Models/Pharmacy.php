<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Pharmacy
 * 
 * @property int $id
 * @property string $name
 * @property string $address
 * @property string $contact_number
 * @property int|null $owner_id
 * 
 * @property Collection|Inventory[] $inventories
 * @property Collection|Purchaseorder[] $purchaseorders
 * @property Collection|Sale[] $sales
 * @property Collection|User[] $users
 *
 * @package App\Models
 */
class Pharmacy extends Model
{
	protected $table = 'pharmacy';
	public $timestamps = false;

	protected $casts = [
		'owner_id' => 'int'
	];

	protected $fillable = [
		'name',
		'address',
		'contact_number',
		'owner_id'
	];

	public function inventories()
	{
		return $this->hasMany(Inventory::class);
	}

	public function purchaseorders()
	{
		return $this->hasMany(Purchaseorder::class);
	}

	public function sales()
	{
		return $this->hasMany(Sale::class);
	}
	public function supplier()
	{
		return $this->belongsTo(Supplier::class);
	}

	public function users()
	{
		return $this->hasMany(User::class, 'owner_id', 'id');
	}
	public function owner()
	{
		return $this->belongsTo(User::class, 'owner_id', 'id');
	}
}
