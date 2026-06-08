<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';

import BaseButton from '@/components/BaseButton.vue';
import BaseCard from '@/components/BaseCard.vue';
import ErrorState from '@/components/ErrorState.vue';
import LoadingState from '@/components/LoadingState.vue';
import { useApi } from '@/composables/useApi';
import { getMe, startTest } from '@/services/apiClient';
import { useAppStore } from '@/stores/appStore';
import { useTestStore } from '@/stores/testStore';
import { useUserStore } from '@/stores/userStore';
import type { ApiClientError } from '@/types/api';
import type { NullableTotemResult, TotemResult } from '@/types/totem';

const router = useRouter();
const userStore = useUserStore();
const testStore = useTestStore();
const appStore = useAppStore();
const { request, isLoading } = useApi();

const localError = ref<string | null>(null);
const hasInitialized = ref(false);

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

async function ensurePublicId(): Promise<string> {
  if (userStore.publicId) {
    return userStore.publicId;
  }

  const response = await getMe(null);
  userStore.setPublicId(response.data.public_id);
  const maybeResult = normalizeResult(response.data.result);
  if (maybeResult) {
    return response.data.public_id;
  }

  return response.data.public_id;
}

async function beginTest() {
  localError.value = null;

  await request(async () => {
    const publicId = await ensurePublicId();
    const response = await startTest(publicId);
    testStore.startSession(response.data.test_session_id, response.data.questions);
    hasInitialized.value = true;
    await router.push('/test');
  }).catch((error: unknown) => {
    const apiError = error as ApiClientError;
    if (apiError?.code === 'RESULT_ALREADY_EXISTS') {
      appStore.setError(null);
      void router.push('/result');
      return;
    }

    localError.value = error instanceof Error ? error.message : 'We could not start the test.';
    hasInitialized.value = true;
  });
}

onMounted(() => {
  if (!userStore.hasHydrated) {
    userStore.hydrateFromStorage();
  }
});
</script>

<template>
  <div class="space-y-4 pb-6">
    <div class="space-y-3">
      <p class="text-sm font-medium text-amber-300">Test intro</p>
      <h1 class="text-3xl font-semibold tracking-tight text-slate-100">
        A short ritual, not a long quiz.
      </h1>
      <p class="text-base leading-7 text-slate-400">
        We will open a server-backed test session, then show one question at a time with large tap targets designed for mobile use inside VK.
      </p>
    </div>

    <BaseCard>
      <div class="space-y-4">
        <div class="grid gap-3 sm:grid-cols-3">
          <div class="rounded-2xl border border-slate-800 bg-slate-950/60 p-4">
            <p class="text-2xl font-semibold text-violet-300">1</p>
            <p class="mt-2 text-sm text-slate-300">Resolve your public ID</p>
          </div>
          <div class="rounded-2xl border border-slate-800 bg-slate-950/60 p-4">
            <p class="text-2xl font-semibold text-violet-300">2</p>
            <p class="mt-2 text-sm text-slate-300">Answer the questions</p>
          </div>
          <div class="rounded-2xl border border-slate-800 bg-slate-950/60 p-4">
            <p class="text-2xl font-semibold text-violet-300">3</p>
            <p class="mt-2 text-sm text-slate-300">Collect your result</p>
          </div>
        </div>

        <div class="rounded-2xl border border-amber-500/20 bg-amber-500/10 p-4 text-sm leading-6 text-amber-100">
          Estimated duration: about 2 to 4 minutes. TODO: sync with backend question count and real completion timing.
        </div>
      </div>
    </BaseCard>

    <LoadingState v-if="isLoading && !hasInitialized">
      <template #title>Preparing your session</template>
      <template #description>We are opening the test session and fetching active questions.</template>
    </LoadingState>

    <ErrorState
      v-else-if="localError"
      title="We could not start the test"
      :description="localError"
      retry-label="Try again"
      home-label="Back to start"
      @retry="beginTest"
      @home="router.push('/')"
    />

    <BaseButton v-else fullWidth @click="beginTest">
      Start test
    </BaseButton>
  </div>
</template>
