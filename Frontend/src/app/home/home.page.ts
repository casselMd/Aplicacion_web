import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { IonHeader, IonToolbar, IonTitle, IonContent, IonList, IonItem, IonLabel } from '@ionic/angular/standalone';
import { ApiService } from '../service/api.service';

@Component({
  selector: 'app-home',
  templateUrl: 'home.page.html',
  styleUrls: ['home.page.scss'],
  standalone: true,
  imports: [IonLabel, CommonModule, IonHeader, IonToolbar, IonTitle, IonContent, IonList, IonItem],
})
export class HomePage implements OnInit {
  productos: any[] = [];

  constructor(private api: ApiService) {}

  ngOnInit() {
    this.api.getProductos().subscribe(data => {
      this.productos = data;
    });
  }
}
