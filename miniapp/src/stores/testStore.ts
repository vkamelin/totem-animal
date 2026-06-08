import { computed, ref } from 'vue';
import { defineStore } from 'pinia';

import type {
  TestAnswerSelection,
  TestQuestion,
} from '@/types/totem';

export const useTestStore = defineStore('test', () => {
  const testSessionId = ref<number | null>(null);
  const questions = ref<TestQuestion[]>([]);
  const currentQuestionIndex = ref(0);
  const answers = ref<TestAnswerSelection[]>([]);

  const currentQuestion = computed(() => questions.value[currentQuestionIndex.value] ?? null);
  const totalQuestions = computed(() => questions.value.length);
  const progress = computed(() => {
    if (totalQuestions.value === 0) {
      return 0;
    }

    return Math.min(currentQuestionIndex.value + 1, totalQuestions.value);
  });
  const isComplete = computed(() => currentQuestionIndex.value >= questions.value.length && questions.value.length > 0);

  function startSession(sessionId: number, nextQuestions: TestQuestion[]) {
    testSessionId.value = sessionId;
    questions.value = nextQuestions;
    currentQuestionIndex.value = 0;
    answers.value = [];
  }

  function selectAnswer(answerCode: string) {
    const question = currentQuestion.value;
    if (!question) {
      return null;
    }

    const selection: TestAnswerSelection = {
      questionCode: question.code,
      answerCode,
    };

    const existingIndex = answers.value.findIndex(
      (item) => item.questionCode === selection.questionCode,
    );

    if (existingIndex >= 0) {
      answers.value.splice(existingIndex, 1, selection);
    } else {
      answers.value.push(selection);
    }

    return selection;
  }

  function goToNextQuestion() {
    if (currentQuestionIndex.value < questions.value.length) {
      currentQuestionIndex.value += 1;
    }
  }

  function resetTest() {
    testSessionId.value = null;
    questions.value = [];
    currentQuestionIndex.value = 0;
    answers.value = [];
  }

  return {
    testSessionId,
    questions,
    currentQuestionIndex,
    answers,
    currentQuestion,
    totalQuestions,
    progress,
    isComplete,
    startSession,
    selectAnswer,
    goToNextQuestion,
    resetTest,
  };
});
