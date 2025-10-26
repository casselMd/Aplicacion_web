    import { Injectable } from '@angular/core';
    import { HttpClient, HttpHeaders } from '@angular/common/http';
    import { Observable, throwError } from 'rxjs';
    import { catchError } from 'rxjs/operators';
    import { environment } from '../../environments/environment';
    import { Proveedor } from '../Models/proveedor.model';



    @Injectable({
    providedIn: 'root'
    })
    export class ProveedorService {
    private apiUrl = environment.URL + '/proveedor';

    constructor(private http: HttpClient) {}

    listar(): Observable<any> {
        return this.http.get(`${this.apiUrl}/listar`, { headers: this.getHeadersAuth() })
        .pipe(catchError(this.handleError));
    }

    registrar(proveedor: Proveedor): Observable<any> {
        return this.http.post(`${this.apiUrl}/registrar`, proveedor, { headers: this.getHeadersAuth() })
        .pipe(catchError(this.handleError));
    }

    actualizar(id: number, proveedor: Proveedor): Observable<any> {
        return this.http.put(`${this.apiUrl}/actualizar/${id}`, proveedor, { headers: this.getHeadersAuth() })
        .pipe(catchError(this.handleError));
    }

    eliminar(id: number): Observable<any> {
        return this.http.delete(`${this.apiUrl}/eliminar/${id}`, { headers: this.getHeadersAuth() })
        .pipe(catchError(this.handleError));
    }

    private getHeadersAuth(): HttpHeaders {
        return new HttpHeaders({
        'Authorization': 'Bearer ' + localStorage.getItem('token')
        });
    }

    private handleError(error: any) {
        console.error('Error en la API', error);
        return throwError(() => error);
    }
    }
