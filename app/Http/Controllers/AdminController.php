<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\User;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin');
    }

    public function dashboard()
    {
        $stats = [
            'total_properties' => Property::count(),
            'pending_properties' => Property::where('status', 'pending')->count(),
            'approved_properties' => Property::where('status', 'approved')->count(),
            'rejected_properties' => Property::where('status', 'rejected')->count(),
            'total_users' => User::where('role', 'user')->count(),
            'total_bookings' => Booking::count(),
            'pending_bookings' => Booking::where('status', 'pending')->count(),
        ];

        return response()->json($stats);
    }

    public function pendingProperties()
    {
        $properties = Property::with(['user', 'images'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($properties);
    }

    public function approveProperty($id)
    {
        $property = Property::findOrFail($id);
        $property->update(['status' => 'approved']);

        return response()->json([
            'property' => $property,
            'message' => 'Property approved successfully'
        ]);
    }

    public function rejectProperty(Request $request, $id)
    {
        $property = Property::findOrFail($id);
        $property->update(['status' => 'rejected']);

        return response()->json([
            'property' => $property,
            'message' => 'Property rejected successfully'
        ]);
    }

    public function allProperties(Request $request)
    {
        $query = Property::with(['user', 'images']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $properties = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json($properties);
    }

    public function allUsers()
    {
        $users = User::where('role', 'user')
            ->withCount(['properties', 'bookings'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($users);
    }

    public function allBookings()
    {
        $bookings = Booking::with(['property.images', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($bookings);
    }

    public function deleteProperty($id)
    {
        $property = Property::findOrFail($id);
        $property->delete();

        return response()->json(['message' => 'Property deleted successfully']);
    }
}

