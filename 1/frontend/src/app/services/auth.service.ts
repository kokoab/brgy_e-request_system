import { Injectable, signal } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { Observable, tap } from 'rxjs';
import { environment } from '../../environments/environment';

export interface User {
  id: number;
  name: string;
  email: string;
  role: 'requestor' | 'staff' | 'admin';
  phone?: string;
  birthday?: string;
}

export interface AuthResponse {
  user: User;
  token: string;
  message: string;
}

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private apiUrl = environment.apiUrl;
  currentUser = signal<User | null>(null);
  isAuthenticated = signal<boolean>(false);
  private authCheckComplete = signal<boolean>(false);

  constructor(
    private http: HttpClient,
    private router: Router
  ) {
    this.checkAuth();
  }

  register(name: string, birthday: string, phone: string, email: string, password: string, passwordConfirmation: string): Observable<AuthResponse> {
    return this.http.post<AuthResponse>(`${this.apiUrl}/register`, {
      name,
      birthday,
      phone,
      email,
      password,
      password_confirmation: passwordConfirmation
    }).pipe(
      tap(response => this.handleAuthResponse(response))
    );
  }

  login(email: string, password: string): Observable<AuthResponse> {
    return this.http.post<AuthResponse>(`${this.apiUrl}/login`, {
      email,
      password
    }).pipe(
      tap(response => this.handleAuthResponse(response))
    );
  }

  logout(): Observable<any> {
    return this.http.post(`${this.apiUrl}/logout`, {}).pipe(
      tap(() => {
        this.clearAuth();
        this.router.navigate(['/login']);
      })
    );
  }

  getUser(): Observable<{ user: User }> {
    return this.http.get<{ user: User }>(`${this.apiUrl}/user`).pipe(
      tap(response => {
        this.currentUser.set(response.user);
        this.isAuthenticated.set(true);
      })
    );
  }

  private handleAuthResponse(response: AuthResponse): void {
    localStorage.setItem('auth_token', response.token);
    this.currentUser.set(response.user);
    this.isAuthenticated.set(true);
    this.authCheckComplete.set(true);
  }

  private clearAuth(): void {
    localStorage.removeItem('auth_token');
    this.currentUser.set(null);
    this.isAuthenticated.set(false);
    this.authCheckComplete.set(true);
  }

  private checkAuth(): void {
    const token = localStorage.getItem('auth_token');
    if (token) {
      // Optimistically assume authenticated if token exists
      // This prevents logout on page refresh while token is being verified
      this.isAuthenticated.set(true);
      
      // Try to verify token by getting user info
      this.getUser().subscribe({
        next: (response) => {
          // Token is valid, user is authenticated
          // State is already set by getUser's tap operator
          this.authCheckComplete.set(true);
        },
        error: (error) => {
          // Only clear auth if it's an authentication error (401/403)
          const status = error?.status;
          if (status === 401 || status === 403) {
            // Token is invalid or expired - clear everything
            console.warn('Token validation failed - authentication error:', status);
            this.clearAuth();
          } else {
            // Network error or other issue
            // Keep optimistic auth state but mark check as complete
            // The user might still be authenticated, we just couldn't verify
            console.warn('Auth check failed - network/other error:', error);
            this.authCheckComplete.set(true);
            // isAuthenticated is already set to true optimistically
          }
        }
      });
    } else {
      // No token, user is not authenticated
      this.isAuthenticated.set(false);
      this.currentUser.set(null);
      this.authCheckComplete.set(true);
    }
  }

  isAuthCheckComplete(): boolean {
    return this.authCheckComplete();
  }

  /**
   * Wait for auth check to complete and return authentication status
   * This is useful for guards that need to wait for the initial auth check
   */
  async waitForAuthCheck(): Promise<boolean> {
    if (this.authCheckComplete()) {
      return this.isAuthenticated();
    }

    // Wait for auth check to complete (max 3 seconds)
    const maxWait = 3000;
    const checkInterval = 50;
    const startTime = Date.now();

    while (!this.authCheckComplete() && (Date.now() - startTime) < maxWait) {
      await new Promise(resolve => setTimeout(resolve, checkInterval));
    }

    return this.isAuthenticated();
  }

  getToken(): string | null {
    return localStorage.getItem('auth_token');
  }

  // Role helper methods
  isRequestor(): boolean {
    return this.currentUser()?.role === 'requestor';
  }

  isStaff(): boolean {
    return this.currentUser()?.role === 'staff';
  }

  isAdmin(): boolean {
    return this.currentUser()?.role === 'admin';
  }

  isStaffOrAdmin(): boolean {
    const role = this.currentUser()?.role;
    return role === 'staff' || role === 'admin';
  }
}