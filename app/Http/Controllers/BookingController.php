<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Property;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function store(Request $request, $propertyId)
    {
        $request->validate([
            'room_id' => 'nullable|exists:rooms,id',
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'message' => 'nullable|string|max:1000',
            'check_in_date' => 'nullable|date',
            'check_out_date' => 'nullable|date|after:check_in_date',
        ]);

        $property = Property::findOrFail($propertyId);
        $user = $request->user();

        // Validate room belongs to property if room_id is provided
        if ($request->room_id) {
            $room = \App\Models\Room::where('id', $request->room_id)
                ->where('property_id', $propertyId)
                ->firstOrFail();
            
            // Check if room is available and has capacity
            if (!$room->is_available) {
                return response()->json([
                    'message' => 'This room is currently not available for booking.'
                ], 422);
            }
            
            if ($room->isFullyOccupied()) {
                return response()->json([
                    'message' => 'This room is fully occupied. No more bookings can be accepted.'
                ], 422);
            }
        }

        // Use user data from database if not provided
        $booking = Booking::create([
            'property_id' => $propertyId,
            'room_id' => $request->room_id,
            'user_id' => $user->id,
            'name' => $request->name ?? $user->name,
            'email' => $request->email ?? $user->email,
            'phone' => $request->phone ?? $user->phone,
            'message' => $request->message,
            'check_in_date' => $request->check_in_date,
            'check_out_date' => $request->check_out_date,
        ]);

        return response()->json([
            'booking' => $booking->load('property'),
            'message' => 'Booking request submitted successfully'
        ], 201);
    }

    public function index(Request $request)
    {
        $query = Booking::with(['property.images', 'user', 'room']);

        if ($request->user()->isAdmin()) {
            // Admin sees all bookings
        } else {
            // Users see their own bookings and bookings for their properties
            $query->where(function($q) use ($request) {
                $q->where('user_id', $request->user()->id)
                  ->orWhereHas('property', function($q) use ($request) {
                      $q->where('user_id', $request->user()->id);
                  });
            });
        }

        $bookings = $query->orderBy('created_at', 'desc')->get();

        return response()->json($bookings);
    }

    public function update(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);

        // Only property owner or admin can update status
        if ($booking->property->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'status' => 'sometimes|in:pending,approved,rejected,completed',
            'is_paid' => 'sometimes|boolean',
        ]);

        $updateData = [];
        if ($request->has('status')) {
            $updateData['status'] = $request->status;
        }
        if ($request->has('is_paid')) {
            $updateData['is_paid'] = $request->is_paid;
        }

        $booking->update($updateData);

        return response()->json([
            'booking' => $booking->load('property', 'user', 'room'),
            'message' => 'Booking status updated successfully'
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);

        if ($booking->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $booking->delete();

        return response()->json(['message' => 'Booking deleted successfully']);
    }
}

