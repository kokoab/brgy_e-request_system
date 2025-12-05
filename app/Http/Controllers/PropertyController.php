<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\PropertyImage;
use App\Models\Room;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class PropertyController extends Controller
{
    public function index(Request $request)
    {
        $query = Property::with(['user', 'images', 'reviews', 'rooms.bookings' => function($q) {
            $q->where('status', 'approved');
        }])
            ->where('status', 'approved');

        // Search by title or description
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        // Filter by price range
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Filter by property type
        if ($request->has('property_type')) {
            $query->where('property_type', $request->property_type);
        }

        // Filter by city
        if ($request->has('city')) {
            $query->where('city', 'like', "%{$request->city}%");
        }

        // Filter by amenities
        if ($request->has('amenities')) {
            $amenities = is_array($request->amenities) ? $request->amenities : [$request->amenities];
            foreach ($amenities as $amenity) {
                $query->whereJsonContains('amenities', $amenity);
            }
        }

        // Distance-based search (requires latitude and longitude)
        if ($request->has('latitude') && $request->has('longitude') && $request->has('radius')) {
            $latitude = $request->latitude;
            $longitude = $request->longitude;
            $radius = $request->radius ?? 10; // default 10km

            $query->selectRaw(
                "*, (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance",
                [$latitude, $longitude, $latitude]
            )
            ->having('distance', '<=', $radius)
            ->orderBy('distance');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $perPage = $request->get('per_page', 12);
        $properties = $query->paginate($perPage);

        return response()->json($properties);
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'price' => 'required|numeric|min:0',
                'address' => 'required|string',
                'city' => 'required|string',
                'state' => 'nullable|string',
                'zip_code' => 'nullable|string',
                'country' => 'nullable|string',
                'latitude' => 'nullable|numeric',
                'longitude' => 'nullable|numeric',
                'bedrooms' => 'nullable|integer|min:0',
                'bathrooms' => 'nullable|integer|min:0',
                'capacity' => 'nullable|integer|min:1',
                'amenities' => 'nullable|string',
                'property_type' => 'nullable|in:boarding_house,apartment,dormitory,other',
                'contact_phone' => 'nullable|string',
                'contact_email' => 'nullable|email',
                'images' => 'nullable|array',
                'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:10240',
                'rooms' => 'nullable|string', // JSON string
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Handle amenities - decode if it's a JSON string
            $amenities = [];
            if ($request->has('amenities') && $request->amenities) {
                if (is_string($request->amenities)) {
                    $decoded = json_decode($request->amenities, true);
                    $amenities = is_array($decoded) ? $decoded : [];
                } elseif (is_array($request->amenities)) {
                    $amenities = $request->amenities;
                }
            }

            $property = Property::create([
                'user_id' => $request->user()->id,
                'title' => $request->title,
                'description' => $request->description,
                'price' => $request->price,
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state ?: null,
                'zip_code' => $request->zip_code ?: null,
                'country' => $request->country ?? 'Philippines',
                'latitude' => $request->latitude && $request->latitude !== '' ? (float) $request->latitude : null,
                'longitude' => $request->longitude && $request->longitude !== '' ? (float) $request->longitude : null,
                'bedrooms' => $request->bedrooms && $request->bedrooms !== '' ? (int) $request->bedrooms : null,
                'bathrooms' => $request->bathrooms && $request->bathrooms !== '' ? (int) $request->bathrooms : null,
                'capacity' => $request->capacity && $request->capacity !== '' ? (int) $request->capacity : null,
                'amenities' => $amenities,
                'property_type' => $request->property_type ?? 'boarding_house',
                'contact_phone' => $request->contact_phone ?: null,
                'contact_email' => $request->contact_email ?: null,
                'status' => 'approved',
            ]);

            // Handle image uploads
            if ($request->hasFile('images')) {
                $order = 0;
                foreach ($request->file('images') as $image) {
                    $path = $image->store('properties', 'public');
                    PropertyImage::create([
                        'property_id' => $property->id,
                        'image_path' => $path,
                        'is_primary' => $order === 0,
                        'order' => $order++,
                    ]);
                }
            }

            // Handle rooms - decode JSON if it's a string
            if ($request->has('rooms') && $request->rooms) {
                $roomsData = [];
                if (is_string($request->rooms)) {
                    $decoded = json_decode($request->rooms, true);
                    $roomsData = is_array($decoded) ? $decoded : [];
                } elseif (is_array($request->rooms)) {
                    $roomsData = $request->rooms;
                }
                
                if (!empty($roomsData)) {
                    $order = 0;
                    foreach ($roomsData as $roomData) {
                        if (!empty($roomData['name']) && !empty($roomData['price'])) {
                            Room::create([
                                'property_id' => $property->id,
                                'name' => $roomData['name'],
                                'description' => $roomData['description'] ?? null,
                                'price' => $roomData['price'],
                                'capacity' => $roomData['capacity'] ?? 1,
                                'is_available' => true,
                                'order' => $order++,
                            ]);
                        }
                    }
                }
            }

            DB::commit();

            return response()->json([
                'property' => $property->load(['images', 'rooms']),
                'message' => 'Property created and listed successfully'
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Property creation error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Error creating property: ' . $e->getMessage(),
                'error' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }

    public function show($id)
    {
        $property = Property::with(['user', 'images', 'reviews.user', 'rooms.bookings' => function($query) {
            $query->where('status', 'approved');
        }])
            ->findOrFail($id);
        
        // Get all bookings for this property (for property owner)
        // Try to get authenticated user (works even on public routes if token is provided)
        $currentUser = auth('sanctum')->user();
        $isOwner = $currentUser && ($property->user_id === $currentUser->id || $currentUser->isAdmin());
        
        if ($isOwner) {
            $allBookings = Booking::with(['user', 'room'])
                ->where('property_id', $property->id)
                ->orderBy('created_at', 'desc')
                ->get();
            
            // Convert to array to ensure relationships are serialized
            $property->all_bookings = $allBookings->map(function($booking) {
                return [
                    'id' => $booking->id,
                    'property_id' => $booking->property_id,
                    'room_id' => $booking->room_id,
                    'user_id' => $booking->user_id,
                    'name' => $booking->name,
                    'email' => $booking->email,
                    'phone' => $booking->phone,
                    'message' => $booking->message,
                    'status' => $booking->status,
                    'is_paid' => $booking->is_paid,
                    'check_in_date' => $booking->check_in_date,
                    'check_out_date' => $booking->check_out_date,
                    'created_at' => $booking->created_at,
                    'updated_at' => $booking->updated_at,
                    'user' => $booking->user ? [
                        'id' => $booking->user->id,
                        'name' => $booking->user->name,
                        'email' => $booking->user->email,
                        'phone' => $booking->user->phone,
                    ] : null,
                    'room' => $booking->room ? [
                        'id' => $booking->room->id,
                        'name' => $booking->room->name,
                    ] : null,
                ];
            })->toArray();
            
            // Get all bookings for each room
            if ($property->rooms) {
                foreach ($property->rooms as $room) {
                    $roomBookings = Booking::with('user')
                        ->where('room_id', $room->id)
                        ->orderBy('created_at', 'desc')
                        ->get();
                    
                    $room->all_bookings = $roomBookings->map(function($booking) {
                        return [
                            'id' => $booking->id,
                            'user_id' => $booking->user_id,
                            'status' => $booking->status,
                            'user' => $booking->user ? [
                                'id' => $booking->user->id,
                                'name' => $booking->user->name,
                                'email' => $booking->user->email,
                            ] : null,
                        ];
                    })->toArray();
                }
            }
        } else {
            // Set empty array for non-owners so the frontend doesn't break
            $property->all_bookings = [];
        }
        
        // Calculate occupancy for each room
        if ($property->rooms) {
            foreach ($property->rooms as $room) {
                $room->occupancy_count = $room->getOccupancyCount();
                $room->remaining_capacity = $room->getRemainingCapacity();
                $room->availability_status = $room->getAvailabilityStatus();
                $room->paid_bookings_count = $room->getPaidBookingsCount();
            }
        }
        
        // Add user info for frontend
        $property->current_user = $currentUser;
        $property->is_owner = $isOwner;

        if (!$property->isApproved() && (!$currentUser || ($currentUser->id !== $property->user_id && !$currentUser->isAdmin()))) {
            return response()->json(['message' => 'Property not found'], 404);
        }

        $property->average_rating = (float) $property->averageRating();
        $property->is_favorited = $currentUser 
            ? $property->favorites()->where('user_id', $currentUser->id)->exists()
            : false;

        return response()->json($property);
    }

    public function update(Request $request, $id)
    {
        $property = Property::findOrFail($id);

        // Only owner or admin can update
        if ($property->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Debug: Log all request data including files
        \Log::info('Property update request', [
            'property_id' => $id,
            'request_all' => $request->all(),
            'request_input' => $request->input(),
            'request_keys' => array_keys($request->all()),
            'has_title' => $request->has('title'),
            'title_value' => $request->input('title'),
            'filled_title' => $request->filled('title'),
            'method' => $request->method(),
            'content_type' => $request->header('Content-Type'),
            'current_property' => $property->toArray()
        ]);

        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'price' => 'sometimes|required|numeric|min:0',
            'address' => 'sometimes|required|string',
            'city' => 'sometimes|required|string',
            'state' => 'nullable|string',
            'zip_code' => 'nullable|string',
            'country' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'bedrooms' => 'nullable|integer|min:0',
            'bathrooms' => 'nullable|integer|min:0',
            'capacity' => 'nullable|integer|min:1',
            'amenities' => 'nullable',
            'property_type' => 'nullable|in:boarding_house,apartment,dormitory,other',
            'contact_phone' => 'nullable|string',
            'contact_email' => 'nullable|email',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:10240',
            'rooms' => 'nullable|string', // JSON string
        ]);

        // Handle amenities - decode if it's a JSON string or comma-separated
        $amenities = [];
        if ($request->has('amenities') && $request->amenities) {
            if (is_string($request->amenities)) {
                // Try to decode as JSON first
                $decoded = json_decode($request->amenities, true);
                if (is_array($decoded)) {
                    $amenities = $decoded;
                } else {
                    // If not JSON, treat as comma-separated string
                    $amenities = array_filter(array_map('trim', explode(',', $request->amenities)));
                }
            } elseif (is_array($request->amenities)) {
                $amenities = $request->amenities;
            }
        }

        // Build update data array - FormData always sends fields, so check if they exist and have values
        $updateData = [];
        
        // Required fields - always update if present in request (FormData sends them)
        if ($request->has('title') && trim($request->title) !== '') {
            $updateData['title'] = trim($request->title);
        }
        if ($request->has('description') && trim($request->description) !== '') {
            $updateData['description'] = trim($request->description);
        }
        if ($request->has('price') && $request->price !== '' && $request->price !== null) {
            $updateData['price'] = $request->price;
        }
        if ($request->has('address') && trim($request->address) !== '') {
            $updateData['address'] = trim($request->address);
        }
        if ($request->has('city') && trim($request->city) !== '') {
            $updateData['city'] = trim($request->city);
        }
        if ($request->has('property_type') && $request->property_type !== '') {
            $updateData['property_type'] = $request->property_type;
        }
        
        // Optional fields - update if present (even if empty, set to null)
        if ($request->has('state')) {
            $updateData['state'] = trim($request->state) ?: null;
        }
        if ($request->has('zip_code')) {
            $updateData['zip_code'] = trim($request->zip_code) ?: null;
        }
        if ($request->has('country')) {
            $updateData['country'] = trim($request->country) ?: 'Philippines';
        }
        if ($request->has('latitude')) {
            $updateData['latitude'] = $request->latitude !== '' && $request->latitude !== null ? (float) $request->latitude : null;
        }
        if ($request->has('longitude')) {
            $updateData['longitude'] = $request->longitude !== '' && $request->longitude !== null ? (float) $request->longitude : null;
        }
        if ($request->has('bedrooms')) {
            $updateData['bedrooms'] = $request->bedrooms !== '' && $request->bedrooms !== null ? (int) $request->bedrooms : null;
        }
        if ($request->has('bathrooms')) {
            $updateData['bathrooms'] = $request->bathrooms !== '' && $request->bathrooms !== null ? (int) $request->bathrooms : null;
        }
        if ($request->has('capacity')) {
            $updateData['capacity'] = $request->capacity !== '' && $request->capacity !== null ? (int) $request->capacity : null;
        }
        if ($request->has('contact_phone')) {
            $updateData['contact_phone'] = trim($request->contact_phone) ?: null;
        }
        if ($request->has('contact_email')) {
            $updateData['contact_email'] = trim($request->contact_email) ?: null;
        }
        
        // Always set amenities (even if empty array)
        $updateData['amenities'] = $amenities;
        
        \Log::info('Updating property', [
            'property_id' => $property->id,
            'update_data' => $updateData,
            'update_data_count' => count($updateData)
        ]);
        
        if (empty($updateData)) {
            \Log::warning('No update data provided');
            return response()->json([
                'property' => $property->load(['images', 'rooms']),
                'message' => 'No changes to update'
            ]);
        }
        
        // Save the property - assign values directly then save to ensure it works
        foreach ($updateData as $key => $value) {
            $property->$key = $value;
        }
        $saved = $property->save();
        
        \Log::info('Property update result', [
            'property_id' => $property->id,
            'saved' => $saved,
            'wasChanged' => $property->wasChanged(),
            'getChanges' => $property->getChanges(),
            'property_after' => $property->fresh()->toArray()
        ]);
        
        // Refresh to get updated data from database
        $property->refresh();
        
        // Reload relationships
        $property->load(['images', 'rooms']);

        // Handle new image uploads
        if ($request->hasFile('images')) {
            $maxOrder = $property->images()->max('order') ?? -1;
            $order = $maxOrder + 1;
            foreach ($request->file('images') as $image) {
                $path = $image->store('properties', 'public');
                PropertyImage::create([
                    'property_id' => $property->id,
                    'image_path' => $path,
                    'is_primary' => false,
                    'order' => $order++,
                ]);
            }
        }

        // Handle rooms update
        if ($request->has('rooms') && $request->rooms) {
            $roomsData = [];
            if (is_string($request->rooms)) {
                $decoded = json_decode($request->rooms, true);
                $roomsData = is_array($decoded) ? $decoded : [];
            } elseif (is_array($request->rooms)) {
                $roomsData = $request->rooms;
            }
            
            if (is_array($roomsData) && !empty($roomsData)) {
                // Get existing room IDs
                $existingRoomIds = $property->rooms()->pluck('id')->toArray();
                $submittedRoomIds = [];
                
                foreach ($roomsData as $roomData) {
                    if (!empty($roomData['name']) && !empty($roomData['price'])) {
                        if (isset($roomData['id']) && $roomData['id']) {
                            // Update existing room
                            $room = Room::find($roomData['id']);
                            if ($room && $room->property_id === $property->id) {
                                $room->update([
                                    'name' => $roomData['name'],
                                    'price' => $roomData['price'],
                                    'capacity' => $roomData['capacity'] ?? 1,
                                    'description' => $roomData['description'] ?? null,
                                ]);
                                $submittedRoomIds[] = $room->id;
                            }
                        } else {
                            // Create new room
                            $maxOrder = $property->rooms()->max('order') ?? -1;
                            $newRoom = Room::create([
                                'property_id' => $property->id,
                                'name' => $roomData['name'],
                                'price' => $roomData['price'],
                                'capacity' => $roomData['capacity'] ?? 1,
                                'description' => $roomData['description'] ?? null,
                                'is_available' => true,
                                'order' => $maxOrder + 1,
                            ]);
                            $submittedRoomIds[] = $newRoom->id;
                        }
                    }
                }
                
                // Delete rooms that were removed (only if they don't have approved bookings)
                $roomsToDelete = array_diff($existingRoomIds, $submittedRoomIds);
                if (!empty($roomsToDelete)) {
                    Room::whereIn('id', $roomsToDelete)
                        ->where('property_id', $property->id)
                        ->whereDoesntHave('bookings', function($query) {
                            $query->where('status', 'approved');
                        })
                        ->delete();
                }
            }
        }

        return response()->json([
            'property' => $property->load(['images', 'rooms']),
            'message' => 'Property updated successfully'
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $property = Property::findOrFail($id);

        // Only owner or admin can delete
        if ($property->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Delete images
        foreach ($property->images as $image) {
            Storage::disk('public')->delete($image->image_path);
        }

        $property->delete();

        return response()->json(['message' => 'Property deleted successfully']);
    }

    public function myProperties(Request $request)
    {
        try {
            $properties = Property::with([
                'images', 
                'reviews', 
                'rooms.bookings' => function($q) {
                    $q->where('status', 'approved');
                },
                'rooms.bookings.user',
                'bookings.user',
                'bookings.room'
            ])
                ->where('user_id', $request->user()->id)
                ->orderBy('created_at', 'desc')
                ->get();
            
            // Calculate occupancy for each room and include all bookings
            foreach ($properties as $property) {
                // Get all bookings for this property
                try {
                    $property->all_bookings = Booking::with(['user', 'room'])
                        ->where('property_id', $property->id)
                        ->orderBy('created_at', 'desc')
                        ->get();
                } catch (\Exception $e) {
                    $property->all_bookings = collect([]);
                }
                
                if ($property->rooms) {
                    foreach ($property->rooms as $room) {
                        try {
                            $room->occupancy_count = $room->getOccupancyCount();
                            $room->remaining_capacity = $room->getRemainingCapacity();
                            $room->availability_status = $room->getAvailabilityStatus();
                            
                            // Get all bookings for this room with user info
                            $room->all_bookings = Booking::with('user')
                                ->where('room_id', $room->id)
                                ->orderBy('created_at', 'desc')
                                ->get();
                        } catch (\Exception $e) {
                            $room->occupancy_count = 0;
                            $room->remaining_capacity = $room->capacity ?? 0;
                            $room->availability_status = 'available';
                            $room->all_bookings = collect([]);
                        }
                    }
                }
            }

            return response()->json($properties);
        } catch (\Exception $e) {
            \Log::error('Error in myProperties: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Error loading properties: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateRoomAvailability(Request $request, $roomId)
    {
        $room = \App\Models\Room::with('property')->findOrFail($roomId);
        $property = $room->property;

        // Only property owner or admin can update room availability
        if ($property->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'is_available' => 'required|boolean',
        ]);

        $room->update([
            'is_available' => $request->is_available
        ]);

        // Reload with bookings to calculate occupancy
        $room->load('bookings');
        $room->occupancy_count = $room->getOccupancyCount();
        $room->remaining_capacity = $room->getRemainingCapacity();
        $room->availability_status = $room->getAvailabilityStatus();

        return response()->json([
            'room' => $room,
            'message' => 'Room availability updated successfully'
        ]);
    }
}

