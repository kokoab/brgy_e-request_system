<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DocumentRequest;
use App\Models\User;

class DocumentRequestController extends Controller
{
    // Available document types
    public static function getDocumentTypes(): array
    {
        return [
            'Clearance Certificate',
            'Residence Certificate',
            'Indigency Certificate'
        ];
    }

    public function getDocumentTypesEndpoint()
    {
        return response()->json([
            'document_types' => self::getDocumentTypes()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'document_type' => 'required|string|in:' . implode(',', self::getDocumentTypes()),
            'description' => 'nullable|string|max:2000'
        ]);

        $user = $request->user();

        $documentData = [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'birthday' => $user->birthday,
        ];  

        $documentRequest = DocumentRequest::create([
            'user_id' => $user->id,
            'document_type' => $request->document_type,
            'description' => $request->input('description'),
            'document_data' => $documentData,
        ]);
        return response()->json($documentRequest, 201);
    }

    public function index(Request $request)
    {
        $user = $request->user();
        
        $query = DocumentRequest::query();
        
        // Requestors see only their requests, Staff/Admin see all
        if ($user->isRequestor()) {
            $query->where('user_id', $user->id);
        }
        
        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('document_type', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }
        
        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('document_status', $request->status);
        }
        
        // Filter by document type
        if ($request->has('document_type') && $request->document_type) {
            $query->where('document_type', $request->document_type);
        }
        
        // Date range filter
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        // Eager load user relationship
        if ($user->isRequestor()) {
            $query->with('user:id,name,email');
        } else {
            $query->with('user:id,name,email,role');
        }
        
        // Order by newest first
        $query->orderBy('created_at', 'desc');
        
        // Pagination
        $perPage = $request->get('per_page', 15);
        $documentRequests = $query->paginate($perPage);
        
        return response()->json([
            'data' => $documentRequests->items(),
            'pagination' => [
                'current_page' => $documentRequests->currentPage(),
                'last_page' => $documentRequests->lastPage(),
                'per_page' => $documentRequests->perPage(),
                'total' => $documentRequests->total(),
                'from' => $documentRequests->firstItem(),
                'to' => $documentRequests->lastItem(),
            ],
            'message' => 'Document requests fetched successfully'
        ]);
    }

    public function approve(Request $request)
    {
        $user = $request->user();
        
        // Only staff can approve
        if (!$user->isStaff()) {
            return response()->json([
                'message' => 'Only staff members can approve document requests'
            ], 403);
        }

        $request->validate([
            'document_request_id' => 'required|exists:document_requests,id',
            'message' => 'nullable|string|max:1000',
        ]);

        $documentRequest = DocumentRequest::find($request->document_request_id);
        $documentRequest->update([
            'document_status' => 'approved',
            'staff_message' => $request->input('message')
        ]);
        
        return response()->json([
            'message' => 'Document request approved successfully',
            'data' => $documentRequest->load('user:id,name,email')
        ]);
    }   

    public function reject(Request $request)
    {
        $user = $request->user();
        
        // Only staff can reject
        if (!$user->isStaff()) {
            return response()->json([
                'message' => 'Only staff members can reject document requests'
            ], 403);
        }

        $request->validate([
            'document_request_id' => 'required|exists:document_requests,id',
            'message' => 'nullable|string|max:1000',
        ]);

        $documentRequest = DocumentRequest::find($request->document_request_id);
        $documentRequest->update([
            'document_status' => 'rejected',
            'staff_message' => $request->input('message')
        ]);
        
        return response()->json([
            'message' => 'Document request rejected successfully',
            'data' => $documentRequest->load('user:id,name,email')
        ]);
    }

    public function cancel(Request $request)
    {
        $user = $request->user();
        
        $request->validate([
            'document_request_id' => 'required|exists:document_requests,id',
        ]);

        $documentRequest = DocumentRequest::find($request->document_request_id);
        
        // Only the owner can cancel, and only if pending
        if ($documentRequest->user_id !== $user->id) {
            return response()->json([
                'message' => 'You can only cancel your own requests'
            ], 403);
        }
        
        if ($documentRequest->document_status !== 'pending') {
            return response()->json([
                'message' => 'Only pending requests can be cancelled'
            ], 400);
        }
        
        $documentRequest->update([
            'document_status' => 'cancelled'
        ]);
        
        return response()->json([
            'message' => 'Document request cancelled successfully',
            'data' => $documentRequest->load('user:id,name,email')
        ]);
    }

    public function overview(Request $request)
    {
        $user = $request->user();
        
        // Only admin can see overview
        if (!$user->isAdmin()) {
            return response()->json([
                'message' => 'Only administrators can view the overview'
            ], 403);
        }

        $stats = [
            'total_requests' => DocumentRequest::count(),
            'pending_requests' => DocumentRequest::where('document_status', 'pending')->count(),
            'approved_requests' => DocumentRequest::where('document_status', 'approved')->count(),
            'rejected_requests' => DocumentRequest::where('document_status', 'rejected')->count(),
            'total_users' => User::count(),
            'requestors' => User::where('role', 'requestor')->count(),
            'staff' => User::where('role', 'staff')->count(),
            'admins' => User::where('role', 'admin')->count(),
        ];

        $recentRequests = DocumentRequest::with('user:id,name,email,role')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'stats' => $stats,
            'recent_requests' => $recentRequests,
            'message' => 'Overview data fetched successfully'
        ]);
    }
}

