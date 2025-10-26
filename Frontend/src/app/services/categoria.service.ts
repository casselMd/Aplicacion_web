// categoria.service.ts
import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { catchError } from 'rxjs/operators';
import { environment } from '../../environments/environment';
import { Categoria } from '../Models/categoria.model';


@Injectable({
    providedIn: 'root'
    })
    export class CategoriaService {
    private apiUrl = environment.URL + '/categoria';

    constructor(private http: HttpClient) {}

    listar(): Observable<any> {
        return this.http.get(`${this.apiUrl}/listar`, { headers: this.getHeadersAuth() })
        .pipe(catchError(this.handleError));
    }

    ver(id: number): Observable<any> {
        return this.http.get(`${this.apiUrl}/ver/${id}`, { headers: this.getHeadersAuth() })
        .pipe(catchError(this.handleError));
    }

    registrar(categoria: Categoria): Observable<any> {
        return this.http.post(`${this.apiUrl}/registrar`, categoria, { headers: this.getHeadersAuth() })
        .pipe(catchError(this.handleError));
    }

    actualizar(id: number, categoria: Categoria): Observable<any> {
        return this.http.put(`${this.apiUrl}/actualizar/${id}`, categoria, { headers: this.getHeadersAuth() })
        .pipe(catchError(this.handleError));
    }

    eliminar(id: number): Observable<any> {
        return this.http.delete(`${this.apiUrl}/eliminar/${id}`, { headers: this.getHeadersAuth() })
        .pipe(catchError(this.handleError));
    }

    private handleError(error: any) {
        console.error('Error en la API', error);
        return throwError(() => error);
    }

    private getHeadersAuth(): HttpHeaders {
        return new HttpHeaders({
        'Authorization': 'Bearer ' + localStorage.getItem('token')
        });
    }
}
