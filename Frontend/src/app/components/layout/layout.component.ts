    import { Component, OnInit } from '@angular/core';
    import { RouterOutlet } from '@angular/router';
    import { SidebarComponent } from '../../components/siderbar/siderbar.component';
    import { AuthService, getPayloadToken, TokenEmpleadoPayload } from '../../services/auth.service';
    import Swal from 'sweetalert2';
    import { ProductoService } from '../../services/producto.service';
    import { CommonModule } from '@angular/common';
    import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
    import { EmpleadoService } from '../../services/empleado.service';
    import { Empleado, EmpleadoCredenciales, EmpleadoPerfil } from '../../Models/empleado.model';
    import { ProductosBajoStockComponent } from '../reporte/productos-bajo-stock/productos-bajo-stock.component';
    import { svg_perfil } from '../../../../public/assets/placeholder_perfil';


    @Component({
    selector: 'app-layout',
    standalone: true,
    imports: [RouterOutlet, SidebarComponent, CommonModule, ReactiveFormsModule, ProductosBajoStockComponent],
    templateUrl: './layout.component.html',
    styleUrl: './layout.component.css'
    })
    export class LayoutComponent implements OnInit{

    //svg pergil 
    placeholder_svg = svg_perfil;
    idEmpleado: number = -1;
    empleado !: EmpleadoPerfil;
    mostrarModal = false;
    modoEdicion = false;
    mostrarCredenciales = false;

    productosStockBajo: any[] = [];
    
    perfilForm!: FormGroup;
    credencialesForm!: FormGroup;
    constructor(private auth:AuthService, 
        private productoService : ProductoService, 
        private fb: FormBuilder,
        private empleadoService :  EmpleadoService){
    }
    
    ngOnInit(): void {
        this.getNombreEmpleado();
        this.obtenerEmpleado();
        

    }
    obtenerEmpleado() {
        this.empleadoService.ver(this.idEmpleado).subscribe(res =>{
        if(res.status){
            this.empleado = res.data;
            this.iniciarForms(this.empleado);
        }
        })
    }

    iniciarForms(empleado: Empleado){
        // Crea el formulario con los datos y lo desactiva
        this.perfilForm = this.fb.group({
        nombre: [{ value: empleado?.nombre, disabled: true }, Validators.minLength(4)],
        apellidos: [{ value: empleado?.apellidos, disabled: true },[Validators.minLength(4)]],
        dni: [{ value: empleado?.dni, disabled: true }, [Validators.minLength(8), Validators.maxLength(11)]]
        });

        this.credencialesForm = this.fb.group({
        username: [empleado?.username || '', [Validators.required, Validators.minLength(4)]],
        password: ['', [Validators.required,Validators.minLength(6)]]
        });
    }
    activarEdicion() {
        this.modoEdicion = true;
        this.perfilForm.enable(); // ahora se puede editar
    }
    
    abrirModalPerfil() {
        this.mostrarModal = true;
        this.modoEdicion = false;
        this.mostrarCredenciales = false;
    }
    

    cerrarModal() {
        this.mostrarModal = false;
    }

    actualizarDatos() {
        const datos:EmpleadoPerfil = this.perfilForm.value;
        console.log('Actualizando datos personales:', datos);
        this.modoEdicion = false;
        this.empleadoService.actualizarDatosPerfil(this.idEmpleado, datos).subscribe({
            next: res => {
                if (res.status) {
                this.obtenerEmpleado();
                
                Swal.fire(
                    'Actualizado' ,
                    res.msg,
                    'success'
                );
                }
            },
            error: () => {
                Swal.fire('Error', 'No se pudo actualizar', 'error');
            }
            });
        // Aquí haces el request al backend si quieres
    }

    actualizarCredenciales() {
        const cred:EmpleadoCredenciales = this.credencialesForm.value;
        // Aquí haces el request al backend si quieres
        this.empleadoService.actualizarCredenciales(cred).subscribe({
            next: res => {
                if (res.status) {
                this.obtenerEmpleado();
                Swal.fire(
                    'Actualizado' ,
                    res.msg,
                    'success'
                );
                }
            },
            error: () => {
                Swal.fire('Error', 'No se pudo actualizar', 'error');
            }
            }
        ) 

    }

    payload !: TokenEmpleadoPayload | null;
    nombreEmpleado !: string |undefined;

    getNombreEmpleado() {
        const token = this.auth.getTokenEmp() || "";
            this.payload = getPayloadToken(token);
            // console.log(this.payload)
        this.nombreEmpleado = this.payload?.data?.nombre_completo;
        const id = this.payload?.data?.id;
        this.idEmpleado = id ?? 0;
    }
    cerrarSesion(){
        this.auth.logout();
    }
    
    


    }
