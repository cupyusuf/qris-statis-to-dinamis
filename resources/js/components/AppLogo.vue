<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
</script>

<template>
    <div class="flex items-center gap-3">
        <div
            class="flex size-10 items-center justify-center rounded-2xl bg-primary text-primary-foreground shadow-lg shadow-primary/20 ring-1 ring-primary/10">
            <AppLogoIcon class="size-5 fill-current text-white dark:text-black" />
        </div>
        <div class="grid text-left text-sm leading-tight">
            <span class="font-semibold tracking-tight text-foreground">QRIS Dinamis</span>
            <span class="text-xs text-muted-foreground">Merchant dashboard</span>
        </div>
    </div>
</template>
