<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Supplier;    

class SupplierController extends Controller
{
    public function addNew()
    {
        return view('admin-views.suppliers.supplieradd');
    }
    
    public function save(Request $request)
    {
        {
            // Validate the incoming request data
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'link' => 'required|url|max:255',
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Adjust allowed file types and size as needed
            ]);

            // Handle file upload for the supplier's image
    if ($request->hasFile('image')) {
        $imagePath = $request->file('image')->store('public/supplier_images');
        $imagePath = Str::replaceFirst('public/', '', $imagePath); // Remove 'public/' from the path
    } else {
        // Handle the case where no image is uploaded
        return redirect()->back()->with('error', 'Please select an image for the supplier.');
    }
    
            // Create a new Supplier instance and populate it with the validated data
            $supplier = new Supplier();
            $supplier->name = $validatedData['name'];
            $supplier->link = $validatedData['link'];
            $supplier->image = $imagePath; // Save the image path
            
            // Save the supplier to the database
            $supplier->save();
    
            // Redirect to the supplier list page with a success message
            return redirect()->route('admin.supplier.list')->with('success', 'Supplier added successfully');
        }
    }
    public function list()
{
    // Retrieve a list of suppliers from the database
    $suppliers = Supplier::all(); // You can adjust this query as needed

    // Pass the suppliers data to the view
    return view('admin-views.suppliers.list', compact('suppliers'));
}
    
    public function edit($id)
    {
        // Find the supplier by ID
        $supplier = Supplier::find($id);

        if (!$supplier) {
            // If the supplier is not found, you can handle this case, for example, by redirecting to a 404 page.
            return redirect()->route('admin.supplier.list')->with('error', 'Supplier not found.');
        }

        // Return the view for editing the supplier with the supplier data
        return view('admin-views.suppliers.edit', compact('supplier'));
    }
    
    public function update(Request $request, $id)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'link' => 'required|url|max:255',
            'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048', // Adjust allowed file types and size as needed
        ]);
    
        // Find the supplier by ID
        $supplier = Supplier::findOrFail($id);
    
        // Update the supplier's name and link
        $supplier->name = $validatedData['name'];
        $supplier->link = $validatedData['link'];
    
        // Handle image update (if a new image is provided)
        if ($request->hasFile('image')) {
            // Delete the old image (if it exists)
            if ($supplier->image && Storage::exists($supplier->image)) {
                Storage::delete($supplier->image);
            }
    
            // Store the new image
            $imagePath = $request->file('image')->store('public/supplier_images');
            $imagePath = Str::replaceFirst('public/', '', $imagePath); // Remove 'public/' from the path
            $supplier->image = $imagePath;
        }
    
        // Save the updated supplier to the database
        $supplier->save();
    
        // Redirect to the supplier list or another appropriate page with a success message
        return redirect()->route('admin.supplier.list')->with('success', 'Supplier updated successfully');
    }
    
    
    public function delete($id)
{
    // Find the supplier by ID
    $supplier = Supplier::find($id);

    // Check if the supplier exists
    if (!$supplier) {
        return redirect()->route('admin.supplier.list')->with('error', 'Supplier not found.');
    }

    // Delete the supplier's image from storage
    Storage::delete('public/' . $supplier->image);

    // Delete the supplier from the database
    $supplier->delete();
    Session::flash('success', 'Supplier deleted successfully.');
    // Redirect to the supplier list page with a success message
    return redirect()->route('admin.supplier.list')->with('success', 'Supplier deleted successfully');
    }
}
