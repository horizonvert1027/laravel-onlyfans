<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transactions extends Model
{
    const UPDATED_AT = null;

    public function user() {
          return $this->belongsTo('App\Models\User')->first();
      }

		public function subscribed() {
	        return $this->belongsTo('App\Models\User', 'subscribed')->first();
	    }

    public function subscription() {
	        return $this->belongsTo('App\Models\Subscriptions', 'subscriptions_id')->first();
	    }
}
