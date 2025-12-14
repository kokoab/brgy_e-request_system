import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { DocumentRequestService, DocumentRequest } from '../../../services/document-request.service';

@Component({
  selector: 'app-staff-dashboard',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './staff-dashboard.html'
})
export class StaffDashboardComponent implements OnInit {
  documentRequests: DocumentRequest[] = [];
  loading = false;
  error = '';
  success = '';
  downloadingId: number | null = null; // Track which document is downloading
  
  // Modal state
  showModal = false;
  currentRequestId: number | null = null;
  actionType: 'approve' | 'reject' | null = null;
  message = '';

  constructor(private documentService: DocumentRequestService) {}

  ngOnInit(): void {
    this.loadRequests();
  }

  loadRequests(): void {
    this.loading = true;
    this.error = '';
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
      default: return 'status-pending';
    }
  }

  /**
   * Download PDF document for any request (staff can download all, regardless of status)
   * @param documentRequestId The ID of the document request to download
   */
  downloadDocument(documentRequestId: number): void {
    // Set downloading state
    this.downloadingId = documentRequestId;
    this.error = '';

    this.documentService.downloadPdf(documentRequestId).subscribe({
      next: (blob: Blob) => {
        // Create a temporary URL for the blob
        const url = window.URL.createObjectURL(blob);
        
        // Create a temporary anchor element
        const link = document.createElement('a');
        link.href = url;
        
        // Generate filename from blob or use default
        const filename = `document-${documentRequestId}-${new Date().getTime()}.pdf`;
        link.download = filename;
        
        // Append to body, click, then remove
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        // Clean up the temporary URL
        window.URL.revokeObjectURL(url);
        
        // Reset downloading state
        this.downloadingId = null;
      },
      error: (err) => {
        console.error('Download failed:', err);
        this.error = err.error?.message || 'Failed to download document';
        this.downloadingId = null;
        
        // Handle blob error (if backend returns JSON error in blob)
        if (err.error instanceof Blob) {
          err.error.text().then((text: string) => {
            try {
              const errorObj = JSON.parse(text);
              this.error = errorObj.message || 'Failed to download document';
            } catch {
              this.error = 'Failed to download document';
            }
          });
        }
      }
    });
  }
}
