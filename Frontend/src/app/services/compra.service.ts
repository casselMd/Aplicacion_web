    import { Injectable } from '@angular/core';
    import { HttpClient, HttpHeaders } from '@angular/common/http';
    import { Observable, throwError } from 'rxjs';
    import { catchError } from 'rxjs/operators';
    import { environment } from '../../environments/environment';
    import { Compra, CompraRegistro } from '../Models/compra.model';

    export interface DetalleCompra {
    id_producto: number;
    precio_unitario: number;
    cantidad: number;
    subtotal: number;
    }

    export interface CompraConDetalle {
    empleado_id: number;
    fecha: string;
    total: number;
    proveedor_id?: number;
    tipo_documento: 'ninguno' | 'boleta' | 'factura';
    numero_documento?: string;
    metodo_pago_id: number;
    observaciones?: string;
    detalles: DetalleCompra[];
    }

    @Injectable({
    providedIn: 'root'
    })
    export class CompraService {
    private apiUrl = environment.URL + '/compra';

    constructor(private http: HttpClient) {}

    registrar(compra: CompraRegistro): Observable<any> {
        return this.http
        .post(this.apiUrl+'/registrar', compra, { headers: this.getHeadersAuth() })
        .pipe(catchError(this.handleError));
    }
    listar(): Observable<any>{
        return this.http
        .get(this.apiUrl+'/listar', { headers: this.getHeadersAuth() })
        .pipe(catchError(this.handleError));
    }
    private getHeadersAuth(): HttpHeaders {
        return new HttpHeaders({
        Authorization: 'Bearer ' + localStorage.getItem('token'),
        'Authorization-Empleado': localStorage.getItem('token_emp') ||''
        });
    }
    eliminar(id: number): Observable<any> {
        return this.http.delete(`${this.apiUrl}/eliminar/${id}`, { headers: this.getHeadersAuth() })
        .pipe(catchError(this.handleError));
    }
    ver(id:number): Observable<any>{
        return this.http
        .get(this.apiUrl+'/ver/'+id, { headers: this.getHeadersAuth() })
        .pipe(catchError(this.handleError));
    }

    private handleError(err: any) {
        console.error('Error API Compra', err);
        return throwError(() => err);
    }
    }
