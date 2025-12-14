import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { DocumentRequestService, DocumentRequest } from '../../../services/document-request.service';

@Component({
  selector: 'app-requestor-dashboard',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './requestor-dashboard.html'
})
export class RequestorDashboardComponent implements OnInit {
  documentRequests: DocumentRequest[] = [];
  filteredRequests: DocumentRequest[] = [];
  loading = false;
  error = '';
  showRequestForm = false;
  documentType = '';
  message = '';
  documentTypes = ['clearance', 'indigency', 'residence', 'recognition'];
  selectedFilter: 'all' | 'pending' | 'approved' | 'rejected' = 'all';

  constructor(private documentService: DocumentRequestService) {}

  ngOnInit(): void {
    this.loadRequests();
  }

  loadRequests(): void {
    this.loading = true;
    this.documentService.getRequests().subscribe({
      next: (response) => {
        this.documentRequests = response.data;
        this.applyFilter();
        this.loading = false;
      },
      error: (err) => {
        this.error = err.error?.message || 'Failed to load requests';
        this.loading = false;
      }
    });
  }

  setFilter(filter: 'all' | 'pending' | 'approved' | 'rejected'): void {
    this.selectedFilter = filter;
    this.applyFilter();
  }

  applyFilter(): void {
    if (this.selectedFilter === 'all') {
      this.filteredRequests = this.documentRequests;
    } else {
      this.filteredRequests = this.documentRequests.filter(
        request => request.document_status === this.selectedFilter
      );
    }
  }

  submitRequest(): void {
    if (!this.documentType.trim()) {
      this.error = 'Please select a document type';
      return;
    }

    this.loading = true;
    this.error = '';
    this.documentService.createRequest(this.documentType, this.message).subscribe({
      next: () => {
        this.documentType = '';
        this.message = '';
        this.showRequestForm = false;
        this.loadRequests();
      },
      error: (err) => {
        this.error = err.error?.message || 'Failed to create request';
        this.loading = false;
      }
    });
  }

  getStatusClass(status: string): string {
    switch (status) {
      case 'approved': return 'status-approved';
      case 'rejected': return 'status-rejected';
      default: return 'status-pending';
    }
  }
}
