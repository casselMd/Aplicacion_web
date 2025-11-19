    import { Component, OnInit } from '@angular/core';
    import { FormBuilder, FormGroup, FormsModule, ReactiveFormsModule, Validators } from '@angular/forms';
    import Swal from 'sweetalert2';
    import { CommonModule } from '@angular/common';
    import {  ProveedorService } from '../../services/proveedor.service';
    import { Proveedor } from '../../Models/proveedor.model';

    @Component({
    selector: 'app-proveedor',
    standalone: true,
    imports: [ReactiveFormsModule, CommonModule, FormsModule],
    templateUrl: './proveedor.component.html',
    styleUrls: ['./proveedor.component.css']
    })
    export class ProveedorComponent implements OnInit {
    proveedores: Proveedor[] = [];
    proveedoresFiltrados: Proveedor[] = [];
    formulario: FormGroup;
    modoEdicion = false;
    proveedorSeleccionado: Proveedor | null = null;
    modalOpen = false;

    filtros = {
        termino: '',
        estado: 1,
        orden: ''
    };

    constructor(
        private fb: FormBuilder,
        private proveedorService: ProveedorService
    ) {
        this.formulario = this.fb.group({
        id: [null],
        nombre: ['', Validators.required],
        ruc: ['', Validators.required],
        direccion: [null],
        telefono: [null],
        tipo: ['', Validators.required],
        observaciones: [null],
        estado: ['1', Validators.required]
        });
    }

    ngOnInit(): void {
        this.obtenerProveedores();
    }

    obtenerProveedores(): void {
        this.proveedorService.listar().subscribe(res => {
        if (res.status) {
            this.proveedores = res.data;
            this.proveedoresFiltrados = [...this.proveedores];
            this.aplicarFiltros();
            // console.log(this.proveedores);
        }
        });
    }

    aplicarFiltros(): void {
        let lista = [...this.proveedores];

        if (this.filtros.termino) {
        const term = this.filtros.termino.toLowerCase();
        lista = lista.filter(p =>
            p.nombre?.toLowerCase().includes(term) || p.ruc?.includes(term)
        );
        }

        if (this.filtros.estado != null) {
        lista = lista.filter(p => p.estado == this.filtros.estado);
        }

        if (this.filtros.orden === 'nombre_asc') {
        lista.sort((a, b) => a.nombre.localeCompare(b.nombre));
        } else if (this.filtros.orden === 'nombre_desc') {
        lista.sort((a, b) => b.nombre.localeCompare(a.nombre));
        }

        this.proveedoresFiltrados = lista;
    }

    get totalProveedores(): number {
        return this.proveedoresFiltrados.length;
    }

    abrirModal(nuevo = true, proveedor?: Proveedor): void {
        this.modoEdicion = !nuevo;
        this.proveedorSeleccionado = proveedor ?? null;

        if (this.modoEdicion && proveedor) {
        this.formulario.setValue({
            id: proveedor.id,
            nombre: proveedor.nombre,
            ruc: proveedor.ruc,
            direccion: proveedor.direccion,
            telefono: proveedor.telefono,
            tipo: proveedor.tipo,
            observaciones: proveedor.observaciones,
            estado: proveedor.estado
        });
        } else {
        this.formulario.reset({ estado: '1' });
        }

        this.modalOpen = true;
    }

    cerrarModal(): void {
        this.modalOpen = false;
        this.formulario.reset({ estado: '1' });
        this.proveedorSeleccionado = null;
    }

    guardar(): void {
        if (this.formulario.invalid) return;

        const datos: Proveedor = this.formulario.value;

        const peticion = this.modoEdicion && this.proveedorSeleccionado
        ? this.proveedorService.actualizar(this.proveedorSeleccionado.id, datos)
        : this.proveedorService.registrar(datos);

        peticion.subscribe({
        next: res => {
            if (res.status) {
            this.obtenerProveedores();
            this.cerrarModal();
            Swal.fire(
                this.modoEdicion ? 'Actualizado' : 'Registrado',
                res.msg,
                'success'
            );
            }
        },
        error: () => {
            Swal.fire('Error', 'No se pudo guardar el proveedor', 'error');
        }
        });
    }

    eliminar(id: number): void {
        Swal.fire({
        title: '¿Eliminar proveedor?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
        }).then(result => {
        if (result.isConfirmed) {
            this.proveedorService.eliminar(id).subscribe({
            next: res => {
                if (res.status) {
                this.obtenerProveedores();
                Swal.fire('Eliminado', res.msg, 'success');
                }
            },
            error: () => Swal.fire('Error', 'No se pudo eliminar', 'error')
            });
        }
        });
    }
    }
