<template>
  <div class="overflow-hidden rounded-lg border border-slate-200 bg-white">
    <table class="min-w-full divide-y divide-slate-200 text-sm">
      <thead class="bg-slate-50">
        <tr>
          <th
            v-for="column in columns"
            :key="column.key"
            class="px-4 py-3 text-left font-medium uppercase tracking-wide text-slate-500"
          >
            {{ column.label }}
          </th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        <tr v-for="(row, index) in rows" :key="index" class="hover:bg-slate-50">
          <td v-for="column in columns" :key="column.key" class="px-4 py-3 text-slate-700">
            {{ row[column.key] ?? '—' }}
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup lang="ts">
export interface BaseTableColumn {
  key: string;
  label: string;
}

interface Props {
  columns: BaseTableColumn[];
  rows: Record<string, unknown>[];
}

defineProps<Props>();
</script>

