# Sonar FMS

## Prerequisites

This uses headless Chrome to render and save the PDF files. This gives us the full HTML and CSS freedom to display a good PDF. There are some prerequisites required:

```bash
curl -sL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs gconf-service libasound2 libatk1.0-0 libc6 libcairo2 libcups2 libdbus-1-3 libexpat1 libfontconfig1 libgbm1 libgcc1 libgconf-2-4 libgdk-pixbuf2.0-0 libglib2.0-0 libgtk-3-0 libnspr4 libpango-1.0-0 libpangocairo-1.0-0 libstdc++6 libx11-6 libx11-xcb1 libxcb1 libxcomposite1 libxcursor1 libxdamage1 libxext6 libxfixes3 libxi6 libxrandr2 libxrender1 libxss1 libxtst6 ca-certificates fonts-liberation libappindicator1 libnss3 lsb-release xdg-utils wget libgbm-dev libxshmfence-dev
sudo npm install --location=global --unsafe-perm puppeteer
sudo chmod -R o+rx /usr/lib/node_modules/puppeteer/.local-chromium
```

## First time set up

Then perform your usual "getting started" tasks for Laravel:

- Clone the repo
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
