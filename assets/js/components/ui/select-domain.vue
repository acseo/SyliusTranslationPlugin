<template>
    <div class="mb-3 field">
        <label class="form-label">{{ t('select_domain_label') }}</label>

        <select
            class="form-select"
            v-model="$attrs.value"
            @change="$emit('input', $event.target.value)"
        >
          <option value="" disabled selected>{{ t('select_domain_placeholder') }}</option>
          <option v-for="(value, domain) in fullMessageCatalogue" :value="domain" :key="domain">{{
                domain
            }}</option>
        </select>
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import t from "../../translations/index.js";

export default {
    methods: {
      t
    },
    name: "ui-select-domain",
    props: {
        defaultValue: {
            type: String,
            default: "",
        },
    },
    computed: {
        ...mapGetters(["fullMessageCatalogue"]),
    },
    mounted() {
        if (this.defaultValue) {
            this.$emit("input", this.defaultValue);
        }
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
