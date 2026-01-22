<template>
    <AuthLayout title="Buat Password">
        <div class="mb-3">
            <div class="small text-muted">Email terverifikasi</div>
            <div class="fw-semibold">{{ email }}</div>
        </div>

        <form @submit.prevent="submit" class="vstack gap-3">
            <div>
                <label class="form-label">Nama</label>
                <input v-model.trim="form.name" type="text" class="form-control" placeholder="Nama lengkap" />
                <div v-if="form.errors.name" class="text-danger small mt-1">{{ form.errors.name }}</div>
            </div>

            <div>
                <label class="form-label">Password</label>
                <input v-model="form.password" type="password" class="form-control" autocomplete="new-password" />
                <div class="form-text">Minimal 8 karakter.</div>
                <div v-if="form.errors.password" class="text-danger small mt-1">{{ form.errors.password }}</div>
            </div>

            <div>
                <label class="form-label">Konfirmasi Password</label>
                <input v-model="form.password_confirmation" type="password" class="form-control"
                    autocomplete="new-password" />
            </div>

            <button class="btn btn-primary w-100" :disabled="form.processing">
                <span v-if="form.processing" class="spinner-border spinner-border-sm me-2"></span>
                Submit
            </button>

            <div class="small text-muted">
                Setelah sukses, kamu akan diarahkan ke halaman login.
            </div>
        </form>
    </AuthLayout>
</template>

<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

const props = defineProps({ email: String });
const page = usePage();

const form = useForm({
    email: props.email,
    name: '',
    password: '',
    password_confirmation: '',
});

function submit() {
    form.post('/register/complete', {
        onSuccess: () => {
            const flash = page.props.flash;
            Swal.fire({
                icon: 'success',
                title: (flash && flash.message) ? flash.message : 'Registrasi sukses!',
                timer: 1600,
                showConfirmButton: false,
            });
        },
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
}

</script>
