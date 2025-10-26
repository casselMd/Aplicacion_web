    import { Injectable } from '@angular/core';
    import { HttpClient, HttpHeaders } from '@angular/common/http';
    import { Observable, throwError } from 'rxjs';
    import { catchError } from 'rxjs/operators';
    import { environment } from '../../environments/environment';
    import { DetalleCompraListado } from '../Models/compra.model';


    @Injectable({
    providedIn: 'root'
    })
    export class DetalleCompraService {
    private apiUrl = environment.URL + '/detalle_compra';

    constructor(private http: HttpClient) {}

    // Listar todos los detalles de una compra específica
    listarPorCompra(compraId: number): Observable<any> {
        return this.http
        .get(`${this.apiUrl}/listar-por-compra/${compraId}`, { headers: this.getHeadersAuth() })
        .pipe(catchError(this.handleError));
    }

    // Registrar un nuevo detalle
    registrar(detalle: DetalleCompraListado): Observable<any> {
        return this.http
        .post(`${this.apiUrl}/registrar`, detalle, { headers: this.getHeadersAuth() })
        .pipe(catchError(this.handleError));
    }

    // Actualizar un detalle existente (si tu backend lo permite)
    actualizar(id: number, detalle: DetalleCompraListado): Observable<any> {
        return this.http
        .put(`${this.apiUrl}/actualizar/${id}`, detalle, { headers: this.getHeadersAuth() })
        .pipe(catchError(this.handleError));
    }

    // Eliminar un detalle (por ejemplo, para retirar un producto de la compra)
    eliminar(id: number): Observable<any> {
        return this.http
        .delete(`${this.apiUrl}/eliminar/${id}`, { headers: this.getHeadersAuth() })
        .pipe(catchError(this.handleError));
    }

    private getHeadersAuth(): HttpHeaders {
        return new HttpHeaders({
        Authorization: 'Bearer ' + localStorage.getItem('token')
        });
    }

    private handleError(error: any) {
        console.error('Error en la API DetalleCompra', error);
        return throwError(() => error);
    }
    }
