<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta name="robots" content="noindex,nofollow">
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0" />
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">
    <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbad5">
    <meta name="msapplication-TileColor" content="#9f00a7">
    <meta name="theme-color" content="#D946EF">
  @isset($title)
    <title>{{ $title }} | {{ config('app.name') }}</title>
  @else
    <title>{{ config('app.name') }}</title>
  @endisset
    <script>
      window.changeTheme = function (dark) {
        if (dark) {
          document.documentElement.classList.add('dark')
        } else {
          document.documentElement.classList.remove('dark')
        }

        localStorage.theme = dark ? 'dark' : 'light'
        window.isDark = dark
      }

      window.changeTheme(localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches))
    </script>

    @routes

    @vite('resources/js/app.js')
  </head>
  <body class="antialiased text-gray-900 dark:text-white bg-white dark:bg-gray-900 selection:text-white selection:bg-primary-800">
    @inertia
    <div id="tooltip-container"></div>
  </body>
</html>
