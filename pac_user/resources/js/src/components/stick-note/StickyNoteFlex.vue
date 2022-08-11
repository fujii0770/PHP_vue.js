<template>
  <div>
    <vs-row v-for="(tempStickyArray,index) in showStickNotes" :key="index"
            v-show="tempStickyArray!== undefined && tempStickyArray!== null && tempStickyArray.length>0">
      <vs-col vs-w="11">{{ index }} ページ目</vs-col>
      <vs-row v-for="(tempSticky,indexItem) in tempStickyArray" :key="indexItem" v-show="!tempSticky.deleted_flg">
        <vs-col vs-w="3">
          <div :class="'note-item ' + (tempSticky.stickIndex === isSelectedSticky ? 'selected': 'unSelected')"
               @dblclick="findClickSticky(tempSticky,index)">
            <img v-if="tempSticky.note_format=='1'"
                 :src="require('@assets/images/stickyNotes/big/yellow.svg')"
                 alt="" height="30" width="50"
            >
            <img v-if="tempSticky.note_format=='2'"
                 :src="require('@assets/images/stickyNotes/big/red.svg')"
                 alt="" height="30" width="50"
            >
            <img v-if="tempSticky.note_format=='3'"
                 :src="require('@assets/images/stickyNotes/big/green.svg')"
                 alt="" height="30" width="50"
            >
            <img v-if="tempSticky.note_format=='4'"
                 :src="require('@assets/images/stickyNotes/big/blue.svg')"
                 alt="" height="30" width="50"
            >
            <img v-if="tempSticky.note_format=='5'"
                 :src="require('@assets/images/stickyNotes/small/sm-yellow.svg')"
                 alt="" height="30" width="50"
            >
            <img v-if="tempSticky.note_format=='6'"
                 :src="require('@assets/images/stickyNotes/small/sm-red.svg')"
                 alt="" height="30" width="50"
            >
            <img v-if="tempSticky.note_format=='7'"
                 :src="require('@assets/images/stickyNotes/small/sm-green.svg')"
                 alt="" height="30" width="50"
            >
            <img v-if="tempSticky.note_format=='8'"
                 :src="require('@assets/images/stickyNotes/small/sm-blue.svg')"
                 alt="" height="30" width="50"
            >
          </div>
        </vs-col>
        <vs-col vs-w="8" class="ellipsis">{{ tempSticky.note_text }}</vs-col>
        <vs-col vs-w="1">
          <vs-dropdown vs-trigger-click style="font-family: inherit !important;"
                       v-show="can_operate_view">
            <a class=""><i class="fa fa-bars"></i></a>
            <vs-dropdown-menu>
              <vs-dropdown-item >
                <vs-button color="primary" class="stick-note-item" type="border"
                           @click="showHideStickyNote(tempSticky.stickIndex)">
                  <i :class="tempSticky.removed_flg ? 'fa fa-eye':'fa fa-eye-slash'"></i>
                  &nbsp;&nbsp;{{ tempSticky.removed_flg ? '表示' : '非表示' }}
                </vs-button>
              </vs-dropdown-item>
              <vs-dropdown-item v-if="tempSticky.is_author">
                <vs-button color="primary" class="stick-note-item" type="border"
                           v-on:click="$emit('editStickyNoteParent', tempSticky.stickIndex)"><i class="fa fa-edit"></i>&nbsp;&nbsp;編集
                </vs-button>
              </vs-dropdown-item>
              <vs-dropdown-item v-if="tempSticky.is_author">
                <vs-button color="primary" class="stick-note-item" type="border"
                           @click="deleteStickyNote(tempSticky.stickIndex)"><i
                    class="fa fa-trash"></i>&nbsp;&nbsp;削除
                </vs-button>
              </vs-dropdown-item>
            </vs-dropdown-menu>
          </vs-dropdown>
        </vs-col>
      </vs-row>
    </vs-row>
  </div>
</template>

<script>

export default {
  components: {},
  props: [
    'showStickNotes'
  ],
  data () {
    return {
      can_operate_view: true,
      isSelectedSticky: -1,
    }
  },

  methods: {
    // 自身のコンポーネントを削除する
    deleteStickyNote (edit_id) {
      this.$store.commit('home/homeDeleteSticky', edit_id)
    },
    // 表示非表示
    showHideStickyNote (edit_id) {
      this.$store.commit('home/homeShowHideSticky', edit_id)
    },
    findClickSticky (stick, pageNo) {
      this.isSelectedSticky = stick.stickIndex
      // スクロール高さの計算
      let top_px = $('.content').scrollTop() + $('div[data-index=' + (pageNo - 1) + ']:not(.page)').offset().top
          - $('.content').offset().top + $('div[data-index=' + (pageNo - 1) + ']:not(.page)').height() * parseFloat(stick.top) / 100

      $('.content').animate({ 'scrollTop': top_px > 100 ? top_px - 100 : top_px }, 500)
    }
  },
  created () {
    this.can_operate_view = !['sent_detail', 'completed_detail', 'received_view'].includes(this.$route.name)
  },
}
</script>
<style scoped>
.ellipsis {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.stick-note-item {
  min-width: 150px;
}

.stick-note-item .vs-button--text {
  display: flex;
  justify-content: center;
  align-items: center;
}

</style>