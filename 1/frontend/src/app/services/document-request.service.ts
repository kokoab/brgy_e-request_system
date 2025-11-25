import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';

export interface DocumentRequest {
  id: number;
  user_id: number;
  document_type: string;
  document_data: {
    name: string;
    email: string;
    phone: string;
    birthday: string;
  };
  document_status: 'pending' | 'approved' | 'rejected';
  staff_message?: string | null;
  created_at: string;
  updated_at: string;
  user?: {
    id: number;
    name: string;
    email: string;
    role?: string;
  };
}

export interface OverviewStats {
  total_requests: number;
  pending_requests: number;
  approved_requests: number;
  rejected_requests: number;
  total_users: number;
  requestors: number;
  staff: number;
  admins: number;
}

@Injectable({
  providedIn: 'root'
})
export class DocumentRequestService {
  private apiUrl = environment.apiUrl;

  constructor(private http: HttpClient) {}

  createRequest(documentType: string): Observable<DocumentRequest> {
    return this.http.post<DocumentRequest>(`${this.apiUrl}/document-request`, {
      document_type: documentType
    });
  }

  getRequests(): Observable<{ data: DocumentRequest[]; message: string }> {
    return this.http.get<{ data: DocumentRequest[]; message: string }>(`${this.apiUrl}/document-requests`);
  }

  approveRequest(documentRequestId: number, message?: string): Observable<{ message: string; data: DocumentRequest }> {
    return this.http.post<{ message: string; data: DocumentRequest }>(
      `${this.apiUrl}/document-request/approve`,
      { 
        document_request_id: documentRequestId,
        message: message || null
      }
    );
  }

  rejectRequest(documentRequestId: number, message?: string): Observable<{ message: string; data: DocumentRequest }> {
    return this.http.post<{ message: string; data: DocumentRequest }>(
      `${this.apiUrl}/document-request/reject`,
      { 
        document_request_id: documentRequestId,
        message: message || null
      }
    );
  }

  getOverview(): Observable<{
    stats: OverviewStats;
    recent_requests: DocumentRequest[];
    message: string;
  }> {
    return this.http.get<{
      stats: OverviewStats;
      recent_requests: DocumentRequest[];
      message: string;
    }>(`${this.apiUrl}/admin/overview`);
  }
}

