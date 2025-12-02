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

    public heatmapData: number[] = [];
    public heatmapLabels: string[] = [
        'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun',
        'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'
    ];

    constructor(
        private ventaService: VentaService,
        private cdr: ChangeDetectorRef
    ) { }

    ngOnInit(): void {
        this.cargarDatos();
    }

    private cargarDatos(): void {
        this.ventaService.ventasMensual().subscribe({
        next: (res) => {
            // 🔒 Validación robusta
            if (!res || typeof res !== 'object') {
            console.error("Respuesta inválida del backend:", res);
            return;
            }

            if (!('status' in res) || !res.status) {
            console.warn("Respuesta sin status TRUE:", res);
            return;
            }

            if (!Array.isArray(res.data)) {
            console.warn("res.data no es un arreglo:", res.data);
            return;
            }

            const meses = this.heatmapLabels;

            const valores = meses.map(mes => {
            const mesData = res.data.find((r: any) => r.mes === mes);
            return mesData ? Number(mesData.total) : 0;
            });

            // Chart.js
            this.barData.labels = meses;
            this.barData.datasets[0].data = valores;

            // Heatmap
            this.heatmapData = valores;

            // Forzar actualización visual
            this.chart?.update();
            this.cdr.detectChanges();
        },

        error: (error) => {
            console.error("Error HTTP:", error);
        }
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
