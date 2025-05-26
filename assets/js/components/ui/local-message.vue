<template>
    <div class="col-8">
        <div class="card">
            <div class="card-header">
                <h3 v-if="filter.domain" class="card-title">
                    {{ filter.domain }}
                </h3>
                <h3 v-else class="card-title fst-italic text-secondary">
                    No domain selected
                </h3>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="input-group">
                        <input
                            type="text"
                            v-model="filterIdValue"
                            placeholder="Search"
                            class="form-control"
                        />
                        <a class="btn" role="button">
                            {{ Object.keys(messages).length }} /
                            {{ totalMessagesCount }}
                        </a>
                    </div>

                    <add-message-modal />
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-body">
                <template v-if="Object.keys(messages).length === 0">
                    <div class="alert alert-info" role="alert">
                        No data found
                    </div>
                </template>
                <div class="row my-2" v-for="(message, id) in messages">
                    <div class="col-6 d-flex align-items-center" v-bind="showTooltip(id)">
                        <icon
                            v-if="message.translated"
                            name="check"
                            className="text-success mx-1"
                        />
                        <icon v-if="message.custom" name="gear" className="mx-1" />
                        <icon
                            v-if="!message.translated"
                            name="cirle"
                            className="text-danger mx-1"
                        />
                        <icon
                            v-if="id.length > idMaxLength"
                            name="three-dots"
                            className="text-warning mx-1"
                        />
                        <span class="fluid" style="font-size: 14px;"
                            >{{ message.id }} {{ id.length > idMaxLength ? "..." : "" }}</span
                        >
                    </div>
                    <div class="col-6">
                        <div class="input-group">
                            <input
                                type="text"
                                :placeholder="message.translatedMessage"
                                v-model="message.translatedMessage"
                                @keyup.enter="editTranslationMessage(id, message, $event)"
                                class="form-control"
                            />
                            <button
                                class="btn btn-icon btn-secondary"
                                role="button"
                                @click="editTranslationMessage(id, message, $event)"
                            >
                                <icon name="pencil" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import icon from "../lib/icon.vue";
import addMessageModal from "./add-message-modal.vue";

export default {
    name: "YaroslavcheSyliusTranslationPluginLocaleMessages",
    components: {
        icon,
        addMessageModal,
    },
    props: {
        selectedLocaleCode: {
            type: String,
            default: "",
        },
    },
    data() {
        return {
            idMaxLength: 40,
            newMessage: {},
            filterIdValue: "",
        };
    },
    computed: {
        ...mapGetters([
            "fullMessageCatalogue",
            "totalMessagesCount",
            "messageCatalogues",
            "customMessageCatalogues",
            "filter",
        ]),
        messages: {
            get: function() {
                let messages = {};
                const domain = this.filter.domain || "messages";
                if (!this.filter.showTranslated && !this.filter.showUntranslated) return messages;
                if (typeof this.fullMessageCatalogue[domain] === "undefined") return;
                Object.keys(this.fullMessageCatalogue[domain]).forEach((id) => {
                    let translatedMessage = null;
                    let customMessage = null;
                    if (this.messageCatalogues[this.selectedLocaleCode]) {
                        if (this.messageCatalogues[this.selectedLocaleCode][domain]) {
                            if (this.messageCatalogues[this.selectedLocaleCode][domain][id]) {
                                translatedMessage = this.messageCatalogues[this.selectedLocaleCode][
                                    domain
                                ][id];
                            }
                        }
                    }
                    if (this.customMessageCatalogues[this.selectedLocaleCode]) {
                        if (this.customMessageCatalogues[this.selectedLocaleCode][domain]) {
                            if (this.customMessageCatalogues[this.selectedLocaleCode][domain][id]) {
                                translatedMessage = customMessage = this.customMessageCatalogues[
                                    this.selectedLocaleCode
                                ][domain][id];
                            }
                        }
                    }

                    if (translatedMessage) {
                        if (
                            this.filterIdValue.length > 0 &&
                            !id.toLowerCase().includes(this.filterIdValue.toLowerCase()) &&
                            !translatedMessage
                                .toLowerCase()
                                .includes(this.filterIdValue.toLowerCase())
                        )
                            return;
                    } else {
                        if (
                            this.filterIdValue.length > 0 &&
                            !id.toLowerCase().includes(this.filterIdValue.toLowerCase())
                        )
                            return;
                    }

                    const isTranslatedMessage = typeof translatedMessage === "string";
                    const isCustomMessage = typeof customMessage === "string";

                    if (!this.filter.showTranslated || !this.filter.showUntranslated) {
                        if (this.filter.showTranslated && !isTranslatedMessage) return;
                        if (this.filter.showUntranslated && isTranslatedMessage) return;
                    }
                    if (this.filter.showCustom && isTranslatedMessage && !isCustomMessage) return;

                    messages[id] = {
                        id: id.substring(0, this.idMaxLength),
                        translatedMessage,
                        translated: typeof translatedMessage === "string",
                        custom: isCustomMessage,
                    };
                });
                return messages;
            },
        },
    },
    methods: {
        showTooltip: function(id) {
            if (id.length > this.idMaxLength) {
                return {
                    "data-tooltip": id,
                    "data-inverted": "",
                    "data-position": "top center",
                };
            }
            return {};
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
        editTranslationMessage: function(fullMessageId, message, event) {
            console.log(event, message.translatedMessage, this.selectedLocaleCode);
            /** can be input and icon */
            // console.log(event.target);
            this.setMessage({
                type: "customMessageCatalogues",
                localeCode: this.selectedLocaleCode,
                domain: this.filter.domain,
                id: fullMessageId,
                message: message.translatedMessage,
            });
        },
        setMessage: function(message) {
            this.$store.dispatch("setMessage", message).then(
                (result) => {
                    if (result.status === "success") {
                        this.$snotify.success(result.message);
                    } else if (result.status === "error") {
                        this.$snotify.error(result.message);
                    }
                },
                (error) => {
                    this.$snotify.error(error.message);
                }
            );
        },
        showAddTranslationModal() {
            /* jQuery(this.$refs.addTranslationModal).modal("show", {
                onApprove: function() {
                    console.log(this);
                },
                onDeny: function() {
                    console.log(this);
                },
            }); */
        },
        onCloseModal: function() {
            this.newMessage = {
                domain: this.filter.domain,
                id: null,
                message: null,
            };
        },
    },
    mounted() {
        /* jQuery(this.$refs.domainDropdown).dropdown({showOnFocus: false}); */
    },
};
</script>
