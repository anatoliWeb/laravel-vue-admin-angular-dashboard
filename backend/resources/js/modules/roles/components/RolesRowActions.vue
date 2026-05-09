<template>
  <BaseDropdown>
    <template #trigger="{ isOpen }">
      <button type="button" class="role-actions-trigger" :class="{ 'is-open': isOpen }">Actions</button>
    </template>

    <template #default="{ close }">
      <div class="role-actions-panel">
        <button type="button" class="role-actions-panel__item" @click="onAction('view', close)">View</button>
        <button v-if="canEdit" type="button" class="role-actions-panel__item" @click="onAction('edit', close)">Edit</button>
        <button v-if="canPermissions" type="button" class="role-actions-panel__item" @click="onAction('permissions', close)">Permissions</button>
        <button v-if="canDelete" type="button" class="role-actions-panel__item role-actions-panel__item--danger" @click="onAction('delete', close)">Delete</button>
      </div>
    </template>
  </BaseDropdown>
</template>

<script setup lang="ts">
import BaseDropdown from '../../../shared/components/ui/BaseDropdown.vue';

interface Props {
  canEdit: boolean;
  canDelete: boolean;
  canPermissions: boolean;
}

defineProps<Props>();
const emit = defineEmits<{ action: [action: 'view' | 'edit' | 'permissions' | 'delete'] }>();

const onAction = (action: 'view' | 'edit' | 'permissions' | 'delete', close: () => void): void => {
  emit('action', action);
  close();
};
</script>

<style scoped>
.role-actions-trigger{height:30px;border-radius:8px;border:1px solid rgba(71,85,105,.6);background:rgba(15,23,42,.7);color:#e2e8f0;padding:0 10px;font-size:12px}
.role-actions-trigger.is-open,.role-actions-trigger:hover{border-color:rgba(96,165,250,.5);background:rgba(51,65,85,.8)}
.role-actions-panel{min-width:130px;display:grid;gap:2px}
.role-actions-panel__item{width:100%;text-align:left;border:0;border-radius:7px;background:transparent;color:#e2e8f0;padding:8px 10px;font-size:12px}
.role-actions-panel__item:hover{background:rgba(51,65,85,.72)}
.role-actions-panel__item--danger{color:#fda4af}
</style>
