# News Aggregator API

This is a RESTful API for a news aggregator service that pulls articles from various sources and provides endpoints for a front-end application to consume.

> Ready to execute requests and comprehensive API documentation can be found [here](https://documenter.getpostman.com/view/8213381/2sAXxJhurS).

## Installation

In terms of local development, you can use the following requirements:

-   PHP 8.2 or higher
-   Composer
-   Node.js & NPM (for front-end tooling if needed)
-   X-debug (for code coverage tests)
-   Table plus (or any other database viewer)

Clone the repository:

```bash
git clone https://github.com/Ishoshot/news-aggregator-api.git
```

Navigate to the project directory:

```bash
cd news-aggregator-api
```

Install PHP dependencies:

```bash
composer install
```

Create the `.env` file and generate an application key:

```bash
cp .env.example .env

php artisan key:generate
```

Set up the database:

```bash
touch database/database.sqlite

php artisan migrate
```

Optimize SQLite performance:

```bash
# SQLite on its own can scale, however, I have found a way to optimize the performance even more. So think SQLite on steroids.
# The command below enables WAL journal on the database

php artisan sqlite:wal-enable
```

Serve the application:

```bash
php artisan serve
```

or if you use [Laravel Herd](https://herd.laravel.com/), your application should be available at http://news-aggregator-api.test

> Note: By default, emails are sent to the `mailpit` driver. You can change this in the `.env` file to something else or maybe even `log` driver.

### Important Environment Variables

This application integrates with external services that requires authentication for communication. So the following environment variables MUST be set.

```bash
NEWSAPI_API_KEY=

THEGUARDIAN_API_KEY=

NEWYORKTIMES_API_KEY=
```

### Docker Environment setup

You need to have the following and should be running

-   Docker
-   Docker Compose

Installing [Docker Desktop](https://www.docker.com/products/docker-desktop/) helps you with the above and more.

[Laravel Sail](https://github.com/laravel/sail) is a light-weight command-line interface for interacting with Laravel's default Docker development environment. So we'll be using sail as its integration is seamless.

Clone the repository:

```bash
git clone https://github.com/Ishoshot/news-aggregator-api.git
```

Navigate to the project directory:

```bash
cd news-aggregator-api
```

Install PHP dependencies:

```bash
composer install
```

Create the `.env` file and generate an application key:

```bash
cp .env.example .env
```

> Don't forget the [Important Environment Variables](#important-environment-variables).

You may start sail:

```bash
./vendor/bin/sail up
```

> When you start `sail`, your application would be accessible via [http://localhost:89](http://localhost:89), and the mailpit UI at [http://localhost:8025](http://localhost:8025).

But we're not done. Using another terminal (CLI) instance, you can continue by generating an application key

```bash
sail artisan key:generate
```

Set up the database:

```bash
touch database/database.sqlite

sail artisan migrate
```

Optimize SQLite performance:

```bash
# SQLite on its own can scale, however, I have found a way to optimize the performance even more. So think SQLite on steroids.
# The command below enables WAL journal on the database

sail artisan sqlite:wal-enable
```

To stop sail, you may run

```bash
./vendor/bin/sail down
```

If you are tired of always having to type `./vendor/bin/sail,` you may configure an alias, and to make sure this is always available, you may add this to your shell configuration file in your home directory, such as `~/.zshrc` or `~/.bashrc`, and then restart your shell.

```bash
alias sail='sh $([ -f sail ] && echo sail || echo vendor/bin/sail)'
```

Once, aliasing is done, you can just do

```bash
sail up
```

Executing commands against the application running in the docker environment, you can do something like:

```bash
sail artisan queue:work

sail php --version

sail composer require laravel/sanctum
```

The above shows how to run `artisan`, `php`, and `composer` commands.

## Tooling

This project uses a few tools to ensure the code quality and consistency. [Pest](https://pestphp.com) is the testing framework of choice, and [PHPStan](https://phpstan.org) for static analysis.

Just like a top-notch popular libraries or the laravel framework itself, testing is really important. So this project uses Pest's type coverage at 100%, and the test suite is also at 100% coverage. (No better way to enforce quality)

For code style, this project uses [Laravel Pint](https://laravel.com/docs/11.x/pint) to ensure the code is consistent and follows the Laravel conventions.

Additionally, [Rector](https://getrector.org) is used to ensure the code is up to date with the latest PHP version.

You run these tools individually or collectively using the following commands:

```bash
# Lint the code using Pint
composer lint

composer test:lint
```

```bash
# Refactor the code using Rector
composer refactor

composer test:refactor
```

```bash
# Run PHPStan (Static Analysis)
composer test:types
```

```bash
# Run Architecture tests
composer test:arch
```

```bash
# Run the type coverage tests
composer test:type-coverage
```

```bash
# Run the test suite
composer test:unit
```

```bash
# Run all the tools
composer test
```

To run code coverage tests, you need to have X-debug or similar installed on your machine.

```bash
# Run code coverage test
composer test:code-coverage
```

```bash
# Run all the tools with code coverage test
composer test-code-coverage
```

> Note: To run these commands in docker environment, prefix the command with `sail `.

```bash
# Create this script
composer test-code-coverage
```

## Running commands to fetch data

To manually run the commands to fetch data from the external sources, you need to:

```bash
# Ensure that you are set for background processing
php artisan queue:work
```

```bash
# Fetch articles for news api
php artisan app:fetch-articles newsapi
```

```bash
# Fetch articles for the guardian
php artisan app:fetch-articles theguardian
```

```bash
# Fetch articles for the new york times
php artisan app:fetch-articles nytimes
```

```bash
# Fetch from all sources
php artisan app:fetch-articles
```

> The `php artisan app:fetch-articles` is registered to automatically run every hour. This can be facilitate using the `schedule:run` command or better still with supervisor on linux.

## Project Documentation - High level

Read more about the internal workings here - [Get it now](https://docs.google.com/document/d/1YzUtP0EG8WaqvUO2TYP8MXfsvTl0c_Cp1ey3PXoV8dc/edit?usp=sharing)
