    import { Injectable } from '@angular/core';
    import { HttpClient, HttpHeaders } from '@angular/common/http';
    import { Observable, throwError } from 'rxjs';
    import { catchError } from 'rxjs/operators';
    import { environment } from '../../environments/environment';
    import { Cliente } from '../Models/cliente.model';



    @Injectable({
    providedIn: 'root'
    })
    export class ClienteService {
    private apiUrl = environment.URL + '/cliente';

    constructor(private http: HttpClient) {}

    listar(): Observable<any> {
        return this.http.get(`${this.apiUrl}/listar`, { headers: this.getHeadersAuth() })
        .pipe(catchError(this.handleError));
    }

    registrar(cliente: Cliente): Observable<any> {
        return this.http.post(`${this.apiUrl}/registrar`, cliente, { headers: this.getHeadersAuth() })
        .pipe(catchError(this.handleError));
    }

    actualizar(id: string, cliente: Cliente): Observable<any> {
        return this.http.put(`${this.apiUrl}/actualizar/${id}`, cliente, { headers: this.getHeadersAuth() })
        .pipe(catchError(this.handleError));
    }

    eliminar(id: string): Observable<any> {
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
