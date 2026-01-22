<template>
    <AuthLayout title="Login Admin">
        <form @submit.prevent="submit" class="vstack gap-3">
            <div>
                <label class="form-label">Email Admin</label>
                <input v-model.trim="form.email" type="email" class="form-control" placeholder="admin@gmail.com"
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
                    <input v-model="form.remember" class="form-check-input" type="checkbox" id="rememberAdmin" />
                    <label class="form-check-label" for="rememberAdmin">Ingat saya</label>
                </div>

                <a class="small text-decoration-none" href="/login">Login User</a>
            </div>

            <button class="btn btn-dark w-100" :disabled="form.processing">
                <span v-if="form.processing" class="spinner-border spinner-border-sm me-2"></span>
                Masuk Admin
            </button>

            <div class="d-grid gap-2 mt-2">
                <a class="btn btn-outline-danger" href="/auth/google/redirect">
                    <i class="bi bi-google me-1"></i> Login dengan Google
                </a>
                <a class="btn btn-outline-dark" href="/auth/github/redirect">
                    <i class="bi bi-github me-1"></i> Login dengan GitHub
                </a>
            </div>


            <div class="alert alert-warning py-2 small mb-0">
                Hanya akun dengan role <span class="fw-semibold">super_admin</span> atau <span
                    class="fw-semibold">petugas</span> yang bisa masuk.
            </div>
        </form>

        <template #footer>
            Kalau akun bukan admin → otomatis ditolak.
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
    form.post('/admin/login', {
        onFinish: () => form.reset('password'),
    });
}

</script>
