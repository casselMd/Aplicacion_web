import { Routes } from '@angular/router';
import { LayoutComponent } from './components/layout/layout.component';
import { DashboardComponent } from './pages/dashboard/dashboard.component';
import { AuthGuard } from './guards/auth.guard';
import { GoogleCallbackComponent } from './Auth/google-callback/google-callback.component';
import { LoginComponent } from './Auth/login/login.component';
import { ForgotPasswordComponent } from './Auth/forgot-password/forgot-password.component';
import { ResetPasswordComponent } from './Auth/reset-password/reset-password.component';

import { CategoriaComponent } from './pages/categoria/categoria.component';
import { ClienteComponent } from './pages/cliente/cliente.component';
import { EmpleadoComponent } from './pages/empleado/empleado.component';
import { ProductoComponent } from './pages/producto/producto.component';
import { UnidadMedidaComponent } from './pages/unidad-medida/unidad-medida.component';
import { ProveedorComponent } from './pages/proveedor/proveedor.component';
import { MetodoPagoComponent } from './pages/metodo-pago/metodo-pago.component';
import { CompraRegistrarComponent } from './pages/compra/compra-registrar/compra-registrar.component';
import { CompraListarComponent } from './pages/compra/compra-listar/compra-listar.component';
import { VentaListarComponent } from './pages/venta/venta-listar/venta-listar.component';
import { VentaRegistrarComponent } from './pages/venta/venta-registrar/venta-registrar.component';
import { InventarioListarComponent } from './pages/inventario/inventario-listar/inventario-listar.component';
import { InventarioRegistrarComponent } from './pages/inventario/inventario-registrar/inventario-registrar.component';


export const routes: Routes = [
  { path: 'login', component: LoginComponent },
  { path: 'Auth/google/callback', component: GoogleCallbackComponent},
  {path: 'Auth/recuperar-password',   component: ForgotPasswordComponent},
  {path: 'Auth/reset-password',component: ResetPasswordComponent},

  {path: '' , component: LayoutComponent, 
    children: [

            {path:'dashboard' , component:DashboardComponent,                      canActivate:[AuthGuard]},
            {path:'cliente' , component:ClienteComponent,                          canActivate:[AuthGuard]},
            {path:'categoria' , component:CategoriaComponent,                      canActivate:[AuthGuard]},
            {path:'empleado' , component:EmpleadoComponent,                        canActivate:[AuthGuard]},
            {path:'metodo-pago' , component:MetodoPagoComponent,                   canActivate:[AuthGuard]},
            {path:'venta' , component:VentaListarComponent,                        canActivate:[AuthGuard]},
            {path:'venta/listar' , component:VentaListarComponent,                 canActivate:[AuthGuard]},
            {path:'venta/registrar' , component:VentaRegistrarComponent,           canActivate:[AuthGuard]},
            {path:'producto' , component:ProductoComponent,                        canActivate:[AuthGuard]},
            {path:'unidad-medida' , component:UnidadMedidaComponent,               canActivate:[AuthGuard]},
            {path:'proveedor' , component:ProveedorComponent,                      canActivate:[AuthGuard]},
            {path:'compra', component: CompraListarComponent,                      canActivate: [AuthGuard]},
            {path:'compra/listar', component: CompraListarComponent,               canActivate: [AuthGuard]},
            {path:'compra/registrar', component: CompraRegistrarComponent,         canActivate: [AuthGuard]},
            {path:'compra/ver', component: CompraRegistrarComponent,               canActivate: [AuthGuard]},
            {path:'inventario', component: InventarioListarComponent,              canActivate: [AuthGuard]},
            {path:'inventario/registrar', component: InventarioRegistrarComponent, canActivate: [AuthGuard]},
            {path:'inventario/listar', component: InventarioListarComponent,       canActivate: [AuthGuard]},

      { path: '**', redirectTo: 'dashboard', pathMatch: 'full'}
    ]
    
  },

  {path : "**" , redirectTo: "login"},
];
