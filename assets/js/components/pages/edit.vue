<template>
    <div class="row">
        <ui-local-filter />
        <ui-local-message :selectedLocaleCode="selectedLocaleCode" />
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import uiLocalFilter from "../ui/local-filter.vue";
import uiLocalMessage from "../ui/local-message.vue";

export default {
    components: {
        uiLocalFilter,
        uiLocalMessage,
    },
    computed: {
        ...mapGetters(["availableLocales"]),
    },
    props: {
        selectedLocaleCode: {
            type: String,
            default: "",
        },
    },
    name: "page-edit",
    methods: {
        setSelectedLocaleCode(localeCode) {
            this.$store.commit("setSelectedLocaleCode", localeCode);
        },
    },
    watch: {
        availableLocales: function() {
            const params = Object.fromEntries(new URLSearchParams(window.location.search));
            if (
                !this.availableLocales[this.selectedLocaleCode] ||
                !params["edit"] ||
                !this.availableLocales[params["edit"]]
            ) {
                window.location.href = "/admin/translation";
            }
            this.setSelectedLocaleCode(Object.keys(this.availableLocales)[0]);
        },
    },
    mounted() {
        const params = Object.fromEntries(new URLSearchParams(window.location.search));
        if (params) this.setSelectedLocaleCode(params["edit"]);
    },
};
</script>
