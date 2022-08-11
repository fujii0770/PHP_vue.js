<template>
  <div>
    <modal name="add-note-modal"
           :pivot-y="0.2"
           :width="450"
           :classes="['v--modal', 'upload-modal', 'p-6']"
           :height="'auto'"
           :clickToClose="false">
      <vs-row>
        <vs-col vs-w="8" vs-type="flex" vs-align="center">
          <span class="mb-2 pb-2 span-title">付箋の種類</span>
        </vs-col>
        <vs-col vs-w="4" vs-type="flex" vs-align="flex-start" vs-justify="flex-end">
          <vs-button radius type="flat" class="close-button" v-on:click="cancelNoteModel"><i class="fas fa-times"></i>
          </vs-button>
        </vs-col>
      </vs-row>
      <vs-row vs-type="flex" vs-align="flex-start" vs-justify="flex-start" style="padding: 5px 10px"
              :class="'note-list '">
        <vs-row vs-type="flex" vs-align="flex-start" vs-justify="flex-start" style="padding: 5px 10px">
          <draggable>
            <vs-col vs-w="3" v-for="note in noteDataBig" :key="note.id">
              <div class="wrap-item row-equal-height"
                   style="margin-bottom: 15px; display: flex; justify-content: center;">
                <div :class="'note-item ' + (note.id == noteOneSelected ? 'selected': 'unSelected')"
                     @click="selectNoteOne(note.id)">
                  <img :src="note.src" width="64" alt="">
                </div>
              </div>
            </vs-col>
            <vs-col vs-w="12" style="margin-top: -25px;padding: 10px">
              <span>※４０文字以内で文字を入力してください。</span></vs-col>
            <vs-col vs-w="3" v-for="note in noteDataSmall" :key="note.id">
              <div class="wrap-item row-equal-height"
                   style="margin-bottom: 15px; display: flex; justify-content: center;">
                <div :class="'note-item ' + (note.id == noteOneSelected ? 'selected': 'unSelected')"
                     @click="selectNoteOne(note.id)">
                  <img :src="note.src" width="64" alt="">
                </div>
              </div>
            </vs-col>
            <vs-col vs-w="12" style="margin-top: -25px;padding: 10px">
              <span>※１０文字以内で文字を入力してください。</span></vs-col>
          </draggable>
        </vs-row>
      </vs-row>
      <vs-row>
        <vs-col vs-w="12" vs-type="flex" vs-align="center">
          <span class="mb-2 pb-2 span-title">入力テキスト</span>
        </vs-col>
        <vs-col vs-w="12">
          <vs-textarea placeholder="付箋コメント" rows="4" maxlength="100"
                       v-model="stickyNotes.note_text"/>
        </vs-col>
        <vs-col vs-w="12" vs-type="flex" vs-align="left">
          <span class="text-danger text-sm" v-show="showDialogErrorMsg">{{ showDialogErrorMsg }}</span>
        </vs-col>
        <vs-col vs-w="12" style="text-align:center">
          <vs-button class="square comment-input" color="primary" type="filled" v-on:click="addStickyNote()">
            付箋を追加
          </vs-button>
        </vs-col>
      </vs-row>
    </modal>
  </div>
</template>

<script>

import draggable from 'vuedraggable'
import { mapActions, mapState } from 'vuex'

export default {
  components: { draggable },
  props: [
    'note_format',
    'note_text',
    'note_index',
  ],
  data () {
    return {
      noteDataBig: [
        { id: 1, src: require('@assets/images/stickyNotes/big/yellow.svg') },
        { id: 2, src: require('@assets/images/stickyNotes/big/red.svg') },
        { id: 3, src: require('@assets/images/stickyNotes/big/green.svg') },
        { id: 4, src: require('@assets/images/stickyNotes/big/blue.svg') },
      ], noteDataSmall: [
        { id: 5, src: require('@assets/images/stickyNotes/small/sm-yellow.svg') },
        { id: 6, src: require('@assets/images/stickyNotes/small/sm-red.svg') },
        { id: 7, src: require('@assets/images/stickyNotes/small/sm-green.svg') },
        { id: 8, src: require('@assets/images/stickyNotes/small/sm-blue.svg') },
      ],

      stickyNotes: {
        note_format: this.note_format,
        note_text: this.note_text,
        note_index: this.note_index,
      },
      showDialogErrorMsg: '',
    }
  },
  watch: {},

  methods: {
    ...mapActions({
      selectNoteOneItem: 'home/selectNoteOneItem',
      addSticky: 'home/addSticky',
      editSticky: 'home/editSticky',
    }),

    cancelNoteModel: function () {
      this.stickyNotes.note_format = ''
      this.stickyNotes.note_text = ''
      this.stickyNotes.note_index = ''
      // 付箋を追加→右側の「コメント/付箋」タブをクリック→文書をクリックすると添付画像のようになりました
      // 2重で表示されているような感じに見えます
      $("#stick-note-append").children(0).remove();
      // this.$modal.hide('add-note-modal')
      this.$store.commit('home/homeUnSelectNote')
    },

    selectNoteOne (noteId) {
      this.selectNoteOneItem(noteId)
      this.stickyNotes.note_format = noteId
    },

    // 付箋を追加する
    addStickyNote () {
      if (!this.fileSelected) {
        this.showDialogErrorMsg = '回覧文書を選択してください。'
        return
      } else if (!this.stickyNotes.note_format) {
        this.showDialogErrorMsg = '付箋の種類を選択してください。'
        return
      } else if (!this.stickyNotes.note_text) {
        this.showDialogErrorMsg = '付箋コメントを入力してください。'
        return
      }

      let input = this.stickyNotes.note_text || '';
      let input_length = input.replace(/\n/g, '').length;
      if (this.stickyNotes.note_format <= 4 && input_length > 40) {
        this.showDialogErrorMsg = '40文字以内で文字を入力してください。'
        return
      } else if (this.stickyNotes.note_format > 4 && input_length > 10) {
        this.showDialogErrorMsg = '10文字以内で文字を入力してください。'
        return
      }

      if (this.stickyNotes.note_index === '') {
        //新規
        let stickyPosition = this.$store.state.home.stickyPosition
        let sticky = {
          id: 0,
          page_num: stickyPosition.page_num,
          left: stickyPosition.left,
          top: stickyPosition.top,
          note_format: this.stickyNotes.note_format,
          note_text: this.stickyNotes.note_text,
          removed_flg: 0,
          deleted_flg: 0,
          is_author: 1,
        }
        this.addSticky(sticky)
      } else {
        //編集
        this.editSticky(this.stickyNotes)
      }
      this.cancelNoteModel()
    },
  },
  computed: {
    ...mapState({
      fileSelected: state => state.home.fileSelected,
      noteOneSelected: state => state.home.noteOneSelected,
    }),
  },
  created () {
    this.selectNoteOneItem(this.note_format)
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

.span-title {
  font-size: 15px;
}

.close-button {
  font-size: 18px;
  position: absolute;
  top: 10px;
  right: 0;
}

.stick-note-item .vs-button--text {
  display: flex;
  justify-content: center;
  align-items: center;
}

</style>