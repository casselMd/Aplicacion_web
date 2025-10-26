
    // ventas-dia-semana.component.ts
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
    selector: 'app-ventas-dia-semana',
    standalone: true,
    imports: [CommonModule, BaseChartDirective],
    templateUrl: './ventas-dia-semana.component.html',
    styleUrls: ['./ventas-dia-semana.component.css']
    })
    export class VentasDiaSemanaComponent implements OnInit {
    
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
    public heatmapLabels: string[] = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];

    constructor(
        private ventaService: VentaService, 
        private cdr: ChangeDetectorRef
    ) {}

    ngOnInit(): void {
        this.cargarDatos();
    }

    private cargarDatos(): void {
        this.ventaService.ventasPorDiaSemana().subscribe(res => {
        if (!res.status || !res.data) return;
        
        const dias = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
        const valores = dias.map(dia => {
            const diaData = res.data.find((r: any) => r.dia === dia) || { total: 0 };
            return diaData.total;
        });

        // Chart.js - Barras
        this.barData.labels = dias;
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