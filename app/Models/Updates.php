<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Updates extends Model
{
	protected $guarded = [];
	public $timestamps = false;

	public function user()
	{
		return $this->belongsTo(User::class)->first();
	}

	public function media()
	{
		return $this->hasMany(Media::class)->where('status', 'active')->orderBy('id','asc');
	}

	public function likes()
	{
		return $this->hasMany(Like::class)->where('status', '1');
	}

	public function comments()
	{
		return $this->hasMany(Comments::class);
	}

	public function replies()
	{
		return $this->hasMany(Replies::class);
	}

	public function bookmarks()
	{
		return $this->belongsToMany(User::class, 'bookmarks','updates_id','user_id');
	}

	public function totalComments()
	{
		$post = $this->withCount(['comments', 'replies'])->whereId($this->id)->get();

		return number_format($post[0]->comments_count + $post[0]->replies_count);
	}

	public function videoViews()
	{
		return $this->hasMany(VideoViews::class);
	}

}
