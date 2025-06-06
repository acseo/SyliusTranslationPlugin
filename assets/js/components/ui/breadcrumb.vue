<template>
    <div class="page-header">
        <ol class="breadcrumb" aria-label="breadcrumbs">
            <li class="breadcrumb-item ">
                <a href="/admin/">Dashboard</a>
            </li>
            <li
                v-for="(segment, index) in segments"
                :class="['breadcrumb-item', { active: index === segments.length - 1 }]"
                :key="index"
            >
                <template v-if="typeof segment === 'object'">
                    <a :href="segment.path">{{ segment.label }}</a>
                </template>
                <template v-else>
                    {{ segment }}
                </template>
            </li>
        </ol>
        <div class="row mt-3 row-gap-2">
            <div class="col-12 col-md">
                <div class="d-md-flex gap-2 align-items-center">
                    <div class="d-flex gap-3 align-items-center">
                        <div class="d-flex flex-column gap-2">
                            <h1 class="page-title">
                                {{ title }}
                            </h1>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "breadcrumb",
    props: {
        title: {
            type: String,
            default: "-",
        },
        segments: {
            type: Array,
            default: () => [],
        },
    },
};
</script>
