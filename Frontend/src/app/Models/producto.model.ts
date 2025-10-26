import { UnidadMedida } from "../services/unidad-medida.service";


export interface Producto {
    id : number;
    nombre: string;
    descripcion: string;
    precio: number;
    precio_venta: number;
    stock : number;
    categoria: {id:number ,nombre:string }; 
    unidad_medida: UnidadMedida;
    status : number;
    es_existente : number;
    margen_ganancia : number;
}










