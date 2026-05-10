<template>
  <BaseForm :model="form.model" @submit="handleSubmit">
    <BaseFormSection title="Create permission">
      <BaseFormField label="Permission key" :required="true" :error="form.getFieldError('name')"><input :value="String(form.model.name)" @input="form.setField('name', ($event.target as HTMLInputElement).value)" /></BaseFormField>
      <BaseFormField label="Description"><input :value="String(form.model.description)" @input="form.setField('description', ($event.target as HTMLInputElement).value)" /></BaseFormField>
    </BaseFormSection>
    <BaseFormActions :loading="asyncForm.isSubmitting.value" :submit-disabled="!form.isDirty.value" @cancel="close" />
  </BaseForm>
</template>
<script setup lang="ts">
import type { FormSubmitContext } from '../../../shared/forms';
import { BaseForm, BaseFormActions, BaseFormField, BaseFormSection, useAsyncForm, useForm } from '../../../shared/forms';
import { useToast } from '../../../shared/toast';
import type { PermissionListItem } from '../types/permissions.types';

const props = defineProps<{ closeModal?: () => void; onCreated?: (item: PermissionListItem) => void }>();
const form = useForm({ name: '', description: '' });
const asyncForm = useAsyncForm();
const toast = useToast();
const close = (): void => props.closeModal?.();

const handleSubmit = async ({ model }: FormSubmitContext<Record<string, unknown>>): Promise<void> => {
  if (!String(model.name).trim()) { form.setErrors({ name: ['Permission key is required.'] }); return; }
  const result = await asyncForm.submit(async () => {
    await new Promise((r) => setTimeout(r, 180));
    const key = String(model.name);
    const created: PermissionListItem = { id: Date.now(), name: key, module: key.split('.')[0] || 'custom', description: String(model.description || ''), used_by_roles: [], type: 'manage', usage: 'unused', created_at: new Date().toISOString() };
    props.onCreated?.(created);
    return created;
  });
  if (result) { toast.success({ title: 'Permission created', message: 'Permission create shell completed.' }); close(); }
};
</script>
