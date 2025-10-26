
export interface Empleado {
    id?: number;
    nombre: string;
    apellidos?: string;
    username?: string;
    password?: string;
    status?: number;
    fecha_creado?: string;
    dni?: string;
    rol?: string;
}

export interface EmpleadoPerfil {
    id: number;
    nombre: string;
    apellidos: string;
    username: string;
    password?: string;
    dni: string;
    rol: string;
    imagen_url:string;
}

export interface EmpleadoCredenciales {
    username: string;
    password?: string;
}




