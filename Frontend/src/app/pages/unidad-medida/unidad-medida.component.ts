    import { Component, OnInit } from '@angular/core';
    import { FormBuilder, FormGroup, Validators, FormsModule, ReactiveFormsModule } from '@angular/forms';
    import { CommonModule } from '@angular/common';
    import Swal from 'sweetalert2';
    import { UnidadMedida, UnidadMedidaService } from '../../services/unidad-medida.service';

    @Component({
    selector: 'app-unidad-medida',
    standalone: true,
    imports: [FormsModule, ReactiveFormsModule, CommonModule],
    templateUrl: './unidad-medida.component.html'
    })
    export class UnidadMedidaComponent implements OnInit {
    unidades: UnidadMedida[] = [];
    unidadesFiltradas: UnidadMedida[] = [];
    formulario: FormGroup;
    modoEdicion = false;
    unidadSeleccionada: UnidadMedida | null = null;
    modalOpen = false;

    filtros = {
        termino: '',
        estado: 1,
        orden: ''
    };

    constructor(
        private fb: FormBuilder,
        private unidadService: UnidadMedidaService
    ) {
        this.formulario = this.fb.group({
        id: [null],
        nombre: ['', Validators.required],
        simbolo: ['', Validators.required],
        status: ['1', Validators.required]
        });
    }

    ngOnInit(): void {
        this.obtenerUnidades();
    }

    obtenerUnidades(): void {
        this.unidadService.listar().subscribe(res => {
        if (res.status) {
            this.unidades = res.data;
            this.aplicarFiltros();
        }
        });
    }

    aplicarFiltros(): void {
        let lista = [...this.unidades];

        if (this.filtros.termino) {
        const term = this.filtros.termino.toLowerCase();
        lista = lista.filter(u =>
            u.nombre.toLowerCase().includes(term) ||
            u.simbolo.toLowerCase().includes(term) ||
            u.id.toString().includes(term)
        );
        }

        if (this.filtros.estado !== null) {
        lista = lista.filter(u => u.status == +this.filtros.estado);
        }

        if (this.filtros.orden === 'nombre_asc') {
        lista.sort((a, b) => a.nombre.localeCompare(b.nombre));
        } else if (this.filtros.orden === 'nombre_desc') {
        lista.sort((a, b) => b.nombre.localeCompare(a.nombre));
        }

        this.unidadesFiltradas = lista;
    }

    get totalUnidades(): number {
        return this.unidadesFiltradas.length;
    }

    abrirModal(nuevo = true, unidad?: UnidadMedida): void {
        this.modoEdicion = !nuevo;
        this.unidadSeleccionada = unidad ?? null;

        if (this.modoEdicion && unidad) {
        this.formulario.setValue({
            id: unidad.id,
            nombre: unidad.nombre,
            simbolo: unidad.simbolo,
            status: unidad.status
        });
        } else {
        this.formulario.reset({ status: '1' });
        }

        this.modalOpen = true;
    }

    cerrarModal(): void {
        this.modalOpen = false;
        this.formulario.reset({ status: '1' });
    }

    guardar(): void {
        if (this.formulario.invalid) return;

        const data: UnidadMedida = this.formulario.value;

        const peticion = this.modoEdicion && this.unidadSeleccionada
        ? this.unidadService.actualizar(this.unidadSeleccionada.id, data)
        : this.unidadService.registrar(data);
        // console.log('unidad de medida : ',data);
        peticion.subscribe({
        next: res => {
            if (res.status) {
            this.obtenerUnidades();
            this.cerrarModal();
            Swal.fire(this.modoEdicion ? 'Actualizado' : 'Registrado', res.msg, 'success');
            }
        },
        error: () => Swal.fire('Error', 'No se pudo guardar', 'error')
        });
    }

    eliminar(id: number): void {
        Swal.fire({
        title: '¿Eliminar unidad?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
        }).then(result => {
        if (result.isConfirmed) {
            this.unidadService.eliminar(id).subscribe({
            next: res => {
                if (res.status) {
                this.obtenerUnidades();
                Swal.fire('Eliminado', res.msg, 'success');
                }
            },
            error: () => Swal.fire('Error', 'No se pudo eliminar', 'error')
            });
        }
        });
    }
    }
