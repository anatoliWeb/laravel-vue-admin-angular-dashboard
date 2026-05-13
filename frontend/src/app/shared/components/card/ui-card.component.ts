import { Component } from '@angular/core';

@Component({
  selector: 'ui-card',
  template: `<section class="ui-card"><ng-content /></section>`,
  styles: [`
    .ui-card{border:1px solid rgba(71,85,105,.45);background:rgba(15,23,42,.7);border-radius:14px;padding:14px}
  `],
  standalone: false,
})
export class UiCardComponent {}

