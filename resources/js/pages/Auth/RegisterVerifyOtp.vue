<template>
    <AuthLayout title="Verifikasi OTP">
        <div class="mb-3">
            <div class="small text-muted">Email</div>
            <div class="fw-semibold">{{ email }}</div>
        </div>

        <div class="alert alert-light border d-flex align-items-center justify-content-between py-2">
            <div class="small">
                <span class="text-muted">Sisa waktu:</span>
                <span class="fw-semibold ms-1">{{ timeLeftText }}</span>
            </div>
            <span class="badge" :class="expired ? 'text-bg-danger' : 'text-bg-success'">
                {{ expired ? 'Expired' : 'Aktif' }}
            </span>
        </div>

        <form @submit.prevent="submitOtp" class="vstack gap-3">
            <div>
                <label class="form-label">Kode OTP (6 digit)</label>
                <input v-model.trim="form.otp" inputmode="numeric" maxlength="6"
                    class="form-control text-center fw-bold" style="letter-spacing:6px;font-size:20px;"
                    placeholder="••••••" />
                <div v-if="form.errors.otp" class="text-danger small mt-1">{{ form.errors.otp }}</div>
            </div>

            <button class="btn btn-primary w-100" :disabled="form.processing || expired">
                <span v-if="form.processing" class="spinner-border spinner-border-sm me-2"></span>
                Verifikasi
            </button>

            <button type="button" class="btn btn-outline-secondary w-100" :disabled="resendBusy" @click="resend">
                <span v-if="resendBusy" class="spinner-border spinner-border-sm me-2"></span>
                Kirim Ulang OTP
            </button>

            <div class="d-flex justify-content-between small">
                <a class="text-decoration-none" href="/register">Ganti email</a>
                <a class="text-decoration-none" href="/login">Login</a>
            </div>
        </form>
    </AuthLayout>
</template>

<script setup>
import { computed, onBeforeUnmount } from 'vue';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import dayjs from 'dayjs';
import { route } from 'ziggy-js';



const props = defineProps({
    email: String,
    expiresAt: String,
    serverNow: String,
});

const page = usePage();

const form = useForm({
    email: props.email,
    otp: '',
});

const resendForm = useForm({ email: props.email });

let timer = null;

const state = {
    expired: false,
    timeLeftText: '00:00:00',
    resendBusy: false,
};

// Sinkronisasi waktu client vs server (biar countdown gak ngaco)
const serverNow = dayjs(props.serverNow);
const clientNow = dayjs();
const driftMs = clientNow.diff(serverNow);

function computeLeft() {
    const now = dayjs().subtract(driftMs, 'millisecond');
    const end = dayjs(props.expiresAt);
    const diff = end.diff(now, 'second');

    if (diff <= 0) {
        state.expired = true;
        state.timeLeftText = '00:00:00';
        return;
    }

    state.expired = false;
    const h = String(Math.floor(diff / 3600)).padStart(2, '0');
    const m = String(Math.floor((diff % 3600) / 60)).padStart(2, '0');
    const s = String(diff % 60).padStart(2, '0');
    state.timeLeftText = `${h}:${m}:${s}`;
}

computeLeft();
timer = setInterval(computeLeft, 1000);

onBeforeUnmount(() => {
    if (timer) clearInterval(timer);
});

function submitOtp() {
    form.post('/register/verify', {
        onFinish: () => form.reset('otp'),
    });
}

function resend() {
    state.resendBusy = true;
    resendForm.post('/register/resend', {
        preserveScroll: true,
        onFinish: () => {
            state.resendBusy = false;
        },
    });
}


// Flash popup (kalau ada)
const flash = page.props.flash;
if (flash && flash.message) {
    Swal.fire({
        icon: flash.type || 'info',
        title: flash.message,
        timer: 1800,
        showConfirmButton: false,
    });
}

const expired = computed(() => state.expired);
const timeLeftText = computed(() => state.timeLeftText);
const resendBusy = computed(() => state.resendBusy);
</script>
