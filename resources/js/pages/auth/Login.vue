<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
/* @chisel-registration */
import { register } from '@/routes';
/* @end-chisel-registration */
import { store } from '@/routes/login';
import { request } from '@/routes/password';
/* @chisel-passkeys */
import PasskeyVerify from '@/components/PasskeyVerify.vue';
/* @end-chisel-passkeys */

defineOptions({
    layout: {
        title: 'Log in to your account',
        description: 'Enter your email and password below to log in',
    },
});

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();
</script>

<template>

    <Head title="Log in" />

    <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600">
        {{ status }}
    </div>

    <!-- @chisel-passkeys -->
    <PasskeyVerify />
    <!-- @end-chisel-passkeys -->

    <Form v-bind="store.form()" :reset-on-success="['password']" v-slot="{ errors, processing }"
        class="mx-auto w-full max-w-md flex flex-col gap-6">
        <div class="rounded-lg bg-card/90 p-4 sm:p-6 shadow-sm">
            <h3 class="mb-2 text-lg font-semibold text-foreground">Masuk ke akun Anda</h3>
            <p class="mb-4 text-sm text-muted-foreground">Gunakan email dan kata sandi Anda untuk masuk.</p>
        </div>

        <div class="grid gap-6 bg-background/90 p-6 rounded-lg shadow-sm">
            <div class="grid gap-2">
                <Label for="email" class="text-sm font-medium">Alamat email</Label>
                <Input id="email" type="email" name="email" required autofocus :tabindex="1" autocomplete="email"
                    placeholder="nama@contoh.com" :aria-describedby="errors.email ? 'email-error' : null"
                    class="w-full" />
                <p class="mt-1 text-xs text-muted-foreground">Masukkan email yang terdaftar pada akun Anda.</p>
                <InputError id="email-error" :message="errors.email" />
            </div>

            <div class="grid gap-2">
                <div class="flex items-center justify-between">
                    <Label for="password" class="text-sm font-medium">Kata sandi</Label>
                    <TextLink v-if="canResetPassword" :href="request()" class="text-sm" :tabindex="5">
                        Lupa kata sandi?
                    </TextLink>
                </div>
                <PasswordInput id="password" name="password" required :tabindex="2" autocomplete="current-password"
                    placeholder="Password" :aria-describedby="errors.password ? 'password-error' : null"
                    class="w-full" />
                <p class="mt-1 text-xs text-muted-foreground">Kata sandi bersifat pribadi — jangan bagikan.</p>
                <InputError id="password-error" :message="errors.password" />
            </div>

            <div class="flex items-center justify-between">
                <Label for="remember" class="flex items-center space-x-3">
                    <Checkbox id="remember" name="remember" :tabindex="3" />
                    <span>Ingat saya</span>
                </Label>
            </div>

            <Button type="submit"
                class="mt-4 w-full bg-primary text-primary-foreground hover:brightness-105 focus:ring-2 focus:ring-primary/30"
                :tabindex="4" :disabled="processing" data-test="login-button">
                <Spinner v-if="processing" />
                Masuk
            </Button>

            <!-- @chisel-registration -->
            <div class="text-center text-sm text-muted-foreground">
                Belum punya akun?
                <TextLink :href="register()" :tabindex="5">Daftar</TextLink>
            </div>
            <!-- @end-chisel-registration -->
        </div>
    </Form>
</template>
