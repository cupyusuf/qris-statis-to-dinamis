<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import QRCode from 'qrcode';
import { computed, ref, watch } from 'vue';

interface QrisPageProps {
    qris: {
        merchantName: string;
        staticPayload: string;
    };
}

const props = defineProps<QrisPageProps>();

const amount = ref('3000');
const staticPayload = ref(props.qris.staticPayload);
const qrImage = ref<string | null>(null);
const qrError = ref<string | null>(null);
const qrLoading = ref(false);

const amountNumber = computed(() => {
    const parsedAmount = Number.parseInt(amount.value, 10);

    return Number.isFinite(parsedAmount) && parsedAmount > 0 ? parsedAmount : 1;
});

const formattedAmount = computed(() => new Intl.NumberFormat('id-ID').format(amountNumber.value));

const qrisPayload = computed(() => buildDynamicQrisPayload(staticPayload.value, amountNumber.value));

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

        const value = payload.slice(index + 4, index + 4 + length);

        entries.push({ id, value });
        index += 4 + length;
    }

    return entries;
};

const serializeTlv = (entries: TlvEntry[]): string =>
    entries
        .map(({ id, value }) => {
            const length = value.length.toString().padStart(2, '0');

            return `${id}${length}${value}`;
        })
        .join('');

const formatAmount = (value: number): string => {
    if (!Number.isFinite(value) || value <= 0) {
        return '1';
    }

    return Number.isInteger(value)
        ? String(value)
        : value.toFixed(2).replace(/0+$/, '').replace(/\.$/, '');
};

const computeCrc16 = (input: string): string => {
    let crc = 0xffff;

    for (let index = 0; index < input.length; index += 1) {
        crc ^= input.charCodeAt(index) << 8;

        for (let bit = 0; bit < 8; bit += 1) {
            if ((crc & 0x8000) !== 0) {
                crc = ((crc << 1) ^ 0x1021) & 0xffff;
            } else {
                crc = (crc << 1) & 0xffff;
            }
        }
    }

    return crc.toString(16).toUpperCase().padStart(4, '0');
};

const buildDynamicQrisPayload = (payload: string, nominal: number): string => {
    const entries = parseTlv(payload.trim());
    const rebuiltEntries: TlvEntry[] = [];
    let pointOfInitiationFound = false;
    let amountFound = false;

    for (const entry of entries) {
        if (entry.id === '63') {
            continue;
        }

        if (entry.id === '01') {
            rebuiltEntries.push({ id: '01', value: '12' });
            pointOfInitiationFound = true;
            continue;
        }

        if (entry.id === '54') {
            rebuiltEntries.push({ id: '54', value: formatAmount(nominal) });
            amountFound = true;
            continue;
        }

        rebuiltEntries.push(entry);
    }

    if (!pointOfInitiationFound) {
        rebuiltEntries.splice(1, 0, { id: '01', value: '12' });
    }

    if (!amountFound) {
        rebuiltEntries.push({ id: '54', value: formatAmount(nominal) });
    }

    const payloadWithoutCrc = serializeTlv(rebuiltEntries) + '6304';

    return payloadWithoutCrc + computeCrc16(payloadWithoutCrc);
};

const renderQrImage = async (): Promise<void> => {
    if (!staticPayload.value.trim()) {
        qrImage.value = null;
        qrError.value = 'Payload QRIS statis belum diisi.';

        return;
    }

    qrLoading.value = true;
    qrError.value = null;

    try {
        qrImage.value = await QRCode.toDataURL(qrisPayload.value, {
            errorCorrectionLevel: 'M',
            margin: 1,
            width: 360,
        });
    } catch (error) {
        qrImage.value = null;
        qrError.value = error instanceof Error ? error.message : 'Gagal membuat QR dinamis.';
    } finally {
        qrLoading.value = false;
    }
};

watch([amount, staticPayload], renderQrImage, { immediate: true });
</script>

<template>

    <Head title="QRIS Dinamis" />

    <div class="min-h-screen bg-background text-foreground">
        <div class="mx-auto flex min-h-screen w-full max-w-4xl items-center px-4 py-8 sm:px-6 lg:px-8">
            <main class="w-full overflow-hidden rounded-4xl border border-border bg-card/90 shadow-lg backdrop-blur">
                <div class="px-6 py-7 sm:px-10 sm:py-10">
                    <div class="mb-8 flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs uppercase tracking-[0.38em] text-primary">QRIS Dinamis</p>
                            <h1 class="mt-3 text-3xl font-semibold tracking-tight sm:text-4xl">
                                {{ props.qris.merchantName }}
                            </h1>
                            <p class="mt-3 max-w-xl text-sm leading-6 text-muted-foreground sm:text-base">
                                Nama toko dipilih dari profil aktif di dashboard. Ubah nominal lalu QR akan menyesuaikan
                                secara dinamis.
                            </p>
                        </div>
                        <div
                            class="hidden rounded-full border border-border bg-muted/80 px-4 py-2 text-sm font-medium text-muted-foreground sm:block">
                            Profil aktif
                        </div>
                    </div>

                    <div class="grid gap-8 lg:grid-cols-[1fr_0.72fr] lg:items-center">
                        <section
                            class="flex flex-col items-center justify-center rounded-4xl bg-muted/40 px-5 py-6 sm:px-8 sm:py-8">
                            <div
                                class="mb-5 inline-flex items-center rounded-full border border-border bg-card px-4 py-2 text-sm font-medium text-muted-foreground shadow-sm">
                                Nominal aktif: Rp {{ formattedAmount }}
                            </div>

                            <div
                                class="flex min-h-88 w-full items-center justify-center rounded-3xl bg-card p-5 shadow-inner sm:min-h-104">
                                <div v-if="qrLoading"
                                    class="flex h-72 w-full items-center justify-center rounded-[1.25rem] bg-muted/30 text-sm text-muted-foreground">
                                    Membuat QR...
                                </div>
                                <template v-else>
                                    <img v-if="qrImage" :src="qrImage" alt="QRIS dinamis"
                                        class="h-72 w-72 rounded-[1.25rem] bg-card p-3 sm:h-80 sm:w-80" />
                                    <div v-else
                                        class="flex h-72 w-full items-center justify-center rounded-[1.25rem] bg-muted/30 text-sm text-muted-foreground">
                                        {{ qrError ?? 'QR belum tersedia.' }}
                                    </div>
                                </template>
                            </div>
                        </section>

                        <section class="grid gap-4">
                            <div class="rounded-[1.75rem] border border-border bg-background p-5 sm:p-6">
                                <p class="text-sm font-medium uppercase tracking-[0.28em] text-primary">Nominal</p>
                                <label class="mt-4 block">
                                    <span class="sr-only">Nominal QRIS</span>
                                    <input v-model="amount" type="number" min="1" step="1"
                                        class="w-full rounded-2xl border border-input bg-card px-4 py-4 text-2xl font-semibold outline-none transition placeholder:text-muted-foreground focus:border-primary focus:ring-4 focus:ring-primary/20"
                                        placeholder="3000" />
                                </label>
                                <p class="mt-3 text-sm leading-6 text-muted-foreground">
                                    QR akan dibentuk dengan nominal ini.
                                </p>
                            </div>

                            <div
                                class="rounded-[1.75rem] border border-border bg-primary p-5 text-primary-foreground shadow-lg sm:p-6">
                                <p class="text-xs uppercase tracking-[0.3em] text-primary-foreground/80">Merchant/Store
                                    Name</p>
                                <p class="mt-3 text-2xl font-semibold tracking-tight sm:text-[2rem]">
                                    {{ props.qris.merchantName }}
                                </p>
                                <p class="mt-3 text-sm leading-6 text-primary-foreground/80">
                                    Pembayaran ditujukan ke toko ini dengan QR dinamis.
                                </p>
                            </div>
                        </section>
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>
