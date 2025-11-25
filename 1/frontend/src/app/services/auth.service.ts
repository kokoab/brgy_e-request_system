import { Injectable, signal } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { Observable, tap } from 'rxjs';
import { environment } from '../../environments/environment';

export interface User {
  id: number;
  name: string;
  email: string;
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

  constructor(
    private http: HttpClient,
    private router: Router
  ) {
    this.checkAuth();
  }

  register(name: string, birthday: string, phone: string, email: string, password: string): Observable<AuthResponse> {
    return this.http.post<AuthResponse>(`${this.apiUrl}/register`, {
      name,
      birthday,
      phone,
      email,
      password
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
  }

  private clearAuth(): void {
    localStorage.removeItem('auth_token');
    this.currentUser.set(null);
    this.isAuthenticated.set(false);
  }

  private checkAuth(): void {
    const token = localStorage.getItem('auth_token');
    if (token) {
      this.getUser().subscribe({
        error: () => this.clearAuth()
      });
    }
  }

  getToken(): string | null {
    return localStorage.getItem('auth_token');
  }
}