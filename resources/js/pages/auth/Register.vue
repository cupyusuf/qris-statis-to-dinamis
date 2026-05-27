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
            <h3 class="mb-2 text-lg font-semibold text-foreground">Create your account</h3>
            <p class="mb-4 text-sm text-muted-foreground">Fill in the details to create a new account.</p>
        </div>

        <div class="grid gap-6 bg-background/90 p-6 rounded-lg shadow-sm">
            <div class="grid gap-2">
                <Label for="name" class="text-sm font-medium">Name</Label>
                <Input id="name" type="text" required autofocus :tabindex="1" autocomplete="name" name="name"
                    placeholder="Full name" class="w-full" :aria-describedby="errors.name ? 'name-error' : null" />
                <InputError id="name-error" :message="errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="email" class="text-sm font-medium">Email address</Label>
                <Input id="email" type="email" required :tabindex="2" autocomplete="email" name="email"
                    placeholder="email@example.com" class="w-full"
                    :aria-describedby="errors.email ? 'email-error' : null" />
                <InputError id="email-error" :message="errors.email" />
            </div>

            <div class="grid gap-2">
                <Label for="password" class="text-sm font-medium">Password</Label>
                <PasswordInput id="password" required :tabindex="3" autocomplete="new-password" name="password"
                    placeholder="Password" :passwordrules="passwordRules" class="w-full"
                    :aria-describedby="errors.password ? 'password-error' : null" />
                <InputError id="password-error" :message="errors.password" />
            </div>

            <div class="grid gap-2">
                <Label for="password_confirmation" class="text-sm font-medium">Confirm password</Label>
                <PasswordInput id="password_confirmation" required :tabindex="4" autocomplete="new-password"
                    name="password_confirmation" placeholder="Confirm password" :passwordrules="passwordRules"
                    class="w-full"
                    :aria-describedby="errors.password_confirmation ? 'password-confirmation-error' : null" />
                <InputError id="password-confirmation-error" :message="errors.password_confirmation" />
            </div>

            <Button type="submit"
                class="mt-2 w-full bg-primary text-primary-foreground hover:brightness-105 focus:ring-2 focus:ring-primary/30"
                tabindex="5" :disabled="processing" data-test="register-user-button">
                <Spinner v-if="processing" />
                Create account
            </Button>

            <div class="text-center text-sm text-muted-foreground">
                Already have an account?
                <TextLink :href="login()" class="underline underline-offset-4" :tabindex="6">Log in</TextLink>
            </div>
        </div>
    </Form>
</template>
