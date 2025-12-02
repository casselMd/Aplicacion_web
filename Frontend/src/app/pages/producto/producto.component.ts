    import { Component, OnInit } from '@angular/core';
    import { FormBuilder, FormGroup, FormsModule, ReactiveFormsModule, Validators } from '@angular/forms';
    import Swal from 'sweetalert2';
    import { CommonModule } from '@angular/common';
    import { ProductoService } from '../../services/producto.service';
    import { CategoriaService } from '../../services/categoria.service';
    import { UnidadMedida, UnidadMedidaService } from '../../services/unidad-medida.service';
    import { Producto } from '../../Models/producto.model';
    import { Categoria } from '../../Models/categoria.model';
    import { AuthService } from '../../services/auth.service';

    @Component({
    selector: 'app-producto',
    standalone: true,
    imports: [ReactiveFormsModule, CommonModule, FormsModule],
    templateUrl: './producto.component.html',
    styleUrls: ['./producto.component.css']
    })
    export class ProductoComponent implements OnInit {
    productos: Producto[] = [];
    productosFiltrados: Producto[] = [];
    formulario: FormGroup;
    modoEdicion = false;
    productoSeleccionado: Producto | null = null;
    modalOpen = false;
    rol : string | null = '';
    // Listas para <select>
    categorias: Categoria[] = [];
    unidades: UnidadMedida[] = [];

    filtros = {
        termino: '',
        estado: 1,
        orden: '',
        existencia:null
    };

    constructor(
        private fb: FormBuilder,
        private productoService: ProductoService,
        private categoriaService: CategoriaService,
        private unidadService: UnidadMedidaService,
        private authService : AuthService
    ) {
        this.formulario = this.fb.group({
        id: [null],
        nombre: ['', Validators.required],
        descripcion: ['', Validators.required],
        precio: [0, [Validators.required, Validators.min(0)]],
        status: [1, Validators.required],
        categoria_id: [null, Validators.required],
        unidad_medida_id: [null, Validators.required],
        es_existente: 0,
        margen_ganancia : 0
        });
    }

    ngOnInit(): void {
        this.obtenerProductos();
        this.cargarCategorias();
        this.cargarUnidades();
        this.obtenerRol();
    }
    obtenerRol() {
        this.authService.getRol().subscribe((rol)=>{
        console.log('ROL RECIBIDO', rol);
        this.rol = rol;
        })
    }

    obtenerProductos(): void {
        this.productoService.listar().subscribe(res => {
        if (res.status) {
            this.productos = res.data;
            this.productosFiltrados = [...this.productos];
            this.aplicarFiltros();
        }
        // console.log('linea 64: ', this.productos)
        });
    }
    private cargarCategorias(): void {
        this.categoriaService.listar().subscribe((res) => {
        if (res.status) {
            this.categorias = res.data;
            this.categorias = this.categorias.filter(p => p.status === 1);
        }
        });
    }

    private cargarUnidades(): void {
        this.unidadService.listar().subscribe((res) => {
        if (res.status) {
            this.unidades = res.data;
            this.unidades = this.unidades.filter(unid => unid.status ===1)
        }
        });
    }

    aplicarFiltros(): void {
        let lista = [...this.productos];

        if (this.filtros.termino) {
        const term = this.filtros.termino.toLowerCase();
        lista = lista.filter(p =>
            p.nombre.toLowerCase().includes(term) ||
            p.id.toString().includes(term)
        );
        }

        if (this.filtros.estado != null) {
        lista = lista.filter(p => p.status == this.filtros.estado);
        }

        if (this.filtros.orden === 'nombre_asc') {
        lista.sort((a, b) => a.nombre.localeCompare(b.nombre));
        } else if (this.filtros.orden === 'nombre_desc') {
        lista.sort((a, b) => b.nombre.localeCompare(a.nombre));
        }
        if(this.filtros.existencia !== null){
        lista = lista.filter(p => p.es_existente == this.filtros.existencia);
        }

        this.productosFiltrados = lista;
    }

    get totalProductos(): number {
        return this.productosFiltrados.length;
    }

    abrirModal(nuevo = true, producto?: Producto): void {
        this.modoEdicion = !nuevo;
        this.productoSeleccionado = producto ?? null;

        if (this.modoEdicion && producto) {
        this.formulario.setValue({
            id: producto.id,
            nombre: producto.nombre,
            descripcion: producto.descripcion,
            precio: producto.precio,
            status: producto.status,
            categoria_id: producto.categoria.id,
            unidad_medida_id: producto.unidad_medida.id,
            es_existente : producto.es_existente,
            margen_ganancia : producto.margen_ganancia
        });
        } else {
        this.formulario.reset({
            nombre: '',
            descripcion: '',
            precio: 0,
            stock: 0,
            status: '1',
            categoria_id: null,
            unidad_medida_id: null,
            es_existente: 0,
            margen_ganancia : 0
        });
        }

        this.modalOpen = true;
    }

    cerrarModal(): void {
        this.modalOpen = false;
        this.formulario.reset({
        nombre: '',
        descripcion: '',
        precio: 0,
        stock: 0,
        status: '1',
        categoria_id: null,
        unidad_medida_id: null,
        es_existente: 0,
        margen_ganancia : 0
        });
        this.productoSeleccionado = null;
    }

    guardar(): void {
        if (this.formulario.invalid) return;
        const datos: Producto = this.formulario.value;
        // console.log("linea 157 : datos->", datos);
        const peticion = this.modoEdicion && this.productoSeleccionado
        ? this.productoService.actualizar(this.productoSeleccionado.id, datos)
        : this.productoService.registrar(datos);

        peticion.subscribe({
        next: res => {
            if (res.status) {
            this.obtenerProductos();
            this.cerrarModal();
            Swal.fire(
                this.modoEdicion ? 'Actualizado' : 'Registrado',
                res.msg,
                'success'
            );
            }
        },
        error: () => {
            Swal.fire('Error', 'No se pudo guardar el producto', 'error');
        }
        });
    }

    eliminar(id: number): void {
        Swal.fire({
        title: '¿Eliminar producto?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
        }).then(result => {
        if (result.isConfirmed) {
            this.productoService.eliminar(id).subscribe({
            next: res => {
                if (res.status) {
                this.obtenerProductos();
                Swal.fire('Eliminado', res.msg, 'success');
                }
            },
            error: () => Swal.fire('Error', 'No se pudo eliminar', 'error')
            });
        }
        });
    }


    // ============================
    // Métodos auxiliares:
    // ============================

    /** Retorna el nombre de la categoría según su ID o '—' si no la encuentra */
    getNombreCategoria(id: number | null): string {
        if (id == null) {
        return '—';
        }
        const cat = this.categorias.find(c => c.id === id);
        return cat ? cat.categoria : '—';
    }

    /** Retorna el nombre de la unidad de medida según su ID o '—' si no la encuentra */
    getNombreUnidad(id: number | null): string {
        if (id == null) {
        return '—';
        }
        const uni = this.unidades.find(u => u.id === id);
        return uni ? uni.nombre : '—';
    }
    }
