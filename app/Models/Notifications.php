<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\Traits\PushNotificationTrait;

class Notifications extends Model
{
	use PushNotificationTrait;

	protected $guarded = [];
	const UPDATED_AT = null;

	public function user()
	{
		return $this->belongsTo(User::class)->first();
	}

	public static function send($userDestination, $userAuthor, $type, $target)
	{
		$settings = AdminSettings::select('push_notification_status')->first();
		$user   = User::find($userDestination);
		$author = User::find($userAuthor);
		$getPushNotificationDevices = $user->oneSignalDevices->pluck('player_id')->all();

		if ($type == 5 && $user->notify_new_tip == 'no' || $type == 6 && $user->notify_new_ppv == 'no') {
			return false;
		}

		self::create([
			'destination' => $userDestination,
			'author' => $userAuthor,
			'type' => $type,
			'target' => $target
	]);

	// Send push notification
	if ($settings->push_notification_status && $getPushNotificationDevices) {

		try {
			$authorName = $author->hide_name == 'yes' ? $author->username : $author->name;
			$post       = Updates::find($target);
			$postUrl    = $post ? url($post->user()->username.'/'.'post', $post->id) : null;

			app()->setLocale($user->language);

			switch ($type) {
				case 1:
					$msg             = $authorName . ' ' . trans('users.has_subscribed');
					$linkDestination = url('notifications');
					break;
				case 2:
					$msg             = $authorName . ' ' . trans('users.like_you');
					$linkDestination = $postUrl;
					break;
				case 3:
					$msg             = $authorName . ' ' . trans('users.comment_you');
					$linkDestination = $postUrl;
					break;
				case 4:
					$msg             = $authorName . ' ' . trans('general.like_your_comment');
					$linkDestination = $postUrl;
					break;

				case 5:
					$msg             = $authorName . ' ' . trans('general.has_sent_you_tip');
					$linkDestination = url('my/payments/received');
					break;

				case 6:
					$msg         	 = $authorName . ' ' . trans('general.has_bought_your_message');
					$linkDestination = url('messages', $user->id);
					break;

				case 7:
					$msg         	 = $authorName . ' ' . trans('general.has_bought_your_content');
					$linkDestination = $postUrl;
					break;

				case 8:
					$msg          	 = trans('general.has_approved_your_post');
					$linkDestination = $postUrl;
					break;

				case 9:
					$msg          	 = trans('general.video_processed_successfully_post');
					$linkDestination = $postUrl;
					break;

				case 10:
					$msg             = trans('general.video_processed_successfully_message');
					$linkDestination = url('notifications');
					break;

				case 11:
					$msg             = trans('general.referrals_made_transaction');
					$linkDestination = url('my/referrals');
					break;

				case 12:
					$msg         	 = trans('general.payment_received_subscription_renewal');
					$linkDestination = url('my/payments/received');
					break;

				case 13:
					$msg          	 = $authorName . ' ' . trans('general.has_changed_subscription_paid');
					$linkDestination = url($author->username);
					break;

				case 14:
					$msg             = $authorName . ' ' . trans('general.is_streaming_live');
					$linkDestination = url('live', $author->username);
					break;

				case 15:
					$msg         	 = $authorName . ' ' . trans('general.has_bought_your_item');
					$linkDestination = url('my/sales');
					break;

				case 16:
					$msg             = $authorName . ' ' . trans('general.has_mentioned_you_post');
					$linkDestination = $postUrl;
					break;

				case 17:
					$msg             = trans('general.story_successfully_posted');
					$linkDestination = url('/');
					break;
			}

			// Send push notification
			PushNotificationTrait::sendPushNotification($msg, $linkDestination, $getPushNotificationDevices);

		} catch (\Exception $e) {
			\Log::info('Push Notification Error - '.$e->getMessage());
		}
		
	}
  }

}
