<?php

namespace App\Listeners;

use App\Events\MassMessagesEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Conversations;
use App\Models\Messages;
use App\Models\MediaMessages;
use Carbon\Carbon;
use App\Helper;

class MassMessagesListener implements ShouldQueue
{

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  MassMessagesEvent  $event
     * @return void
     */
    public function handle(MassMessagesEvent $event)
    {
      // Get data
      $user = $event->user;
      $fileuploader = $event->fileuploader;
      $messageData = $event->messageData;
      $price = $event->priceMessage;
      $hasFileZip = $event->hasFileZip;
      $file = $event->file;
      $originalName = $event->originalName;
      $size = $event->size;
      $token = $event->token;

        // Get Subscriptions Active
        $subscriptionsActive = $user->mySubscriptions()
            ->where('stripe_id', '=', '')
              ->where('ends_at', '>=', now())
              ->orWhere('stripe_status', 'active')
                ->where('stripe_id', '<>', '')
                  ->whereIn('stripe_price', $user->plans()->pluck('name'))
                  ->orWhere('stripe_id', '=', '')
                ->whereIn('stripe_price', $user->plans()->pluck('name'))
            ->where('free', '=', 'yes')
          ->get();

          // Send an email notification to all subscribers when there is a new post
          foreach ($subscriptionsActive as $subscriber) {

            // Verify Conversation Exists
     				$conversation = Conversations::where('user_1', $user->id)
       				->where('user_2', $subscriber->user()->id)
       				->orWhere('user_1', $subscriber->user()->id)
       				->where('user_2', $user->id)->first();

     				$time = Carbon::now();

             if (! isset($conversation)) {
               $newConversation = new Conversations();
               $newConversation->user_1 = $user->id;
               $newConversation->user_2 = $subscriber->user()->id;
               $newConversation->updated_at = $time;
               $newConversation->save();

               $conversationID = $newConversation->id;

             } else {
               $conversation->updated_at = $time;
               $conversation->save();

               $conversationID = $conversation->id;
             }

              $message = new Messages();
              $message->conversations_id = $conversationID;
       				$message->from_user_id    = $user->id;
       				$message->to_user_id      = $subscriber->user()->id;
       				$message->message         = trim(Helper::checkTextDb($messageData));
       				$message->updated_at      = $time;
              $message->price           = $price;
              $message->save();

               if ($fileuploader) {

                 foreach ($fileuploader as $key => $media) {

                   $files = MediaMessages::whereFile($media['file'])
                   ->where('messages_id', '<>', $message->id)
                   ->groupBy('file')
                   ->get();

                   foreach ($files as $key) {

                     $mediaMessages = new MediaMessages();
                     $mediaMessages->messages_id = $message->id;
                     $mediaMessages->type = $key->type;
                     $mediaMessages->file = $key->file;
                     $mediaMessages->video_poster = $key->video_poster;
                     $mediaMessages->width = $key->width;
                     $mediaMessages->height = $key->height;
                     $mediaMessages->file_name = $key->file_name;
                     $mediaMessages->file_size = $key->file_size;
                     $mediaMessages->token = $key->token;
                     $mediaMessages->status = 'active';
                     $mediaMessages->created_at = now();
                     $mediaMessages->save();

                   }

                   // Delete Old files
                   MediaMessages::whereFile($media['file'])->whereMessagesId(0)->delete();
                 }
               }// Fileuploader

               if ($hasFileZip) {
                 // We insert the file into the database
                 MediaMessages::create([
                   'messages_id' => $message->id,
                   'type' => 'zip',
                   'file' => $file,
                   'file_name' => $originalName,
                   'file_size' => $size,
                   'token' => $token,
                   'status' => 'active',
                   'created_at' => now()
                 ]);
               }// Has file Zip

          }// foreach

    }
}
