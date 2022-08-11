<template>
  <div id="page-breaks">
    <vs-card>
      <vs-row>
        <div id="preview_area">
          <vs-tabs v-model="currentTabIndex" v-if="fileInfoList.length > 0">
            <vs-tab
              :key="index"
              :label="fileInfo.filename"
              v-for="(fileInfo, index) in fileInfoList"
            >
            </vs-tab>
          </vs-tabs>
          <div
            class="preview-wrapper"
            :key="index"
            v-show="fileInfo.active"
            v-for="(fileInfo, index) in fileInfoList"
          >
            <OdsEditor
              v-if="fileInfo.filetype === 'ods'"
              :key="index"
              ref="editor"
            ></OdsEditor>
            <OdtEditor v-else :key="index" ref="editor"></OdtEditor>
          </div>
        </div>
        <div class="right-toolbar">
          <vs-row
            vs-type="flex"
            vs-justify="flex-start"
            vs-align="space-between"
            vs-w="12"
            style="padding: 10px; position: relative"
          >
            <vs-button
              size="small"
              class=""
              color="primary"
              type="filled"
              v-if="currentFiletype === 'ods'"
              @click="resetPageBreak"
              >改ページリセット<br />(全シート)</vs-button
            >
            <vs-button
              class=""
              color="primary"
              type="filled"
              v-else
              @click="resetPageBreak"
              >改ページリセット
            </vs-button>
            <vs-button
              size="small"
              class=""
              color="primary"
              type="filled"
              v-if="currentFiletype === 'ods'"
              @click="resetCurrentSheetPageBreak"
              >改ページリセット<br />(表示中のシート)
            </vs-button>
          </vs-row>
          <vs-row
            v-if="currentFiletype === 'ods'"
            vs-type="flex"
            vs-justify="flex-start"
            vs-align="space-between"
            vs-w="12"
            style="padding: 10px; position: relative"
          >
            <vs-select
              label="シート選択"
              class="select-sheet"
              v-model.number="sheetno"
            >
              <vs-select-item
                :key="index"
                :value="index"
                :text="Number(index) + 1"
                v-for="index in sheets"
              />
            </vs-select>
          </vs-row>
          <vs-row
            vs-type="flex"
            vs-justify="flex-start"
            vs-align="space-between"
            vs-w="12"
            style="padding: 10px; position: relative"
          >
            <vs-input-number :step="5" label="倍率:" v-model="magnification">
            </vs-input-number>
          </vs-row>
          <vs-row
            vs-type="flex"
            vs-justify="flex-start"
            vs-align="space-between"
            vs-w="12"
            style="padding: 10px; position: relative"
          >
            <vs-button
              class=""
              color="danger"
              type="filled"
              @click="onRejectPageBreaks"
              >{{
                $store.state.pageBreaks.circularDocIdBeforeMod
                  ? "改ページ調整中止"
                  : "アップロード中止"
              }}</vs-button
            >
            <vs-button
              class=""
              color="primary"
              type="filled"
              @click="decidePageBreaks"
              >決定</vs-button
            >
          </vs-row>
        </div>
      </vs-row>
    </vs-card>
  </div>
</template>
<script>
import { mapState, mapActions } from "vuex";
import InfiniteLoading from "vue-infinite-loading";

import Utils from "../../utils/utils";

import config from "../../app.config";

import OdsEditor from "./ods/OdsEditor.vue";
import OdtEditor from "./odt/OdtEditor.vue";

export default {
  components: {
    InfiniteLoading,
    OdsEditor,
    OdtEditor,
  },
  data() {
    return {
      currentTabIndex: 0,
      previousRoute: null,// PAC_5-2242
    };
  },
  // PAC_5-2242 Start
  beforeRouteEnter(to, from, next) {
    next((vm) => {
      if (from.path != '/'){
        vm.previousRoute = from;
      }
    });
  },
  // PAC_5-2242 End
  async created() {
    // PAC_5-2242 Start
    const hash = this.$route.params.hash;
    if(hash) {
      localStorage.setItem('tokenPublic', hash);
      this.$store.commit('home/setUsingPublicHash', true);

      this.setFileInfoList([]);
      this.userInfo = await this.getInfoByHash();
      if (!this.userInfo || !this.userInfo.repage_preview_flg) {
        this.$router.go(-1);
      }
      this.emailTemplateOptions = Utils.setEmailTemplateOptions(this.userInfo);
      localStorage.setItem('envFlg', this.userInfo.current_env_flg);
    } else {
      if (!JSON.parse(getLS("user")).repage_preview_flg) {
        this.$router.go(-1);
      }
      this.setFileInfoList([]);
      this.userInfo = await this.getMyInfo();
    }
    // PAC_5-2242 End
    this.$store.commit('setting/setWithdrawalCaution', this.userInfo.withdrawal_caution);
    window.addEventListener("beforeunload", this.confirmSave);
    this.$store.commit('home/setCloseCheck', true );
  },
  async mounted() {
    await this.fetchTypeAndPreview();
  },
  computed: {
    ...mapState({
      fileInfoList: (state) => state.pageBreaks.fileInfoList,
      circular: state => state.home.circular,
    }),
    magnification: {
      get() {
        if (this.fileInfoList.length > 0) {
          return this.fileInfoList[this.currentTabIndex].magnification;
        }
        return null;
      },
      set(value) {
        this.changeActiveFileMagnification(value);
      },
    },
    sheetno: {
      get() {
        if (this.fileInfoList.length > 0) {
          return this.fileInfoList[this.currentTabIndex].sheetno;
        }
        return -1;
      },
      set(value) {
        this.changeSheetnoByIndex({
          index: this.currentTabIndex,
          sheetno: value,
        });
      },
    },
    sheets: function () {
      if (this.fileInfoList.length > 0) {
        return this.fileInfoList[this.currentTabIndex].sheets;
      }
      return [];
    },
    currentFiletype: function () {
      // 表示中のファイルタイプ: null か "ods" か "odt"
      if (this.fileInfoList.length > 0) {
        return this.fileInfoList[this.currentTabIndex].filetype;
      }
      return null;
    },
  },
  methods: {
    ...mapActions({
      setFileInfoList: "pageBreaks/setFileInfoList",
      changeActiveFileByIndex: "pageBreaks/changeActiveFileByIndex",
      changeActiveFileMagnification: "pageBreaks/changeActiveFileMagnification",
      changeActiveFileSheetno: "pageBreaks/changeActiveFileSheetno",
      changeSheetnoByIndex: "pageBreaks/changeSheetnoByIndex",
      setSheetsByIndex: "pageBreaks/setSheetsByIndex",
      getMyInfo: "user/getMyInfo",
      getInfoByHash: "user/getInfoByHash",
      setCircularDocIdAfterMod: "pageBreaks/setCircularDocIdAfterMod",
      uploadFilesForPageBreak: "pageBreaks/uploadFilesForPageBreak",
      rejectPageBreaks: "pageBreaks/rejectPageBreaks",
      decidePageBreaksBeforeAcceptUpload:
        "pageBreaks/decidePageBreaksBeforeAcceptUpload",
      decidePageBreaksAfterAcceptUpload:
        "pageBreaks/decidePageBreaksAfterAcceptUpload",
    }),
    onRejectPageBreaks: async function () {
      this.$store.commit('home/setCloseCheck', false );
      if (this.$store.state.pageBreaks.circularDocIdBeforeMod === null) {
        // DB登録済みではない場合は、アップロードファイルを削除する
        await this.rejectPageBreaks();
      }
      if (
        this.$route.query.create_new == "true" &&
        this.$route.query.circular_id
      ) {
        this.$router.push("/saves/" + this.$route.query.circular_id);
      // PAC_5-2242 Start
      } else {
        if (this.$route.params.hash && this.previousRoute) {
          this.previousRoute.query.back = true;
          if(this.circular){
            this.$router.push({name: this.previousRoute.name, params:{id: this.circular.id}});
          }else {
            this.$router.push(this.previousRoute);
          }
        } else {
          if(this.circular){
            this.$router.push({name: this.previousRoute.name, params:{id: this.circular.id}});
          }else{
            this.$router.push("/");
          }
        }
      }
      // PAC_5-2242 End
    },
    decidePageBreaks: async function () {
      this.$store.commit('home/setCloseCheck', false );

      const circularDocIdBeforeMod =
        this.$store.state.pageBreaks.circularDocIdBeforeMod;
      const breaks = this.$refs.editor[0].postedBreakJson
        ? this.$refs.editor[0].postedBreakJson
        : null;
      if (circularDocIdBeforeMod === null) {
        // homeのonCompletedUploadを参考にする
        this.$store.commit("home/checkCircularUserNextSend", false);
        const result = await this.decidePageBreaksBeforeAcceptUpload(breaks);
        if (!result) {
          // エラー発生
          return;
        }

        // 画面遷移
        this.setCircularDocIdAfterMod(result.fileInfo.circular_document_id);
        if (this.$route.query.create_new == "true") {
          this.$router.push("/saves/" + result.circular.id);
        // PAC_5-2242 Start
        } else {
          if (this.previousRoute) {
            this.previousRoute.query.back = false;
            this.$router.push(this.previousRoute);
          } else {
            this.$router.go(-1);
          }
        }
        // PAC_5-2242 End
      } else {
        // DB上のPDFデータ置き換え
        const result = await this.decidePageBreaksAfterAcceptUpload(breaks);
        if (!result) {
          // エラー発生
          return;
        }

        // 画面遷移
        this.setCircularDocIdAfterMod(circularDocIdBeforeMod);
        if (this.$route.query.create_new == "true") {
          this.$router.push(
            "/saves/" + this.$store.state.pageBreaks.circularIdForRegisteredDocs
          );
        // PAC_5-2242 Start
        } else {
          if (this.previousRoute) {
            this.previousRoute.query.back = false;
            this.$router.push(this.previousRoute);
          } else {
            this.$router.go(-1);
          }
        }
        // PAC_5-2242 End
      }
    },

    fetchTypeAndPreview: async function () {
      // 前画面でのアップロードファイル情報を取得する
      const targetFiles = this.$store.state.pageBreaks.uploadFileInfoList
        .filter(
          (uploadFile) => uploadFile.server_file_name_for_office_soft !== null
        )
        .map((targetFile) => {
          // const directoryForUpload = targetFile.server_file_path.slice(
          //   "uploads".length,
          //   -1 * targetFile.server_file_name.length
          // );
          return {
            name: targetFile.name,
            originFilename: targetFile.origin_file_name_for_office_soft,
            serverFilename: targetFile.server_file_name_for_office_soft,
            pdfFilename: targetFile.server_file_name,
            pdfFilepath: targetFile.server_file_path,
            // directoryForUpload: directoryForUpload,
          };
        });

      if (targetFiles.length === 0) {
        this.$router.go(-1);
        return;
      }

      // サーバー上のファイル情報を取得
      const editorType = await this.uploadFilesForPageBreak(targetFiles);
      if (editorType === null) {
        this.$router.go(-1);
      }
    },
    increment: function (value) {
      this.changeActiveFileMagnification(
        this.fileInfoList[this.currentTabIndex].magnification + value
      );
    },
    decrement: function (value) {
      this.changeActiveFileMagnification(
        this.fileInfoList[this.currentTabIndex].magnification - value
      );
    },
    resetPageBreak: function () {
      if (this.currentFiletype === "ods") {
        this.$refs.editor[this.currentTabIndex].initPostedBreakJson();
      }
      if (this.currentFiletype === "odt") {
        this.$refs.editor[this.currentTabIndex].reset();
      }
    },
    resetCurrentSheetPageBreak: function () {
      if (this.currentFiletype === "ods") {
        this.$refs.editor[
          this.currentTabIndex
        ].initCurrentSheetPostedBreakJson();
      }
    },
    confirmSave (event) {
      if( 1 != this.userInfo.withdrawal_caution ) {
        return;
      }
      if( 0 == this.$store.state.home.closeCheck ) {
        return;
      }
      event.returnValue = "編集中のものは保存されませんが、よろしいですか？";  /* Edge, Chromeではメッセージが表示されない */
    },
  },
  watch: {
    currentTabIndex: function (val) {
      this.changeActiveFileByIndex(val);
    },
  },
  destroyed() {
    window.removeEventListener("beforeunload", this.confirmSave);
  },
};
</script>

<style lang="scss" scoped>
#preview_area {
  width: calc(100% - 290px);
  text-align: center;
}

.preview-wrapper {
  overflow: auto;
  height: calc(100vh - 180px);
}

.right-toolbar {
  width: 290px;
}

.magnification-input {
  width: 100px;
}

button.magnification-button {
  padding: 0.4rem !important;
  margin-right: 0;
}
</style>
