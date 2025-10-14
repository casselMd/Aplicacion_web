import { Routes } from '@angular/router';
import { AuthGuard } from './guards/auth.guard';
import { GoogleCallbackComponent } from './Auth/google-callback/google-callback.component';
import { LoginComponent } from './Auth/login/login.component';
import { ForgotPasswordComponent } from './Auth/forgot-password/forgot-password.component';
import { ResetPasswordComponent } from './Auth/reset-password/reset-password.component';

export const routes: Routes = [
  { path: 'login', component: LoginComponent },
  { path: 'Auth/google/callback', component: GoogleCallbackComponent},
  {path: 'Auth/recuperar-password',   component: ForgotPasswordComponent},
  {path: 'Auth/reset-password',component: ResetPasswordComponent},


  {path : "**" , redirectTo: "login"},
];
