
export interface Proveedor {
    id: number;
    nombre: string;
    ruc?: string;
    direccion?: string;
    telefono?: string;
    tipo?: string;
    observaciones?: string;
    estado?: number;
}


///             Proveedor - Compra

export interface ProveedorCompraListar{
    id:number,
    nombre: string ,
    ruc : string 
}
