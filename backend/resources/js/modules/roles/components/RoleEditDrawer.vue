<template>
  <BaseForm :model="form.model" @submit="handleSubmit">
    <BaseFormSection title="Edit role">
      <BaseFormField label="Role name" :required="true" :error="form.getFieldError('name')"><input :value="String(form.model.name)" @input="form.setField('name', ($event.target as HTMLInputElement).value)" /></BaseFormField>
      <BaseFormField label="Description"><input :value="String(form.model.description)" @input="form.setField('description', ($event.target as HTMLInputElement).value)" /></BaseFormField>
    </BaseFormSection>
    <BaseFormActions :loading="asyncForm.isSubmitting.value" :submit-disabled="!form.isDirty.value" @cancel="close" />
  </BaseForm>
</template>
<script setup lang="ts">
import type { FormSubmitContext } from '../../../shared/forms';
import { BaseForm, BaseFormActions, BaseFormField, BaseFormSection, useAsyncForm, useForm } from '../../../shared/forms';
import { useToast } from '../../../shared/toast';
import type { RoleListItem } from '../types/roles.types';

const props = defineProps<{ closeDrawer?: () => void; role: RoleListItem; onUpdated?: (item: RoleListItem) => void }>();
const form = useForm({ name: props.role.name, description: props.role.description });
const asyncForm = useAsyncForm();
const toast = useToast();
const close = (): void => props.closeDrawer?.();

const handleSubmit = async ({ model }: FormSubmitContext<Record<string, unknown>>): Promise<void> => {
  const result = await asyncForm.submit(async () => {
    await new Promise((r) => setTimeout(r, 200));
    const updated: RoleListItem = { ...props.role, name: String(model.name), description: String(model.description || '') };
    props.onUpdated?.(updated);
    return updated;
  });
  if (result) { toast.success({ title: 'Role updated', message: 'Role edit shell completed.' }); close(); }
};
</script>
