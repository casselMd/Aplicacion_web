    import { Component, OnInit } from '@angular/core';
    import { FormBuilder, FormGroup, FormsModule, ReactiveFormsModule, Validators } from '@angular/forms';
    import Swal from 'sweetalert2';
    import { CommonModule } from '@angular/common';
    import {  MetodoPagoService } from '../../services/metodo-pago.service';
    import { MetodoPago } from '../../Models/metodopago.model';

    @Component({
    selector: 'app-metodopago',
    standalone: true,
    imports: [ReactiveFormsModule, CommonModule, FormsModule],
    templateUrl: './metodo-pago.component.html',
    styleUrls: ['./metodo-pago.component.css']
    })
    export class MetodoPagoComponent implements OnInit {
    metodos: MetodoPago[] = [];
    metodosFiltrados: MetodoPago[] = [];
    formulario: FormGroup;
    modoEdicion = false;
    metodoSeleccionado: MetodoPago | null = null;
    modalOpen = false;

    filtros = {
        termino: '',
        estado: 1,
        orden: ''
    };

    constructor(
        private fb: FormBuilder,
        private metodoPagoService: MetodoPagoService
    ) {
        this.formulario = this.fb.group({
        id: [null],
        metodo_pago: ['', Validators.required],
        status: ['1', Validators.required]
        });
    }

    ngOnInit(): void {
        this.obtenerMetodosPago();
    }

    obtenerMetodosPago(): void {
        this.metodoPagoService.listar().subscribe(res => {
        if (res.status) {
            this.metodos = res.data;
            this.metodosFiltrados = [...this.metodos];
            this.aplicarFiltros();
        }
        });
    }

    aplicarFiltros(): void {
        let lista = [...this.metodos];

        if (this.filtros.termino) {
        const term = this.filtros.termino.toLowerCase();
        lista = lista.filter(m =>
            m.metodo_pago.toLowerCase().includes(term) || m.id.toString().includes(term)
        );
        }

        if (this.filtros.estado != null) {
        lista = lista.filter(m => m.status == this.filtros.estado);
        }

        if (this.filtros.orden === 'metodo_pago_asc') {
        lista.sort((a, b) => a.metodo_pago.localeCompare(b.metodo_pago));
        } else if (this.filtros.orden === 'metodo_pago_desc') {
        lista.sort((a, b) => b.metodo_pago.localeCompare(a.metodo_pago));
        }

        this.metodosFiltrados = lista;
    }

    get totalMetodos(): number {
        return this.metodosFiltrados.length;
    }

    abrirModal(nuevo = true, metodo?: MetodoPago): void {
        this.modoEdicion = !nuevo;
        this.metodoSeleccionado = metodo ?? null;

        if (this.modoEdicion && metodo) {
        this.formulario.setValue({
            id: metodo.id,
            metodo_pago: metodo.metodo_pago,
            status: metodo.status
        });
        } else {
        this.formulario.reset({ status: '1' });
        }

        this.modalOpen = true;
    }

    cerrarModal(): void {
        this.modalOpen = false;
        this.formulario.reset({ status: '1' });
        this.metodoSeleccionado = null;
    }

    guardar(): void {
        if (this.formulario.invalid) return;

        const datos: MetodoPago = this.formulario.value;

        const peticion = this.modoEdicion && this.metodoSeleccionado
        ? this.metodoPagoService.actualizar(this.metodoSeleccionado.id, datos)
        : this.metodoPagoService.registrar(datos);

        peticion.subscribe({
        next: res => {
            if (res.status) {
            this.obtenerMetodosPago();
            this.cerrarModal();
            Swal.fire(
                this.modoEdicion ? 'Actualizado' : 'Registrado',
                res.msg,
                'success'
            );
            }
        },
        error: () => {
            Swal.fire('Error', 'No se pudo guardar el método de pago', 'error');
        }
        });
    }

    eliminar(id: number): void {
        Swal.fire({
        title: '¿Eliminar método de pago?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
        }).then(result => {
        if (result.isConfirmed) {
            this.metodoPagoService.eliminar(id).subscribe({
            next: res => {
                if (res.status) {
                this.obtenerMetodosPago();
                Swal.fire('Eliminado', res.msg, 'success');
                }
            },
            error: () => Swal.fire('Error', 'No se pudo eliminar', 'error')
            });
        }
        });
    }
    }
