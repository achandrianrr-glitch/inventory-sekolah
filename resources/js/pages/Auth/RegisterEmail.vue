<template>
    <AuthLayout title="Registrasi (Gmail)">
        <form @submit.prevent="submit" class="vstack gap-3">
            <div>
                <label class="form-label">Email Gmail</label>
                <input v-model.trim="form.email" type="email" class="form-control" placeholder="nama@gmail.com"
                    autocomplete="email" />
                <div v-if="form.errors.email" class="text-danger small mt-1">{{ form.errors.email }}</div>
                <div class="form-text">OTP akan dikirim ke email ini (berlaku 1 jam).</div>
            </div>

            <button class="btn btn-primary w-100" :disabled="form.processing">
                <span v-if="form.processing" class="spinner-border spinner-border-sm me-2"></span>
                Kirim OTP
            </button>

            <div class="d-flex justify-content-between small">
                <a class="text-decoration-none" href="/login">Sudah punya akun? Login</a>
                <a class="text-decoration-none" href="/admin/login">Login Admin</a>
            </div>
        </form>
    </AuthLayout>
</template>

<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { useForm } from '@inertiajs/vue3';

const form = useForm({ email: '' });

function submit() {
    form.post('/register/request-otp');
}
</script>
