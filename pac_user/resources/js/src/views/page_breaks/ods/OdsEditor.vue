<template>
  <OdsMainPreview
    v-if="sheetno != -1"
    v-bind:preview-json="previewJson"
    v-bind:sheetno="sheetno"
    v-bind:break-positions.sync="postedBreakJson"
    v-bind:scale="magnification / 100"
  ></OdsMainPreview>
</template>

<script>
import { mapState, mapActions } from "vuex";
import OdsMainPreview from "./OdsMainPreview.vue";
import pageBreaksService from "../../../services/pageBreaks.service";

export default {
  name: "ods-editor",
  components: {
    OdsMainPreview,
  },
  data: function () {
    return {
      // APIのレスポンス
      previewJson: null,
      // 表示シート番号
      // sheetno: -1, // -1 = 表示シートなし
      // 編集中の改ページ位置
      postedBreakJson: {},
    };
  },
  computed: {
    ...mapState({
      fileInfoList: (state) => state.pageBreaks.fileInfoList,
    }),
    // 全シート番号の入った配列を返す
    sheets: function () {
      if (!this.previewJson) {
        return [];
      }

      return Object.keys(this.previewJson.sheetCells).map((x) => parseInt(x));
    },
    magnification: function () {
      return this.fileInfoList[this.$vnode.key].magnification;
    },
    // sheetno: {
    //   get() {
    //     return this.fileInfoList[this.$vnode.key].sheetno;
    //   },
    //   set(value) {
    //     this.changeSheetnoByIndex({index: this.$vnode.key, sheetno: value});
    //   },
    // },
    sheetno: function () {
      return this.fileInfoList[this.$vnode.key].sheetno;
    },
  },
  async mounted() {
    await this.fetchPreview();
  },
  methods: {
    ...mapActions({
      changeSheetnoByIndex: "pageBreaks/changeSheetnoByIndex",
      setSheetsByIndex: "pageBreaks/setSheetsByIndex",
      odsPreview: "pageBreaks/odsPreview",
    }),
    // 編集中の改ページ位置をAPIから取得したものに戻す
    reset: function () {
      this.initPostedBreakJson();
    },
    // プレビュー用情報取得
    fetchPreview: async function () {
      const preview = await this.odsPreview(this.fileInfoList[this.$vnode.key].serverFilename);
      if (preview) {
        this.handleApiResponse(preview);
      }
    },
    // APIから取得したデータをセット
    handleApiResponse: function (json) {
      this.previewJson = json;
      this.initPostedBreakJson();
      // 最初のシートに設定
      // this.sheetno = this.sheets.length == 0 ? -1 : this.sheets[0];
      // this.changeSheetnoByIndex(this.$vnode.key, this.sheets.length == 0 ? -1 : this.sheets[0]);
      this.changeSheetnoByIndex({
        index: this.$vnode.key,
        sheetno: this.sheets.length == 0 ? -1 : this.sheets[0],
      });
      this.setSheetsByIndex({ index: this.$vnode.key, sheets: this.sheets });
    },
    // APIから取得したデータを、postedBreakJsonへセット
    initPostedBreakJson: function () {
      // previewJson.pageBreak は直接変更しない（リセットの際に参照するため）
      const pageBreakJson = JSON.stringify(this.previewJson.pageBreak);
      this.postedBreakJson = {};
      this.postedBreakJson = JSON.parse(pageBreakJson);
    },
    initCurrentSheetPostedBreakJson: function () {
      // previewJson.pageBreak は直接変更しない（リセットの際に参照するため）
      const pageBreakJson = JSON.stringify(this.previewJson.pageBreak);
      const newPostedBreakJson = JSON.parse(
        JSON.stringify(this.postedBreakJson)
      );
      newPostedBreakJson[this.sheetno] =
        JSON.parse(pageBreakJson)[this.sheetno];
      this.postedBreakJson = {};
      this.postedBreakJson = newPostedBreakJson;
    },
    // exportPdf: function () {
    //   // 改ページ位置設定→PDF生成
    //   (async () => {
    //     const param = { breaks: this.postedBreakJson };
    //     const promise = fetch("http://172.16.10.71/py/ods_update.py", {
    //       method: "POST",
    //       body: JSON.stringify(param),
    //     });
    //     const json = await util.handleFetch(promise);
    //     if (json.error) {
    //       alert(json.error);
    //     } else {
    //       // PDF出力・表示
    //       window.open("http://172.16.10.71/py/export.py", "_blank");
    //     }
    //   })();
    // },
  },
};
</script>

<style lang="scss" scoped>
</style>