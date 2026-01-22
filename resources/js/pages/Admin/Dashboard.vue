\<template>
    <div class="container py-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <h4 class="mb-1">Portal Admin</h4>
                <div class="text-muted small">Tahap 3: Admin route terlindungi role.</div>
            </div>

            <button type="button" class="btn btn-outline-secondary btn-sm" :disabled="processing" @click="logoutAdmin">
                <span v-if="processing" class="spinner-border spinner-border-sm me-2"></span>
                <i v-else class="bi bi-box-arrow-right me-1"></i>
                Logout
            </button>
        </div>

        <div class="alert alert-info small">
            Kamu sudah siap lanjut Tahap 4 (OTP + verifikasi email + social login).
        </div>
    </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

const processing = ref(false);

function logoutAdmin() {
    if (processing.value) return;

    processing.value = true;
    router.post('/admin/logout', {}, {
        onFinish: () => {
            processing.value = false;
        },
    });
}
</script>
