import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { DocumentRequestService, DocumentRequest, PaginationInfo } from '../../../services/document-request.service';

@Component({
  selector: 'app-requestor-dashboard',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './requestor-dashboard.html'
})
export class RequestorDashboardComponent implements OnInit {
  documentRequests: DocumentRequest[] = [];
  documentTypes: string[] = [];
  loading = false;
  error = '';
  success = '';
  showRequestForm = false;
  documentType = '';
  description = '';
  
  // Search and filter
  searchTerm = '';
  statusFilter = '';
  documentTypeFilter = '';
  
  // Pagination
  pagination: PaginationInfo | null = null;
  currentPage = 1;
  perPage = 15;

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

  submitRequest(): void {
    if (!this.documentType.trim()) {
      this.error = 'Please select a document type';
      return;
    }

    this.loading = true;
    this.error = '';
    this.documentService.createRequest(this.documentType, this.description).subscribe({
      next: () => {
        this.documentType = '';
        this.description = '';
        this.showRequestForm = false;
        this.success = 'Request created successfully';
        setTimeout(() => this.success = '', 3000);
        this.loadRequests(1);
      },
      error: (err) => {
        this.error = err.error?.message || 'Failed to create request';
        this.loading = false;
      }
    });
  }

  cancelRequest(requestId: number): void {
    if (!confirm('Are you sure you want to cancel this request?')) {
      return;
    }

    this.loading = true;
    this.error = '';
    this.documentService.cancelRequest(requestId).subscribe({
      next: () => {
        this.success = 'Request cancelled successfully';
        setTimeout(() => this.success = '', 3000);
        this.loadRequests(this.currentPage);
      },
      error: (err) => {
        this.error = err.error?.message || 'Failed to cancel request';
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
    this.loadRequests(1);
  }

  goToPage(page: number): void {
    if (page >= 1 && this.pagination && page <= this.pagination.last_page) {
      this.loadRequests(page);
    }
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
