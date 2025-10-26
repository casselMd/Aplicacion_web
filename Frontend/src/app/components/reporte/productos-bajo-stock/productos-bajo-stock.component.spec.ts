    import { ComponentFixture, TestBed } from '@angular/core/testing';

    import { ProductosBajoStockComponent } from './productos-bajo-stock.component';

    describe('ProductosBajoStockComponent', () => {
    let component: ProductosBajoStockComponent;
    let fixture: ComponentFixture<ProductosBajoStockComponent>;

    beforeEach(async () => {
        await TestBed.configureTestingModule({
        imports: [ProductosBajoStockComponent]
        })
        .compileComponents();

        fixture = TestBed.createComponent(ProductosBajoStockComponent);
        component = fixture.componentInstance;
        fixture.detectChanges();
    });

    it('should create', () => {
        expect(component).toBeTruthy();
    });
    });
