<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Messages extends Model
{
  protected $guarded = array();

  public function user()
  {
    return $this->belongsTo('App\Models\User', 'from_user_id')->first();
  }

  public function from()
  {
    return $this->belongsTo('App\Models\User', 'from_user_id')->first();
  }

  public function to()
  {
    return $this->belongsTo('App\Models\User', 'to_user_id')->first();
  }

  public static function markSeen()
  {
    $this->timestamps = false;
    $this->status = 'readed';
    $this->save();
  }

  public function media() {
		return $this->hasMany('App\Models\MediaMessages')->where('status', 'active')->orderBy('id','asc');
	}
}
