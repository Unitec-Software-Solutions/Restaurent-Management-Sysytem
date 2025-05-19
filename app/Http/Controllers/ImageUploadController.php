<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageUploadController extends Controller
{
    public function store(Request $request)
    {
        // Validate the uploaded file
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Max 2MB
        ]);

        // Store the image in the 'public' disk (e.g., storage/app/public/images)
        $imagePath = $request->file('image')->store('images', 'public');

        // Save the image path to the database (e.g., in the `food_items` table)
        // Example: Assuming you have a `FoodItem` model
        $foodItem = new \App\Models\FoodItem();
        $foodItem->img = $imagePath;
        $foodItem->save();

        return redirect()->back()->with('success', 'Image uploaded successfully!');
    }
}
