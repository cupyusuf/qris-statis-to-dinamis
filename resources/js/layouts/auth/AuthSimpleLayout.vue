<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight, QrCode, ShieldCheck, Sparkles } from 'lucide-vue-next';
import AppLogo from '@/components/AppLogo.vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="relative min-h-svh overflow-hidden bg-background px-4 py-6 sm:px-6 lg:px-8">
        <div
            class="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_top_left,rgba(16,185,129,0.14),transparent_26%),radial-gradient(circle_at_top_right,rgba(251,191,36,0.12),transparent_24%),linear-gradient(180deg,rgba(255,255,255,0.0),rgba(255,255,255,0.14))]" />

        <div
            class="mx-auto grid min-h-[calc(100vh-3rem)] w-full max-w-6xl items-stretch gap-6 lg:grid-cols-[1fr_0.96fr]">
            <section
                class="relative overflow-hidden rounded-2xl sm:rounded-4xl border border-border/70 bg-card/80 p-4 sm:p-6 lg:p-8 shadow-[0_30px_90px_rgba(24,16,8,0.08)] backdrop-blur-xl">
                <div
                    class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(16,185,129,0.12),transparent_28%)]" />
                <div class="relative flex h-full flex-col justify-between gap-8">
                    <div class="space-y-6">
                        <Link :href="home()"
                            class="inline-flex items-center gap-3 rounded-full border border-border/70 bg-background/80 px-4 py-2 text-sm font-medium text-foreground shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                            <AppLogo />
                        </Link>

                        <div class="max-w-xl space-y-4">
                            <p
                                class="inline-flex items-center gap-2 rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.28em] text-primary">
                                <Sparkles class="size-3.5" />
                                Merchant payment hub
                            </p>
                            <h1 class="text-4xl font-semibold tracking-tight text-foreground sm:text-5xl lg:text-6xl">
                                QRIS yang lebih jelas, modern, dan siap dipakai harian.
                            </h1>
                            <p class="text-base leading-7 text-muted-foreground sm:text-lg">
                                Kelola profil merchant, baca payload dari gambar QR, dan tampilkan halaman publik yang
                                informatif tanpa terasa seperti template bawaan.
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-3">
                            <Link :href="home()"
                                class="inline-flex items-center justify-center rounded-full bg-primary px-5 py-3 text-sm font-semibold text-primary-foreground shadow-lg shadow-primary/20 transition hover:-translate-y-0.5 hover:shadow-xl">
                                Buka halaman publik
                                <ArrowRight class="ml-2 size-4" />
                            </Link>
                            <Link href="/dashboard"
                                class="inline-flex items-center justify-center rounded-full border border-border bg-background px-5 py-3 text-sm font-semibold text-foreground transition hover:border-primary/40 hover:bg-accent">
                                Lihat dashboard
                            </Link>
                        </div>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-3">
                        <div class="rounded-2xl border border-border/70 bg-background/80 p-4 backdrop-blur">
                            <QrCode class="size-5 text-primary" />
                            <p class="mt-3 text-sm font-semibold">QR dinamis</p>
                            <p class="mt-1 text-sm leading-6 text-muted-foreground">Nominal langsung menyesuaikan
                                payload.</p>
                        </div>
                        <div class="rounded-2xl border border-border/70 bg-background/80 p-4 backdrop-blur">
                            <ShieldCheck class="size-5 text-primary" />
                            <p class="mt-3 text-sm font-semibold">Profil terkontrol</p>
                            <p class="mt-1 text-sm leading-6 text-muted-foreground">Aktifkan merchant yang sedang
                                dipakai.</p>
                        </div>
                        <div class="rounded-2xl border border-border/70 bg-background/80 p-4 backdrop-blur">
                            <Sparkles class="size-5 text-primary" />
                            <p class="mt-3 text-sm font-semibold">Tampilan bersih</p>
                            <p class="mt-1 text-sm leading-6 text-muted-foreground">Fokus ke informasi penting, tanpa
                                noise.</p>
                        </div>
                    </div>
                </div>
            </section>

            <section
                class="flex flex-col justify-between rounded-2xl sm:rounded-4xl border border-border/70 bg-card/90 p-4 sm:p-6 lg:p-8 shadow-[0_30px_90px_rgba(24,16,8,0.08)] backdrop-blur-xl">
                <div class="space-y-2">
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-primary">{{ title }}</p>
                    <h2 class="text-2xl font-semibold tracking-tight text-foreground sm:text-3xl">{{ title }}</h2>
                    <p class="max-w-md text-sm leading-6 text-muted-foreground sm:text-base">
                        {{ description }}
                    </p>
                </div>

                <div
                    class="mt-6 rounded-xl sm:rounded-3xl border border-border/70 bg-background/80 p-4 sm:p-6 shadow-inner shadow-black/5">
                    <slot />
                </div>
            </section>
        </div>
    </div>
</template>
