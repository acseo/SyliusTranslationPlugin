<template>
    <div class="col-sm-6 col-lg-3 mb-3">
        <div class="card card-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <slot />
                        <div v-if="label" class="subheader">{{ label }}</div>
                        <loading v-if="loading" />
                        <div v-else-if="value" class="h2 mb-0 me-2">
                            {{ value }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import loading from "./loading.vue";

export default {
    name: "card-value",
    components: {
        loading,
    },
    props: {
        loading: {
            type: Boolean,
            default: false,
        },
        value: {
            type: String,
            default: "",
        },
        label: {
            type: String,
            default: "",
        },
    },
};
</script>
