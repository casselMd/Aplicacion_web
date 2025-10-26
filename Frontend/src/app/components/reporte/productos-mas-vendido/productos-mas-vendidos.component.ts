    // productos-mas-vendidos.component.ts
    import {
    Component,
    OnInit,
    ViewChild,
    ChangeDetectorRef
    } from '@angular/core';
    import { CommonModule } from '@angular/common';
    import { BaseChartDirective } from 'ng2-charts';
    import { Chart, ChartConfiguration, ChartOptions, registerables } from 'chart.js';
    import { VentaService } from '../../../services/venta.service';

    Chart.register(...registerables);

    @Component({
    selector: 'app-productos-mas-vendidos',
    standalone: true,
    imports: [CommonModule, BaseChartDirective],
    templateUrl: './productos-mas-vendido.component.html',
    })
    export class ProductosMasVendidosComponent implements OnInit {
    @ViewChild(BaseChartDirective) pieChart!: BaseChartDirective;

    public pieData: ChartConfiguration<'doughnut'>['data'] = {
        labels: [],
        datasets: [{
        data: [],
        backgroundColor: ['#60a5fa', '#f472b6', '#34d399', '#fbbf24', '#a78bfa'],
        hoverOffset: 8,
        borderWidth: 0
        }]

    };

    public pieOptions: ChartOptions<'doughnut'> = {
        responsive: true,
        cutout: 75 ,
        maintainAspectRatio: false,
        plugins: {
        legend: {
            position: 'bottom',
            labels: { color: '#fff', usePointStyle: true, padding: 20 }
        }
        }
    };

    constructor(
        private ventaSvc: VentaService,
        private cdr: ChangeDetectorRef
    ) {}

    ngOnInit(): void {
        this.cargarDatos();
    }

    private cargarDatos(limit = 5) {
        this.ventaSvc.productosMasVendidos(limit).subscribe({
        next: res => {
            if (!res.status || !res.data) return;
            const arr = res.data as Array<{ producto: string; cantidad: number }>;
            if (!arr.length) return;

            this.pieData.labels    = arr.map(r => r.producto);
            this.pieData.datasets[0].data = arr.map(r => r.cantidad);

            // Detectamos cambios y forzamos el redraw del chart inmediatamente
            this.cdr.detectChanges();
            // BaseChartDirective dispone de update()
            setTimeout(() => this.pieChart.update(), 0);
        },
        error: console.error
        });
    }
    }
