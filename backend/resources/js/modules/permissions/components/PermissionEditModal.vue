<template>
  <BaseForm :model="form.model" @submit="handleSubmit">
    <BaseFormSection title="Edit permission" description="Maintain RBAC capability contract for this permission.">
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

const props = defineProps<{ closeModal?: () => void; permission: PermissionListItem; onUpdated?: (item: PermissionListItem) => void }>();
const form = useForm({ name: props.permission.name, description: props.permission.description });
const asyncForm = useAsyncForm();
const toast = useToast();
const close = (): void => props.closeModal?.();

const handleSubmit = async ({ model }: FormSubmitContext<Record<string, unknown>>): Promise<void> => {
  const result = await asyncForm.submit(async () => {
    await new Promise((r) => setTimeout(r, 180));
    const nextName = String(model.name);
    const updated: PermissionListItem = { ...props.permission, name: nextName, module: nextName.split('.')[0] || props.permission.module, description: String(model.description || '') };
    props.onUpdated?.(updated);
    return updated;
  });
  if (result) { toast.success({ title: 'Permission updated', message: 'Permission edit workflow completed.' }); close(); }
};
</script>
