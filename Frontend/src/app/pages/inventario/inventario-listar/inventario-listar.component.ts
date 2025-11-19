    import { Component } from '@angular/core';
    import { CommonModule } from '@angular/common';
    import { FormsModule } from '@angular/forms';
    import { RouterLinkWithHref } from '@angular/router';
    import { InventarioService } from '../../../services/inventario.service';
    import Swal from 'sweetalert2';

    @Component({
    selector: 'app-inventario-listar',
    standalone: true,
    imports: [CommonModule, FormsModule, RouterLinkWithHref],
    templateUrl: './inventario-listar.component.html',
    })
    export class InventarioListarComponent {
    movimientos: any[] = [];
    movimientosFiltrados: any[] = [];
    filtro: string = '';
    movimientoSeleccionado: any;

    constructor(private inventarioService: InventarioService) {}

    ngOnInit(): void {
        this.obtenerMovimientos();
    }

    obtenerMovimientos(): void {
        this.inventarioService.listar().subscribe(res => {
        if (res.status) {
            this.movimientos = res.data;
            this.movimientosFiltrados = [...this.movimientos];
        }
        });
    }

    filtrarMovimientos(): void {
        const valor = this.filtro.toLowerCase().trim();
        this.movimientosFiltrados = this.movimientos.filter(m =>
        m.idMovimiento.toString().includes(valor) ||
        m.producto.nombre.toLowerCase().includes(valor)
        );
    }

    verDetalle(movimiento: any): void {
        this.movimientoSeleccionado = movimiento;
        const modal = document.getElementById('modalInventario');
        if (modal) modal.classList.remove('hidden');
    }

    cerrarModal(): void {
        const modal = document.getElementById('modalInventario');
        if (modal) modal.classList.add('hidden');
    }
    eliminarDetalle(id:number){
        Swal.fire({
        title: '¿Eliminar Movimiento?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
        }).then(result => {
        if (result.isConfirmed) {
            this.inventarioService.eliminar(id).subscribe({
            next: res => {
                if (res.status) {
                this.obtenerMovimientos();
                Swal.fire('Movimiento eliminado', res.msg, 'success');
                }
            },
            error: () => Swal.fire('Error', 'No se pudo eliminar', 'error')
            });
        }
        });
    }
    }
