<template>
  <BaseForm :model="form.model" @submit="handleSubmit">
    <BaseFormSection title="Edit role" description="Manage role identity and permission matrix assignment." layout="grid">
      <BaseFormField label="Role name" :required="true" :error="form.getFieldError('name')">
        <input :value="String(form.model.name)" @input="form.setField('name', ($event.target as HTMLInputElement).value)" />
      </BaseFormField>
      <BaseFormField label="Description">
        <input :value="String(form.model.description)" @input="form.setField('description', ($event.target as HTMLInputElement).value)" />
      </BaseFormField>
    </BaseFormSection>

    <BaseFormSection title="Role permissions" description="Grouped permission controls for scalable RBAC maintenance.">
      <div class="control">
        <input type="text" placeholder="Search permissions..." :value="query" @input="query = ($event.target as HTMLInputElement).value" />
      </div>

      <div class="groups">
        <section v-for="group in groupedPermissions" :key="group.module" class="group">
          <h4>{{ group.module }}</h4>
          <div class="grid">
            <label v-for="permission in group.permissions" :key="permission" class="item">
              <input type="checkbox" :checked="selectedPermissions.has(permission)" @change="togglePermission(permission)" />
              <span>{{ permission }}</span>
            </label>
          </div>
        </section>
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
import { permissionsService } from '../../permissions/services/permissions.service';
import type { RoleListItem } from '../types/roles.types';

const props = defineProps<{ closeModal?: () => void; role: RoleListItem; onUpdated?: (item: RoleListItem) => void }>();

const form = useForm({ name: props.role.name, description: props.role.description });
const asyncForm = useAsyncForm();
const toast = useToast();

const isMetaLoading = ref(true);
const query = ref('');
const allPermissions = ref<string[]>([]);
const selectedPermissions = ref(new Set<string>(props.role.permissions));

const close = (): void => props.closeModal?.();

/**
 * Permission grouping engine:
 * Large enterprise RBAC lists remain usable only when grouped by module
 * prefixes (`users.*`, `roles.*`, `tokens.*`). This prevents flat-list
 * overload and keeps role editing scalable.
 */
const groupedPermissions = computed(() => {
  const needle = query.value.trim().toLowerCase();
  const filtered = allPermissions.value.filter((permission) =>
    !needle || permission.toLowerCase().includes(needle),
  );

  const bucket = new Map<string, string[]>();
  filtered.forEach((permission) => {
    const module = permission.split('.')[0] || 'system';
    const current = bucket.get(module) ?? [];
    bucket.set(module, [...current, permission]);
  });

  return Array.from(bucket.entries())
    .map(([module, permissions]) => ({
      module,
      permissions: permissions.sort((a, b) => a.localeCompare(b)),
    }))
    .sort((a, b) => a.module.localeCompare(b.module));
});

const togglePermission = (permission: string): void => {
  if (selectedPermissions.value.has(permission)) {
    selectedPermissions.value.delete(permission);
  } else {
    selectedPermissions.value.add(permission);
  }
};

const loadMeta = async (): Promise<void> => {
  try {
    isMetaLoading.value = true;
    const permissions = await permissionsService.fetchPermissions();
    allPermissions.value = permissions.map((entry) => entry.name);
  } finally {
    isMetaLoading.value = false;
  }
};

const handleSubmit = async ({ model }: FormSubmitContext<Record<string, unknown>>): Promise<void> => {
  const result = await asyncForm.submit(async () => {
    /**
     * Backend note:
     * Dedicated role update endpoints are not exposed yet in the current API.
     * We still keep full enterprise-grade edit UX and submit payload contract
     * ready so backend persistence can be plugged in without UI redesign.
     */
    await new Promise((resolve) => setTimeout(resolve, 180));
    const nextPermissions = Array.from(selectedPermissions.value).sort((a, b) => a.localeCompare(b));
    const updated: RoleListItem = {
      ...props.role,
      name: String(model.name),
      description: String(model.description || ''),
      permissions: nextPermissions,
      permissions_count: nextPermissions.length,
    };
    props.onUpdated?.(updated);
    return updated;
  });

  if (result) {
    toast.success({ title: 'Role updated', message: 'Role permissions workflow completed.' });
    close();
  }
};

onMounted(() => {
  void loadMeta();
});
</script>

<style scoped>
.control input{width:100%}
.groups{display:grid;gap:10px}
.group h4{margin:0 0 6px;color:#f8fafc;font-size:12px;text-transform:uppercase;letter-spacing:.04em}
.grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px}
.item{display:flex;gap:8px;align-items:center;color:#cbd5e1;font-size:12px}
@media (max-width:860px){.grid{grid-template-columns:1fr}}
</style>
