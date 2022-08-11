<template>
    <div class="circular-portal" v-if="!optionFlg">
        <vx-card class="mb-4">
            <header-component title="" :logoShachihataDefault="require('@assets/images/logo/logo_shachihata_circular.png')" @hiddenAppPortal="$emit('hiddenAppPortal')"></header-component>
            <circular-component
                :isShachihataImage="isShachihataImage"
                :issetFavoriteInternal="true"></circular-component>
        </vx-card>
    </div>
</template>

<script>
import { mapState, mapActions } from "vuex";
import VxPagination from '@/components/vx-pagination/VxPagination.vue';
import HeaderComponent from "./HeaderComponent";
import CircularComponent from "./CircularComponent";

export default {
    components: {
        VxPagination,
        HeaderComponent,
        CircularComponent
    },
    name: "CircularPortal",
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
            isShachihataImage: false,
            optionFlg : JSON.parse(getLS('user')).option_flg,
        }
    },
    methods: {

    },
    computed: {
        ...mapState({
            listFavoriteState: state => state.portal.listFavorite,
            listService: state => state.portal.listService,
        }),
        listFavoriteInternal: {
            get() {return this.listFavoriteState.filter((item) => item.is_shachihata == 1);},
        },
        issetFavoriteInternal: {
            get() {return  this.listFavoriteInternal.length;},
        },

    },
    watch: {

    }

}
</script>

<style scoped>



</style>
