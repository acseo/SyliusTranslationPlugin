<template>
    <div class="card mt-3">
        <div class="border-bottom">
            <div class="table-responsive">
                <table class="table card-table table-vcenter datatable">
                    <thead>
                        <tr>
                            <th v-for="column in columns" scope="col">{{ column }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <slot />
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "ui-table",
    props: {
        columns: {
            type: Array,
            default: () => [],
        },
    },
};
</script>
