    import { Component } from '@angular/core';
    import { CommonModule } from '@angular/common';
    import { FormsModule } from '@angular/forms';
    import { Router } from '@angular/router';
    import { ToastController, IonHeader, IonToolbar, IonTitle, IonContent, IonInput, IonButton } from '@ionic/angular/standalone';
    import { ApiService } from 'src/app/service/api.service';

    @Component({
    selector: 'app-login',  
    templateUrl: './login.page.html',
    styleUrls: ['./login.page.css'],
    standalone: true,
    imports: [CommonModule, FormsModule, IonHeader, IonToolbar, IonTitle, IonContent, IonInput, IonButton],
    })
    export class LoginPage {
    usuario: string = '';
    clave: string = '';

    constructor(
        private api: ApiService,
        private toastCtrl: ToastController,
        private router: Router
    ) {}

    async login() {
        try {
        this.api.login(this.usuario, this.clave).subscribe(
            async (res: any) => {
            if (res.success) {
                //  Login correcto
                const toast = await this.toastCtrl.create({
                message: `Bienvenido ${this.usuario}`,
                duration: 2000,
                color: 'success'
                });
                await toast.present();

                // Redirigir al home/dashboard
                this.router.navigateByUrl('/home', { replaceUrl: true });
            } else {
                //  Usuario o contraseña incorrectos
                const toast = await this.toastCtrl.create({
                message: res.message || 'Credenciales inválidas',
                duration: 2000,
                color: 'danger'
                });
                await toast.present();
            }
            },
            async () => {
            //  Error de conexión con el servidor
            const toast = await this.toastCtrl.create({
                message: 'Error de conexión con el servidor',
                duration: 2000,
                color: 'danger'
            });
            await toast.present();
            }
        );
        } catch (err) {
        const toast = await this.toastCtrl.create({
            message: 'Ocurrió un error inesperado',
            duration: 2000,
            color: 'danger'
        });
        await toast.present();
        }
    }
    }
