<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaMessages extends Model
{
  protected $fillable = [
    'messages_id',
    'type',
    'file',
    'width',
    'height',
    'video_poster',
    'file_name',
    'file_size',
    'token',
    'status',
    'created_at'
  ];

  public function messages() {
        return $this->belongsTo('App\Models\Messages');
    }
}
