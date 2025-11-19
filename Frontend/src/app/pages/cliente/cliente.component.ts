    import { Component, OnInit } from '@angular/core';
    import { FormBuilder, FormGroup, FormsModule, ReactiveFormsModule, Validators } from '@angular/forms';
    import Swal from 'sweetalert2';
    import {  ClienteService } from '../../services/cliente.service';
    import { CommonModule } from '@angular/common';
    import { Cliente } from '../../Models/cliente.model';

    @Component({
    selector: 'app-cliente',
    standalone: true,
    imports: [ReactiveFormsModule, CommonModule, FormsModule],
    templateUrl: './cliente.component.html',
    styleUrls: ['./cliente.component.css']
    })
    export class ClienteComponent implements OnInit {
    clientes: Cliente[] = [];
    clientesFiltrados: Cliente[] = [];
    formulario: FormGroup;
    modoEdicion = false;
    clienteSeleccionado: Cliente | null = null;
    modalOpen = false;

    filtros = {
        termino: '',
        estado: 1,
        orden: ''
    };

    constructor(
        private fb: FormBuilder,
        private clienteService: ClienteService
    ) {
    this.formulario = this.fb.group({
        id: [null, [Validators.required, Validators.minLength(8), Validators.maxLength(11)]],
        cliente: ['', [Validators.required, Validators.minLength(10)]],
        telefono: ['', [Validators.required, Validators.pattern(/^\d{9}$/)]],
        direccion: ['', [Validators.required, Validators.minLength(10)]],
        status: ['1', Validators.required]
    });

    }

    ngOnInit(): void {
        this.obtenerClientes();
    }

    obtenerClientes(): void {
        this.clienteService.listar().subscribe(res => {
        if (res.status) {
            this.clientes = res.data;
            this.clientesFiltrados = [...this.clientes];
            this.aplicarFiltros();
            //filtrados ', this.clientesFiltrados);
        }
        });
    }

    aplicarFiltros(): void {
        let lista = [...this.clientes];

        if (this.filtros.termino) {
        const term = this.filtros.termino.toLowerCase();
        lista = lista.filter(c =>
            c.cliente.toLowerCase().includes(term) || c.id.toString().includes(term)
        );
        }

        if (this.filtros.estado != null) {
        lista = lista.filter(c => c.status == this.filtros.estado);
        // console.log('estado de filtros: ',this.filtros.estado);
        }

        if (this.filtros.orden === 'cliente_asc') {
        lista.sort((a, b) => a.cliente.localeCompare(b.cliente));
        } else if (this.filtros.orden === 'cliente_desc') {
        lista.sort((a, b) => b.cliente.localeCompare(a.cliente));
        }

        this.clientesFiltrados = lista;
    }

    get totalClientes(): number {
        return this.clientesFiltrados.length;
    }

    abrirModal(nuevo = true, cli?: Cliente): void {
        this.modoEdicion = !nuevo;
        this.clienteSeleccionado = cli ?? null;

        if (this.modoEdicion && cli) {
            this.formulario.setValue({
            id: cli.id,
            cliente: cli.cliente,
            telefono: cli.telefono,
            direccion: cli.direccion,
            status: cli.status
            });
        } else {
            this.formulario.reset({
            status: '1'
            });
        }


        this.modalOpen = true;
    }

    cerrarModal(): void {
        this.modalOpen = false;
        this.formulario.reset({ status: '1' });
        this.clienteSeleccionado = null;
    }

    guardar(): void {
        if (this.formulario.invalid) return;

        const datos: Cliente = this.formulario.value;

        const peticion = this.modoEdicion && this.clienteSeleccionado
        ? this.clienteService.actualizar(this.clienteSeleccionado.id, datos)
        : this.clienteService.registrar(datos);
        // //console.log(datos)
        peticion.subscribe({
        next: res => {
            if (res.status) {
            this.obtenerClientes();
            this.cerrarModal();
            Swal.fire(
                this.modoEdicion ? 'Actualizado' : 'Registrado',
                res.msg,
                'success'
            );
            
            }
            
            
        },
        error: () => {
            Swal.fire('Error', 'No se pudo guardar el cliente', 'error');
        }
        });
    }

    eliminar(id: string): void {
        Swal.fire({
        title: '¿Eliminar cliente?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
        }).then(result => {
        if (result.isConfirmed) {
            this.clienteService.eliminar(id).subscribe({
            next: res => {
                if (res.status) {
                this.obtenerClientes();
                Swal.fire('Eliminado', res.msg, 'success');
                }
            },
            error: () => Swal.fire('Error', 'No se pudo eliminar', 'error')
            });
        }
        });
    }
    }
