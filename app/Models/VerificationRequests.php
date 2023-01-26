<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

  class VerificationRequests extends Model
{
  protected $guarded = array();
  const UPDATED_AT = null;

  public function user(){
    return $this->belongsTo('App\Models\User')->first();
  }
}
