    import { Component, OnInit } from '@angular/core';
    import { FormBuilder, FormGroup, FormsModule, ReactiveFormsModule, Validators } from '@angular/forms';
    import { ActivatedRoute, Router, RouterLink } from '@angular/router';
    import { AuthService } from '../../services/auth.service';
    import { CommonModule } from '@angular/common';
import { IonButton, IonCard, IonCardContent, IonContent, IonIcon, IonInput, IonItem, IonLabel, IonText } from '@ionic/angular/standalone';

    @Component({
    selector: 'app-reset-password',
    standalone:true,
    imports:[  CommonModule,
    ReactiveFormsModule,
    RouterLink,
    IonContent,
    IonInput,
    IonButton,
    IonText,
    IonItem,
    IonLabel,
    IonIcon,
    IonCard,
    IonCardContent],
    templateUrl: './reset-password.component.html',
    styleUrls: ['./reset-password.component.css']
    })
    export class ResetPasswordComponent implements OnInit {
    resetForm: FormGroup;
    mensaje: string = '';
    token: string = '';
    loading = false;

    constructor(
        private fb: FormBuilder,
        private route: ActivatedRoute,
        private router: Router,
        private authService: AuthService
    ) {
        this.resetForm = this.fb.group({
        nueva: ['', [Validators.required, Validators.minLength(6)]]
        });
    }

    ngOnInit(): void {

        this.token = this.route.snapshot.queryParamMap.get('token') || '';
    }

    cambiarPassword() {
        console.log(this.token);
        if (this.resetForm.invalid || !this.token) return;

        this.loading = true;
        const nueva = this.resetForm.value.nueva;

        this.authService.cambiarPassword(this.token, nueva).subscribe({
        next: (res) => {
            this.mensaje = res.msg;
            this.loading = false;
            setTimeout(() => this.router.navigate(['/login']), 2500);
        },
        error: (err) => {
            this.mensaje = err.error.msg || 'Ocurrió un error.';
            this.loading = false;
        }
        });
    }
    }
