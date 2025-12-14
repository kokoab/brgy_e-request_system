import { Component, OnInit, effect } from '@angular/core';
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
  constructor(public authService: AuthService) {
    // Ensure user data is loaded if we're authenticated but don't have user yet
    effect(() => {
      const isAuth = this.authService.isAuthenticated();
      const hasUser = !!this.authService.currentUser();
      const checkComplete = this.authService.isAuthCheckComplete();
      
      if (isAuth && !hasUser && checkComplete) {
        // We're authenticated but user data wasn't loaded, fetch it
        this.authService.getUser().subscribe();
      }
    });
  }

  ngOnInit(): void {
    // If authenticated but no user data, fetch it
    if (this.authService.isAuthenticated() && !this.authService.currentUser()) {
      this.authService.getUser().subscribe();
    }
  }

  logout(): void {
    this.authService.logout().subscribe();
  }
}
