    import { Injectable } from '@angular/core';
    import { HttpClient, HttpHeaders } from '@angular/common/http';
    import { BehaviorSubject, Observable, throwError } from 'rxjs';
    import { catchError } from 'rxjs/operators';
    import { environment } from '../../environments/environment';
    import { Producto } from '../Models/producto.model';



    @Injectable({
    providedIn: 'root'
    })
    export class ProductoService {
    private apiUrl = environment.URL + '/producto';

    private stockBajoSubject = new BehaviorSubject<any[]>([]);
    productosStockBajo$ = this.stockBajoSubject.asObservable();

    constructor(private http: HttpClient) {
        this.actualizarProductosBajoStock();
    }

    listar(): Observable<any> {
        return this.http
        .get(`${this.apiUrl}/listar`, { headers: this.getHeadersAuth() })
        .pipe(catchError(this.handleError));
    }

    registrar(producto: Producto): Observable<any> {
        return this.http
        .post(`${this.apiUrl}/registrar`, producto, { headers: this.getHeadersAuth() })
        .pipe(catchError(this.handleError));
    }

    actualizar(id: number, producto: Producto): Observable<any> {
        return this.http
        .put(`${this.apiUrl}/actualizar/${id}`, producto, { headers: this.getHeadersAuth() })
        .pipe(catchError(this.handleError));
    }

    eliminar(id: number): Observable<any> {
        return this.http
        .delete(`${this.apiUrl}/eliminar/${id}`, { headers: this.getHeadersAuth() })
        .pipe(catchError(this.handleError));
    }
    actualizarProductosBajoStock(): void {
        this.http.get<any>(`${this.apiUrl}/productos_bajo_stock`, { headers: this.getHeadersAuth() }).subscribe({
        next: (res) => {
            if (res.status) this.stockBajoSubject.next(res.data);
        },
        error: () => this.stockBajoSubject.next([])
        });
    }



    private getHeadersAuth(): HttpHeaders {
        return new HttpHeaders({
        Authorization: 'Bearer ' + localStorage.getItem('token'),
        });
    }

    private handleError(error: any) {
        console.error('Error en la API', error);
        return throwError(() => error);
    }
    }
