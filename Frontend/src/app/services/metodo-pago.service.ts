    import { Injectable } from '@angular/core';
    import { HttpClient, HttpHeaders } from '@angular/common/http';
    import { Observable, throwError } from 'rxjs';
    import { catchError } from 'rxjs/operators';
    import { environment } from '../../environments/environment';
    import { MetodoPago } from '../Models/metodopago.model';



    @Injectable({
    providedIn: 'root'
    })
    export class MetodoPagoService {
    private apiUrl = environment.URL + '/metodopago';

    constructor(private http: HttpClient) {}

    listar(): Observable<any> {
        return this.http.get(`${this.apiUrl}/listar`, { headers: this.getHeadersAuth() })
        .pipe(catchError(this.handleError));
    }

    registrar(metodo: MetodoPago): Observable<any> {
        return this.http.post(`${this.apiUrl}/registrar`, metodo, { headers: this.getHeadersAuth() })
        .pipe(catchError(this.handleError));
    }

    actualizar(id: number, metodo: MetodoPago): Observable<any> {
        return this.http.put(`${this.apiUrl}/actualizar/${id}`, metodo, { headers: this.getHeadersAuth() })
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
