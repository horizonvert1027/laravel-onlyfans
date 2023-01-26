<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comments extends Model
{
	protected $guarded = [];
	const CREATED_AT = 'date';
	const UPDATED_AT = null;

	public function user()
	{
		return $this->belongsTo(User::class)->first();
	}

	public function updates()
	{
		return $this->belongsTo(Updates::class)->first();
	}

	public function likes()
	{
		return $this->hasMany(CommentsLikes::class);
	}

	public function replies()
	{
		return $this->hasMany(Replies::class);
	}
}
