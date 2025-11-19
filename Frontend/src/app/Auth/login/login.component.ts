import { Component } from '@angular/core';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators, FormsModule } from '@angular/forms';
import { Router, RouterLinkWithHref } from '@angular/router';
import Swal from 'sweetalert2';
import { AuthService, getPayloadToken } from '../../services/auth.service';
import { environment } from '../../../environments/environment';
import { addIcons } from 'ionicons';
import { lockClosedOutline, personOutline } from 'ionicons/icons';
import { CommonModule } from '@angular/common';
import {
  IonButton, IonContent, IonIcon, IonItem, IonInput
} from '@ionic/angular/standalone';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [
    ReactiveFormsModule,
    RouterLinkWithHref,
    IonContent,
    IonItem,
    IonIcon,
    IonButton,
    IonInput,
    CommonModule,
    FormsModule
  ],
  templateUrl: './login.component.html',
  styleUrl: './login.component.css'
})
export class LoginComponent {
  loginForm: FormGroup;

  constructor(
    private fb: FormBuilder,
    private authService: AuthService,
    private router: Router
  ) {
    this.loginForm = this.fb.group({
      username: ['', [Validators.required]],
      password: ['', Validators.required]
    });

    addIcons({ personOutline, lockClosedOutline });
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
              this.authService.setRol(payload.data.rol);
            }
            console.log('Login exitoso');

            this.router.navigate(['/']);
          } else {
            Swal.fire('Error', response.msg, 'error');
          }
        },
        error: (err) => {
          console.error('Error en login:', err);
          Swal.fire('Error', 'Error de conexión con el servidor', 'error');
        }
      });
    } else {
      Swal.fire('Error', 'Ingresa tu correo y contraseña para continuar', 'warning');
    }
  }

  loginConGoogle() {
    const clientId = environment.client_id;
    const redirectUri = 'http://localhost/Delicias/Backend/AuthGoogle/callback';
    const scope = 'email profile';
    const responseType = 'code';

    const url = `https://accounts.google.com/o/oauth2/v2/auth?client_id=${clientId}&redirect_uri=${redirectUri}&response_type=${responseType}&scope=${scope}`;
    window.location.href = url;
  }
}
