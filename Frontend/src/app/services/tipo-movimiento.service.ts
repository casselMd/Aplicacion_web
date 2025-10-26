import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { catchError, Observable, throwError } from 'rxjs';
import { environment } from '../../environments/environment';

@Injectable({
    providedIn: 'root'
    })
    export class TipoMovimientoService {
    private apiUrl = environment.URL + '/tipomovimiento';
    constructor(private http: HttpClient) { }
    listar(): Observable<any> {
        return this.http
        .get(`${this.apiUrl}/listar`, { headers: this.getHeadersAuth() })
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
