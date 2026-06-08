Документ описывает набор вопросов для развлекательного теста «Тотемное животное».

Это не клинический опросник и не психодиагностика. Модель построена как игровая типология поверх личностных шкал. Методологический ориентир — Big Five / Five-Factor Model и открытый пул IPIP, но формулировки вопросов сделаны специально для развлекательного продукта, а не скопированы из академических тестов.

## 1. Шкалы

Используются те же 7 шкал, что и в `animals.md`.

```text
extraversion           социальная энергия, контактность, тяга к людям
openness               любопытство, нестандартность, тяга к новому
self_control           дисциплина, порядок, планирование, устойчивость к хаосу
agreeableness          мягкость, эмпатия, готовность сотрудничать
emotional_stability    спокойствие, стрессоустойчивость, способность держать удар
dominance              напор, лидерство, контроль ситуации, готовность давить
adaptability           гибкость, хитрость, ситуативность, умение выкручиваться
```

## 2. Принцип весов

Каждый ответ меняет только те шкалы, которые действительно связаны с выбранным поведением.

Диапазон веса одного ответа по одной шкале:

```text
-14..-10   сильное снижение признака
-9..-5     умеренное снижение признака
-4..4      слабое влияние
5..9       умеренное усиление признака
10..14     сильное усиление признака
```

В этом документе специально не используются экстремальные веса `18–20`, чтобы пользовательский профиль не улетал слишком быстро в 0 или 100.

## 3. Рекомендуемый алгоритм расчёта

Начальное значение каждой шкалы: `50`.

Рекомендуемый способ — считать среднее смещение по каждой шкале, а не простую сумму всех весов.

```php
$userTraits = [
    'extraversion' => 50,
    'openness' => 50,
    'self_control' => 50,
    'agreeableness' => 50,
    'emotional_stability' => 50,
    'dominance' => 50,
    'adaptability' => 50,
];

$traitDeltas = [];
$traitCounts = [];

foreach ($userTraits as $trait => $value) {
    $traitDeltas[$trait] = 0;
    $traitCounts[$trait] = 0;
}

foreach ($selectedAnswers as $answer) {
    foreach ($answer['weights'] as $trait => $delta) {
        $traitDeltas[$trait] += $delta;
        $traitCounts[$trait]++;
    }
}

foreach ($userTraits as $trait => $baseValue) {
    if ($traitCounts[$trait] > 0) {
        $averageDelta = $traitDeltas[$trait] / $traitCounts[$trait];
        $userTraits[$trait] = $baseValue + ($averageDelta * 3.2);
    }

    $userTraits[$trait] = max(0, min(100, (int) round($userTraits[$trait])));
}
```

Коэффициент `3.2` подобран для развлекательного теста: профиль заметно двигается, но не становится слишком экстремальным после 20–24 вопросов.

## 4. Как использовать вопросы в MVP

В документе 28 вопросов.

Для одного прохождения рекомендуется показывать:

```text
20 вопросов из 28
```

Желательно выбирать вопросы не полностью случайно, а с контролем покрытия шкал: каждая шкала должна встретиться хотя бы в 9–10 ответах выбранного набора.

Если нужна максимально предсказуемая первая версия — показывать все 28 вопросов. Если важнее повторное прохождение — показывать 20 случайных вопросов с балансировкой.

## 5. Вопросы и ответы

```json
[
  {
    "code": "q01_unknown_company",
    "question": "Ты попадаешь в незнакомую компанию. Что делаешь?",
    "answers": [
      {
        "code": "a",
        "text": "Быстро нахожу, с кем поговорить, и вливаюсь в движ",
        "weights": {
          "extraversion": 12,
          "agreeableness": 6,
          "adaptability": 5
        }
      },
      {
        "code": "b",
        "text": "Сначала наблюдаю, кто тут кто, а потом выбираю момент",
        "weights": {
          "extraversion": -6,
          "openness": 6,
          "self_control": 5,
          "adaptability": 9
        }
      },
      {
        "code": "c",
        "text": "Держусь спокойно, без лишней активности",
        "weights": {
          "extraversion": -10,
          "self_control": 6,
          "emotional_stability": 7
        }
      },
      {
        "code": "d",
        "text": "Если атмосфера странная — ищу повод уйти",
        "weights": {
          "extraversion": -12,
          "emotional_stability": -6,
          "adaptability": 6,
          "agreeableness": -3
        }
      }
    ]
  },
  {
    "code": "q02_conflict_role",
    "question": "В компании начинается спор. Твоя обычная роль?",
    "answers": [
      {
        "code": "a",
        "text": "Беру слово и пытаюсь разрулить",
        "weights": {
          "extraversion": 7,
          "dominance": 12,
          "self_control": 6,
          "emotional_stability": 6
        }
      },
      {
        "code": "b",
        "text": "Стараюсь примирить людей, если это возможно",
        "weights": {
          "agreeableness": 13,
          "emotional_stability": 5,
          "dominance": -4
        }
      },
      {
        "code": "c",
        "text": "Смотрю, кто перегибает, и делаю выводы",
        "weights": {
          "extraversion": -6,
          "openness": 6,
          "self_control": 5,
          "adaptability": 7,
          "agreeableness": -3
        }
      },
      {
        "code": "d",
        "text": "Не лезу, если меня напрямую не трогают",
        "weights": {
          "extraversion": -10,
          "dominance": -7,
          "emotional_stability": 5,
          "agreeableness": -2
        }
      }
    ]
  },
  {
    "code": "q03_broken_plan",
    "question": "У тебя сорвался план на день. Как реагируешь?",
    "answers": [
      {
        "code": "a",
        "text": "Быстро перестраиваюсь и ищу новый вариант",
        "weights": {
          "adaptability": 13,
          "emotional_stability": 7,
          "openness": 5
        }
      },
      {
        "code": "b",
        "text": "Раздражаюсь, но всё равно собираю новый план",
        "weights": {
          "self_control": 10,
          "emotional_stability": -4,
          "adaptability": 5
        }
      },
      {
        "code": "c",
        "text": "Мне даже интересно, куда теперь повернёт день",
        "weights": {
          "openness": 12,
          "adaptability": 9,
          "self_control": -6
        }
      },
      {
        "code": "d",
        "text": "Меня это выбивает, нужно время прийти в себя",
        "weights": {
          "emotional_stability": -13,
          "adaptability": -5,
          "self_control": -4
        }
      }
    ]
  },
  {
    "code": "q04_decisions",
    "question": "Как ты обычно принимаешь важные решения?",
    "answers": [
      {
        "code": "a",
        "text": "Собираю факты, сравниваю варианты, потом действую",
        "weights": {
          "self_control": 13,
          "openness": 5,
          "emotional_stability": 7
        }
      },
      {
        "code": "b",
        "text": "Слушаю интуицию, но не отключаю голову",
        "weights": {
          "openness": 9,
          "adaptability": 8,
          "self_control": 4
        }
      },
      {
        "code": "c",
        "text": "Решаю быстро, если чувствую, что момент подходящий",
        "weights": {
          "dominance": 9,
          "adaptability": 8,
          "self_control": -5,
          "emotional_stability": 5
        }
      },
      {
        "code": "d",
        "text": "Долго сомневаюсь и прокручиваю последствия",
        "weights": {
          "emotional_stability": -12,
          "self_control": 5,
          "dominance": -6,
          "openness": 3
        }
      }
    ]
  },
  {
    "code": "q05_annoying_people",
    "question": "Что тебя сильнее всего раздражает в людях?",
    "answers": [
      {
        "code": "a",
        "text": "Хаос, безответственность и обещания в пустоту",
        "weights": {
          "self_control": 13,
          "agreeableness": -4,
          "dominance": 5
        }
      },
      {
        "code": "b",
        "text": "Жестокость, равнодушие и отсутствие эмпатии",
        "weights": {
          "agreeableness": 13,
          "emotional_stability": -3,
          "dominance": -3
        }
      },
      {
        "code": "c",
        "text": "Тупое следование правилам без понимания смысла",
        "weights": {
          "openness": 12,
          "adaptability": 7,
          "self_control": -5
        }
      },
      {
        "code": "d",
        "text": "Когда кто-то пытается мной командовать",
        "weights": {
          "dominance": 11,
          "agreeableness": -7,
          "emotional_stability": -3,
          "adaptability": 4
        }
      }
    ]
  },
  {
    "code": "q06_rest",
    "question": "Какой отдых тебе ближе?",
    "answers": [
      {
        "code": "a",
        "text": "Люди, движение, разговоры, новые места",
        "weights": {
          "extraversion": 13,
          "openness": 7,
          "adaptability": 5
        }
      },
      {
        "code": "b",
        "text": "Тишина, дом, лес, минимум контактов",
        "weights": {
          "extraversion": -13,
          "emotional_stability": 8,
          "self_control": 4
        }
      },
      {
        "code": "c",
        "text": "Что-то необычное: новый город, формат, опыт",
        "weights": {
          "openness": 13,
          "adaptability": 8,
          "self_control": -4
        }
      },
      {
        "code": "d",
        "text": "Комфортный понятный отдых без сюрпризов",
        "weights": {
          "self_control": 8,
          "emotional_stability": 7,
          "openness": -8,
          "adaptability": -4
        }
      }
    ]
  },
  {
    "code": "q07_work_role",
    "question": "В работе или деле тебе ближе роль…",
    "answers": [
      {
        "code": "a",
        "text": "Лидера, который задаёт направление",
        "weights": {
          "dominance": 13,
          "extraversion": 6,
          "emotional_stability": 6
        }
      },
      {
        "code": "b",
        "text": "Стратега, который видит систему и риски",
        "weights": {
          "openness": 8,
          "self_control": 11,
          "extraversion": -5
        }
      },
      {
        "code": "c",
        "text": "Исполнителя, который надёжно доводит до конца",
        "weights": {
          "self_control": 13,
          "agreeableness": 7,
          "dominance": -5
        }
      },
      {
        "code": "d",
        "text": "Импровизатора, который спасает, когда всё пошло не так",
        "weights": {
          "adaptability": 13,
          "openness": 7,
          "self_control": -8,
          "emotional_stability": 4
        }
      }
    ]
  },
  {
    "code": "q08_conflict_behavior",
    "question": "Как ты ведёшь себя в конфликте?",
    "answers": [
      {
        "code": "a",
        "text": "Говорю прямо и давлю, если нужно",
        "weights": {
          "dominance": 13,
          "agreeableness": -10,
          "emotional_stability": 7,
          "extraversion": 4
        }
      },
      {
        "code": "b",
        "text": "Пытаюсь договориться без лишней крови",
        "weights": {
          "agreeableness": 13,
          "self_control": 7,
          "dominance": -4
        }
      },
      {
        "code": "c",
        "text": "Ухожу в сторону и возвращаюсь, когда эмоции спадут",
        "weights": {
          "extraversion": -8,
          "self_control": 7,
          "emotional_stability": 5,
          "dominance": -6
        }
      },
      {
        "code": "d",
        "text": "Ищу слабое место в позиции другого человека",
        "weights": {
          "adaptability": 12,
          "openness": 7,
          "agreeableness": -10,
          "dominance": 5
        }
      }
    ]
  },
  {
    "code": "q09_rules",
    "question": "Как ты относишься к правилам?",
    "answers": [
      {
        "code": "a",
        "text": "Правила нужны, иначе всё развалится",
        "weights": {
          "self_control": 13,
          "openness": -6,
          "adaptability": -4,
          "emotional_stability": 4
        }
      },
      {
        "code": "b",
        "text": "Правила нормальны, если они имеют смысл",
        "weights": {
          "self_control": 8,
          "openness": 7,
          "emotional_stability": 5
        }
      },
      {
        "code": "c",
        "text": "Правила — это рекомендация, если никто не пострадал",
        "weights": {
          "adaptability": 12,
          "openness": 8,
          "self_control": -8,
          "agreeableness": 3
        }
      },
      {
        "code": "d",
        "text": "Чужие правила часто пишут те, кто хочет контролировать других",
        "weights": {
          "dominance": 9,
          "agreeableness": -7,
          "openness": 8,
          "adaptability": 6
        }
      }
    ]
  },
  {
    "code": "q10_support",
    "question": "Что ты делаешь, когда видишь, что человек расстроен?",
    "answers": [
      {
        "code": "a",
        "text": "Подхожу, спрашиваю, что случилось, стараюсь поддержать",
        "weights": {
          "agreeableness": 14,
          "extraversion": 7,
          "emotional_stability": 3
        }
      },
      {
        "code": "b",
        "text": "Помогаю делом, без лишних разговоров",
        "weights": {
          "agreeableness": 9,
          "self_control": 8,
          "extraversion": -4
        }
      },
      {
        "code": "c",
        "text": "Сначала наблюдаю, уместно ли вообще вмешиваться",
        "weights": {
          "self_control": 6,
          "adaptability": 6,
          "extraversion": -6,
          "agreeableness": 2
        }
      },
      {
        "code": "d",
        "text": "Не лезу: не все хотят, чтобы их трогали",
        "weights": {
          "extraversion": -9,
          "agreeableness": -4,
          "emotional_stability": 6,
          "dominance": -3
        }
      }
    ]
  },
  {
    "code": "q11_friends",
    "question": "Что для тебя естественнее в компании друзей?",
    "answers": [
      {
        "code": "a",
        "text": "Шутить, говорить, запускать движ",
        "weights": {
          "extraversion": 14,
          "openness": 6,
          "self_control": -4
        }
      },
      {
        "code": "b",
        "text": "Быть тем, кто всех собирает и организует",
        "weights": {
          "extraversion": 8,
          "self_control": 9,
          "dominance": 8,
          "agreeableness": 4
        }
      },
      {
        "code": "c",
        "text": "Сидеть рядом с близкими и спокойно общаться",
        "weights": {
          "agreeableness": 10,
          "emotional_stability": 7,
          "extraversion": -4
        }
      },
      {
        "code": "d",
        "text": "Появиться, исчезнуть, снова появиться",
        "weights": {
          "adaptability": 11,
          "openness": 7,
          "self_control": -7,
          "extraversion": -2
        }
      }
    ]
  },
  {
    "code": "q12_risk",
    "question": "Как ты реагируешь на риск?",
    "answers": [
      {
        "code": "a",
        "text": "Если цель стоит того — иду вперёд",
        "weights": {
          "dominance": 11,
          "emotional_stability": 8,
          "openness": 5
        }
      },
      {
        "code": "b",
        "text": "Сначала считаю последствия",
        "weights": {
          "self_control": 13,
          "emotional_stability": 7,
          "openness": -3
        }
      },
      {
        "code": "c",
        "text": "Риск бодрит, без него скучно",
        "weights": {
          "openness": 12,
          "extraversion": 6,
          "self_control": -8,
          "adaptability": 6
        }
      },
      {
        "code": "d",
        "text": "Скорее избегаю, если можно не лезть",
        "weights": {
          "emotional_stability": -6,
          "dominance": -8,
          "self_control": 5,
          "openness": -6
        }
      }
    ]
  },
  {
    "code": "q13_chaos",
    "question": "Когда вокруг хаос, ты…",
    "answers": [
      {
        "code": "a",
        "text": "Навожу порядок и раскладываю всё по местам",
        "weights": {
          "self_control": 14,
          "dominance": 6,
          "adaptability": -5
        }
      },
      {
        "code": "b",
        "text": "Использую хаос как возможность",
        "weights": {
          "adaptability": 13,
          "openness": 8,
          "self_control": -6,
          "emotional_stability": 4
        }
      },
      {
        "code": "c",
        "text": "Сохраняю спокойствие и жду, пока шум осядет",
        "weights": {
          "emotional_stability": 12,
          "extraversion": -6,
          "dominance": -4,
          "self_control": 4
        }
      },
      {
        "code": "d",
        "text": "Внутренне напрягаюсь, даже если внешне норм",
        "weights": {
          "emotional_stability": -13,
          "self_control": 4,
          "adaptability": -4,
          "extraversion": -3
        }
      }
    ]
  },
  {
    "code": "q14_core_need",
    "question": "Что тебе ближе?",
    "answers": [
      {
        "code": "a",
        "text": "Быть заметным и влиять на происходящее",
        "weights": {
          "extraversion": 10,
          "dominance": 13,
          "agreeableness": -2
        }
      },
      {
        "code": "b",
        "text": "Быть свободным и не зависеть от чужих ожиданий",
        "weights": {
          "openness": 8,
          "dominance": 6,
          "agreeableness": -5,
          "adaptability": 6
        }
      },
      {
        "code": "c",
        "text": "Быть полезным и надёжным для своих",
        "weights": {
          "agreeableness": 12,
          "self_control": 9,
          "dominance": -3
        }
      },
      {
        "code": "d",
        "text": "Быть в тишине и понимать больше, чем говоришь",
        "weights": {
          "extraversion": -13,
          "openness": 9,
          "self_control": 6,
          "emotional_stability": 4
        }
      }
    ]
  },
  {
    "code": "q15_new_ideas",
    "question": "Как ты обычно относишься к новым идеям?",
    "answers": [
      {
        "code": "a",
        "text": "С интересом: сначала хочу понять, что в этом есть",
        "weights": {
          "openness": 13,
          "adaptability": 5,
          "agreeableness": 3
        }
      },
      {
        "code": "b",
        "text": "Осторожно: идея должна выдержать проверку",
        "weights": {
          "self_control": 11,
          "openness": 4,
          "emotional_stability": 5
        }
      },
      {
        "code": "c",
        "text": "Если идея ломает старые правила — уже интересно",
        "weights": {
          "openness": 14,
          "adaptability": 8,
          "self_control": -6,
          "dominance": 3
        }
      },
      {
        "code": "d",
        "text": "Чаще скептически: большинство идей — шум",
        "weights": {
          "openness": -8,
          "self_control": 7,
          "agreeableness": -5,
          "emotional_stability": 3
        }
      }
    ]
  },
  {
    "code": "q16_underestimated",
    "question": "Что ты делаешь, если тебя недооценили?",
    "answers": [
      {
        "code": "a",
        "text": "Доказываю результатом",
        "weights": {
          "self_control": 12,
          "dominance": 6,
          "emotional_stability": 7
        }
      },
      {
        "code": "b",
        "text": "Спокойно жду момента, чтобы показать себя",
        "weights": {
          "emotional_stability": 10,
          "adaptability": 7,
          "extraversion": -5,
          "self_control": 4
        }
      },
      {
        "code": "c",
        "text": "Сразу даю понять, что они ошиблись",
        "weights": {
          "dominance": 13,
          "agreeableness": -7,
          "extraversion": 5,
          "emotional_stability": 3
        }
      },
      {
        "code": "d",
        "text": "Внешне молчу, но внутри это задевает",
        "weights": {
          "emotional_stability": -13,
          "extraversion": -7,
          "dominance": -4,
          "agreeableness": 2
        }
      }
    ]
  },
  {
    "code": "q17_hard_to_tolerate",
    "question": "Что тебе сложнее всего терпеть?",
    "answers": [
      {
        "code": "a",
        "text": "Когда меня ограничивают без причины",
        "weights": {
          "openness": 8,
          "dominance": 8,
          "adaptability": 6,
          "agreeableness": -5
        }
      },
      {
        "code": "b",
        "text": "Когда люди ведут себя грубо и бесчувственно",
        "weights": {
          "agreeableness": 13,
          "emotional_stability": -4,
          "dominance": -3
        }
      },
      {
        "code": "c",
        "text": "Когда всё непредсказуемо и нет опоры",
        "weights": {
          "self_control": 10,
          "emotional_stability": -9,
          "adaptability": -7
        }
      },
      {
        "code": "d",
        "text": "Когда вокруг скука и однообразие",
        "weights": {
          "openness": 13,
          "extraversion": 5,
          "self_control": -5,
          "adaptability": 5
        }
      }
    ]
  },
  {
    "code": "q18_strength",
    "question": "Как ты проявляешь силу?",
    "answers": [
      {
        "code": "a",
        "text": "Защищаю своих и держу удар",
        "weights": {
          "dominance": 8,
          "agreeableness": 8,
          "emotional_stability": 11
        }
      },
      {
        "code": "b",
        "text": "Иду к цели, даже если никто не поддерживает",
        "weights": {
          "dominance": 12,
          "self_control": 9,
          "extraversion": -3,
          "agreeableness": -2
        }
      },
      {
        "code": "c",
        "text": "Не показываю лишнего и действую точно",
        "weights": {
          "self_control": 11,
          "emotional_stability": 11,
          "extraversion": -8,
          "adaptability": 6
        }
      },
      {
        "code": "d",
        "text": "Умею смягчить ситуацию, когда все уже на взводе",
        "weights": {
          "agreeableness": 12,
          "emotional_stability": 8,
          "dominance": -5,
          "adaptability": 3
        }
      }
    ]
  },
  {
    "code": "q19_adventure",
    "question": "Тебе предлагают внезапную авантюру. Что отвечаешь?",
    "answers": [
      {
        "code": "a",
        "text": "Погнали, потом разберёмся",
        "weights": {
          "openness": 12,
          "extraversion": 8,
          "self_control": -11,
          "adaptability": 8
        }
      },
      {
        "code": "b",
        "text": "Сначала скажите, куда, зачем и какие риски",
        "weights": {
          "self_control": 12,
          "emotional_stability": 5,
          "openness": -2,
          "adaptability": -2
        }
      },
      {
        "code": "c",
        "text": "Если компания хорошая — можно",
        "weights": {
          "extraversion": 9,
          "agreeableness": 8,
          "adaptability": 4,
          "openness": 3
        }
      },
      {
        "code": "d",
        "text": "Скорее нет, мне нормально без внезапных приключений",
        "weights": {
          "openness": -9,
          "extraversion": -8,
          "self_control": 7,
          "emotional_stability": 5
        }
      }
    ]
  },
  {
    "code": "q20_self_description",
    "question": "Какое описание тебе ближе?",
    "answers": [
      {
        "code": "a",
        "text": "Я чувствую людей и стараюсь не делать больно без причины",
        "weights": {
          "agreeableness": 14,
          "dominance": -5,
          "emotional_stability": -2
        }
      },
      {
        "code": "b",
        "text": "Я вижу систему, детали и последствия",
        "weights": {
          "openness": 9,
          "self_control": 12,
          "extraversion": -4
        }
      },
      {
        "code": "c",
        "text": "Я умею выживать, подстраиваться и находить проход",
        "weights": {
          "adaptability": 14,
          "openness": 6,
          "agreeableness": -4
        }
      },
      {
        "code": "d",
        "text": "Я не люблю слабую позицию и предпочитаю контролировать ситуацию",
        "weights": {
          "dominance": 14,
          "emotional_stability": 7,
          "agreeableness": -8,
          "self_control": 4
        }
      }
    ]
  },
  {
    "code": "q21_loneliness",
    "question": "Как ты обычно относишься к одиночеству?",
    "answers": [
      {
        "code": "a",
        "text": "Мне одному нормально, я быстро восстанавливаюсь в тишине",
        "weights": {
          "extraversion": -12,
          "emotional_stability": 8,
          "self_control": 5,
          "openness": 4
        }
      },
      {
        "code": "b",
        "text": "Немного одиночества полезно, но без людей я быстро тухну",
        "weights": {
          "extraversion": 9,
          "agreeableness": 6,
          "emotional_stability": -2
        }
      },
      {
        "code": "c",
        "text": "Смотря какое одиночество: если это свобода — отлично",
        "weights": {
          "openness": 8,
          "adaptability": 7,
          "dominance": 4,
          "agreeableness": -3
        }
      },
      {
        "code": "d",
        "text": "Долго быть одному тяжело, начинаю накручивать себя",
        "weights": {
          "extraversion": 5,
          "agreeableness": 5,
          "emotional_stability": -12,
          "dominance": -4
        }
      }
    ]
  },
  {
    "code": "q22_trust",
    "question": "Как ты начинаешь доверять людям?",
    "answers": [
      {
        "code": "a",
        "text": "Быстро чувствую, мой человек или нет",
        "weights": {
          "agreeableness": 7,
          "openness": 6,
          "adaptability": 5,
          "self_control": -3
        }
      },
      {
        "code": "b",
        "text": "Смотрю на поступки и время, словам не спешу верить",
        "weights": {
          "self_control": 11,
          "emotional_stability": 6,
          "extraversion": -5,
          "adaptability": 4
        }
      },
      {
        "code": "c",
        "text": "Доверяю легко, если человек открытый и тёплый",
        "weights": {
          "agreeableness": 12,
          "extraversion": 7,
          "dominance": -4,
          "emotional_stability": -2
        }
      },
      {
        "code": "d",
        "text": "Почти никому не доверяю полностью",
        "weights": {
          "agreeableness": -10,
          "emotional_stability": 5,
          "self_control": 7,
          "extraversion": -8
        }
      }
    ]
  },
  {
    "code": "q23_attention",
    "question": "Что ты чувствуешь, когда оказываешься в центре внимания?",
    "answers": [
      {
        "code": "a",
        "text": "Заряжаюсь, это моя территория",
        "weights": {
          "extraversion": 14,
          "dominance": 10,
          "emotional_stability": 6
        }
      },
      {
        "code": "b",
        "text": "Если есть понятная роль — справляюсь спокойно",
        "weights": {
          "self_control": 9,
          "emotional_stability": 8,
          "extraversion": 3
        }
      },
      {
        "code": "c",
        "text": "Мне неловко, но могу выдержать",
        "weights": {
          "extraversion": -8,
          "emotional_stability": -5,
          "self_control": 5,
          "agreeableness": 3
        }
      },
      {
        "code": "d",
        "text": "Скорее хочу исчезнуть из кадра",
        "weights": {
          "extraversion": -14,
          "emotional_stability": -7,
          "dominance": -6,
          "openness": 2
        }
      }
    ]
  },
  {
    "code": "q24_care",
    "question": "Как ты заботишься о близких?",
    "answers": [
      {
        "code": "a",
        "text": "Слушаю, поддерживаю, стараюсь быть рядом",
        "weights": {
          "agreeableness": 14,
          "extraversion": 5,
          "emotional_stability": 3
        }
      },
      {
        "code": "b",
        "text": "Решаю конкретные проблемы: привезти, починить, закрыть вопрос",
        "weights": {
          "self_control": 10,
          "agreeableness": 8,
          "dominance": 5
        }
      },
      {
        "code": "c",
        "text": "Не лезу лишний раз, но если нужно — включаюсь",
        "weights": {
          "extraversion": -7,
          "emotional_stability": 6,
          "adaptability": 5,
          "agreeableness": 4
        }
      },
      {
        "code": "d",
        "text": "Иногда хочу помочь, но не всегда понимаю как",
        "weights": {
          "agreeableness": 7,
          "emotional_stability": -8,
          "self_control": -3,
          "dominance": -4
        }
      }
    ]
  },
  {
    "code": "q25_pressure",
    "question": "На тебя давят и требуют срочно решить вопрос. Что происходит?",
    "answers": [
      {
        "code": "a",
        "text": "Собираюсь и действую, давление только фокусирует",
        "weights": {
          "emotional_stability": 13,
          "dominance": 9,
          "self_control": 8
        }
      },
      {
        "code": "b",
        "text": "Сначала торможу процесс, чтобы не наломать дров",
        "weights": {
          "self_control": 12,
          "emotional_stability": 5,
          "dominance": -3,
          "adaptability": -2
        }
      },
      {
        "code": "c",
        "text": "Начинаю искать нестандартный обходной путь",
        "weights": {
          "adaptability": 13,
          "openness": 8,
          "self_control": -4,
          "emotional_stability": 3
        }
      },
      {
        "code": "d",
        "text": "Меня клинит или злит, потом уже разбираюсь",
        "weights": {
          "emotional_stability": -14,
          "dominance": 4,
          "self_control": -6,
          "agreeableness": -4
        }
      }
    ]
  },
  {
    "code": "q26_status",
    "question": "Как ты относишься к статусу и признанию?",
    "answers": [
      {
        "code": "a",
        "text": "Признание важно: если я силён, это должно быть видно",
        "weights": {
          "dominance": 13,
          "extraversion": 9,
          "agreeableness": -3
        }
      },
      {
        "code": "b",
        "text": "Приятно, но важнее реальный результат",
        "weights": {
          "self_control": 10,
          "emotional_stability": 7,
          "dominance": 5
        }
      },
      {
        "code": "c",
        "text": "Мне важнее свобода, чем чужая оценка",
        "weights": {
          "openness": 9,
          "adaptability": 6,
          "extraversion": -4,
          "dominance": 4
        }
      },
      {
        "code": "d",
        "text": "Не люблю соревнование за статус, мне ближе спокойные связи",
        "weights": {
          "agreeableness": 11,
          "dominance": -10,
          "emotional_stability": 5,
          "extraversion": -3
        }
      }
    ]
  },
  {
    "code": "q27_planning",
    "question": "Как у тебя с планами и режимом?",
    "answers": [
      {
        "code": "a",
        "text": "План нужен, без него всё расползается",
        "weights": {
          "self_control": 14,
          "emotional_stability": 5,
          "openness": -4,
          "adaptability": -5
        }
      },
      {
        "code": "b",
        "text": "План есть, но я спокойно меняю его по ситуации",
        "weights": {
          "self_control": 7,
          "adaptability": 9,
          "emotional_stability": 7,
          "openness": 4
        }
      },
      {
        "code": "c",
        "text": "Живу гибко: жёсткий режим быстро начинает душить",
        "weights": {
          "adaptability": 11,
          "openness": 9,
          "self_control": -9,
          "dominance": 3
        }
      },
      {
        "code": "d",
        "text": "Планы часто ломаются, и я сам не всегда понимаю почему",
        "weights": {
          "self_control": -13,
          "emotional_stability": -7,
          "adaptability": 4,
          "openness": 3
        }
      }
    ]
  },
  {
    "code": "q28_group_loyalty",
    "question": "Если твоя компания или команда попала в сложную ситуацию, ты…",
    "answers": [
      {
        "code": "a",
        "text": "Остаюсь рядом и помогаю, своих не бросают",
        "weights": {
          "agreeableness": 13,
          "emotional_stability": 8,
          "self_control": 6,
          "dominance": 3
        }
      },
      {
        "code": "b",
        "text": "Беру управление, если остальные растерялись",
        "weights": {
          "dominance": 13,
          "emotional_stability": 9,
          "self_control": 7,
          "extraversion": 4
        }
      },
      {
        "code": "c",
        "text": "Ищу нестандартный выход, даже если он не всем нравится",
        "weights": {
          "adaptability": 12,
          "openness": 9,
          "agreeableness": -4,
          "dominance": 5
        }
      },
      {
        "code": "d",
        "text": "Сначала оцениваю, не тащат ли меня в чужой бардак",
        "weights": {
          "self_control": 9,
          "agreeableness": -6,
          "extraversion": -5,
          "adaptability": 5,
          "emotional_stability": 4
        }
      }
    ]
  }
]
```

## 6. PHP-структура

```php
$questions = [
    [
        'code' => 'q01_unknown_company',
        'question' => 'Ты попадаешь в незнакомую компанию. Что делаешь?',
        'answers' => [
            [
                'code' => 'a',
                'text' => 'Быстро нахожу, с кем поговорить, и вливаюсь в движ',
                'weights' => [
                    'extraversion' => 12,
                    'agreeableness' => 6,
                    'adaptability' => 5,
                ],
            ],
            [
                'code' => 'b',
                'text' => 'Сначала наблюдаю, кто тут кто, а потом выбираю момент',
                'weights' => [
                    'extraversion' => -6,
                    'openness' => 6,
                    'self_control' => 5,
                    'adaptability' => 9,
                ],
            ],
        ],
    ],
];
```

## 7. Проверка покрытия шкал

Перед публикацией набора вопросов нужно проверить, что выбранные вопросы не перекосили шкалы.

```php
function analyzeQuestionsCoverage(array $questions): array
{
    $coverage = [];

    foreach ($questions as $question) {
        foreach ($question['answers'] as $answer) {
            foreach ($answer['weights'] as $trait => $weight) {
                if (!isset($coverage[$trait])) {
                    $coverage[$trait] = [
                        'mentions' => 0,
                        'positive' => 0,
                        'negative' => 0,
                        'sum' => 0,
                    ];
                }

                $coverage[$trait]['mentions']++;
                $coverage[$trait]['sum'] += $weight;

                if ($weight > 0) {
                    $coverage[$trait]['positive']++;
                } elseif ($weight < 0) {
                    $coverage[$trait]['negative']++;
                }
            }
        }
    }

    return $coverage;
}
```

Для этого набора целевой смысл такой:

```text
extraversion           много прямых социальных вопросов + отдельная проверка центра внимания
openness               вопросы про новые идеи, риск, свободу, нестандартные решения
self_control           вопросы про порядок, планы, решения, ответственность
agreeableness          усилен отдельными вопросами про заботу, доверие, поддержку, команду
emotional_stability    усилен отдельными вопросами про давление, внимание, одиночество, стресс
dominance              вопросы про лидерство, конфликт, статус, управление ситуацией
adaptability           вопросы про хаос, обходные пути, изменение планов, импровизацию
```
