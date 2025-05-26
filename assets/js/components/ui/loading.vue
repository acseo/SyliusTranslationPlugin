<template>
    <div v-if="enabled" v-bind:class="{ 'curtain-loader': isCurtain }">
        <img src="/build/admin/images/loader.64deb632.svg" alt="Loading" />
    </div>
</template>

<script>
export default {
    name: "loading",
    props: {
        enabled: {
            type: Boolean,
            default: true,
        },
        isCurtain: {
            type: Boolean,
            default: false,
        },
    },
};
</script>

<style scoped>
.curtain-loader {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(255, 255, 255, 0.8);
    display: flex;
    justify-content: center;
    align-items: center;
}
</style>
