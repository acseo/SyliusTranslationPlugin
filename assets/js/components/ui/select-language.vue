<template>
    <div class="mb-3 field">
        <label class="form-label">{{ t("select_language_label") }}</label>
        <div class="ts-wrapper">
            <div class="ts-control">
                <span v-if="selectedNewLocale" class="badge me-1">
                    {{ supportedLocales[selectedNewLocale] }}
                    <span class="ms-1" style="cursor:pointer" @click="removeLocale">&times;</span>
                </span>
                <input
                    type="text"
                    autocomplete="off"
                    tabindex="0"
                    role="combobox"
                    v-model="search"
                    @keydown="dropdownOpen = true"
                    @focus="dropdownOpen = true"
                    @blur="dropdownOpen = false"
                    :placeholder="!selectedNewLocale ? t('search_placeholder') : ''"
                />
            </div>
            <div
                class="ts-dropdown"
                :style="{ display: dropdownOpen ? 'block' : 'none', visibility: 'visible' }"
            >
                <div role="listbox" tabindex="-1" class="ts-dropdown-content">
                    <div
                        class="option"
                        :class="{ active: selectedNewLocale === localeCode }"
                        role="option"
                        v-for="(languageName, localeCode) in filteredLocales"
                        :key="localeCode"
                        @mousedown.prevent="selectLocale(localeCode)"
                    >
                        {{ languageName }}
                    </div>
                </div>
            </div>
            <button :disabled="!selectedNewLocale" class="btn btn-primary ms-1" @click="addLocale">
                {{ t("add_button") }}
            </button>
        </div>
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import t from "../../translations/index.js";

export default {
    name: "ui-select",
    data() {
        return {
            dropdownOpen: false,
            selectedNewLocale: "",
            search: "",
        };
    },
    computed: {
        ...mapGetters(["supportedLocales"]),
        filteredLocales() {
            if (!this.search) return this.supportedLocales;
            const searchLower = this.search.toLowerCase();
            return Object.fromEntries(
                Object.entries(this.supportedLocales).filter(
                    ([code, name]) =>
                        code.toLowerCase().includes(searchLower) ||
                        name.toLowerCase().includes(searchLower)
                )
            );
        },
    },
    methods: {
        t,
        selectLocale(localeCode) {
            this.selectedNewLocale = localeCode;
            this.dropdownOpen = false;
            this.search = "";
        },
        removeLocale() {
            this.selectedNewLocale = "";
        },
        addLocale() {
            const payload = { localeCode: this.selectedNewLocale };
            this.$store.dispatch("addLocale", payload).then(
                (result) => {
                    if (result.status === "success") {
                        this.selectedNewLocale = "";
                        this.$snotify.success(result.message);
                        this.dropdownOpen = false;
                        this.search = "";
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

<style>
.option:hover {
    background-color: #f8f9fa !important;
    cursor: pointer;
}
.option.active {
    background-color: #0d6efd !important;
    color: #fff !important;
}
</style>
