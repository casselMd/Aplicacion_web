    import { Component } from '@angular/core';
    import { ProductoService } from '../../../services/producto.service';
    import { CommonModule } from '@angular/common';

    @Component({
    selector: 'app-productos-bajo-stock',
    imports: [CommonModule],
    templateUrl: './productos-bajo-stock.component.html',
    styleUrls: ['./productos-bajo-stock.component.css'] // nota: corregí styleUrl -> styleUrls
    })
    export class ProductosBajoStockComponent {
    productosStockBajo: any[] = [];
    mostrar = false;
    productoMinStock: any = null;

    constructor(private prodService: ProductoService) {}

    ngOnInit(): void {
        this.prodService.productosStockBajo$.subscribe(p => {
        this.productosStockBajo = p;

        // Obtener el producto con stock mínimo
        if (this.productosStockBajo.length > 0) {
            this.productoMinStock = this.productosStockBajo.reduce((prev, curr) =>
            prev.stock < curr.stock ? prev : curr
            );
        } else {
            this.productoMinStock = null;
        }
        });
    }

    abrirModal() {
        this.mostrar = true;
    }

    cerrarModal() {
        this.mostrar = false;
    }
    }
