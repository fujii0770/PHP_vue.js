<template>
    <div class="special-portal">
        <vx-card class="mb-4">
            <HeaderComponent title="受取連携会社" @hiddenAppPortal="$emit('hiddenAppPortal')"></HeaderComponent>
            <special-component
                :issetSpecialInternal="true"></special-component>
        </vx-card>
    </div>
</template>

<script>
import { mapState, mapActions } from "vuex";
import VxPagination from '@/components/vx-pagination/VxPagination.vue';
import HeaderComponent from "./HeaderComponent";
import SpecialComponent from "./SpecialComponent";

export default {
    components: {
        VxPagination,
        HeaderComponent,
        SpecialComponent
    },
    name: "SpecialPortal",
    props: [],
    beforeCreate() {
    },
    created() {

    },
    beforeMount() {
    },
    mounted() {

    },
    beforeUpdate() {
    },
    update() {
    },
    beforeDestroy() {
    },
    destroyed() {
    },

    data() {
        return {

        }
    },
    methods: {

    },
    computed: {
        ...mapState({
            listService: state => state.portal.listService,
        }),

    },
    watch: {

    }

}
</script>

<style scoped>



</style>
