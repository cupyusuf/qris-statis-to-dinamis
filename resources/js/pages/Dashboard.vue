<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { computed, reactive, ref } from 'vue';
import { dashboard } from '@/routes';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import jsQR from 'jsqr';

type QrisProfile = {
    id: number;
    merchant_name: string;
    static_payload: string;
    is_active: boolean;
    created_at: string | null;
    updated_at: string | null;
};

type Props = {
    profiles: QrisProfile[];
    activeProfileId: number | null;
    status?: string;
};

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});

const props = defineProps<Props>();

const activeProfile = computed(() =>
    props.profiles.find((profile) => profile.id === props.activeProfileId) ?? null,
);

const createMerchantName = ref('');
const createStaticPayload = ref('');
const uploadInput = ref<HTMLInputElement | null>(null);
const uploadStatus = ref<string | null>(null);
const uploadError = ref<string | null>(null);
const copyStatus = ref<string | null>(null);
const copyError = ref<string | null>(null);
const editUploadInputs = reactive<Record<number, HTMLInputElement | null>>({});

const editProfiles = reactive<Record<number, { merchant_name: string; static_payload: string }>>(
    Object.fromEntries(
        props.profiles.map((profile) => [profile.id, {
            merchant_name: profile.merchant_name,
            static_payload: profile.static_payload,
        }]),
    ),
);

const copyPayload = async (payload: string): Promise<void> => {
    copyStatus.value = null;
    copyError.value = null;

    try {
        await navigator.clipboard.writeText(payload);
        copyStatus.value = 'Payload berhasil disalin ke clipboard.';
    } catch {
        copyError.value = 'Gagal menyalin payload. Silakan salin manual dari kolom payload.';
    }
};

const setEditUploadInput = (
    profileId: number,
    element: Element | { $el?: Element } | null,
): void => {
    const resolvedElement = element instanceof Element
        ? element
        : element?.$el instanceof Element
            ? element.$el
            : null;

    editUploadInputs[profileId] = resolvedElement instanceof HTMLInputElement ? resolvedElement : null;
};

const openEditUpload = (profileId: number): void => {
    editUploadInputs[profileId]?.click();
};

interface TlvEntry {
    id: string;
    value: string;
}

const parseTlv = (payload: string): TlvEntry[] => {
    const entries: TlvEntry[] = [];
    let index = 0;

    while (index + 4 <= payload.length) {
        const id = payload.slice(index, index + 2);
        const length = Number.parseInt(payload.slice(index + 2, index + 4), 10);

        if (!Number.isFinite(length) || length < 0) {
            break;
        }

        entries.push({
            id,
            value: payload.slice(index + 4, index + 4 + length),
        });

        index += 4 + length;
    }

    return entries;
};

const extractMerchantName = (payload: string): string =>
    parseTlv(payload).find((entry) => entry.id === '59')?.value ?? '';

const readFileAsDataUrl = (file: File): Promise<string> =>
    new Promise((resolve, reject) => {
        const reader = new FileReader();

        reader.onerror = () => reject(new Error('Gagal membaca file gambar.'));
        reader.onload = () => resolve(String(reader.result));
        reader.readAsDataURL(file);
    });

const loadImage = (source: string): Promise<HTMLImageElement> =>
    new Promise((resolve, reject) => {
        const image = new Image();

        image.onload = () => resolve(image);
        image.onerror = () => reject(new Error('Gagal memuat gambar QR.'));
        image.src = source;
    });

const decodeQrImage = async (file: File): Promise<string> => {
    const dataUrl = await readFileAsDataUrl(file);
    const image = await loadImage(dataUrl);
    const canvas = document.createElement('canvas');
    const context = canvas.getContext('2d');

    if (!context) {
        throw new Error('Tidak bisa membuka canvas untuk membaca QR.');
    }

    canvas.width = image.naturalWidth;
    canvas.height = image.naturalHeight;
    context.drawImage(image, 0, 0);

    const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
    const decoded = jsQR(imageData.data, imageData.width, imageData.height, {
        inversionAttempts: 'dontInvert',
    });

    if (!decoded) {
        throw new Error('QR code tidak terdeteksi pada gambar ini.');
    }

    return decoded.data.trim();
};

const handleQrUpload = async (event: Event): Promise<void> => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];

    if (!file) {
        return;
    }

    uploadError.value = null;
    uploadStatus.value = 'Membaca QR dari gambar...';

    try {
        const payload = await decodeQrImage(file);
        createStaticPayload.value = payload;

        const merchantName = extractMerchantName(payload);

        if (merchantName) {
            createMerchantName.value = merchantName;
        }

        uploadStatus.value = 'QR berhasil dibaca. Payload sudah terisi.';
    } catch (error) {
        createStaticPayload.value = '';
        uploadStatus.value = null;
        uploadError.value = error instanceof Error ? error.message : 'Gagal membaca QR.';
    } finally {
        target.value = '';
    }
};

const handleEditQrUpload = async (profileId: number, event: Event): Promise<void> => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];

    if (!file) {
        return;
    }

    uploadError.value = null;
    uploadStatus.value = 'Membaca QR dari gambar...';

    try {
        const payload = await decodeQrImage(file);
        const merchantName = extractMerchantName(payload);

        editProfiles[profileId].static_payload = payload;

        if (merchantName) {
            editProfiles[profileId].merchant_name = merchantName;
        }

        uploadStatus.value = 'QR berhasil dibaca. Form edit sudah terisi.';
    } catch (error) {
        uploadStatus.value = null;
        uploadError.value = error instanceof Error ? error.message : 'Gagal membaca QR.';
    } finally {
        target.value = '';
    }
};
</script>

<template>

    <Head title="Dashboard" />

    <h1 class="sr-only">Dashboard</h1>

    <div class="space-y-6">
        <div class="grid gap-4 lg:grid-cols-[1.15fr_0.85fr]">
            <section
                class="rounded-2xl border border-sidebar-border/70 bg-background p-6 shadow-sm dark:border-sidebar-border">
                <Heading variant="small" title="Create QRIS Profile"
                    description="Tambah merchant baru beserta static payload-nya" />

                <div class="mb-4 rounded-2xl border border-dashed border-sidebar-border/60 bg-muted/20 p-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm font-medium">Upload QR code image</p>
                            <p class="text-xs text-muted-foreground">
                                Upload PNG/JPG QR code untuk menyalin payload otomatis ke form.
                            </p>
                        </div>

                        <div class="flex items-center gap-2">
                            <Button type="button" variant="secondary" @click="uploadInput?.click()">
                                Upload QR
                            </Button>
                            <input ref="uploadInput" type="file" accept="image/*" class="hidden"
                                @change="handleQrUpload" />
                        </div>
                    </div>

                    <p v-if="uploadStatus" class="mt-3 text-sm text-primary" role="status" aria-live="polite">
                        {{ uploadStatus }}
                    </p>
                    <p v-if="uploadError" class="mt-3 text-sm text-destructive" role="alert">
                        {{ uploadError }}
                    </p>
                </div>

                <Form action="/dashboard/qris-profiles" method="post"
                    class="space-y-4 rounded-2xl border border-sidebar-border/60 bg-muted/20 p-4"
                    v-slot="{ errors, processing }">
                    <div class="grid gap-2">
                        <Label for="merchant_name">Merchant / Store Name</Label>
                        <input id="merchant_name" v-model="createMerchantName" name="merchant_name" type="text"
                            class="h-11 rounded-md border border-input bg-background px-3 text-sm outline-none ring-offset-background focus-visible:ring-2 focus-visible:ring-ring"
                            placeholder="Yusuf Tech" required />
                        <InputError :message="errors.merchant_name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="static_payload">Static Payload</Label>
                        <textarea id="static_payload" v-model="createStaticPayload" name="static_payload" rows="7"
                            class="min-h-40 rounded-md border border-input bg-background px-3 py-2 text-sm font-mono outline-none ring-offset-background focus-visible:ring-2 focus-visible:ring-ring"
                            placeholder="000201..." required></textarea>
                        <InputError :message="errors.static_payload" />
                    </div>

                    <label class="flex items-center gap-3 text-sm text-muted-foreground">
                        <Checkbox id="is_active" name="is_active" value="1" />
                        Set as active profile
                    </label>

                    <div class="flex items-center gap-3">
                        <Button :disabled="processing">Create profile</Button>
                        <span class="text-xs text-muted-foreground">Aktifkan otomatis jika dicentang</span>
                    </div>
                </Form>
            </section>

            <section
                class="rounded-2xl border border-sidebar-border/70 bg-background p-6 shadow-sm dark:border-sidebar-border">
                <Heading variant="small" title="Active Profile"
                    description="Dipakai halaman publik untuk generate QR" />

                <div v-if="activeProfile" class="space-y-4 rounded-2xl border border-sidebar-border/60 bg-muted/20 p-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.25em] text-muted-foreground">Merchant / Store Name</p>
                        <p class="mt-2 text-2xl font-semibold">{{ activeProfile.merchant_name }}</p>
                    </div>

                    <div>
                        <p class="text-xs uppercase tracking-[0.25em] text-muted-foreground">Static Payload</p>
                        <pre
                            class="mt-2 max-h-52 overflow-auto rounded-xl bg-background p-3 text-xs leading-5">{{ activeProfile.static_payload }}</pre>
                    </div>

                    <Button type="button" variant="secondary" @click="copyPayload(activeProfile.static_payload)">
                        Copy payload
                    </Button>
                    <p v-if="copyStatus" class="text-sm text-primary" role="status" aria-live="polite">
                        {{ copyStatus }}
                    </p>
                    <p v-if="copyError" class="text-sm text-destructive" role="alert">
                        {{ copyError }}
                    </p>
                </div>

                <div v-else
                    class="rounded-2xl border border-dashed border-sidebar-border/60 bg-muted/20 p-6 text-sm text-muted-foreground">
                    Belum ada profil aktif. Buat profil pertama dari form di sebelah kiri.
                </div>
            </section>
        </div>

        <section
            class="space-y-4 rounded-2xl border border-sidebar-border/70 bg-background p-6 shadow-sm dark:border-sidebar-border">
            <Heading variant="small" title="Manage Profiles" description="Edit, activate, atau hapus merchant QRIS" />

            <div v-if="props.status"
                class="rounded-2xl border border-primary/30 bg-primary/10 px-4 py-3 text-sm text-primary dark:text-primary-foreground">
                {{ props.status }}
            </div>

            <div v-if="!props.profiles.length"
                class="rounded-2xl border border-dashed border-sidebar-border/60 bg-muted/20 p-6 text-sm text-muted-foreground">
                Belum ada data QRIS profile.
            </div>

            <div v-else class="grid gap-4 xl:grid-cols-2">
                <article v-for="profile in props.profiles" :key="profile.id"
                    class="rounded-2xl border border-sidebar-border/60 bg-muted/20 p-4">
                    <div class="mb-4 flex items-start justify-between gap-3">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-lg font-semibold">{{ profile.merchant_name }}</h3>
                                <span v-if="profile.is_active"
                                    class="rounded-full bg-primary px-2 py-0.5 text-[11px] font-medium text-primary-foreground">
                                    Active
                                </span>
                            </div>
                            <p class="text-xs text-muted-foreground">
                                Updated {{ profile.updated_at ?? 'just now' }}
                            </p>
                        </div>
                    </div>

                    <Form :action="`/dashboard/qris-profiles/${profile.id}`" method="patch" class="space-y-4"
                        v-slot="{ errors, processing }">
                        <div
                            class="flex flex-col gap-3 rounded-2xl border border-dashed border-sidebar-border/60 bg-background/60 p-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm font-medium">Upload QR untuk edit ini</p>
                                <p class="text-xs text-muted-foreground">Scan gambar QR dan isi ulang payload merchant
                                    ini.</p>
                            </div>

                            <div class="flex items-center gap-2">
                                <Button type="button" variant="secondary" @click="openEditUpload(profile.id)">
                                    Upload QR
                                </Button>
                                <input :ref="(element) => setEditUploadInput(profile.id, element)" type="file"
                                    accept="image/*" class="hidden"
                                    @change="(event) => handleEditQrUpload(profile.id, event)" />
                            </div>
                        </div>

                        <div class="grid gap-2">
                            <Label :for="`merchant_name_${profile.id}`">Merchant / Store Name</Label>
                            <Input :id="`merchant_name_${profile.id}`" v-model="editProfiles[profile.id].merchant_name"
                                name="merchant_name" type="text" required />
                            <InputError :message="errors.merchant_name" />
                        </div>

                        <div class="grid gap-2">
                            <Label :for="`static_payload_${profile.id}`">Static Payload</Label>
                            <textarea :id="`static_payload_${profile.id}`"
                                v-model="editProfiles[profile.id].static_payload" name="static_payload" rows="7"
                                class="min-h-40 rounded-md border border-input bg-background px-3 py-2 text-sm font-mono outline-none ring-offset-background focus-visible:ring-2 focus-visible:ring-ring"
                                required></textarea>
                            <InputError :message="errors.static_payload" />
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            <Button :disabled="processing">Save changes</Button>
                        </div>
                    </Form>

                    <div class="mt-4 flex flex-wrap items-center gap-3">
                        <Form :action="`/dashboard/qris-profiles/${profile.id}/activate`" method="post">
                            <Button type="submit" variant="secondary" :disabled="profile.is_active">
                                {{ profile.is_active ? 'Already active' : 'Activate' }}
                            </Button>
                        </Form>

                        <Form :action="`/dashboard/qris-profiles/${profile.id}`" method="delete">
                            <Button type="submit" variant="destructive">
                                Delete
                            </Button>
                        </Form>
                    </div>
                </article>
            </div>
        </section>
    </div>
</template>
