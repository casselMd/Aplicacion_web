import { ComponentFixture, TestBed } from '@angular/core/testing';
import { VentasMensualComponent } from './ventas-mensual.component';

describe('VentasMensualComponent', () => {
    let component: VentasMensualComponent; 
    let fixture: ComponentFixture<VentasMensualComponent>;

    beforeEach(async () => {
        await TestBed.configureTestingModule({
        imports: [VentasMensualComponent]
        })
        .compileComponents();

        fixture = TestBed.createComponent(VentasMensualComponent);
        component = fixture.componentInstance;
        fixture.detectChanges();
    });
    
    it('should create', () => {
        expect(component).toBeTruthy();
    });
});