import { ComponentFixture, TestBed } from '@angular/core/testing';
import { VentasDelDiaComponent } from './ventas-del-dia.component';

describe('VentasDelDiaComponent', () => {
    let component: VentasDelDiaComponent;
    let fixture: ComponentFixture<VentasDelDiaComponent>;

    beforeEach(async () => {
        await TestBed.configureTestingModule({
        imports: [VentasDelDiaComponent]
        })
        .compileComponents();

        fixture = TestBed.createComponent(VentasDelDiaComponent);
        component = fixture.componentInstance;
        fixture.detectChanges();
    });

    it('should create', () => {
        expect(component).toBeTruthy();
    });
});
