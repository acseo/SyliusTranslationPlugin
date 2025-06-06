<template>
    <div class="col-4">
        <div class="card">
            <div class="card-header">
              <h3 class="card-title">{{ t('domain') }}</h3>
            </div>
            <div class="card-body">
                <div
                    style="margin-bottom: 5px;"
                    v-for="(domainMessages, domainName) in fullMessageCatalogue"
                    :key="domainName"
                >
                    <a
                        role="button"
                        @click="setFilterDomain(domainName)"
                        :class="'pointer-cursor ' + (domain === domainName ? 'text-primary' : '')"
                    >
                        <div class="d-flex align-items-center">
                            <icon v-if="domain === domainName" name="folder-open" />
                            <icon v-else name="folder-close" />
                            <span class="ms-1">
                                {{ domainName }} ({{ Object.keys(domainMessages).length }})
                            </span>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        <div class="card mt-3">
            <div class="card-header">
              <h3 class="card-title">{{ t('filter') }}</h3>
            </div>
            <div class="card-body">
                <div class="form-check form-switch">
                    <input
                        class="form-check-input"
                        type="checkbox"
                        role="switch"
                        id="filterTranslated"
                        v-model="showTranslated"
                    />
                  <label class="form-check-label" for="filterTranslated">{{ t('show_translated') }}</label>
                </div>

                <div class="form-check form-switch" v-show="showTranslated">
                    <input
                        class="form-check-input"
                        type="checkbox"
                        role="switch"
                        id="filterCustom"
                        v-model="showCustom"
                    />
                  <label class="form-check-label" for="filterCustom">{{ t('only_custom') }}</label>
                </div>
                <div class="form-check form-switch" v-show="showTranslated">
                    <input
                        class="form-check-input"
                        type="checkbox"
                        role="switch"
                        id="filterUntranslated"
                        v-model="showUntranslated"
                    />
                    <label class="form-check-label" for="filterUntranslated">{{ t('show_untranslated') }}</label>

                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import icon from "../lib/icon.vue";
import t from "../../translations/index.js";

export default {
    name: "ui-local-filter",
    components: {
        icon,
    },
    data() {
        return {
            showTranslated: false,
            showUntranslated: true,
            showCustom: true,
            domain: null,
        };
    },
    computed: {
        ...mapGetters(["fullMessageCatalogue"]),
    },
    watch: {
        showTranslated: function() {
            this.setFilter();
        },
        showUntranslated: function() {
            this.setFilter();
        },
        showCustom: function() {
            this.setFilter();
        },
        domain: function() {
            this.setFilter();
        },
    },
    methods: {
        t,
        setFilter: function() {
            const filter = {
                showTranslated: this.showTranslated,
                showUntranslated: this.showUntranslated,
                showCustom: this.showCustom,
                domain: this.domain,
            };
            this.$store.commit("setFilter", filter);
        },
        setFilterDomain: function(domain) {
            this.domain = domain;
        },
    },
};
</script>
