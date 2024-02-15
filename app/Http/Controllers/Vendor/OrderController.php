<?php

namespace App\Http\Controllers\Vendor;

use App\Models\Order;
use App\Exports\OrderExport;
use App\Models\OrderPayment;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use App\CentralLogics\OrderLogic;
use App\Exports\OrderRefundExport;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    public function list($status , Request $request)
    {
        $key = explode(' ', $request['search']);

        $data =0;
        $restaurant =Helpers::get_restaurant_data();
        if (($restaurant->restaurant_model == 'subscription' &&  $restaurant?->restaurant_sub?->self_delivery == 1)  || ($restaurant->restaurant_model == 'commission' &&  $restaurant->self_delivery_system == 1) ){
        $data =1;
        }

         Order::where(['restaurant_checked' => 0])->where('restaurant_id',Helpers::get_restaurant_id())->update(['restaurant_checked' => 1]);

        $orders = Order::with(['customer'])
        ->when($status == 'searching_for_deliverymen', function($query){
            return $query->SearchingForDeliveryman();
        })
        ->when($status == 'confirmed', function($query){
            return $query->whereIn('order_status',['confirmed', 'accepted'])->whereNotNull('confirmed');
        })
        ->when($status == 'pending', function($query) use($data){
            if(config('order_confirmation_model') == 'restaurant' || $data)
            {
                return $query->where('order_status','pending');
            }
            else
            {
                return $query->where('order_status','pending')->where('order_type', 'take_away');
            }
        })
        ->when($status == 'cooking', function($query){
            return $query->where('order_status','processing');
        })
        ->when($status == 'food_on_the_way', function($query){
            return $query->where('order_status','picked_up');
        })
        ->when($status == 'delivered', function($query){
            return $query->Delivered();
        })
        ->when($status == 'ready_for_delivery', function($query){
            return $query->where('order_status','handover');
        })
        ->when($status == 'refund_requested', function($query){
            return $query->Refund_requested();
        })
        ->when($status == 'refunded', function($query){
            return $query->Refunded();
        })
        ->when($status == 'scheduled', function($query) use($data){
            return $query->Scheduled()->where(function($q) use($data){
                if(config('order_confirmation_model') == 'restaurant' || $data)
                {
                    $q->whereNotIn('order_status',['failed','canceled', 'refund_requested', 'refunded']);
                }
                else
                {
                    $q->whereNotIn('order_status',['pending','failed','canceled', 'refund_requested', 'refunded'])->orWhere(function($query){
                        $query->where('order_status','pending')->where('order_type', 'take_away');
                    });
                }
            });
        })
        ->when($status == 'all', function($query) use($data){
            return $query->where(function($q1) use($data) {
                $q1->whereNotIn('order_status',(config('order_confirmation_model') == 'restaurant'|| $data)?['failed','canceled', 'refund_requested', 'refunded']:['pending','failed','canceled', 'refund_requested', 'refunded'])
                ->orWhere(function($q2){
                    return $q2->where('order_status','pending')->where('order_type', 'take_away');
                })->orWhere(function($q3){
                    return $q3->where('order_status','pending')->whereNotNull('subscription_id');
                });
            });
        })
        ->when(in_array($status, ['pending','confirmed']), function($query){
            return $query->OrderScheduledIn(30);
        })
        ->when(isset($key), function ($query) use ($key) {
            return $query->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('id', 'like', "%{$value}%")
                        ->orWhere('order_status', 'like', "%{$value}%")
                        ->orWhere('transaction_reference', 'like', "%{$value}%");
                }
            });
        })
        ->Notpos()
        ->NotDigitalOrder()
        ->hasSubscriptionToday()
        ->where('restaurant_id',\App\CentralLogics\Helpers::get_restaurant_id())
        ->orderBy('schedule_at', 'desc')
        ->paginate(config('default_pagination'));

        $st=$status;
        $status = translate('messages.'.$status);
        return view('vendor-views.order.list', compact('orders', 'status','st'));
    }

    public function search(Request $request){
        $key = explode(' ', $request['search']);
        $orders=Order::where(['restaurant_id'=>Helpers::get_restaurant_id()])->where(function ($q) use ($key) {
            foreach ($key as $value) {
                $q->orWhere('id', 'like', "%{$value}%")
                    ->orWhere('order_status', 'like', "%{$value}%")
                    ->orWhere('transaction_reference', 'like', "%{$value}%");
            }
        })->Notpos()
        ->NotDigitalOrder()
        ->limit(100)->get();
        return response()->json([
            'view'=>view('vendor-views.order.partials._table',compact('orders'))->render()
        ]);
    }

    public function details(Request $request,$id)
    {
        $order = Order::with(['payments','subscription','subscription.schedule_today','details', 'customer'=>function($query){
            return $query->withCount('orders');
        },'delivery_man'=>function($query){
            return $query->withCount('orders');
        }])->where(['id' => $id, 'restaurant_id' => Helpers::get_restaurant_id()])->first();
        if (isset($order)) {
            return view('vendor-views.order.order-view', compact('order'));
        } else {
            Toastr::info('No more orders!');
            return back();
        }
    }

    public function status(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'order_status' => 'required|in:confirmed,processing,handover,delivered,canceled',
            'reason' =>'required_if:order_status,canceled',
        ],[
            'id.required' => 'Order id is required!'
        ]);

        $order = Order::where(['id' => $request->id, 'restaurant_id' => Helpers::get_restaurant_id()])->first();

        if($order->delivered != null)
        {
            Toastr::warning(translate('messages.cannot_change_status_after_delivered'));
            return back();
        }

        if($request['order_status']=='canceled' && !config('canceled_by_restaurant'))
        {
            Toastr::warning(translate('messages.you_can_not_cancel_a_order'));
            return back();
        }

        if($request['order_status']=='canceled' && $order->confirmed)
        {
            Toastr::warning(translate('messages.you_can_not_cancel_after_confirm'));
            return back();
        }

        $data =0;
        $restaurant =Helpers::get_restaurant_data();
        if (($restaurant->restaurant_model == 'subscription' && $restaurant?->restaurant_sub?->self_delivery == 1)  || ($restaurant->restaurant_model == 'commission' &&  $restaurant->self_delivery_system == 1) ){
        $data =1;
        }

         if($request['order_status']=='delivered' && $order->order_type == 'delivery' && !$data)
        {
            Toastr::warning(translate('messages.you_can_not_delivered_delivery_order'));
            return back();
        }

        if($request['order_status'] =="confirmed")
        {
            if(!$data && config('order_confirmation_model') == 'deliveryman' && $order->order_type == 'delivery' && $order->subscription_id == null )
            {
                Toastr::warning(translate('messages.order_confirmation_warning'));
                return back();
            }
        }

        if ($request->order_status == 'delivered') {
            $order_delivery_verification = (boolean)\App\Models\BusinessSetting::where(['key' => 'order_delivery_verification'])->first()?->value;
            if($order->order_type =='delivery' && $order_delivery_verification)
            {
                if($request->otp)
                {
                    if($request->otp != $order->otp)
                    {
                        Toastr::warning(translate('messages.order_varification_code_not_matched'));
                        return back();
                    }
                }
                else
                {
                    Toastr::warning(translate('messages.order_varification_code_is_required'));
                    return back();
                }
            }

            if($order->transaction  == null || isset($order->subscription_id))
            {
                $unpaid_payment = OrderPayment::where('payment_status','unpaid')->where('order_id',$order->id)->first()?->payment_method;
                $unpaid_pay_method = 'digital_payment';
                if($unpaid_payment){
                    $unpaid_pay_method = $unpaid_payment;
                }

                if($order->payment_method == 'cash_on_delivery' || $unpaid_pay_method == 'cash_on_delivery')
                {
                    $ol = OrderLogic::create_transaction(order:$order,received_by:'restaurant', status: null);
                }
                else{
                    $ol = OrderLogic::create_transaction(order:$order,received_by:'admin', status: null);
                }


                if(!$ol)
                {
                    Toastr::warning(translate('messages.faield_to_create_order_transaction'));
                    return back();
                }
            }

            $order->payment_status = 'paid';

            OrderLogic::update_unpaid_order_payment(order_id:$order->id, payment_method:$order->payment_method);

            $order->details->each(function($item, $key){
                if($item->food)
                {
                    $item->food->increment('order_count');
                }
            });
            $order->customer ?  $order->customer->increment('order_count') : '';
        }
        if($request->order_status == 'canceled' || $request->order_status == 'delivered')
        {
            if($order->delivery_man)
            {
                $dm = $order->delivery_man;
                $dm->current_orders = $dm->current_orders>1?$dm->current_orders-1:0;
                $dm->save();
            }
        }

        if($request->order_status == 'canceled' )
        {
            Helpers::increment_order_count($order->restaurant);
            $order->cancellation_reason = $request->reason;
            $order->canceled_by = 'restaurant';
            if(!isset($order->confirmed) && isset($order->subscription_id)){
                $order->subscription()->update(['status' => 'canceled']);
                    if($order?->subscription?->log){
                        $order->subscription->log()->update([
                            'order_status' => $request->status,
                            'canceled' => now(),
                            ]);
                    }
            }
        }
        if($request->order_status == 'delivered')
        {
            $order->restaurant->increment('order_count');
            if($order->delivery_man)
            {
                $order->delivery_man->increment('order_count');
            }
        }
        $order->order_status = $request->order_status;
        if ($request->order_status == "processing") {
            $order->processing_time = $request->processing_time;
        }
        $order[$request['order_status']] = now();
        $order->save();


        if(!Helpers::send_order_notification($order))
        {
            Toastr::warning(translate('messages.push_notification_faild'));
        }
        OrderLogic::update_subscription_log($order);
        Toastr::success(translate('messages.order_status_updated'));
        return back();
    }

    public function update_shipping(Request $request, $id)
    {
        $request->validate([
            'contact_person_name' => 'required',
            'address_type' => 'required',
            'contact_person_number' => 'required',
            'address' => 'required',
        ]);

        $address = [
            'contact_person_name' => $request->contact_person_name,
            'contact_person_number' => $request->contact_person_number,
            'address_type' => $request->address_type,
            'address' => $request->address,
            'floor' => $request->floor,
            'road' => $request->road,
            'house' => $request->house,
            'longitude' => $request->longitude,
            'latitude' => $request->latitude,
            'created_at' => now(),
            'updated_at' => now()
        ];

        DB::table('customer_addresses')->where('id', $id)->update($address);
        Toastr::success('Delivery address updated!');
        return back();
    }

    public function generate_invoice($id)
    {
        $order = Order::where(['id' => $id, 'restaurant_id' => Helpers::get_restaurant_id()])->with(['payments'])->first();
        return view('vendor-views.order.invoice', compact('order'));
    }

    public function add_payment_ref_code(Request $request, $id)
    {
        Order::where(['id' => $id, 'restaurant_id' => Helpers::get_restaurant_id()])->update([
            'transaction_reference' => $request['transaction_reference']
        ]);

        Toastr::success('Payment reference code is added!');
        return back();
    }


    public function orders_export($status , Request $request)
    {
        try{
            $key = explode(' ', $request['search']);

            $data =0;
            $restaurant =Helpers::get_restaurant_data();
            if (($restaurant->restaurant_model == 'subscription' &&  $restaurant?->restaurant_sub?->self_delivery == 1)  || ($restaurant->restaurant_model == 'commission' &&  $restaurant->self_delivery_system == 1) ){
            $data =1;
            }

            Order::where(['checked' => 0])->where('restaurant_id',Helpers::get_restaurant_id())->update(['checked' => 1]);

            $orders = Order::with(['customer'])
            ->when($status == 'searching_for_deliverymen', function($query){
                return $query->SearchingForDeliveryman();
            })
            ->when($status == 'confirmed', function($query){
                return $query->whereIn('order_status',['confirmed', 'accepted'])->whereNotNull('confirmed');
            })
            ->when($status == 'pending', function($query) use($data){
                if(config('order_confirmation_model') == 'restaurant' || $data)
                {
                    return $query->where('order_status','pending');
                }
                else
                {
                    return $query->where('order_status','pending')->where('order_type', 'take_away');
                }
            })
            ->when($status == 'cooking', function($query){
                return $query->where('order_status','processing');
            })
            ->when($status == 'food_on_the_way', function($query){
                return $query->where('order_status','picked_up');
            })
            ->when($status == 'delivered', function($query){
                return $query->Delivered();
            })
            ->when($status == 'ready_for_delivery', function($query){
                return $query->where('order_status','handover');
            })
            ->when($status == 'refund_requested', function($query){
                return $query->Refund_requested();
            })
            ->when($status == 'refunded', function($query){
                return $query->Refunded();
            })
            ->when($status == 'scheduled', function($query) use($data){
                return $query->Scheduled()->where(function($q) use($data){
                    if(config('order_confirmation_model') == 'restaurant' || $data)
                    {
                        $q->whereNotIn('order_status',['failed','canceled', 'refund_requested', 'refunded']);
                    }
                    else
                    {
                        $q->whereNotIn('order_status',['pending','failed','canceled', 'refund_requested', 'refunded'])->orWhere(function($query){
                            $query->where('order_status','pending')->where('order_type', 'take_away');
                        });
                    }
                });
            })
            ->when($status == 'all', function($query) use($data){
                return $query->where(function($q1) use($data) {
                    $q1->whereNotIn('order_status',(config('order_confirmation_model') == 'restaurant'|| $data)?['failed','canceled', 'refund_requested', 'refunded']:['pending','failed','canceled', 'refund_requested', 'refunded'])
                    ->orWhere(function($q2){
                        return $q2->where('order_status','pending')->where('order_type', 'take_away');
                    })->orWhere(function($q3){
                        return $q3->where('order_status','pending')->whereNotNull('subscription_id');
                    });
                });
            })
            ->when(in_array($status, ['pending','confirmed']), function($query){
                return $query->OrderScheduledIn(30);
            })
            ->when(isset($key), function ($query) use ($key) {
                return $query->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('id', 'like', "%{$value}%")
                            ->orWhere('order_status', 'like', "%{$value}%")
                            ->orWhere('transaction_reference', 'like', "%{$value}%");
                    }
                });
            })
            ->Notpos()
            ->NotDigitalOrder()
            ->hasSubscriptionToday()
            ->where('restaurant_id',\App\CentralLogics\Helpers::get_restaurant_id())
            ->orderBy('schedule_at', 'desc')
            ->get();

            if (in_array($status, ['requested','rejected','refunded']))
            {
                $data = [
                    'orders'=>$orders,
                    'type'=>$request->order_type ?? translate('messages.all'),
                    'status'=>$status,
                    'order_status'=>isset($request->orderStatus)?implode(', ', $request->orderStatus):null,
                    'search'=>$request->search ?? $key[0] ??null,
                    'from'=>$request->from_date??null,
                    'to'=>$request->to_date??null,
                    'zones'=>isset($request->zone)?Helpers::get_zones_name($request->zone):null,
                    'restaurant'=>Helpers::get_restaurant_name(Helpers::get_restaurant_id()),
                ];

                if ($request->type == 'excel') {
                    return Excel::download(new OrderRefundExport($data), 'RefundOrders.xlsx');
                } else if ($request->type == 'csv') {
                    return Excel::download(new OrderRefundExport($data), 'RefundOrders.csv');
                }
            }


                $data = [
                    'orders'=>$orders,
                    'type'=>$request->order_type ?? translate('messages.all'),
                    'status'=>$status,
                    'order_status'=>isset($request->orderStatus)?implode(', ', $request->orderStatus):null,
                    'search'=>$request->search ?? $key[0] ??null,
                    'from'=>$request->from_date??null,
                    'to'=>$request->to_date??null,
                    'zones'=>isset($request->zone)?Helpers::get_zones_name($request->zone):null,
                    'restaurant'=>Helpers::get_restaurant_name(Helpers::get_restaurant_id()),
                ];

                if ($request->type == 'excel') {
                    return Excel::download(new OrderExport($data), 'Orders.xlsx');
                } else if ($request->type == 'csv') {
                    return Excel::download(new OrderExport($data), 'Orders.csv');
                }

            } catch(\Exception $e) {
                // dd($e);
                Toastr::error("line___{$e->getLine()}",$e->getMessage());
                info(["line___{$e->getLine()}",$e->getMessage()]);
                return back();
            }
    }

    public function add_order_proof(Request $request, $id)
    {
        $order = Order::find($id);
        $img_names = $order->order_proof?json_decode($order->order_proof):[];
        $images = [];
        $total_file = count($request->order_proof) + count($img_names);
        if(!$img_names){
            $request->validate([
                'order_proof' => 'required|array|max:5',
            ]);
        }

        if ($total_file>5) {
            Toastr::error(translate('messages.order_proof_must_not_have_more_than_5_item'));
            return back();
        }

        if (!empty($request->file('order_proof'))) {
            foreach ($request->order_proof as $img) {
                $image_name = Helpers::upload('order/', 'png', $img);
                array_push($img_names, $image_name);
            }
            $images = $img_names;
        }

        $order->order_proof = json_encode($images);
        $order->save();

        Toastr::success(translate('messages.order_proof_added'));
        return back();
    }


    public function remove_proof_image(Request $request)
    {
        $order = Order::find($request['id']);
        $array = [];
        $proof = isset($order->order_proof) ? json_decode($order->order_proof, true) : [];
        if (count($proof) < 2) {
            Toastr::warning(translate('all_image_delete_warning'));
            return back();
        }
        if (Storage::disk('public')->exists('order/' . $request['name'])) {
            Storage::disk('public')->delete('order/' . $request['name']);
        }
        foreach ($proof as $image) {
            if ($image != $request['name']) {
                array_push($array, $image);
            }
        }
        Order::where('id', $request['id'])->update([
            'order_proof' => json_encode($array),
        ]);
        Toastr::success(translate('order_proof_image_removed_successfully'));
        return back();
    }
    public function download($file_name)
    {
        return Storage::download(base64_decode($file_name));
    }
}
