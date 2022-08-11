<template>
  <!-- 付箋大 -->
  <div
      class="text-options cursor-move stickynote"
      ref="stickyNote"
      style="width:160px"
      draggable
      @dragstart="cursorPosition($event)"
      @dragend="changePosition($event)"
      v-show="!removed_flg && !deleted_flg"
      @dblclick="editSelf(edit_id)"
  >
    <img v-if="note_format=='1'"
         :src="require('@assets/images/stickyNotes/big/yellow.svg')"
         alt=""
    >
    <img v-if="note_format=='2'"
         :src="require('@assets/images/stickyNotes/big/red.svg')"
         alt=""
    >
    <img v-if="note_format=='3'"
         :src="require('@assets/images/stickyNotes/big/green.svg')"
         alt=""
    >
    <img v-if="note_format=='4'"
         :src="require('@assets/images/stickyNotes/big/blue.svg')"
         alt=""
    >
    <img v-if="note_format=='5'"
         :src="require('@assets/images/stickyNotes/small/sm-yellow.svg')"
         alt=""
    >
    <img v-if="note_format=='6'"
         :src="require('@assets/images/stickyNotes/small/sm-red.svg')"
         alt=""
    >
    <img v-if="note_format=='7'"
         :src="require('@assets/images/stickyNotes/small/sm-green.svg')"
         alt=""
    >
    <img v-if="note_format=='8'"
         :src="require('@assets/images/stickyNotes/small/sm-blue.svg')"
         alt=""
    >
    <!-- 付箋コメント -->
    <span :class="'stickynote-text ' +(note_format<5?'span-format-big':'span-format-small')" v-bind:title="comment">{{ comment }}</span>
    <!-- 削除ボタン -->
    <a class="button delete stickynote-delete" @click="destroySelf(edit_id)" v-show="can_operate"><i
        class="fas fa-times"></i></a>
  </div>
</template>

<script>

export default {
  props: [
    'comment',
    'edit_id',
    'note_format',
    'removed_flg',
    'deleted_flg',
    'can_operate',
    'callAddStickNoteDialog',
  ],
  data () {
    return {
      chgLeft: 0,
      chgTop: 0,
      message: ''
    }
  },
  methods: {
    // Drag&Dropで付箋の位置を変更する
    changePosition (event) {
      const stickyNote = this.$refs.stickyNote
      // 親コンポーネントを取得
      const parent = stickyNote.parentNode
      // 座標を取得
      const parentClientRect = parent.getBoundingClientRect()
      // カーソル位置に付箋の中心を持ってくるようにstyleを変更
      // レスポンシブ対応のため、「px」ではなく「%」で設定
      let movedX = (event.clientX - parentClientRect.left - this.chgLeft) / parentClientRect.width
      let movedY = (event.clientY - parentClientRect.top - this.chgTop) / parentClientRect.height
      if (movedX < 0 || movedX > 1 || movedY < 0 || movedY > 1) {
        //ページ間ドラッグは許可されていません
        return
      }
      stickyNote.style.left = `${movedX * 100}%`
      stickyNote.style.top = `${movedY * 100}%`
      let data = {
        edit_id: this.edit_id,
        left: stickyNote.style.left,
        top: stickyNote.style.top,
      }
      this.$store.commit('home/homeUpdateStickyPosition', data)
    },
    // Drag&Dropで付箋の位置を変更する
    cursorPosition (event) {
      const stickyNote = this.$refs.stickyNote
      // 親コンポーネントを取得
      const stickyClientRect = stickyNote.getBoundingClientRect()
      this.chgLeft = event.clientX - stickyClientRect.left
      this.chgTop = event.clientY - stickyClientRect.top
    },
    // 自身のコンポーネントを削除する
    destroySelf (edit_id) {
      this.$store.commit('home/homeDeleteSticky', edit_id)
      this.$destroy()
      this.$el.parentNode.removeChild(this.$el)
    },
    editSelf (edit_id) {
      if (this.can_operate) {
        this.callAddStickNoteDialog(edit_id)
      }
    },
  },
  computed: {}
}
</script>

<style scoped>
.stickynote {
  position: absolute;
  top: 30%;
  left: 50%;
}

.stickynote img {
  width: 100%;
}

.stickynote-text {
  position: absolute;
  top: 20%;
  left: 10%;
  right: 10%;
  word-break: break-all;
  text-align: left;
  white-space: pre-wrap;
  overflow: hidden;
}

.span-format-big {
  height: 60%;
  top: 15%;
}

.span-format-small {
  height: 50%;
  top: 20%;
}

.stickynote-delete {
  position: absolute;
  top: -2%;
  right: 5%;
}

.stickynote-break {
  word-break: break-all;
}
</style>