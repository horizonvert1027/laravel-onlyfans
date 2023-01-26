<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Replies extends Model 
{
	protected $guarded = [];

	public function user()
    {
        return $this->belongsTo(User::class)->first();
    }

    public function updates()
	{
		return $this->belongsTo(Updates::class)->first();
	}

	public function comment()
    {
        return $this->belongsTo(Comments::class);
    }

    public function likes()
	{
		return $this->hasMany(CommentsLikes::class);
	}

}
