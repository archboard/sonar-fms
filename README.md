<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400"></a></p>

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

# Laravel Inertia Starter

This is a starting point for new projects using [Inertia](https://inertiajs.com/) with Vue 3 and Tailwindcss. It uses Laravel Mix v6 to compile the assets. It does not include anything for authentication, but would suggest using [Laravel Fortify](https://laravel.com/docs/fortify) to provide all of the routes and login logic for authentication. This template is meant to give you a head start in starting a new project from scratch.

Also included is [grantholle/api-resource-detection](https://github.com/grantholle/api-resource-detection) that helps with returning API resources to your Vue components, which I find very ergonomic and one of the best features of Inertia.

Here's the full list of what's included in this starter:

- Laravel Mix v6
- Inertiajs
- Tailwindcss
- Requires `facade/ignition`
- `grantholle/api-resource-detection`
- Laravel Telescope (local environment only)

## Usage

First, start your project by using [degit](https://github.com/Rich-Harris/degit).

```
npx degit grantholle/laravel-inertia#main [your project name]
```

Then perform your usual "getting started" tasks for Laravel:

- `composer install`
- `cp .env.example .env`
- `php artisan key:generate`
- Set up your database
- `npm i`
- `npm run dev`

At this point, you should be set to do your project with Inertia and Tailwind already installed. Depending on your project requirements, you'll likely want to start with authentication.

## Deployment

This is my (Grant's) first attempt at using Laravel Octane. I used the following commands to get openswoole installed:

```
sudo apt install -y libpq-dev libcurl4-openssl-dev
pecl install -D 'enable-sockets="no" enable-openssl="yes" enable-http2="yes" enable-mysqlnd="no" enable-swoole-json="yes" enable-swoole-curl="yes" enable-cares="yes" with-postgres="yes"' openswoole
```

I don't actually think Laravel would use the Postgres and curl tools that comes with Swoole, but enabled them nontheless.

### Supervisor

Here are the supervisor configs used for Horizon and Octane:

```
[program:sonar-fms-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/invoices.ldiglobal.org/live/artisan horizon
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/worker.log
stopwaitsecs=360
```

```
[program:sonar-fms-octane]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/invoices.ldiglobal.org/live/artisan octane:start --server=swoole --host=0.0.0.0 --port=80
autostart=true
autorestart=true
user=www-data
#numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/octane.log
stopwaitsecs=3600
```
