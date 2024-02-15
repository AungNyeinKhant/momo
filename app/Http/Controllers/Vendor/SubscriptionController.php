<?php

namespace App\Http\Controllers\Vendor;

use App\Library\Payer;
use App\Traits\Payment;
use App\Library\Receiver;
use App\Models\Restaurant;
use App\Models\DataSetting;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use Illuminate\Support\Carbon;
use App\Models\BusinessSetting;
use App\Models\RestaurantWallet;
use Illuminate\Support\Facades\DB;
use App\Models\SubscriptionPackage;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\RestaurantSubscription;
use Illuminate\Support\Facades\Schema;
use App\Library\Payment as PaymentInfo;
use App\Models\SubscriptionTransaction;
use Illuminate\Support\Facades\Validator;
use App\Exports\SubsPackageWiseTransactionExport;

class SubscriptionController extends Controller
{
    public function subscription()
    {
            $id=Helpers::get_restaurant_id();
            $restaurant=  Restaurant::where('id',$id)->first();
            if ($restaurant->restaurant_model == 'subscription' || $restaurant->restaurant_model == 'unsubscribed') {
                $rest_subscription= RestaurantSubscription::where('restaurant_id', $id)->with(['package'])->latest()->first();
                $transcations = SubscriptionTransaction::where('restaurant_id', $id)->latest()->paginate(config('default_pagination'));
                $package_id=(isset($rest_subscription->package_id))  ? $rest_subscription->package_id : 0 ;
                $total_bill=SubscriptionTransaction::where('restaurant_id', $id)->where('package_id', $package_id)->sum('paid_amount');
                $packages= SubscriptionPackage::where('status', 1)->get();
                return view('vendor-views.subscription.subscription',compact(['rest_subscription','restaurant','transcations','packages','total_bill']));
            }
            else{
            abort(404);
        }
    }

        public function transcation(Request $request){
            $key = explode(' ', $request['search']);
            $filter = $request->query('filter', 'all');
            $from=$request?->start_date;
            $to= $request?->end_date;
            $transcations = SubscriptionTransaction::where('restaurant_id', Helpers::get_restaurant_id())->with('package')
            ->when($filter == 'month', function ($query) {
                return $query->whereMonth('created_at', Carbon::now()->month);
            })
            ->when($filter == 'year', function ($query) {
                return $query->whereYear('created_at', Carbon::now()->year);
            })

        ->when(isset($key), function($q) use($key){
            $q->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('id', 'like', "%{$value}%")
                        ->orWhere('paid_amount', 'like', "%{$value}%")
                        ->orWhere('reference', 'like', "%{$value}%")
                        ->orWheredate('created_at', 'like', "%{$value}%");
                }
            });
        })
        ->when(isset($from) && isset($to) , function($q)use($from,$to){
            $q->whereBetween('created_at', ["{$from}", "{$to} 23:59:59"]);
        })
            ->latest()->paginate(config('default_pagination'));
            $total = $transcations->total();
            return view('vendor-views.subscription.subscription-transaction',[
            'transcations' => $transcations,
            'filter' => $filter,
            'total' => $total,
            'from' =>  $from,
            'to' =>  $to,
            ]);
        }
        // public function trans_search_by_date(Request $request){
        //     $from=$request->start_date;
        //     $to= $request->end_date;
        //     $filter = 'all';
        //     $transcations=SubscriptionTransaction::where('restaurant_id', Helpers::get_restaurant_id())
        //     ->whereBetween('created_at', ["{$from}", "{$to} 23:59:59"])
        //     ->latest()->paginate(config('default_pagination'));
        //     $total = $transcations->total();
        //     return view('vendor-views.subscription.subscription-transaction',[
        //         'transcations' => $transcations,
        //         'filter' => $filter,
        //         'total' => $total,
        //         'from' =>  $from,
        //         'to' =>  $to,
        //         ]);
        // }

    public function package_renew_change_update(Request $request){
        $package = SubscriptionPackage::findOrFail($request->package_id);
        $restaurant_id=Helpers::get_restaurant_id();
        $discount = $request->discount ?? 0;

        $total_parice =$package->price - (($package->price*$discount)/100);
        $reference= $request->reference ?? null;
        if($request->button == 'renew'){
            $type = 'renew';
        }else{
            $type = null;
        }
        if ($request->payment_type == 'wallet') {
            $wallet = RestaurantWallet::where('vendor_id',Helpers::get_vendor_id())->first();
            if ( $wallet?->balance >= $total_parice) {
                $payment_method= 'wallet';
                $status =Helpers::subscription_plan_chosen(restaurant_id:$restaurant_id ,package_id:$package->id,payment_method: $payment_method ,discount:$discount, reference:$reference ,type:$type);
                if($status === 'downgrade_error'){
                    Toastr::error(translate('messages.You_can_not_downgraded_to_this_package_please_choose_a_package_with_higher_upload_limits') );
                    return back();
                    }
                $wallet->total_withdrawn= $wallet->total_withdrawn +$total_parice;
                    $wallet?->save();
            }
            else{
                Toastr::error('Insufficient Balance');
                return back();
            }
        }
        elseif ($request->payment_type == 'pay_now') {
            $payment_method= 'pay_now';
            $status = Helpers::subscription_plan_chosen(restaurant_id:$restaurant_id ,package_id:$package->id,payment_method: $payment_method ,discount:$discount, reference:$reference ,type:$type);
            if($status === 'downgrade_error'){
                Toastr::error(translate('messages.You_can_not_downgraded_to_this_package_please_choose_a_package_with_higher_upload_limits') );
                return back();
                }
            return to_route('vendor.subscription.digital_payment_methods' , ['subscription_transaction_id'=>$status]);
        }
        Toastr::success(translate('messages.subscription_successful') );
        return redirect()->route('vendor.subscription.subscription');
    }

    // public function rest_transcation_search(Request $request)
    // {
    //     $key = explode(' ', $request['search']);
    //     $transcations = SubscriptionTransaction::where('restaurant_id',Helpers::get_restaurant_id())->where(function ($q) use ($key) {
    //         foreach ($key as $value) {
    //             $q->orWhere('id', 'like', "%{$value}%")
    //                 ->orWhere('paid_amount', 'like', "%{$value}%")
    //                 ->orWhere('reference', 'like', "%{$value}%")
    //                 ->orWheredate('created_at', 'like', "%{$value}%");
    //         }
    //     })
    //         ->get();
    //     $total = $transcations->count();
    //     return response()->json([
    //         'view' => view('vendor-views.subscription._rest_subs_transcation', compact('transcations'))->render(), 'total'=> $total
    //     ]);
    // }

    public function invoice($id){
        $subscription_transaction= SubscriptionTransaction::findOrFail($id);
        $restaurant= Restaurant::findOrFail($subscription_transaction->restaurant_id);

        return view('vendor-views.subscription.subs_transcation_invoice', compact(
            'restaurant',
            'subscription_transaction',
        ));
    }

    public function package_selected(Request $request,$id){

        $rest_subscription= RestaurantSubscription::where('restaurant_id',Helpers::get_restaurant_id())->with(['package'])->latest()->first();
        $package = SubscriptionPackage::where('status',1)->where('id',$id)->first();
        return response()->json([
            'view' => view('vendor-views.subscription._package_selected', compact('rest_subscription','package'))->render()
        ]);
    }


    public function getPaymentMethods($subscription_transaction_id)
    {
        // Check if the addon_settings table exists
        if (!Schema::hasTable('addon_settings')) {
            return [];
        }
        $published_status = 0; // Set a default value
        $payment_published_status = config('get_payment_publish_status');
        if (isset($payment_published_status[0]['is_published'])) {
            $published_status = $payment_published_status[0]['is_published'];
        }

        $type=request()?->type ?? null;
        $methods = DB::table('addon_settings')->where('is_active',1)->where('settings_type', 'payment_config')

        ->when($published_status == 0, function($q){
            $q->whereIn('key_name', ['ssl_commerz','paypal','stripe','razor_pay','senang_pay','paytabs','paystack','paymob_accept','paytm','flutterwave','liqpay','bkash','mercadopago']);
        })
        ->get();
        $env = env('APP_ENV') == 'live' ? 'live' : 'test';
        $credentials = $env . '_values';

        $data = [];
        foreach ($methods as $method) {
            $credentialsData = json_decode($method->$credentials);
            $additional_data = json_decode($method->additional_data);
            if ($credentialsData->status == 1) {
                $data[] = [
                    'gateway' => $method->key_name,
                    'gateway_title' => $additional_data?->gateway_title,
                    'gateway_image' => $additional_data?->gateway_image
                ];
            }
        }
        return view('module_payment_method',compact('data' ,'subscription_transaction_id' , 'type'));
    }

    public function digital_payment(Request $request){
        $validator = Validator::make($request->all(), [
            'subscription_id' => 'required',
            'payment_gateway' => 'required',
        ]);

        if ($validator->fails()) {
            Toastr::error(translate('messages.Something_went_wrong_please_try_again') );
            return to_route('vendor.subscription.subscription');
        }

        $subscription = SubscriptionTransaction::with('restaurant')->where('transaction_status',0)->findOrFail($request->subscription_id);
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
            // $login_data = DataSetting::where('key', 'restaurant_login_url')->pluck('value')->first();
            $payment_info = new PaymentInfo(
                success_hook: 'sub_success',
                failure_hook: 'sub_fail',
                currency_code: Helpers::currency_code(),
                payment_method: $request->payment_gateway,
                payment_platform: 'web',
                payer_id: $subscription->restaurant_id,
                receiver_id: '100',
                additional_data: $additional_data,
                payment_amount: $subscription->paid_amount ,
                external_redirect_link: route('subscription_payment_view',['type'=> $request?->type]),
                attribute: 'restaurant_subscription_payments',
                attribute_id: $subscription->id,
            );
            $receiver_info = new Receiver('Admin','example.png');
            $redirect_link = Payment::generate_link($payer, $payment_info, $receiver_info);
            return redirect()->away($redirect_link);
    }



    public function transcation_list_export(Request $request){
        try{
            $key = explode(' ', $request['search']);
            $filter = $request->query('filter', 'all');
            $from=$request?->start_date;
            $to= $request?->end_date;
            $data = SubscriptionTransaction::where('restaurant_id', Helpers::get_restaurant_id())->with('package')
            ->when($filter == 'month', function ($query) {
                return $query->whereMonth('created_at', Carbon::now()->month);
            })
            ->when($filter == 'year', function ($query) {
                return $query->whereYear('created_at', Carbon::now()->year);
            })

            ->when(isset($key), function($q) use($key){
                $q->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('id', 'like', "%{$value}%")
                            ->orWhere('paid_amount', 'like', "%{$value}%")
                            ->orWhere('reference', 'like', "%{$value}%")
                            ->orWheredate('created_at', 'like', "%{$value}%");
                    }
                });
            })
            ->when(isset($from) && isset($to) , function($q)use($from,$to){
                $q->whereBetween('created_at', ["{$from}", "{$to} 23:59:59"]);
            })
            ->latest()->get();
            $data = [
                'data'=>$data,
                'filter'=>$filter ?? translate('messages.All'),
                'from'=>$from,
                'package_name'=> $package_name ?? null ,
                'restaurant_name'=> Restaurant::where('id',Helpers::get_restaurant_id())->first()?->name ,
                'to'=>$to,
                'search'=>request()->search ?? null,
            ];
            if($request->type == 'csv'){
                return Excel::download(new SubsPackageWiseTransactionExport($data), 'SubscriptionTransaction.csv');
            }
            return Excel::download(new SubsPackageWiseTransactionExport($data), 'SubscriptionTransaction.xlsx');
        } catch(\Exception $e) {
            Toastr::error("line___{$e->getLine()}",$e->getMessage());
            info(["line___{$e->getLine()}",$e->getMessage()]);
            return back();
        }
    }
}

