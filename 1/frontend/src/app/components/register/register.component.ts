import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';
import { HttpErrorResponse } from '@angular/common/http';
import { AuthService } from '../../services/auth.service';

@Component({
  selector: 'app-register',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterLink],
  templateUrl: './register.component.html'
})
export class RegisterComponent {
  name = '';
  birthday = '';
  phone = '';
  email = '';
  password = '';
  passwordConfirmation = '';
  error = '';
  errors: { [key: string]: string[] } = {};
  loading = false;

  constructor(
    private authService: AuthService,
    private router: Router
  ) {}

  onSubmit(): void {
    this.loading = true;
    this.error = '';
    this.errors = {};

    this.authService.register(
      this.name,
      this.birthday,
      this.phone,
      this.email,
      this.password,
      this.passwordConfirmation
    ).subscribe({
      next: () => {
        this.router.navigate(['/dashboard']);
      },
      error: (err: HttpErrorResponse) => {
        console.error('Registration error:', err);
        if (err.error?.errors) {
          // Laravel validation errors
          this.errors = err.error.errors;
          this.error = 'Please fix the validation errors below';
        } else if (err.error?.message) {
          this.error = err.error.message;
        } else if (err.message) {
          this.error = err.message;
        } else {
          this.error = 'Registration failed. Please check your connection and try again.';
        }
        this.loading = false;
      }
    });
  }
}
