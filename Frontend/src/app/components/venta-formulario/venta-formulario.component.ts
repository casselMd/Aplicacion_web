    import { Component, EventEmitter, Input, OnInit, Output } from '@angular/core';
    import { FormBuilder, FormGroup, Validators, FormControl, ReactiveFormsModule } from '@angular/forms';
    import { CommonModule } from '@angular/common';
    import { FormsModule } from '@angular/forms';


    import Swal from 'sweetalert2';

    import { MatAutocompleteModule } from '@angular/material/autocomplete';
    import { MatFormFieldModule } from '@angular/material/form-field';
    import { MatInputModule } from '@angular/material/input';
    import { map, Observable, startWith } from 'rxjs';
    import { RouterLinkWithHref } from '@angular/router';
    import { Cliente } from '../../Models/cliente.model';
    import { MetodoPago } from '../../Models/metodopago.model';
    import { Producto } from '../../Models/producto.model';
    import { DetalleVentaRegistro, Venta, VentaRegistro } from '../../Models/venta.model';
    import { VentaService } from '../../services/venta.service';
    import { ClienteService } from '../../services/cliente.service';
    import { MetodoPagoService } from '../../services/metodo-pago.service';
    import { ProductoService } from '../../services/producto.service';

    @Component({
    selector: 'app-formulario-venta',
    standalone: true,
    imports: [
        CommonModule,
        FormsModule,
        ReactiveFormsModule,
        MatAutocompleteModule,
        MatFormFieldModule,
        MatInputModule,
    ],
    templateUrl: './venta-formulario.component.html',
    styleUrl: './venta-formulario.component.css'
    })
    export class FormularioVentaComponent implements OnInit {
    ventaForm!: FormGroup;

    clientes: Cliente[] = [];
    metodosPago: MetodoPago[] = [];
    productos: Producto[] = [];

    // Detalle
    detalles: DetalleVentaRegistro[] = [];
    productoSeleccionado!: Producto;
    controlProducto = new FormControl<Producto | null>(null);
    productosFiltrados$!: Observable<Producto[]>;

    producto = { precio: 0, cantidad: 1 };

    mensaje = '';
    
    @Input() ventaEditar?: Venta;
    @Output() guardado = new EventEmitter<VentaRegistro>();

    constructor(
        private fb: FormBuilder,
        private ventaSvc: VentaService,
        private clienteSvc: ClienteService,
        private mpagoSvc: MetodoPagoService,
        private productoSvc: ProductoService
    ) {}

    ngOnInit() {
        this.initForm();
        this.loadMasters();
        
        if (this.ventaEditar) {
        this.patchForm(this.ventaEditar);
        
        }
        
        //filtro de productos
        this.productosFiltrados$ = this.controlProducto.valueChanges.pipe(
        startWith(''),
        map(value => {
            const texto = typeof value === 'string' ? value : '';
            return this._filter(texto);
        })
        );
        //comprobar si el producto ya se ha seleccionado una vez
        this.controlProducto.valueChanges.subscribe(prod => {
        if (typeof prod === 'object' && prod !== null) {
            this.productoSeleccionado = prod;
            this.producto.precio = prod.precio_venta;
        }
        });

        
    }
    private patchForm(v: Venta) {
        const id = (v.cliente as any).id;
        this.ventaForm.patchValue({
        cliente_id:  id,
        metodo_pago_id: v.metodo_pago.id
        });
        this.detalles = (v.detalles ?? []).map(d => ({
        producto_id: d.producto.id,
        nombre: d.producto.nombre,
        precio_unitario: d.precio_unitario,
        cantidad: d.cantidad,
        subtotal: d.subtotal
        }));
    }


    private initForm() {
        this.ventaForm = this.fb.group({
        cliente_id: [null, Validators.required],
        metodo_pago_id: [null, Validators.required],
        es_delivery : 0
        });
    }

    private loadMasters() {
        this.clienteSvc.listar().subscribe(r => { 
        if (r.status) {this.clientes = r.data;
            this.clientes =  this. clientes.filter(c => c.status ===1 );
        } 
        });
        this.mpagoSvc.listar().subscribe(r => { 
        if (r.status) {this.metodosPago = r.data;
            this.metodosPago =  this.metodosPago.filter(mp => mp.status ===1)
        }});
        this.productoSvc.listar().subscribe(r => { 
            if (r.status) {
            this.productos = r.data;
            this.productos = this.productos.filter(p => p?.categoria?.nombre?.toLowerCase() !== "consumibles" && p.status===1 )
            } 
            

        });
        
    }

    addProducto() {
        if (!this.productoSeleccionado || this.producto.cantidad < 1) return;

        const detalle: DetalleVentaRegistro = {
        producto_id: this.productoSeleccionado.id,
        nombre: this.productoSeleccionado.nombre,
        precio_unitario: this.productoSeleccionado.precio_venta,
        cantidad: this.producto.cantidad,
        subtotal: +(this.productoSeleccionado.precio * this.producto.cantidad).toFixed(2)
        };

        // Evitar productos duplicados
        for (let detalleGuardado of this.detalles) {
        if (detalleGuardado.producto_id === detalle.producto_id) {
            let diferenciaStock = this.productoSeleccionado.stock - (detalleGuardado.cantidad + detalle.cantidad);
            let unidadesDisponibles = this.productoSeleccionado.stock - detalleGuardado.cantidad;

            if (this.productoSeleccionado.es_existente === 0 &&  diferenciaStock < 0) {
            Swal.fire('Error', `Stock insuficiente. Solo quedan
                ${unidadesDisponibles}
                unidades de ${detalleGuardado.nombre}`, 'error');
            this.producto.cantidad = this.productoSeleccionado.stock - detalleGuardado.cantidad;
            return;
            }

            detalleGuardado.cantidad += detalle.cantidad;
            detalleGuardado.subtotal += detalle.subtotal;
            return;
        }
        }

        // Si no es duplicado, validar que haya stock suficiente
        if (this.productoSeleccionado.es_existente === 0 &&   detalle.cantidad > this.productoSeleccionado.stock) {
        Swal.fire('Error', `Stock insuficiente. Solo quedan
            ${this.productoSeleccionado.stock}
            unidades de ${detalle.nombre}`, 'error');
        this.producto.cantidad = this.productoSeleccionado.stock;
        return;
        }

        // Si pasa la validación, se agrega como nuevo producto
        this.detalles.push(detalle);


    }

    removeDetalle(index: number) {
        this.detalles.splice(index, 1);
    }

    getTotal(): number {
        return this.detalles.reduce((sum, d) => sum + d.subtotal, 0);
    }

    registrarVenta() {
        if (this.ventaForm.invalid || this.detalles.length === 0) {
        Swal.fire('Error', 'Completa los datos.', 'warning');
        return;
        }
        // si estamos editando, emitimos guardado
        if (this.ventaEditar) {
        
        const payload: VentaRegistro = {
            id: this.ventaEditar.id,
            cliente_id: this.ventaForm.value.cliente_id,
            metodo_pago_id: this.ventaForm.value.metodo_pago_id,
            es_delivery : this.ventaForm.value.es_delivery,
            observaciones: this.ventaForm.value.observaciones,
            total: this.getTotal(),
            detalles: this.detalles
        };
        this.guardado.emit(payload);
        } else{

            const payload: VentaRegistro = {
            total: this.getTotal(),
            metodo_pago_id: this.ventaForm.value.metodo_pago_id,
            es_delivery : this.ventaForm.value.es_delivery,
            cliente_id: this.ventaForm.value.cliente_id,
            observaciones: this.ventaForm.value.observaciones,
            detalles: this.detalles
            };
            // console.log('payload Venta : ', payload);

            this.ventaSvc.registrar(payload).subscribe({
            next: () => {
                this.mensaje = 'Venta registrada con éxito';
                this.ventaForm.reset();
                this.detalles = [];
                Swal.fire('Registrado', 'Venta pendiente por confirmar', 'success');
            },
            error: () => {
                Swal.fire('Error', 'No se pudo registrar la venta', 'error');
            }
            });
        }
    }

    private _filter(value: string): Producto[] {
        const filterValue = value.toLowerCase();
        return this.productos.filter(p => p.nombre.toLowerCase().includes(filterValue));
    }

    displayFn(producto: Producto): string {
        return producto?.nombre ?? '';
    }
    }

