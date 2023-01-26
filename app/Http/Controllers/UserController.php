<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Subscriptions;
use App\Models\AdminSettings;
use App\Models\Withdrawals;
use App\Models\Updates;
use App\Models\Like;
use App\Models\Notifications;
use App\Models\Purchases;
use App\Models\Reports;
use App\Models\Restrictions;
use App\Models\Media;
use App\Models\TaxRates;
use App\Models\Plans;
use App\Models\PaymentGateways;
use App\Models\Transactions;
use App\Models\VerificationRequests;
use App\Models\Deposits;
use App\Models\Categories;
use App\Helper;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use App\Notifications\AdminVerificationPending;
use App\Notifications\AdminWithdrawalPending;
use App\Models\Referrals;
use App\Models\ShopCategories;
use App\Models\ReferralTransactions;
use Yabacon\Paystack;
use App\Events\SubscriptionDisabledEvent;
use App\Models\LoginSessions;
use Illuminate\Validation\Rule;
use Phattarachai\LaravelMobileDetect\Agent;
use App\Models\Stories;
use Image;
use DB;
use QrCode;

class UserController extends Controller
{
  use Traits\UserDelete;
  use Traits\Functions;

  public function __construct(Request $request, AdminSettings $settings) {
    $this->request = $request;
    $this->settings = $settings::first();
  }

  /**
	 * Display dashboard user
	 *
	 * @return View
	 */
  public function dashboard()
  {
    if (auth()->user()->verified_id != 'yes') {
      abort(404);
    }

    $earningNetUser = auth()->user()->myPaymentsReceived()->sum('earning_net_user');
    $earningNetSubscriptions = auth()->user()->myPaymentsReceived()->whereType('subscription')->sum('earning_net_user');
    $earningNetTips = auth()->user()->myPaymentsReceived()->whereType('tip')->sum('earning_net_user');
    $earningNetPPV = auth()->user()->myPaymentsReceived()->whereType('ppv')->sum('earning_net_user');
    $subscriptionsActive = auth()->user()->totalSubscriptionsActive();

    $month = date('m');
    $year = date('Y');
    $daysMonth = Helper::daysInMonth($month, $year);
    $dateFormat = "$year-$month-";

    $monthFormat  = trans("months.$month");
    $currencySymbol = $this->settings->currency_symbol;

    for ($i=1; $i <= $daysMonth; ++$i) {
      $date = date('Y-m-d', strtotime($dateFormat.$i));
      $_subscriptions = auth()->user()->myPaymentsReceived()->whereDate('created_at', '=', $date)->sum('earning_net_user');
      $monthsData[] =  "'$monthFormat $i'";
      $_earningNetUser = $_subscriptions;
      $earningNetUserSum[] = $_earningNetUser;
    }

		// Today
		$stat_revenue_today = Transactions::where('created_at', '>=', date('Y-m-d H:i:s', strtotime('today')))
		->whereApproved('1')
    ->whereSubscribed(auth()->id())
		 ->sum('earning_net_user');

     // Yesterday
 		$stat_revenue_yesterday = Transactions::where('created_at', '>=', Carbon::yesterday())
    ->where('created_at', '<', date('Y-m-d H:i:s', strtotime('today')))
 		->whereApproved('1')
     ->whereSubscribed(auth()->id())
 		 ->sum('earning_net_user');

		 // Week
	 	$stat_revenue_week = Transactions::whereBetween('created_at', [
	        Carbon::parse()->startOfWeek(),
	        Carbon::parse()->endOfWeek(),
	    ])->whereApproved('1')
      ->whereSubscribed(auth()->id())
	 	 ->sum('earning_net_user');

     // Last Week
	 	$stat_revenue_last_week = Transactions::whereBetween('created_at', [
	        Carbon::now()->startOfWeek()->subWeek(),
	        Carbon::now()->subWeek()->endOfWeek(),
	    ])->whereApproved('1')
      ->whereSubscribed(auth()->id())
	 	 ->sum('earning_net_user');

		 // Month
	 	$stat_revenue_month = Transactions::whereBetween('created_at', [
	        Carbon::parse()->startOfMonth(),
	        Carbon::parse()->endOfMonth(),
	    ])->whereApproved('1')
      ->whereSubscribed(auth()->id())
			->sum('earning_net_user');

      // Last Month
 	 	$stat_revenue_last_month = Transactions::whereBetween('created_at', [
 	        Carbon::now()->startOfMonth()->subMonth(),
 	        Carbon::now()->subMonth()->endOfMonth(),
 	    ])->whereApproved('1')
       ->whereSubscribed(auth()->id())
 			->sum('earning_net_user');

    $label = implode(',', $monthsData);
    $data = implode(',', $earningNetUserSum);

    return view('users.dashboard', [
          'earningNetUser' => $earningNetUser,
          'earningNetSubscriptions' => $earningNetSubscriptions,
          'earningNetTips' => $earningNetTips,
          'earningNetPPV' => $earningNetPPV,
          'subscriptionsActive' => $subscriptionsActive,
          'label' => $label,
          'data' => $data,
          'month' => $monthFormat,
          'stat_revenue_today' => $stat_revenue_today,
          'stat_revenue_yesterday' => $stat_revenue_yesterday,
    			'stat_revenue_week' => $stat_revenue_week,
          'stat_revenue_last_week' => $stat_revenue_last_week,
    			'stat_revenue_month' => $stat_revenue_month,
          'stat_revenue_last_month' => $stat_revenue_last_month 
        ]);
  }

  public function profile($slug, $media = null)
  {
    $media = request('media');
    $mediaTitle = null;
    $sortPostByTypeMedia = null;

    if (isset($media)) {
      $mediaTitle = trans('general.'.$media.'').' - ';
      $sortPostByTypeMedia = '&media='.$media;
      $media = '/'.$media;
    }

    // All Payments
    $allPayment = PaymentGateways::where('enabled', '1')->whereSubscription('yes')->get();

    // Stripe Key
      $_stripe = PaymentGateways::whereName('Stripe')->where('enabled', '1')->select('key')->first();

      $user = User::where('username','=', $slug)->whereStatus('active')->firstOrFail();

      if ($media && $user->verified_id != 'yes') {
        abort(404);
      }

      // Hidden Profile Admin
      if (auth()->check() && $this->settings->hide_admin_profile == 'on'
          && $user->id == 1
          && auth()->id() != 1
          ) {
            abort(404);
      } elseif (auth()->guest()
          && $this->settings->hide_admin_profile == 'on'
          && $user->id == 1
        ) {
          abort(404);
        }

        // Hidden Profile Blocked Countries
        if (in_array(Helper::userCountry(), $user->blockedCountries())
            && auth()->check()
            && auth()->user()->permission != 'all'
            && auth()->id() != $user->id
            || auth()->guest()
            && in_array(Helper::userCountry(), $user->blockedCountries())
          ) {
            abort(404);
          }

      if (isset($media)) {
        $query = $user->media();
      } else {
        $query = $user->updates()->whereFixedPost('0');
      }

      //=== Photos
  		$query->when(request('media') == 'photos', function($q) {
  			$q->where('media.image', '<>', '');
  		});

      //=== Videos
  		$query->when(request('media') == 'videos', function($q) use($user) {
  			$q->where('media.video', '<>', '')
        ->where(function ($query) {
          $query->when(request('sort') == 'unlockable', function($q) {
            $q->where('updates.price', '<>', 0.00);
          });

          $query->when(request('sort') == 'free', function($q) {
            $q->where('updates.locked', 'no');
          });
        })
          ->orWhere('media.video_embed', '<>', '')
          ->where('media.user_id', $user->id);
  		});

      //=== Audio
  		$query->when(request('media') == 'audio', function($q) {
  			$q->where('media.music', '<>', '');
  		});

      //=== Files
  		$query->when(request('media') == 'files', function($q) {
  			$q->where('media.file', '<>', '');
  		});

      // Sort by older
      $query->when(request('sort') == 'oldest', function($q) {
        $q->orderBy('updates.id', 'asc');
      });

      // Sort by unlockable
      $query->when(request('sort') == 'unlockable', function($q) {
        $q->where('updates.price', '<>', 0.00);
      });

      // Sort by free
      $query->when(request('sort') == 'free', function($q) {
        $q->where('updates.locked', 'no');
      });

      $updates = $query->orderBy('updates.id','desc')
      ->groupBy('updates.id')
      ->paginate($this->settings->number_posts_show);

      // Check if subscription exists
      if (auth()->check()) {
        $checkSubscription = auth()->user()->checkSubscription($user);

        if ($checkSubscription) {
          // Get Payment gateway the subscription
          $paymentGatewaySubscription = Transactions::whereSubscriptionsId($checkSubscription->id)->first();
        }

        // Check Payment Incomplete
        $paymentIncomplete = auth()->user()
          ->userSubscriptions()
            ->whereIn('stripe_price', $user->plans()->pluck('name'))
            ->whereStripeStatus('incomplete')
            ->first();
      }

      //<<<-- * Redirect the user real name * -->>>
      $uri = request()->path();
      $uriCanonical = $user->username.$media;

      if ($uri != $uriCanonical) {
        return redirect($uriCanonical);
      }

      // Find post pinned
      $findPostPinned = $user->updates()->whereFixedPost('1')->paginate($this->settings->number_posts_show);

      // Count all likes
      $likeCount = $user->likesCount();

      // Categories
      $categories = explode(',', $user->categories_id);

      // Subscriptions Active
      $subscriptionsActive = $user->totalSubscriptionsActive();

      // User Plans
      $plans = $user->plans()
        ->where('interval', '<>', 'monthly')
        ->whereStatus('1')
        ->get();

        // User Plan Monthly Active
        $userPlanMonthlyActive = $user->planActive();

        // Total Items of User
        $userProducts = $user->products()->whereStatus('1');

        // Filter by oldest
        $userProducts->when(request('sort') == 'oldest', function($q) {
          $q->orderBy('id', 'asc');
        });

        // Filter by lowest price
        $userProducts->when(request('sort') == 'priceMin', function($q) {
          $q->orderBy('price', 'asc');
        });

        // Filter by Highest price
        $userProducts->when(request('sort') == 'priceMax', function($q) {
          $q->orderBy('price', 'desc');
        });

        // Filter by Physical Products
        $userProducts->when(request('sort') == 'physical', function($q) {
          $q->where('type', 'physical');
        });

        // Filter by Digital Products
        $userProducts->when(request('sort') == 'digital', function($q) {
          $q->where('type', 'digital');
        });

        // Filter by Custom Content
        $userProducts->when(request('sort') == 'custom', function($q) {
          $q->where('type', 'custom');
        });

        // Categories Shop
        if (request('media') == 'shop') {
          $cat  = request('cat');
          $shopCategories = ShopCategories::orderBy('name')->get();

          if ($cat) {
            $category = ShopCategories::whereSlug($cat)->firstOrFail();

            // Filter by Category
            $userProducts->when($cat, function($q) use ($cat, $category) {
              $q->where('category', $category->id);
            });
          }

        }

        $userProducts = $userProducts->orderBy('id', 'desc')
            ->paginate(15);

      return view('users.profile',[
          'user' => $user,
            'updates' => $updates,
            'findPostPinned' => $findPostPinned,
            '_stripe' => $_stripe,
            'checkSubscription' => $checkSubscription ?? null,
            'media' => $media,
            'mediaTitle' => $mediaTitle,
            'sortPostByTypeMedia' => $sortPostByTypeMedia,
            'allPayment' => $allPayment,
            'paymentIncomplete' => $paymentIncomplete ?? null,
            'likeCount' => $likeCount,
            'categories' => $categories,
            'paymentGatewaySubscription' => $paymentGatewaySubscription->payment_gateway ?? null,
            'subscriptionsActive' => $subscriptionsActive,
            'plans' => $plans,
            'userPlanMonthlyActive' => $userPlanMonthlyActive ?? null,
            'userProducts' => $userProducts,
            'shopCategories' => $shopCategories ?? null
        ]);

  }//<--- End Method

  public function postDetail($slug, $id)
  {
    $user    = User::where( 'username','=', $slug )->where('status','active')->firstOrFail();
    $updates = Updates::whereUserId($user->id)
    ->whereId($id)
    ->where('status', '<>', 'encode')
    ->orderBy('id','desc')
    ->paginate(1);

    $updateCount = $updates->count();

      // Check the status of the post
      if (auth()->check() && $updateCount != 0 && $updates[0]->user_id != auth()->id()
        && $updates[0]->status == 'pending'
        && auth()->user()->role != 'admin'
      ) {
  			abort(404);
  		} elseif (auth()->guest() && $updateCount != 0 && $updates[0]->status == 'pending') {
  			abort(404);
  		}

      // Hidden Profile Blocked Countries
      if (in_array(Helper::userCountry(), $user->blockedCountries())
          && auth()->check()
          && auth()->user()->permission != 'all'
          && auth()->id() != $user->id
          || auth()->guest()
          && in_array(Helper::userCountry(), $user->blockedCountries())
        ) {
          abort(404);
        }

      $users = $this->userExplore();

      if ($user->status == 'suspended' || $updateCount == 0) {
        abort(404);
      }

      //<<<-- * Redirect the user real name * -->>>
      $uri = request()->path();
      $uriCanonical = $user->username.'/post/'.$updates[0]->id;

      if( $uri != $uriCanonical ) {
        return redirect($uriCanonical);
      }

      return view('users.post-detail',
          ['user' => $user,
          'updates' => $updates,
          'inPostDetail' => true,
          'users' => $users
        ]);

  }//<--- End Method


    public function settings()
    {
        return view('users.settings');
    }

    public function updateSettings()
    {
      $input = $this->request->all();
      $id = auth()->id();

     $validator = Validator::make($input, [
    'profession'  => '|min:6|max:100|string',
    'countries_id' => 'required',
    ]);

     if ($validator->fails()) {
         return redirect()->back()
                   ->withErrors($validator)
                   ->withInput();
     }

     $user               = User::find($id);
     $user->profession   = trim(strip_tags($input['profession']));
     $user->countries_id = trim($input['countries_id']);
     $user->email_new_subscriber = $input['email_new_subscriber'] ?? 'no';
     $user->save();

     \Session::flash('status', trans('auth.success_update'));

     return redirect('settings');
    }

    public function notifications()
    {
      // Notifications
      $notifications = DB::table('notifications')
         ->select(DB::raw('
        notifications.id id_noty,
        notifications.type,
        notifications.target,
        notifications.created_at,
        users.id userId,
        users.username,
        users.hide_name,
        users.name,
        users.avatar,
        updates.id,
        updates.description,
        U2.username usernameAuthor,
        messages.message,
        messages.to_user_id userDestination,
        products.name productName
        '))
        ->leftjoin('users', 'users.id', '=', DB::raw('notifications.author'))
        ->leftjoin('updates', 'updates.id', '=', DB::raw('notifications.target'))
        ->leftjoin('messages', 'messages.id', '=', DB::raw('notifications.target'))
        ->leftjoin('users AS U2', 'U2.id', '=', DB::raw('updates.user_id'))
        ->leftjoin('comments', 'comments.updates_id', '=', DB::raw('notifications.target
        AND comments.user_id = users.id
        AND comments.updates_id = updates.id'))
        ->leftjoin('products', 'products.id', '=', DB::raw('notifications.target'))
        ->where('notifications.destination', '=',  auth()->id())
        ->where('users.status', '=',  'active');

        // Sort by subscriptions
        $notifications->when(request('sort') == 'subscriptions', function($q) {
          $q->where('notifications.type', 1);
        });

        // Sort by likes
        $notifications->when(request('sort') == 'likes', function($q) {
          $q->where('notifications.type', 2);
        });

        // Sort by tips
        $notifications->when(request('sort') == 'tips', function($q) {
          $q->where('notifications.type', 5);
        });

        // Sort by live_streaming
        $notifications->when(request('sort') == 'live_streaming', function($q) {
          $q->where('notifications.type', 14);
        });

        // Sort by mentions
        $notifications->when(request('sort') == 'mentions', function($q) {
          $q->where('notifications.type', 16);
        });

        $notifications = $notifications->groupBy('notifications.id')
        ->orderBy('notifications.id', 'DESC')
        ->paginate(20);

      // Mark seen Notification
      $getNotifications = Notifications::where('destination', auth()->id())->where('status', '0');
      $getNotifications->count() > 0 ? $getNotifications->update([
        'status' => '1'
        ]) : null;

      return view('users.notifications', ['notifications' => $notifications]);
    }

    public function settingsNotifications()
    {
      $user = User::find(auth()->id());
      $user->notify_new_subscriber = $this->request->notify_new_subscriber ?? 'no';
      $user->notify_liked_post = $this->request->notify_liked_post ?? 'no';
      $user->notify_liked_comment = $this->request->notify_liked_comment ?? 'no';
      $user->notify_commented_post = $this->request->notify_commented_post ?? 'no';
      $user->notify_new_tip = $this->request->notify_new_tip ?? 'no';
      $user->email_new_subscriber = $this->request->email_new_subscriber ?? 'no';
      $user->notify_email_new_post = $this->request->notify_email_new_post ?? 'no';
      $user->notify_new_ppv = $this->request->notify_new_ppv ?? 'no';
      $user->email_new_tip = $this->request->email_new_tip ?? 'no';
      $user->email_new_ppv = $this->request->email_new_ppv ?? 'no';
      $user->notify_live_streaming = $this->request->notify_live_streaming ?? 'no';
      $user->notify_mentions = $this->request->notify_mentions ?? 'no';
      $user->save();

      return response()->json([
          'success' => true
      ]);
    }

    public function deleteNotifications()
    {
      auth()->user()->notifications()->delete();
      return back();
    }

    public function password()
    {
      return view('users.password');
    }//<--- End Method

      public function updatePassword(Request $request)
      {

  	   $input = $request->all();
  	   $id    = auth()->id();
       $passwordRequired = auth()->user()->password != '' ? 'required|' : null;

  		   $validator = Validator::make($input, [
  			'old_password' => $passwordRequired.'min:6',
  	     'new_password' => 'required|min:6',
      	]);

  			if ($validator->fails()) {
           return redirect()->back()
  						 ->withErrors($validator)
  						 ->withInput();
  					 }

  	   if (auth()->user()->password != '' && !\Hash::check($input['old_password'], auth()->user()->password)) {
  		    return redirect('settings/password')->with( array( 'incorrect_pass' => trans('general.password_incorrect') ) );
  		}

  	   $user = User::find($id);
  	   $user->password  = \Hash::make($input[ "new_password"] );
  	   $user->save();

  	   \Session::flash('status',trans('auth.success_update_password'));

  	   return redirect('settings/password');

  	}//<--- End Method

    public function mySubscribers()
    {
      $subscriptions = auth()->user()->mySubscriptions()->orderBy('id','desc')->paginate(20);


      return view('users.my_subscribers')->withSubscriptions($subscriptions);
    }

    public function mySubscriptions()
    {
      $subscriptions = auth()->user()->userSubscriptions()->orderBy('id','desc')->paginate(20);
      return view('users.my_subscriptions')->withSubscriptions($subscriptions);
    }

    public function myPayments()
    {
      if (request()->is('my/payments')) {
        $transactions = auth()->user()->myPayments()->orderBy('id','desc')->paginate(20);
      } elseif (request()->is('my/payments/received')) {
        $transactions = auth()->user()->myPaymentsReceived()->orderBy('id','desc')->paginate(20);
      } else {
        abort(404);
      }

      return view('users.my_payments')->withTransactions($transactions);
    }

    public function payoutMethod()
    {
      $stripeConnectCountries = explode(',', $this->settings->stripe_connect_countries);

      return view('users.payout_method')->withStripeConnectCountries($stripeConnectCountries);
    }

    public function payoutMethodConfigure()
    {

		if ($this->request->type != 'paypal'
        && $this->request->type != 'bank'
        && $this->request->type != 'payoneer'
        && $this->request->type != 'zelle'
        && $this->request->type != 'western'
      ) {
			return redirect('settings/payout/method');
		}

		// Validate Email Paypal
		if ($this->request->type == 'paypal') {
			$rules = array(
	        'email_paypal' => 'required|email|confirmed',
        );

		$this->validate($this->request, $rules);

		$user                  = User::find(auth()->id());
		$user->paypal_account  = $this->request->email_paypal;
		$user->payment_gateway = 'PayPal';
		$user->save();

		\Session::flash('status', trans('admin.success_update'));
		return redirect('settings/payout/method')->withInput();

		}// Validate Email Paypal

    // Validate Email Payoneer
		elseif ($this->request->type == 'payoneer') {
			$rules = array(
	        'email_payoneer' => 'required|email|confirmed',
        );

		$this->validate($this->request, $rules);

		$user                  = User::find(auth()->id());
		$user->payoneer_account  = $this->request->email_payoneer;
		$user->payment_gateway = 'Payoneer';
		$user->save();

		\Session::flash('status', trans('admin.success_update'));
		return redirect('settings/payout/method')->withInput();

		}// Validate Email Payoneer

    // Validate Email Zelle
		elseif ($this->request->type == 'zelle') {
			$rules = array(
	        'email_zelle' => 'required|email|confirmed',
        );

		$this->validate($this->request, $rules);

		$user                  = User::find(auth()->id());
		$user->zelle_account  = $this->request->email_zelle;
		$user->payment_gateway = 'Zelle';
		$user->save();

		\Session::flash('status', trans('admin.success_update'));
		return redirect('settings/payout/method')->withInput();

		}// Validate Email Zelle

    // Validate Western
		elseif ($this->request->type == 'western') {
      $messages = [
        'document_id.required' => __('validation.required', ['attribute' => __('general.document_id')])
      ];

			$rules = array(
	        'name' => 'required',
          'document_id' => 'required',
        );

		$this->validate($this->request, $rules, $messages);

    auth()->user()->update([
      'name' => $this->request->name,
      'document_id' => $this->request->document_id,
      'payment_gateway' => 'Western Union'
    ]);

		\Session::flash('status', trans('admin.success_update'));
		return redirect('settings/payout/method')->withInput();

		}// Validate Western

    // Validate Bank
		elseif ($this->request->type == 'bank') {

			$rules = array(
	        'bank_details'  => 'required|min:20',
       		 );

		  $this->validate($this->request, $rules);

		   $user                  = User::find(auth()->id());
		   $user->bank            = strip_tags($this->request->bank_details);
		   $user->payment_gateway = 'Bank';
		   $user->save();

			\Session::flash('status', trans('admin.success_update'));
			return redirect('settings/payout/method');
		}// End Bank

    }//<--- End Method

    public function uploadAvatar()
		{
      $validator = Validator::make($this->request->all(), [
        'avatar' => 'required|mimes:jpg,gif,png,jpe,jpeg|dimensions:min_width=200,min_height=200|max:'.$this->settings->file_size_allowed.'',
      ]);

		   if ($validator->fails()) {
		        return response()->json([
				        'success' => false,
				        'errors' => $validator->getMessageBag()->toArray(),
				    ]);
		    }

		// PATHS
	  $path = config('path.avatar');

		 //<--- HASFILE PHOTO
	    if ($this->request->hasFile('avatar')) {
				$photo     = $this->request->file('avatar');
				$extension = $this->request->file('avatar')->getClientOriginalExtension();
				$avatar    = strtolower(auth()->user()->username.'-'.auth()->id().time().str_random(10).'.'.$extension );

				$imgAvatar = Image::make($photo)->orientate()->fit(200, 200, function ($constraint) {
					$constraint->aspectRatio();
					$constraint->aspectRatio();
				})->encode($extension);

				// Copy folder
				Storage::put($path.$avatar, $imgAvatar, 'public');

				//<<<-- Delete old image -->>>/
				if (auth()->user()->avatar != $this->settings->avatar) {
					Storage::delete(config('path.avatar').auth()->user()->avatar);
				}

				// Update Database
				auth()->user()->update(['avatar' => $avatar]);

				return response()->json([
				        'success' => true,
				        'avatar' => Helper::getFile($path.$avatar),
				    ]);
	    }//<--- HASFILE PHOTO
    }//<--- End Method Avatar

    public function settingsPage()
    {
      $genders = explode(',', $this->settings->genders);
      $categories = explode(',', auth()->user()->categories_id);

      return view('users.edit_my_page', [
        'genders' => $genders,
        'categories' => $categories
      ]);
    }

    public function updateSettingsPage()
    {

      $input = $this->request->all();
      $id    = auth()->id();
      $input['is_admin'] = auth()->user()->permissions;
      $input['is_creator'] = auth()->user()->verified_id == 'yes' ? 0 : 1;
      $input['is_birthdateChanged'] = auth()->user()->birthdate_changed == 'no' ? 0 : 1;

      $messages = array (
      "letters" => trans('validation.letters'),
      "email.required_if" => trans('validation.required'),
      "birthdate.before" => trans('general.error_adult'),
      "birthdate.required_if" => trans('validation.required'),
      "story.required_if" => trans('validation.required'),
		);

		 Validator::extend('ascii_only', function($attribute, $value, $parameters){
    		return !preg_match('/[^x00-x7F\-]/i', $value);
		});

		// Validate if have one letter
	Validator::extend('letters', function($attribute, $value, $parameters){
    	return preg_match('/[a-zA-Z0-9]/', $value);
	});

      $validator = Validator::make($input, [
        'full_name' => 'required|string|max:100',
        'username'  => 'required|min:3|max:25|ascii_only|alpha_dash|letters|unique:pages,slug|unique:reserved,name|unique:users,username,'.$id,
        'email'  => 'required_if:is_admin,==,full_access|unique:users,email,'.$id,
        'website' => 'url',
        'facebook' => 'url',
        'twitter' => 'url',
        'instagram' => 'url',
        'youtube' => 'url',
        'pinterest' => 'url',
        'github' => 'url',
        'snapchat' => 'url',
        'tiktok' => 'url',
        'telegram' => 'url',
        'twitch' => 'url',
        'discord' => 'url',
        'vk' => 'url',
        'reddit' => 'url',
        'spotify' => 'url',
        'story' => 'required_if:is_creator,==,0|max:'.$this->settings->story_length.'',
        'countries_id' => 'required',
        'city' => 'max:100',
        'address' => 'max:100',
        'zip' => 'max:20',
        'profession'  => 'min:6|max:100|string',
        'birthdate' => 'required_if:is_birthdateChanged,==,0|date_format:'.Helper::formatDatepicker().'|before:'.Carbon::now()->subYears(18),
     ], $messages);

      if ($validator->fails()) {
           return response()->json([
               'success' => false,
               'errors' => $validator->getMessageBag()->toArray(),
           ]);
       } //<-- Validator

       $story = $this->request->story ?: auth()->user()->story;

       $categories = $this->request->categories_id ? implode( ',', $this->request->categories_id) : '';

      $user                  = User::find($id);
      $user->name            = strip_tags($this->request->full_name);
      $user->username        = trim($this->request->username);
      $user->email           = $this->request->email ? trim($this->request->email) : auth()->user()->email;
      $user->website         = trim($this->request->website) ?? '';
      $user->categories_id   = $categories;
      $user->profession      = $this->request->profession;
      $user->countries_id    = $this->request->countries_id;
      $user->city            = $this->request->city;
      $user->address         = $this->request->address;
      $user->zip             = $this->request->zip;
      $user->company         = $this->request->company;
      $user->story           = trim(Helper::checkTextDb($story));
      $user->facebook        = trim($this->request->facebook) ?? '';
      $user->twitter         = trim($this->request->twitter) ?? '';
      $user->instagram       = trim($this->request->instagram) ?? '';
      $user->youtube         = trim($this->request->youtube) ?? '';
      $user->pinterest       = trim($this->request->pinterest) ?? '';
      $user->github          = trim($this->request->github) ?? '';
      $user->snapchat        = trim($this->request->snapchat) ?? '';
      $user->tiktok          = trim($this->request->tiktok) ?? '';
      $user->telegram        = trim($this->request->telegram) ?? '';
      $user->twitch          = trim($this->request->twitch) ?? '';
      $user->discord         = trim($this->request->discord) ?? '';
      $user->vk              = trim($this->request->vk) ?? '';
      $user->reddit          = trim($this->request->reddit) ?? '';
      $user->spotify         = trim($this->request->spotify) ?? '';
      $user->plan            = 'user_'.auth()->id();
      $user->gender          = $this->request->gender;
      $user->birthdate       = auth()->user()->birthdate_changed == 'no' ? Carbon::createFromFormat(Helper::formatDatepicker(), $this->request->birthdate)->format('m/d/Y') : auth()->user()->birthdate;
      $user->birthdate_changed = 'yes';
      $user->language      = $this->request->language;
      $user->hide_name     = $this->request->hide_name ?? 'no';
      $user->save();

      return response()->json([
              'success' => true,
              'url' => url(trim($this->request->username)),
              'locale' => $this->request->language != '' && config('app.locale') != $this->request->language ? true : false,
            ]);

    }//<--- End Method

    public function saveSubscription()
    {
      $input = $this->request->all();

      if (auth()->user()->verified_id == 'no' || auth()->user()->verified_id == 'reject') {
        return back();
      }

      if ($this->settings->currency_position == 'right') {
				$currencyPosition =  2;
			} else {
				$currencyPosition =  null;
			}

      if (! $this->request->free_subscription) {

        $messages = [
  			'price_weekly.min' => trans('users.price_minimum_subscription'.$currencyPosition, ['symbol' => $this->settings->currency_symbol, 'code' => $this->settings->currency_code]),
  			'price_weekly.max' => trans('users.price_maximum_subscription'.$currencyPosition, ['symbol' => $this->settings->currency_symbol, 'code' => $this->settings->currency_code]),

        "price_weekly.required_if" => trans('general.subscription_price_required'),

        'price.min' => trans('users.price_minimum_subscription'.$currencyPosition, ['symbol' => $this->settings->currency_symbol, 'code' => $this->settings->currency_code]),
  			'price.max' => trans('users.price_maximum_subscription'.$currencyPosition, ['symbol' => $this->settings->currency_symbol, 'code' => $this->settings->currency_code]),
        "price.required" => trans('general.subscription_price_required'),

        'price_quarterly.min' => trans('users.price_minimum_subscription'.$currencyPosition, ['symbol' => $this->settings->currency_symbol, 'code' => $this->settings->currency_code]),
  			'price_quarterly.max' => trans('users.price_maximum_subscription'.$currencyPosition, ['symbol' => $this->settings->currency_symbol, 'code' => $this->settings->currency_code]),
        "price_quarterly.required_if" => trans('general.subscription_price_required'),

        'price_biannually.min' => trans('users.price_minimum_subscription'.$currencyPosition, ['symbol' => $this->settings->currency_symbol, 'code' => $this->settings->currency_code]),
  			'price_biannually.max' => trans('users.price_maximum_subscription'.$currencyPosition, ['symbol' => $this->settings->currency_symbol, 'code' => $this->settings->currency_code]),
        "price_biannually.required_if" => trans('general.subscription_price_required'),

        'price_yearly.min' => trans('users.price_minimum_subscription'.$currencyPosition, ['symbol' => $this->settings->currency_symbol, 'code' => $this->settings->currency_code]),
  			'price_yearly.max' => trans('users.price_maximum_subscription'.$currencyPosition, ['symbol' => $this->settings->currency_symbol, 'code' => $this->settings->currency_code]),
        "price_yearly.required_if" => trans('general.subscription_price_required'),
  		];

        $validator = Validator::make($input, [
          'price_weekly' => 'required_if:status_weekly,1|numeric|min:'.$this->settings->min_subscription_amount.'|max:'.$this->settings->max_subscription_amount.'',
          'price' => 'required|numeric|min:'.$this->settings->min_subscription_amount.'|max:'.$this->settings->max_subscription_amount.'',
          'price_quarterly' => 'required_if:status_quarterly,1|numeric|min:'.$this->settings->min_subscription_amount.'|max:'.$this->settings->max_subscription_amount.'',
          'price_biannually' => 'required_if:status_biannually,1|numeric|min:'.$this->settings->min_subscription_amount.'|max:'.$this->settings->max_subscription_amount.'',
          'price_yearly' => 'required_if:status_yearly,1|numeric|min:'.$this->settings->min_subscription_amount.'|max:'.$this->settings->max_subscription_amount.'',
       ], $messages);

       if ($validator->fails()) {
          return redirect()->back()
              ->withErrors($validator)
              ->withInput();
            }

            // Subscription Price (Weekly)
            if ($this->request->price_weekly) {
              $plan = Plans::updateOrCreate(
                [
                  'user_id' => auth()->id(),
                  'name' => 'user_'.auth()->id().'_weekly'
                ],
                [
                  'price' => $this->request->price_weekly,
                  'interval' => 'weekly',
                  'status' => $this->request->status_weekly ?? '0',
              ]);
            }

            // Subscription Price (Per month)
            if ($this->request->price) {
              $plan = Plans::updateOrCreate(
                [
                  'user_id' => auth()->id(),
                  'name' => 'user_'.auth()->id()
                ],
               [
                 'price' => $this->request->price,
                 'interval' => 'monthly',
                 'status' => '1'
              ]);
            }

            // Subscription Price (3 months)
            if ($this->request->price_quarterly) {
              $plan = Plans::updateOrCreate(
                [
                  'user_id' => auth()->id(),
                  'name' => 'user_'.auth()->id().'_quarterly'
                ],
                [

                  'price' => $this->request->price_quarterly,
                  'interval' => 'quarterly',
                  'status' => $this->request->status_quarterly ?? '0',
              ]);
            }

            // Subscription Price (6 months)
            if ($this->request->price_biannually) {
              $plan = Plans::updateOrCreate(
                [
                  'user_id' => auth()->id(),
                  'name' => 'user_'.auth()->id().'_biannually'
                ],
                [

                  'price' => $this->request->price_biannually,
                  'interval' => 'biannually',
                  'status' => $this->request->status_biannually ?? '0',
              ]);
            }

            // Subscription Price (12 months)
            if ($this->request->price_yearly) {
              $plan = Plans::updateOrCreate(
                [
                  'user_id' => auth()->id(),
                  'name' => 'user_'.auth()->id().'_yearly'
                ],
                [

                  'price' => $this->request->price_yearly,
                  'interval' => 'yearly',
                  'status' => $this->request->status_yearly ?? '0',
              ]);
            }

      }// Request free subscription

      $freeSubscription = $this->request->free_subscription ?? 'no';

      // Notify to subscribers
      $notifySubscriber = $freeSubscription != auth()->user()->free_subscription
          ? event(new SubscriptionDisabledEvent(auth()->user(), $freeSubscription))
          : null;

      // Free Subscription
      auth()->user()->update(['free_subscription' => $freeSubscription]);

			return redirect('settings/subscription')
          ->withStatus(trans('admin.success_update'));

    }//<--- End Method

  protected function createPlanStripe()
  {
    $payment = PaymentGateways::whereName('Stripe')->whereEnabled(1)->first();
    $plan = 'user_'.auth()->id();

    if ($payment) {
      if ($this->request->price != auth()->user()->price) {
        $stripe = new \Stripe\StripeClient($payment->key_secret);

        try {
          $planCurrent = $stripe->plans->retrieve($plan, []);

          // Delete old plan
          $stripe->plans->delete($plan, []);

          // Delete Product
          $stripe->products->delete($planCurrent->product, []);
        } catch (\Exception $exception) {
          // not exists
        }

        // Create Plan
        $plan = $stripe->plans->create([
            'currency' => $this->settings->currency_code,
            'interval' => 'month',
            "product" => [
                "name" => trans('general.subscription_for').' @'.auth()->user()->username,
            ],
            'nickname' => $plan,
            'id' => $plan,
            'amount' => $this->settings->currency_code == 'JPY' ? $this->request->price : $this->request->price * 100,
        ]);
      }
    }
  }

  protected function createPlanPaystack()
  {
    $payment = PaymentGateways::whereName('Paystack')->whereEnabled(1)->first();

    if ($payment) {

      // initiate the Library's Paystack Object
      $paystack = new Paystack($payment->key_secret);

      //========== Create Plan if no exists
      if ( ! auth()->user()->paystack_plan) {

        $userPlan = $paystack->plan->create([
                'name'=> trans('general.subscription_for').' @'.auth()->user()->username,
                'amount'=> auth()->user()->price*100,
                'interval'=> 'monthly',
                'currency'=> $this->settings->currency_code
              ]);

      $planCode = $userPlan->data->plan_code;

      // Insert Plan Code to User
      User::whereId(auth()->id())->update([
            'paystack_plan' => $planCode
          ]);
      } else {
        if ($this->request->price != auth()->user()->price) {

          $userPlan = $paystack->plan->update([
                  'name'=> trans('general.subscription_for').' @'.auth()->user()->username,
                  'amount'=> $this->request->price*100,
                ],['id' => auth()->user()->paystack_plan]);
        }
      }
    } // payment
  } // end method


   public function uploadCover(Request $request)
   {
     $settings  = AdminSettings::first();

     $validator = Validator::make($this->request->all(), [
       'image' => 'required|mimes:jpg,gif,png,jpe,jpeg|dimensions:min_width=200,min_height=200|max:'.$settings->file_size_allowed.'',
     ]);

      if ($validator->fails()) {
           return response()->json([
               'success' => false,
               'errors' => $validator->getMessageBag()->toArray(),
           ]);
       }

   // PATHS
   $path = config('path.cover');

    //<--- HASFILE PHOTO
     if ($this->request->hasFile('image') )	{

       $photo       = $this->request->file('image');
       $widthHeight = getimagesize($photo);
       $extension   = $photo->getClientOriginalExtension();
       $cover       = strtolower(auth()->user()->username.'-'.auth()->id().time().str_random(10).'.'.$extension );

       //=============== Image Large =================//
       $width     = $widthHeight[0];
       $height    = $widthHeight[1];
       $max_width = $width < $height ? 800 : 1900;

       if ($width > $max_width) {
         $coverScale = $max_width / $width;
       } else {
         $coverScale = 1;
       }

       $scale    = $coverScale;
       $widthCover = ceil($width * $scale);

       $imgCover = Image::make($photo)->orientate()->resize(853, 568, function ($constraint) {
         $constraint->aspectRatio();
         $constraint->aspectRatio();
       })->encode($extension);

       // Copy folder
       Storage::put($path.$cover, $imgCover, 'public');

       if (auth()->user()->cover != $this->settings->cover_default) {
         //<<<-- Delete old image -->>>/
           Storage::delete(config('path.cover').auth()->user()->cover);
       }

       // Update Database
       auth()->user()->update(['cover' => $cover]);

       return response()->json([
               'success' => true,
               'cover' => Helper::getFile($path.$cover),
           ]);

     }//<--- HASFILE PHOTO
   }//<--- End Method Cover

    public function withdrawals()
    {
      $withdrawals = auth()->user()->withdrawals()->orderBy('id','desc')->paginate(20);

      return view('users.withdrawals')->withWithdrawals($withdrawals);
    }

    public function makeWithdrawals()
    {
      if (auth()->user()->payment_gateway != ''
          && Withdrawals::whereUserId(auth()->id())
          ->whereStatus('pending')
          ->count() == 0) {

            switch (auth()->user()->payment_gateway) {
              case 'PayPal':
              $_account = auth()->user()->paypal_account;
              break;

              case 'Payoneer':
              $_account = auth()->user()->payoneer_account;
              break;

              case 'Zelle':
              $_account = auth()->user()->zelle_account;
              break;

              case 'Western Union':
                $_account = auth()->user()->document_id;
                break;

              case 'Bank':
              $_account = auth()->user()->bank;
              break;
            }

         // If custom amount withdrawal
         if ($this->settings->type_withdrawals == 'custom') {

           if ($this->settings->currency_position == 'right') {
     				$currencyPosition =  2;
     			} else {
     				$currencyPosition =  null;
     			}

          $messages = [
    			'amount.min' => trans('general.amount_minimum'.$currencyPosition, ['symbol' => $this->settings->currency_symbol, 'code' => $this->settings->currency_code]),
    			'amount.max' => trans('general.max_amount_minimum'.$currencyPosition, ['symbol' => $this->settings->currency_symbol, 'code' => $this->settings->currency_code]),
    		];

          $maxAmountWitdrawal = $this->settings->amount_max_withdrawal ?: auth()->user()->balance;

           $this->request->validate([
       			'amount' => 'required|numeric|min:'.$this->settings->amount_min_withdrawal.'|max:'.$maxAmountWitdrawal,
       		], $messages);

          $amount = $this->request->amount;

        } else {
          $amount = auth()->user()->balance;
        }

        // Date Estimated Payment
        if ($this->settings->specific_day_payment_withdrawals) {
          $estimatedPayment = Helper::paymentDateOfEachMonth($this->settings->specific_day_payment_withdrawals);
        } else {
          $estimatedPayment = Carbon::parse(now())->addWeekdays($this->settings->days_process_withdrawals);
        }

 			$sql           = new Withdrawals();
 			$sql->user_id  = auth()->id();
 			$sql->amount   = $amount;
 			$sql->gateway  = auth()->user()->payment_gateway;
 			$sql->account  = $_account;
      $sql->estimated_payment = $estimatedPayment;
 			$sql->save();

      // Notify Admin via Email
      try {
        Notification::route('mail' , $this->settings->email_admin)
            ->notify(new AdminWithdrawalPending($sql));
      } catch (\Exception $e) {
        \Log::info($e->getMessage());
      }

      // Remove Balance the User
      auth()->user()->decrement('balance', $amount);

    } else {
      return redirect()->back()
         ->withErrors([
           'errors' => trans('general.withdrawal_pending'),
         ]);
    }

    return redirect('settings/withdrawals');
  } // End Method makeWithdrawals

    public function deleteWithdrawal()
    {
  		$withdrawal = auth()->user()->withdrawals()
      ->whereId($this->request->id)
      ->whereStatus('pending')
      ->firstOrFail();

      // Add Balance the User again
      auth()->user()->increment('balance', $withdrawal->amount);

			$withdrawal->delete();

			return redirect('settings/withdrawals');

    }//<--- End Method

    public function deleteImageCover()
    {
      $path  = 'public/cover/';
      $id    = auth()->id();

      // Image Cover
  		$image = $path.auth()->user()->cover;

      if (\File::exists($image)) {
        \File::delete($image);
      }

      $user = User::find($id);
      $user->cover = '';
      $user->save();

      return response()->json([
              'success' => true,
          ]);
    }// End Method

    public function reportCreator(Request $request)
    {
  		$data = Reports::firstOrNew([
        'user_id' => auth()->id(),
        'report_id' => $request->id,
        'type' => 'user'
      ]);

      $validator = Validator::make($this->request->all(), [
        'reason' => 'required|in:spoofing,copyright,privacy_issue,violent_sexual,spam,fraud,under_age',
      ]);

       if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'text' => __('general.error'),
            ]);
        }

  		if ($data->exists ) {
        return response()->json([
            'success' => false,
            'text' => __('general.already_sent_report'),
        ]);
  		} else {
        $data->reason = $request->reason;
  			$data->save();

        return response()->json([
            'success' => true,
            'text' => __('general.reported_success'),
        ]);
  		}
  	}//<--- End Method

    public function like(Request $request){

  		$like = Like::firstOrNew(['user_id' => auth()->id(), 'updates_id' => $request->id]);

  		$user = Updates::find($request->id);

  		if ($like->exists) {
  			   $notifications = Notifications::where('destination', $user->user_id)
  			   ->where('author', auth()->id())
  			   ->where('target', $request->id)
  			   ->where('type','2')
  			   ->first();

  				// IF ACTIVE DELETE LIKE
  				if ($like->status == '1') {
            $like->status = '0';
  					$like->update();

            	// DELETE NOTIFICATION
  				if (isset($notifications)) {
            $notifications->status = '1';
            $notifications->update();
          }

  				// ELSE ACTIVE AGAIN
  				} else {
  					$like->status = '1';
  					$like->update();
  				}

  		} else {
  			// INSERT
  			$like->save();

  			// Send Notification //destination, author, type, target
  			if ($user->user_id != auth()->id() && $user->user()->notify_liked_post == 'yes') {
  				Notifications::send($user->user_id, auth()->id(), '2', $request->id);
  			}
  		}

      $totalLikes = number_format($user->likes()->count());

      return response()->json([
				'success' => true,
				'total' => trans_choice('general.like_likes', $totalLikes, ['total' => $totalLikes])
			]);

  	}//<---- End Method

    public function ajaxNotifications()
    {
  		 if (request()->ajax()) {

         // Logout user suspended or Pending
         if (auth()->user()->status == 'suspended'
              || auth()->user()->status == 'pending'
              || ! auth()->check())
              {
                auth()->logout();

                return response()->json([
                  'error' => true,
                ]);
              }

  			// Notifications
  			$notifications_count = auth()->user()->notifications()->where('status', '0')->count();
        // Messages
  			$messages_count = auth()->user()->messagesInbox();

  			return response()->json([
          'messages' => $messages_count,
          'notifications' => $notifications_count
        ]);

  		   } else {
  				return response()->json(['error' => 1]);
  			}
     }//<---- * End Method

     public function verifyAccount()
     {
       return view('users.verify_account');
     }//<---- * End Method

     public function verifyAccountSend()
     {
       $checkRequest = VerificationRequests::whereUserId(auth()->id())->whereStatus('pending')->first();

       if ($checkRequest) {
         return redirect()->back()
     				->withErrors([
     					'errors' => trans('admin.pending_request_verify'),
     				]);
       } elseif (auth()->user()->verified_id == 'reject') {
         return redirect()->back()
     				->withErrors([
     					'errors' => trans('admin.rejected_request'),
     				]);
       }

       $input = $this->request->all();
       $input['isUSCitizen'] = auth()->user()->countries_id;

       $messages = [
         "form_w9.required_if" => trans('general.form_w9_required'),
         "image.required" => trans('validation.required', ['attribute' => __('general.verification_image_id')]),
         "image_reverse.required" => trans('validation.required', ['attribute' => __('general.verification_image_reverse_id')]),
         "image_selfie.required" => trans('validation.required', ['attribute' => __('general.verification_image_selfie')]),
       ];

       $zipVerificationCreator = $this->settings->zip_verification_creator ? true : false;

      $validator = Validator::make($input, [
        'address'  => '',
        'city' => '',
        'zip' => Rule::requiredIf($zipVerificationCreator),
        'image' => 'required|mimes:jpg,gif,png,jpe,jpeg,zip|max:'.$this->settings->file_size_allowed_verify_account.'',
        'image_reverse' => 'required|mimes:jpg,gif,png,jpe,jpeg,zip|max:'.$this->settings->file_size_allowed_verify_account.'',
        'image_selfie' => 'required|mimes:jpg,gif,png,jpe,jpeg,zip|max:'.$this->settings->file_size_allowed_verify_account.'',
        'form_w9'  => 'required_if:isUSCitizen,==,1|mimes:pdf|max:'.$this->settings->file_size_allowed_verify_account.'',
     ], $messages);

      if ($validator->fails()) {
          return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
      }

      // PATHS
  		$path = config('path.verification');

      // Image ID (Front)
      if ($this->request->hasFile('image')) {
        $extension = $this->request->file('image')->getClientOriginalExtension();
        $fileImage = strtolower(auth()->id().time().Str::random(40).'.'.$extension);
        $this->request->file('image')->storePubliclyAs($path, $fileImage);
	   }//<====== End HasFile

     // Image ID (Reverse)
     if ($this->request->hasFile('image_reverse')) {
       $extension = $this->request->file('image_reverse')->getClientOriginalExtension();
       $fileImageReverse = 'reverse-'.strtolower(auth()->id().time().Str::random(40).'.'.$extension);
       $this->request->file('image_reverse')->storePubliclyAs($path, $fileImageReverse);
    }//<====== End HasFile

    // Image ID (Selfie)
    if ($this->request->hasFile('image_selfie')) {
      $extension = $this->request->file('image_selfie')->getClientOriginalExtension();
      $fileImageSelfie = 'selfie-'.strtolower(auth()->id().time().Str::random(40).'.'.$extension);
      $this->request->file('image_selfie')->storePubliclyAs($path, $fileImageSelfie);
   }//<====== End HasFile

     // Form W9 US citizen
     if ($this->request->hasFile('form_w9')) {
       $extension = $this->request->file('form_w9')->getClientOriginalExtension();
       $fileFormW9 = strtolower(auth()->id().time().Str::random(40).'.'.$extension);
       $this->request->file('form_w9')->storePubliclyAs($path, $fileFormW9);
    }//<====== End HasFile

      $sql          = new VerificationRequests();
 			$sql->user_id = auth()->id();
 			$sql->address = $input['address'];
 			$sql->city    = $input['city'];
      $sql->zip     = $input['zip'] ?? '';
      $sql->image   = $fileImage;
      $sql->image_reverse = $fileImageReverse;
      $sql->image_selfie  = $fileImageSelfie;
      $sql->form_w9 = $fileFormW9 ?? '';
 			$sql->save();

      // Save data user
      User::whereId(auth()->id())->update([
        'address' => $this->request->address,
        'city' => $this->request->city,
        'zip' => $this->request->zip
      ]);

      // Notify Admin via Email
      try {
        Notification::route('mail' , $this->settings->email_admin)
            ->notify(new AdminVerificationPending($sql));
      } catch (\Exception $e) {
        \Log::info($e->getMessage());
      }

      return redirect('settings/verify/account')->withStatus(__('general.send_success_verification'));
     }

     public function invoice($id)
     {
       $data = Transactions::whereId($id)->whereApproved('1')->firstOrFail();

        if ($data->user_id != auth()->id() && ! auth()->user()->isSuperAdmin()) {
          abort(404);
        }
          
      $taxes = TaxRates::whereIn('id', collect(explode('_', $data->taxes)))->get();
      $total = $data->amount + ($data->amount * $taxes->sum('percentage') / 100);
      $creator = isset($data->subscribed()->username) ? ' @'.$data->subscribed()->username : null;

   		return view('users.invoice')->with([
          'data' =>$data,
          'taxes' => $taxes,
          'total' => $total,
          'creator' => $creator
        ]);
     }

     public function formAddUpdatePaymentCard()
     {
       $payment = PaymentGateways::whereName('Stripe')->whereEnabled(1)->firstOrFail();
       \Stripe\Stripe::setApiKey($payment->key_secret);

       return view('users.add_payment_card', [
         'intent' => auth()->user()->createSetupIntent(),
         'key' => $payment->key
       ]);
     }// End Method

     public function addUpdatePaymentCard()
     {
       $payment = PaymentGateways::whereName('Stripe')->whereEnabled(1)->firstOrFail();
       \Stripe\Stripe::setApiKey($payment->key_secret);

       if (! $this->request->payment_method) {
         return response()->json([
           "success" => false
         ]);
       }

       if (! auth()->user()->hasPaymentMethod()) {
           auth()->user()->createOrGetStripeCustomer();
       }

       try {
         auth()->user()->deletePaymentMethods();
       } catch (\Exception $e) {
         // error
       }

       auth()->user()->updateDefaultPaymentMethod($this->request->payment_method);
       auth()->user()->save();

       return response()->json([
         "success" => true
       ]);
     }// End Method

     public function cancelSubscription($id)
     {
       $checkSubscription = auth()->user()->userSubscriptions()->whereStripeId($id)->firstOrFail();
       $creator = User::wherePlan($checkSubscription->stripe_price)->first();
       $payment = PaymentGateways::whereName('Stripe')->whereEnabled(1)->firstOrFail();

       $stripe = new \Stripe\StripeClient($payment->key_secret);

       try {
         $response = $stripe->subscriptions->cancel($id, []);
       } catch (\Exception $e) {
         return back()->withErrorMessage($e->getMessage());
       }

       sleep(2);

       $checkSubscription->ends_at = date('Y-m-d H:i:s', $response->current_period_end);
       $checkSubscription->save();

       session()->put('subscription_cancel', trans('general.subscription_cancel'));
       return redirect($creator->username);

     }// End Method

     // Delete Account
     public function deleteAccount()
     {
       if (auth()->user()->isSuperAdmin()) {
        return redirect('privacy/security');
       }

       if (! \Hash::check($this->request->password, auth()->user()->password) ) {
  		    return back()->with(['incorrect_pass' => trans('general.password_incorrect')]);
  		}

       $this->deleteUser(auth()->id());

       return redirect('/');
     }

     // My Bookmarks
     public function myBookmarks()
     {
       $bookmarks = auth()->user()->bookmarks()->orderBy('bookmarks.id','desc')->paginate($this->settings->number_posts_show);

       $users = $this->userExplore();

       return view('users.bookmarks', ['updates' => $bookmarks, 'users' => $users]);
     }

     // Download File
     public function downloadFile($id)
   	{
      $post = Updates::findOrFail($id);
      $checkUserSubscription = auth()->user()->checkSubscription($post->user());

      if ($post->locked == 'yes') {
          if (! $checkUserSubscription
            && ! auth()->user()->payPerView()->whereUpdatesId($post->id)->first()
            && $post->user()->id != auth()->id()
            || $checkUserSubscription
            && $post->price != 0.00
            && $checkUserSubscription->free == 'yes'
            && ! auth()->user()->payPerView()->whereUpdatesId($post->id)->first()
          ) {
          abort(404);
        }
      }

      $media = Media::whereUpdatesId($post->id)->where('file', '<>', '')->firstOrFail();

      $pathFile = config('path.files').$media->file;
      $headers = [
				'Content-Type:' => 'application/x-zip-compressed',
				'Cache-Control' => 'no-cache, no-store, must-revalidate',
				'Pragma' => 'no-cache',
				'Expires' => '0'
			];

      return Storage::download($pathFile, $media->file_name.' '.__('general.by').' @'.$post->user()->username.'.zip', $headers);

    }

    public function myCards()
    {
      $payment = PaymentGateways::whereName('Stripe')->whereEnabled(1)->first();
      $paystackPayment = PaymentGateways::whereName('Paystack')->whereEnabled(1)->first();

      if (! $payment && ! $paystackPayment) {
        abort(404);
      }

      if (auth()->user()->stripe_id != '' && auth()->user()->pm_type != '' && isset($payment->key_secret)) {
        $stripe = new \Stripe\StripeClient($payment->key_secret);

        $response = $stripe->paymentMethods->all([
          'customer' => auth()->user()->stripe_id,
          'type' => 'card',
        ]);

        $expiration = $response->data[0]->card->exp_month.'/'.$response->data[0]->card->exp_year;
      }

      $chargeAmountPaystack = ['NGN' => '50.00', 'GHS' => '0.10', 'ZAR' => '1', 'USD' => 0.20];

      if (array_key_exists($this->settings->currency_code, $chargeAmountPaystack)) {
          $chargeAmountPaystack = $chargeAmountPaystack[$this->settings->currency_code];
      } else {
          $chargeAmountPaystack = 0;
      }

      return view('users.my_cards',[
        'key_secret' => $payment->key_secret ?? null,
        'expiration' => $expiration ?? null,
        'paystackPayment' => $paystackPayment,
        'chargeAmountPaystack' => $chargeAmountPaystack
      ]);
    }

    // Privacy Security
    public function privacySecurity()
    {
      $agent = new Agent();

      // IP
      $ip = request()->ip();
      // Device
      $device  = $agent->device();
      // Device type
      $deviceType  = $agent->isPhone() ? 'phone' : 'desktop';
      // Browser
      $browser = $agent->browser();
      $browser = $browser . ' '.$agent->version($browser);

      $currentSession = LoginSessions::whereUserId(auth()->id())
      ->where('ip', '=', $ip)
      ->where('device', '=', $device)
      ->where('device_type', '=', $deviceType)
      ->where('browser', '=', $browser)
      ->first();

      $agents = LoginSessions::where('id', '<>', $currentSession->id ?? 0)->whereUserId(auth()->id())->latest()->take(5)->get();

      return view('users.privacy_security', [
        'agents' => $agents,
        'currentSession' => $currentSession
      ]);
    }

    public function savePrivacySecurity()
    {
      $user = User::find(auth()->id());
      $user->hide_profile = $this->request->hide_profile ?? 'no';
      $user->hide_last_seen = $this->request->hide_last_seen ?? 'no';
      $user->hide_count_subscribers = $this->request->hide_count_subscribers ?? 'no';
      $user->hide_my_country = $this->request->hide_my_country ?? 'no';
      $user->show_my_birthdate = $this->request->show_my_birthdate ?? 'no';
      $user->active_status_online = $this->request->active_status_online ?? 'no';
      $user->two_factor_auth = $this->request->two_factor_auth ?? 'no';
      $user->posts_privacy = $this->request->posts_privacy;
      $user->save();

			return redirect('privacy/security')->withStatus(trans('admin.success_update'));
    }

    // Logout a session based on session id.
    public function logoutSession($id)
    {
        \DB::table('sessions')
            ->where('id', $id)->delete();

        return redirect('privacy/security');
    }

    public function deletePaymentCard()
    {
      $paymentMethod = auth()->user()->defaultPaymentMethod();

      $paymentMethod->delete();

      return redirect('my/cards')->withSuccessRemoved(__('general.successfully_removed'));
    }

    public function invoiceDeposits($id)
    {
      $data = Deposits::whereId($id)->whereStatus('active')->first();

      if ($data->user_id != auth()->id() && ! auth()->user()->isSuperAdmin()) {
        abort(404);
      }

      $taxes = TaxRates::whereIn('id', collect(explode('_', $data->taxes)))->get();
      $totalTaxes = ($data->amount * $taxes->sum('percentage') / 100);

      $totalAmount = ($data->amount + $data->transaction_fee + $totalTaxes);

     return view('users.invoice-deposits', [
       'data' => $data,
       'amount' => $data->amount,
       'percentageApplied' => $data->percentage_applied,
       'transactionFee' => $data->transaction_fee,
       'totalAmount' => $totalAmount,
       'taxes' => $taxes
     ]);
    }

    // My Purchases
    public function myPurchases()
    {
      $purchases = auth()->user()->payPerView()->orderBy('pay_per_views.id','desc')->paginate($this->settings->number_posts_show);

      $users = $this->userExplore();

      return view('users.my-purchases', [
        'updates' => $purchases,
        'users' => $users
        ]);
    }

    // My Purchases Ajax Pagination
    public function ajaxMyPurchases()
    {
      $skip = $this->request->input('skip');
      $total = $this->request->input('total');

      $data = auth()->user()->payPerView()->orderBy('pay_per_views.id','desc')->skip($skip)->take($this->settings->number_posts_show)->get();
      $counterPosts = ($total - $this->settings->number_posts_show - $skip);

      return view('includes.updates',
          ['updates' => $data,
          'ajaxRequest' => true,
          'counterPosts' => $counterPosts,
          'total' => $total
          ])->render();

    }//<--- End Method

    public function myPosts()
    {
      if (auth()->user()->verified_id != 'yes') {
        abort(404);
      }

      $posts = Updates::whereUserId(auth()->id())
      ->where('status', '<>', 'encode')
      ->orderBy('id', 'desc')
      ->paginate(20);

      if ($posts->currentPage() > $posts->lastPage()) {
        abort(404);
      }

      return view('users.my_posts')->withPosts($posts);

    }//<--- End Method

    public function blockCountries()
    {
      if (auth()->user()->verified_id != 'yes') {
        abort(404);
      }

      return view('users.block_countries');
    }//<--- End Method

    public function blockCountriesStore()
    {
      $blockedCountries = $this->request->countries ? implode( ',', $this->request->countries) : '';

      User::whereId(auth()->id())->update([
        'blocked_countries' => $blockedCountries
      ]);

      return back()->withStatus(trans('auth.success_update'));

    }//<--- End Method

    public function myReferrals()
    {
      $transactions = ReferralTransactions::whereReferredBy(auth()->id())
      ->orderBy('id', 'desc')
      ->paginate(20);

       return view('users.referrals', ['transactions' => $transactions]);

    }//<--- End Method

    public function mySales()
    {
      if (auth()->user()->verified_id != 'yes') {
        abort(404);
      }

      $sales = auth()->user()->sales();

      // Sort by oldest
      $sales->when(request('sort') == 'oldest', function($q) {
        $q->orderBy('id', 'asc');
      });

      // Sort by pending
      $sales->when(request('sort') == 'pending', function($q) {
        $q->where('delivery_status', 'pending');
      });

      $sales = $sales->orderBy('id', 'desc')->paginate(10);

      return view('users.my-sales')->withSales($sales);
    }

    public function myProducts()
    {
      if (auth()->user()->verified_id != 'yes') {
        abort(404);
      }

      $products = auth()->user()->products()
      ->with('purchases')
      ->orderBy('id', 'desc')->paginate(20);

      if ($products->currentPage() > $products->lastPage()) {
        abort(404);
      }

      return view('users.my_products')->withProducts($products);

    }//<--- End Method

    public function purchasedItems()
    {
      $purchases = auth()->user()->purchasedItems()->orderBy('id', 'desc')->paginate(10);

      return view('users.purchased_items')->withPurchases($purchases);
    }

    public function mentions()
    {
      $users = User::whereStatus('active')
          ->where('username', 'LIKE', '%'.$this->request->filter.'%')
          ->orderBy('verified_id', 'asc')
          ->take(5)
          ->get();

          foreach ($users as $user) {

            $verified = $user->verified_id == 'yes' ? ' <i class="bi bi-patch-check-fill verified"></i>' : null;

            $data[] = [
        				'name' => $user->hide_name == 'yes' ? $user->username.$verified : $user->name.$verified,
        				'username' => $user->username,
        				"avatar" => Helper::getFile(config('path.avatar').$user->avatar)
        		];
          }

      return response()->json([
        'tags' => $data ?? null
      ], 200);
    }

    public function restrictUser($id)
    {
      $verifyUser = User::findOrFail($id);

      // Avoid self restricting
      if ($verifyUser->id == auth()->id()) {
        abort(500);
      }

      // Avoid Admin Restriction
      if ($verifyUser->isSuperAdmin()) {
        return response()->json([
            'success' => true
        ]);
      }

      $restrict = Restrictions::firstOrNew(['user_id' => auth()->id(), 'user_restricted' => $id]);

      if ($restrict->exists) {
        $restrict->delete();
      } else {
        $restrict->save();
      }

      return response()->json([
          'success' => true
      ]);
    }// End method restrictUser

    public function restrictions()
    {
      $restrictions = auth()->user()->restrictions()->orderBy('id', 'desc')->paginate(15);

      return view('users.restricted_users')->withRestrictions($restrictions);

    }// End method restrictions

    // My Likes
    public function myLikes()
    {
      $likes = auth()->user()->myLikes()->orderBy('likes.id','desc')->paginate($this->settings->number_posts_show);

      $users = $this->userExplore();

      return view('users.likes', ['updates' => $likes, 'users' => $users]);
    }// End method myLikes

    // Likes Ajax Pagination
    public function ajaxMyLikes()
    {
      $skip = $this->request->input('skip');
      $total = $this->request->input('total');

      $data = auth()->user()->myLikes()->orderBy('likes.id','desc')->skip($skip)->take($this->settings->number_posts_show)->get();
      $counterPosts = ($total - $this->settings->number_posts_show - $skip);

      return view('includes.updates',
          ['updates' => $data,
          'ajaxRequest' => true,
          'counterPosts' => $counterPosts,
          'total' => $total
          ])->render();

    }//<--- End Method

    /**
	 * Get data Earnings Dashboard Creator
	 *
	 * @return Response
	 */
  public function getDataChart()
  {
    if (! $this->request->expectsJson()) {
      abort(401);
    }

    switch ($this->request->range) {
      case 'month':
        $month = date('m');
        $year  = date('Y');
        $daysMonth = Helper::daysInMonth($month, $year);
        $dateFormat = "$year-$month-";

        $monthFormat  = __("months.$month");
        $currencySymbol = $this->settings->currency_symbol;

        for ($i=1; $i <= $daysMonth; ++$i) {
          $date = date('Y-m-d', strtotime($dateFormat.$i));
          $payments = auth()->user()->myPaymentsReceived()->whereDate('created_at', '=', $date)->sum('earning_net_user');
          
          $monthsData[] =  "$monthFormat $i";
          $earningNetUser = $payments;
          $earningNetUserSum[] = $earningNetUser;
        }

        $label = $monthsData;
        $data = $earningNetUserSum;

        break;

        case 'last-month':
          $month = date('m', strtotime('-1 month'));
          $year  = date('Y');
          $daysMonth = Helper::daysInMonth($month, $year);
          $dateFormat = "$year-$month-";

          $monthFormat  = __("months.$month");
          $currencySymbol = $this->settings->currency_symbol;

          for ($i=1; $i <= $daysMonth; ++$i) {
            $date = date('Y-m-d', strtotime($dateFormat.$i));
            $payments = auth()->user()->myPaymentsReceived()->whereDate('created_at', '=', $date)->sum('earning_net_user');
            
            $monthsData[] =  "$monthFormat $i";
            $earningNetUser = $payments;
            $earningNetUserSum[] = $earningNetUser;
          }
          
          $label = $monthsData;
          $data = $earningNetUserSum;

          break;

          case 'year':
            $year  = date('Y');
            $dateFormat = "$year-";
            $currencySymbol = $this->settings->currency_symbol;

            for ($i=1; $i <= 12; ++$i) {
              $month = str_pad($i, 2, "0", STR_PAD_LEFT);
              $date = date('Y-m', strtotime($dateFormat.$month));
              $payments = auth()->user()->myPaymentsReceived()->where('created_at', 'LIKE', '%'.$date.'%')->sum('earning_net_user');
              
              $monthsData[] =  __("months.$month");
              $earningNetUser = $payments;
              $earningNetUserSum[] = $earningNetUser;
            }

            $label = $monthsData;
            $data = $earningNetUserSum;
            break;
      
      default:

      return response()->json([
        'success' => false
      ], 401);

        break;
    }

    return response()->json([
      'success' => true,
      'labels'  => $label,
      'datasets' => $data
		]);
  }

  public function logoutOtherDevices()
  {
    $validated = $this->request->validate([
      'password' => 'required'
    ]);

    Auth::logoutOtherDevices($this->request->password);

    // Delete Login Sessions of others devices
    $agent = new Agent();

		// IP
		$ip = request()->ip();
		// Device
		$device  = $agent->device();
    // Device type
		$deviceType  = $agent->isPhone() ? 'phone' : 'desktop';
		// Browser
		$browser = $agent->browser();
		$browser = $browser . ' '.$agent->version($browser);

    $sessions = LoginSessions::whereUserId(auth()->id())
    ->where('ip', '=', $ip)
    ->where('device', '=', $device)
    ->where('device_type', '=', $deviceType)
    ->where('browser', '=', $browser)
    ->get();

    LoginSessions::whereUserId(auth()->id())
      ->where('id', '<>', $sessions[0]->id)
      ->delete();

    return back();
  }

  public function myStories()
  {
    if (auth()->user()->verified_id != 'yes') {
      abort(404);
    }

    $stories = Stories::whereUserId(auth()->id())->latest()->paginate(30);

    return view('users.my-stories',['stories' => $stories]);

  }

}
