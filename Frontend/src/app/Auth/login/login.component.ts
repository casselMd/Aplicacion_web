import { Component } from '@angular/core';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { BehaviorSubject } from 'rxjs';
import { Router, RouterLinkWithHref } from '@angular/router';
import Swal from 'sweetalert2';
import { AuthService, getPayloadToken } from '../../services/auth.service';
import { environment } from '../../../environments/environment';
import { IonButton, IonContent, IonIcon, IonItem } from '@ionic/angular/standalone';
import { addIcons } from 'ionicons';
import { lockClosedOutline, logoIonic, personOutline } from 'ionicons/icons';

@Component({
    selector: 'app-login',
    standalone: true,
    imports: [ReactiveFormsModule, RouterLinkWithHref, IonContent, IonItem, IonIcon, IonButton],
    templateUrl: './login.component.html',
    styleUrl: './login.component.css'
})
    export class LoginComponent {
    loginForm: FormGroup;
    constructor(private fb: FormBuilder,
        private authService: AuthService,
        private router:Router) {
        this.loginForm = this.fb.group({
            username: ['', [Validators.required]],
            password: ['', Validators.required]
        });
         addIcons({personOutline,lockClosedOutline});
    }

    onSubmit() {
        if (this.loginForm.valid) {
            this.authService.login(this.loginForm.value).subscribe({
            next: (response) => {
                if (response.status) {
                const token = response.data.access_token;
                const tokenEmp = response.data.token_empleado;

                localStorage.setItem('token', token);
                localStorage.setItem('token_emp', tokenEmp);

                const payload = getPayloadToken(tokenEmp);
                if (payload && payload.data.rol) {
                    this.authService.setRol(payload.data.rol); // Aquí actualizas el rol
                }
                console.log(response);

                this.router.navigate(['/']);
                } else {
                // Aquí puedes mostrar un mensaje con SweetAlert o snackbar
                Swal.fire('Error', response.msg, 'error');
                }
            },
            error: (err) => {
                console.error('Error en login:', err);
                Swal.fire('Error', 'Error de conexión con el servidor', 'error');
            }
            });

        }else{
        Swal.fire('Error', 'Ingresa tu correo y contraseña para continuar', 'warning');
        }
    }



        /****
         * Login con Google
        */
        loginConGoogle() {
        const clientId = environment.client_id;
        const redirectUri = "http://localhost/Delicias/Backend/AuthGoogle/callback";

        const scope = 'email profile';
        const responseType = 'code';

        const url = `https://accounts.google.com/o/oauth2/v2/auth?client_id=${clientId}&redirect_uri=${redirectUri}&response_type=${responseType}&scope=${scope}`;
        window.location.href = url;
    }

}
