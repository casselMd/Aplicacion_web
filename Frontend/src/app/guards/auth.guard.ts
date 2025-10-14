import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { catchError, map, Observable, of } from 'rxjs';
import { AuthService } from '../services/auth.service';

export const AuthGuard: CanActivateFn = (route, state): Observable<boolean> => {
    const token = localStorage.getItem('token');
    const router = inject(Router);
    const auth = inject(AuthService);

    if (!token) {
        router.navigate(['/login']);
        return of(false);
    }

    return auth.verificarToken(token).pipe(
        map((res) => {
        if (res.status) {
            return true;
        } else {
            router.navigate(['/login']);
            return false;
        }
        }),
        catchError((error) => {
        // Si ocurre un error en la verificación, también redirigimos al login.
        router.navigate(['/login']);
        return of(false);
        })
    );

};
