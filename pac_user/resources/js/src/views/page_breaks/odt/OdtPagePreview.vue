<template>
  <canvas
    v-bind:width="width"
    v-bind:height="height"
    class="odt-canvas"
    @mousemove="onmousemove"
    @mouseenter="onmouseenter"
    @mouseleave="onmouseleave"
    @mouseup="onmouseup"
  ></canvas>
</template>

<script>
export default {
  name: "odt-page-preview",
  props: {
    // Canvasへ描画時の倍率
    imgDrawScale: Number,
    // ページ情報
    page: Object,
    // 改ページ位置調整されようとしているか
    draggedFromBottom: Boolean,
    draggedFromTop: Boolean,
  },
  data: function () {
    return {
      // Canvas
      ctx: null,
      // Canvasの上にマウスカーソルが乗っているか
      isMouseOn: false,
      // マウス座標 (描画更新用)
      lastMouseY: -1,
    };
  },
  computed: {
    // Canvas 幅高さ計算
    width: function () {
      return this.page.image.width * this.imgDrawScale;
    },
    height: function () {
      return this.page.image.height * this.imgDrawScale;
    },
    // pages.rows の値と Canvas上の位置を対応させるための倍率
    rowPositionScale: function () {
      return this.height / this.page.height;
    },
    // マウス位置に線を表示するか
    shouldShowMouseLine: function () {
      return (this.draggedFromBottom || this.draggedFromTop) && this.isMouseOn;
    },
    // マウスが通り過ぎた区切り線(強調表示する線)のindex
    mouseReachRowIndex: function () {
      if (!this.shouldShowMouseLine) {
        return -1; // -> none
      }

      const rows = this.page.rows;
      const y = this.lastMouseY / this.rowPositionScale;

      const foundIndex = rows.findIndex((rowY) => rowY > y);
      if (this.draggedFromTop) {
        const overedIndex = foundIndex == -1 ? rows.length : foundIndex;
        return overedIndex - 1;
      } else {
        // draggedFromBottom
        return foundIndex;
      }
    },
  },
  mounted: function () {
    // 初期化
    this.ctx = this.$el.getContext("2d");
    this.draw();
  },
  updated: function () {
    // canvas サイズ変更時用
    this.draw();
  },
  methods: {
    draw: function () {
      // ページ画像描画
      this.ctx.drawImage(this.page.image, 0, 0, this.width, this.height);
      // 区切り線描画
      this.ctx.fillStyle = "blue";
      const rows = this.page.rows;
      for (const rowY of rows) {
        const drawY = rowY * this.rowPositionScale;
        this.ctx.fillRect(0, drawY, this.width, 1);
      }
      // マウス位置に線描画
      this.ctx.fillStyle = "rgb(160,220,220)";
      if (this.shouldShowMouseLine) {
        this.ctx.fillRect(0, this.lastMouseY - 2, this.width, 5);
      }
      // マウスが通り過ぎた区切り線(強調表示する線) 描画
      this.ctx.fillStyle = "blue";
      if (this.mouseReachRowIndex != -1) {
        const rowY = rows[this.mouseReachRowIndex];
        const drawY = rowY * this.rowPositionScale;
        this.ctx.fillRect(0, drawY - 4, this.width, 8);
      }
    },
    // マウス関連イベント
    onmousemove: function (event) {
      // lastMouseY を更新する必要があれば、更新する
      if (this.shouldShowMouseLine) {
        const rect = this.$el.getBoundingClientRect();
        const y = event.clientY - rect.top;
        this.lastMouseY = y;
      }
    },
    onmouseenter: function () {
      this.isMouseOn = true;
    },
    onmouseleave: function () {
      this.isMouseOn = false;
    },
    onmouseup: function () {
      const mouseReachRowIndex = this.mouseReachRowIndex;
      if (mouseReachRowIndex != -1) {
        let event;
        // event.num = 通り過ぎた区切り線の数
        if (this.draggedFromTop) {
          event = {
            num: mouseReachRowIndex + 1,
            type: "fromTop",
          };
        } else {
          // draggedFromBottom
          event = {
            num: this.page.rows.length - mouseReachRowIndex,
            type: "fromBottom",
          };
        }

        if (event.num > 0) {
          // 離した位置を送信
          this.$emit("changeRequest", event);
        }
      }
    },
  },
  watch: {
    // 状態変化時に画面更新するため
    lastMouseY: function () {
      this.draw();
    },
    shouldShowMouseLine: function () {
      this.draw();
    },
    page: function () {
      this.draw();
    },
  },
};
</script>

<style lang="scss" scoped>
.odt-canvas {
  display: block;
  margin: 16px auto;
  box-shadow: 0 0 10px 0 rgba(0, 0, 0, 0.5);
}
</style>