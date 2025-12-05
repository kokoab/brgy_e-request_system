import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { DocumentRequestService, DocumentRequest, PaginationInfo } from '../../../services/document-request.service';

@Component({
  selector: 'app-staff-dashboard',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './staff-dashboard.html'
})
export class StaffDashboardComponent implements OnInit {
  documentRequests: DocumentRequest[] = [];
  documentTypes: string[] = [];
  loading = false;
  error = '';
  success = '';
  
  // Search and filter
  searchTerm = '';
  statusFilter = '';
  documentTypeFilter = '';
  dateFrom = '';
  dateTo = '';
  
  // Pagination
  pagination: PaginationInfo | null = null;
  currentPage = 1;
  perPage = 15;
  
  // Modal state
  showModal = false;
  currentRequestId: number | null = null;
  actionType: 'approve' | 'reject' | null = null;
  message = '';

  constructor(private documentService: DocumentRequestService) {}

  ngOnInit(): void {
    this.loadDocumentTypes();
    this.loadRequests();
  }

  loadDocumentTypes(): void {
    this.documentService.getDocumentTypes().subscribe({
      next: (response) => {
        this.documentTypes = response.document_types;
      },
      error: (err) => {
        console.error('Failed to load document types', err);
      }
    });
  }

  loadRequests(page: number = 1): void {
    this.loading = true;
    this.currentPage = page;
    this.error = '';
    
    const params: any = {
      page: this.currentPage,
      per_page: this.perPage
    };
    
    if (this.searchTerm.trim()) {
      params.search = this.searchTerm.trim();
    }
    
    if (this.statusFilter) {
      params.status = this.statusFilter;
    }
    
    if (this.documentTypeFilter) {
      params.document_type = this.documentTypeFilter;
    }
    
    if (this.dateFrom) {
      params.date_from = this.dateFrom;
    }
    
    if (this.dateTo) {
      params.date_to = this.dateTo;
    }
    
    this.documentService.getRequests(params).subscribe({
      next: (response) => {
        this.documentRequests = response.data;
        this.pagination = response.pagination;
        this.loading = false;
      },
      error: (err) => {
        this.error = err.error?.message || 'Failed to load requests';
        this.loading = false;
      }
    });
  }

  onSearch(): void {
    this.loadRequests(1);
  }

  onFilterChange(): void {
    this.loadRequests(1);
  }

  clearFilters(): void {
    this.searchTerm = '';
    this.statusFilter = '';
    this.documentTypeFilter = '';
    this.dateFrom = '';
    this.dateTo = '';
    this.loadRequests(1);
  }

  goToPage(page: number): void {
    if (page >= 1 && this.pagination && page <= this.pagination.last_page) {
      this.loadRequests(page);
    }
  }

  openApproveModal(requestId: number): void {
    this.currentRequestId = requestId;
    this.actionType = 'approve';
    this.message = '';
    this.showModal = true;
  }

  openRejectModal(requestId: number): void {
    this.currentRequestId = requestId;
    this.actionType = 'reject';
    this.message = '';
    this.showModal = true;
  }

  closeModal(): void {
    this.showModal = false;
    this.currentRequestId = null;
    this.actionType = null;
    this.message = '';
  }

  submitAction(): void {
    if (!this.currentRequestId || !this.actionType) {
      return;
    }

    this.loading = true;
    this.error = '';
    this.success = '';

    const message = this.message.trim() || undefined;
    const requestId = this.currentRequestId;

    const request = this.actionType === 'approve'
      ? this.documentService.approveRequest(requestId, message)
      : this.documentService.rejectRequest(requestId, message);

    request.subscribe({
      next: () => {
        this.success = `Request ${this.actionType}d successfully`;
        this.closeModal();
        this.loadRequests();
        setTimeout(() => this.success = '', 3000);
      },
      error: (err) => {
        this.error = err.error?.message || `Failed to ${this.actionType} request`;
        this.loading = false;
      }
    });
  }

  getStatusClass(status: string): string {
    switch (status) {
      case 'approved': return 'status-approved';
      case 'rejected': return 'status-rejected';
      case 'cancelled': return 'status-cancelled';
      default: return 'status-pending';
    }
  }
}
