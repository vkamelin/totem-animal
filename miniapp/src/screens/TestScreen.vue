<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';

import AnimalAvatar from '@/components/AnimalAvatar.vue';
import BaseButton from '@/components/BaseButton.vue';
import BaseCard from '@/components/BaseCard.vue';
import ErrorState from '@/components/ErrorState.vue';
import LoadingState from '@/components/LoadingState.vue';
import OptionButton from '@/components/OptionButton.vue';
import ProgressBar from '@/components/ProgressBar.vue';
import { useApi } from '@/composables/useApi';
import { finishTest } from '@/services/apiClient';
import { useAppStore } from '@/stores/appStore';
import { useResultStore } from '@/stores/resultStore';
import { useTestStore } from '@/stores/testStore';
import { useUserStore } from '@/stores/userStore';
import type { ApiClientError, TestAnswerPayload } from '@/types/api';

const router = useRouter();
const userStore = useUserStore();
const testStore = useTestStore();
const resultStore = useResultStore();
const appStore = useAppStore();
const { request, isLoading } = useApi();

const localError = ref<string | null>(null);
const selectedAnswerCode = ref<string | null>(null);

const currentQuestion = computed(() => testStore.currentQuestion);
const totalQuestions = computed(() => testStore.totalQuestions);
const progress = computed(() => testStore.progress);
const isSessionReady = computed(() => Boolean(testStore.testSessionId && totalQuestions.value > 0));

async function submitAnswer(answerCode: string) {
  if (!currentQuestion.value || !userStore.publicId) {
    await router.push('/intro');
    return;
  }

  selectedAnswerCode.value = answerCode;
  localError.value = null;

  const selection = testStore.selectAnswer(answerCode);
  if (!selection) {
    return;
  }

  const isLastQuestion = progress.value >= totalQuestions.value;

  if (!isLastQuestion) {
    testStore.goToNextQuestion();
    selectedAnswerCode.value = null;
    return;
  }

  const payload: TestAnswerPayload[] = testStore.answers.map((item) => ({
    question_code: item.questionCode,
    answer_code: item.answerCode,
  }));

  await request(async () => {
    const response = await finishTest(
      userStore.publicId as string,
      payload,
      testStore.testSessionId ?? undefined,
    );

    resultStore.setResult(response.data.result);
    selectedAnswerCode.value = null;
    await router.push('/result');
  }).catch((error: unknown) => {
    const apiError = error as ApiClientError;
    localError.value = error instanceof Error ? error.message : 'We could not finish the test.';
    if (apiError?.code === 'RESULT_ALREADY_EXISTS') {
      appStore.setError(null);
      void router.push('/result');
      return;
    }
  });
}

onMounted(() => {
  if (!isSessionReady.value) {
    void router.replace('/intro');
  }
});
</script>

<template>
  <div class="space-y-4 pb-6">
    <LoadingState v-if="isLoading && !currentQuestion">
      <template #title>Finishing your result</template>
      <template #description>Please wait while we save the final answer and calculate your totem animal.</template>
    </LoadingState>

    <ErrorState
      v-else-if="localError"
      title="We could not continue the test"
      :description="localError"
      retry-label="Retry finish"
      home-label="Back to intro"
      @retry="submitAnswer(selectedAnswerCode ?? '')"
      @home="router.push('/intro')"
    />

    <div v-else-if="currentQuestion" class="space-y-4">
      <ProgressBar :current="progress" :total="totalQuestions" />

      <BaseCard>
        <div class="space-y-4">
          <div class="flex items-center justify-between gap-3">
            <div class="space-y-1">
              <p class="text-sm font-medium text-amber-300">
                Question {{ progress }}
              </p>
              <p class="text-xs text-slate-400">
                Choose the answer that feels most natural.
              </p>
            </div>
            <AnimalAvatar :size="64" />
          </div>

          <h1 class="text-2xl font-semibold tracking-tight text-slate-100">
            {{ currentQuestion.text }}
          </h1>
        </div>
      </BaseCard>

      <div class="space-y-3">
        <OptionButton
          v-for="answer in currentQuestion.answers"
          :key="answer.code"
          :selected="selectedAnswerCode === answer.code"
          :disabled="isLoading"
          @click="submitAnswer(answer.code)"
        >
          {{ answer.text }}
        </OptionButton>
      </div>
    </div>

    <BaseCard v-else>
      <div class="space-y-4 text-center">
        <p class="text-lg font-semibold text-slate-100">No active test session</p>
        <p class="text-sm leading-6 text-slate-400">
          Start a new session from the intro screen to fetch questions from the backend.
        </p>
        <BaseButton fullWidth @click="router.push('/intro')">Go to intro</BaseButton>
      </div>
    </BaseCard>
  </div>
</template>
