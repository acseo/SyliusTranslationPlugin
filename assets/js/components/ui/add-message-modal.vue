<template>
    <div>
        <buton role="button" class="btn btn-primary btn-icon ms-1" @click="open">
            <icon name="plus" />
        </buton>

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
                            Add new message
                        </h5>
                    </div>
                    <div class="modal-body">
                        <loading v-if="isLoading" :isCurtain="true" />
                        <ui-select-domain v-model="newMessage.domain" />
                        <div class="mt-3 field">
                            <label for="basic-url" class="form-label">Translation Id</label>
                            <input
                                :disabled="isLoading"
                                type="text"
                                class="form-control"
                                id="basic-url"
                                aria-describedby="basic-addon3 basic-addon4"
                                v-model="newMessage.id"
                                maxlength="40"
                            />
                        </div>
                        <div class="mt-3 field">
                            <label for="basic-url" class="form-label">Translation</label>
                            <input
                                :disabled="isLoading"
                                type="text"
                                class="form-control"
                                id="basic-url"
                                aria-describedby="basic-addon3 basic-addon4"
                                v-model="newMessage.message"
                                maxlength="255"
                            />
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button
                            :disabled="isLoading"
                            type="button"
                            class="btn btn-secondary"
                            @click="close"
                        >
                            <span>Cancel</span>
                        </button>
                        <button
                            :disabled="!newMessage.id || !newMessage.message || isLoading"
                            type="button"
                            class="btn btn-primary"
                            @click="addTranslationMessage"
                        >
                            <span>Add</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import icon from "../lib/icon.vue";
import loading from "../ui/loading.vue";
import uiSelectDomain from "./select-domain.vue";
export default {
    name: "add-message-modal",
    components: {
        icon,
        uiSelectDomain,
        loading,
    },
    computed: {
        ...mapGetters(["filter", "fullMessageCatalogue", "selectedLocaleCode"]),
    },
    data() {
        return {
            showModal: false,
            newMessage: {},
            isLoading: false,
        };
    },
    methods: {
        open() {
            this.showModal = true;
            this.newMessage = {
                domain: this.filter.domain ? this.filter.domain : "messages",
                id: "",
                message: "",
            };
        },
        close() {
            this.newMessage = {};
            this.isLoading = false;
            this.showModal = false;
        },
        addTranslationMessage: function() {
            this.setMessage({
                type: "customMessageCatalogues",
                localeCode: this.selectedLocaleCode,
                domain: this.newMessage.domain,
                id: this.newMessage.id,
                message: this.newMessage.message,
            });
        },
        setMessage: function(message) {
            this.isLoading = true;
            this.$store.dispatch("setMessage", message).then(
                (result) => {
                    if (result.status === "success") {
                        this.$snotify.success(result.message);
                        this.close();
                    } else if (result.status === "error") {
                        this.$snotify.error(result.message);
                        this.isLoading = false;
                    }
                },
                (error) => {
                    this.$snotify.error(error.message);
                    this.isLoading = false;
                }
            );
        },
    },
};
</script>
