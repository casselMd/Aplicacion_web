    import { Component, OnInit } from '@angular/core';
    import { ActivatedRoute, Router } from '@angular/router';
    import { AuthService } from '../../services/auth.service';

    @Component({
    selector: 'app-google-callback',
    templateUrl: './google-callback.component.html',
    styleUrls: ['./google-callback.component.css']
    })
    export class GoogleCallbackComponent implements OnInit {
    cargando: boolean = true;

    constructor(
        private route: ActivatedRoute,
        private router: Router,
        private authService: AuthService
    ) {}

    ngOnInit(): void {
        this.route.queryParams.subscribe(params => {
        const authID = params['auth_id'];
        if (authID) {
            setTimeout(() => {
            this.authService.obtenerTokensDesdeServidor(authID).subscribe({
                next: res => {
                if (res.status) {
                    this.authService.guardarTokens(res.data);
                    this.router.navigate(['/dashboard']);
                } else {
                    this.router.navigate(['/login'], {
                    queryParams: { error: 'Error en inicio de sesión.' }
                    });
                }
                },
                error: () => {
                this.router.navigate(['/login'], {
                    queryParams: { error: 'Sesión inválida o expirada.' }
                });
                }
            });
            }, 1000);
        } else {
            this.router.navigate(['/login'], {
            queryParams: { error: 'No se recibió auth_id.' }
            });
        }
        });
    }
    }
