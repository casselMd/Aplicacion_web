
    import { Component, OnInit } from '@angular/core';
    import { CommonModule } from '@angular/common';
    import { Chart, ChartConfiguration, ChartOptions, ChartType, registerables } from 'chart.js';
    import { BaseChartDirective } from 'ng2-charts';
    import { VentaService } from '../../../services/venta.service';

    // Registrar los componentes de Chart.js
    Chart.register(...registerables);

    @Component({
    selector: 'app-ventas-del-dia',
    standalone: true,
    imports: [CommonModule, BaseChartDirective],
    templateUrl: './ventas-del-dia.component.html',
    styleUrls: ['./ventas-del-dia.component.css']
    })
    export class VentasDelDiaComponent implements OnInit {
    
    public donutData: ChartConfiguration<'doughnut'>['data'] = {
        labels: ['Transcurrido', 'Restante'],
        datasets: [{
        data: [0, 100],
        backgroundColor: ['#4ade80', '#374151'], // verde y gris oscuro
        borderWidth: 0
        }]
    };

    public donutOptions: ChartOptions<'doughnut'> = {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '75%',
        plugins: {
        legend: { 
            display: false 
        },
        tooltip: {
            enabled: false // Opcional: deshabilitar tooltips si no los necesitas
        }
        }
    };

    public donutType: ChartType = 'doughnut' as const;
    totalVentasHoy = 0;

    constructor(private ventaService: VentaService) {}

    ngOnInit(): void {
        this.actualizarProgresoDia();
        this.obtenerVentasDelDia();
    }

    private actualizarProgresoDia() {
        const ahora = new Date();
        const inicio = new Date();
        inicio.setHours(0, 0, 0, 0);
        const fin = new Date();
        fin.setHours(23, 59, 59, 999);
        
        const totalMs = fin.getTime() - inicio.getTime();
        const elapsedMs = Date.now() - inicio.getTime();
        const pct = +(elapsedMs / totalMs * 100).toFixed(2);
        
        this.donutData.datasets[0].data = [pct, 100 - pct];
    }

    private obtenerVentasDelDia() {
        this.ventaService.totalVentasDelDia().subscribe(res => {
        if (res.status) {
            this.totalVentasHoy = res.data.total;
        }
        });
    }
    }