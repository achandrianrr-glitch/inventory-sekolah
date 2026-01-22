<template>
    <div class="min-vh-100 d-flex align-items-center bg-light">
        <div class="container py-4">
            <div class="row justify-content-center">
                <div class="col-12 col-sm-10 col-md-7 col-lg-5">
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <div class="rounded-circle bg-primary" style="width:10px;height:10px;"></div>
                                <div class="fw-semibold">{{ title }}</div>
                            </div>

                            <slot />
                        </div>
                    </div>

                    <div class="text-center mt-3 text-muted small">
                        <slot name="footer" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    title: { type: String, default: 'Masuk' },
});
</script>
