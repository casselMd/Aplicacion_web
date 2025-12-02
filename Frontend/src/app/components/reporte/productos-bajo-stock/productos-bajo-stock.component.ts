    //producto-bajo-stock.component.ts
    import { Component, OnInit } from '@angular/core';
    import { ProductoService } from '../../../services/producto.service';
    import { CommonModule } from '@angular/common';

    @Component({
    selector: 'app-productos-bajo-stock',
    imports: [CommonModule],
    templateUrl: './productos-bajo-stock.component.html',
    styleUrls: ['./productos-bajo-stock.component.css']
    })
    export class ProductosBajoStockComponent implements OnInit {
    productosStockBajo: any[] = [];
    mostrar = false;
    productoMinStock: any = null;

    constructor(private prodService: ProductoService) {}

    ngOnInit(): void {
        // Cargar los datos al inicializar el componente
        this.prodService.actualizarProductosBajoStock();
        
        // Suscribirse a los cambios (asumiendo que tienes stockBajo$ observable)
        this.prodService.productosStockBajo$.subscribe((productos: any[]) => {
        console.log('Productos recibidos:', productos); // Debug
        this.productosStockBajo = productos;

        // Obtener el producto con stock mínimo
        if (this.productosStockBajo.length > 0) {
            this.productoMinStock = this.productosStockBajo.reduce((prev, curr) =>
            prev.stock < curr.stock ? prev : curr
            );
            console.log('Producto con stock mínimo:', this.productoMinStock); // Debug
        } else {
            this.productoMinStock = null;
            console.log('No hay productos con stock bajo'); // Debug
        }
        });
    }

    abrirModal(): void {
        this.mostrar = true;
    }

    cerrarModal(): void {
        this.mostrar = false;
    }
    }