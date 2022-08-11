<template>
    <div class="bm-pagination">
        <div class="bm-pagination--nav" :val="totalPage" v-if="totalPage >= 1">
            <button
                    class="bm-pagination--buttons btn-prev-pagination bm-pagination--button-prev"
                    :class="currentPage <= 1 ? 'disabled' : ''"
                    @click="currentPage <= 1 ? '' : turn(currentPage - 1)"
            >
                <i class="notranslate icon-scale material-icons null">chevron_left</i>
            </button>
            <ul class="bm-pagination--ul">
                <li
                        v-for="i in showPageBtn"
                        class="bm-pagination--li"
                        :class="i == currentPage ? 'is-current' : ''"
                        v-if="i > 0"
                        @click="turn(i)"
                        :key="i"
                >
                    <span>{{ i }}</span>
                    <div class="effect"></div>
                </li>
                <li class="bm-pagination--li" v-else-if="i === '-'">
                    <span>...</span>
                    <div class="effect"></div>
                </li>
                <li class="bm-pagination--li" v-else-if="i === '+'">
                    <span>...</span>
                    <div class="effect"></div>
                </li>
            </ul>
            <button
                    class="bm-pagination--buttons btn-next-pagination bm-pagination--button-next"
                    :class="currentPage >= totalPage ? 'disabled' : ''"
                    @click="currentPage >= totalPage ? '' : turn(currentPage + 1)"
            >
                <i class="notranslate icon-scale material-icons null">chevron_right</i>
            </button>
        </div>
    </div>
</template>
<script>
    export default {
        name: "vx-pagination",
        props: {
            currentPage: {
                type: Number,
                default: 1,
            },
            total: {
                type: Number,
                default: 0,
            },            
            totalSmall: {
                type: Number,
                default: 0,
            },
        },
        data() {
            return {
                goPage: "",
                toPrev: false,
                toNext: false,
            };
        },
        created: function () {},
        computed: {
            totalPage() {
                //return Math.ceil(this.total / this.limit);
                if(this.totalSmall){
                    return this.totalSmall;
                }else{
                    return this.total;
                }
            },            
            totalPagesSmall() {
                return this.totalSmall;
            },

            showPageBtn() {
                let pageNum = Number(this.totalPage),
                    pageEspeciallyNum = Number(this.totalSmall),
                    index = Number(this.currentPage),
                    arr = [];
                if ((!pageEspeciallyNum && pageNum <= 3) || (pageEspeciallyNum && pageEspeciallyNum <= 3)) {
                    for (let i = 1; i <= pageNum; i++) {
                        arr.push(i);
                    }
                    return arr;
                }
                if(pageEspeciallyNum > 0){
                    if (index < 2 || index > pageNum - 1){
                        arr = [
                            1,
                            "+",
                            pageNum,
                        ];
                        return arr;
                    }
                    if (index == 2){
                        arr = [
                            1,
                            index,
                            "+",
                            pageNum,
                        ];
                        return arr;
                    }
                    if (index == 3){
                        arr = [
                            1,
                            index - 1,
                            index,
                            "+",
                            pageNum,
                        ];
                        return arr;
                    }
                    if (index == pageNum - 1){
                        arr = [
                            1,
                            "-",
                            index,
                            pageNum,
                        ];
                        return arr;
                    }
                    if (index == pageNum - 2){
                        arr = [
                            1,
                            "-",
                            index,
                            index + 1,
                            pageNum,
                        ];
                        return arr;
                    }
                    arr = [
                        1,
                        "-",
                        index,
                        "+",
                        pageNum,
                    ];
                    return arr;
                }
                if (index < 4 || index > pageNum - 3)
                    return [
                        1,
                        2,
                        3,
                        4,
                        "+",
                        pageNum - 3,
                        pageNum - 2,
                        pageNum - 1,
                        pageNum,
                    ];
                if (index == 4)
                    return [
                        1,
                        "-",
                        index - 1,
                        index,
                        index + 1,
                        index + 2,
                        index + 3,
                        "+",
                        pageNum,
                    ];
                if (index == pageNum - 3)
                    return [
                        1,
                        "-",
                        index - 3,
                        index - 2,
                        index - 1,
                        index,
                        index + 1,
                        "+",
                        pageNum,
                    ];
                return [
                    1,
                    "-",
                    index - 2,
                    index - 1,
                    index,
                    index + 1,
                    index + 2,
                    "+",
                    pageNum,
                ];
            },
        },
        methods: {
            turn(page) {
                let i = parseInt(Number(page));
                if (i < 1) {
                    i = 1;
                } else if (i > this.totalPage) {
                    i = this.totalPage;
                }
                //this.$emit("turnToPage",i);
                this.$emit("update:currentPage",i);
            },
        },
    };
</script>
<style lang="scss">
    .bm-pagination {
        --color-pagination: #f0f0f0;
        --color-pagination-alpha: #f0f0f0;

        .bm-pagination--nav {
            display: flex;
            display: -webkit-box;
            display: -ms-flexbox;
            -webkit-box-align: center;
            align-items: center;
            -webkit-box-pack: center;
            -ms-flex-pack: center;
            justify-content: center;

            .bm-pagination--ul {
                padding: 0;
                background: #f0f0f0;
                border-radius: 20px;
                display: -webkit-box;
                display: -ms-flexbox;
                display: flex;
                -webkit-box-align: center;
                -ms-flex-align: center;
                justify-content: center;

                .bm-pagination--li {
                    cursor: pointer;
                    width: 35px;
                    height: 35px;
                    display: -webkit-box;
                    display: -ms-flexbox;
                    display: flex;
                    -webkit-box-align: center;
                    -ms-flex-align: center;
                    align-items: center;
                    -webkit-box-pack: center;
                    -ms-flex-pack: center;
                    justify-content: center;
                    border-radius: 8px;
                    -webkit-transition: all 0.25s ease;
                    transition: all 0.25s ease;
                    position: relative;
                    -webkit-backface-visibility: visible;
                    backface-visibility: visible;
                    margin: 0 2px;
                    font-weight: 700;
                    color: rgba(0, 0, 0, 0.5);

                    span {
                        z-index: 100;
                    }

                    .effect {
                        z-index: 50;
                        content: "";
                        position: absolute;
                        width: 100%;
                        height: 100%;
                        left: 0;
                        top: 0;
                        display: block;
                        border-radius: 8px;
                        -webkit-transition: all 0.2s ease;
                        transition: all 0.2s ease;
                        -webkit-box-shadow: 0 0 20px 0 transparent;
                        box-shadow: 0 0 20px 0 transparent;
                    }
                }

                .bm-pagination--li.is-current {
                    border-radius: 50%;
                    -webkit-transform: scale(1.05);
                    transform: scale(1.05);
                    color: #fff;
                    font-weight: 700;
                    cursor: default;
                    background: rgba(var(--vs-primary),1);
                }
            }
            .bm-pagination--buttons {
                cursor: pointer;
                width: 35px;
                height: 35px;
                border-radius: 50%;
                border: 0;
                cursor: pointer;
                display: -webkit-box;
                display: -ms-flexbox;
                display: flex;
                -webkit-box-align: center;
                -ms-flex-align: center;
                align-items: center;
                -webkit-box-pack: center;
                -ms-flex-pack: center;
                justify-content: center;
                -ms-flex-negative: 0;
                flex-shrink: 0;
                color: rgba(0, 0, 0, 0.6);
                -webkit-transition: all 0.2s ease;
                transition: all 0.2s ease;
                background: #f0f0f0;
                margin: 0;
                z-index: 200;
            }
            .bm-pagination--buttons:hover {
                background: rgba(var(--vs-primary),1);
                color: #fff;
            }
            .bm-pagination--buttons.bm-pagination--button-prev {
                margin-right: 5px;
            }
            .bm-pagination--buttons.bm-pagination--button-next {
                margin-left: 5px;
            }
            .bm-pagination--buttons.disabled {
                cursor: default;
                pointer-events: none;
                opacity: 0.5;
            }
        }
    }

</style>