<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AdminSettings;
use App\Models\Categories;
use App\Models\Updates;
use App\Models\LiveStreamings;
use App\Models\Bookmarks;
use App\Models\Comments;
use App\Models\Stories;
use App\Models\StoryFonts;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Helper;
use Image;
use Cache;
use Mail;
use App\Models\Countries;

class HomeController extends Controller
{
  use Traits\Functions;

  public function __construct(Request $request, AdminSettings $settings)
  {
    $this->request = $request;
    try {
      // Check Datebase access
      $this->settings = $settings::first();

    } catch (\Exception $e) {
      // Empty
    }
  }

    /**
     * Homepage Section.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {      
      try {
        // Check Datebase access
         $this->settings;
      } catch (\Exception $e) {
        // Redirect to Installer
        return redirect('install/script');
      }
      
        // Home Guest
        if (auth()->guest()) {
          $users = User::where('featured','yes')
            ->where('status','active')
              ->whereVerifiedId('yes')
              ->whereHideProfile('no')
              ->whereRelation('plans', 'status', '1')
              ->where('id', '<>', $this->settings->hide_admin_profile == 'on' ? 1 : 0)
              ->where('blocked_countries', 'NOT LIKE', '%'.Helper::userCountry().'%')
            ->orWhere('featured','yes')
              ->where('status','active')
                ->whereVerifiedId('yes')
                ->whereHideProfile('no')
                ->whereFreeSubscription('yes')
                ->where('id', '<>', $this->settings->hide_admin_profile == 'on' ? 1 : 0)
                ->where('blocked_countries', 'NOT LIKE', '%'.Helper::userCountry().'%')
                ->inRandomOrder()
              ->paginate(6);

            // Total creators
            $usersTotal = User::whereStatus('active')
            ->whereVerifiedId('yes')
            ->whereRelation('plans', 'status', '1')
            ->whereHideProfile('no')
              ->orWhere('status', 'active')
            ->whereVerifiedId('yes')
            ->whereFreeSubscription('yes')
            ->whereHideProfile('no')
            ->count();

            $home = $this->settings->home_style == 0 ? 'home' : 'home-login';

          return view('index.'.$home, [
              'users' => $users,
              'usersTotal' => $usersTotal
            ]);

        } else {

          $users = $this->userExplore();

          $updates = Updates::leftjoin('users', 'updates.user_id', '=', 'users.id')
              ->leftjoin('plans', 'plans.user_id', '=', 'users.id')
              ->leftjoin('subscriptions', 'subscriptions.stripe_price', '=', 'plans.name')

    			    ->where('subscriptions.user_id', '=', auth()->user()->id)
              ->where('subscriptions.stripe_id', '=', '')
              ->where('ends_at', '>=', now())
              ->where('updates.status', 'active')

              ->orWhere('subscriptions.stripe_id', '<>', '')
              ->where('subscriptions.user_id', '=', auth()->user()->id)
              ->where('stripe_status', 'active')
              ->where('updates.status', 'active')

              ->orWhere('subscriptions.stripe_id', '<>', '')
              ->where('subscriptions.user_id', '=', auth()->user()->id )
              ->where('ends_at', '>=', now())
              ->where('stripe_status', 'canceled')
              ->where('updates.status', 'active')

              ->orWhere('subscriptions.user_id', '=', auth()->user()->id)
              ->where('subscriptions.stripe_id', '=', '')
              ->whereFree('yes')
              ->where('updates.status', 'active')

              ->orWhere('updates.user_id', auth()->user()->id)
              ->where('updates.status', 'active')
        			->groupBy('updates.id')
        			->orderBy('updates.id', 'desc')
        			->select('updates.*')
        			->paginate($this->settings->number_posts_show);

            $stories = Stories::leftjoin('users', 'stories.user_id', '=', 'users.id')
              ->leftjoin('plans', 'plans.user_id', '=', 'users.id')
              ->leftjoin('subscriptions', 'subscriptions.stripe_price', '=', 'plans.name')

    			    ->where('subscriptions.user_id', '=', auth()->user()->id)
              ->where('subscriptions.stripe_id', '=', '')
              ->where('ends_at', '>=', now())
              ->where('stories.status', 'active')
              ->where('stories.created_at', '>', date('Y-m-d H:i:s', strtotime('- 1 day')))

              ->orWhere('subscriptions.stripe_id', '<>', '')
              ->where('subscriptions.user_id', '=', auth()->user()->id)
              ->where('stripe_status', 'active')
              ->where('stories.status', 'active')
              ->where('stories.created_at', '>', date('Y-m-d H:i:s', strtotime('- 1 day')))

              ->orWhere('subscriptions.stripe_id', '<>', '')
              ->where('subscriptions.user_id', '=', auth()->user()->id )
              ->where('ends_at', '>=', now())
              ->where('stripe_status', 'canceled')
              ->where('stories.status', 'active')
              ->where('stories.created_at', '>', date('Y-m-d H:i:s', strtotime('- 1 day')))

              ->orWhere('subscriptions.user_id', '=', auth()->user()->id)
              ->where('subscriptions.stripe_id', '=', '')
              ->whereFree('yes')
              ->where('stories.status', 'active')
              ->where('stories.created_at', '>', date('Y-m-d H:i:s', strtotime('- 1 day')))

              ->orWhere('stories.user_id', auth()->user()->id)
              ->where('stories.status', 'active')
              ->where('stories.created_at', '>', date('Y-m-d H:i:s', strtotime('- 1 day')))
        			->groupBy('stories.id')
        			->orderBy('stories.id', 'desc')
        			->select('stories.*')
        			->get();

          $storyFonts = StoryFonts::all();
          if ($storyFonts->count()) {
            foreach ($storyFonts as $font) {
                $fonts[] = str_replace('+', ' ', $font->name);
              }
              $fonts = implode("|", $fonts);
            }

          return view('index.home-session', [
            'users' => $users, 
            'updates' => $updates,
            'stories' => $stories,
            'fonts' => $fonts ?? null
          ]);

        }
    }

    public function ajaxUserUpdates()
    {
      $skip = $this->request->input('skip');
      $total = $this->request->input('total');

      $data = Updates::leftjoin('users', 'updates.user_id', '=', 'users.id')
          ->leftjoin('plans', 'plans.user_id', '=', 'users.id')
          ->leftjoin('subscriptions', 'subscriptions.stripe_price', '=', 'plans.name')

          ->where('subscriptions.user_id', '=', auth()->user()->id )
          ->where('subscriptions.stripe_id', '=', '')
          ->where('ends_at', '>=', now())

          ->orWhere('subscriptions.stripe_id', '<>', '')
          ->where('subscriptions.user_id', '=', auth()->user()->id )
          ->where('stripe_status', 'active')

          ->orWhere('subscriptions.stripe_id', '<>', '')
          ->where('subscriptions.user_id', '=', auth()->user()->id )
          ->where('ends_at', '>=', now())
          ->where('stripe_status', 'canceled')

          ->orWhere('subscriptions.user_id', '=', auth()->user()->id)
          ->where('subscriptions.stripe_id', '=', '')
          ->whereFree('yes')

          ->orWhere('updates.user_id', auth()->user()->id)
          ->skip($skip)
          ->take($this->settings->number_posts_show)
          ->groupBy('updates.id')
          ->orderBy( 'updates.id', 'desc' )
          ->select('updates.*')
          ->get();

      $counterPosts = ($total - $this->settings->number_posts_show - $skip);

      return view('includes.updates', ['updates' => $data, 'ajaxRequest' => true, 'counterPosts' => $counterPosts, 'total' => $total])->render();
    }//<--- End Method

    public function getVerifyAccount($confirmation_code)
    {
  		if (auth()->guest()
          || auth()->check()
          && auth()->user()->confirmation_code == $confirmation_code
          && auth()->user()->status == 'pending'
          ) {
  		$user = User::where( 'confirmation_code', $confirmation_code )->where('status','pending')->first();

  		if ($user) {

  			$update = User::where( 'confirmation_code', $confirmation_code )
  			->where('status','pending')
  			->update(['status' => 'active', 'confirmation_code' => '']);

        if ($this->settings->autofollow_admin) {
          // Auto-follow Admin
          $this->autoFollowAdmin($user->id);
        }

  			auth()->loginUsingId($user->id);

        // Insert Login Session
        $this->loginSession($user->id);

        $redirect = $this->request->input('r') ?: '/';

  			 return redirect($redirect)
  					->with([
  						'success_verify' => true,
  					]);
  			} else {
  			return redirect('/')
  					->with([
  						'error_verify' => true,
  					]);
  			}
  		}
      else {
  			 return redirect('/');
  		}
  	}// End Method

    public function creators($type = false)
    {
      $query = trim($this->request->input('q'));

      switch ($type) {
        case 'featured':
        $orderBy = 'featured_date';
        $title = trans('general.featured_creators');
          break;

        case 'more-active':
        $orderBy = 'COUNT(updates.id)';
        $title = trans('general.more_active_creators');
          break;

        case 'new':
        $orderBy = 'id';
        $title = trans('general.new_creators');
          break;

      case 'free':
        $orderBy = 'free_subscription';
        $title = trans('general.creators_with_free_subscription');
          break;

        default:
        $orderBy = 'COUNT(subscriptions.id)';
        $title = trans('general.explore_our_creators');
          break;
      }

      // Search Creator
      if (strlen($query) >= 3) {

        $title = trans('general.search').' "'.$query.'"';

        $users = User::where('users.status','active')
              ->where('username','LIKE', '%'.$query.'%')
                ->whereVerifiedId('yes')
                ->where('id', '<>', $this->settings->hide_admin_profile == 'on' ? 1 : 0)
                ->whereRelation('plans', 'status', '1')
                ->whereFreeSubscription('no')
                ->whereHideProfile('no')
                ->where('blocked_countries', 'NOT LIKE', '%'.Helper::userCountry().'%')
                ->orWhere('users.status','active')
                  ->whereVerifiedId('yes')
                  ->where('id', '<>', $this->settings->hide_admin_profile == 'on' ? 1 : 0)
                  ->whereFreeSubscription('yes')
                  ->whereHideProfile('no')
                  ->where('name','LIKE', '%'.$query.'%')
                  ->whereHideName('no')
                  ->where('blocked_countries', 'NOT LIKE', '%'.Helper::userCountry().'%')
                ->orderBy('featured_date','desc')
                ->paginate(12);

      } else {

        if ($type == 'free') {
          $users = User::where('users.status','active')
                  ->whereVerifiedId('yes')
                  ->where('id', '<>', $this->settings->hide_admin_profile == 'on' ? 1 : 0)
                  ->whereFreeSubscription('yes')
                  ->whereHideProfile('no')
                  ->where('blocked_countries', 'NOT LIKE', '%'.Helper::userCountry().'%');

                  $this->filterByGenderAge($users);

                  $users = $users->orderBy(\DB::raw($orderBy),'desc')
                  ->paginate(12);
        } else {

          $data = User::where('users.status','active');

          $whereRawFeatured = $type == 'featured' ? 'featured = "yes"' : 'users.status = "active"';

          $data->where('users.status','active')
                  ->whereVerifiedId('yes')
                  ->where('users.id', '<>', $this->settings->hide_admin_profile == 'on' ? 1 : 0)
                  ->whereRelation('plans', 'status', '1')
                  ->whereFreeSubscription('no')
                  ->whereHideProfile('no')
                  ->whereRaw($whereRawFeatured)
                  ->where('blocked_countries', 'NOT LIKE', '%'.Helper::userCountry().'%');

                  $this->filterByGenderAge($data);

                $data->orWhere('users.status','active')
                  ->whereVerifiedId('yes')
                  ->where('users.id', '<>', $this->settings->hide_admin_profile == 'on' ? 1 : 0)
                  ->whereFreeSubscription('yes')
                  ->whereHideProfile('no')
                  ->where('blocked_countries', 'NOT LIKE', '%'.Helper::userCountry().'%')
                  ->whereRaw($whereRawFeatured);

                  $this->filterByGenderAge($data);

          if ($type == 'more-active') {
            $data->leftjoin('updates', 'updates.user_id', '=', 'users.id');
          }

          if (! $type) {
            $data->leftjoin('plans', 'plans.user_id', '=', 'users.id')
            ->leftjoin('subscriptions', 'subscriptions.stripe_price', '=', 'plans.name');

            $data->orWhere('subscriptions.stripe_id', '=', '')
            ->where('ends_at', '>=', now())
            ->where('users.status','active')
            ->whereHideProfile('no')
            ->where('users.id', '<>', $this->settings->hide_admin_profile == 'on' ? 1 : 0)
            ->where('blocked_countries', 'NOT LIKE', '%'.Helper::userCountry().'%');

            $this->filterByGenderAge($data);

            $data->orWhere('subscriptions.stripe_id', '<>', '')
            ->where('stripe_status', 'active')
            ->where('users.status','active')
            ->whereHideProfile('no')
            ->where('users.id', '<>', $this->settings->hide_admin_profile == 'on' ? 1 : 0)
            ->where('blocked_countries', 'NOT LIKE', '%'.Helper::userCountry().'%');

            $this->filterByGenderAge($data);

            $data->orWhere('subscriptions.stripe_id', '=', '')
            ->whereFree('yes')
            ->where('users.status','active')
            ->whereHideProfile('no')
            ->where('users.id', '<>', $this->settings->hide_admin_profile == 'on' ? 1 : 0)
            ->where('blocked_countries', 'NOT LIKE', '%'.Helper::userCountry().'%');

            $this->filterByGenderAge($data);
          }
          $users = $data->groupBy('users.id')
              ->orderBy(\DB::raw($orderBy), 'DESC')
              ->orderBy('users.id', 'ASC')
              ->select('users.*')
              ->paginate(12);
        }
      }

        if ($this->request->input('page') > $users->lastPage()) {
    			abort('404');
    		}
        return view('index.creators', [
          'users' => $users,
          'title' => $title
        ]);
    }

    public function category($slug, $type = false)
    {
      $category = Categories::where('slug', '=', $slug)->where('mode','on')->firstOrFail();
      $title    = \Lang::has('categories.' . $category->slug) ? __('categories.' . $category->slug) : $category->name;

      switch ($type) {
        case 'featured':
        $orderBy = 'featured_date';
        $title = $title.' - '.trans('general.featured_creators');
          break;

        case 'more-active':
        $orderBy = 'COUNT(updates.id)';
        $title = $title.' - '.trans('general.more_active_creators');
          break;

        case 'new':
        $orderBy = 'id';
        $title = $title.' - '.trans('general.new_creators');
          break;

      case 'free':
        $orderBy = 'free_subscription';
        $title = $title.' - '.trans('general.creators_with_free_subscription');
          break;

        default:
        $orderBy = 'COUNT(subscriptions.id)';
          break;
      }

      if ($type == 'free') {
        $users = User::where('users.status','active')
            ->where('categories_id', 'LIKE', '%'.$category->id.'%')
              ->whereVerifiedId('yes')
              ->where('id', '<>', $this->settings->hide_admin_profile == 'on' ? 1 : 0)
              ->whereFreeSubscription('yes')
              ->whereHideProfile('no')
              ->where('blocked_countries', 'NOT LIKE', '%'.Helper::userCountry().'%');

              $this->filterByGenderAge($users);

            $users = $users->orderBy($orderBy, 'desc')
            ->paginate(12);
      } else {

        $data = User::where('users.status','active');

        $whereRawFeatured = $type == 'featured' ? 'featured = "yes"' : 'users.status = "active"';

          $data->where('users.status','active')
              ->where('categories_id', 'LIKE', '%'.$category->id.'%')
              ->whereVerifiedId('yes')
              ->where('users.id', '<>', $this->settings->hide_admin_profile == 'on' ? 1 : 0)
              ->whereRelation('plans', 'status', '1')
              ->whereFreeSubscription('no')
              ->whereHideProfile('no')
              ->whereRaw($whereRawFeatured)
              ->where('blocked_countries', 'NOT LIKE', '%'.Helper::userCountry().'%');

              $this->filterByGenderAge($data);

            $data->orWhere('users.status','active')
              ->where('categories_id', 'LIKE', '%'.$category->id.'%')
              ->whereVerifiedId('yes')
              ->where('users.id', '<>', $this->settings->hide_admin_profile == 'on' ? 1 : 0)
              ->whereFreeSubscription('yes')
              ->whereHideProfile('no')
              ->where('blocked_countries', 'NOT LIKE', '%'.Helper::userCountry().'%')
              ->whereRaw($whereRawFeatured);

              $this->filterByGenderAge($data);

              if ($type == 'more-active') {
                $data->leftjoin('updates', 'updates.user_id', '=', 'users.id');
              }

              if (! $type) {
                $data->leftjoin('plans', 'plans.user_id', '=', 'users.id')
                ->leftjoin('subscriptions', 'subscriptions.stripe_price', '=', 'plans.name');

                $data->orWhere('subscriptions.stripe_id', '=', '')
                ->where('ends_at', '>=', now())
                ->where('categories_id', 'LIKE', '%'.$category->id.'%')
                ->where('blocked_countries', 'NOT LIKE', '%'.Helper::userCountry().'%');

                $this->filterByGenderAge($data);

                $data->orWhere('subscriptions.stripe_id', '<>', '')
                ->where('stripe_status', 'active')
                ->where('categories_id', 'LIKE', '%'.$category->id.'%')
                ->where('blocked_countries', 'NOT LIKE', '%'.Helper::userCountry().'%');

                $this->filterByGenderAge($data);

                $data->orWhere('subscriptions.stripe_id', '<>', '')
                ->where('ends_at', '>=', now())
                ->where('stripe_status', 'canceled')
                ->where('categories_id', 'LIKE', '%'.$category->id.'%')
                ->where('blocked_countries', 'NOT LIKE', '%'.Helper::userCountry().'%');

                $this->filterByGenderAge($data);

                $data->orWhere('subscriptions.stripe_id', '=', '')
                ->whereFree('yes')
                ->where('categories_id', 'LIKE', '%'.$category->id.'%')
                ->where('blocked_countries', 'NOT LIKE', '%'.Helper::userCountry().'%');

                $this->filterByGenderAge($data);
              }


            $users = $data->groupBy('users.id')
                ->orderBy(\DB::raw($orderBy), 'DESC')
                ->orderBy('users.id', 'ASC')
                ->select('users.*')
                ->paginate(12);
      }

        if ($this->request->input('page') > $users->lastPage()) {
    			abort('404');
    		}
        return view('index.categories', [
          'users' => $users,
            'title' => $title,
            'slug' => $slug,
            'image' => $category->image,
            'keywords' => $category->keywords,
            'description' => $category->description,
            'isCategory' => true,
        ]);
    }

    public function contact()
  	{
      if ($this->settings->disable_contact) {
        abort(404);
      }

      return view('index.contact');
    }

    public function funciona()
  	{
      return view('index.funciona');
    }
    
     public function marca()
  	{
      return view('index.marca');
    }

    public function contactStore(Request $request)
  	{
      $settings = AdminSettings::first();
      $input = $request->all();
      $request['_captcha'] = $settings->captcha_contact;

      $errorMessages = [
        'g-recaptcha-response.required_if' => 'reCAPTCHA Error',
        'g-recaptcha-response.captcha' => 'reCAPTCHA Error',
      ];

        $validator = Validator::make($input, [
          'full_name' => 'min:3|max:25',
          'email'     => 'required|email',
          'subject'     => 'required',
          'message' => 'min:10|required',
          'g-recaptcha-response' => 'required_if:_captcha,==,on|captcha',
          'agree_terms_privacy' => ''
       ], $errorMessages);

      if ($validator->fails()) {
        return redirect('contact')
        ->withInput()->withErrors($validator);
       }

       // SEND EMAIL TO SUPPORT
       $fullname    = $input['full_name'];
  	   $email_user  = $input['email'];
  		 $title_site  = $settings->title;
       $subject     = $input['subject'];
  		 $email_reply = $settings->email_admin;

       Mail::send('emails.contact-email', array(
         'full_name' => $input['full_name'],
         'email' => $input['email'],
         'subject' => $input['subject'],
         '_message' => $input['message']
       ),
  		 function($message) use (
  				 $fullname,
  				 $email_user,
  				 $title_site,
  				 $email_reply,
           $subject
  		 ) {
            $message->from($email_reply, $fullname);
            $message->subject(trans('general.message').' - '.$subject.' - '.$email_user);
            $message->to($email_reply,$title_site);
            $message->replyTo($email_user);
          });

      return redirect('contact')->with(['notification' => trans('general.send_contact_success')]);
  	}

    // Dark Mode
    public function darkMode($mode)
    {
      if ($mode == 'dark') {
        auth()->user()->dark_mode = 'on';
        auth()->user()->save();
      } else {
        auth()->user()->dark_mode = 'off';
        auth()->user()->save();
      }

      return redirect()->back();

    }

    // Add Bookmark
    public function addBookmark()
    {
      // Find post exists
      $post = Updates::findOrFail($this->request->id);

      $bookmark = Bookmarks::firstOrNew([
        'user_id' => auth()->user()->id,
        'updates_id' => $this->request->id
      ]);

      if ($bookmark->exists) {
        $bookmark->delete();

        return response()->json([
          'success' => true,
          'type' => 'deleted'
        ]);
      } else {
        $bookmark->save();

        return response()->json([
          'success' => true,
          'type' => 'added'
        ]);
      }
    } // End addBookmark

    public function searchCreator()
    {
      $query = $this->request->get('user');
      $data = "";

      if ($query != '' && strlen($query) >= 2) {
        $sql = User::where('status','active')
            ->where('username','LIKE', '%'.$query.'%')
             ->whereVerifiedId('yes')
             ->where('id', '<>', $this->settings->hide_admin_profile == 'on' ? 1 : 0)
             ->whereRelation('plans', 'status', '1')
              ->whereFreeSubscription('no')
              ->whereHideProfile('no')
              ->where('blocked_countries', 'NOT LIKE', '%'.Helper::userCountry().'%')

              ->orWhere('name','LIKE', '%'.$query.'%')
               ->whereVerifiedId('yes')
               ->where('id', '<>', $this->settings->hide_admin_profile == 'on' ? 1 : 0)
               ->whereRelation('plans', 'status', '1')
                ->whereFreeSubscription('no')
                ->whereHideProfile('no')
                ->whereHideName('no')
                ->where('blocked_countries', 'NOT LIKE', '%'.Helper::userCountry().'%')

             ->orWhere('status','active')
              ->where('username','LIKE', '%'.$query.'%')
                ->whereVerifiedId('yes')
                ->where('id', '<>', $this->settings->hide_admin_profile == 'on' ? 1 : 0)
                ->whereFreeSubscription('yes')
                ->whereHideProfile('no')
                ->where('blocked_countries', 'NOT LIKE', '%'.Helper::userCountry().'%')

                ->orWhere('status','active')
                 ->where('name','LIKE', '%'.$query.'%')
                   ->whereVerifiedId('yes')
                   ->where('id', '<>', $this->settings->hide_admin_profile == 'on' ? 1 : 0)
                   ->whereFreeSubscription('yes')
                   ->whereHideProfile('no')
                   ->whereHideName('no')
                   ->where('blocked_countries', 'NOT LIKE', '%'.Helper::userCountry().'%')
                   ->orderBy('id','desc')
             ->take(4)
             ->get();

          if ($sql) {
            foreach ($sql as $user) {

              $name = $user->hide_name == 'yes' ? $user->username : $user->name;
              $description = $user->profession ?: '@'.$user->username;

              $data .= '<div class="card border-0">
  							<div class="list-group list-group-sm list-group-flush">
                 <a href="'.url($user->username).'" class="list-group-item list-group-item-action text-decoration-none py-2 px-3 bg-autocomplete">
                   <div class="media">
                    <div class="media-left mr-3 position-relative">
                        <img class="media-object rounded-circle" src="'.Helper::getFile(config('path.avatar').$user->avatar).'" width="30" height="30">
                    </div>
                    <div class="media-body overflow-hidden">
                      <div class="d-flex justify-content-between align-items-center">
                       <h6 class="media-heading mb-0 text-truncate">
                            '.$name.'
                        </h6>
                      </div>
  										<small class="text-truncate m-0 w-100 text-left d-block mt-1">'.$description.'</small>
                    </div>
                </div>
                  </a>
               </div>
  					 </div>';
            }
            return $data;
           }
          }
        }// End Method

      public function refreshCreators()
      {
        $type = $this->request->type == 'free' ?: false;
        $users = $this->userExplore($type);
        
        return view('includes.listing-explore-creators', ['users' => $users])->render();
      }

      public function creatorsBroadcastingLive()
      {
        // Search Live Streaming
        $users = LiveStreamings::where('live_streamings.updated_at', '>', now()->subMinutes(5))
        ->leftjoin('users', 'users.id', '=', 'live_streamings.user_id')
        ->where('live_streamings.status', '0')
        ->where('users.blocked_countries', 'NOT LIKE', '%'.Helper::userCountry().'%');

        $this->filterByGenderAge($users);

        $users = $users->orderBy('live_streamings.id', 'desc')
        ->select('live_streamings.*')
        ->paginate(20);

        if ($this->request->input('page') > $users->lastPage()) {
    			abort('404');
    		}

        return view('index.creators-live', [
          'users' => $users
        ]);
      }// End method creatorsBroadcastingLive
}
