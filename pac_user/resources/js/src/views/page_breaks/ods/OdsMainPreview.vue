<template>
  <div>
    <canvas
      ref="shown"
      class="shown"
      v-bind:width="shownWidth + 1"
      v-bind:height="shownHeight + 1"
      v-on:mousemove="mousemove"
      v-on:click="click"
    ></canvas>
    <canvas
      ref="hidden"
      class="preview-holder hidden"
      v-bind:width="previewWidth"
      v-bind:height="previewHeight"
    ></canvas>
  </div>
</template>

<script>
export default {
  name: "ods-main-preview",
  props: {
    // コンポーネント生成時にすべて必要

    // 表示するシートのデータのみを与えるようにしてもよい
    // APIのレスポンス
    previewJson: Object,
    // 表示中シート番号
    sheetno: Number,

    // 編集中の改ページ位置情報
    breakPositions: Object,
    // 倍率
    scale: Number,
  },
  data: function () {
    return {
      // 強調表示用 マウスに近いセル区切りの情報
      mouseoverLine: null,
    };
  },
  computed: {
    // シートプレビュー描画Canvasのサイズ
    previewWidth: function () {
      return Math.max(...this.cellPixels.column.values(), 0);
    },
    previewHeight: function () {
      return Math.max(...this.cellPixels.row.values(), 0);
    },
    // 表示Canvas
    // scale: function () {
    //     // 最大サイズ - 1
    //     const maxWidth = 800 - 1;
    //     const maxHeight = 600 - 1;
    //     return Math.min(maxWidth / this.previewWidth, maxHeight / this.previewHeight);
    // },
    // 表示Canvasのサイズ - 1
    // 表示Canvas上のシートプレビューのサイズ
    shownWidth: function () {
      return this.previewWidth * this.scale;
    },
    shownHeight: function () {
      return this.previewHeight * this.scale;
    },
    sheetAreas: function () {
      const printAreas = this.previewJson.areas.filter(
        (x) => x.sheetno == this.sheetno
      );
      return printAreas;
    },
    // シートプレビュー描画時のセル区切り位置
    // APIから得たセルの大きさから生成
    cellPixels: function () {
      const cellSize = this.previewJson.sheetCells[this.sheetno];

      // 変換用
      const dpi = 96;
      const inch = 2540;
      const toPixel = (length) => (length / inch) * dpi;

      // 離れたセルの間をどれだけ空けるか
      const dividerLength = 400;

      // 左端、右端は高さ方向の処理の場合は上端、下端の意味
      // {番号: その左端 (px)}
      // 印刷範囲の右端位置も特定できる
      const toMap = (lengths) => {
        // ソート＆keyはintに変換
        const entries = Object.entries(lengths)
          .sort(([cellnoA], [cellnoB]) => cellnoA - cellnoB)
          .map(([k, v]) => [parseInt(k), v]);

        let currentPos = 0; // 1/100mm

        const map = new Map();
        entries.forEach((x, index) => {
          const [cellno, length] = x;

          // 前のセル
          const pre = entries[index - 1];
          if (pre) {
            const [preCellno] = pre;
            // 前のセルと離れているか？
            const preNextCellno = preCellno + 1;
            const isNextCell = preNextCellno == cellno;
            if (!isNextCell) {
              // 離れていれば前のセルの右端を追加
              map.set(preNextCellno, toPixel(currentPos));
              currentPos += dividerLength;
            }
          }

          // 処理中セルの左端を追加
          map.set(cellno, toPixel(currentPos));
          currentPos += length;
        });

        // 右端を追加
        const totalPx = toPixel(currentPos);
        const [lastCellno] = entries[entries.length - 1];
        map.set(lastCellno + 1, totalPx);
        return map;
      };

      return {
        row: toMap(cellSize.rowHeights),
        column: toMap(cellSize.columnWidths),
      };
    },
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
    // 表示Canvas
    getCanvas: function () {
      return this.$refs.shown;
    },
    // シートプレビュー描画Canvas
    getPreviewCanvas: function () {
      return this.$refs.hidden;
    },
    // events
    mousemove: function (event) {
      // 一番近い区切り線を求める
      const rect = this.getCanvas().getBoundingClientRect();
      const [x, y] = [event.clientX - rect.left, event.clientY - rect.top];

      const getNearestLine = (map, mousePos) => {
        const entries = map.entries();

        let lower; // 左側/上側
        let entry = entries.next();
        while (!entry.done && entry.value[1] < mousePos) {
          lower = entry.value;
          entry = entries.next();
        }
        // 右側/下側
        const higher = entry.done ? null : entry.value;

        if (!lower) {
          return higher;
        } else if (!higher) {
          return lower;
        } else {
          const lowerDistance = mousePos - lower[1];
          const higherDistance = higher[1] - mousePos;
          // [index, 区切り線の位置]
          return lowerDistance < higherDistance ? lower : higher;
        }
      };

      const cellMouseX = x / this.scale;
      const cellMouseY = y / this.scale;
      const nearestX = getNearestLine(this.cellPixels.column, cellMouseX);
      const nearestY = getNearestLine(this.cellPixels.row, cellMouseY);

      // 行列どちらが近いか
      const isColumn =
        Math.abs(nearestX[1] - cellMouseX) < Math.abs(nearestY[1] - cellMouseY);

      this.mouseoverLine = {
        direction: isColumn ? "column" : "row",
        lineInfo: isColumn ? nearestX : nearestY,
      };
    },
    click: function (event) {
      const mouseLine = this.mouseoverLine;
      if (mouseLine) {
        // クリック位置追加/削除
        const [cellIndex] = mouseLine.lineInfo;
        const sheetno = this.sheetno;
        const breakList = this.breakPositions[sheetno][mouseLine.direction];

        const nameMap = {
          column: "columnWidths",
          row: "rowHeights",
        };

        const sheetCells = this.previewJson.sheetCells[sheetno];
        const sizeMap = sheetCells[nameMap[mouseLine.direction]];

        // 非表示セルの周りをクリックした時に対応
        // 解除時→その範囲に入りうる改ページをすべて解除
        // 設定時→最後の非表示セルの後ろへ設定
        const isInvisible = (size) => size === 0;

        // sizeMapの何番～何番のセル区切りがあるか
        // 非表示セルの周りであれば start != end
        const start = (() => {
          let i = cellIndex;
          while (isInvisible(sizeMap[i - 1])) {
            i--;
          }
          return i;
        })();

        const end = (() => {
          let i = cellIndex;
          while (isInvisible(sizeMap[i])) {
            i++;
          }
          return i;
        })();

        // 設定されていれば解除する
        let isReleased = false;
        for (let i = start; i <= end; i++) {
          const listIndex = breakList.indexOf(i);
          if (listIndex != -1) {
            breakList.splice(listIndex, 1);
            isReleased = true;
          }
        }

        if (!isReleased) {
          // 1つも解除していない = セットされていなかった
          // 設定する
          breakList.push(end);
        }

        // 親へ更新通知
        this.$emit("update:breakPositions", this.breakPositions);
      }
    },
    // 表示Canvas全クリア
    clearShown: function () {
      const canvas = this.getCanvas();
      const ctx = canvas.getContext("2d");

      ctx.clearRect(0, 0, canvas.width, canvas.height);
    },
    // 表示Canvasへ描画
    drawShown: function () {
      const canvas = this.getCanvas();
      const ctx = canvas.getContext("2d");

      const scale = this.scale;

      const drawWidth = this.shownWidth;
      const drawHeight = this.shownHeight;
      // 画像
      ctx.drawImage(this.getPreviewCanvas(), 0, 0, drawWidth, drawHeight);

      // セル間の区切り線が細く見えるように lineWidth と strokeRect の幅を0.3にする
      ctx.lineWidth = 0.3;
      // セル区切り線
      ctx.strokeStyle = "rgb(211,211,211)";
      for (const item of this.cellPixels.column) {
        const pos = item[1] * scale;
        ctx.strokeRect(pos, 0, 0.3, drawHeight);
      }
      for (const item of this.cellPixels.row) {
        const pos = item[1] * scale;
        ctx.strokeRect(0, pos, drawWidth, 0.3);
      }
      // 外枠、改ページ線、マウス上の線を太くする
      ctx.lineWidth = 3;
      // 印刷範囲を囲む四角
      ctx.strokeStyle = "blue";
      for (const area of this.sheetAreas) {
        const [x, y, w, h] = (() => {
          const x = this.cellPixels.column.get(area.startColumn);
          const w = this.cellPixels.column.get(area.endColumn + 1) - x;
          const y = this.cellPixels.row.get(area.startRow);
          const h = this.cellPixels.row.get(area.endRow + 1) - y;
          return [x, y, w, h];
        })().map((x) => x * scale);

        ctx.strokeRect(x + 1, y + 1, w - 1, h - 1);
      }
      // 編集中の改ページ線
      ctx.strokeStyle = "blue";
      const sheetsBreak = this.breakPositions[this.sheetno];
      for (const index of sheetsBreak.row) {
        const linePos = this.cellPixels.row.get(index) * scale;
        ctx.strokeRect(0, linePos, drawWidth, 1);
      }
      for (const index of sheetsBreak.column) {
        const linePos = this.cellPixels.column.get(index) * scale;
        ctx.strokeRect(linePos, 0, 1, drawHeight);
      }
      // マウスに一番近い線
      ctx.strokeStyle = "rgb(105,105,105)";
      const mouseLine = this.mouseoverLine;
      if (mouseLine) {
        const pos = mouseLine.lineInfo[1] * scale;
        if (mouseLine.direction == "column") {
          ctx.strokeRect(pos, 0, 1, drawHeight);
        } else {
          // row
          ctx.strokeRect(0, pos, drawWidth, 1);
        }
      }
      // 線の太さを元に戻す
      ctx.lineWidth = 1;
    },
    // シートプレビュー描画Canvasへ描画
    generateSheetPreview: async function () {
      const previewCanvas = this.getPreviewCanvas();
      const ctx = previewCanvas.getContext("2d");

      const columnMap = this.cellPixels.column;
      const rowMap = this.cellPixels.row;
      for (const area of this.sheetAreas) {
        // 位置
        const x = columnMap.get(area.startColumn);
        const y = rowMap.get(area.startRow);
        const w = columnMap.get(area.endColumn + 1) - x;
        const h = rowMap.get(area.endRow + 1) - y;
        // 画像配置
        const promise = this.toImage(area.image).then((img) => {
          ctx.drawImage(img, x, y, w, h);
        });
        await promise;
      }
      this.clearShown();
      this.drawShown();
    },
  },
  created: function () {
    // porps変更時に画面更新するため
    this.$watch(
      function () {
        return [
          this.$props.sheetno,
          this.$props.previewJson,
          this.$props.scale,
        ];
      },
      function () {
        this.generateSheetPreview(); // <= promise
      }
    );
  },
  mounted: function () {
    // 初期化
    this.generateSheetPreview();
  },
  watch: {
    // 状態変化時に画面更新するため
    breakPositions: function () {
      this.drawShown();
    },
    mouseoverLine: function () {
      this.drawShown();
    },
  },
};
</script>

<style lang="scss" scoped>
.hidden {
  display: none;
}
</style>