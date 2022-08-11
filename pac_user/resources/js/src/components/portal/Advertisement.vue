<template>
    <div class="comp-portal-advertisement">
        <div class="header-comp" v-if="$store.state.portal.editStatus"></div>
        <vx-card v-if="listAdvertisement && listAdvertisement.length > 0" class="advertisement-list advertisement-scrollbar border-8 border-white border-solid">
            <swiper :options="swiperOption" ref="mySwiper" class="swiper-wrapper" key="advertisement">
                <swiper-slide v-for="advertisement in listAdvertisement" :key="advertisement.id">
                    <a class="swiper-zoom-container" :href="advertisement.url ? advertisement.url : baseUrl" target="_blank">
                        <img :src="'data:image/jpeg;base64, '+ advertisement.banner_src" alt="ad-image" width="auto"/>
                    </a>
                </swiper-slide>
                <div class="swiper-pagination" slot="pagination"></div>
            </swiper>
        </vx-card>
    </div>
</template>

<script>
import { mapState, mapActions } from "vuex";
import VxCard from '../vx-card/VxCard.vue';
import config from "../../app.config";
import 'swiper/dist/css/swiper.css';
import { swiper, swiperSlide } from 'vue-awesome-swiper';
import {PORTAL_COMPONENT} from '../../enums/portal_component';

export default {
    components: {
        VxCard,
        swiper,
        swiperSlide,
    },
    name: "Advertisement",
    props: [],
    created() {
    },
    mounted() {
        this.onSearch();
    },
    data() {
        return {
            defaultBanner: `${config.LOCAL_API_URL}/images/no-preview.png`,
            baseUrl: `${config.LOCAL_API_URL}`,
            listAdvertisement: [],
            selected: {},
            swiperOption:{
                init: true,
                autoplay: {
                    delay: 2500,
                    disableOnInteraction: false,
                    pauseOnMouseEnter: true,
                },
                initialSlide: 0,
                direction: 'horizontal',
                pagination: {
                    el: '.swiper-pagination',
                    type: 'bullets',
                    clickable: true,
                },
                loop: true,
                centeredSlides: true,
                slidesPerView: 1,
                spaceBetween: 10,
                observer: true,
                observeParents: true,
                observeSlideChildren: true
            },
        }
    },
    methods: {
        ...mapActions({
            getListAdvertisement: "advertise/getListAdvertisement",
        }),

        onSearch: async function () {
            let params = {
                mst_advertisement_id: null,
                mst_company_id: null,
                mst_department_id: null,
                mst_position_id: null
            }
            const data = await this.getListAdvertisement(params);
            this.listAdvertisement = data;
            if (this.listAdvertisement && this.listAdvertisement.length > 0) {
                this.$emit('changeHasData', PORTAL_COMPONENT.ADVERTISEMENT, true)
            } else {
                this.$emit('changeHasData', PORTAL_COMPONENT.ADVERTISEMENT, false)
            }
            if (!this.listAdvertisement || !this.listAdvertisement.length || this.listAdvertisement.length <= 1) {
                this.swiperOption.autoplay = false;
                this.swiperOption.pagination = false;
                this.swiperOption.loop = false;
            } else {
                this.swiperOption.autoplay = {
                    delay: 2500,
                    disableOnInteraction: false,
                    pauseOnMouseEnter: true,
                };
                this.swiperOption.pagination = {
                    el: '.swiper-pagination',
                    type: 'bullets',
                    clickable: true,
                };
                this.swiperOption.loop = true;
            }
            this.$nextTick(()=>{
                if (this.swiper && this.listAdvertisement && this.listAdvertisement.length && this.listAdvertisement.length > 1) {
                    this.swiper.el.onmouseover = function () {
                        this.swiper.autoplay.stop();
                    }
                    this.swiper.el.onmouseout = function () {
                        this.swiper.autoplay.start();
                    }
                }
            })
        }

    },
    computed: {
        swiper() {
            if (this.$refs.mySwiper) {
                return this.$refs.mySwiper.swiper
            } else {
                return null;
            }
        }
    },
    watch: {
    }

}
</script>

<style scoped>

</style>
