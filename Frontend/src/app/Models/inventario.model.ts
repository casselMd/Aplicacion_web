export interface InventarioRegistro {  
    observaciones: string,
    cantidad: number,
    producto_id: number,
    tipo_movimiento_id: number,
}
export interface InventarioListado {  
    idMovimiento: number,
            cantidad: number,
            fecha: string,
            observaciones: string,
            status: number,
            producto: {
                id: number,
                nombre: string
            },
            tipo_movimiento: {
                id: number,
                nombre: string
            },
            empleado: {
                id: number,
                nombre:string ,
                dni: string
            }
}

