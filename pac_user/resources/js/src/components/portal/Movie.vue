<template>
    <div class="comp-portal-movie">
        <vx-card
            v-if="listMovie.length > 0"
            class="border-8 border-white border-solid"
            style="height: 100%;"
        >
            <div class="movie-box">
                <div class="movie-item" v-for="movie in listMovie"
                        :key="movie.id">
                    <div class="theme-title over-text">{{movie.theme_name}}</div>
                    <div class="title over-text">{{movie.subject}}</div>
                    <youtube :video-id="movie.video_id" ref="youtube" @buffering="bufferingMovie($event, movie.id)" @playing="playingMovie($event, 1, movie.id)" :playerVars="playerVars" width="210" height="210"></youtube>
                </div>
            </div>
            <div class="other-movie" @click="showMovieList" v-if="this.$refs">
                <i class="fas fa-caret-right" style="vertical-align:middle;font-size: 30px; color: rgba(var(--vs-primary),1);"></i>
                <div class="text">その他の動画をチェック</div>
            </div>
        </vx-card>
    
        <vs-popup class="movie-list-popup" title="動画一覧" :active.sync="movieListView">
            <div class="movie-content">
                <div class="theme-list">
                    <div class="theme-item" :class="themeId === theme.id ? 'selected' : ''" @click="selectTheme(theme.id, theme.theme_name)" v-for="theme in themeList">
                        <span>{{theme.theme_name}}</span>
                    </div>
                </div>
    
                <div class="label">再生回数</div>
                <div class="movie-list">
                    <div class="movie-item" v-for="movie in listMovieTop"
                         :key="movie.id">
                        <youtube :video-id="movie.video_id" ref="youtube" @buffering="bufferingMovie($event, movie.id)" @playing="playingMovie($event, 2, movie.id)" :playerVars="playerVars" width="210" height="210"></youtube>
                        <div class="movie-info">
                            <div class="title">{{movie.subject}}</div>
                            <div class="theme-title">{{movie.theme_name}}</div>
                            <div class="play-count">{{movie.play_count}}ビュー</div>
                        </div>
                    </div>
                </div>
    
                <div class="label">{{themeName}}</div>
                <div class="movie-list">
                    <div class="movie-item" v-for="movie in listMovieAll"
                         :key="movie.id">
                        <youtube :video-id="movie.video_id" ref="youtube" @buffering="bufferingMovie($event, movie.id)" @playing="playingMovie($event, 3, movie.id)" :playerVars="playerVars" width="210" height="210"></youtube>
                        <div class="movie-info">
                            <div class="title">{{movie.subject}}</div>
                            <div class="theme-title">{{movie.theme_name}}</div>
                            <div class="play-count">{{movie.play_count}}ビュー</div>
                        </div>
                    </div>
                </div>
                <vx-pagination :total="pagination.totalPage" :currentPage.sync="pagination.currentPage"></vx-pagination>
            </div>
        </vs-popup>
    </div>
</template>
<script>
import Vue from 'vue';
import { mapState, mapActions } from "vuex";
import VxCard from '../vx-card/VxCard.vue';
import VxPagination from '@/components/vx-pagination/VxPagination.vue';
import config from "../../app.config";
import VueYoutube, { getIdFromUrl } from 'vue-youtube';
import {PORTAL_COMPONENT} from '../../enums/portal_component';

Vue.use(VueYoutube);

export default {
    components: {
        VxCard,
        VxPagination,
        getIdFromUrl,
    },
    name: "Movie",
    props: [],
    created() {
    },
    mounted() {
        this.onSearch();
        this.initMovieList();
        this.movieListView = false;
    },
    data() {
        return {
            defaultBanner: `${config.LOCAL_API_URL}/images/no-preview.png`,
            baseUrl: `${config.LOCAL_API_URL}`,
            playerVars: {
                autoplay: 0,
                loop: 0,
                enablejsapi: 1,
                iv_load_policy: 3,
                rel: 0,
                origin: `${config.LOCAL_API_URL}`,
            },
            listMovie: [],
            movieListView: false,
            listMovieAll: [],
            listMovieTop: [],
            themeList: [
                {id: 0, theme_name: '全て'}
            ],
            themeId: 0,
            themeName: 'すべての動画',
            pagination: {
                totalPage: 0,
                currentPage: 1,
                limit: 9,
                totalItem: 0,
                from: 1,
                to: 9
            },
            playIdList: [],
            isPlaying: false,
            play_position: 0,
            playId: 0,
        }
    },
    methods: {
        ...mapActions({
            getListMovie: "movie/getListMovie",
            getMovieTheme: "movie/getMovieTheme",
            getListMovieTop: "movie/getListMovieTop",
            addPlayCount: "movie/addPlayCount",
        }),
        getVideoId (url) {
            return getIdFromUrl(url)
        },
        onSearch: async function () {
            const params = {
                location_type: '1,2',
                mst_movie_id: null,
                mst_company_id: null,
                mst_department_id: null,
                mst_position_id: null
            }
            const data = await this.getListMovie(params);
            if (data) {
                if (data.data) {
                    let listMovie = Object.values(data.data);
                    listMovie.filter(item => {
                        item.video_id = this.getVideoId(item.movie_url);
                    })
                    this.listMovie = listMovie;
                    if (this.listMovie.length > 0) {
                        this.$emit('changeHasData', PORTAL_COMPONENT.MOVIE, true)
                    } else {
                        this.$emit('changeHasData', PORTAL_COMPONENT.MOVIE, false)
                    }
                }
            }
        },
        loadThemeList: async function () {
            const params = {
                mst_movie_id: null,
                mst_company_id: null,
                mst_department_id: null,
                mst_position_id: null,
            }
            const data = await this.getMovieTheme(params);
            if (data) {
                this.themeList.push(...data);
            }
        },
        onSearchList: async function () {
            this.stopAll();
            const params = {
                location_type: '1,2,3',
                theme_id: this.themeId,
                mst_movie_id: null,
                mst_company_id: null,
                mst_department_id: null,
                mst_position_id: null,
                limit: this.pagination.limit,
                page: this.pagination.currentPage,
            }
            const data = await this.getListMovie(params);
            if (data) {
                this.pagination.totalItem   = data.total;
                this.pagination.totalPage   = data.last_page;
                if (this.pagination.totalPage !== data.last_page
                    && this.pagination.currentPage > data.last_page
                    && data.notices.last_page > 0
                ){
                    this.pagination.currentPage = data.last_page;
                }
                this.pagination.totalPage   = data.last_page;
                this.pagination.limit       = data.per_page;
                this.pagination.from        = data.from;
                this.pagination.to          = data.to;
                if (data.data) {
                    let listMovieAll = Object.values(data.data);
                    listMovieAll.filter(item => {
                        item.video_id = this.getVideoId(item.movie_url);
                    })
                    this.listMovieAll = listMovieAll;
                }
            }
        },
        onSearchTopList: async function () {
            this.stopAll();
            const params = {
                location_type: '1,2,3',
                show_num: 3,
                theme_id: this.themeId,
                mst_movie_id: null,
                mst_company_id: null,
                mst_department_id: null,
                mst_position_id: null,
            }
            const data = await this.getListMovieTop(params);
            if (data) {
                let listMovieTop = Object.values(data);
                listMovieTop.filter(item => {
                    item.video_id = this.getVideoId(item.movie_url);
                })
                this.listMovieTop = listMovieTop;
            }
        },
        initMovieList: function () {
            this.themeId = 0;
            this.themeName = 'すべての動画';
            this.pagination = {
                totalPage: 0,
                currentPage: 1,
                limit: 9,
                totalItem: 0,
                from: 1,
                to: 9
            };
            this.listMovieAll = [];
            this.listMovieTop = [];
            this.themeList = [
                {id: 0, theme_name: '全て'}
            ];
            this.isPlaying = false;
            this.play_position = 0;
            this.playId = 0;
        },
        showMovieList: async function () {
            this.initMovieList();
            this.loadThemeList();
            this.onSearchTopList();
            this.onSearchList();
            this.movieListView = true;
        },
        selectTheme: async function (themeId, themeName) {
            this.themeId = themeId;
            if (themeId === 0) themeName = 'すべての動画';
            this.themeName = themeName;
            if (this.pagination.currentPage !== 1) {
                this.pagination.currentPage = 1;
            } else {
                await this.onSearchList();
            }
            await this.onSearchTopList();
        },
        bufferingMovie(e, movie_id) {
            // if (this.isPlaying && movie_id == this.playId) return false;
            // this.pauseMovie();
            // this.isPlaying = true;
            // this.playId = movie_id;
            // e.playVideo();
        },
        playingMovie(e, position, movie_id) {
            const playIdList = this.playIdList;
            let has_movie_id = playIdList.find(item=> {
                return item === movie_id;
            })
            if (!has_movie_id) {
                this.playIdList.push(movie_id);
                const params = {
                    theme_id: this.themeId,
                    mst_movie_id: movie_id,
                    mst_company_id: null,
                    mst_department_id: null,
                    mst_position_id: null,
                }
                this.addPlayCount(params);
            }

            if (this.isPlaying && movie_id == this.playId && position == this.play_position) return false;
            this.pauseMovie();
            this.isPlaying = true;
            this.play_position = position;
            this.playId = movie_id;
            setTimeout(()=> {
                e.playVideo();
            }, 200)
        },
        pauseMovie() {
            if (this.players) {
                this.players.filter(item=> {
                    if (item.player) item.player.pauseVideo();
                })
            }
        },
        stopAll() {
            this.isPlaying = false;
            this.play_position = 0;
            this.playId = 0;
            if (this.players) {
                this.players.filter(item=> {
                    if (item.player) item.player.stopVideo();
                })
            }
        },
    },
    computed: {
        players() {
            return this.$refs.youtube
        },
    },
    watch: {
        'pagination.currentPage': function (val) {
            this.onSearchList();
        },
        'movieListView': function (val) {
            if (!val) {
                if (this.players) {
                    this.players.filter(item=> {
                        if (item.player) item.player.stopVideo();
                    })
                }
                setTimeout(() => {
                    this.initMovieList();
                }, 1000)
            }
        },
    }

}
</script>

<style lang="scss">
.movie-list-popup .vs-popup {
    width: 780px !important;
    .label {
        font-size: 1.6rem;
        line-height: 2.8rem;
        font-weight: 400;
        color: #333333;
    }
    padding-bottom: 20px;
    .movie-content {
        margin: auto;
        width: 720px;
    }
}
.movie-list-popup .con-vs-popup .vs-popup--content {
    padding: 8px;
}
.movie-list-popup .movie-list {
    display: flex;
    width: 100%;
    flex-flow: wrap;
    border-bottom: 4px solid #EFEFEF;
    margin-bottom: 10px;
    justify-content: left;
    .title {
        font-size: 1.2rem;
        line-height: 2.2rem;
        font-weight: 400;
        max-height: 4.4rem;
        overflow: hidden;
        display: block;
        -webkit-line-clamp: 2;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        text-overflow: ellipsis;
        white-space: normal;
    }
    .theme-title {
        color: #a1a1a1;
        font-family: "Roboto","Arial",sans-serif;
        font-size: 1rem;
        line-height: 1.4rem;
        font-weight: 400;
        max-height: 4rem;
    }
    .play-count {
        font-family: "Roboto","Arial",sans-serif;
        font-size: 1rem;
        line-height: 1.4rem;
        font-weight: 400;
        max-height: 4rem;
        overflow: hidden;
        display: block;
        -webkit-line-clamp: 2;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        text-overflow: ellipsis;
        white-space: normal;
        display: -ms-flexbox;
        display: -webkit-flex;
        display: flex;
    }
}

.movie-list-popup .movie-list:last-child {
    border-bottom: 0;
}
.movie-list-popup .movie-list .movie-item {
    margin: 15px;
    > div:nth-child(1):not(.movie-info) {
        width: 210px;
        height: 210px;
        background: #EFEFEF;
    }
}
.movie-list .movie-item > iframe {
    background: #EFEFEF;
}
.movie-list-popup .movie-info {
    background: rgba(255,255,255,.7);
    border-bottom-left-radius: 7px;
    border-bottom-right-radius: 7px;
    width: 210px;
}
.movie-list-popup .theme-list {
    line-height: 30px;
    display: flex;
    display: -webkit-flex;
    flex-wrap: wrap;
    align-items:center;
    -webkit-align-items: center;
    justify-content: left;
    -webkit-justify-content: flex-start;
    
    .theme-item {
        padding: 0px 15px;
        background: #EFEFEF;
        border-radius: 20px;
        max-width: 280px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        cursor: pointer;
        border: 1px solid #CCCCCC;
        margin-right: 20px;
        margin-bottom: 12px;
    }
    .theme-item.selected {
        background: #0984E3;
        border-color: #0984E3;
        color: #FFF;
    }
}
</style>
