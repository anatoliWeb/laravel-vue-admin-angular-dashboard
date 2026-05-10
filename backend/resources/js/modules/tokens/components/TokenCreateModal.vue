<template>
  <BaseForm :model="form.model" @submit="handleSubmit">
    <BaseFormSection title="Create token">
      <BaseFormField label="Token name" :required="true" :error="form.getFieldError('name')"><input :value="String(form.model.name)" @input="form.setField('name', ($event.target as HTMLInputElement).value)" /></BaseFormField>
      <BaseFormField label="Scopes (comma separated)"><input :value="String(form.model.scopes)" @input="form.setField('scopes', ($event.target as HTMLInputElement).value)" /></BaseFormField>
    </BaseFormSection>
    <BaseFormActions :loading="asyncForm.isSubmitting.value" :submit-disabled="!form.isDirty.value" @cancel="close" />
  </BaseForm>
</template>
<script setup lang="ts">
import type { FormSubmitContext } from '../../../shared/forms';
import { BaseForm, BaseFormActions, BaseFormField, BaseFormSection, useAsyncForm, useForm } from '../../../shared/forms';
import { useToast } from '../../../shared/toast';
import type { TokenListItem } from '../types/tokens.types';

const props = defineProps<{ closeModal?: () => void; onCreated?: (item: TokenListItem) => void }>();
const form = useForm({ name: '', scopes: 'users.view' });
const asyncForm = useAsyncForm();
const toast = useToast();
const close = (): void => props.closeModal?.();

const handleSubmit = async ({ model }: FormSubmitContext<Record<string, unknown>>): Promise<void> => {
  if (!String(model.name).trim()) { form.setErrors({ name: ['Token name is required.'] }); return; }
  const result = await asyncForm.submit(async () => {
    await new Promise((r) => setTimeout(r, 220));
    const scopes = String(model.scopes).split(',').map((s) => s.trim()).filter(Boolean);
    const created: TokenListItem = { id: Date.now(), name: String(model.name), owner: { id: 1, name: 'Admin User' }, scopes, scopes_count: scopes.length, last_used_at: null, created_at: new Date().toISOString(), status: 'active', type: 'user' };
    props.onCreated?.(created);
    return created;
  });
  if (result) { toast.success({ title: 'Token created', message: 'Token creation shell completed.' }); close(); }
};
</script>
