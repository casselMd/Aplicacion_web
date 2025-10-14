    import { Component } from '@angular/core';
    import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
    import { Router, RouterLinkWithHref } from '@angular/router';
    import { AuthService } from '../../services/auth.service';
    import { CommonModule } from '@angular/common';


    // auth-response.interface.ts
    export interface AuthResponse {
    status: boolean;
    msg: string;
    }

    @Component({
    standalone:true,
    imports:[ReactiveFormsModule, RouterLinkWithHref ,  CommonModule],
    selector: 'app-forgot-password',
    templateUrl: './forgot-password.component.html'

    })
    export class ForgotPasswordComponent {
    forgotForm: FormGroup;
    mensaje: string = '';
    loading: boolean = false;

    constructor(
        private fb: FormBuilder,
        private authService: AuthService,
        private router: Router
    ) {
        this.forgotForm = this.fb.group({
        correo: ['', [Validators.required, Validators.email]]
        });
    }

    enviarCorreo() {
        if (this.forgotForm.invalid) return;
        this.loading = true;
        this.mensaje = '';

        const { correo } = this.forgotForm.value;

        this.authService.solicitarRecuperacion(correo).subscribe({
        next: (res:AuthResponse) => {
            this.mensaje = res.msg;
            this.loading = false;
        },
        error: (err) => {
            this.mensaje = err.error.msg || 'Ocurrió un error.';
            console.log(err)
            this.loading = false;
        }
        });
    }
    }
