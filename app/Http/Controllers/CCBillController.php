<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\AdminSettings;
use App\Models\Subscriptions;
use App\Models\Notifications;
use App\Models\PaymentGateways;
use App\Models\Transactions;
use App\Models\Conversations;
use App\Models\Deposits;
use App\Models\Messages;
use App\Models\Updates;
use App\Models\User;
use Carbon\Carbon;
use App\Helper;
use Mail;

class CCBillController extends Controller
{
  use Traits\Functions;

  public function __construct(AdminSettings $settings, Request $request) {
		$this->settings = $settings::first();
		$this->request = $request;
	}

  /**
   * Show/Send form PayPal
   *
   * @return response
   */
    public function show()
    {

    if (! $this->request->expectsJson()) {
        abort(404);
    }

    // Find the User
    $user = User::whereVerifiedId('yes')
        ->whereId($this->request->id)
        ->where('id', '<>', auth()->id())
        ->firstOrFail();

    // Check if Plan exists
    $plan = $user->plans()
      ->whereInterval($this->request->interval)
         ->firstOrFail();

      // Get Payment Gateway
      $payment = PaymentGateways::findOrFail($this->request->payment_gateway);

      $currencyCodes = [
        'AUD' => 036,
        'CAD' => 124,
        'JPY' => 392,
        'GBP' => 826,
        'USD' => 840,
        'EUR' => 978
      ];

      switch ($plan->interval) {
        case 'weekly':
          $interval = 7;
          break;

        case 'monthly':
          $interval = 30;
          break;

        case 'quarterly':
          $interval = 90;
          break;

        case 'biannually':
          $interval = 180;
          break;

        case 'yearly':
          $interval = 365;
          break;
      }

      $formPrice = Helper::amountGross($plan->price);
      $formInitialPeriod = $interval;
      $formNumRebills = 99;
      $currencyCode = array_key_exists($this->settings->currency_code, $currencyCodes) ? $currencyCodes[$this->settings->currency_code] : 840;

      // Hash
      $hash = md5($formPrice . $formInitialPeriod . $formPrice . $formInitialPeriod . $formNumRebills . $currencyCode . $payment->ccbill_salt);

      // Redirect to CCBill
      $input['clientAccnum']    = $payment->ccbill_accnum;
      $input['clientSubacc']    = $payment->ccbill_subacc;
      $input['initialPrice']    = $formPrice;
  		$input['initialPeriod']   = $formInitialPeriod;
  		$input['recurringPrice']  = $formPrice;
  		$input['recurringPeriod'] = $formInitialPeriod;
  		$input['numRebills']      = $formNumRebills;
      $input['formDigest']      = $hash;
      $input['currencyCode']    = $currencyCode;
      $input['type']            = 'subscription';
      $input['creator']         = $user->id ?? null;
      $input['user']            = auth()->id();
      $input['interval']        = $interval;
      $input['planInterval']    = $plan->interval;
      $input['user']            = auth()->id();
      $input['priceOriginal']   = $plan->price;
      $input['taxes']           = auth()->user()->taxesPayable();

      // Base url
      $baseURL = 'https://api.ccbill.com/wap-frontflex/flexforms/' . $payment->ccbill_flexid;

      // Build redirect url
      $inputs = http_build_query($input);
      $redirectUrl = $baseURL . '?' . $inputs;

			return response()->json([
					        'success' => true,
                  'url' => $redirectUrl,
					    ]);

    } // End method show

    public function webhooks(Request $request)
    {
      // Get Payment Gateway
  		$payment = PaymentGateways::whereName('CCBill')->firstOrFail();

      $currencyCodes = [
  			'AUD' => 036,
  			'CAD' => 124,
  			'JPY' => 392,
  			'GBP' => 826,
  			'USD' => 840,
  			'EUR' => 978
  		];

      $amount = $request->subscriptionInitialPrice;
      $digest = $request->dynamicPricingValidationDigest;

      $currencyCode = array_key_exists($this->settings->currency_code, $currencyCodes) ? $currencyCodes[$this->settings->currency_code] : 840;

      if (isset($request->{'X-type'}) && $request->{'X-type'} == 'subscription') {

        $formInitialPeriod = $request->{'X-interval'};
        $formNumRebills = 99;

        // Hash
        $hash = md5($amount . $formInitialPeriod . $amount . $formInitialPeriod . $formNumRebills . $currencyCode . $payment->ccbill_salt);

      } else {
        $formInitialPeriod = 2;

        // Hash
        $hash = md5($amount . $formInitialPeriod . $currencyCode . $payment->ccbill_salt);
      }

      // Validateion the hash
      if ($hash != $digest) {
          // Return error
          return response('Error Hash, Hash Mismatch');
      }

      // Event type
      switch ($request->eventType) {

        case 'NewSaleSuccess':

          if ($request->{'X-type'} == 'subscription') {

            // Find user
            $creator = User::whereId($request->{'X-creator'})
                ->whereVerifiedId('yes')
                  ->firstOrFail();

            // Check if Plan exists
            $plan = $creator->plans()
              ->whereInterval($request->{'X-planInterval'})
                 ->firstOrFail();

            // Subscription ID
            $subscr_id = $request->subscriptionId;

            // Amount
            $amount = $request->subscriptionInitialPrice;

            $userID = $request->{'X-user'};

            // Subscription
            $subscription = Subscriptions::where('subscription_id', $subscr_id)->first();

            if (! isset($subscription)) {
              // Insert DB
              $subscription          = new Subscriptions();
              $subscription->user_id = $userID;
              $subscription->stripe_price = $plan->name;
              $subscription->subscription_id = $subscr_id;
              $subscription->ends_at = $creator->planInterval($plan->interval);
              $subscription->interval = $plan->interval;
              $subscription->save();
            }

            // Admin and user earnings calculation
            $earnings = $this->earningsAdminUser($creator->custom_fee, $request->{'X-priceOriginal'}, $payment->fee, $payment->fee_cents);

            // Insert Transaction
            $this->transaction(
                $request->transactionId,
                $userID,
                $subscription->id,
                $creator->id,
                $amount,
                $earnings['user'],
                $earnings['admin'],
                'CCBill',
                'subscription',
                $earnings['percentageApplied'],
                $request->{'X-taxes'} ?? null
              );

           // Add Earnings to User
           $creator->increment('balance', $earnings['user']);

         } elseif ($request->{'X-type'} == 'tip') {

            // Amount
            $amount = $request->subscriptionInitialPrice;

            $userID = $request->{'X-user'};

            // Verify transaction
            $verifyTxnId = Transactions::where('txn_id', $request->transactionId)->first();

            if (! isset($verifyTxnId)) {

              // Find creator
              $creator = User::findOrFail($request->{'X-creator'});

              // Admin and user earnings calculation
              $earnings = $this->earningsAdminUser($creator->custom_fee, $request->{'X-amountFixed'}, $payment->fee, $payment->fee_cents);

              // Insert Transaction
              $this->transaction(
                  $request->transactionId,
                  $userID,
                  0,
                  $creator->id,
                  $request->{'X-amountFixed'},
                  $earnings['user'],
                  $earnings['admin'],
                  'CCBill',
                  'tip',
                  $earnings['percentageApplied'],
                  $request->{'X-taxes'}
                );

              // Add Earnings to User
              $creator->increment('balance', $earnings['user']);

              // Send Notification
              Notifications::send($creator->id, $userID, '5', $userID);

              //= Check if the tip is sent by message
              if (isset($request->{'X-isMessage'})) {

                // Verify Conversation Exists
                $conversation = Conversations::where('user_1', $userID)
                  ->where('user_2', $creator->id)
                  ->orWhere('user_1', $creator->id)
                  ->where('user_2', $userID)->first();

                  if (! isset($conversation)) {
                    $newConversation = new Conversations();
                    $newConversation->user_1 = $userID;
                    $newConversation->user_2 = $creator->id;
                    $newConversation->updated_at = now();
                    $newConversation->save();

                    $conversationID = $newConversation->id;

                  } else {
                    $conversation->updated_at = now();
                    $conversation->save();

                    $conversationID = $conversation->id;
                  }

                  $message = new Messages();
                  $message->conversations_id = $conversationID;
                  $message->from_user_id    = $userID;
                  $message->to_user_id      = $creator->id;
                  $message->message         = '';
                  $message->updated_at      = now();
                  $message->tip             = 'yes';
                  $message->tip_amount      = $amount;
                  $message->save();

              }// end isMessage

            }// end verifyTxnId

          } elseif ($request->{'X-type'} == 'wallet') {

            // Verify Deposit
            $verifyTxnId = Deposits::where('txn_id', $request->transactionId)->first();

            // Amount
            $amount = $request->{'X-amountFixed'};

            $userID = $request->{'X-user'};

            if (! isset($verifyTxnId)) {

              // Insert Deposit ($userID, $txnId, $amount, $paymentGateway)
              $this->deposit($userID, $request->transactionId, $amount, 'CCBill', $request->{'X-taxes'});

              // Add Funds to User
              User::find($userID)->increment('wallet', $amount);
            }
            // end wallet

          } elseif ($request->{'X-type'} == 'ppv') {
            // Amount
            $amount = $request->subscriptionInitialPrice;

            $userID = $request->{'X-user'};

            // Verify transaction
            $verifyTxnId = Transactions::where('txn_id', $request->transactionId)->first();

            if (! isset($verifyTxnId)) {

              // Check if it is a Message or Post
              $media = isset($request->{'X-isMessage'}) ? Messages::find($request->{'X-media'}) : Updates::find($request->{'X-media'});

              // Admin and user earnings calculation
              $earnings = $this->earningsAdminUser($media->user()->custom_fee, $request->{'X-priceOriginal'}, $payment->fee, $payment->fee_cents);

              // Insert Transaction
              $this->transaction(
                  $request->transactionId,
                  $userID,
                  0,
                  $media->user()->id,
                  $request->{'X-priceOriginal'},
                  $earnings['user'],
                  $earnings['admin'],
                  'CCBill',
                  'ppv',
                  $earnings['percentageApplied'],
                  $request->{'X-taxes'}
                );

              // Add Earnings to User
              $media->user()->increment('balance', $earnings['user']);

              //= Check if the tip is sent by message
              if (isset($request->{'X-isMessage'})) {
                // $user_id, $updates_id, $messages_id
                $this->payPerViews($userID, 0, $media->id);

                // Send Notification - destination, author, type, target
                Notifications::send($media->user()->id, $userID, '6', $media->id);

              } else {
                // $user_id, $updates_id, $messages_id
                $this->payPerViews($userID, $media->id, 0);

                // Send Notification - destination, author, type, target
                Notifications::send($media->user()->id, $userID, '7', $media->id);
              }// end isMessage

            }// end verifyTxnId
          } // end PPV

          break;

       case 'RenewalSuccess':

          // Find user
          $creator = User::whereId($request->{'X-creator'})
              ->whereVerifiedId('yes')
                ->firstOrFail();

          // Check if Plan exists
          $plan = $creator->plans()
            ->whereInterval($request->{'X-interval'})
               ->firstOrFail();

          // Subscription ID
          $subscr_id = $request->subscriptionId;

          // Amount
          $amount = $request->subscriptionInitialPrice;

          $userID = $request->{'X-user'};

          // Subscription
          $subscription = Subscriptions::where('subscription_id', $subscr_id)->firstOrFail();
          $subscription->ends_at = $creator->planInterval($plan->interval);
          $subscription->save();

          // Admin and user earnings calculation
          $earnings = $this->earningsAdminUser($creator->custom_fee, $request->{'X-priceOriginal'}, $payment->fee, $payment->fee_cents);

          // Insert Transaction
          $this->transaction(
              $request->transactionId,
              $userID,
              $subscr_id,
              $creator->id,
              $amount,
              $earnings['user'],
              $earnings['admin'],
              'CCBill',
              'subscription',
              $earnings['percentageApplied'],
              $request->{'X-taxes'}
            );

         // Add Earnings to User
         $creator->increment('balance', $earnings['user']);

         // Send Notification to User --- destination, author, type, target
         Notifications::send($creator->id, $userID, 12, $userID);

          break;

      case 'Cancellation':

          // Subscription ID
          $subscr_id = $request->subscriptionId;

          // Update subscription to Canceled
          $subscription = Subscriptions::where('subscription_id', $subscr_id)->firstOrFail();
          $subscription->cancelled = 'yes';
          $subscription->save();
          break;

      }

      return response('WebHook Handled');

    } // End method webhooks

    // Approved transaction
    public function approved(Request $request)
    {
      if (isset($request->type) && $request->type == 'subscription') {

        $creator = User::findOrFail($request->creator);

         session()->put('subscription_success', trans('general.subscription_success'));
      	 return redirect($creator->username);

      } elseif (isset($request->type) && $request->type == 'tip') {

        $creator = User::findOrFail($request->creator);

        if (isset($request->isMessage)) {
          return redirect('messages/'.$creator->id);
        } else {
          session()->put('subscription_success', trans('general.tip_sent_success'));
          return redirect($creator->username);
        }

      } elseif (isset($request->type) && $request->type == 'wallet') {
        return redirect('my/wallet');
      }
      elseif (isset($request->type) && $request->type == 'ppv') {

        if (isset($request->isMessage)) {
          // Check if it is a Message or Post
          $media = Messages::find($request->media);
          return redirect('messages/'.$media->from_user_id);
        } else {
          return redirect('my/purchases');
        }

      } else {
          return redirect('/');
      }

    }// End method
}
