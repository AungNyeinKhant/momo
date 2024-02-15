<?php

use App\Models\Order;
use App\Models\WalletPayment;
use App\CentralLogics\Helpers;
use Illuminate\Support\Carbon;
use App\CentralLogics\OrderLogic;
use App\CentralLogics\CustomerLogic;
use Illuminate\Support\Facades\Mail;
use App\Models\SubscriptionTransaction;

if (! function_exists('translate')) {
    function translate($key, $replace = [])
    {
        if(strpos($key, 'validation.') === 0 || strpos($key, 'passwords.') === 0 || strpos($key, 'pagination.') === 0 || strpos($key, 'order_texts.') === 0) {
            return trans($key, $replace);
        }

        $key = strpos($key, 'messages.') === 0?substr($key,9):$key;
        $local = app()->getLocale();
        try {
            // $language=\App\Models\BusinessSetting::where('key','language')->first();
            // $language = $language->value ?? '';
            // dd($language);
            // foreach(json_decode($language) as $lang){
            //     // if($lang != $local){
            //         $lang_array = include(base_path('resources/lang/' . $lang . '/messages.php'));
            //         if (!array_key_exists($key, $lang_array)) {
            //             $processed_key = str_replace('_', ' ', Helpers::remove_invalid_charcaters($key));
            //             $lang_array[$key] = $processed_key;
            //             $str = "<?php return " . var_export($lang_array, true) . ";";
            //             file_put_contents(base_path('resources/lang/' . $lang . '/messages.php'), $str);
            //         // }
            //     }
            // }
            $lang_array = include(base_path('resources/lang/' . $local . '/messages.php'));
            $processed_key = ucfirst(str_replace('_', ' ', Helpers::remove_invalid_charcaters($key)));

            if (!array_key_exists($key, $lang_array)) {
                $lang_array[$key] = $processed_key;
                $str = "<?php return " . var_export($lang_array, true) . ";";
                file_put_contents(base_path('resources/lang/' . $local . '/messages.php'), $str);
                $result = $processed_key;
            } else {
                $result = trans('messages.' . $key, $replace);
            }
        } catch (\Exception $exception) {
            info($exception->getMessage());
            $result = trans('messages.' . $key, $replace);
        }

        return $result;
    }
}

if (! function_exists('order_place')) {

    function order_place($data) {
        $order = Order::find($data->attribute_id);
        $order->order_status='confirmed';
        if($order->payment_method != 'partial_payment'){
            $order->payment_method=$data->payment_method;
        }
        // $order->transaction_reference=$data->transaction_ref;
        $order->payment_status='paid';
        $order->confirmed=now();
        $order->save();
        OrderLogic::update_unpaid_order_payment(order_id:$order->id, payment_method:$data->payment_method);
        try {
            Helpers::send_order_notification($order);
        } catch (\Exception $e) {
            // info($e);
        }
    }
}
    if (! function_exists('order_place')) {
        function order_failed($data) {
            $order = Order::find($data->attribute_id);
            $order->order_status='failed';
            if($order->payment_method != 'partial_payment'){
                $order->payment_method=$data->payment_method;
            }
            $order->failed=now();
            $order->save();
        }
    }

    if (! function_exists('wallet_success')) {
        function wallet_success($data) {
            $order = WalletPayment::find($data->attribute_id);
            $order->payment_method=$data->payment_method;
            // $order->transaction_reference=$data->transaction_ref;
            $order->payment_status='success';
            $order->save();
            $wallet_transaction = CustomerLogic::create_wallet_transaction($data->payer_id, $data->payment_amount, 'add_fund',$data->payment_method);
            if($wallet_transaction)
            {
                $mail_status = Helpers::get_mail_status('add_fund_mail_status_user');
                try{
                    if(config('mail.status') && $mail_status == '1') {
                        Mail::to($wallet_transaction->user->email)->send(new \App\Mail\AddFundToWallet($wallet_transaction));
                    }
                }catch(\Exception $ex)
                {
                    info($ex->getMessage());
                }
            }
        }
    }
    if (! function_exists('wallet_failed')) {
        function wallet_failed($data) {
            $order = WalletPayment::find($data->attribute_id);
            $order->payment_status='failed';
            $order->payment_method=$data->payment_method;
            $order->save();
        }
    }

    if (! function_exists('sub_success')) {
        function sub_success($data){
            $subscription_transaction= SubscriptionTransaction::where('id',$data->attribute_id)->with('restaurant','restaurant.restaurant_sub_update_application')->first();
            $subscription_transaction->payment_status ='success';
            $subscription_transaction->reference = $data->transaction_id;
            $subscription_transaction->payment_method = $data->payment_method;
            $subscription_transaction->transaction_status = 1;
            $subscription_transaction->restaurant->restaurant_sub_update_application->update([
                // 'expiry_date'=> Carbon::now()->addDays($subscription_transaction->validity)->format('Y-m-d'),
                'status'=>1
            ]);
            $subscription_transaction->save();
        }
    }

    if (! function_exists('sub_fail')) {
        function sub_fail($data){
            $subscription_transaction= SubscriptionTransaction::where('id',$data->attribute_id)->with('restaurant')->first();
            $subscription_transaction->payment_status ='failed';
            $subscription_transaction->reference = $data?->transaction_id ?? null;
            $subscription_transaction->payment_method = $data->payment_method;
            $subscription_transaction->save();
        }
    }

    if (!function_exists('addon_published_status')) {
        function addon_published_status($module_name)
        {
            $is_published = 0;
            try {
                $full_data = include("Modules/{$module_name}/Addon/info.php");
                $is_published = $full_data['is_published'] == 1 ? 1 : 0;
                return $is_published;
            } catch (\Exception $exception) {
                return 0;
            }
        }
    }
