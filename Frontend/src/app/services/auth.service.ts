    import { Injectable } from '@angular/core';
    import { BehaviorSubject, catchError, Observable, throwError } from 'rxjs';
    import { HttpClient, HttpHeaders } from '@angular/common/http';
    import { Router } from '@angular/router';
    import { environment } from '../../environments/environment';
    import {jwtDecode} from 'jwt-decode';

    @Injectable({
    providedIn: 'root'
    })
    export class AuthService {
    
        private url =environment.URL;
        constructor(
        private http: HttpClient, 
        private router: Router
        ) {}
        private userRol = new BehaviorSubject<string | null>("");

        setRol(rol: string) {
        this.userRol.next(rol);
        }

        getRol(): Observable<string | null> {
        return this.userRol.asObservable();
        }
    
        login(credentials: { username: string; password: string }):Observable<any> {
        const headers = new HttpHeaders({
            'Content-Type': 'application/json',
        });
        return this.http.post<{ data : {access_token: string, token_empleado:string },status:boolean,msg : string}>
        (`${this.url}/empleado/login`, JSON.stringify(credentials), {headers});
        }
    
        // Llama a la API para verificar la validez del token
        verificarToken(token: any): Observable<any> {
        return this.http.get(`${this.url}/empleado/validar_token/${token}`)
            .pipe(catchError(this.handleError));
        }
        private handleError(error: any) {
            console.error('Error en la API de Autenticacion', error);
            
            return throwError(() => error);
        }
        
        // Método para cerrar sesión
        logout() {
        localStorage.clear();
        this.router.navigate(['/login']);
        }
    
        getToken(){
        return localStorage.getItem('token');
        }
        getTokenEmp(){
        return localStorage.getItem('token_emp');
        }



    // loginWithGoogleCode(code: string) {
    //   const url = `${this.backendUrl}?code=${code}`;
    //   return this.http.get<any>(url);
    // }

    // guardarTokens(auth: any) {
    //   localStorage.setItem('tokenJWT', auth.tokenJWT);
    //   localStorage.setItem('token_empleado', auth.token_empleado);
    // }
    private apiUrl = 'http://localhost/Delicias/Backend';


    obtenerTokensDesdeServidor(auth_id: string): Observable<any> {
    return this.http.get(`${this.apiUrl}/AuthGoogle/session`, {
        params: { auth_id }, withCredentials: true
    }).pipe(
        catchError(this.handleError)
    );;
    }

    guardarTokens(data: any): void {
        localStorage.setItem('token', data.access_token);
        localStorage.setItem('token_emp', data.token_empleado);
    }

    solicitarRecuperacion(correo: string): Observable<any> {
        return this.http.post<any>(`${this.apiUrl}/Auth/recuperar`, { correo });
    }
    cambiarPassword(token: string, nueva: string): Observable<any> {
        return this.http.post(`${this.apiUrl}/Auth/cambiar_password`, { token, nueva });
    }
    }


    export interface TokenEmpleadoPayload {
    data:{
        id: number;
        rol: string;
        nombre_completo:string;
    },
    exp: number;
    iat: number;
    }

    export function getPayloadToken(token: string):TokenEmpleadoPayload | null {
    try {
        const decoded = jwtDecode<TokenEmpleadoPayload>(token);
        return decoded;
    } catch (error) {
        // console.error('Token inválido:', error);
        return null;
    }
    }