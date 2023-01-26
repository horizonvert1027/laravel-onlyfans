<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversations extends Model
{
  protected $guarded = array();
  public $timestamps = false;

  public function user()
  {
        return $this->belongsTo('App\Models\User')->first();
    }

  public function last()
  {
      return $this->hasMany('App\Models\Messages','conversations_id')
          ->where('messages.mode', 'active')
          ->orderBy('messages.updated_at', 'DESC')
          ->take(1)
          ->first();
  }

  public function messages()
  {
      return $this->hasMany('App\Models\Messages','conversations_id')
        ->where('messages.mode', 'active')
        ->orderBy('messages.updated_at', 'DESC');
    }

  public function from()
  {
        return $this->belongsTo('App\Models\User', 'from_user_id')->first();
    }

  public function to()
  {
        return $this->belongsTo('App\Models\User', 'to_user_id')->first();
    }
}
