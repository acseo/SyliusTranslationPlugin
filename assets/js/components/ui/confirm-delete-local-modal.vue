<template>
    <div class="ms-1">
        <button type="button" class="btn btn-icon" @click="open">
            <icon name="trash" />
        </button>

        <div
            class="modal fade"
            :class="{ show: showModal }"
            :style="{
                display: showModal ? 'block' : 'none',
                background: showModal ? 'rgba(0,0,0,0.5)' : '',
            }"
            tabindex="-1"
            role="dialog"
            aria-hidden="!showModal"
        >
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                          {{ t('confirm_delete_title') }}
                        </h5>
                    </div>
                    <div class="modal-body">
                        <loading v-if="isLoading" :isCurtain="true" />
                        <p class="lead">
                            {{ t('confirm_delete_message') }}
                            <span class="fw-bold">{{ localeCode }}</span> ?
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button
                            :disabled="isLoading"
                            type="button"
                            class="btn btn-secondary"
                            @click="close"
                        >
                          <span>{{ t('cancel') }}</span>
                        </button>
                        <button
                            :disabled="isLoading"
                            type="button"
                            class="btn btn-danger"
                            @click="removeLocale"
                        >
                          <span>{{ t('delete') }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import icon from "../lib/icon.vue";
import loading from "../ui/loading.vue";
import t from "../../translations/index.js";

export default {
    name: "confirm-delete-local-modal",
    components: {
        icon,
        loading,
    },
    props: {
        localeCode: {
            type: String,
            required: true,
        },
    },
    data() {
        return {
            showModal: false,
            isLoading: false,
        };
    },
    methods: {
        t,
        open() {
            this.showModal = true;
        },
        close() {
            this.isLoading = false;
            this.showModal = false;
        },
        removeLocale() {
            this.isLoading = true;
            this.$store.dispatch("removeLocale", { localeCode: this.localeCode }).then(
                (result) => {
                    if (result.status === "success") {
                        this.$snotify.success(result.message);
                        this.close();
                    } else if (result.status === "error") {
                        this.isLoading = false;
                        this.$snotify.error(result.message);
                    }
                },
                (error) => {
                    this.isLoading = false;
                    this.$snotify.error(error.message);
                }
            );
        },
    },
};
</script>
