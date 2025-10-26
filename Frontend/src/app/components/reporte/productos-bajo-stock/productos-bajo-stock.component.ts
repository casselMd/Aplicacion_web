    import { Component } from '@angular/core';
    import { ProductoService } from '../../../services/producto.service';
    import { CommonModule } from '@angular/common';

    @Component({
    selector: 'app-productos-bajo-stock',
    imports: [CommonModule],
    templateUrl: './productos-bajo-stock.component.html',
    styleUrl: './productos-bajo-stock.component.css'
    })
    export class ProductosBajoStockComponent {
    productosStockBajo: any[] = [];
    mostrar = false;

    constructor(private prodService: ProductoService) {}

    ngOnInit(): void {
        this.prodService.productosStockBajo$.subscribe(p => this.productosStockBajo = p);
    }

    abrirModal() {
        this.mostrar = true;
    }

    cerrarModal() {
        this.mostrar = false;
    }
    }
