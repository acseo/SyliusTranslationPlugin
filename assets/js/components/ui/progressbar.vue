<template>
    <div class="progress">
        <div
            class="progress-bar"
            role="progressbar"
            :style="{ width: value + '%' }"
            :aria-valuenow="value"
            aria-valuemin="0"
            :aria-valuemax="max"
        ></div>
    </div>
</template>

<script>
export default {
    name: "progress-bar",
    props: {
        value: {
            type: Number,
            default: 0,
        },
        max: {
            type: Number,
            default: 100,
        },
    },
};
</script>
