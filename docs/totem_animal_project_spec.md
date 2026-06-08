# VK Mini App «Тотемное животное» — проектная спецификация

## 1. Назначение проекта

«Тотемное животное» — развлекательный VK Mini App, в котором пользователь проходит короткий тест, отвечает на вопросы о поведении, привычках и реакциях в разных ситуациях, после чего получает результат: тотемное животное, изображение и описание характера.

Проект не является психодиагностикой, клиническим опросником или научной классификацией личности. Это игровая типология, построенная на шкалах характера. Методологический ориентир — логика Big Five / Five-Factor Model: экстраверсия, открытость опыту, самоконтроль / добросовестность, доброжелательность и эмоциональная устойчивость. Дополнительно используются две продуктовые игровые шкалы: доминантность и адаптивность.

Цель MVP — сделать лёгкий, быстрый и понятный миниапп с повторным восстановлением результата. Если пользователь уже проходил тест, при новом входе он должен увидеть свой ранее рассчитанный результат, а не проходить тест заново.

## 2. Ключевые продуктовые требования

Пользовательский сценарий:

1. Пользователь открывает VK Mini App.
2. Фронтенд проверяет VK Storage на наличие внутреннего идентификатора клиента.
3. Если идентификатора нет, фронтенд обращается к backend, backend создаёт анонимного клиента и возвращает `public_id`.
4. Фронтенд сохраняет `public_id` в VK Storage.
5. Backend проверяет, есть ли для `public_id` уже сохранённый результат.
6. Если результат есть, backend возвращает сохранённый snapshot результата.
7. Если результата нет, пользователь проходит тест.
8. После завершения теста backend рассчитывает профиль, подбирает животное и сохраняет неизменяемый результат.
9. При последующих входах пользователь видит тот же результат.

Важно: данные пользователей VK не хранятся. Не сохраняются `vk_user_id`, имя, фамилия, аватар, screen name или raw VK payload. Пользователь определяется только внутренним анонимным идентификатором, который хранится на фронтенде в VK Storage.

## 3. Технологический стек

Рекомендуемый backend-стек:

```text
PHP 8.4+
Slim 4
MySQL 8
Composer
PHP-DI
Slim PSR-7
Illuminate Database или прямой PDO-слой
Phinx migrations / seeders
vlucas/phpdotenv
Monolog
Symfony Validator
PHPStan
PHP CS Fixer
PHPUnit
```

Для данного проекта выбран Slim 4, потому что backend небольшой: API, тест, расчёт результата, сохранение сессии, выдача результата и минимальная административная часть в будущем. Laravel или Yii2/Yii3 для MVP избыточны. Slim 4 даёт маршрутизацию, middleware и полный контроль над архитектурой без тяжёлой фреймворочной надстройки.

Namespace проекта:

```text
App
```

## 4. Архитектура backend

Рекомендуемая структура проекта:

```text
app/
  Actions/
    Api/
      HealthAction.php
      ClientStartAction.php
      StartTestAction.php
      FinishTestAction.php
      GetResultAction.php
    Admin/
      DashboardAction.php

  Domain/
    Totem/
      Entity/
        Animal.php
        Question.php
        Answer.php
        TestSession.php
        TestResult.php
      Service/
        TotemCalculator.php
        AnimalMatcher.php
        ResultBuilder.php

  Infrastructure/
    Database/
      Database.php
    Logging/
      LoggerFactory.php
    Storage/
      ImagePathResolver.php

  Middleware/
    CorsMiddleware.php
    ErrorHandlerMiddleware.php
    VkSignatureMiddleware.php

  Support/
    Env.php
    JsonResponse.php

bootstrap/
  app.php
  container.php
  database.php
  middleware.php
  routes.php

config/
  app.php
  database.php
  logging.php
  phinx.php

database/
  migrations/
  seeds/
    data/
      animals.php
      questions.php
    AnimalsSeeder.php
    QuestionsSeeder.php

public/
  index.php

runtime/
  logs/
  cache/

tests/
  Unit/
  Feature/
```

Контроллеры / Actions должны быть тонкими. Основная логика должна находиться в доменных сервисах.

Основные сервисы:

```text
TotemCalculator
- принимает выбранные ответы
- считает пользовательский профиль по 7 шкалам
- нормализует результат

AnimalMatcher
- сравнивает профиль пользователя с профилями животных
- выбирает ближайшее животное

ResultBuilder
- собирает snapshot результата
- сохраняет выбранное животное, описание, изображение, профиль пользователя и summary ответов
```

## 5. Шкалы характера

Используются 7 шкал:

| Шкала | Смысл |
|---|---|
| `extraversion` | социальная энергия, контактность, тяга к людям |
| `openness` | любопытство, нестандартность, тяга к новому |
| `self_control` | дисциплина, порядок, планирование, устойчивость к хаосу |
| `agreeableness` | мягкость, эмпатия, готовность сотрудничать |
| `emotional_stability` | спокойствие, стрессоустойчивость, способность держать удар |
| `dominance` | напор, лидерство, контроль ситуации, готовность давить |
| `adaptability` | гибкость, хитрость, ситуативность, умение выкручиваться |

У животных каждая характеристика хранится в отдельной колонке таблицы `animals`, а не в JSON. Это сделано для быстрого поиска, отбора и аналитики.

У ответов веса хранятся в JSON, потому что это конфигурационная структура ответа, а не поле для прямого поиска.

## 6. Животные

Используется 40 животных. Каждое животное имеет:

```text
code
name
title
description
image_path
extraversion
openness
self_control
agreeableness
emotional_stability
dominance
adaptability
is_active
sort_order
```

Список животных:

| code | Животное |
|---|---|
| `lion` | Лев |
| `wolf` | Волк |
| `fox` | Лиса |
| `bear` | Медведь |
| `eagle` | Орёл |
| `cat` | Кот |
| `dog` | Пёс |
| `owl` | Сова |
| `raven` | Ворон |
| `dolphin` | Дельфин |
| `raccoon` | Енот |
| `tiger` | Тигр |
| `panda` | Панда |
| `otter` | Выдра |
| `turtle` | Черепаха |
| `deer` | Олень |
| `bull` | Бык |
| `monkey` | Обезьяна |
| `snake` | Змея |
| `horse` | Лошадь |
| `hedgehog` | Ёж |
| `shark` | Акула |
| `chameleon` | Хамелеон |
| `capybara` | Капибара |
| `lemur` | Лемур |
| `lynx` | Рысь |
| `swan` | Лебедь |
| `ant` | Муравей |
| `spider` | Паук |
| `elephant` | Слон |
| `hyena` | Гиена |
| `parrot` | Попугай |
| `octopus` | Осьминог |
| `badger` | Барсук |
| `rabbit` | Кролик |
| `goat` | Коза |
| `beaver` | Бобр |
| `peacock` | Павлин |
| `frog` | Лягушка |
| `moose` | Лось |

Изображение животного хранится по соглашению:

```text
/images/animals/{code}.webp
```

Поле `description` — пользовательское описание результата. Оно должно отражать и архетип животного, и характер пользователя, который получил этот результат. Описание должно быть живым, понятным, немного ироничным, без мистического пафоса и без псевдонаучных заявлений.

## 7. Вопросы

В наборе используется 28 вопросов. Для MVP можно показывать все 28 вопросов, чтобы результат был более устойчивым. Позже можно показывать 20 вопросов из 28 с балансировкой покрытия шкал.

Каждый вопрос имеет:

```text
code
text
answers[]
```

Каждый ответ имеет:

```text
code
text
weights
```

Пример структуры в `database/seeds/data/questions.php`:

```php
[
    'code' => 'q01_unknown_company',
    'text' => 'Ты попадаешь в незнакомую компанию. Что делаешь?',
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
    ],
]
```

Веса ответов находятся в диапазоне от `-14` до `14`.

| Диапазон | Смысл |
|---|---|
| `-14..-10` | сильное снижение признака |
| `-9..-5` | умеренное снижение признака |
| `-4..4` | слабое влияние |
| `5..9` | умеренное усиление признака |
| `10..14` | сильное усиление признака |

## 8. Алгоритм расчёта профиля

Начальное значение каждой шкалы:

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
```

Рекомендуемый алгоритм — считать среднее смещение по каждой шкале, а не простую сумму всех весов. Это предотвращает слишком быстрый уход значений в 0 или 100.

```php
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

Коэффициент `3.2` подобран для развлекательного теста: профиль заметно двигается, но не становится слишком экстремальным.

## 9. Алгоритм выбора животного

Профиль пользователя сравнивается с профилем каждого животного по 7 шкалам. Базовый алгоритм — евклидово расстояние:

```php
function distance(array $userTraits, array $animalTraits): float
{
    $sum = 0;

    foreach ($userTraits as $trait => $userValue) {
        $animalValue = $animalTraits[$trait] ?? 50;
        $sum += ($userValue - $animalValue) ** 2;
    }

    return sqrt($sum);
}
```

Побеждает животное с минимальным расстоянием.

## 10. База данных

### 10.1. `app_clients`

Хранит анонимных клиентов приложения. VK-данные здесь не хранятся.

```text
id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
public_id CHAR(36) NOT NULL UNIQUE
first_seen_at TIMESTAMP NULL
last_seen_at TIMESTAMP NULL
created_at TIMESTAMP NULL
updated_at TIMESTAMP NULL
```

Назначение:

- создать внутренний client id при первом запуске;
- вернуть `public_id` на фронтенд;
- фронтенд сохраняет `public_id` в VK Storage;
- при повторном входе фронтенд передаёт `public_id` обратно.

### 10.2. `animals`

Хранит животных и их профили.

```text
id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
code VARCHAR(80) NOT NULL UNIQUE
name VARCHAR(120) NOT NULL
title VARCHAR(255) NULL
description TEXT NOT NULL
image_path VARCHAR(500) NOT NULL
extraversion TINYINT UNSIGNED NOT NULL
openness TINYINT UNSIGNED NOT NULL
self_control TINYINT UNSIGNED NOT NULL
agreeableness TINYINT UNSIGNED NOT NULL
emotional_stability TINYINT UNSIGNED NOT NULL
dominance TINYINT UNSIGNED NOT NULL
adaptability TINYINT UNSIGNED NOT NULL
is_active BOOLEAN NOT NULL DEFAULT TRUE
sort_order INT NOT NULL DEFAULT 0
created_at TIMESTAMP NULL
updated_at TIMESTAMP NULL
deleted_at TIMESTAMP NULL
```

### 10.3. `questions`

Хранит вопросы теста.

```text
id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
code VARCHAR(80) NOT NULL UNIQUE
text TEXT NOT NULL
is_active BOOLEAN NOT NULL DEFAULT TRUE
sort_order INT NOT NULL DEFAULT 0
created_at TIMESTAMP NULL
updated_at TIMESTAMP NULL
deleted_at TIMESTAMP NULL
```

### 10.4. `answers`

Хранит варианты ответов.

```text
id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
question_id BIGINT UNSIGNED NOT NULL
code VARCHAR(80) NOT NULL
text TEXT NOT NULL
weights JSON NOT NULL
sort_order INT NOT NULL DEFAULT 0
is_active BOOLEAN NOT NULL DEFAULT TRUE
created_at TIMESTAMP NULL
updated_at TIMESTAMP NULL
deleted_at TIMESTAMP NULL
```

Ограничения:

```text
FOREIGN KEY question_id REFERENCES questions.id
ON DELETE RESTRICT
ON UPDATE CASCADE

UNIQUE (question_id, code)
```

### 10.5. `test_sessions`

Хранит прохождения теста.

```text
id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
public_id CHAR(36) NOT NULL UNIQUE
client_id BIGINT UNSIGNED NULL
public_id CHAR(36) NULL
status ENUM('started', 'completed', 'abandoned') NOT NULL DEFAULT 'started'
questions_count SMALLINT UNSIGNED NOT NULL DEFAULT 0
answers_count SMALLINT UNSIGNED NOT NULL DEFAULT 0
started_at TIMESTAMP NULL
completed_at TIMESTAMP NULL
last_activity_at TIMESTAMP NULL
client_ip_hash CHAR(64) NULL
user_agent_hash CHAR(64) NULL
created_at TIMESTAMP NULL
updated_at TIMESTAMP NULL
```

Не хранить raw IP и raw User-Agent. Если нужно хранить технические признаки — только hash.

### 10.6. `test_session_answers`

Хранит выбранные ответы в рамках сессии.

```text
id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
test_session_id BIGINT UNSIGNED NOT NULL
question_id BIGINT UNSIGNED NOT NULL
answer_id BIGINT UNSIGNED NOT NULL
question_code VARCHAR(80) NOT NULL
answer_code VARCHAR(80) NOT NULL
question_text TEXT NOT NULL
answer_text TEXT NOT NULL
weights_snapshot JSON NOT NULL
answered_at TIMESTAMP NULL
created_at TIMESTAMP NULL
updated_at TIMESTAMP NULL
```

Эта таблица хранит snapshot текста вопроса, текста ответа и весов. Это нужно, чтобы старые результаты оставались воспроизводимыми после редактирования вопросов.

### 10.7. `test_results`

Хранит финальный неизменяемый результат.

```text
id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
public_id CHAR(36) NOT NULL UNIQUE
test_session_id BIGINT UNSIGNED NOT NULL UNIQUE
client_id BIGINT UNSIGNED NULL
public_id CHAR(36) NULL
animal_id BIGINT UNSIGNED NULL
animal_code VARCHAR(80) NOT NULL
animal_name VARCHAR(120) NOT NULL
result_title VARCHAR(255) NOT NULL
result_description TEXT NOT NULL
result_image_path VARCHAR(500) NOT NULL

user_extraversion TINYINT UNSIGNED NOT NULL
user_openness TINYINT UNSIGNED NOT NULL
user_self_control TINYINT UNSIGNED NOT NULL
user_agreeableness TINYINT UNSIGNED NOT NULL
user_emotional_stability TINYINT UNSIGNED NOT NULL
user_dominance TINYINT UNSIGNED NOT NULL
user_adaptability TINYINT UNSIGNED NOT NULL

animal_extraversion TINYINT UNSIGNED NOT NULL
animal_openness TINYINT UNSIGNED NOT NULL
animal_self_control TINYINT UNSIGNED NOT NULL
animal_agreeableness TINYINT UNSIGNED NOT NULL
animal_emotional_stability TINYINT UNSIGNED NOT NULL
animal_dominance TINYINT UNSIGNED NOT NULL
animal_adaptability TINYINT UNSIGNED NOT NULL

score_distance DECIMAL(10,4) NULL

created_at TIMESTAMP NULL
updated_at TIMESTAMP NULL
```

Важно: `test_results` — главный источник для восстановления результата при повторном входе.

Поиск результата:

```sql
SELECT *
FROM test_results
WHERE public_id = :public_id
ORDER BY created_at DESC
LIMIT 1;
```

Если результат найден — вернуть snapshot. Если результата нет — начать новый тест.

### 10.8. `result_events`

Опциональная таблица для лёгкой аналитики.

```text
id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
test_result_id BIGINT UNSIGNED NOT NULL
public_id CHAR(36) NULL
event_type ENUM('view', 'share', 'copy_link') NOT NULL
payload JSON NULL
client_ip_hash CHAR(64) NULL
user_agent_hash CHAR(64) NULL
created_at TIMESTAMP NULL
```

## 11. Seed-файлы

### 11.1. `database/seeds/data/animals.php`

Файл возвращает массив из 40 животных.

Каждая запись:

```php
[
    'code' => 'lion',
    'name' => 'Лев',
    'title' => 'Твоё тотемное животное — Лев',
    'description' => '...',
    'image_path' => '/images/animals/lion.webp',
    'extraversion' => 82,
    'openness' => 42,
    'self_control' => 66,
    'agreeableness' => 38,
    'emotional_stability' => 78,
    'dominance' => 96,
    'adaptability' => 38,
    'is_active' => 1,
    'sort_order' => 10,
]
```

### 11.2. `AnimalsSeeder`

Назначение:

- загрузить `database/seeds/data/animals.php`;
- проверить, что животных ровно 40;
- проверить обязательные поля;
- проверить список кодов;
- проверить значения характеристик;
- вставить или обновить записи в таблице `animals`.

Требования:

```text
idempotent
без truncate
без удаления записей
INSERT ... ON DUPLICATE KEY UPDATE
code как natural key
транзакция
валидация до начала записи
```

### 11.3. `database/seeds/data/questions.php`

Файл возвращает массив из 28 вопросов.

Каждая запись:

```php
[
    'code' => 'q01_unknown_company',
    'text' => 'Ты попадаешь в незнакомую компанию. Что делаешь?',
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
    ],
]
```

### 11.4. `QuestionsSeeder`

Назначение:

- загрузить `database/seeds/data/questions.php`;
- проверить, что вопросов ровно 28;
- проверить, что у каждого вопроса ровно 4 ответа;
- проверить, что коды ответов — `a`, `b`, `c`, `d`;
- проверить допустимые ключи весов;
- проверить диапазон весов `-14..14`;
- вставить или обновить вопросы и ответы.

Требования:

```text
questions.code как natural key
answers: unique(question_id, code)
weights хранить JSON
json_encode(..., JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)
idempotent
без truncate
без удаления записей
транзакция
валидация до начала записи
```

## 12. API

Минимальный набор API endpoints:

```http
GET /api/health
GET /api/me
POST /api/test/start
POST /api/test/finish
GET /api/result/{public_id}
```

### `POST /api/me`

Вход:

```json
{
  "public_id": "uuid-or-null"
}
```

Логика:

1. Если `public_id` передан и найден в `app_clients`, обновить `last_seen_at`.
2. Если `public_id` не передан или не найден, создать нового клиента.
3. Проверить, есть ли последний результат в `test_results`.
4. Вернуть `public_id` и флаг наличия результата.

### `POST /api/test/start`

Создаёт `test_sessions`, возвращает `test_session_public_id` и список активных вопросов с ответами.

### `POST /api/test/finish`

Сохраняет выбранные ответы в `test_session_answers` со snapshot вопроса, ответа и весов. Считает профиль, выбирает животное, сохраняет `test_results`, возвращает результат.

### `GET /api/result/{public_id}`

Возвращает сохранённый snapshot результата.

## 13. Восстановление результата

Главное правило:

```text
Результат пользователя восстанавливается по public_id.
```

Если результат найден, backend возвращает сохранённый snapshot. Если результата нет, backend возвращает состояние, что пользователь должен пройти тест.

Результат не должен зависеть от текущих данных таблицы `animals`, потому что описания, изображения или числовые профили животных могут быть изменены после прохождения пользователем теста.

## 14. Безопасность и приватность

VK-данные пользователей не сохраняются.

Не хранить:

```text
vk_user_id
first_name
last_name
screen_name
photo_url
raw VK profile payload
raw IP
raw User-Agent
```

Допускается хранить:

```text
public_id
client_ip_hash
user_agent_hash
```

`public_id` — анонимный внутренний идентификатор приложения, который сам по себе не раскрывает личность пользователя.
