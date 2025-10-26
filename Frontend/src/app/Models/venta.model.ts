
// Modelos para listar, registrar y manipular ventas y sus detalles
/*
export interface Venta {
    id: number;
    empleado_id: number;
    cliente_id: number;
    metodo_pago_id: number;
    fecha: string;
    total: number;
    status: number;
    }
*/
export interface VentaRegistro {
    id?:number,
    cliente_id: number;
    metodo_pago_id: number;
    es_delivery :number;
    observaciones?: string;
    total: number;
    detalles: DetalleVentaRegistro[];
}

export interface DetalleVenta {
    id: number;
    venta_id: number;
    producto_id: number;
    precio_unitario: number;
    cantidad: number;
    subtotal: number;
}

export interface DetalleVentaRegistro {
    producto_id: number;
    nombre: string; // Para mostrar en tabla
    precio_unitario: number;
    cantidad: number;
    subtotal: number;
}


export interface Venta {
    id: number;
    fecha: string;
    total: number;
    status: number;
    empleado: {
        id: number;
        nombre: string;
        dni: string;
    };
    cliente: {
        id: string;
        nombre: string;
    };
    metodo_pago: {
        id: number;
        nombre: string;
    };
    es_delivery :number;
    detalles?: DetalleVentaListado[]; // Solo cuando se necesite ver detalle completo
}



export interface DetalleVentaRegistro {
    producto_id: number;
    precio_unitario: number;
    cantidad: number;
    subtotal: number;
}

export interface DetalleVentaListado {
    id: number;
    venta_id: number;
    producto: {
        id: number;
        nombre: string;
};
    precio_unitario: number;
    cantidad: number;
    subtotal: number;
}

export interface VentaPorTipoAtencion {
    mes: string;
    canal: string;
    total: number;
}
