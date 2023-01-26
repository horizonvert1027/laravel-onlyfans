<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\AdminSettings;
use App\Models\User;
use App\Models\Notifications;
use Mail;

class Subscriptions extends Model
{
	protected $guarded = [];

	public function user()
	{
		return $this->belongsTo(User::class)->first();
	}

	public function subscribed()
	{
		return $this->belongsToMany(
					User::class,
					Plans::class,
					'name',
					'user_id',
					'stripe_price',
					'id'
				)->first();
	}

	public static function sendEmailAndNotify($subscriber, $user)
	{
		$user = User::find($user);

		// Set Lang user
		app()->setLocale($user->language);

		$settings     = AdminSettings::select('title', 'email_no_reply')->first();
		$titleSite    = $settings->title;
		$sender       = $settings->email_no_reply;
		$emailUser    = $user->email;
		$fullNameUser = $user->name;
		$subject      = $subscriber.' '.trans('users.has_subscribed');
		
		try {

			if ($user->email_new_subscriber == 'yes') {				
			//<------ Send Email to User ---------->>>
			Mail::send('emails.new_subscriber', [
				'body' => $subject,
				'title_site' => $titleSite,
				'fullname'   => $fullNameUser
			],
				function($message) use ($sender, $subject, $fullNameUser, $titleSite, $emailUser)
					{
					$message->from($sender, $titleSite)
										->to($emailUser, $fullNameUser)
										->subject($subject.' - '.$titleSite);
					});
				//<------ End Send Email to User ---------->>>
				}

		} catch (\Exception $e) {
			\Log::info('Error send email new Subscriber ---'. $e->getMessage());
		}
		
		if ($user->notify_new_subscriber == 'yes') {
			// Send Notification to User --- destination, author, type, target
			Notifications::send($user->id, auth()->user()->id, '1', $user->id);
		}
	}

}
