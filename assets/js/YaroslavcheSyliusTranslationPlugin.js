import Vue from "vue";
import Snotify, { SnotifyPosition } from "vue-snotify";
import messages from "./translations/index.js";
import app from "./app.vue";
import { store } from "./store";

Vue.config.productionTip = false;

require("vue-snotify/styles/material.css");
const options = {
    toast: {
        position: SnotifyPosition.leftTop,
        timeout: 7000,
    },
};
Vue.use(Snotify, options);

window.onload = () => {
    const vm = new Vue({
        store,
        render: (h) => h(app),
    }).$mount("#yaroslavche-sylius-translation-plugin");
};
