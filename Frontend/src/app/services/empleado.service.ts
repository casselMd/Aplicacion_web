    import { Injectable } from '@angular/core';
    import { HttpClient, HttpHeaders } from '@angular/common/http';
    import { Observable, throwError } from 'rxjs';
    import { catchError } from 'rxjs/operators';
    import { environment } from '../../environments/environment';
    import { Empleado, EmpleadoCredenciales } from '../Models/empleado.model';



    @Injectable({
    providedIn: 'root'
    })
    export class EmpleadoService {
    private apiUrl = environment.URL + '/empleado';

    constructor(private http: HttpClient) {}

    listar(): Observable<any> {
        return this.http
        .get(`${this.apiUrl}/listar`, { headers: this.getHeadersAuth() })
        .pipe(catchError(this.handleError));
    }

    ver(id: number): Observable<any> {
        return this.http
        .get(`${this.apiUrl}/ver/${id}`, { headers: this.getHeadersAuth() })
        .pipe(catchError(this.handleError));
    }

    registrar(empleado: Empleado): Observable<any> {
        return this.http
        .post(`${this.apiUrl}/registrar`, empleado, { headers: this.getHeadersAuth() })
        .pipe(catchError(this.handleError));
    }

    actualizar(id: number, empleado: Empleado): Observable<any> {
        return this.http
        .put(`${this.apiUrl}/actualizar/${id}`, empleado, { headers: this.getHeadersAuth() })
        .pipe(catchError(this.handleError));
    }

    actualizarDatosPerfil(id: number, empleado: Empleado): Observable<any> {
        return this.http
        .put(`${this.apiUrl}/actualizarDatosPersonales`, empleado, { headers: this.getHeadersAuth() })
        .pipe(catchError(this.handleError));
    }
    actualizarCredenciales(empleadoCred: EmpleadoCredenciales): Observable<any> {
        return this.http
        .put(`${this.apiUrl}/actualizarCredenciales`, empleadoCred, { headers: this.getHeadersAuth() })
        .pipe(catchError(this.handleError));
    }

    eliminar(id: number): Observable<any> {
        return this.http
        .delete(`${this.apiUrl}/eliminar/${id}`, { headers: this.getHeadersAuth() })
        .pipe(catchError(this.handleError));
    }

    private getHeadersAuth(): HttpHeaders {
        return new HttpHeaders({
        Authorization: 'Bearer ' + localStorage.getItem('token'),
        'Authorization-Empleado': localStorage.getItem('token_emp') || ''
        });
    }

    private handleError(error: any) {
        console.error('Error en la API Empleado', error);
        return throwError(() => error);
    }
    }
