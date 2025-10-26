import { ComponentFixture, TestBed } from '@angular/core/testing';
import { VentasDiaSemanaComponent } from './ventas-dia-semana.component';

describe('VentasDiaSemanaComponent', () => {
    let component: VentasDiaSemanaComponent;
    let fixture: ComponentFixture<VentasDiaSemanaComponent>;

    beforeEach(async () => {
        await TestBed.configureTestingModule({
        imports: [VentasDiaSemanaComponent]
        })
        .compileComponents();

        fixture = TestBed.createComponent(VentasDiaSemanaComponent);
        component = fixture.componentInstance;
        fixture.detectChanges();
    });

    it('should create', () => {
        expect(component).toBeTruthy();
    });
});
