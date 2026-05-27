<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import { store } from '@/routes/register';

defineProps<{
    passwordRules: string;
}>();

defineOptions({
    layout: {
        title: 'Create an account',
        description: 'Enter your details below to create your account',
    },
});
</script>

<template>

    <Head title="Register" />

    <Form v-bind="store.form()" :reset-on-success="['password', 'password_confirmation']"
        v-slot="{ errors, processing }" class="mx-auto w-full max-w-md flex flex-col gap-6">
        <div class="rounded-lg bg-card/90 p-4 sm:p-6 shadow-sm">
            <h3 class="mb-2 text-lg font-semibold text-foreground">Buat akun baru</h3>
            <p class="mb-4 text-sm text-muted-foreground">Isi detail di bawah untuk membuat akun baru.</p>
        </div>

        <div class="grid gap-6 bg-background/90 p-6 rounded-lg shadow-sm">
            <div class="grid gap-2">
                <Label for="name" class="text-sm font-medium">Nama lengkap</Label>
                <Input id="name" type="text" required autofocus autocomplete="name" name="name"
                    placeholder="Nama lengkap" class="w-full" :aria-describedby="errors.name ? 'name-error' : null" />
                <InputError id="name-error" :message="errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="email" class="text-sm font-medium">Alamat email</Label>
                <Input id="email" type="email" required autocomplete="email" name="email" placeholder="nama@contoh.com"
                    class="w-full" :aria-describedby="errors.email ? 'email-error' : null" />
                <p class="mt-1 text-xs text-muted-foreground">Gunakan email yang valid untuk verifikasi.</p>
                <InputError id="email-error" :message="errors.email" />
            </div>

            <div class="grid gap-2">
                <Label for="password" class="text-sm font-medium">Kata sandi</Label>
                <PasswordInput id="password" required autocomplete="new-password" name="password"
                    placeholder="Kata sandi" :passwordrules="passwordRules" class="w-full"
                    :aria-describedby="errors.password ? 'password-error' : null" />
                <p class="mt-1 text-xs text-muted-foreground">Minimal 8 karakter; gunakan kombinasi huruf dan angka.</p>
                <InputError id="password-error" :message="errors.password" />
            </div>

            <div class="grid gap-2">
                <Label for="password_confirmation" class="text-sm font-medium">Konfirmasi kata sandi</Label>
                <PasswordInput id="password_confirmation" required autocomplete="new-password"
                    name="password_confirmation" placeholder="Konfirmasi kata sandi" :passwordrules="passwordRules"
                    class="w-full"
                    :aria-describedby="errors.password_confirmation ? 'password-confirmation-error' : null" />
                <InputError id="password-confirmation-error" :message="errors.password_confirmation" />
            </div>

            <Button type="submit"
                class="mt-2 w-full bg-primary text-primary-foreground hover:brightness-105 focus:ring-2 focus:ring-primary/30"
                :disabled="processing" data-test="register-user-button">
                <Spinner v-if="processing" />
                Buat akun
            </Button>

            <div class="text-center text-sm text-muted-foreground">
                Sudah punya akun?
                <TextLink :href="login()" class="underline underline-offset-4">Masuk</TextLink>
            </div>
        </div>
    </Form>
</template>
