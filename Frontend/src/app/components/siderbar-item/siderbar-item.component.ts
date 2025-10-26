import { Component, Input } from '@angular/core';
import { Router, RouterOutlet } from '@angular/router';

@Component({
    selector: 'app-sidebar-item',
    standalone: true,
    imports: [],
    templateUrl: './siderbar-item.component.html',
    styleUrl: './siderbar-item.component.css'
    })
    export class SidebarItemComponent {
    // Recibimos el ícono, el texto y la ruta como inputs
    @Input() icon: string = '';
    @Input() text: string = '';
    @Input() route: string = '';

    constructor(private router: Router) {}

    // Método para navegar a la ruta indicada
    navigate(): void {
        if (this.route) {
        this.router.navigate([this.route]);
        }
    }
}
