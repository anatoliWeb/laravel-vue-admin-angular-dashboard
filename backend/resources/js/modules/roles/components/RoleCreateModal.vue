<template>
  <BaseForm :model="form.model" @submit="handleSubmit">
    <BaseFormSection title="Create role">
      <BaseFormField label="Role name" :required="true" :error="form.getFieldError('name')">
        <input :value="String(form.model.name)" @input="form.setField('name', ($event.target as HTMLInputElement).value)" />
      </BaseFormField>
      <BaseFormField label="Description">
        <input :value="String(form.model.description)" @input="form.setField('description', ($event.target as HTMLInputElement).value)" />
      </BaseFormField>
    </BaseFormSection>
    <BaseFormActions :loading="asyncForm.isSubmitting.value" :submit-disabled="!form.isDirty.value" @cancel="close" />
  </BaseForm>
</template>
<script setup lang="ts">
import type { FormSubmitContext } from '../../../shared/forms';
import { BaseForm, BaseFormActions, BaseFormField, BaseFormSection, useAsyncForm, useForm } from '../../../shared/forms';
import { useToast } from '../../../shared/toast';
import type { RoleListItem } from '../types/roles.types';

const props = defineProps<{ closeModal?: () => void; onCreated?: (item: RoleListItem) => void }>();
const form = useForm({ name: '', description: '' });
const asyncForm = useAsyncForm();
const toast = useToast();
const close = (): void => props.closeModal?.();

const handleSubmit = async ({ model }: FormSubmitContext<Record<string, unknown>>): Promise<void> => {
  if (!String(model.name).trim()) { form.setErrors({ name: ['Role name is required.'] }); return; }
  const result = await asyncForm.submit(async () => {
    await new Promise((r) => setTimeout(r, 200));
    const created: RoleListItem = { id: Date.now(), name: String(model.name), description: String(model.description || ''), permissions: [], permissions_count: 0, users_count: 0, status: 'active', type: 'custom', created_at: new Date().toISOString() };
    props.onCreated?.(created);
    return created;
  });
  if (result) { toast.success({ title: 'Role created', message: 'Role create shell completed.' }); close(); }
};
</script>
