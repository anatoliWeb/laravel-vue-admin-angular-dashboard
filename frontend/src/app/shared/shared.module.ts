import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { AppButtonComponent } from './components/button/app-button.component';
import { AppInputComponent } from './components/input/app-input.component';
import { LoadingStateComponent } from './components/loading-state/loading-state.component';
import { UiCardComponent } from './components/card/ui-card.component';
import { TablePlaceholderComponent } from './components/table-placeholder/table-placeholder.component';
import { ModalPlaceholderComponent } from './components/modal-placeholder/modal-placeholder.component';
import { EmptyValuePipe } from './pipes/empty-value.pipe';

@NgModule({
  declarations: [
    AppButtonComponent,
    AppInputComponent,
    LoadingStateComponent,
    UiCardComponent,
    TablePlaceholderComponent,
    ModalPlaceholderComponent,
    EmptyValuePipe,
  ],
  imports: [CommonModule, FormsModule, ReactiveFormsModule],
  exports: [
    CommonModule,
    FormsModule,
    ReactiveFormsModule,
    AppButtonComponent,
    AppInputComponent,
    LoadingStateComponent,
    UiCardComponent,
    TablePlaceholderComponent,
    ModalPlaceholderComponent,
    EmptyValuePipe,
  ],
})
export class SharedModule {}

