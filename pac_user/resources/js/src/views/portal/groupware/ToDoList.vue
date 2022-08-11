<template>
    <div id="to_do_list">
        <vs-row>
            <div class="to-do-list-page">
                <top-menu></top-menu>
                <to-do-list></to-do-list>
            </div>
        </vs-row>
    </div>
</template>
<script>

import TopMenu from "../../../components/portal/TopMenu";
import ToDoList from "../../../components/portal/ToDoList";

export default {
    components: {
        TopMenu,
        ToDoList,
    },
    data() {
        return {
        
        }
    },
    computed: {},
    methods: {},
    created() {},
    mounted() {}
}

</script>
<style lang="scss" scoped>
    #to_do_list {
        .to-do-list-page {
            width: 100%;
            .comp-portal-to-do-list {
                border-radius: 8px;
                display: flex;
                background-color: white !important;
                border-color: white !important;
                height: calc(100vh - 80px);
            }
        }
    }
</style>
