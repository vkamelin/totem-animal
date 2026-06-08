import type { NullableTotemResult, TestQuestion, TotemResult } from '@/types/totem';

export type ApiSuccessResponse<T> = {
  success: true;
  data: T;
};

export type ApiErrorCode =
  | 'VALIDATION_ERROR'
  | 'CLIENT_NOT_FOUND'
  | 'RESULT_ALREADY_EXISTS'
  | 'RESULT_NOT_FOUND'
  | 'QUESTIONS_NOT_CONFIGURED'
  | 'ANIMALS_NOT_CONFIGURED'
  | 'TEST_SESSION_NOT_FOUND'
  | 'TEST_SESSION_ALREADY_COMPLETED'
  | 'DUPLICATE_ANSWERS'
  | 'ANSWERS_COUNT_MISMATCH'
  | 'INVALID_ANSWERS'
  | 'FINISH_TEST_VALIDATION'
  | 'RESULT_VALIDATION'
  | 'INTERNAL_SERVER_ERROR'
  | 'NETWORK_ERROR'
  | 'TIMEOUT_ERROR'
  | 'UNKNOWN_ERROR';

export interface ApiErrorPayload {
  code: ApiErrorCode | string;
  message: string;
  details?: unknown;
}

export interface ApiErrorResponse {
  success: false;
  error: ApiErrorPayload;
}

export interface PublicIdRequest {
  public_id: string | null;
}

export interface MeData {
  public_id: string;
  result: NullableTotemResult;
}

export type MeResponse = ApiSuccessResponse<MeData>;

export interface StartTestRequest {
  public_id: string;
}

export interface StartTestData {
  public_id: string;
  test_session_id: number;
  questions_count: number;
  questions: TestQuestion[];
}

export type StartTestResponse = ApiSuccessResponse<StartTestData>;

export interface TestAnswerPayload {
  question_code: string;
  answer_code: string;
}

export interface FinishTestRequest {
  public_id: string;
  test_session_id: number;
  answers: TestAnswerPayload[];
}

export interface FinishTestData {
  public_id: string;
  test_session_id: number;
  result: TotemResult;
}

export type FinishTestResponse = ApiSuccessResponse<FinishTestData>;

export interface ResultData {
  public_id: string;
  result: TotemResult;
}

export type ResultResponse = ApiSuccessResponse<ResultData>;

export interface ApiClientError extends Error {
  code: ApiErrorCode;
  status: number;
  details?: unknown;
  isApiError: true;
}
