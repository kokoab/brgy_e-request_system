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
}
