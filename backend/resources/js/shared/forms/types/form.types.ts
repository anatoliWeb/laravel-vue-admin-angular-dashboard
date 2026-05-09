import type { ComputedRef, Ref } from 'vue';

export type FormLayout = 'vertical' | 'grid';

export interface FormSubmitContext<TModel extends Record<string, unknown>> {
  model: TModel;
  reset: () => void;
}

export interface UseFormResult<TModel extends Record<string, unknown>> {
  model: TModel;
  initialModel: TModel;
  touched: Partial<Record<keyof TModel, boolean>>;
  dirtyFields: Partial<Record<keyof TModel, boolean>>;
  isDirty: ComputedRef<boolean>;
  isSubmitting: Ref<boolean>;
  touchField: (field: keyof TModel) => void;
  setField: <K extends keyof TModel>(field: K, value: TModel[K]) => void;
  reset: () => void;
  submit: (handler: (model: TModel) => Promise<void> | void) => Promise<void>;
}
