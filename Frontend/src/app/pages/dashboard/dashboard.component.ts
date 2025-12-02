import { Producto } from './../../Models/producto.model';
    import {
    AfterViewInit,
    Component,
    ElementRef,
    NgZone,
    OnDestroy,
    OnInit,
    ViewChild
    } from '@angular/core';

    import {
    ChartComponent,
    ApexAxisChartSeries,
    ApexChart,
    ApexXAxis,
    ApexDataLabels,
    ApexStroke,
    ApexLegend,
    ApexYAxis,
    ApexTitleSubtitle,
    ApexTooltip
    } from 'ng-apexcharts';

    import { ClienteService } from '../../services/cliente.service';
    import { EmpleadoService } from '../../services/empleado.service';
    import { CompraService } from '../../services/compra.service';
    import { VentaService } from '../../services/venta.service';
    import {    ProductoService } from '../../services/producto.service';   

    import { VentasDelDiaComponent } from '../../components/reporte/ventas-del-dia/ventas-del-dia.component';
    import { ProductosMasVendidosComponent } from '../../components/reporte/productos-mas-vendido/productos-mas-vendidos.component';
    import { VentasDiaSemanaComponent } from '../../components/reporte/ventas-dia-semana/ventas-dia-semana.component';
    import { ProductosBajoStockComponent } from '../../components/reporte/productos-bajo-stock/productos-bajo-stock.component';
    import { VentasMensualComponent } from '../../components/reporte/ventas-mensual/ventas-mensual.component';

    import { BaseChartDirective } from 'ng2-charts';
    import { Chart, ChartConfiguration, ChartOptions, registerables } from 'chart.js';

    Chart.register(...registerables);

    export type LineChartOptions = {
    series: ApexAxisChartSeries;
    chart: ApexChart;
    xaxis: ApexXAxis;
    dataLabels: ApexDataLabels;
    stroke: ApexStroke;
    title: ApexTitleSubtitle;
    yaxis: ApexYAxis;
    legend: ApexLegend;
    colors: string[];
    tooltip: ApexTooltip;
    };

    @Component({
    selector: 'app-dashboard',
    templateUrl: './dashboard.component.html',
    standalone: true,
    imports: [
        ChartComponent,
        VentasDelDiaComponent,
        ProductosMasVendidosComponent,
        VentasDiaSemanaComponent,
        ProductosBajoStockComponent,
        VentasMensualComponent
    ]
    })
    export class DashboardComponent implements OnInit, AfterViewInit, OnDestroy {
    @ViewChild('chartContainer', { static: true }) chartContainer!: ElementRef;
    @ViewChild('chart') chart!: ChartComponent;
    @ViewChild('pieChart') pieChart!: BaseChartDirective;
    @ViewChild('chartRing', { static: true }) chartRing!: ElementRef;

    private resizeObserver!: ResizeObserver;

    public chartOptions!: LineChartOptions;
    public totalEmpleadosActivos: number = 0;
    public totalClientesActivos: number = 0;

    private dataCompra: number[] = [];
    private dataVenta: number[] = [];
    private mesActual: number = new Date().getMonth();

    constructor(
        private clienteService: ClienteService,
        private empleadoService: EmpleadoService,
        private ProductoService: ProductoService,   
        private compraService: CompraService,
        private ventaService: VentaService,
        private ngZone: NgZone
    ) {}

    // Ciclo de vida
    ngOnInit() {
        this.iniciarGraficoVentas();
        this.obtenerClientesActivos();
        this.obtenerEmpleadosActivos();
        this.obtenerResumenCompras();
        this.obtenerResumenVentas();
        this.cargarDatos();
    }

    onChartMounted() {
        console.log('Chart listo:', this.chart);
        
        // Actualizamos options si ya hay datos
        const apex = (this.chart as any)?._chart;
        if (apex) {
            apex.updateOptions(this.chartOptions, false, true);
        }

        // Observador de redimensionamiento
        this.resizeObserver = new ResizeObserver(() => {
            if (apex) apex.updateOptions(this.chartOptions, false, true);
        });
        this.resizeObserver.observe(this.chartContainer.nativeElement);
        }


        ngAfterViewInit(): void {
        // Asegura que Angular haya renderizado el DOM
        setTimeout(() => {
            const apex = (this.chart as any)?._chart;
            if (apex) {
            apex.updateOptions(this.chartOptions, false, true);
            }
        }, 0);

        // ResizeObserver funcional con Tailwind
        this.resizeObserver = new ResizeObserver(() => {
            const apex = (this.chart as any)?._chart;
            if (apex) apex.updateOptions(this.chartOptions, false, true);
        });

        // Observa el contenedor con altura definida
        this.resizeObserver.observe(this.chartContainer.nativeElement);
        }


    ngOnDestroy(): void {
        if (this.resizeObserver) this.resizeObserver.disconnect();
    }

    // Métodos principales
    obtenerResumenVentas() {
        this.ventaService.listar().subscribe(res => {
        if (res.status) {
            this.chartOptions.series[0].data = res.resumen.slice(0, this.mesActual + 1);
        }
        });
    }

    obtenerResumenCompras() {
        this.compraService.listar().subscribe(res => {
        if (res.status) {
            this.chartOptions.series[1].data = res.resumen.slice(0, this.mesActual + 1);
        }
        });
    }

    iniciarGraficoVentas() {
        const meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        const mesesHastaAhora = meses.slice(0, this.mesActual + 1);

        this.chartOptions = {
        series: [
            { name: 'Ventas', data: this.dataVenta },
            { name: 'Compras', data: this.dataCompra }
        ],
        chart: {
            height: 320,
            type: 'line',
            zoom: { enabled: false },
            toolbar: { show: false },
            foreColor: '#f1f5f9',
            background: 'transparent'
        },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 2 },
        title: {
            text: 'Compras vs Ventas',
            align: 'left',
            style: {
            fontSize: '20px',
            fontWeight: 'bold',
            color: '#000'
            }
        },
        xaxis: {
            categories: mesesHastaAhora,
            labels: {
            style: { colors: '#000', fontSize: '13px' }
            },
            axisBorder: { color: '#000' },
            axisTicks: { color: '#000' }
        },
        yaxis: {
            title: {
            text: 'Cantidad',
            style: { color: '#000', fontSize: '13px' }
            },
            labels: {
            style: { colors: '#000', fontSize: '12px' }
            }
        },
        legend: {
            position: 'top',
            labels: {
            colors: '#000',
            useSeriesColors: false
            }
        },
        colors: ['#3B82F6', '#EF4444'],
        tooltip: {
            theme: 'dark',
            style: {
            fontSize: '14px',
            fontFamily: 'Arial'
            }
        }
        };
    }

    // Datos generales
    obtenerEmpleadosActivos() {
        this.empleadoService.listar().subscribe(res => {
        if (res.status) this.totalEmpleadosActivos = res.total_activos;
        });
    }

    obtenerClientesActivos() {
        this.clienteService.listar().subscribe(res => {
        if (res.status) this.totalClientesActivos = res.total_activos;
        });
    }

    // Gráfico de productos más vendidos
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
        maintainAspectRatio: false,
        plugins: {
        legend: {
            position: 'right',
            labels: {
            color: '#fff',
            usePointStyle: true,
            padding: 20
            }
        }
        }
    };

    cargarDatos() {
        this.ProductoService.listar().subscribe({
        next: res => {
            if (!res.status || !res.data) return;

            const arr = res.data as Array<{ producto: string; cantidad: number }>;
            if (!arr.length) return;

            this.pieData.labels = arr.map(r => r.producto);
            this.pieData.datasets[0].data = arr.map(r => r.cantidad);

            this.forceRedraw();
        },
        error: console.error
        });
    }

    private forceRedraw() {
        if (this.pieChart?.chart) {
        this.pieChart.chart.resize();
        this.pieChart.chart.update();
        }
    }
    }
