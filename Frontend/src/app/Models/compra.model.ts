
import { Empleado } from './empleado.model';
import { MetodoPago } from './metodopago.model';
import { ProveedorCompraListar } from './proveedor.model';
import { Producto } from './producto.model';


///         Listar 
export interface CompraListado {
    id: number;
    empleado: EmpleadoCompraListar;
    proveedor: ProveedorCompraListar;
    fecha: string;
    total: number;
}

// Listar con detalles
export interface Compra {
    id : number;
    empleado: EmpleadoCompraListar;
    fecha: string; // ISO string
    total: number;
    status: number;
    proveedor: ProveedorCompraListar;
    tipo_documento: 'ninguno' | 'boleta' | 'factura';
    numero_documento?: string;
    metodo_pago: MetodoPago;
    observaciones?: string;
    fecha_registro?: string; // ISO string
    detalles : DetalleCompraListado[]
}
export interface CompraConDetalles {
    id : number;
    empleado: EmpleadoCompraListar;
    fecha: string; // ISO string
    total: number;
    status: number;
    proveedor: ProveedorCompraListar;
    tipo_documento: 'ninguno' | 'boleta' | 'factura';
    numero_documento?: string;
    metodo_pago: {id:number, nombre:string};
    observaciones?: string;
    fecha_registro?: string; // ISO string
    detalles : VerDetalleCompraConListado[]
}
export interface DetalleCompraListado {
    id? : number;
    id_producto: number;
    precio_unitario: number;
    cantidad: number;
    subtotal: number;
}
export interface VerDetalleCompraConListado {
    id? : number;
    producto: {id: number, nombre: string};
    precio_unitario: number;
    cantidad: number;
    subtotal: number;
}

//          Registrar 
export interface CompraRegistro {
    fecha?: string; // ISO string
    total: number;
    proveedor_id: number;
    tipo_documento: 'ninguno' | 'boleta' | 'factura';
    numero_documento?: string | null;
    metodo_pago_id?: number;
    observaciones?: string;
    detalles : DetalleCompraRegistro[]
}

export interface DetalleCompraRegistro {
    id_producto: number;
    nombre ?: string;
    cantidad: number;
    precio_unitario: number;
    subtotal : number;
}




export interface EmpleadoCompraListar{
    id:number,
    nombre: string,
    dni:string
}


