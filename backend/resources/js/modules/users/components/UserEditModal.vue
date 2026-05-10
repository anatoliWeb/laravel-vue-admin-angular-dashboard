<template>
  <BaseForm :model="form.model" @submit="handleSubmit">
    <BaseFormSection title="Edit user" description="Update identity, role assignments, and direct RBAC overrides." layout="grid">
      <BaseFormField label="Name" :required="true" :error="form.getFieldError('name')">
        <input :value="String(form.model.name)" @input="form.setField('name', ($event.target as HTMLInputElement).value)" />
      </BaseFormField>

      <BaseFormField label="Email" :required="true" :error="form.getFieldError('email')">
        <input :value="String(form.model.email)" @input="form.setField('email', ($event.target as HTMLInputElement).value)" />
      </BaseFormField>

      <BaseFormField label="Password (optional)" :error="form.getFieldError('password')">
        <input
          type="password"
          autocomplete="new-password"
          :value="String(form.model.password ?? '')"
          @input="form.setField('password', ($event.target as HTMLInputElement).value)"
        />
      </BaseFormField>
    </BaseFormSection>

    <BaseFormSection title="Role assignment" description="Assign roles that provide inherited permission sets.">
      <div class="rbac-control">
        <input
          type="text"
          placeholder="Search roles..."
          :value="roleQuery"
          @input="roleQuery = ($event.target as HTMLInputElement).value"
        />
      </div>

      <div class="rbac-grid">
        <label v-for="role in filteredRoles" :key="role.id" class="rbac-item">
          <input
            type="checkbox"
            :checked="selectedRoleIds.has(role.id)"
            @change="toggleRole(role.id)"
          />
          <span>{{ role.name }}</span>
        </label>
      </div>
    </BaseFormSection>

    <BaseFormSection title="Direct permissions" description="Direct grants and denies override role inheritance when needed.">
      <div class="rbac-control">
        <input
          type="text"
          placeholder="Search permissions..."
          :value="permissionQuery"
          @input="permissionQuery = ($event.target as HTMLInputElement).value"
        />
      </div>

      <div class="permission-groups">
        <section v-for="group in groupedPermissions" :key="group.module" class="permission-group">
          <h4>{{ group.module }}</h4>
          <div class="rbac-grid">
            <label v-for="permission in group.permissions" :key="permission" class="rbac-item">
              <input
                type="checkbox"
                :checked="selectedDirectPermissions.has(permission)"
                @change="toggleDirectPermission(permission)"
              />
              <span>{{ permission }}</span>
            </label>
          </div>
          <div class="rbac-grid rbac-grid--deny">
            <label v-for="permission in group.permissions" :key="`${permission}-deny`" class="rbac-item">
              <input
                type="checkbox"
                :checked="selectedDeniedPermissions.has(permission)"
                @change="toggleDeniedPermission(permission)"
              />
              <span>Deny: {{ permission }}</span>
            </label>
          </div>
        </section>
      </div>
    </BaseFormSection>

    <BaseFormSection title="Effective permissions" description="Merged view of inherited role permissions and direct overrides.">
      <div class="effective-grid">
        <div v-for="permission in effectivePermissions" :key="permission.name" class="effective-item">
          <strong>{{ permission.name }}</strong>
          <span :class="permission.source === 'denied' ? 'is-denied' : 'is-allowed'">
            {{ permission.source }}
          </span>
        </div>
      </div>
    </BaseFormSection>

    <BaseFormActions :loading="asyncForm.isSubmitting.value || isMetaLoading" :submit-disabled="!form.isDirty.value" @cancel="close" />
  </BaseForm>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';

import type { FormSubmitContext } from '../../../shared/forms';
import { BaseForm, BaseFormActions, BaseFormField, BaseFormSection, useAsyncForm, useForm } from '../../../shared/forms';
import { useToast } from '../../../shared/toast';
import { usersService } from '../services/users.service';
import type { UserListItem } from '../types/users.types';
import { isNormalizedApiError } from '../../../services/api/interceptors';
import { cacheStore } from '../../../shared/cache';

type RoleMeta = { id: number; name: string };
type PermissionSource = 'inherited' | 'direct' | 'denied';

const props = defineProps<{
  closeModal?: () => void;
  user: UserListItem;
  onUpdated?: (item: UserListItem) => void;
}>();

const form = useForm({
  name: props.user.name,
  email: props.user.email,
  password: '',
});

const asyncForm = useAsyncForm();
const toast = useToast();

const isMetaLoading = ref(true);
const roleQuery = ref('');
const permissionQuery = ref('');
const roles = ref<RoleMeta[]>([]);
const permissions = ref<string[]>([]);
const rolePermissions = ref<Record<string, string[]>>({});

const selectedRoleIds = ref(new Set<number>());
const selectedDirectPermissions = ref(new Set<string>(props.user.permissions ?? []));
const selectedDeniedPermissions = ref(new Set<string>(props.user.denied_permissions ?? []));

const close = (): void => props.closeModal?.();

/**
 * RBAC INHERITANCE STRATEGY:
 * - roles provide inherited permission sets
 * - direct permissions grant explicit additions
 * - denied permissions explicitly override grants
 *
 * Keeping this logic in one modal keeps enterprise RBAC edits understandable
 * and avoids ambiguous permission state during admin operations.
 */
const inheritedPermissions = computed(() => {
  const roleNames = roles.value
    .filter((role) => selectedRoleIds.value.has(role.id))
    .map((role) => role.name);

  return new Set(
    roleNames.flatMap((roleName) => rolePermissions.value[roleName] ?? []),
  );
});

const effectivePermissions = computed(() => {
  const result = new Map<string, PermissionSource>();

  inheritedPermissions.value.forEach((permission) => {
    result.set(permission, 'inherited');
  });

  selectedDirectPermissions.value.forEach((permission) => {
    result.set(permission, 'direct');
  });

  selectedDeniedPermissions.value.forEach((permission) => {
    result.set(permission, 'denied');
  });

  return Array.from(result.entries())
    .map(([name, source]) => ({ name, source }))
    .sort((a, b) => a.name.localeCompare(b.name));
});

const filteredRoles = computed(() => {
  const needle = roleQuery.value.trim().toLowerCase();
  if (!needle) return roles.value;
  return roles.value.filter((role) => role.name.toLowerCase().includes(needle));
});

const groupedPermissions = computed(() => {
  const needle = permissionQuery.value.trim().toLowerCase();
  const filtered = permissions.value.filter((permission) =>
    !needle || permission.toLowerCase().includes(needle),
  );

  const bucket = new Map<string, string[]>();
  filtered.forEach((permission) => {
    const module = permission.split('.')[0] || 'system';
    const current = bucket.get(module) ?? [];
    bucket.set(module, [...current, permission]);
  });

  return Array.from(bucket.entries())
    .map(([module, list]) => ({
      module,
      permissions: list.sort((a, b) => a.localeCompare(b)),
    }))
    .sort((a, b) => a.module.localeCompare(b.module));
});

const toggleRole = (roleId: number): void => {
  if (selectedRoleIds.value.has(roleId)) {
    selectedRoleIds.value.delete(roleId);
  } else {
    selectedRoleIds.value.add(roleId);
  }
};

const toggleDirectPermission = (permission: string): void => {
  if (selectedDirectPermissions.value.has(permission)) {
    selectedDirectPermissions.value.delete(permission);
  } else {
    selectedDirectPermissions.value.add(permission);
    selectedDeniedPermissions.value.delete(permission);
  }
};

const toggleDeniedPermission = (permission: string): void => {
  if (selectedDeniedPermissions.value.has(permission)) {
    selectedDeniedPermissions.value.delete(permission);
  } else {
    selectedDeniedPermissions.value.add(permission);
    selectedDirectPermissions.value.delete(permission);
  }
};

const loadMeta = async (): Promise<void> => {
  try {
    isMetaLoading.value = true;
    const meta = await usersService.fetchRbacMeta();
    roles.value = meta.roles;
    permissions.value = meta.permissions.map((entry) => entry.name);
    rolePermissions.value = meta.role_permissions;

    const initialRoleIds = meta.roles
      .filter((role) => props.user.roles.includes(role.name))
      .map((role) => role.id);
    selectedRoleIds.value = new Set(initialRoleIds);
  } finally {
    isMetaLoading.value = false;
  }
};

const handleSubmit = async ({ model }: FormSubmitContext<Record<string, unknown>>): Promise<void> => {
  form.clearErrors();

  const payload = {
    name: String(model.name ?? '').trim(),
    email: String(model.email ?? '').trim(),
    password: String(model.password ?? '').trim() || undefined,
    roles: Array.from(selectedRoleIds.value),
    permissions: Array.from(selectedDirectPermissions.value),
    denied_permissions: Array.from(selectedDeniedPermissions.value),
  };

  const result = await asyncForm.submit(async () => {
    return usersService.updateUser(props.user.id, payload);
  });

  if (!result) {
    const error = asyncForm.lastError.value;
    if (isNormalizedApiError(error) && error.code === 'validation' && error.errors) {
      form.setErrors(error.errors);
    }
    toast.error({ title: 'Update failed', message: 'Unable to update user RBAC data.' });
    return;
  }

  props.onUpdated?.({
    ...result,
    status: props.user.status,
  });

  cacheStore.invalidatePrefix('users.');
  cacheStore.invalidatePrefix('roles.');
  cacheStore.invalidatePrefix('permissions.');
  cacheStore.invalidatePrefix('dashboard.');

  toast.success({ title: 'User updated', message: 'RBAC assignments updated successfully.' });
  close();
};

onMounted(() => {
  void loadMeta();
});
</script>

<style scoped>
.rbac-control input{width:100%}
.rbac-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px}
.rbac-grid--deny{margin-top:8px}
.rbac-item{display:flex;gap:8px;align-items:center;color:#cbd5e1;font-size:12px}
.permission-groups{display:grid;gap:12px}
.permission-group h4{margin:0 0 6px;color:#f8fafc;font-size:12px;text-transform:uppercase;letter-spacing:.04em}
.effective-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px}
.effective-item{display:flex;justify-content:space-between;gap:8px;border:1px solid rgba(71,85,105,.5);border-radius:8px;padding:7px 9px}
.effective-item strong{color:#e2e8f0;font-size:12px}
.is-allowed{color:#86efac;font-size:11px;text-transform:capitalize}
.is-denied{color:#fca5a5;font-size:11px;text-transform:capitalize}
@media (max-width:860px){.rbac-grid,.effective-grid{grid-template-columns:1fr}}
</style>
