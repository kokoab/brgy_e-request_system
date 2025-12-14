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
  loading = false;
  error = '';
  showRequestForm = false;
  documentType = '';
  message = '';
  documentTypes = ['clearance', 'indigency', 'residence', 'recognition'];

  constructor(private documentService: DocumentRequestService) {}

  ngOnInit(): void {
    this.loadRequests();
  }

  loadRequests(): void {
    this.loading = true;
    this.documentService.getRequests().subscribe({
      next: (response) => {
        this.documentRequests = response.data;
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
