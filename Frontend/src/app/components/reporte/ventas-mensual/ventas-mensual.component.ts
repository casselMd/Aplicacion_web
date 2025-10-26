    // ventas-mensual.component.ts
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
    selector: 'app-ventas-mensual',
    standalone: true,
    imports: [CommonModule, BaseChartDirective],
    templateUrl: './ventas-mensual.component.html',
    styleUrls: ['./ventas-mensual.component.css']
    })
    export class VentasMensualComponent implements OnInit {
    
    @ViewChild(BaseChartDirective) chart: BaseChartDirective | undefined;
    
    public barData: ChartConfiguration<'bar'>['data'] = {
        labels: [],
        datasets: [
        {
            label: 'Ventas',
            data: [],
            backgroundColor: '#82d456ff',
            borderRadius: 6,
            borderSkipped: false
        }
        ]
    };

    public barOptions: ChartOptions<'bar'> = {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
        x: {
            ticks: { color: '#fff' },
            grid: { color: '#375144ff' }
        },
        y: {
            beginAtZero: true,
            ticks: { color: '#fff' },
            grid: { color: '#375140ff' }
        }
        },
        plugins: {
        legend: { display: false }
        }
    };

    public heatmapData: any[] = [];
    public heatmapLabels: string[] = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

    constructor(
        private ventaService: VentaService, 
        private cdr: ChangeDetectorRef
    ) {}

    ngOnInit(): void {
        this.cargarDatos();
    }

    private cargarDatos(): void {
        this.ventaService.ventasMensual().subscribe(res => {
        if (!res.status || !res.data) return;
        
        const meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        const valores = meses.map(mes => {
            const mesData = res.data.find((r: any) => r.mes === mes) || { total: 0 };
            return mesData.total;
        });

        // Chart.js - Barras
        this.barData.labels = meses;
        this.barData.datasets[0].data = valores;

        // Datos para heatmap personalizado
        this.heatmapData = valores;

        // Actualizar el gráfico
        this.chart?.update();
        this.cdr.detectChanges();
        });
    }

    getHeatmapColor(valor: number): string {
        if (valor <= 10) return '#e0feeaff';
        if (valor <= 30) return '#60faafff';
        if (valor <= 60) return '#31f05dff';
        return '#578e0bff';
    }

    getHeatmapIntensity(valor: number): number {
        const max = Math.max(...this.heatmapData);
        return max > 0 ? (valor / max) * 100 : 0;
    }
    }