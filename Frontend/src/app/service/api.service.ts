import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
    providedIn: 'root'
    })
    export class ApiService {
    private apiUrl = 'http://localhost/pasteleria_api'; // tu backend en XAMPP

    constructor(private http: HttpClient) {}

    // Login
    login(email: string, password: string): Observable<any> {
        return this.http.post(`${this.apiUrl}/auth/login.php`, { email, password });
    }

    // Listar productos
    getProductos(): Observable<any> {
        return this.http.get(`${this.apiUrl}/productos/listar.php`);
    }
}
