<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DocumentRequest;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;

class DocumentRequestController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'document_type' => 'required|string|in:clearance,indigency,residence,recognition',
            'message' => 'nullable|string|max:1000'
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
            'document_data' => $documentData,
            'requestor_message' => $request->input('message'),
        ]);
        return response()->json($documentRequest, 201);
    }

    public function index(Request $request)
    {
        $user = $request->user();

        // Requestors see only their requests, Staff/Admin see all
        if ($user->isRequestor()) {
            $documentRequests = DocumentRequest::where('user_id', $user->id)
                ->with('user:id,name,email')
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            // Staff and Admin see all requests - newest first
            $documentRequests = DocumentRequest::with('user:id,name,email,role')
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return response()->json([
            'data' => $documentRequests,
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

    public function downloadPdf(Request $request, $id)
    {
        // Find the document request
        $documentRequest = DocumentRequest::with('user')->find($id);

        // Check if document request exists
        if (!$documentRequest) {
            return response()->json([
                'message' => 'Document request not found'
            ], 404);
        }

        // Check authorization: Only staff and admin can download documents
        // Requestors cannot download - only staff can
        $user = $request->user();

        if (!$user->isStaffOrAdmin()) {
            return response()->json([
                'message' => 'Only staff members can download documents'
            ], 403);
        }

        // Staff can download documents regardless of status (approved, pending, or rejected)
        // No need to check document_status anymore

        // Determine template based on document type using switch statement
        // This allows different templates for different document types
        // IMPORTANT: Templates are in resources/views/templates/ folder
        switch (strtolower($documentRequest->document_type)) {
            case 'clearance':
                $template = 'templates.clearance';
                break;

            case 'indigency':
                $template = 'templates.indigency';
                break;

            case 'residence':
                $template = 'templates.residence';
                break;

            case 'recognition':
                $template = 'templates.recognition';
                break;

            default:
                // Fallback to clearance template if document type doesn't match
                // This handles any unexpected document types gracefully
                $template = 'templates.clearance';
                break;
        }

        // Note: If you add a new document type:
        // 1. Add a case in this switch statement
        // 2. Create the corresponding template file in resources/views/templates/
        // 3. Update the validation rules in store() method if needed

        // Prepare data for the template
        $data = [
            'documentRequest' => $documentRequest,
        ];

        // Generate PDF
        try {
            $pdf = Pdf::loadView($template, $data);

            // Set PDF options
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOption('enable-local-file-access', true);
            $pdf->setOption('isRemoteEnabled', true);
            $pdf->setOption('isHtml5ParserEnabled', true);

            // Generate filename
            $filename = str_replace(' ', '-', strtolower($documentRequest->document_type))
                . '-' . $documentRequest->id
                . '-' . date('Y-m-d')
                . '.pdf';

            // Return as download
            return $pdf->download($filename);

            // Alternative: Return as stream (opens in browser)
            // return $pdf->stream($filename);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error generating PDF: ' . $e->getMessage()
            ], 500);
        }
    }
}
