import { inject } from '@angular/core';
import { Router, CanActivateFn } from '@angular/router';
import { AuthService } from '../services/auth.service';

export const authGuard: CanActivateFn = async (route, state) => {
  const authService = inject(AuthService);
  const router = inject(Router);

  // Wait for auth check to complete and get authentication status
  const isAuthenticated = await authService.waitForAuthCheck();

  if (isAuthenticated) {
    return true;
  }

  router.navigate(['/login']);
  return false;
};