import type {
  ApiClientError,
  ApiErrorPayload,
  ApiErrorResponse,
  FinishTestData,
  FinishTestRequest,
  FinishTestResponse,
  MeResponse,
  PublicIdRequest,
  ResultResponse,
  StartTestData,
  StartTestRequest,
  StartTestResponse,
  TestAnswerPayload,
} from '@/types/api';

const API_BASE_URL = '/api';
const REQUEST_TIMEOUT_MS = 15_000;

function createApiError(
  code: ApiClientError['code'] | string,
  message: string,
  status: number,
  details?: unknown,
): ApiClientError {
  const error = new Error(message) as ApiClientError;
  error.name = 'ApiClientError';
  error.code = code as ApiClientError['code'];
  error.status = status;
  error.details = details;
  error.isApiError = true;
  return error;
}

async function readErrorPayload(response: Response): Promise<ApiErrorPayload | null> {
  try {
    const payload = (await response.json()) as ApiErrorResponse | unknown;
    if (
      typeof payload === 'object' &&
      payload !== null &&
      'success' in payload &&
      payload.success === false &&
      'error' in payload &&
      typeof payload.error === 'object' &&
      payload.error !== null
    ) {
      return payload.error as ApiErrorPayload;
    }
  } catch {
    return null;
  }

  return null;
}

async function request<TResponse>(
  path: string,
  options: RequestInit = {},
): Promise<TResponse> {
  const controller = new AbortController();
  const timeoutId = window.setTimeout(() => controller.abort(), REQUEST_TIMEOUT_MS);

  try {
    const response = await fetch(`${API_BASE_URL}${path}`, {
      ...options,
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        ...(options.headers ?? {}),
      },
      signal: controller.signal,
    });

    if (!response.ok) {
      const errorPayload = await readErrorPayload(response);
      const message = errorPayload?.message ?? `Request failed with status ${response.status}.`;
      const code = errorPayload?.code ?? 'UNKNOWN_ERROR';
      throw createApiError(code, message, response.status, errorPayload?.details);
    }

    return (await response.json()) as TResponse;
  } catch (error) {
    if (error instanceof DOMException && error.name === 'AbortError') {
      throw createApiError('TIMEOUT_ERROR', 'The request timed out.', 408);
    }

    if (isApiClientError(error)) {
      throw error;
    }

    throw createApiError('NETWORK_ERROR', 'A network error occurred.', 0, error);
  } finally {
    window.clearTimeout(timeoutId);
  }
}

function isApiClientError(error: unknown): error is ApiClientError {
  return (
    typeof error === 'object' &&
    error !== null &&
    'isApiError' in error &&
    (error as ApiClientError).isApiError === true
  );
}

export function getMe(publicId: string | null): Promise<MeResponse> {
  const payload: PublicIdRequest = { public_id: publicId };
  return request<MeResponse>('/me', {
    method: 'POST',
    body: JSON.stringify(payload),
  });
}

export function startTest(publicId: string): Promise<StartTestResponse> {
  const payload: StartTestRequest = { public_id: publicId };
  return request<StartTestResponse>('/test/start', {
    method: 'POST',
    body: JSON.stringify(payload),
  });
}

export function finishTest(
  publicId: string,
  answers: TestAnswerPayload[],
  testSessionId?: number,
): Promise<FinishTestResponse> {
  if (typeof testSessionId !== 'number') {
    return Promise.reject(
      createApiError(
        'VALIDATION_ERROR',
        'test_session_id is required to finish the test.',
        422,
      ),
    );
  }

  const payload: FinishTestRequest = {
    public_id: publicId,
    test_session_id: testSessionId,
    answers,
  };

  return request<FinishTestResponse>('/test/finish', {
    method: 'POST',
    body: JSON.stringify(payload),
  });
}

export function getResult(publicId: string): Promise<ResultResponse> {
  return request<ResultResponse>(`/result/${encodeURIComponent(publicId)}`, {
    method: 'GET',
  });
}

export type { FinishTestData, StartTestData };
