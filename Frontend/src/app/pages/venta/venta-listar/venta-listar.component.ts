    import { ChangeDetectorRef, Component, NgZone } from '@angular/core';
    import { CommonModule } from '@angular/common';
    import { FormsModule, ReactiveFormsModule } from '@angular/forms';
    import Swal from 'sweetalert2';
    import { VentaService } from '../../../services/venta.service';
    import { Venta } from '../../../Models/venta.model';
    import { RouterLinkWithHref } from '@angular/router';
    import { VentaRegistrarComponent } from '../venta-registrar/venta-registrar.component';
    import { FormularioVentaComponent } from '../../../components/venta-formulario/venta-formulario.component';
    import { AuthService } from '../../../services/auth.service';
    import html2pdf from 'html2pdf.js';
    import { LoaderComponent } from '../../../components/shared/loader/loader.component';
    import { firstValueFrom } from 'rxjs';
    @Component({
    selector: 'app-venta-listar',
    standalone: true,
    imports: [CommonModule, ReactiveFormsModule, FormsModule, RouterLinkWithHref,
        FormularioVentaComponent, LoaderComponent
    ],
    templateUrl: './venta-listar.component.html',
    styleUrl: './venta-listar.component.css'
    })
    export class VentaListarComponent {
    filtro: string = '';
    ventas: Venta[] = [];
    ventasFiltradas: Venta[] = [];
    ventaSeleccionada!: Venta;
    rol !: string| null ;

    constructor(private ventaService: VentaService,
        private zone: NgZone,
        private cd: ChangeDetectorRef, 
        private authService:AuthService) {}

    ngOnInit(): void {
        this.obtenerVentas();
        this.authService.getRol().subscribe(
        (res) => {this.rol =  res;}
        )
    }

    obtenerVentas(): void {
        this.ventaService.listar().subscribe(
        res => {
            if (res.status) {
            this.ventas = res.data;
            this.ventasFiltradas = [...this.ventas];
            }
        }
        );
    }

    filtrarVentas() {
        const filtroNormalizado = this.filtro.toLowerCase().trim();

        this.ventasFiltradas = this.ventas.filter(venta =>
        venta.id.toString().includes(filtroNormalizado)
        );
    }

    cancelarVenta(id: number) {
        Swal.fire({
        title: '¿Eliminar venta?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
        }).then(result => {
        if (result.isConfirmed) {
            this.ventaService.eliminar(id).subscribe({
            next: res => {
                if (res.status) {
                this.obtenerVentas();
                Swal.fire('Venta eliminada', res.msg, 'success');
                }
            },
            error: () => Swal.fire('Error', 'No se pudo eliminar', 'error')
            });
        }
        });
    }

    verVenta(id: number) {
        this.ventaService.ver(id).subscribe(res => {
        if (res.status) {
            this.ventaSeleccionada = res.data;
            console.log(res)
            this.abrirModalDetalleVenta();
        }
        });
    }

    abrirModalDetalleVenta() {
        const modal = document.getElementById('modalVenta');
        if (modal) modal.classList.remove('hidden');
    }

    cerrarModal() {
        const modal = document.getElementById('modalVenta');
        if (modal) modal.classList.add('hidden');
    }


    //editar venta
    ventaEnEditar?: Venta;

    editarVenta(id:number) {
        this.ventaService.ver(id).subscribe({
        next : res => {
            if (res.status ) {
                this.ventaEnEditar = res.data;
                
            }
        }
        , error : (err)=>{console.log(err)}
        });
        
    }

    cerrarEdicion() {
        this.ventaEnEditar = undefined;
    }

    actualizarVenta(payload: any) {
        this.ventaService.actualizar(payload.id, payload).subscribe({
            next: res => {
            if (res.status) {
            Swal.fire('Actualizado', 'Venta actualizada correctamente', 'success');
            this.obtenerVentas();
            this.cerrarEdicion();
            }
            }, error: (err) => Swal.fire('No se pudo actualizar',err , 'error')
        });
    }
    confirmarVenta(id:number){
        Swal.fire({
        title: '¿Confirmar venta?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, Confirmar',
        cancelButtonText: 'Cancelar'
        }).then(result => {
        if (result.isConfirmed) {
            this.ventaService.confirmar(id).subscribe({
                next: res=>{
                if(res.status){
                    Swal.fire('Confirmado', 'Venta confirmada correctamente', 'success');
                    this.obtenerVentas();
                }
                },
                error: (err) =>{Swal.fire('No se pudo actualizar',err , 'error')}
            })
        }
        });
        
    }

    generandoPDF = false;

    async generarPDF(id: number) {
        this.generandoPDF = true;

        try {
        // 1. Obtener los datos de la compra
        const res = await firstValueFrom(this.ventaService.ver(id));
        if (!res.status) return;

        this.ventaSeleccionada = res.data;

        // 2. Forzar detección de cambios para que el DOM se actualice
        this.cd.detectChanges();

        // 3. Esperar a que Angular termine de estabilizarse (render completo)
        await new Promise<void>((resolve) => {
            this.zone.onStable.pipe().subscribe(() => {
            requestAnimationFrame(() => resolve());
            });
        });

        // 4. Mostrar loader por al menos 50ms
        await new Promise((r) => setTimeout(r, 50));

        const original = document.getElementById('contenidoFactura');
        if (!original) return;

        original.classList.add('modo-impresion');

        // 5. Clonar el DOM ya renderizado
        const clone = original.cloneNode(true) as HTMLElement;
        const btn = clone.querySelector('#btnCerrarModal') as HTMLElement;
        if (btn) btn.remove();

        const opt = {
            margin: 0.5,
            filename: `factura_compra_${id}.pdf`,
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2 },
            jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
        };

        // 6. Generar el PDF
        await new Promise<void>((resolve) => {
            html2pdf().set(opt).from(clone).save().then(() => resolve());
            setTimeout(() => resolve(), 4000); // fallback
        });

        } finally {
        // 7. Restaurar estado
        this.generandoPDF = false;
        const original = document.getElementById('contenidoFactura');
        if (original) original.classList.remove('modo-impresion');
        }
    }
}
