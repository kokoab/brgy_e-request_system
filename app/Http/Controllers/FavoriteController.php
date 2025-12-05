<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Property;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function toggle(Request $request, $propertyId)
    {
        $property = Property::findOrFail($propertyId);

        $favorite = Favorite::where('property_id', $propertyId)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($favorite) {
            $favorite->delete();
            return response()->json(['message' => 'Removed from favorites', 'is_favorited' => false]);
        } else {
            Favorite::create([
                'property_id' => $propertyId,
                'user_id' => $request->user()->id,
            ]);
            return response()->json(['message' => 'Added to favorites', 'is_favorited' => true]);
        }
    }

    public function index(Request $request)
    {
        $favorites = Favorite::with(['property.images', 'property.user', 'property.reviews'])
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($favorites);
    }

    public function check(Request $request, $propertyId)
    {
        $isFavorited = Favorite::where('property_id', $propertyId)
            ->where('user_id', $request->user()->id)
            ->exists();

        return response()->json(['is_favorited' => $isFavorited]);
    }
}

