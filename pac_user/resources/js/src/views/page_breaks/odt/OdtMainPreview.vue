<template>
  <div class="odt-pages">
    <div v-for="(page, index) in pages" :key="index">
      <OdtPagePreview
        v-bind:page="page"
        v-bind:imgDrawScale="scale"
        v-bind:draggedFromBottom="dividerDragged[index]"
        v-bind:draggedFromTop="dividerDragged[index - 1]"
        @changeRequest="onPageChangeRequest(index, $event)"
      ></OdtPagePreview>
      <div
        v-if="index != pages.length - 1"
        class="odt-page-divider"
        @mousedown="dividerHoldStart(index)"
        ondragstart="return false"
      ></div>
    </div>
  </div>
</template>

<script>
import OdtPagePreview from "./OdtPagePreview.vue";

export default {
  name: "ods-main-preview",
  components: {
    OdtPagePreview,
  },
  props: {
    // APIのレスポンス
    previewJson: Object,
    scale: Number,
  },
  data: function () {
    return {
      // Canvas 最大の横幅
      // viewWidth: 0,
      // odtPagePreviewへ渡す
      // 描画時の倍率
      // scale: 0,
      // odtPagePreviewへ渡すオブジェクトの配列
      // previewJson.pagesと次の点を除いて同じ
      // * 画像がImage
      pages: [],
      // ページの間にある線がドラッグされているか？
      // [index]の下側の線がドラッグされていればtrue
      // 同時に1つの線のみがドラッグできるため配列で管理しなくてもよい
      dividerDragged: [],
    };
  },
  mounted: function () {
    // 初期化
    // this.viewWidth = 600;

    // どこで離しても離した扱いにするため document に設定
    document.addEventListener("mouseup", () => {
      this.dividerHoldEnd();
    });
  },
  methods: {
    toImage: (uri) => {
      // data URI scheme => Image
      const image = new Image();
      image.src = uri;
      return new Promise((resolve) => {
        image.addEventListener(
          "load",
          () => {
            resolve(image);
          },
          false
        );
      });
    },
    dividerHoldStart: function (index) {
      // index と index + 1 の間の線 がドラッグされ始めた
      const dragged = this.dividerDragged;
      // Vue.set(dragged, index, true);
      this.$set(dragged, index, true);
    },
    dividerHoldEnd: function () {
      // ドラッグ解除用
      // 全解除
      for (let i = 0; i < this.dividerDragged.length; i++) {
        // Vue.set(this.dividerDragged, i, false);
        this.$set(this.dividerDragged, i, false);
      }
    },
    // odtPagePreviewからのイベント
    onPageChangeRequest: function (index, event) {
      const isFromTop = event.type == "fromTop";
      const emitted = {
        page: isFromTop ? index - 1 : index, // 余白を変更するページ(上側のページ)
        type: isFromTop ? "increase" : "decrease", // 中身を増やすか減らすか
        row: event.num, // 通り過ぎた線の数
      };

      this.$emit("pageChangeRequest", emitted);
    },
  },
  watch: {
    previewJson: {
      handler: function () {
        (async () => {
          // this.previewJson.pages を this.pages へコピー
          // 画像はImageにする
          const toImagePromises = this.previewJson.pages.map((x) =>
            this.toImage(x.image)
          );
          const images = await Promise.all(toImagePromises);
          this.pages = this.previewJson.pages.map((x, i) => {
            const obj = Object.assign({}, x);
            obj.image = images[i];
            return obj;
          });

          // 横幅を収める倍率を計算
          // const maxImageWidth = images.reduce((max, currentImage) => Math.max(max, currentImage.width), 0);
          // this.scale = this.viewWidth / maxImageWidth;

          // ドラッグ全解除
          this.dividerDragged = [];
        })();
      },
    },
  },
};
</script>

<style lang="scss" scoped>
.odt-pages {
  padding: 0 15px;
  display: inline-block;
  // background: white;
}

.odt-page-divider {
  height: 8px;
  background: rgb(160, 220, 220);
  margin-left: auto;
  margin-right: auto;
  box-shadow: 0 0 3px 0 rgb(0 0 0 / 50%);
}
</style>