    import { Injectable } from '@angular/core';
    import { HttpClient, HttpHeaders } from '@angular/common/http';
    import { Observable, throwError } from 'rxjs';
    import { catchError } from 'rxjs/operators';
    import { environment } from '../../environments/environment';

    @Injectable({
    providedIn: 'root'
    })
    export class InventarioService {
    private apiUrl = environment.URL + '/inventario';

    constructor(private http: HttpClient) {}

    listar(): Observable<any> {
        return this.http
        .get(`${this.apiUrl}/listar`, { headers: this.getHeadersAuth() })
        .pipe(catchError(this.handleError));
    }
    registrar(mov: any): Observable<any> {
        return this.http
        .post(`${this.apiUrl}/registrar`, mov , { headers: this.getHeadersAuth() })
        .pipe(catchError(this.handleError));
    }

    ver(id: number): Observable<any> {
        return this.http
        .get(`${this.apiUrl}/ver/${id}`, { headers: this.getHeadersAuth() })
        .pipe(catchError(this.handleError));
    }
    eliminar(id: number): Observable<any> {
        return this.http.delete(`${this.apiUrl}/eliminar/${id}`, { headers: this.getHeadersAuth() })
        .pipe(catchError(this.handleError));
    }
    
    private getHeadersAuth(): HttpHeaders {
        return new HttpHeaders({
        Authorization: 'Bearer ' + localStorage.getItem('token'),
        'Authorization-Empleado': localStorage.getItem('token_emp') || ''
        });
    }

    private handleError(error: any) {
        console.error('Error API Inventario', error);
        return throwError(() => error);
    }
    }
