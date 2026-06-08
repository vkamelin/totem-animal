<template>
  <div class="flex flex-col items-center justify-center gap-4 rounded-3xl border border-slate-800 bg-slate-900/80 p-6 text-center">
    <div class="h-12 w-12 animate-spin rounded-full border-2 border-slate-700 border-t-violet-500" />
    <div class="space-y-1">
      <p class="text-sm font-semibold text-slate-100">
        <slot name="title">Loading...</slot>
      </p>
      <p class="text-sm text-slate-400">
        <slot name="description">Please wait a moment.</slot>
      </p>
    </div>
  </div>
</template>
