<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\Http\Controllers\Controller;
use App\Models\AddOn;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use Illuminate\Support\Facades\Validator;
use App\Scopes\RestaurantScope;
use App\Models\Translation;

class AddOnController extends Controller
{
    public function list(Request $request)
    {
        $vendor = $request['vendor'];
        $addons = AddOn::withoutGlobalScope(RestaurantScope::class)->withoutGlobalScope('translate')->with('translations')->where('restaurant_id', $vendor?->restaurants[0]?->id)->latest()->get();

        return response()->json(Helpers::addon_data_formatting($addons, true, true, app()->getLocale()),200);
    }

    public function store(Request $request)
{
    try {
        if (!$request?->vendor?->restaurants[0]?->food_section) {
            return response()->json([
                'errors' => [
                    ['code' => 'unauthorized', 'message' => translate('messages.permission_denied')]
                ]
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'price' => 'required|numeric'
           
        ]);
        // $data=[];
        // if(!empty($request?->translations)){
        //     $data = json_decode($request?->translations);
        // }
        //     if (count($data) < 1) {
        //         $validator->getMessageBag()->add('translations', translate('messages.Name and description in English are required'));
        //     }
        //     if ($validator->fails() || count($data) < 1) {
        //         return response()->json(['errors' => "Transalation error".$data], 403);
        //     }

        $vendor = $request['vendor'];
        $addon = new AddOn();
        $addon->name = $request->name;
        $addon->image = Helpers::upload(dir: 'Addon/', format: 'png', image: $request->file('image'));
        $addon->price = $request->price;
        $addon->restaurant_id = $vendor?->restaurants[0]?->id;
        $addon->save();

        // foreach ($data as $key => $item) {
            
        // }
        Translation::updateOrInsert(
                [
                    'translationable_type' => 'App\Models\AddOn',
                    'translationable_id' => $addon->id,
                    'locale' => 'en',
                    'key' => 'name'
                ],
                ['value' => $addon->name]
            );

        return response()->json(['message' => translate('messages.addon_added_successfully')], 200);
    } catch (\Exception $e) {
        
        return response()->json(['error' => $e->getMessage()], 500);
    }
}



    public function update(Request $request)
    {
        if(!$request?->vendor?->restaurants[0]?->food_section)
        {
            return response()->json([
                'errors'=>[
                    ['code'=>'unauthorized', 'message'=>translate('messages.permission_denied')]
                ]
            ],403);
        }
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'name' => 'required',
            'price' => 'required',
            'image' => 'nullable|max:2048'
            
        ]);

        // $data = $request?->translations;

        // if (count($data) < 1) {
        //     $validator->getMessageBag()->add('translations', translate('messages.Name and description in english is required'));
        // }

        // if ($validator->fails() || count($data) < 1 ) {
        //     return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        // }

        $addon = AddOn::withoutGlobalScope(RestaurantScope::class)->find($request->id);
    //   try {echo($addon?->name);
           
    //   }
    //   catch (Exception $e){
    //         return response()->json(['errors' => $addon], 500);
    //   }
        $addon->name = $request->name;
        if($request->hasFile('image')){
            $addon->image = Helpers::update(dir:'Addon/', old_image:$addon->image, format:'png', image: $request->file('image'));
            
        }
        
        $addon->price = $request->price;
        $addon?->save();
        

        // foreach ($data as $key=>$item) {
            
        // }
        Translation::updateOrInsert(
                [
                    'translationable_type' => 'App\Models\AddOn',
                    'translationable_id' => $addon->id,
                    'locale' => 'en',
                    'key' => 'name'
                ],
                ['value' => $request->name]
            );
        return response()->json(['message' => "$addon".$request->name], 200);
    }

    public function delete(Request $request)
    {
        if(!$request?->vendor?->restaurants[0]?->food_section)
        {
            return response()->json([
                'errors'=>[
                    ['code'=>'unauthorized', 'message'=>translate('messages.permission_denied')]
                ]
            ],403);
        }
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $addon = AddOn::withoutGlobalScope(RestaurantScope::class)->withoutGlobalScope('translate')->findOrFail($request->id);
        $addon?->translations()?->delete();
        $addon?->delete();

        return response()->json(['message' => translate('messages.addon_deleted_successfully')], 200);
    }

    public function status(Request $request)
    {
        if(!$request?->vendor?->restaurants[0]?->food_section)
        {
            return response()->json([
                'errors'=>[
                    ['code'=>'unauthorized', 'message'=>translate('messages.permission_denied')]
                ]
            ],403);
        }
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'status' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $addon_data = AddOn::withoutGlobalScope(RestaurantScope::class)->findOrFail($request->id);
        $addon_data->status = $request->status;
        $addon_data?->save();

        return response()->json(['message' => translate('messages.addon_status_updated')], 200);
    }

    public function search(Request $request){

        $vendor = $request['vendor'];
        $limit = $request['limite']??25;
        $offset = $request['offset']??1;

        $key = explode(' ', $request['search']);
        $addons=AddOn::withoutGlobalScope(RestaurantScope::class)->whereHas('restaurant',function($query)use($vendor){
            return $query->where('vendor_id', $vendor['id']);
        })->where(function ($q) use ($key) {
            foreach ($key as $value) {
                $q->orWhere('name', 'like', "%{$value}%");
            }
        })->orderBy('name')->paginate($limit, ['*'], 'page', $offset);
        $data = [
            'total_size' => $addons->total(),
            'limit' => $limit,
            'offset' => $offset,
            'addons' => $addons->items()
        ];

        return response()->json([$data],200);
    }
}
