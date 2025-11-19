    import { Component, OnInit } from '@angular/core';
    import {
    FormBuilder,
    FormGroup,
    FormsModule,
    ReactiveFormsModule,
    Validators
    } from '@angular/forms';
    import Swal from 'sweetalert2';
    import { CommonModule } from '@angular/common';
    import {  EmpleadoService } from '../../services/empleado.service';
    import { Empleado } from '../../Models/empleado.model';

    @Component({
    selector: 'app-empleado',
    standalone: true,
    imports: [ReactiveFormsModule, CommonModule, FormsModule],
    templateUrl: './empleado.component.html',
    styleUrls: ['./empleado.component.css']
    })
    export class EmpleadoComponent implements OnInit {
    empleados: Empleado[] = [];
    empleadosFiltrados: Empleado[] = [];

    formulario!: FormGroup;
    modoEdicion = false;
    empleadoSeleccionado: Empleado | null = null;
    modalOpen = false;

    filtros = {
        termino: '',
        estado: 1,
        orden: ''
    };

    constructor(
        private fb: FormBuilder,
        private empleadoService: EmpleadoService
    ) {
        this.formulario = this.fb.group({
        id: [null],
        nombre: ['', Validators.required],
        apellidos: ['', Validators.required],
        username: ['', Validators.required],
        // En este control “password” aplicaremos validadores dinámicamente:
        password: ['', Validators.required],
        status: ['1', Validators.required],
        dni: ['', [Validators.required, Validators.minLength(8), Validators.maxLength(8)]],
        rol: ['', Validators.required]
        });
    }

    ngOnInit(): void {
        this.obtenerEmpleados();
    }

    private obtenerEmpleados(): void {
        this.empleadoService.listar().subscribe(res => {
        if (res.status) {
            this.empleados = res.data;
            this.empleadosFiltrados = [...this.empleados];
            this.aplicarFiltros();
        }
        });
    }

    aplicarFiltros(): void {
        let lista = [...this.empleados];

        if (this.filtros.termino) {
        const term = this.filtros.termino.toLowerCase();
        lista = lista.filter(e =>
            e.nombre?.toLowerCase().includes(term) ||
            e.apellidos?.toLowerCase().includes(term) ||
            e.username?.toLowerCase().includes(term) ||
            e.dni?.includes(term)
        );
        }

        if (this.filtros.estado != null) {
        lista = lista.filter(e => e.status == this.filtros.estado);
        }

        if (this.filtros.orden === 'nombre_asc') {
        lista.sort((a, b) => a.nombre.localeCompare(b.nombre));
        } else if (this.filtros.orden === 'nombre_desc') {
        lista.sort((a, b) => b.nombre.localeCompare(a.nombre));
        }

        this.empleadosFiltrados = lista;
    }

    get totalEmpleados(): number {
        return this.empleadosFiltrados.length;
    }

    abrirModal(nuevo = true, empleado?: Empleado): void {
        this.modoEdicion = !nuevo;
        this.empleadoSeleccionado = empleado ?? null;

        // Ajustamos los validadores del campo “password” según modo (nuevo/edit)
        if (this.modoEdicion) {
        // Campo “password” ya no será obligatorio en edición (se usa para cambiar contraseña)
        this.formulario.get('password')!.setValidators([]);
        this.formulario.get('password')!.updateValueAndValidity();

        // Cargamos los valores de “empleado” en el formulario, dejando password vacío
        this.formulario.setValue({
            id: empleado!.id,
            nombre: empleado!.nombre,
            apellidos: empleado!.apellidos,
            username: empleado!.username,
            password: '', // El usuario ingresa si quiere cambiar contraseña
            status: empleado!.status,
            dni: empleado!.dni,
            rol: empleado!.rol
        });
        } else {
        // Nuevo empleado: password debe ser obligatorio
        this.formulario.get('password')!.setValidators([Validators.required]);
        this.formulario.get('password')!.updateValueAndValidity();

        // Reseteamos todo el formulario a valores en blanco / por defecto
        this.formulario.reset({
            nombre: '',
            apellidos: '',
            username: '',
            password: '',
            status: '1',
            dni: '',
            rol: ''
        });
        }

        this.modalOpen = true;
    }

    cerrarModal(): void {
        this.modalOpen = false;
        this.empleadoSeleccionado = null;
    }

    guardar(): void {
    if (this.formulario.invalid) {
        Swal.fire('Error', 'Por favor completa todos los campos obligatorios.', 'warning');
        return;
    }

    // Obtenemos los valores del formulario
    const datosFromForm: Empleado = this.formulario.value;
    // console.log("datos form : ", datosFromForm);
    // Creamos un objeto payload donde password sea opcional
    const payload: Partial<Empleado> = {
        id: datosFromForm.id,
        nombre: datosFromForm.nombre,
        apellidos: datosFromForm.apellidos,
        username: datosFromForm.username,
        status: datosFromForm.status,
        dni: datosFromForm.dni,
        rol: datosFromForm.rol
    };
    // console.log("payload : "+ payload);
    // Solo incluimos password si estamos en modo creación, o si en edición lo escribió el usuario
    if (!this.modoEdicion || (this.modoEdicion && datosFromForm.password)) {
        payload.password = datosFromForm.password;
    }
    // console.log("lina 166");
    const peticion = this.modoEdicion && this.empleadoSeleccionado
        ? this.empleadoService.actualizar(this.empleadoSeleccionado.id!, payload as Empleado)
        : this.empleadoService.registrar(payload as Empleado);
    // console.log("linea 170");
    peticion.subscribe({
        next: res => {
        if (res.status) {
            this.obtenerEmpleados();
            this.cerrarModal();
            Swal.fire(
            this.modoEdicion ? 'Actualizado' : 'Registrado',
            res.msg,
            'success'
            );
        }
        },
        error: () => {
        Swal.fire('Error', 'No se pudo guardar el empleado', 'error');
        }
    });
    }


    eliminar(id: number): void {
        Swal.fire({
        title: '¿Eliminar empleado?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
        }).then(result => {
        if (result.isConfirmed) {
            this.empleadoService.eliminar(id).subscribe({
            next: res => {
                if (res.status) {
                this.obtenerEmpleados();
                Swal.fire('Eliminado', res.msg, 'success');
                }
            },
            error: () => Swal.fire('Error', 'No se pudo eliminar', 'error')
            });
        }
        });
    }
    }
