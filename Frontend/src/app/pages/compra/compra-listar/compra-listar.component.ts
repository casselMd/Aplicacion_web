    import { ChangeDetectorRef, Component, NgZone } from '@angular/core';
    import { CompraService } from '../../../services/compra.service';
    import { Compra, CompraConDetalles } from '../../../Models/compra.model';
    import { CommonModule } from '@angular/common';
    import { FormsModule, ReactiveFormsModule } from '@angular/forms';
    import Swal from 'sweetalert2';
    import { Producto } from '../../../Models/producto.model';
    import { RouterLinkWithHref } from '@angular/router';
    import html2pdf from 'html2pdf.js';
    import { LoaderComponent } from '../../../components/shared/loader/loader.component';
    import { firstValueFrom } from 'rxjs';

    @Component({
    selector: 'app-compra-listar',
    standalone: true,
    imports: [CommonModule, ReactiveFormsModule, FormsModule , RouterLinkWithHref, LoaderComponent],
    templateUrl: './compra-listar.component.html',
    styleUrl: './compra-listar.component.css'
    })
    export class CompraListarComponent {
    filtro: string = '';
    compras: any[] = []; 
    comprasFiltradas: any[] = [];
    compraSeleccionada!: CompraConDetalles;

    constructor(private compraService: CompraService,
        private zone: NgZone,
        private cd: ChangeDetectorRef
    ) {}

    ngOnInit(): void {
        this.obtenerCompras();
        //this.filtrarCompras();
    }

    obtenerCompras(): void {
        this.compraService.listar().subscribe(
        res => {
            if (res.status) {
            this.compras = res.data;
            this.comprasFiltradas = [...this.compras];
        }
        },
        );
    }
    filtrarCompras() {
        const filtroNormalizado = this.filtro.toLowerCase().trim();

        this.comprasFiltradas = this.compras.filter(compra =>
        compra.id.toString().includes(filtroNormalizado) ||
        compra.numero_documento.toLowerCase().includes(filtroNormalizado)
        );
    }

    cancelarCompra(id: number){
        Swal.fire({
            title: '¿Eliminar compra?',
            text: 'Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
            }).then(result => {
            if (result.isConfirmed) {
                this.compraService.eliminar(id).subscribe({
                next: res => {
                    if (res.status) {
                    this.obtenerCompras();
                    Swal.fire('Compra eliminada', res.msg, 'success');
                    }
                },
                error: () => Swal.fire('Error', 'No se pudo eliminar', 'error')
                });
            }
            });
    }
    

    verCompra(id: number) {
        this.compraService.ver(id).subscribe((res) => {
        if(res.status){
            this.compraSeleccionada = res.data;
            // console.log(this.compraSeleccionada)
            this.abrirModal();
        }
        });
    }

    abrirModal() {
        const modal = document.getElementById('modalCompra');
        if (modal) modal.classList.remove('hidden');
    }

    cerrarModal() {
        const modal = document.getElementById('modalCompra');
        if (modal) modal.classList.add('hidden');
    }



    
    generandoPDF =  false;


    async generarPDF(id: number) {
        this.generandoPDF = true;

        try {
        // 1. Obtener los datos de la compra
        const res = await firstValueFrom(this.compraService.ver(id));
        if (!res.status) return;

        this.compraSeleccionada = res.data;

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
