    import { Component, OnInit } from '@angular/core';
    import { SidebarItemComponent } from '../siderbar-item/siderbar-item.component';
    import { CommonModule } from '@angular/common';
    import { AuthService,  getPayloadToken,  TokenEmpleadoPayload } from '../../services/auth.service';


    interface MenuItem {
    icon: string;
    text: string;
    route: string;
    roles: string[];
    }

    @Component({
    selector: 'app-sidebar',
    standalone: true,
    imports: [ SidebarItemComponent,CommonModule],
    templateUrl: './siderbar.component.html',
    styleUrl: './siderbar.component.css'
    })

    export class SidebarComponent implements OnInit {

    allMenuItems: MenuItem[] = [
        { icon: 'fa-solid fa-house', text: 'Dashboard', route: '/dashboard', roles: ['admin', 'empleado'] },
        { icon: 'fa-solid fa-users', text: 'Cliente', route: '/cliente', roles: ['admin','empleado'] },
        { icon: 'fa-solid fa-box', text: 'Producto', route: '/producto', roles: ['admin', 'empleado'] },
        { icon: 'fa-solid fa-cash-register', text: 'Compra', route: '/compra', roles: ['admin'] },
        { icon: 'fa-solid fa-shopping-cart', text: 'Venta', route: '/venta', roles: ['admin', 'empleado'] },
        { icon: 'fa-solid fa-boxes-stacked', text: 'Inventario', route: '/inventario', roles: ['admin'] },
        { icon: 'fa-solid fa-user-tie', text: 'Empleado', route: '/empleado', roles: ['admin'] },
        { icon: 'fa-solid fa-user', text: 'Proveedor', route: '/proveedor', roles: ['admin'] },
        { icon: 'fa-solid fa-tags', text: 'Categoria', route: '/categoria', roles: ['admin'] },
        { icon: 'fa-solid fa-credit-card', text: 'Metodo Pago', route: '/metodo-pago', roles: ['admin'] },
        { icon: 'fa-solid fa-ruler', text: 'Unidades de Medida', route: '/unidad-medida', roles: ['admin'] },
    ];

    itemMenu: MenuItem[] = [];

    constructor(private authService: AuthService) {}
    payload !:TokenEmpleadoPayload | null ;
    //filtroRol!: string;
    rol: string = "";
    ngOnInit(): void {
        /*const token = this.authService.getTokenEmp() || "";
        this.payload = getPayloadToken(token);
        this.filtroRol =this.payload?.data.rol ?this.payload?.data.rol :"" ;
        this.authService.setRol(this.filtroRol);
        //console.log(this.payload?.data.rol);
        if (this.filtroRol) {
        this.itemMenu = this.allMenuItems.filter(item => item.roles.includes(this.filtroRol));
        }*/
        this.authService.getRol().subscribe((rol) => {
            this.rol = rol ?? "";
            this.itemMenu = this.allMenuItems.filter(item => item.roles.includes(this.rol));
        });

        // Si recargas la página, puedes volver a obtener el rol desde el token guardado
        const tokenEmp = localStorage.getItem('token_emp');
        if (tokenEmp) {
            const payload = getPayloadToken(tokenEmp);
            if (payload && payload.data.rol) {
            this.authService.setRol(payload.data.rol); // Refresca el valor en el BehaviorSubject
            }
        }
    }
    }




