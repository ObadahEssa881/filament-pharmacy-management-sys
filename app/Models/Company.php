<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Company
 * 
 * @property int $id
 * @property string $name
 * @property string|null $contact_person
 * @property string $phone
 * @property string $email
 * @property string $address
 * 
 * @property Collection|Medicine[] $medicines
 *
 * @package App\Models
 */
class Company extends Model
{
	protected $table = 'company';
	public $timestamps = false;

	protected $fillable = [
		'name',
		'contact_person',
		'phone',
		'email',
		'address'
	];

	public function medicines()
	{
		return $this->hasMany(Medicine::class);
	}
}
