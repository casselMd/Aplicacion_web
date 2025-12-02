    import { Component, OnInit } from '@angular/core';
    import { FormBuilder, FormGroup, Validators, ReactiveFormsModule, FormControl } from '@angular/forms';
    import { CommonModule } from '@angular/common';
    import { ProductoService } from '../../../services/producto.service';
    import { InventarioService } from '../../../services/inventario.service';
    import { Producto } from '../../../Models/producto.model';
    

    import { MatAutocompleteModule } from '@angular/material/autocomplete';
    import { MatFormFieldModule } from '@angular/material/form-field';
    import { MatInputModule } from '@angular/material/input';
    import { MatSelectModule } from '@angular/material/select';
    import { MatOptionModule } from '@angular/material/core';
    import { FormsModule } from '@angular/forms';
    import { map, Observable, startWith } from 'rxjs';
    import Swal from 'sweetalert2';
    import { TipoMovimientoService } from '../../../services/tipo-movimiento.service';
    import { TipoMovimiento } from '../../../Models/tipomovimiento.model';
    import { RouterLinkWithHref } from '@angular/router';

    @Component({
    selector: 'app-inventarioregistrar',
    standalone: true,
    imports: [
        CommonModule, ReactiveFormsModule, FormsModule,
        MatAutocompleteModule, MatFormFieldModule, MatInputModule,
        MatSelectModule, MatOptionModule, RouterLinkWithHref
    ],
    templateUrl: './inventario-registrar.component.html'
    })
    export class InventarioRegistrarComponent implements OnInit {
    inventarioForm!: FormGroup;

    productos: Producto[] = [];
    tiposMovimiento: TipoMovimiento[] = [];

    controlProducto = new FormControl<Producto | null>(null);
    productosFiltrados$!: Observable<Producto[]>;
    productoSeleccionado: Producto | null = null;

    constructor(
        private fb: FormBuilder,
        private productoSvc: ProductoService,
        private tipoMovSvc: TipoMovimientoService,
        private inventarioSvc: InventarioService
    ) {}

    ngOnInit() {
        this.inventarioForm = this.fb.group({
        stock: [null, [Validators.required, Validators.min(1)]],
        observaciones: [''],
        tipo_movimiento_id: [null, Validators.required] 
        });

        this.productoSvc.listar().subscribe(r => {
        if (r.status) {
            this.productos = r.data;
            this.productos = this.productos.filter(p => p?.es_existente === 1 && p.status ===1)
            this.inicializarFiltroProductos();
        }
        });

        this.tipoMovSvc.listar().subscribe(r => {
        if (r.status) this.tiposMovimiento = r.data; 
        });

        this.productosFiltrados$ = this.controlProducto.valueChanges.pipe(
        startWith(''),
        map(value => {
            const texto = typeof value === 'string' ? value : '';
            return this._filter(texto);
        })
        );

        this.controlProducto.valueChanges.subscribe(producto => {
        if (typeof producto === 'object' && producto !== null) {
            this.productoSeleccionado = producto;
        }
        });
    }

    private inicializarFiltroProductos() {
    this.productosFiltrados$ = this.controlProducto.valueChanges.pipe(
        startWith(''),
        map(value => {
            const texto = typeof value === 'string' ? value : value?.nombre || '';
            return texto ? this._filter(texto) : this.productos.slice();
        })
    );
}

    private _filter(value: string): Producto[] {
        const filterValue = value.toLowerCase();
        return this.productos.filter(p => p.nombre.toLowerCase().includes(filterValue));
    }

    displayFn(producto: Producto): string {
        return producto && producto.nombre ? producto.nombre : '';
    }

    registrarInventario() {
        if (!this.productoSeleccionado || this.inventarioForm.invalid) {
        Swal.fire('Error', 'Completa todos los campos obligatorios.', 'warning');
        return;
        }

        const payload = {
        producto_id: this.productoSeleccionado.id,
        cantidad: this.inventarioForm.value.stock,
        observaciones: this.inventarioForm.value.observaciones,
        tipo_movimiento_id: this.inventarioForm.value.tipo_movimiento_id,
        fecha: new Date().toISOString().split('T')[0]
        };
        console.log('Payload inventario:', payload); 

        this.inventarioSvc.registrar(payload).subscribe({
        next: (res) => {
            console.log('Respuesta:', res);
            if (res.status) {
            Swal.fire('Éxito', 'Movimiento registrado correctamente.', 'success');
            this.inventarioForm.reset();
            this.controlProducto.reset();
            this.productoSeleccionado = null;
            } else {
                Swal.fire('Error', res.msg, 'error');
            }
        },
        error: () => {
            Swal.fire('Error', 'Ocurrió un error al registrar el movimiento.', 'error');
        }
        });
    }
    }
