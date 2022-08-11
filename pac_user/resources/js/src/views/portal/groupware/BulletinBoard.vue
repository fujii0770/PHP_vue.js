<template>
    <div id="bulletin_board" :class="isMobile?'mobile':''">
     <vs-row>
<!--        <div class="mr-2 leftpanel">-->
<!--            <menu-left></menu-left>-->
<!--        </div>-->
        <div class="bbs">
            <top-menu v-if="!isMobile"></top-menu>
            <bbs></bbs>
        </div>
    </vs-row>
    </div>
</template>
<script>

import TopMenu from "../../../components/portal/TopMenu";
    import Bbs from "../../../components/portal/Bbs";
    export default {
        components: {
            Bbs,
            TopMenu
        },
        data() {
            return {
                heightBbs: 0,
                widthBbs: 0,
                isMobile: false
            }
        },
        computed: {

        },
        methods: {

        },

        created() {
          if (
            /phone|iPhone|iPad|Android|BlackBerry|IEMobile|MQQBrowser|JUC|Fennec|wOSBrowser|BrowserNG|WebOS|Symbian|Windows Phone/i.test(
              navigator.userAgent
            )
          ) {
            this.isMobile = true
          }
        },

        mounted() {
 //           this.heightBbs = $(".iframe-groupware").outerHeight();
//            this.widthBbs = $(".iframe-groupware").outerWidth();
        }
    }

</script>
<style lang="scss">
  .iframe-groupware{
      height: calc(100vh - 87px);
  }

  #bulletin_board.mobile{
    width: 100%;

    .bbs{
      width: 100%;

      #bbs .con-vs-card{
        padding: 10px;
      }
    }

    .topic_menubar, .category_menubar{
      padding: 10px 0 10px 10px;
      height: 50px; 
    }

    .category_menubar{
      height: 60px;
    }

    table{
      tr{
        display: table;
        width: 100%;

        td{
          width: 50%;
        }
      }
    }

    .addCategory {
      margin-top: 10px;
      padding: 1rem;
      white-space: nowrap;
    }

  }
  @media (max-width: 480px){
    #bulletin_board.mobile{
      padding: 0 1.2rem;
      
      .comment_action{
        margin-top: 10px;
      }
    }
  }
  // PAC_5-3017 Start
  //@media( min-width:481px ){
  //  .main-vertical.navbar-sticky .router-content{
  //    margin-top: 120px;
  //  }
  //}
  // PAC_5-3017 End
</style>
