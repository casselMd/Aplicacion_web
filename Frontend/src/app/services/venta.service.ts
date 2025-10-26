    import { HttpClient, HttpHeaders } from '@angular/common/http';
    import { Injectable } from '@angular/core';
    import { VentaRegistro } from '../Models/venta.model';
    import { Observable, throwError } from 'rxjs';
    import { catchError } from 'rxjs/operators';
    import { environment } from '../../environments/environment';

    @Injectable({
    providedIn: 'root'
    })
    export class VentaService {
    private apiUrl = environment.URL + '/venta';

    constructor(private http: HttpClient) {}

    registrar(venta: VentaRegistro): Observable<any> {
        return this.http
        .post(this.apiUrl + '/registrar', venta, { headers: this.getHeadersAuth() })
        .pipe(catchError(this.handleError));
    }

    listar(): Observable<any> {
        return this.http
        .get(this.apiUrl + '/listar', { headers: this.getHeadersAuth() })
        .pipe(catchError(this.handleError));
    }
    
    actualizar(id: number, venta: VentaRegistro): Observable<any> {
        return this.http.put(`${this.apiUrl}/actualizar/${id}`, venta, { headers: this.getHeadersAuth() })
        .pipe(catchError(this.handleError));
    }
        
    eliminar(id: number): Observable<any> {
        return this.http
        .delete(`${this.apiUrl}/eliminar/${id}`, { headers: this.getHeadersAuth() })
        .pipe(catchError(this.handleError));
    }

    ver(id: number): Observable<any> {
        return this.http
        .get(this.apiUrl + '/ver/' + id, { headers: this.getHeadersAuth() })
        .pipe(catchError(this.handleError));
    }
    confirmar(id: number): Observable<any> {
        return this.http
        .put(`${this.apiUrl}/confirmar/${id}`,{}, { headers: this.getHeadersAuth() })
        .pipe(catchError(this.handleError));
    }
    totalVentasDelDia(): Observable<any> {
        return this.http
        .get(this.apiUrl + '/total_ventas_del_dia', { headers: this.getHeadersAuth() })
        .pipe(catchError(this.handleError));
    }
    productosMasVendidos(limit:number): Observable<any> {
        return this.http
        .get(this.apiUrl + '/productos_mas_vendidos/'+ limit, { headers: this.getHeadersAuth() })
        .pipe(catchError(this.handleError));
    }
    ventasPorDiaSemana(): Observable<any> {
        return this.http
        .get(this.apiUrl + '/ventas_por_dia_semana/', { headers: this.getHeadersAuth() })
        .pipe(catchError(this.handleError));
    }
    
    ventasMensual(): Observable<any> {
        return this.http
        .get(this.apiUrl + '/ventas_mensual/', { headers: this.getHeadersAuth() })
        .pipe(catchError(this.handleError));
    }
    
    private getHeadersAuth(): HttpHeaders {
        return new HttpHeaders({
            Authorization: 'Bearer ' + localStorage.getItem('token'),
            'Authorization-Empleado': localStorage.getItem('token_emp') || ''
        });
    }

    private handleError(err: any) {
        console.error('Error API Venta', err);
        return throwError(() => err);
    }
    }
