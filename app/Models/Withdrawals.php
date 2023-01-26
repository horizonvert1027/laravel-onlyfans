<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Withdrawals extends Model {

	protected $guarded = array();
	const CREATED_AT = 'date';
	const UPDATED_AT = null;

	public function user() {
        return $this->belongsTo('App\Models\User')->first();
    }
}
