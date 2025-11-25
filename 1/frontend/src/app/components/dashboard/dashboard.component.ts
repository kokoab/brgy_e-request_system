import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { AuthService } from '../../services/auth.service';
import { RequestorDashboardComponent } from './requestor-dashboard/requestor-dashboard';
import { StaffDashboardComponent } from './staff-dashboard/staff-dashboard';
import { AdminDashboardComponent } from './admin-dashboard/admin-dashboard';

@Component({
  selector: 'app-dashboard',
  standalone: true,
  imports: [CommonModule, RequestorDashboardComponent, StaffDashboardComponent, AdminDashboardComponent],
  templateUrl: './dashboard.component.html'
})
export class DashboardComponent implements OnInit {
  constructor(public authService: AuthService) {}

  ngOnInit(): void {
    // User data is already loaded by the service
  }

  logout(): void {
    this.authService.logout().subscribe();
  }
}
