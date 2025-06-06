<template>
    <div>
        <vue-snotify />
        <breadcrumb :segments="segments" :title="title" />

        <!-- ROUNTING -->
        <page-edit v-if="urlParams.edit" :selectedLocaleCode="urlParams.edit" />
        <page-dashboard v-else />
        <!-- ROUNTING -->
    </div>
</template>

<script>
import pageDashboard from "./components/pages/dashboard.vue";
import pageEdit from "./components/pages/edit.vue";
import breadcrumb from "./components/ui/breadcrumb.vue";

export default {
    name: "app",
    data() {
        return {
            selectedNewLocale: "",
            showModal: false,
        };
    },
    components: {
        breadcrumb,
        pageEdit,
        pageDashboard,
    },
    computed: {
        segments() {
            return this.urlParams.edit
                ? [
                      {
                          label: "Translations",
                          path: "/admin/translation",
                      },
                      this.urlParams.edit,
                      "Edit",
                  ]
                : ["Translations"];
        },
        title() {
            return this.urlParams.edit ? "Edit" : "Translations";
        },
        urlParams() {
            return Object.fromEntries(new URLSearchParams(window.location.search));
        },
    },
    created() {
        this.$store.dispatch("fetchLocalesData").then(
            (result) => {
                if (result.status === "error") {
                    this.$snotify.error(result.message);
                }
            },
            (error) => {
                this.$snotify.error(error.message);
            }
        );
        this.$store.dispatch("fetchFullMessageCatalogue").then(
            (result) => {
                if (result.status === "error") {
                    this.$snotify.error(result.message);
                }
            },
            (error) => {
                this.$snotify.error(error.message);
            }
        );
    },
};
</script>
