<template>
  <BaseForm :model="form.model" @submit="handleSubmit">
    <BaseFormSection title="Create user" description="Provision identity and initial RBAC assignments." layout="grid">
      <BaseFormField label="Name" :required="true" :error="form.getFieldError('name')">
        <input :value="String(form.model.name)" @input="form.setField('name', ($event.target as HTMLInputElement).value)" />
      </BaseFormField>

      <BaseFormField label="Email" :required="true" :error="form.getFieldError('email')">
        <input :value="String(form.model.email)" @input="form.setField('email', ($event.target as HTMLInputElement).value)" />
      </BaseFormField>

      <BaseFormField label="Password" :required="true" :error="form.getFieldError('password')">
        <input type="password" autocomplete="new-password" :value="String(form.model.password)" @input="form.setField('password', ($event.target as HTMLInputElement).value)" />
      </BaseFormField>
    </BaseFormSection>

    <BaseFormSection title="Roles" description="Assign roles during user creation.">
      <div class="rbac-grid">
        <label v-for="role in roles" :key="role.id" class="rbac-item">
          <input type="checkbox" :checked="selectedRoleIds.has(role.id)" @change="toggleRole(role.id)" />
          <span>{{ role.name }}</span>
        </label>
      </div>
    </BaseFormSection>

    <BaseFormSection title="Direct permissions" description="Optional explicit grants/denials at creation time.">
      <div class="rbac-grid">
        <label v-for="permission in permissions" :key="permission.name" class="rbac-item">
          <input type="checkbox" :checked="selectedPermissions.has(permission.name)" @change="togglePermission(permission.name)" />
          <span>{{ permission.name }}</span>
        </label>
      </div>
    </BaseFormSection>

    <BaseFormActions :loading="asyncForm.isSubmitting.value || isMetaLoading" :submit-disabled="!form.isDirty.value" @cancel="close" />
  </BaseForm>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';

import type { FormSubmitContext } from '../../../shared/forms';
import { BaseForm, BaseFormActions, BaseFormField, BaseFormSection, useAsyncForm, useForm } from '../../../shared/forms';
import { isNormalizedApiError } from '../../../services/api/interceptors';
import { cacheStore } from '../../../shared/cache';
import { useToast } from '../../../shared/toast';
import { usersService } from '../services/users.service';
import type { UserListItem } from '../types/users.types';

const props = defineProps<{
  closeModal?: () => void;
  onCreated?: (item: UserListItem) => void;
}>();

const form = useForm({
  name: '',
  email: '',
  password: '',
});
const asyncForm = useAsyncForm();
const toast = useToast();

const isMetaLoading = ref(true);
const roles = ref<Array<{ id: number; name: string }>>([]);
const permissions = ref<Array<{ id: number; name: string }>>([]);
const selectedRoleIds = ref(new Set<number>());
const selectedPermissions = ref(new Set<string>());

const close = (): void => props.closeModal?.();

const toggleRole = (roleId: number): void => {
  if (selectedRoleIds.value.has(roleId)) {
    selectedRoleIds.value.delete(roleId);
  } else {
    selectedRoleIds.value.add(roleId);
  }
};

const togglePermission = (permissionName: string): void => {
  if (selectedPermissions.value.has(permissionName)) {
    selectedPermissions.value.delete(permissionName);
  } else {
    selectedPermissions.value.add(permissionName);
  }
};

const loadMeta = async (): Promise<void> => {
  try {
    isMetaLoading.value = true;
    const meta = await usersService.fetchRbacMeta();
    roles.value = meta.roles;
    permissions.value = meta.permissions;
  } finally {
    isMetaLoading.value = false;
  }
};

const handleSubmit = async ({ model }: FormSubmitContext<Record<string, unknown>>): Promise<void> => {
  form.clearErrors();

  const payload = {
    name: String(model.name ?? '').trim(),
    email: String(model.email ?? '').trim(),
    password: String(model.password ?? '').trim(),
    roles: Array.from(selectedRoleIds.value),
    permissions: Array.from(selectedPermissions.value),
    denied_permissions: [],
  };

  const result = await asyncForm.submit(async () => usersService.createUser(payload));

  if (!result) {
    const error = asyncForm.lastError.value;
    if (isNormalizedApiError(error) && error.code === 'validation' && error.errors) {
      form.setErrors(error.errors);
    }
    toast.error({ title: 'Create failed', message: 'Unable to create user.' });
    return;
  }

  props.onCreated?.({
    ...result,
    status: 'active',
  });

  cacheStore.invalidatePrefix('users.');
  cacheStore.invalidatePrefix('roles.');
  cacheStore.invalidatePrefix('dashboard.');

  toast.success({ title: 'User created', message: 'User and RBAC assignments created successfully.' });
  close();
};

onMounted(() => {
  void loadMeta();
});
</script>

<style scoped>
.rbac-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px}
.rbac-item{display:flex;gap:8px;align-items:center;color:#cbd5e1;font-size:12px}
@media (max-width:860px){.rbac-grid{grid-template-columns:1fr}}
</style>
