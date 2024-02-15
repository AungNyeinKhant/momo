<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Models\Zone;
use App\Models\Admin;
use App\Models\Vendor;
use App\Models\Restaurant;
use App\Models\Translation;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use App\Models\BusinessSetting;
use App\Models\SubscriptionPackage;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Models\SubscriptionTransaction;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use MatanYadaev\EloquentSpatial\Objects\Point;
use App\Library\Payer;
use App\Traits\Payment;
use App\Library\Receiver;
use App\Library\Payment as PaymentInfo;


class VendorLoginController extends Controller
{
    public function login(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required|min:6'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $data = [
            'email' => $request->email,
            'password' => $request->password
        ];

        if (auth('vendor')->attempt($data)) {
            $token = $this->genarate_token($request['email']);
            $vendor = Vendor::where(['email' => $request['email']])->first();

            $restaurant=$vendor?->restaurants[0];

            if($restaurant?->restaurant_model == 'subscription' && $restaurant->restaurant_sub_trans && $restaurant->restaurant_sub_trans->transaction_status == 0){
                return response()->json([
                    'pending_payment' => [
                        'id' =>$restaurant->restaurant_sub_trans->id
                    ]
                ], 200);
            }



            if($vendor->restaurants[0]->status == 0 &&  $vendor->status == 0)
            {
                return response()->json([
                    'errors' => [
                        ['code' => 'auth-002', 'message' => translate('messages.inactive_vendor_warning')]
                    ]
                ], 403);
            }

            if( $restaurant?->restaurant_model == 'none')
            {
                return response()->json([
                    'subscribed' => [
                        'restaurant_id' => $vendor?->restaurants[0]?->id, 'type' => 'new_join'
                    ]
                ], 200);
            }

            if ( $restaurant?->restaurant_model == 'subscription' ) {
                $rest_sub = $restaurant?->restaurant_sub;
                if (isset($rest_sub)) {
                    if ($rest_sub?->mobile_app == 0 ) {
                        return response()->json([
                            'errors' => [
                                ['code'=>'no_mobile_app', 'message'=>translate('Your Subscription Plan is not Active for Mobile App')]
                            ]
                        ], 401);
                    }
                }
            }
            if( $restaurant?->restaurant_model == 'unsubscribed' && isset($restaurant?->restaurant_sub_update_application)){
                $vendor->auth_token = $token;
                $vendor?->save();
                        if($restaurant?->restaurant_sub_update_application?->max_product== 'unlimited' ){
                            $max_product_uploads= -1;
                        }
                        else{
                            $max_product_uploads= $restaurant?->restaurant_sub_update_application?->max_product - $restaurant?->foods()?->count();
                            if($max_product_uploads > 0){
                                $max_product_uploads ?? 0;
                            }elseif($max_product_uploads < 0) {
                                $max_product_uploads = 0;
                            }
                        }

                    $data['subscription_other_data'] =  [
                        'total_bill'=>  (float) SubscriptionTransaction::where('restaurant_id', $restaurant->id)->where('package_id', $restaurant?->restaurant_sub_update_application?->package?->id)->sum('paid_amount'),
                        'max_product_uploads' => (int) $max_product_uploads,
                        ];

                return response()->json(['token' => $token, 'zone_wise_topic'=> $vendor?->restaurants[0]?->zone?->restaurant_wise_topic,
                'subscription' => $restaurant?->restaurant_sub_update_application,
                'subscription_other_data' => $data['subscription_other_data'],
                'balance' =>(float)($vendor?->wallet?->balance ?? 0),
                'restaurant_id' =>(int) $restaurant?->id,
                'package' => $restaurant?->restaurant_sub_update_application?->package
                ], 426);
            }

            if($restaurant?->restaurant_model == 'unsubscribed' && !isset($restaurant?->restaurant_sub_update_application)){
                return response()->json([
                    'subscribed' => [
                        'restaurant_id' => $vendor?->restaurants[0]?->id, 'type' => 'new_join'
                    ]
                ], 200);
            }
            $vendor->auth_token = $token;
            $vendor?->save();
            return response()->json(['token' => $token, 'zone_wise_topic'=> $vendor?->restaurants[0]?->zone?->restaurant_wise_topic], 200);
        } else {
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => 'Unauthorized.']);
            return response()->json([
                'errors' => $errors
            ], 401);
        }
    }

    private function genarate_token($email)
    {
        $token = Str::random(120);
        $is_available = Vendor::where('auth_token', $token)->where('email', '!=', $email)->count();
        if($is_available)
        {
            $this->genarate_token($email);
        }
        return $token;
    }


    public function register(Request $request)
    {
        $status = BusinessSetting::where('key', 'toggle_restaurant_registration')->first();
        if(!isset($status) || $status->value == '0')
        {
            return response()->json(['errors' => Helpers::error_formater('self-registration', translate('messages.restaurant_self_registration_disabled'))]);
        }

        $validator = Validator::make($request->all(), [
            'fName' => 'required',
            // 'restaurant_name' => 'required',
            // 'restaurant_address' => 'required',
            'lat' => 'required|numeric|min:-90|max:90',
            'lng' => 'required|numeric|min:-180|max:180',
            'email' => 'required|email|unique:vendors',
            'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:9|unique:vendors',
            'min_delivery_time' => 'required',
            'max_delivery_time' => 'required',
            'password' => ['required', Password::min(6)],//->mixedCase()->letters()->numbers()->symbols()->uncompromised()
            'zone_id' => 'required',
            'logo' => 'required|max:2048',
            'cover_photo' => 'nullable|max:2048',
            'vat' => 'required',
            'delivery_time_type'=>'required',

        ]);

        if($request->zone_id)
        {
            $zone = Zone::query()
            ->whereContains('coordinates', new Point($request->lat, $request->lng, POINT_SRID))
            ->where('id',$request->zone_id)
            ->first();
            if(!$zone){
                $validator->getMessageBag()->add('latitude', translate('messages.coordinates_out_of_zone'));
            }
        }

        $data = json_decode($request->translations, true);
        //$data = $request->translations;


        // info($data);
        if (count($data) < 1) {
            $validator->getMessageBag()->add('translations', translate('messages.Name and description in english is required'));
        }

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $vendor = new Vendor();
        $vendor->f_name = $request->fName;
        $vendor->l_name = $request->lName;
        $vendor->email = $request->email;
        $vendor->phone = $request->phone;
        $vendor->password = bcrypt($request->password);
        $vendor->status = null;
        $vendor->save();

        $restaurant = new Restaurant;
        $restaurant->name = $data[0]['value'];

        // $restaurant->name = $request->restaurant_name;
        $restaurant->phone = $request->phone;
        $restaurant->email = $request->email;
        $restaurant->logo = Helpers::upload( dir: 'restaurant/', format:'png', image:$request->file('logo'));
        $restaurant->cover_photo = Helpers::upload( dir: 'restaurant/cover/', format:'png',image: $request->file('cover_photo'));
        // $restaurant->address = $request->restaurant_address;
        $restaurant->address = $data[1]['value'];

        $restaurant->latitude = $request->lat;
        $restaurant->longitude = $request->lng;
        $restaurant->vendor_id = $vendor->id;
        $restaurant->zone_id = $request->zone_id;
        $restaurant->restaurant_type = $request->restaurant_type;
        $restaurant->tax = $request->vat;
        $restaurant->delivery_time =$request->minimum_delivery_time .'-'. $request->maximum_delivery_time.'-'.$request->delivery_time_type;
        $restaurant->status = 0;
        $restaurant->restaurant_model = 'none';
        $restaurant->save();


        foreach ($data as $key=>$i) {
            $data[$key]['translationable_type'] = 'App\Models\Restaurant';
            $data[$key]['translationable_id'] = $restaurant->id;
        }
        Translation::insert($data);



        $cuisine_ids = [];
        $cuisine_ids = json_decode($request->cuisine_ids, true);
        //$cuisine_ids = $request->cuisine_ids;
        $restaurant?->cuisine()?->sync($cuisine_ids);
        try{
            $admin= Admin::where('role_id', 1)->first();
            $mail_status = Helpers::get_mail_status('registration_mail_status_restaurant');
            if(config('mail.status') && $mail_status == '1'){
                Mail::to($request['email'])->send(new \App\Mail\VendorSelfRegistration('pending', $vendor->f_name.' '.$vendor->l_name));
            }
            $mail_status = Helpers::get_mail_status('restaurant_registration_mail_status_admin');
            if(config('mail.status') && $mail_status == '1'){
                Mail::to($admin['email'])->send(new \App\Mail\RestaurantRegistration('pending', $vendor->f_name.' '.$vendor->l_name));
            }
        }catch(\Exception $ex){
            info($ex->getMessage());
        }

        return response()->json([
            'restaurant_id'=> $restaurant->id,
            'message'=>translate('messages.application_placed_successfully')],200);
    }

    public function package_view(){
        $packages= SubscriptionPackage::where('status',1)->get();
        return response()->json(['packages'=> $packages], 200);
    }

    public function business_plan(Request $request){

        $validator = Validator::make($request->all(), [
            'restaurant_id' => 'required',
            'payment' => 'nullable',
            'business_plan' => 'required|in:subscription,commission',
            'package_id' => 'nullable|required_if:business_plan,subscription',
            // 'payment_gateway' => 'nullable|required_if:payment,paying_now',

        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $restaurant=Restaurant::findOrFail($request->restaurant_id);

        if($request->business_plan == 'subscription' && $request->package_id != null ) {
            $restaurant_id=$restaurant->id;
            $package_id=$request->package_id;
            $payment_method=$request->payment_method ?? 'free_trial';
            $reference=$request->reference ?? null;
            $discount=$request->discount ?? 0;
            $restaurant=Restaurant::findOrFail($restaurant_id);
            $type=$request->type ?? 'new_join';

            if($request->payment == 'free_trial' ){
                $status=Helpers::subscription_plan_chosen(restaurant_id:$restaurant_id , package_id:$package_id,payment_method: $payment_method ,discount:$discount, reference:$reference ,type: $type);

                if($status === 'downgrade_error'){
                    return response()->json([
                        'errors' => ['message' => translate('messages.You_can_not_downgraded_to_this_package_please_choose_a_package_with_higher_upload_limits')]
                    ], 403);
                }
            }
            elseif($request->payment == 'paying_now'){
                $status= Helpers::subscription_plan_chosen(restaurant_id:$restaurant_id , package_id:$package_id,payment_method: 'pay_now' ,discount:$discount, reference:$reference ,type: $type);
                if($status === 'downgrade_error'){
                    return response()->json([
                        'errors' => ['message' => translate('messages.You_can_not_downgraded_to_this_package_please_choose_a_package_with_higher_upload_limits')]
                    ], 403);
                }
                return response()->json(['id'=>$status],200);
            }
            $data=[
            'restaurant_model' => 'subscription',
            'logo'=> $restaurant->logo,
            'message' => translate('messages.application_placed_successfully')
            ];
            return response()->json($data,200);
        }

        elseif($request->business_plan == 'commission' ){
            $restaurant->restaurant_model = 'commission';
            $restaurant->save();

        $data=['restaurant_model' => 'commission',
        'logo'=> $restaurant->logo,
        'message' => translate('messages.application_placed_successfully')
        ];
        return response()->json($data,200);
        }
    }


    public function subscription_payment_api(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'callback' => 'nullable',
            'payment_gateway' => 'required',

        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $subscription = SubscriptionTransaction::with('restaurant')->where('transaction_status',0)->findOrFail($request->id);
        $payer = new Payer(
            $subscription->restaurant->name ,
            $subscription->restaurant->email,
            $subscription->restaurant->phone,
            ''
        );
        $additional_data = [
            'business_name' => BusinessSetting::where(['key'=>'business_name'])->first()?->value,
            'business_logo' => asset('storage/app/public/business') . '/' .BusinessSetting::where(['key' => 'logo'])->first()?->value
        ];
        $payment_info = new PaymentInfo(
            success_hook: 'sub_success',
            failure_hook: 'sub_fail',
            currency_code: Helpers::currency_code(),
            payment_method: $request->payment_gateway,
            payment_platform: 'web',
            payer_id: $subscription->restaurant_id,
            receiver_id: '100',
            additional_data:  $additional_data,
            payment_amount: $subscription->paid_amount ,
            external_redirect_link: $request->has('callback')?$request['callback']:session('callback'),
            attribute: 'restaurant_subscription_payments',
            attribute_id: $subscription->id,
        );

        $receiver_info = new Receiver('Admin','example.png');
        $redirect_link = Payment::generate_link($payer, $payment_info, $receiver_info);
        $data = [
            'redirect_link' => $redirect_link,
            // 'type'=> 'subscription'
        ];
        return response()->json($data, 200);
    }

}
