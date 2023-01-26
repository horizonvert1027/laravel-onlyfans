<?php

namespace App\Http\Controllers;

use Mail;
use App\Helper;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AdminSettings;
use App\Models\LiveComments;
use Fahim\PaypalIPN\PaypalIPNListener;
use Illuminate\Support\Facades\Validator;
use App\Models\PaymentGateways;
use App\Models\Transactions;
use Laravel\Cashier\Exceptions\IncompletePayment;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use App\Models\Notifications;
use App\Models\Conversations;
use App\Models\Messages;
use Yabacon\Paystack;


class TipController extends Controller
{
  use Traits\Functions;

  public function __construct(Request $request, AdminSettings $settings) {
    $this->request = $request;
    $this->settings = $settings::first();
  }

  /**
	 *  Send Tip Request
	 *
	 * @return Response
	 */
  public function send() {

    // Find the User
    $user = User::whereVerifiedId('yes')->whereId($this->request->id)->where('id', '<>', Auth::user()->id)->firstOrFail();

    // Currency Position
    if ($this->settings->currency_position == 'right') {
      $currencyPosition =  2;
    } else {
      $currencyPosition =  null;
    }

    $messages = array (
      'amount.min' => trans('general.amount_minimum'.$currencyPosition, ['symbol' => $this->settings->currency_symbol, 'code' => $this->settings->currency_code]),
      'amount.max' => trans('general.amount_maximum'.$currencyPosition, ['symbol' => $this->settings->currency_symbol, 'code' => $this->settings->currency_code]),
  );

  //<---- Validation
  $validator = Validator::make($this->request->all(), [
      'amount' => 'required|integer|min:'.$this->settings->min_tip_amount.'|max:'.$this->settings->max_tip_amount,
      'payment_gateway_tip' => 'required',
      ], $messages);

    if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->getMessageBag()->toArray(),
            ]);
        }

    switch ($this->request->payment_gateway_tip) {
      case 'wallet':
        return $this->sendTipWallet();
        break;

      case 1:
        return $this->sendTipPayPal();
        break;

      case 2:
        return $this->sendTipStripe();
        break;

      case 4:
        return $this->ccbillForm(
            $this->request->amount,
            auth()->user()->id,
            'tip',
            $user->id,
            $this->request->isMessage
          );
        break;

      case 5:
        return $this->sendTipPaystack();
        break;
    }


    return response()->json([
      'success' => true,
      'insertBody' => '<i></i>'
    ]);

  } // End method Send

  /**
	 *  Send Tip Wallet
	 *
	 * @return Response
	 */
   protected function sendTipWallet()
   {
     $user = User::find($this->request->id);
     $amount = $this->request->amount;

     if (auth()->user()->wallet < Helper::amountGross($amount)) {
       return response()->json([
         "success" => false,
         "errors" => ['error' => __('general.not_enough_funds')]
       ]);
     }

     // Admin and user earnings calculation
     $earnings = $this->earningsAdminUser($user->custom_fee, $amount, null, null);

     // Insert Transaction
     $this->transaction(
        'w_'.str_random(25),
        auth()->user()->id,
        0,
        $user->id,
        $amount,
        $earnings['user'],
        $earnings['admin'],
        'Wallet',
        'tip',
        $earnings['percentageApplied'],
        auth()->user()->taxesPayable()
      );

     // Subtract user funds
     auth()->user()->decrement('wallet', Helper::amountGross($amount));

     // Add Earnings to User
     $user->increment('balance', $earnings['user']);

     // Send Email Creator
     if ($user->email_new_tip == 'yes' && ! $this->request->isLive) {
         $this->notifyEmailNewTip($user, auth()->user()->username, $amount);
     }

     // Send Notification
     if (! $this->request->isLive) {
       Notifications::send($user->id, auth()->user()->id, '5', auth()->user()->id);
     }

     //====== Check if is Live Streaming
     if ($this->request->isLive) {
       $sql           = new LiveComments();
       $sql->user_id  = auth()->id();
       $sql->live_streamings_id = $this->request->liveID;
       $sql->joined  = 0;
       $sql->tip     = '1';
       $sql->tip_amount = $amount;
       $sql->save();
     }

     //============== Check if the tip is sent by message
     if ($this->request->isMessage) {

       // Verify Conversation Exists
       $conversation = Conversations::where('user_1', Auth::user()->id)
         ->where('user_2', $user->id)
         ->orWhere('user_1', $user->id)
         ->where('user_2', Auth::user()->id)->first();

         if (! isset($conversation)) {
           $newConversation = new Conversations;
           $newConversation->user_1 = Auth::user()->id;
           $newConversation->user_2 = $user->id;
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
         $message->from_user_id    = Auth::user()->id;
         $message->to_user_id      = $user->id;
         $message->message         = '';
         $message->updated_at      = now();
         $message->tip             = 'yes';
         $message->tip_amount      = $amount;
         $message->save();
     } // End ttp Message

     return response()->json([
       "success" => true,
       "wallet" => Helper::userWallet()
     ]);

   } // End sendTipWallet


  /**
	 *  Send Tip PayPal
	 *
	 * @return Response
	 */
  protected function sendTipPayPal()
  {
    // Get Payment Gateway
    $payment = PaymentGateways::whereId(1)->whereName('PayPal')->firstOrFail();

    // Find user
    $user = User::find($this->request->id);

      if ($this->request->isMessage) {
        $urlSuccess = route('paypal.success');
        $urlCancel  = url('paypal/msg/tip/redirect', $this->request->id);
        $isMessage  = true;
      } else {
        $urlSuccess = route('paypal.success');
        $urlCancel   = url('paypal/tip/cancel', $user->username);
        $isMessage = false;
      }

      try {
        // Init PayPal
        $provider = new PayPalClient();
        $token = $provider->getAccessToken();
        $provider->setAccessToken($token);

        $order = $provider->createOrder([
              "intent"=> "CAPTURE",
              'application_context' =>
                  [
                      'return_url' => $urlSuccess,
                      'cancel_url' => $urlCancel,
                      'shipping_preference' => 'NO_SHIPPING'
                  ],
              "purchase_units"=> [
                   [
                      "amount"=> [
                          "currency_code"=> $this->settings->currency_code,
                          "value"=> Helper::amountGross($this->request->amount),
                          'breakdown' => [
                            'item_total' => [
                              "currency_code"=> $this->settings->currency_code,
                              "value"=> Helper::amountGross($this->request->amount)
                            ],
                          ],
                      ],
                       'description' => __('general.tip_for').' @'.$user->username,

                       'items' => [
                         [
                           'name' => __('general.tip_for').' @'.$user->username,
                            'category' => 'DIGITAL_GOODS',
                              'quantity' => '1',
                              'unit_amount' => [
                                "currency_code"=> $this->settings->currency_code,
                                "value" => Helper::amountGross($this->request->amount)
                              ],
                         ],
                      ],

                      'custom_id' => http_build_query([
                          'id' => $user->id,
                          'amount' => $this->request->amount,
                          'sender' => auth()->id(),
                          'm' => $isMessage,
                          'taxes' => auth()->user()->taxesPayable(),
                          'type' => 'tip'
                      ]),
                  ],
              ],
          ]);

          return response()->json([
                      'success' => true,
                      'url' => $order['links'][1]['href']
                  ]);

        } catch (\Exception $e) {

          \Log::debug($e);

          return response()->json([
            'errors' => ['error' => $e->getMessage()]
          ]);
        }
      } // sendTipPayPal

  /**
	 *  Send Tip Stripe
	 *
	 * @return Response
	 */
  protected function sendTipStripe()
  {
        // Get Payment Gateway
        $payment = PaymentGateways::whereName('Stripe')->firstOrFail();
        $user    = User::find($this->request->id);

      	$cents  = $this->settings->currency_code == 'JPY' ? Helper::amountGross($this->request->amount) : (Helper::amountGross($this->request->amount)*100);
      	$amount = (int)$cents;
      	$currency_code = $this->settings->currency_code;
      	$description = __('general.tip_for').' @'.$user->username;

        \Stripe\Stripe::setApiKey($payment->key_secret);

        $intent = null;
        try {
          if (isset($this->request->payment_method_id)) {
            # Create the PaymentIntent
            $intent = \Stripe\PaymentIntent::create([
              'payment_method' => $this->request->payment_method_id,
              'amount' => $amount,
              'currency' => $currency_code,
              "description" => $description,
              'confirmation_method' => 'manual',
              'confirm' => true
            ]);
          }
          if (isset($this->request->payment_intent_id)) {
            $intent = \Stripe\PaymentIntent::retrieve(
              $this->request->payment_intent_id
            );
            $intent->confirm();
          }
          return $this->generatePaymentResponse($intent);
        } catch (\Stripe\Exception\ApiErrorException $e) {
          # Display error on client
          return response()->json([
            'error' => $e->getMessage()
          ]);
        }
  } // End Method sendTipStripe

  protected function generatePaymentResponse($intent)
  {
    # Note that if your API version is before 2019-02-11, 'requires_action'
    # appears as 'requires_source_action'.
    if (isset($intent->status) && $intent->status == 'requires_action' &&
        $intent->next_action->type == 'use_stripe_sdk') {
      # Tell the client to handle the action
      return response()->json([
        'requires_action' => true,
        'payment_intent_client_secret' => $intent->client_secret,
      ]);
    } else if (isset($intent->status) && $intent->status == 'succeeded') {
      # The payment didn’t need any additional actions and completed!
      # Handle post-payment fulfillment

      $user = User::find($this->request->id);

      // Insert DB
      //========== Processor Fees
      $amount = $this->request->amount;
      $payment = PaymentGateways::whereName('Stripe')->first();

      // Admin and user earnings calculation
      $earnings = $this->earningsAdminUser($user->custom_fee, $amount, $payment->fee, $payment->fee_cents);

      // Insert Transaction
      $this->transaction(
          $intent->id,
          auth()->user()->id,
          0,
          $user->id,
          $amount,
          $earnings['user'],
          $earnings['admin'],
          'Stripe',
          'tip',
          $earnings['percentageApplied'],
          auth()->user()->taxesPayable()
        );

      // Add Earnings to User
      $user->increment('balance', $earnings['user']);

      // Send Email Creator
      if ($user->email_new_tip == 'yes') {
        $this->notifyEmailNewTip($user, auth()->user()->username, $amount);
      }

      // Send Notification
      Notifications::send($user->id, auth()->user()->id, '5', auth()->user()->id);

      //============== Check if the tip is sent by message
      if ($this->request->isMessage) {

        // Verify Conversation Exists
				$conversation = Conversations::where('user_1', Auth::user()->id)
  				->where('user_2', $user->id)
  				->orWhere('user_1', $user->id)
  				->where('user_2', Auth::user()->id)->first();

          if (! isset($conversation)) {
            $newConversation = new Conversations;
            $newConversation->user_1 = Auth::user()->id;
            $newConversation->user_2 = $user->id;
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
          $message->from_user_id    = Auth::user()->id;
          $message->to_user_id      = $user->id;
          $message->message         = '';
          $message->updated_at      = now();
          $message->tip             = 'yes';
          $message->tip_amount      = $amount;
          $message->save();
      }

      return response()->json([
        "success" => true,
        "wallet" => Helper::userWallet()
      ]);
    } else {
      # Invalid status
      http_response_code(500);
      return response()->json(['error' => 'Invalid PaymentIntent status']);
    }
  }// End generatePaymentResponse

  public function sendTipPaystack()
  {

    $payment = PaymentGateways::whereName('Paystack')->whereEnabled(1)->firstOrFail();
    $user  = User::find($this->request->id);
    $paystack = new Paystack($payment->key_secret);
    $amount = $this->request->amount;

    if (isset($this->request->trxref)) {
      try {
        $tranx = $paystack->transaction->verify([
          'reference' => $this->request->trxref,
        ]);
      } catch (\Exception $e) {
        return response()->json([
          "success" => false,
          'errors' => ['error' => $e->getMessage()]
        ]);
      }

      if ('success' === $tranx->data->status) {
        // Verify transaction
        $verifyTxnId = Transactions::where('txn_id', $tranx->data->reference)->first();

      if (! isset($verifyTxnId)) {

        // Admin and user earnings calculation
        $earnings = $this->earningsAdminUser($user->custom_fee, $amount, $payment->fee, $payment->fee_cents);

        // Insert Transaction
        $this->transaction(
            $tranx->data->reference,
            auth()->user()->id,
            0,
            $user->id,
            $amount,
            $earnings['user'],
            $earnings['admin'],
            'Paystack',
            'tip',
            $earnings['percentageApplied'],
            auth()->user()->taxesPayable()
          );

        // Add Earnings to User
        $user->increment('balance', $earnings['user']);

        // Send Email Creator
        if ($user->email_new_tip == 'yes') {
          $this->notifyEmailNewTip($user, auth()->user()->username, $amount);
        }

        // Send Notification
        Notifications::send($user->id, auth()->user()->id, '5', auth()->user()->id);

        //============== Check if the tip is sent by message
        if (isset($this->request->isMessage)) {

          // Verify Conversation Exists
          $conversation = Conversations::where('user_1', auth()->user()->id)
            ->where('user_2', $user->id)
            ->orWhere('user_1', $user->id)
            ->where('user_2', auth()->user()->id)->first();

            if (! isset($conversation)) {
              $newConversation = new Conversations;
              $newConversation->user_1 = auth()->user()->id;
              $newConversation->user_2 = $user->id;
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
            $message->from_user_id    = auth()->user()->id;
            $message->to_user_id      = $user->id;
            $message->message         = '';
            $message->updated_at      = now();
            $message->tip             = 'yes';
            $message->tip_amount      = $amount;
            $message->save();

         } // end isMessage
        } // end verifyTxnId

        return response()->json([
          "success" => true,
          'instantPayment' => true,
          "wallet" => Helper::userWallet()
        ]);
      } else {
        return response()->json([
            'success' => false,
            'errors' => ['error' => $tranx->data->gateway_response],
        ]);
      }

    } else {
      return response()->json([
          'success' => true,
          'insertBody' => "<script type='text/javascript'>var handler = PaystackPop.setup({
            key: '".$payment->key."',
            email: '".auth()->user()->email."',
            amount: ".(Helper::amountGross($amount)*100).",
            currency: '".$this->settings->currency_code."',
            ref: '".Helper::genTranxRef()."',
            callback: function(response) {
              var input = $('<input type=hidden name=trxref />').val(response.reference);
              $('#formSendTip').append(input);
              $('#tipBtn').trigger('click');
            },
            onClose: function() {
                alert('Window closed');
            }
          });
          handler.openIframe();</script>"
      ]);
    }

  }// end method

}
