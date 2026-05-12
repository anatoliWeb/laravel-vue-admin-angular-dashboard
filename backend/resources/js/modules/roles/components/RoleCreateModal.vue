<template>
  <BaseForm :model="form.model" @submit="handleSubmit">
    <BaseFormSection title="Create role">
      <BaseFormField label="Role name" :required="true" :error="form.getFieldError('name')">
        <input :value="String(form.model.name)" @input="form.setField('name', ($event.target as HTMLInputElement).value)" />
      </BaseFormField>
      <BaseFormField label="Internal description">
        <input :value="String(form.model.description)" @input="form.setField('description', ($event.target as HTMLInputElement).value)" />
      </BaseFormField>
    </BaseFormSection>

    <BaseFormSection title="Translations" description="Localized presentation fields are managed separately from immutable technical role key.">
      <div class="locale-tabs">
        <button
          v-for="locale in enabledLocales"
          :key="locale.code"
          type="button"
          class="locale-tab"
          :class="{ 'is-active': activeLocale === locale.code }"
          @click="activeLocale = locale.code"
        >
          {{ locale.label }}
        </button>
      </div>

      <div v-for="locale in enabledLocales" v-show="activeLocale === locale.code" :key="`${locale.code}-fields`" class="locale-panel">
        <BaseFormField :label="`${locale.label} label`">
          <input
            :value="translations[locale.code].label"
            @input="translations[locale.code].label = ($event.target as HTMLInputElement).value"
          />
        </BaseFormField>
        <BaseFormField :label="`${locale.label} description`">
          <textarea
            rows="2"
            :value="translations[locale.code].description"
            @input="translations[locale.code].description = ($event.target as HTMLTextAreaElement).value"
          />
        </BaseFormField>
      </div>
    </BaseFormSection>
    <BaseFormActions :loading="asyncForm.isSubmitting.value" :submit-disabled="!form.isDirty.value" @cancel="close" />
  </BaseForm>
</template>
<script setup lang="ts">
import { ref } from 'vue';

import type { FormSubmitContext } from '../../../shared/forms';
import { BaseForm, BaseFormActions, BaseFormField, BaseFormSection, useAsyncForm, useForm } from '../../../shared/forms';
import { getEnabledLocales } from '../../../shared/i18n/helpers';
import type { LocaleCode } from '../../../shared/i18n/config';
import { useToast } from '../../../shared/toast';
import { rolesService } from '../services/roles.service';
import type { RoleListItem } from '../types/roles.types';

type LocalizedTranslation = {
  label: string;
  description: string;
};

const props = defineProps<{ closeModal?: () => void; onCreated?: (item: RoleListItem) => void }>();
const form = useForm({ name: '', description: '' });
const asyncForm = useAsyncForm();
const toast = useToast();
const enabledLocales = getEnabledLocales();
const activeLocale = ref<LocaleCode>(enabledLocales[0]?.code ?? 'en');
const translations = ref<Record<LocaleCode, LocalizedTranslation>>({
  en: { label: '', description: '' },
  uk: { label: '', description: '' },
  de: { label: '', description: '' },
});
const close = (): void => props.closeModal?.();

const handleSubmit = async ({ model }: FormSubmitContext<Record<string, unknown>>): Promise<void> => {
  if (!String(model.name).trim()) { form.setErrors({ name: ['Role name is required.'] }); return; }
  const result = await asyncForm.submit(async () => {
    await new Promise((r) => setTimeout(r, 200));
    /**
     * Translation payload is intentionally separated from technical role key.
     * This keeps backend RBAC identifiers stable while allowing multilingual
     * labels/descriptions to evolve independently.
     */
    const translationPayload = Object.fromEntries(
      enabledLocales.map((locale) => [locale.code, { ...translations.value[locale.code] }]),
    );
    void translationPayload;

    const created = await rolesService.createRole({
      name: String(model.name).trim(),
      description: String(model.description || ''),
      permissions: [],
      translations: translationPayload,
    });
    props.onCreated?.(created);
    return created;
  });
  if (result) { toast.success({ title: 'Role created', message: 'Role create shell completed.' }); close(); }
};
</script>
<style scoped>
.locale-tabs{display:flex;gap:8px;flex-wrap:wrap}
.locale-tab{height:30px;padding:0 10px;border-radius:999px;border:1px solid rgba(71,85,105,.6);background:rgba(15,23,42,.65);color:#cbd5e1;font-size:12px}
.locale-tab.is-active{border-color:rgba(59,130,246,.55);background:rgba(59,130,246,.2);color:#bfdbfe}
.locale-panel{display:grid;gap:10px}
</style>
