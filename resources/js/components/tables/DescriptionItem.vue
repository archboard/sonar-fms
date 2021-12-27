<template>
  <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
    <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">
      <slot name="dt" />
    </dt>
    <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">
      <slot name="dd" />
    </dd>
  </div>
</template>
