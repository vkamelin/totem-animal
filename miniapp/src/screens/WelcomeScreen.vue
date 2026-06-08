<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';

import AnimalAvatar from '@/components/AnimalAvatar.vue';
import BaseButton from '@/components/BaseButton.vue';
import BaseCard from '@/components/BaseCard.vue';
import LoadingState from '@/components/LoadingState.vue';
import ErrorState from '@/components/ErrorState.vue';
import { useApi } from '@/composables/useApi';
import { getMe } from '@/services/apiClient';
import { useResultStore } from '@/stores/resultStore';
import { useUserStore } from '@/stores/userStore';
import type { NullableTotemResult, TotemResult } from '@/types/totem';

const router = useRouter();
const userStore = useUserStore();
const resultStore = useResultStore();
const { request, isLoading } = useApi();

const localError = ref<string | null>(null);
const hasLoaded = ref(false);

async function loadClient() {
  localError.value = null;

  await request(async () => {
    const response = await getMe(userStore.publicId);
    if (response.data.public_id !== userStore.publicId) {
      userStore.setPublicId(response.data.public_id);
    }

    const result = normalizeResult(response.data.result);
    if (result) {
      resultStore.setResult(result);
    } else {
      resultStore.setEmptyResult();
    }
    hasLoaded.value = true;
  }).catch((error: unknown) => {
    localError.value = error instanceof Error ? error.message : 'Failed to load your profile.';
    hasLoaded.value = true;
  });
}

function normalizeResult(result: NullableTotemResult): TotemResult | null {
  if (
    result.animal_code &&
    result.animal_name &&
    result.result_title &&
    result.result_description &&
    result.result_image_path &&
    result.user_traits &&
    result.animal_traits &&
    result.score_distance !== null &&
    result.created_at
  ) {
    return result as TotemResult;
  }

  return null;
}

onMounted(() => {
  void loadClient();
});
</script>

<template>
  <div class="space-y-4 pb-6">
    <div class="space-y-3 pt-2">
      <p class="text-[0.72rem] uppercase tracking-[0.35em] text-amber-300/80">
        Totem Animal
      </p>
      <h1 class="max-w-[13ch] text-4xl font-semibold tracking-tight text-slate-100">
        Find the animal that reflects your inner pattern.
      </h1>
      <p class="max-w-[32ch] text-base leading-7 text-slate-400">
        Answer a short set of touch-friendly questions, collect your result, and return to your profile later with the same public ID.
      </p>
    </div>

    <BaseCard>
      <div class="space-y-4">
        <div class="flex items-center gap-4">
          <AnimalAvatar :size="92" />
          <div class="space-y-2">
            <p class="text-sm font-medium text-amber-300">Anonymous by design</p>
            <p class="text-sm leading-6 text-slate-400">
              We store only a backend public ID locally so your test can resume and your result can be restored.
            </p>
          </div>
        </div>

        <div class="grid gap-3 text-sm text-slate-300">
          <div class="rounded-2xl border border-slate-800 bg-slate-950/60 px-4 py-3">
            A short onboarding step
          </div>
          <div class="rounded-2xl border border-slate-800 bg-slate-950/60 px-4 py-3">
            One question at a time
          </div>
          <div class="rounded-2xl border border-slate-800 bg-slate-950/60 px-4 py-3">
            A saved totem animal result
          </div>
        </div>
      </div>
    </BaseCard>

    <LoadingState v-if="isLoading && !hasLoaded">
      <template #title>Loading your session</template>
      <template #description>We are resolving your public ID and checking whether a result already exists.</template>
    </LoadingState>

    <ErrorState
      v-else-if="localError"
      title="We could not load your session"
      :description="localError"
      retry-label="Try again"
      home-label="Reload"
      @retry="loadClient"
      @home="loadClient"
    />

    <div v-else class="space-y-3">
      <BaseButton fullWidth @click="router.push('/intro')">
        Start test
      </BaseButton>
      <BaseButton
        v-if="resultStore.hasResult"
        fullWidth
        variant="secondary"
        @click="router.push('/result')"
      >
        View result
      </BaseButton>
    </div>
  </div>
</template>
