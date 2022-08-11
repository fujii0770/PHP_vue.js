<template>
  <OdtMainPreview
    v-bind:previewJson="previewJson"
    v-bind:scale="magnification / 100"
    @pageChangeRequest="onPageChangeRequest"
  ></OdtMainPreview>
</template>

<script>
import { mapState } from "vuex";
import OdtMainPreview from "./OdtMainPreview.vue";
import pageBreaksService from "../../../services/pageBreaks.service";
import config from "../../../app.config";

export default {
  name: "odt-editor",
  components: {
    OdtMainPreview,
  },
  data: function () {
    return {
      // APIのレスポンス
      previewJson: null,
    };
  },
  computed: {
    ...mapState({
      fileInfoList: (state) => state.pageBreaks.fileInfoList,
    }),
    magnification: function () {
      return this.fileInfoList[this.$vnode.key].magnification;
    },
  },
  async mounted() {
    await this.fetchPreview();
  },
  methods: {
    // プレビュー用情報取得
    fetchPreview: async function () {
      const result = await pageBreaksService
        .odtPreview(this.fileInfoList[this.$vnode.key].serverFilename)
        .then(
          (response) => {
            return response.data;
          },
          (error) => {
            return false;
          }
        );
      if (!result || result.error) {
        alert("プレビューデータの取得に失敗しました。");
      } else {
        this.previewJson = result;
      }
    },
    // odtMainPreviewで改ページ操作がされた
    onPageChangeRequest: async function (event) {
      const json = await pageBreaksService
        .odtUpdate(this.fileInfoList[this.$vnode.key].serverFilename, event)
        .then(
          (response) => {
            return response.data;
          },
          (error) => {
            return {error: error};
          }
        );

      const messageMap = {
        invalid: "無効な指定です。",
        found_original_break: "改ページがあるため移動できませんでした。",
        not_moved:
          "指定範囲を移動できませんでした。\n※より狭い範囲であれば移動できる可能性があります。",
      };

      if (json.error) {
        alert(json.error);
      } else {
        const result = json.result;
        if (result == "moved") {
          // 移動できた場合はプレビューを更新
          await this.fetchPreview();
        } else {
          const message = messageMap[result] || result;
          alert(message);
        }
      }
    },
    // アップロード時点の状態に戻し、プレビュー再読み込み
    reset: async function () {
      const errorMessage = await pageBreaksService
        .odtReset(this.fileInfoList[this.$vnode.key].serverFilename)
        .then(
          (_response) => {
            return null;
          },
          (error) => {
            return error;
          }
        );

      if (errorMessage) {
        alert("改ページリセットに失敗しました。");
      } else {
        await this.fetchPreview();
      }
    },
    // exportPdf: function () {
    //   // PDF生成
    //   window.open("http://172.16.10.71/py/export.py", "_blank");
    // },
  },
};
</script>

<style lang="scss" scoped>
</style>