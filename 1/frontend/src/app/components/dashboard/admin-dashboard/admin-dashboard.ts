import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { DocumentRequestService, OverviewStats, DocumentRequest } from '../../../services/document-request.service';

@Component({
  selector: 'app-admin-dashboard',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './admin-dashboard.html'
})
export class AdminDashboardComponent implements OnInit {
  stats: OverviewStats | null = null;
  recentRequests: DocumentRequest[] = [];
  loading = false;
  error = '';

  constructor(private documentService: DocumentRequestService) {}

  ngOnInit(): void {
    this.loadOverview();
  }

  loadOverview(): void {
    this.loading = true;
    this.error = '';
    this.documentService.getOverview().subscribe({
      next: (response) => {
        this.stats = response.stats;
        this.recentRequests = response.recent_requests;
        this.loading = false;
      },
      error: (err) => {
        this.error = err.error?.message || 'Failed to load overview';
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
