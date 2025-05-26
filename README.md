# Movie Search API

Laravel-приложение для поиска фильмов с использованием Elasticsearch. Приложение интегрируется с TMDb API для импорта данных о фильмах и жанрах.

## Возможности

- 🔍 Полнотекстовый поиск фильмов по названию, описанию и жанрам
- 📊 Взвешенный поиск с настраиваемыми коэффициентами релевантности
- 🎬 Интеграция с TMDb API для импорта популярных фильмов
- 🏷️ Поддержка поиска по связанным жанрам
- ⚡ Автоматическая индексация в Elasticsearch при создании/обновлении записей
- 📄 Пагинация результатов поиска

## Требования

- PHP 8.1+
- Laravel 10+
- Elasticsearch 7.x/8.x
- Composer

## Установка

### 1. Клонирование и установка зависимостей

```bash
git clone https://github.com/MelomiLight/elasticsearch-laravel.git
cd movie-search-api
composer install
```

### 2. Настройка окружения

Скопируйте файл окружения и настройте переменные:

```bash
cp .env.example .env
php artisan key:generate
```

### 3. Настройка переменных окружения

Добавьте в `.env` файл следующие переменные:

```env
# Elasticsearch
ELASTICSEARCH_HOST=http://localhost:9200

# TMDb API
TMDB_API_KEY=your_tmdb_api_key_here

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=movie_search
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 4. Настройка базы данных

```bash
php artisan migrate
```

### 5. Запуск Elasticsearch

Убедитесь, что Elasticsearch запущен и доступен по адресу, указанному в `ELASTICSEARCH_HOST`.

## Использование

### Импорт данных

#### Импорт жанров из TMDb:

```bash
php artisan genres:import
```

#### Импорт популярных фильмов из TMDb:

```bash
php artisan movies:import-popular
```

Эта команда импортирует первые 10 страниц популярных фильмов (~200 фильмов).

### API Endpoints

#### Поиск фильмов

```http
GET /api/V1/movies/search
```

**Параметры запроса:**

- `query` (string, обязательный) - поисковый запрос
- `page` (integer, опциональный) - номер страницы (по умолчанию: 1)
- `perPage` (integer, опциональный) - количество результатов на странице (по умолчанию: 10)

**Пример запроса:**

```bash
curl "http://localhost:8000/api/V1/movies/search?query=spider&page=1&perPage=5"
```

**Пример ответа:**

```json
{
  "data": {
    "movies": [
      {
        "id": 1,
        "tmdb_id": 557,
        "title": "Spider-Man",
        "overview": "After being bitten by a genetically altered spider...",
        "release_date": "2002-05-01",
        "poster_path": "/gh4cZbhZxyTbgxQPxD0dOudNPTn.jpg",
        "vote_average": 7.2,
        "vote_count": 13507,
        "genres": [
          {
            "id": 1,
            "name": "Action"
          },
          {
            "id": 2,
            "name": "Fantasy"
          }
        ]
      }
    ]
  }
}
```

## Архитектура поиска

### Поисковые поля и веса

Поиск выполняется по следующим полям с указанными весами:

- `title` (вес: 3) - название фильма
- `overview` (вес: 1) - описание фильма
- `genres.name` (вес: 2) - названия жанров

### Автоматическая индексация

Приложение использует trait `ESearchable`, который автоматически:

- Индексирует модели в Elasticsearch при сохранении
- Удаляет документы из индекса при удалении моделей
- Использует Laravel Jobs для асинхронной обработки

### Структура индекса

Каждый фильм индексируется в Elasticsearch со следующей структурой:

```json
{
  "title": "Spider-Man",
  "overview": "After being bitten by a genetically altered spider...",
  "genres": [
    {"name": "Action"},
    {"name": "Fantasy"}
  ]
}
```

## Модели

### Movie

Основная модель фильма с полями:

- `tmdb_id` - ID фильма в TMDb
- `title` - название
- `overview` - описание
- `release_date` - дата выхода
- `poster_path` - путь к постеру
- `vote_average` - средняя оценка
- `vote_count` - количество голосов

### Genre

Модель жанра:

- `tmdb_id` - ID жанра в TMDb
- `name` - название жанра

## Конфигурация

### Elasticsearch

Настройки Elasticsearch находятся в `config/services.php`:

```php
'elasticsearch' => [
    'hosts' => [
        env('ELASTICSEARCH_HOST', 'http://localhost:9200')
    ],
],
```

### Настройка поиска

Для настройки поисковых полей и их весов отредактируйте свойства в модели `Movie`:

```php
protected static array $esSearchableFields = [
    'title' => 3,        // Вес для названия
    'overview' => 1,     // Вес для описания
];

protected static array $esSearchableRelations = [
    'genres' => [
        'name' => 2,     // Вес для названий жанров
    ],
];
```

## Разработка

### Запуск очередей

Для обработки заданий индексации запустите worker:

```bash
php artisan queue:work
```

### Тестирование

```bash
php artisan test
```

## Устранение неполадок

### Elasticsearch недоступен

Убедитесь, что:

1. Elasticsearch запущен и доступен
2. Правильно указан `ELASTICSEARCH_HOST` в `.env`
3. Нет ограничений файрвола

### Ошибки индексации

Проверьте логи Laravel:

```bash
tail -f storage/logs/laravel.log
```

### Пересоздание индекса

Если нужно пересоздать индекс Elasticsearch:

```bash
# Удалить индекс (замените 'movies' на имя вашего индекса)
curl -X DELETE "localhost:9200/movies"

# Переиндексировать все фильмы
php artisan tinker
>>> App\Models\Movie::all()->each(fn($movie) => dispatch(new App\Jobs\IndexModelToElasticsearch($movie)));
```
