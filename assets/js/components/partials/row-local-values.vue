<template>
    <tr>
        <th>
            <div>
                <a class="link" @click="setSelectedLocaleCode">{{ localeCode }}</a>
            </div>
            <div class="label">
                {{ localeLanguageName }}
            </div>
        </th>

        <th>
            <div class="lead">
                <span v-show="translatedMessagesCountLoader">
                    <div class="ui active inline loader"></div>
                </span>
                {{ translatedMessagesCount }}
            </div>
        </th>
        <th>
            <div class="lead">
                <span v-show="untranslatedMessagesCountLoader">
                    <div class="ui active inline loader"></div>
                </span>
                {{ untranslatedMessagesCount }}
            </div>
        </th>
        <th>
            <div class="lead">
                <span v-show="translatedMessagesPercentageLoader">
                    <div class="ui active inline loader"></div>
                </span>
                {{ translatedMessagesPercentage }}
            </div>
        </th>
        <th>
            <progress-bar :value="translatedMessagesPercentageValue" max="100" />
        </th>
        <th class="d-flex">
            <button @click="setSelectedLocaleCode" class="btn btn-icon">
                <icon name="pencil" />
            </button>

            <confirm-delete-local-modal
                :localeCode="localeCode"
                v-if="
                    Object.entries(availableLocales).length > 1 && defaultLocaleCode !== localeCode
                "
            />
        </th>
    </tr>
</template>

<script>
import { mapGetters } from "vuex";
import icon from "../lib/icon.vue";
import confirmDeleteLocalModal from "../ui/confirm-delete-local-modal.vue";
import progressBar from "../ui/progressbar.vue";

export default {
    name: "row-local-values",
    components: {
        progressBar,
        icon,
        confirmDeleteLocalModal,
    },
    props: {
        localeCode: {
            type: String,
            required: true,
        },
    },
    data() {
        return {
            translatedMessagesCountLoader: true,
            untranslatedMessagesCountLoader: true,
            translatedMessagesPercentageLoader: true,
            translatedMessagesPercentageValue: 0,
        };
    },
    computed: {
        ...mapGetters([
            "availableLocales",
            "fullMessageCatalogue",
            "totalMessagesCount",
            "messageCatalogues",
            "selectedLocaleCode",
            "defaultLocaleCode",
            "selectedDomain",
        ]),
        localeLanguageName: {
            get: function() {
                return this.availableLocales[this.localeCode];
            },
        },
        translatedMessagesCount: {
            get: function() {
                if (typeof this.messageCatalogues[this.localeCode] === "undefined") {
                    this.translatedMessagesCountLoader = false;
                    return "-";
                } else {
                    let translatedMessagesCount = 0;
                    Object.keys(this.messageCatalogues[this.localeCode]).forEach((domain) => {
                        translatedMessagesCount += Object.keys(
                            this.messageCatalogues[this.localeCode][domain]
                        ).length;
                    });
                    this.translatedMessagesCountLoader = false;
                    return translatedMessagesCount;
                }
            },
        },
        untranslatedMessagesCount: {
            get: function() {
                if (typeof this.messageCatalogues[this.localeCode] === "undefined") {
                    this.untranslatedMessagesCountLoader = false;
                    return "-";
                } else {
                    const untranslatedMessagesCount =
                        this.totalMessagesCount - this.translatedMessagesCount;
                    this.untranslatedMessagesCountLoader = false;
                    return untranslatedMessagesCount;
                }
            },
        },
        translatedMessagesPercentage: {
            get: function() {
                if (typeof this.messageCatalogues[this.localeCode] === "undefined") {
                    this.translatedMessagesPercentageLoader = false;
                    return "-";
                } else {
                    const percentage = (
                        (this.translatedMessagesCount / this.totalMessagesCount) *
                        100
                    ).toFixed(2);

                    this.translatedMessagesPercentageValue = parseInt(percentage);
                    this.translatedMessagesPercentageLoader = false;
                    return `${percentage}%`;
                }
            },
        },
    },
    methods: {
        setSelectedLocaleCode() {
            this.$store.commit("setSelectedLocaleCode", this.localeCode);
            window.location.search = `?edit=${this.localeCode}`;
        },
        removeLocale() {
            this.$store.dispatch("removeLocale", { localeCode: this.localeCode }).then(
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
    },
};
</script>

<style scoped>
.ui.loader {
    font-size: 10px;
}

.link {
    cursor: pointer;
}

.removeLocale {
    float: right;
    margin: 0 !important;
}

.ui.attached.right.rail {
    width: auto;
    height: auto;
}
</style>
