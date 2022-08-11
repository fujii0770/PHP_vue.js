
<template>
  <div
    class="vs-sidebar--item"
    :class="[
      {'vs-sidebar-item-active'            : activeLink},
      {'disabled-item pointer-events-none' : isDisabled}
    ]" >

      <router-link
        v-if="to && activeLink"
        exact
        :class="[{'router-link-active': activeLink}]"
        :to="to"
        :target="target" @click.native="flushSelf">
          <vs-icon v-if="!featherIcon" :icon-pack="iconPack" :icon="icon" />
            <img v-else-if="customIcon" style="width:18px;height:18px;" :src="bindIcon(customIcon, activeLink)">
<!--          <feather-icon v-else :class="{'w-3 h-3': iconSmall}" :icon="icon" />-->
          <slot />
      </router-link>

      <router-link
              v-else-if="to"
              exact
              :class="[{'router-link-active': activeLink}]"
              :to="to"
              :target="target">
          <vs-icon v-if="!featherIcon" :icon-pack="iconPack" :icon="icon" />
          <img v-else-if="customIcon" style="width:18px;height:18px;" :src="bindIcon(customIcon, activeLink)">
<!--          <feather-icon v-else :class="{'w-3 h-3': iconSmall}" :icon="icon" />-->
          <slot />
      </router-link>

      <a v-else :target="target" :href="href">
        <vs-icon v-if="!featherIcon" :icon-pack="iconPack" :icon="icon" />
<!--        <feather-icon v-else :class="{'w-3 h-3': iconSmall}" :icon="icon" />-->
        <slot />
      </a>
  </div>
</template>

<script>
export default {
  name: 'v-nav-menu-item',
  props: {
    icon        : { type: String,                 default: ""               },
    iconSmall   : { type: Boolean,                default: false            },
    iconPack    : { type: String,                 default: 'material-icons' },
    href        : { type: [String, null],         default: '#'              },
    to          : { type: [String, Object, null], default: null             },
    slug        : { type: String,                 default: null             },
    index       : { type: [String, Number],       default: null             },
    featherIcon : { type: Boolean,                default: true             },
    target      : { type: String,                 default: '_self'          },
    isDisabled  : { type: Boolean,                default: false            },
    customIcon  : { type: String,                 default: ""               },
  },
  computed: {
    activeLink() {
      return ((this.to == this.$route.path) || (this.$route.meta.parent == this.slug) && this.to) ? true : false
    }
  },
    mounted() {
        if (window.document.documentMode) {
            $('.vs-sidebar--item svg').attr('viewBox', '0 0 8 24');
        }
    },
    methods: {
        flushSelf:function(){
            if(this.to == this.$route.path){
                this.$router.go(0);
            }
        },
        bindIcon(icon, active) {
            // 新規作成
            if(icon === "creation"){
                if(active){
                    return require("@assets/images/pages/home/creation_active.svg");
                }else{
                    return require("@assets/images/pages/home/creation.svg");
                }
            }else if(icon === "saved"){ // 下書き一覧
                if(active){
                    return require("@assets/images/pages/home/saved_active.svg");
                }else{
                    return require("@assets/images/pages/home/saved.svg");
                }
            }else if(icon === "received"){ // 受信一覧
                if(active){
                    return require("@assets/images/pages/home/received_active.svg");
                }else{
                    return require("@assets/images/pages/home/received.svg");
                }
            }else if(icon === "sent"){ // 送信一覧
                if(active){
                    return require("@assets/images/pages/home/sent_active.svg");
                }else{
                    return require("@assets/images/pages/home/sent.svg");
                }
            }else if(icon === "completed"){ // 完了一覧
                if(active){
                    return require("@assets/images/pages/home/completed_active.svg");
                }else{
                    return require("@assets/images/pages/home/completed.svg");
                }
            }else if(icon === "viewing"){ // 閲覧一覧
                if(active){
                    return require("@assets/images/pages/home/viewing_active.svg");
                }else{
                    return require("@assets/images/pages/home/viewing.svg");
                }
            }else if(icon === "document-search"){ // 長期保管
                if(active){
                    return require("@assets/images/pages/home/document_search_active.svg");
                }else{
                    return require("@assets/images/pages/home/document_search.svg");
                }
            }else if(icon === "download"){ // ダウンロード
                if(active){
                    return require("@assets/images/pages/home/download_active.svg");
                }else{
                    return require("@assets/images/pages/home/download.svg");
                }
            }else if(icon === "bizcard"){ // 名刺管理
                if(active){
                    return require("@assets/images/pages/home/bizcard_active.svg");
                }else{
                    return require("@assets/images/pages/home/bizcard.svg");
                }
            }else if(icon === "template"){ // 下書き一覧
                if(active){
                    return require("@assets/images/pages/home/template_active.svg");
                }else{
                    return require("@assets/images/pages/home/template.svg");
                }
            }else if(icon === "portal-icon"){ // ポータル
                if(active){
                    return require("@assets/images/pages/portal/myPage.svg");
                }else{
                    return require("@assets/images/pages/portal/myPage.svg");
                }
            }else if(icon === "templatecsv"){ // 回覧完了テンプレート一覧
                if(active){
                    return require("@assets/images/pages/home/template_csv_active.svg");
                }else{
                    return require("@assets/images/pages/home/template_csv.svg");
                }
            }else if(icon === "timesheet"){ // Timesheet
                if(active){
                    return require("@assets/images/pages/home/timesheet_active.svg");
                }else{
                    return require("@assets/images/pages/home/timesheet.svg");
                }
            }
        }
    }
}


</script>

