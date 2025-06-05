<template>
    <div>
        <div class="row mt-3">
            <card-value
                :loading="availableLocalesLoader"
                :label="t('available_locales')"
                :value="totalAvailableLocalesCount"
            />
            <card-value
                :loading="fullMessageCatalogueLoader"
                :label="t('total_domains_count')"
                :value="totalDomainsCount"
            />
            <card-value
                :loading="fullMessageCatalogueLoader"
                :label="t('total_messages_count')"
                :value="totalMessagesCount"
            />
            <card-value
                :loading="totalTranslationProgressPercentageLoader"
                :label="t('translation_progress')"
                :value="totalTranslationProgressPercentage"
            />
        </div>

        <progressbar :value="totalTranslationProgressPercentageValue" max="100" />

        <div v-show="Object.keys(availableLocales).length > 0">
            <ui-table
                :columns="[
          t('language'),
          t('translated_messages'),
          t('untranslated_messages'),
          t('translation_progress'),
          t('progress'),
          t('action'),
        ]"
            >
                <row-local-values
                    v-for="(languageName, localeCode, index) in availableLocales"
                    :key="index"
                    :localeCode="localeCode"
                />
            </ui-table>
        </div>

        <div class="mt-3">
            <ui-select-language />
        </div>
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import rowLocalValues from "../partials/row-local-values.vue";
import cardValue from "../ui/card-value.vue";
import progressbar from "../ui/progressbar.vue";
import uiSelectLanguage from "../ui/select-language.vue";
import uiTable from "../ui/table.vue";
import t from "../../translations/index.js";

export default {
    name: "page-dashboard",
    components: {
        cardValue,
        progressbar,
        rowLocalValues,
        uiSelectLanguage,
        uiTable,
    },
    data() {
        return {
            fullMessageCatalogueLoader: true,
            availableLocalesLoader: true,
            totalTranslationProgressPercentageLoader: true,
            totalTranslationProgressPercentageValue: 0,
        };
    },
    methods: {
      t
    },
    computed: {
        ...mapGetters([
            "availableLocales",
            "fullMessageCatalogue",
            "totalMessagesCount",
            "messageCatalogues",
        ]),
        totalAvailableLocalesCount: function() {
            if (Object.entries(this.availableLocales).length === 0) {
                return "";
            }
            return Object.keys(this.availableLocales).length;
        },
        totalDomainsCount: function() {
            if (Object.entries(this.fullMessageCatalogue).length === 0) {
                return "";
            }
            return Object.keys(this.fullMessageCatalogue).length;
        },
        totalTranslationProgressPercentage: function() {
            if (
                Object.entries(this.fullMessageCatalogue).length === 0 ||
                Object.entries(this.availableLocales).length === 0
            ) {
                return "";
            }
            let totalTranslatedMessages = 0;
            let totalAvailableLocales = 0;
            Object.keys(this.availableLocales).forEach((localeCode) => {
                const localeCodeDomains = this.messageCatalogues[localeCode];
                if (typeof localeCodeDomains !== "undefined") {
                    totalAvailableLocales++;
                    Object.keys(localeCodeDomains).forEach((domain) => {
                        totalTranslatedMessages += Object.keys(localeCodeDomains[domain]).length;
                    });
                }
            });
            const totalPercentage = (
                (totalTranslatedMessages / (this.totalMessagesCount * totalAvailableLocales)) *
                100
            ).toFixed(2);
            if (isNaN(parseInt(totalPercentage))) {
                return "";
            }

            this.totalTranslationProgressPercentageValue = parseInt(totalPercentage);
            this.totalTranslationProgressPercentageLoader = false;
            return `${totalPercentage}%`;
        },
    },
    watch: {
        fullMessageCatalogue() {
            this.fullMessageCatalogueLoader = false;
        },
        availableLocales() {
            this.availableLocalesLoader = false;
        },
    },
};
</script>
