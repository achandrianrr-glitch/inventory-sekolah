<template>
    <AuthLayout title="Login User">
        <form @submit.prevent="submit" class="vstack gap-3">
            <div>
                <label class="form-label">Email (Gmail)</label>
                <input v-model.trim="form.email" type="email" class="form-control" placeholder="nama@gmail.com"
                    autocomplete="email" />
                <div v-if="form.errors.email" class="text-danger small mt-1">{{ form.errors.email }}</div>
            </div>

            <div>
                <label class="form-label">Password</label>
                <input v-model="form.password" type="password" class="form-control" autocomplete="current-password" />
                <div v-if="form.errors.password" class="text-danger small mt-1">{{ form.errors.password }}</div>
            </div>

            <div class="d-flex align-items-center justify-content-between">
                <div class="form-check">
                    <input v-model="form.remember" class="form-check-input" type="checkbox" id="rememberUser" />
                    <label class="form-check-label" for="rememberUser">Ingat saya</label>
                </div>

                <a class="small text-decoration-none" href="/admin/login">Login Admin</a>
            </div>

            <button class="btn btn-primary w-100" :disabled="form.processing">
                <span v-if="form.processing" class="spinner-border spinner-border-sm me-2"></span>
                Masuk
            </button>
            <div class="d-grid gap-2 mt-2">
                <a class="btn btn-outline-danger" href="/auth/google/redirect">
                    <i class="bi bi-google me-1"></i> Login dengan Google
                </a>
                <a class="btn btn-outline-dark" href="/auth/github/redirect">
                    <i class="bi bi-github me-1"></i> Login dengan GitHub
                </a>
            </div>

            <div class="small mt-3">
                Belum punya akun?
                <a class="text-decoration-none" href="/register">Registrasi (OTP Gmail)</a>
            </div>

        </form>

        <template #footer>
            Registrasi + OTP Gmail akan dibuat di <span class="fw-semibold">Tahap 4</span>.
        </template>
    </AuthLayout>
</template>

<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

function submit() {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
}

</script>
