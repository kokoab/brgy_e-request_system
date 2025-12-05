import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';

export interface DocumentRequest {
  id: number;
  user_id: number;
  document_type: string;
  description?: string | null;
  document_data: {
    name: string;
    email: string;
    phone: string;
    birthday: string;
  };
  document_status: 'pending' | 'approved' | 'rejected' | 'cancelled';
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

export interface PaginationInfo {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from: number | null;
  to: number | null;
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

  getDocumentTypes(): Observable<{ document_types: string[] }> {
    return this.http.get<{ document_types: string[] }>(`${this.apiUrl}/document-types`);
  }

  createRequest(documentType: string, description?: string): Observable<DocumentRequest> {
    return this.http.post<DocumentRequest>(`${this.apiUrl}/document-request`, {
      document_type: documentType,
      description: description || null
    });
  }

  getRequests(params?: {
    search?: string;
    status?: string;
    document_type?: string;
    date_from?: string;
    date_to?: string;
    page?: number;
    per_page?: number;
  }): Observable<{ 
    data: DocumentRequest[]; 
    pagination: PaginationInfo;
    message: string;
  }> {
    let url = `${this.apiUrl}/document-requests`;
    const queryParams = new URLSearchParams();
    
    if (params) {
      if (params.search) queryParams.append('search', params.search);
      if (params.status) queryParams.append('status', params.status);
      if (params.document_type) queryParams.append('document_type', params.document_type);
      if (params.date_from) queryParams.append('date_from', params.date_from);
      if (params.date_to) queryParams.append('date_to', params.date_to);
      if (params.page) queryParams.append('page', params.page.toString());
      if (params.per_page) queryParams.append('per_page', params.per_page.toString());
    }
    
    if (queryParams.toString()) {
      url += '?' + queryParams.toString();
    }
    
    return this.http.get<{ 
      data: DocumentRequest[]; 
      pagination: PaginationInfo;
      message: string;
    }>(url);
  }

  cancelRequest(documentRequestId: number): Observable<{ message: string; data: DocumentRequest }> {
    return this.http.post<{ message: string; data: DocumentRequest }>(
      `${this.apiUrl}/document-request/cancel`,
      { document_request_id: documentRequestId }
    );
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

