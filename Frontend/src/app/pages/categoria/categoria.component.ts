    import { Component, OnInit } from '@angular/core';
    import { FormBuilder, FormGroup, FormsModule, ReactiveFormsModule, Validators } from '@angular/forms';
    import Swal from 'sweetalert2';
    import {  CategoriaService } from '../../services/categoria.service';
    import { CommonModule } from '@angular/common';
    import { Categoria } from '../../Models/categoria.model';

    @Component({
    selector: 'app-categoria',
    standalone:true,
    imports:[ReactiveFormsModule, CommonModule, FormsModule],
    templateUrl: './categoria.component.html',
    styleUrls: ['./categoria.component.css']
    })
    export class CategoriaComponent implements OnInit {
    categorias: Categoria[] = [];

    formulario: FormGroup;
    modoEdicion = false;
    categoriaSeleccionada: Categoria | null = null;
    modalOpen = false;      // controla visibilidad del modal

    constructor(
        private fb: FormBuilder,
        private categoriaService: CategoriaService
    ) {
        this.formulario = this.fb.group({
        id : [null],
        categoria: ['', [Validators.required,Validators.minLength(4)]],
        descripcion: ['', [Validators.required, Validators.minLength(4)]],
        status: ['1',[Validators.required]]
        });
    }

    ngOnInit(): void {
        this.obtenerCategorias();
    }


    abrirModal(nuevo = true, cat?: Categoria): void {
        this.modoEdicion = !nuevo;
        this.categoriaSeleccionada = cat ?? null;
        if (this.modoEdicion && cat) {
        // console.log('categoria: ', cat, 'form: ',this.formulario.value)
        this.formulario.setValue({  id: cat.id,
                                    categoria: cat.categoria,
                                    descripcion: cat.descripcion,
                                    status: cat.status});
        // console.log(this.formulario.patchValue)
        } else {
        this.formulario.reset({ status: 'activo' });
        }
        this.modalOpen = true;
    }

    cerrarModal(): void {
        this.modalOpen = false;
        this.formulario.reset({ status: 'activo' });
        this.categoriaSeleccionada = null;
    }

    guardar(): void {
        if (this.formulario.invalid) return;
        const datos: Categoria = this.formulario.value;
        // console.log(datos);

        const peticion = this.modoEdicion && this.categoriaSeleccionada
        ? this.categoriaService.actualizar(this.categoriaSeleccionada.id, datos)
        : this.categoriaService.registrar(datos);
        // console.log(this.categoriaSeleccionada?.id, datos )
        // //console.log(peticion);
        peticion.subscribe({
        next: res => {
            // console.log(res)
            if (res.status) {
            this.obtenerCategorias();
            this.cerrarModal();
            Swal.fire(
                this.modoEdicion ? 'Actualizado' : 'Registrado',
                res.msg,
                'success'
            );
            }
        },
        error: () => {
            Swal.fire('Error', 'No se pudo guardar la categoría', 'error');
        }
        });
    }

    eliminar(id: number): void {
        Swal.fire({
        title: '¿Eliminar categoría?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
        }).then(result => {
        if (result.isConfirmed) {
            this.categoriaService.eliminar(id).subscribe({
            next: res => {
                if (res.status) {
                this.obtenerCategorias();
                Swal.fire('Eliminado', res.msg, 'success');
                }
            },
            error: () => Swal.fire('Error', 'No se pudo eliminar', 'error')
            });
        }
        });
    }





    categoriasFiltradas: Categoria[] = [];
    filtros = {
        termino: '',
        estado: 1,
        fechaDesde: '',
        fechaHasta: '',
        orden: ''
    };

    

    obtenerCategorias(): void {
        this.categoriaService.listar().subscribe(res => {
        if (res.status) {
            this.categorias = res.data;
            // console.log(this.categorias)
            this.categoriasFiltradas = [...this.categorias];
            this.aplicarFiltros();
        }
        });
    }

    aplicarFiltros(): void {
        let lista = [...this.categorias];

        // 1) Búsqueda por texto en categoria o descripción
        if (this.filtros.termino) {
        const term = this.filtros.termino.toLowerCase();
        lista = lista.filter(cat =>
            cat.categoria.toLowerCase().includes(term) ||
            cat.descripcion.toLowerCase().includes(term)
        );
        }

        // 2) Filtrar por estado
        if (this.filtros.estado != null) {
        lista = lista.filter(cat => cat.status == this.filtros.estado);
        }

        

        // 4) Orden alfabético
        if (this.filtros.orden === 'categoria_asc') {
        lista.sort((a, b) => a.categoria.localeCompare(b.categoria));
        } else if (this.filtros.orden === 'categoria_desc') {
        lista.sort((a, b) => b.categoria.localeCompare(a.categoria));
        }

        this.categoriasFiltradas = lista;
    }

    get totalCategorias(): number {
    return this.categoriasFiltradas.length;
    }
    }
