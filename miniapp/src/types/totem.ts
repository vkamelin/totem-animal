export interface TotemTraits {
  extraversion: number | null;
  openness: number | null;
  self_control: number | null;
  agreeableness: number | null;
  emotional_stability: number | null;
  dominance: number | null;
  adaptability: number | null;
}

export interface TotemResult {
  animal_code: string;
  animal_name: string;
  result_title: string;
  result_description: string;
  result_image_path: string;
  user_traits: TotemTraits;
  animal_traits: TotemTraits;
  score_distance: number;
  created_at: string;
}

export interface NullableTotemTraits {
  extraversion: number | null;
  openness: number | null;
  self_control: number | null;
  agreeableness: number | null;
  emotional_stability: number | null;
  dominance: number | null;
  adaptability: number | null;
}

export interface NullableTotemResult {
  animal_code: string | null;
  animal_name: string | null;
  result_title: string | null;
  result_description: string | null;
  result_image_path: string | null;
  user_traits: NullableTotemTraits;
  animal_traits: NullableTotemTraits;
  score_distance: number | null;
  created_at: string | null;
}

export interface TestAnswerOption {
  code: string;
  text: string;
}

export interface TestQuestion {
  code: string;
  text: string;
  answers: TestAnswerOption[];
}

export interface TestAnswerSelection {
  questionCode: string;
  answerCode: string;
}

export interface StoredAnimalSummary {
  animalCode: string;
  animalName: string;
  resultTitle: string;
  resultDescription: string;
  resultImagePath: string;
}
