    import { Component, OnInit } from '@angular/core';
    import { FormBuilder, FormGroup, Validators, ReactiveFormsModule, FormControl,  } from '@angular/forms';
    import { CommonModule } from '@angular/common';
    import { CompraService, DetalleCompra } from '../../../services/compra.service';
    import { MetodoPagoService } from '../../../services/metodo-pago.service';
    import { ProductoService } from '../../../services/producto.service';
    import Swal from 'sweetalert2';
    import { FormsModule } from '@angular/forms';
    import { MetodoPago } from '../../../Models/metodopago.model';
    import { Producto } from '../../../Models/producto.model';
    import { Compra, CompraRegistro, DetalleCompraListado, DetalleCompraRegistro } from '../../../Models/compra.model';
    import { Proveedor } from '../../../Models/proveedor.model';
    import { ProveedorService } from '../../../services/proveedor.service';

    import { MatAutocompleteModule } from '@angular/material/autocomplete';
    import { MatFormFieldModule } from '@angular/material/form-field';
    import { MatInputModule } from '@angular/material/input';
    import { map, Observable, startWith } from 'rxjs';
    import { RouterLinkWithHref } from '@angular/router';

    @Component({

    selector: 'app-compra-registrar',
    standalone: true,
    imports: [CommonModule, ReactiveFormsModule, FormsModule,
        MatAutocompleteModule,MatFormFieldModule,MatInputModule, MatFormFieldModule,
        MatInputModule,RouterLinkWithHref
    ],
    templateUrl: './compra-registrar.component.html'
    })
    export class CompraRegistrarComponent implements OnInit {
    ventaForm!: FormGroup;

    proveedores:Proveedor[] = [];
    metodosPago: MetodoPago[] = [];
    productos: Producto[] = [];

    // Para detalle
    products: DetalleCompraRegistro[] = [];
    selectedProductId!: number | '';
    product: {  precio: number, stock: number } = { precio: 0, stock: 1 };

    mensaje: string = '';

    constructor(
        private fb: FormBuilder,
        private compraSvc: CompraService,
        private proveedorSvc: ProveedorService,
        private mpagoSvc: MetodoPagoService,
        private prodSvc: ProductoService
    ) {}
    productoSeleccionado !: Producto;
    controlProducto = new FormControl<Producto | null>(null);
    productosFiltrados$!: Observable<Producto[]>;

    filteredProductos!: Observable<Producto[]>;
    ngOnInit() {
        this.initForm();
        this.loadMasters();
        this.productosFiltrados$ = this.controlProducto.valueChanges.pipe(
        startWith(''),
        map(value => {
            const texto = typeof value === 'string' ? value : '';
            return this._filter(texto);
        })
        );
        this.controlProducto.valueChanges.subscribe(producto => {
        if (typeof producto === 'object' && producto !== null) {
        //console.log('Producto seleccionado (objeto):', producto);
        // console.log('ID del producto:', producto.id);
        this.productoSeleccionado =  producto
        //  console.log(this.productoSeleccionado);
        this.selectedProductId =  producto.id;
        this.product.precio = producto.precio;
        

        //  console.log(this.product);
        }
    });
    }
    
    private initForm() {
        this.ventaForm = this.fb.group({
        proveedor_id: [null, Validators.required],
        metodo_pago_id: [null, Validators.required],
        numero_documento : [null],
        tipo_documento : [null, Validators.required],
        fecha_documento : [null],
        observaciones: ['']
        });
    }

    private loadMasters() {
        this.proveedorSvc.listar().subscribe(r => { 
        if (r.status) {
            this.proveedores = r.data;
            this.proveedores = this.proveedores.filter(p => p.estado ===1);

        } 
        });
        this.mpagoSvc.listar().subscribe(r => {
        if (r.status) {
            this.metodosPago = r.data; 
            this.metodosPago = this.metodosPago.filter(mp => mp.status ===1 ); 

        }
        });
        this.prodSvc.listar().subscribe(r => { 
        if (r.status) {
            this.productos = r.data;
            this.productos = this.productos.filter(p => p?.es_existente === 0 && p.status ===1)
        } 
        });
    }

    
    
    // agregar Productos al carrto
    addProduct() {
        if (!this.selectedProductId || this.product.stock < 1) return;
        const detalle: DetalleCompraRegistro = {
        id_producto: +this.productoSeleccionado.id,
        nombre : this.productoSeleccionado.nombre,
        precio_unitario: this.product.precio,
        cantidad: this.product.stock,
        subtotal: +(this.productoSeleccionado.precio * this.product.stock).toFixed(2)
        };
        for (let p of this.products) {
        if(p.id_producto === detalle.id_producto){
            p.cantidad += detalle.cantidad;
            p.subtotal += detalle.subtotal;
            return;
        }
        }
        this.products.push(detalle);    
    }

    removeDetalle(i: number) {
        this.products.splice(i, 1);
    }

    getTotal(): number {
        return this.products.reduce((sum, x) => sum + x.subtotal, 0);
    }

    registrarVenta() {
        if (this.ventaForm.invalid || this.products.length === 0) {
        Swal.fire('Error', 'Datos incompletos.', 'warning');
        return;
        }
        
        const payload: CompraRegistro = {
        fecha: this.ventaForm.value.fecha_documento,
        proveedor_id: this.ventaForm.value.proveedor_id,
        metodo_pago_id: this.ventaForm.value.metodo_pago_id,
        tipo_documento : this.ventaForm.value.tipo_documento,
        numero_documento : this.ventaForm.value.numero_documento,
        observaciones: this.ventaForm.value.observaciones,
        total : this.getTotal(), 
        detalles: this.products
        };
        // console.log('payload', payload);

        this.compraSvc.registrar(payload).subscribe({
        next: res => {
            if(res.status){
            this.mensaje = res.msg;
            this.ventaForm.reset();
            this.products = [];
            Swal.fire('Registro', res.msg, 'success');
            }else{
            Swal.fire('Error', res.msg, 'error');
            }
            
        },
        error: () => {
            Swal.fire('Error', 'No se pudo registrar la Compra', 'error');
        }
        });
    }


    ///angular material
    private _filter(value: string): Producto[] {
        const filterValue = value.toLowerCase();
        return this.productos.filter(p => p.nombre.toLowerCase().includes(filterValue));
    }

    displayFn(producto: Producto): string {
        return producto && producto.nombre ? producto.nombre : '';
    }
    
    }
