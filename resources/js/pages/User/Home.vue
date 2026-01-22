<template>
    <div class="container py-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <h4 class="mb-1">Portal User</h4>
                <div class="text-muted small">Tahap 3: Login user sudah terpisah dari admin.</div>
            </div>

            <button type="button" class="btn btn-outline-secondary btn-sm" :disabled="isLoggingOut"
                @click="handleLogout">
                <span v-if="isLoggingOut" class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>
                <i v-else class="bi bi-box-arrow-right me-1"></i>
                Logout
            </button>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="fw-semibold mb-2">Next:</div>
                <ul class="mb-0">
                    <li>Tahap 4: Register Gmail + OTP + Social Login</li>
                    <li>Tahap 6: UI User (Katalog → Pinjam → Status → Kembali)</li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const isLoggingOut = ref(false);

function handleLogout() {
  if (isLoggingOut.value) return;
  isLoggingOut.value = true;

  router.post('/logout', {}, {
    preserveScroll: true,
    onFinish: () => {
      isLoggingOut.value = false;
    },
  });
}
</script>


<style scoped>
/* animasi halus, kecil, tidak lebay */
.btn {
    transition: transform 160ms ease, box-shadow 160ms ease;
}

.btn:active {
    transform: scale(0.98);
}
</style>
