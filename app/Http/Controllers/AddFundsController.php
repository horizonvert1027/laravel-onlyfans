<?php

namespace App\Http\Controllers;

use Mail;
use App\Helper;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\AdminSettings;
use App\Models\Notifications;
use Fahim\PaypalIPN\PaypalIPNListener;
use Illuminate\Support\Facades\Validator;
use App\Models\PaymentGateways;
use App\Models\Transactions;
use Laravel\Cashier\Exceptions\IncompletePayment;
use KingFlamez\Rave\Facades\Rave as Flutterwave;
use Illuminate\Support\Facades\Notification;
use App\Notifications\AdminDepositPending;
use App\Models\Conversations;
use App\Models\Messages;
use App\Models\Deposits;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Yabacon\Paystack;
use Mollie\Api\MollieApiClient;
use Illuminate\Support\Facades\Http;
use Razorpay\Api\Api;

class AddFundsController extends Controller
{
  use Traits\Functions;

  public function __construct(Request $request, AdminSettings $settings) {
    $this->request = $request;
    $this->settings = $settings::first();
  }

  /**
	 *  Wallet View
	 *
	 * @return Response
	 */
  public function wallet()
  {
    if ($this->settings->disable_wallet == 'on') {
      abort(404);
    }
    $data = Deposits::whereUserId(auth()->user()->id)->orderBy('id', 'desc')->paginate(20);

    $equivalent_money = Helper::equivalentMoney($this->settings->wallet_format);

    return view('users.wallet', ['data' => $data, 'equivalent_money' => $equivalent_money]);
  }
  
  public function test() 
  {
       $pos_id= env('VPOS_POS_ID');
            $idempotency_key= env('VPOS_IDEMPOTENCY_KEY');
            $bearer_token= env('VPOS_TOKEN');
            $paymentStatus= 0;

             $curl = curl_init();
            curl_setopt_array($curl, array(
              CURLOPT_URL => 'https://vpos.ao/api/v1/transactions',
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_HEADER => true, 
              CURLOPT_ENCODING => '',
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => 'POST',
              CURLOPT_HEADERFUNCTION=> array($this,'handleHeaderLine'),
              CURLOPT_POSTFIELDS =>'{"type": "payment", "pos_id": '.$pos_id.', "mobile": "900000000", "amount": "10"}',
              CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer '.$bearer_token,
                'Content-Type: application/json'
              )
            ));
            

            $response = curl_exec($curl);
            curl_close($curl);
            $responseDetail= $this->http_parse_headers($response);
            
            if(!empty($responseDetail['location'])){
                $explodeLocation= explode("/",$responseDetail['location']);
                $transactionID= trim($explodeLocation[4]);

                $curl2 = curl_init();
                curl_setopt_array($curl2, array(
                  CURLOPT_URL => 'https://vpos.ao'.$responseDetail['location'],
                  CURLOPT_RETURNTRANSFER => true,
                  CURLOPT_ENCODING => '',
                  CURLOPT_MAXREDIRS => 10,
                  CURLOPT_TIMEOUT => 0,
                  CURLOPT_FOLLOWLOCATION => true,
                  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                  CURLOPT_CUSTOMREQUEST => 'GET',
                  CURLOPT_HTTPHEADER => array(
                    'Authorization: Bearer '.$bearer_token
                  ),
                ));

                $response2 = curl_exec($curl2);
                curl_close($curl2);
                if(!empty($response2)){
                    $jsonDecodeResponse2= json_decode($response2);
                    if(!empty($jsonDecodeResponse2->id) && !empty($jsonDecodeResponse2->status)){
                       //$transactionID= $jsonDecodeResponse2->id; 
                       if($jsonDecodeResponse2->status=="accepted"){
                          $paymentStatus= 1;
                       } else {
                          $paymentStatus= 0;
                       }                       
                    }                 
                }
            }    
            //  $verifiedTxnId = Deposits::where('txn_id', $transactionID)->first();
            //      if (! $verifiedTxnId) {
                    // Insert Deposit
            //        $this->deposit($user, $transactionID, $amount, 'Vpos', $taxes);

                    // Add Funds to User
            //        User::find($user)->increment('wallet', $amount);
           //       }
           return dd($jsonDecodeResponse2);
  }
  
  public function handleHeaderLine( $curl, $header_line ) {
      return strlen($header_line);
    }
    
    
  public function vpos_webhook()
    {
        $request= $this->request->all();
        if(!empty($request)){
            $transactionID= $request['id'];
            $verifiedTxnId = Deposits::where('txn_id', $transactionID)->first();

                    // Add Funds to User
                    User::find($user)->increment('wallet', $amount);
                
               $verifiedTxnId->status = 'active';
                    $verifiedTxnId->save();   
            if($request["status"]=="accepted"){                
                if(!empty($verifiedTxnId)){
                    $userid= $verifiedTxnId->user_id;
                    $amount= $verifiedTxnId->amount;
                    // Add Funds to User
                    User::find($userid)->increment('wallet', $amount);

                    $verifiedTxnId->status = 'active';
                    $verifiedTxnId->save();
                }
            } else {
                if(!empty($verifiedTxnId)){
                    $verifiedTxnId->status = 'pending';
                    $verifiedTxnId->save();
                }
            }
            
        }
    }    
    
  public function http_parse_headers($raw_headers)
    {
        $headers = array();
        $key = ''; // [+]

        foreach(explode("\n", $raw_headers) as $i => $h)
        {
            $h = explode(':', $h, 2);

            if (isset($h[1]))
            {
                if (!isset($headers[$h[0]]))
                    $headers[$h[0]] = trim($h[1]);
                elseif (is_array($headers[$h[0]]))
                {
                    $headers[$h[0]] = array_merge($headers[$h[0]], array(trim($h[1]))); // [+]
                }
                else
                {
                    $headers[$h[0]] = array_merge(array($headers[$h[0]]), array(trim($h[1]))); // [+]
                }

                $key = $h[0]; // [+]
            }
            else // [+]
            { // [+]
                if (substr($h[0], 0, 1) == "\t") // [+]
                    $headers[$key] .= "\r\n\t".trim($h[0]); // [+]
                elseif (!$key) // [+]
                    $headers[0] = trim($h[0]);trim($h[0]); // [+]
            } // [+]
        }

        return $headers;
    }    
    

  /**
	 *  Add Funds Request
	 *
	 * @return Response
	 */
  public function send() {

    // Validate Payment Gateway
    Validator::extend('check_payment_gateway', function($attribute, $value, $parameters) {
      return PaymentGateways::whereName($value)->first();
    });

    // Currency Position
    if ($this->settings->currency_position == 'right') {
      $currencyPosition =  2;
    } else {
      $currencyPosition =  null;
    }

    $messages = array (
      'amount.min' => trans('general.amount_minimum'.$currencyPosition, ['symbol' => $this->settings->currency_symbol, 'code' => $this->settings->currency_code]),
      'amount.max' => trans('general.amount_maximum'.$currencyPosition, ['symbol' => $this->settings->currency_symbol, 'code' => $this->settings->currency_code]),
      'telephone' => 'Digite o numero de telemovel',
      'payment_gateway.check_payment_gateway' => trans('general.payments_error'),
      'image.required_if' => trans('general.please_select_image'),
  );

  //<---- Validation
  $validator = Validator::make($this->request->all(), [
      'amount' => 'required|integer|min:'.$this->settings->min_deposits_amount.'|max:'.$this->settings->max_deposits_amount,
      'telephone' => 'required|numeric',
      'payment_gateway' => 'required|check_payment_gateway',
      'image' => 'required_if:payment_gateway,==,Bank|mimes:jpg,gif,png,jpe,jpeg|max:'.$this->settings->file_size_allowed_verify_account.'',
      ], $messages);

    if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->getMessageBag()->toArray(),
            ]);
        }

    switch ($this->request->payment_gateway) {
      case 'PayPal':
        return $this->sendPayPal();
        break;

      case 'Stripe':
        return $this->sendStripe();
        break;

      case 'Bank':
        return $this->sendBankTransfer();
        break;
        
      case 'Vpos':
          return $this->sendVpos(
              $this->request->telephone,
              $this->request->amount
            );
          break;

      case 'CCBill':
        return $this->ccbillForm(
            $this->request->amount,
            auth()->user()->id,
            'wallet'
          );
        break;

      case 'Paystack':
      return $this->sendPaystack();
        break;

      case 'Coinpayments':
      return $this->sendCoinpayments();
        break;

      case 'Mercadopago':
      return $this->sendMercadopago();
        break;

      case 'Flutterwave':
      return $this->sendFlutterwave();
        break;

      case 'Mollie':
      return $this->sendMollie();
        break;

      case 'Razorpay':
      return $this->sendRazorpay();
        break;
    }

    return response()->json([
      'success' => true,
      'insertBody' => '<i></i>'
    ]);

  } // End method Send

  /**
	 *  Add funds PayPal
	 *
	 * @return Response
	 */
  protected function sendPayPal()
  {
    // Get Payment Gateway
    $payment = PaymentGateways::whereId(1)->whereName('PayPal')->firstOrFail();

      $urlSuccess = route('paypal.success');
      $urlCancel   = url('my/wallet');

      $feePayPal   = $payment->fee;
      $centsPayPal =  $payment->fee_cents;

      $taxes = $this->settings->tax_on_wallet ? ($this->request->amount * auth()->user()->isTaxable()->sum('percentage') / 100) : 0;
      $taxesPayable = $this->settings->tax_on_wallet ? auth()->user()->taxesPayable() : null;

      $amountFixed = number_format($this->request->amount + ($this->request->amount * $feePayPal / 100) + $centsPayPal + $taxes, 2, '.', '');

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
                        "value"=> $amountFixed,
                        'breakdown' => [
                          'item_total' => [
                            "currency_code"=> $this->settings->currency_code,
                            "value"=> $amountFixed
                          ],
                        ],
                    ],
                     'description' => __('general.add_funds').' @'.auth()->user()->username,

                     'items' => [
                       [
                         'name' => __('general.add_funds').' @'.auth()->user()->username,
                          'category' => 'DIGITAL_GOODS',
                            'quantity' => '1',
                            'unit_amount' => [
                              "currency_code"=> $this->settings->currency_code,
                              "value" => $amountFixed
                            ],
                       ],
                    ],

                    'custom_id' => http_build_query([
                        'id' => auth()->id(),
                        'amount' => $this->request->amount,
                        'taxes' => $taxesPayable,
                        'type' => 'deposit'
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
    } // sendPayPal
    
    
    /**
     * add funds Vpos
     * 
     * @return Response
     */
  protected function sendVpos($phone,$amount)
  {
            $pos_id= env('VPOS_POS_ID');
            $idempotency_key= env('VPOS_IDEMPOTENCY_KEY');
            $bearer_token= env('VPOS_TOKEN');

             $curl = curl_init();
            curl_setopt_array($curl, array(
              CURLOPT_URL => 'https://vpos.ao/api/v1/transactions',
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_HEADER => true, 
              CURLOPT_ENCODING => '',
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => 'POST',
              CURLOPT_HEADERFUNCTION=> array($this,'handleHeaderLine'),
              CURLOPT_POSTFIELDS =>'{"type": "payment", "pos_id": '.$pos_id.', "mobile": "'.$phone.'", "amount": "'.$amount.'", "callback_url": "https://oprivado.com/wallet/vpos_webhook"}',
              CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer '.$bearer_token,
                'Content-Type: application/json'
              ),
            ));

            $response = curl_exec($curl);
            curl_close($curl);
           $responseDetail= $this->http_parse_headers($response);
            
            if(!empty($responseDetail['location'])){
                $explodeLocation= explode("/",$responseDetail['location']);
                $transactionID= trim($explodeLocation[4]);

                $curl2 = curl_init();
                curl_setopt_array($curl2, array(
                  CURLOPT_URL => 'https://vpos.ao/'.$responseDetail['location'],
                  CURLOPT_RETURNTRANSFER => true,
                  CURLOPT_ENCODING => '',
                  CURLOPT_MAXREDIRS => 10,
                  CURLOPT_TIMEOUT => 0,
                  CURLOPT_FOLLOWLOCATION => true,
                  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                  CURLOPT_CUSTOMREQUEST => 'GET',
                  CURLOPT_HTTPHEADER => array(
                    'Authorization: Bearer '.$bearer_token
                  ),
                ));

                $response2 = curl_exec($curl2);
                curl_close($curl2);
                if(!empty($response2)){
                    $jsonDecodeResponse2= json_decode($response2);
                    if(!empty($jsonDecodeResponse2->id) && !empty($jsonDecodeResponse2->status)){
                       //$transactionID= $jsonDecodeResponse2->id; 
                       if($jsonDecodeResponse2->status=="accepted"){
                          $paymentStatus= 1;
                       } else {
                          $paymentStatus= 0;
                       }                       
                    }                 
                }
            } 
            
            $userId = auth()->check() ? auth()->user()->id : null;
            
                 $verifiedTxnId = Deposits::where('txn_id', $transactionID)->first();
                  if (! $verifiedTxnId) {
                    // Insert Deposit
                    $this->deposit($userId, $transactionID, $amount, 'Vpos', $this->settings->tax_on_wallet ? auth()->user()->taxesPayable() : null);

                    // Add Funds to User
                    //User::find($userId)->increment('wallet', $amount);
                  }
 
      return response()->json([
          'success' => true,
          "status" => 'pending',
      'status_info' => __('general.pagamento'),
          'url' => '/my/wallet'
      ]);
  }

  /**
	 *  Add funds Stripe
	 *
	 * @return Response
	 */
  protected function sendStripe()
  {
        // Get Payment Gateway
        $payment = PaymentGateways::whereName('Stripe')->firstOrFail();

        $feeStripe   = $payment->fee;
      	$centsStripe =  $payment->fee_cents;

        $taxes = $this->settings->tax_on_wallet ? ($this->request->amount * auth()->user()->isTaxable()->sum('percentage') / 100) : 0;

        if ($this->settings->currency_code == 'JPY') {
          $amountFixed = round($this->request->amount + ($this->request->amount * $feeStripe / 100) + $centsStripe + $taxes);
        } else {
          $amountFixed = number_format($this->request->amount + ($this->request->amount * $feeStripe / 100) + $centsStripe + $taxes, 2, '.', '');
        }

      	$amountGross = ($this->request->amount);
      	$amount   = $this->settings->currency_code == 'JPY' ? $amountFixed : ($amountFixed*100);

      	$currency_code = $this->settings->currency_code;
      	$description = __('general.add_funds').' @'.auth()->user()->username;

        $stripe = new \Stripe\StripeClient($payment->key_secret);

        $checkout = $stripe->checkout->sessions->create([
          'line_items' => [[
            'price_data' => [
              'currency' => $currency_code,
              'product_data' => [
                'name' => $description,
              ],
              'unit_amount' => $amount,
            ],
            'quantity' => 1,
          ]],
          'mode' => 'payment',

              'metadata' => [
                'user' => auth()->id(),
                'amount' => $this->request->amount,
                'taxes' => $this->settings->tax_on_wallet ? auth()->user()->taxesPayable() : null,
                'type' => 'deposit'
              ],

          'payment_method_types' => ['card'],
          'customer_email' => auth()->user()->email,

          'success_url' => url('my/wallet'),
          'cancel_url' => url('my/wallet'),
        ]);

        return response()->json([
          'success' => true,
          'url' => $checkout->url,
        ]);

  } // End Method sendStripe

  public function sendBankTransfer()
  {
    // PATHS
    $path = config('path.admin');

    if ($this->request->hasFile('image')) {

    $extension = $this->request->file('image')->getClientOriginalExtension();
    $fileImage = 'bt_'.strtolower(auth()->user()->id.time().str_random(40).'.'.$extension);

    $this->request->file('image')->storePubliclyAs($path, $fileImage);

   }//<====== End HasFile

   // Insert Deposit
   $deposit = $this->deposit(
      auth()->user()->id,
      'bt_'.str_random(25),
      $this->request->amount,
      'Bank',
      $this->settings->tax_on_wallet ? auth()->user()->taxesPayable() : null,
      $fileImage
    );

    // Notify Admin via Email
    try {
      Notification::route('mail' , $this->settings->email_admin)
          ->notify(new AdminDepositPending($deposit));
    } catch (\Exception $e) {
      \Log::info($e->getMessage());
    }

    return response()->json([
      "success" => true,
      "status" => 'pending',
      'status_info' => __('general.pending_deposit')
    ]);

  } // End method sendBankTransfer

  public function sendPaystack()
  {
    $payment = PaymentGateways::whereName('Paystack')->whereEnabled(1)->firstOrFail();
    $paystack = new Paystack($payment->key_secret);

    $fee   = $payment->fee;
    $cents = $payment->fee_cents;

    $taxes = $this->settings->tax_on_wallet ? ($this->request->amount * auth()->user()->isTaxable()->sum('percentage') / 100) : 0;

    $amount = number_format($this->request->amount + ($this->request->amount * $fee / 100) + $cents + $taxes, 2, '.', '');

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
        // Verify Deposit
        $verifyTxnId = Deposits::where('txn_id', $tranx->data->reference)->first();

        if (! isset($verifyTxnId)) {

          // Insert Deposit
          $this->deposit(
            auth()->user()->id,
            $tranx->data->reference,
            $this->request->amount,
            'Paystack',
            $this->settings->tax_on_wallet ? auth()->user()->taxesPayable() : null
          );

          // Add Funds to User
          User::find(auth()->user()->id)->increment('wallet', $this->request->amount);

        return response()->json([
          "success" => true,
          'instantPayment' => true
        ]);
      }// verifyTxnId
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
          amount: ".($amount*100).",
          currency: '".$this->settings->currency_code."',
          ref: '".Helper::genTranxRef()."',
          callback: function(response) {
            var input = $('<input type=hidden name=trxref />').val(response.reference);
            $('#formAddFunds').append(input);
            $('#addFundsBtn').trigger('click');
          },
          onClose: function() {
              alert('Window closed');
          }
        });
        handler.openIframe();</script>"
    ]);
   }
  }// end method

  /**
	 *  Add funds CoinPaments
	 *
	 * @return Response
	 */
  protected function sendCoinpayments()
  {
    // Get Payment Gateway
    $payment = PaymentGateways::whereName('Coinpayments')->firstOrFail();

      $urlSuccess = route('paymentProcess');
      $urlCancel   = url('my/wallet');

      $urlIPN = route('coinpaymentsIPN', [
        'user' => auth()->user()->id,
        'amountOriginal' => $this->request->amount,
        'taxes' => $this->settings->tax_on_wallet ? auth()->user()->taxesPayable() : null
      ]);

      $fee   = $payment->fee;
      $cents =  $payment->fee_cents;

      $taxes = $this->settings->tax_on_wallet ? ($this->request->amount * auth()->user()->isTaxable()->sum('percentage') / 100) : 0;

      $amountFixed = number_format($this->request->amount + ($this->request->amount * $fee / 100) + $cents + $taxes, 2, '.', '');

      return response()->json([
                  'success' => true,
                  'insertBody' => '<form name="_click" action="https://www.coinpayments.net/index.php" method="post"  style="display:none">
                  <input type="hidden" name="cmd" value="_pay">
                  <input type="hidden" name="reset" value="1"/>
                  <input type="hidden" name="merchant" value="'.$payment->key.'">
                  <input type="hidden" name="success_url" value="'.$urlSuccess.'">
                  <input type="hidden" name="cancel_url"   value="'.$urlCancel.'">
                  <input type="hidden" name="ipn_url" value="'.$urlIPN.'">
                  <input type="hidden" name="currency" value="'.$this->settings->currency_code.'">
                  <input type="hidden" name="amountf" value="'.$amountFixed.'">
                  <input type="hidden" name="want_shipping" value="0">
                  <input type="hidden" name="item_name" value="'.__('general.add_funds').' @'.auth()->user()->username.'">
                  <input type="hidden" name="email" value="'.auth()->user()->email.'">
                  <input type="hidden" name="first_name" value="'.auth()->user()->firstname.'">
                  <input type="hidden" name="last_name" value="'.auth()->user()->lastname.'">
                  <input type="submit">
                  </form> <script type="text/javascript">document._click.submit();</script>',
              ]);
      } // sendCoinpayments

      // CoinPaments IPN
      public function coinPaymentsIPN(Request $request)
      {
        // Get Payment Gateway
        $payment = PaymentGateways::whereName('Coinpayments')->firstOrFail();

        $merchantId = $payment->key;
        $ipnSecret = $payment->key_secret;

        $currency = $this->settings->currency_code;

        // Find user
        $user = User::findOrFail($request->user);

        // Validations...
    		if (! isset($_POST['ipn_mode']) || $_POST['ipn_mode'] != 'hmac') {
    			exit;
    		}

        if (! isset($_SERVER['HTTP_HMAC']) || empty($_SERVER['HTTP_HMAC'])) {
    			exit;
    		}

    		$getRequest = file_get_contents('php://input');

    		if ($getRequest === FALSE || empty($getRequest)) {
    			exit;
    		}

    		if (! isset($_POST['merchant']) || $_POST['merchant'] != trim($merchantId)) {
    			exit;
    		}

    		$hmac = hash_hmac("sha512", $getRequest, trim($ipnSecret));
    		if (!hash_equals($hmac, $_SERVER['HTTP_HMAC'])) {
    			exit;
    		}

        // Variables
        $ipn_type = $_POST['ipn_type'];
    		$txn_id = $_POST['txn_id'];
    		$item_name = $_POST['item_name'];
    		$currency1 = $_POST['currency1'];
    		$currency2 = $_POST['currency2'];
    		$status = intval($_POST['status']);

        // Check Button payment
        if ($ipn_type != 'button') {
    			exit;
    		}

        // Check currency
        if ($currency1 != $currency) {
    			exit;
    		}

        if ($status >= 100 || $status == 2) {
          try {

            // Insert Deposit
            $this->deposit($user->id, $txn_id, $request->amountOriginal, 'Coinpayments', $request->taxes);

            // Add Funds to User
            $user->increment('wallet', $request->amountOriginal);

          } catch (\Exception $e) {
            Log::info($e->getMessage());
          }
        } // status >= 100

      } // coinPaymentsIPN

      // Return Success Page Payment in Process
      public function paymentProcess()
      {
          return redirect('my/wallet')->with(['payment_process' => true]);
      }

      // Sent payment Mercadopago
      public function sendMercadopago()
      {
        try {
          // Get Payment Gateway
          $payment = PaymentGateways::whereName('Mercadopago')->firstOrFail();

          $fee = $payment->fee;
          $cents =  $payment->fee_cents;

          $taxes = $this->settings->tax_on_wallet ? ($this->request->amount * auth()->user()->isTaxable()->sum('percentage') / 100) : 0;

          $amountFixed = number_format($this->request->amount + ($this->request->amount * $fee / 100) + $cents + $taxes, 2, '.', '');

            // Mercadopago secret key
            \MercadoPago\SDK::setAccessToken($payment->key_secret);

            // Create a preference object
            $preference = new \MercadoPago\Preference();

            // Preference item
            $item = new \MercadoPago\Item();
            $item->title = __('general.add_funds').' @'.auth()->user()->username;
            $item->quantity = 1;
            $item->unit_price = $amountFixed;
            $item->currency_id = $this->settings->currency_code;

            // Item to preference
            $preference->items = [$item];

            // Auto-return
            $preference->auto_return = 'approved';

            // Return url
            $preference->back_urls = [
              'success' => route('mercadopadoProcess',
              [
                'userId' => auth()->user()->id,
                'transactionID' => 'mp_'.str_random(25),
                'amountOriginal' => $this->request->amount,
                'userTaxes' => $this->settings->tax_on_wallet ? auth()->user()->taxesPayable() : null
            ])];

            // External reference
            $preference->external_reference = 'userId='.auth()->user()->id.',amountOriginal='.$this->request->amount.',userTaxes='.auth()->user()->taxesPayable().'';

            $preference->payment_methods = array(
                "excluded_payment_types" => array(
                  array("id" => "cash")
                ),
                "installments" => 1
            );

            $preference->save();

            // Redirect to payment
            $redirectUrl = $payment->sandbox == 'true' ? $preference->sandbox_init_point : $preference->init_point;

            return response()->json([
              'success' => true,
              'url' => $redirectUrl
            ]);

        } catch(\Exception $e) {
          return response()->json([
              'success' => false,
              'errors' => ['error' => $e->getMessage()],
          ]);
        }

      }// End Method sendMercadopago

      public function mercadoPagoProcess(Request $request)
      {
        try {

          // Get Payment Gateway
          $payment = PaymentGateways::whereName('Mercadopago')->firstOrFail();

          // Mercadopago secret key
          \MercadoPago\SDK::setAccessToken($payment->key_secret);

          $paymentData = \MercadoPago\Payment::find_by_id($request->payment_id);

            // if payment not approved
            if ($request->status !== $paymentData->status) {
                \Log::info('Payment Data: '. $paymentData. 'Status Request: '. $request->status);
                throw new \Exception('Payment failed');
            }

            // Verify Deposit
            $verifyTxnId = Deposits::whereTxnId($request->transactionID)->first();

            if (! isset($verifyTxnId)) {
              // Insert Deposit
              $this->deposit(
                $request->userId,
                $request->transactionID,
                $request->amountOriginal,
                'Mercadopago',
                $request->userTaxes ?? null
              );

              // Add Funds to User
              User::find($request->userId)->increment('wallet', $request->amountOriginal);
            }

            return redirect('my/wallet');

        } catch(\Exception $e) {

            return redirect('my/wallet')->withErrorMessage($e->getMessage());
        }

      }// End Method mercadoPagoProcess

      public function sendFlutterwave()
      {
        try {

          // Get Payment Gateway
          $payment = PaymentGateways::whereName('Flutterwave')->firstOrFail();

          $fee = $payment->fee;

          $taxes = $this->settings->tax_on_wallet ? ($this->request->amount * auth()->user()->isTaxable()->sum('percentage') / 100) : 0;

          $amountFixed = number_format($this->request->amount + ($this->request->amount * $fee / 100) + $taxes, 2, '.', '');

          //This generates a payment reference
        $reference = Flutterwave::generateReference();

        // Enter the details of the payment
        $data = [
            'payment_options' => 'card,banktransfer,mobilemoneyghana,mpesa',
            'amount' => $amountFixed,
            'email' => request()->email,
            'tx_ref' => $reference,
            'currency' => $this->settings->currency_code,
            'redirect_url' => route('flutterwaveCallback'),
            'customer' => [
                'email' => auth()->user()->email,
                "name" => auth()->user()->name
            ],

            "meta" => [
                "user" => auth()->id(),
                "amountFinal" => $this->request->amount,
                "taxes" => $this->settings->tax_on_wallet ? auth()->user()->taxesPayable() : null
            ],

            "customizations" => [
                "title" => __('general.add_funds').' @'.auth()->user()->username
            ]
        ];

        $payment = Flutterwave::initializePayment($data);

        if ($payment['status'] !== 'success') {
          return response()->json([
              'success' => false,
              'errors' => ['error' => __('general.error')],
          ]);
        }

        return response()->json([
          'success' => true,
          'url' => $payment['data']['link']
        ]);

        } catch (\Exception $e) {
          return response()->json([
              'success' => false,
              'errors' => ['error' => $e->getMessage()],
          ]);
        }

      }// End sendFlutterwave

      /**
     * Obtain Rave callback information
     * @return void
     */
    public function flutterwaveCallback()
    {
        $status = request()->status;

        //if payment is successful
        if ($status ==  'successful') {

        $transactionID = Flutterwave::getTransactionIDFromCallback();
        $data = Flutterwave::verifyTransaction($transactionID);

        $verifyTxnId = Deposits::where('txn_id', $data['data']['tx_ref'])->first();

        if ($data['data']['status'] == "successful"
        && $data['data']['amount'] >= $data['data']['meta']['amountFinal']
        && $data['data']['currency'] == $this->settings->currency_code
        && ! $verifyTxnId
        ) {
          // Insert Deposit
          $this->deposit(
            $data['data']['meta']['user'],
            $data['data']['tx_ref'],
            $data['data']['meta']['amountFinal'],
            'Flutterwave',
            $data['data']['meta']['taxes'] ?? null
          );

          // Add Funds to User
          User::find($data['data']['meta']['user'])->increment('wallet', $data['data']['meta']['amountFinal']);
        }

      } // end payment is successful

        return redirect('my/wallet');

    }// End flutterwaveCallback

    public function sendMollie()
    {
      // Get Payment Gateway
      $paymentGateway = PaymentGateways::whereName('Mollie')->firstOrFail();

      $mollie = new MollieApiClient();
      $mollie->setApiKey($paymentGateway->key);

      $fee   = $paymentGateway->fee;
    	$cents =  $paymentGateway->fee_cents;

      $taxes = $this->settings->tax_on_wallet ? ($this->request->amount * auth()->user()->isTaxable()->sum('percentage') / 100) : 0;

    	$amount = number_format($this->request->amount + ($this->request->amount * $fee / 100) + $cents + $taxes, 2, '.', '');

      $payment = $mollie->payments->create([
      'amount' => [
          'currency' => $this->settings->currency_code,
          'value' => $amount, // You must send the correct number of decimals, thus we enforce the use of strings
      ],
      'description' => __('general.add_funds').' @'.auth()->user()->username,
      'webhookUrl' => url('webhook/mollie'),
      'redirectUrl' => url('my/wallet'),
      "metadata"    => array(
            'user_id' => auth()->user()->id,
            'amount' =>  $this->request->amount,
            'taxes' => $this->settings->tax_on_wallet ? auth()->user()->taxesPayable() : null
        )
      ]);

      $payment = $mollie->payments->get($payment->id);

      return response()->json([
    			'success' => true,
          'url' => $payment->getCheckoutUrl(), // redirect customer to Mollie checkout page
    	]);

    }// End sendMollie

    public function webhookMollie()
    {
    $paymentGateway = PaymentGateways::whereName('Mollie')->firstOrFail();

    $mollie = new MollieApiClient();
    $mollie->setApiKey($paymentGateway->key);

    if (! $this->request->has('id')) {
            return;
        }

        $payment = $mollie->payments->get($this->request->id);

        if ($payment->isPaid()) {

            // Verify Transaction ID and insert in DB
            $verifiedTxnId = Deposits::where('txn_id', $payment->id)->first();

            if (! isset($verifiedTxnId)) {

              // Insert Deposit
              $this->deposit(
                $payment->metadata->user_id,
                $payment->id,
                $payment->metadata->amount,
                'Mollie',
                $payment->metadata->taxes ?? null
              );

      				//Add Funds to User
      				User::find($payment->metadata->user_id)->increment('wallet', $payment->metadata->amount);

            }// Verify Transaction ID

        }// End isPaid()

    }//<----- End Method webhook()

    public function sendRazorpay()
    {
      // Get Payment Gateway
      $paymentGateway = PaymentGateways::whereName('Razorpay')->firstOrFail();

      $fee   = $paymentGateway->fee;
    	$cents =  $paymentGateway->fee_cents;

      $taxes = $this->settings->tax_on_wallet ? ($this->request->amount * auth()->user()->isTaxable()->sum('percentage') / 100) : 0;

    	$amountFixed = number_format($this->request->amount + ($this->request->amount * $fee / 100) + $cents + $taxes, 2, '.', '');
    	$amount   = ($amountFixed*100);

      if (isset($this->request->payment_id)) {

        //Input items of form
        $input = $this->request->all();
        //get API Configuration
        $api = new Api($paymentGateway->key, $paymentGateway->key_secret);
        //Fetch payment information by razorpay_payment_id
        $payment = $api->payment->fetch($this->request->payment_id);

        if (count($input)) {
            try {
                $response = $api->payment->fetch($this->request->payment_id)->capture(array('amount'=>$payment['amount']));

            } catch (\Exception $e) {
              return response()->json([
					        'success' => false,
					        'errors' => ['error' => $e->getMessage()],
					    ]);
            }

            // Insert DB
            $this->deposit(
              auth()->user()->id,
              $response->id,
              $this->request->amount,
              'Razorpay',
              $this->settings->tax_on_wallet ? auth()->user()->taxesPayable() : null
            );

            //Add Funds to User
            auth()->user()->increment('wallet', $this->request->amount);
          }

        return response()->json([
          "success" => true,
          "url" => url('my/wallet')
        ]);

      } else {
        return response()->json([
      			'success' => true,
            'insertBody' => "<script type='text/javascript'>var options = {
            'key': '".$paymentGateway->key."',
            'amount': ".$amount.", // 2000 paise = INR 20
            'name': '".$this->settings->title."',
            'description': '".__('general.add_funds').' @'.auth()->user()->username."',
            'handler': function (response){

              var input = $('<input type=hidden name=payment_id />').val(response.razorpay_payment_id);
              $('#formAddFunds').append(input);
              $('#addFundsBtn').trigger('click');
            },

            'prefill': {
               'name': '".auth()->user()->username."',
               'email':   '".auth()->user()->email."',
            },

            'theme': {
                'color': '#00A65A'
            }
            };
            var rzp1 = new Razorpay(options);
            rzp1.open();
            </script>"
      	]);
      }
    }//<----- End Method sendRazorpay()


}
