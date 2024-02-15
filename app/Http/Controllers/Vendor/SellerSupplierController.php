<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Supplier;  

class SellerSupplierController extends Controller
{
    //
    public function showSupplierList()
    {
        // Fetch and pass the supplier data to the view
        $suppliers =  Supplier::all();

        return view('vendor-views.items.buyingitems', compact('suppliers'));
    }
}
