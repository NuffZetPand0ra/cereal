# Cereal

A Symfony 7 application for managing cereal products and manufacturers, with:

- Web UI for CRUD operations
- JSON API for products
- AI-assisted cereal draft generation (name, nutrients, and image suggestion)
- Docker-based local development with FrankenPHP + Postgres

## Tech Stack

- PHP 8.2+
- Symfony 7.1
- Doctrine ORM + Doctrine Migrations
- Twig
- FrankenPHP (Caddy)
- PostgreSQL 16
- PHPUnit 9

## Requirements

- Docker + Docker Compose
- A browser for the web UI

## Quick Start

1. Clone and enter the project:

```sh
git clone https://github.com/NuffZetPand0ra/cereal.git
cd cereal
```

2. Create a local env file required by compose override:

```sh
touch .env.local
```

3. Start the stack:

```sh
docker compose up --pull always -d --wait
```

4. Run migrations:

```sh
docker compose exec -T php php bin/console doctrine:migrations:migrate --no-interaction
```

5. Optional: load fixtures:

```sh
docker compose exec -T php php bin/console doctrine:fixtures:load --no-interaction
```

6. Open the app:

- Products: http://localhost/products
- Manufacturers: http://localhost/manufacturers

## AI Draft Assistant

The product edit/create page includes an AI draft button that can generate:

- Nutrition values
- Product image suggestion URL
- Suggested product name when name is not provided

Endpoint:

- POST /api/ai/cereal-draft

Request body:

```json
{
  "name": "",
  "idea": "Chocolate berry cereal for kids"
}
```

Notes:

- `idea` is required
- `name` is optional
- If `name` is omitted, the response may include `suggestedName`

### OpenAI Integration

Set `OPENAI_API_KEY` in `.env.local` to enable LLM-based suggestions.

Without an API key, the app falls back to deterministic heuristic suggestions.

Example `.env.local`:

```dotenv
OPENAI_API_KEY=your_key_here
```

## API Overview

- GET /api/products
- GET /api/product/{id}
- POST /api/product
- PUT /api/product/{id}
- PATCH /api/product/{id}
- DELETE /api/product/{id}
- POST /api/ai/cereal-draft

## Tests

Run all tests inside the container:

```sh
docker compose exec -T php php ./vendor/bin/phpunit
```

Run focused tests:

```sh
docker compose exec -T php php ./vendor/bin/phpunit tests/Service/CerealIdeaAssistantTest.php
```

## CI

GitHub Actions runs on pull requests and includes:

- Docker image build
- Service startup and HTTP reachability check
- PHPUnit execution
- Automatic container diagnostics dump on failure

## Useful Commands

Start:

```sh
docker compose up --pull always -d --wait
```

Stop:

```sh
docker compose down
```

Logs:

```sh
docker compose logs -f
```

## License

MIT