<template>
    <!-- position: relative; は 子要素の offsetTop 取得のため -->
    <div ref="container" class="content" style="position: relative; text-align: center;">
        <div style="display: inline-block;">
            <div ref="pages"
                v-for="(page, index) in pages"
                :key="index" :data-index="index">

                <pdf-page-editor v-show="!!page.imageUrl" class="page"
                    ref="editors"
                    :data-index="index"
                    v-bind="commonEditorProperties"
                    :page="page.editorParam"
                    :expectedSize="expectedPagesSize[index]"
                    :imageUrl="page.imageUrl"
                    :isVisible="pagesVisibility[index]"
                    @generateStamp="$emit('generateStamp', $event)"
                    @area-selected="$emit('area-selected', {index: index, points: $event})"
                    @addStickyNote="addStickyNote"
                >
                </pdf-page-editor>

                <pdf-page-skeleton v-show="!page.imageUrl" class="page"
                    :loading="page.loading"
                    :expectedSize="expectedPagesSize[index]" :imageScale="imageScale">
                </pdf-page-skeleton>
            </div>
        </div>
    </div>
</template>

<script>

import PdfPageEditor from "../../components/home/PdfPageEditor";
import PdfPageSkeleton from "../../components/home/PdfPageSkeleton";

export default {
  name: 'pdf-pages',
  components: {
    PdfPageEditor,
    PdfPageSkeleton,
  },
  props: {
    expectedPagesSize   : { type: Array, required: true },
    pages               : { type: Array, required: true },
    rotateAngle         : { type: Number, default: 0 },
    opacity             : { type: Number, default: 0.5 },
    imageScale          : { type: Number, required: true },
    deleteFlg           : { type: Boolean, default: false},
    deleteWatermark     : { type: String, default: ''},
    isPublic            : { type: Boolean, default: false},
    enable              : { type: Boolean, default: true},
    areaSelectMode      : { type: Boolean, default: false },
    stamps              : { type: Array},
  },
  data() {
    return {
      pagesVisibility: [],
      intersectionObserver: null,
    };
  },
  computed: {
    commonEditorProperties() {
      // 共通プロパティ
      return {
        rotateAngle: this.rotateAngle,
        opacity: this.opacity,
        imageScale: this.imageScale,
        deleteFlg: this.deleteFlg,
        deleteWatermark: this.deleteWatermark,
        isPublic: this.isPublic,
        enable: this.enable,
        stamps: this.stamps,
        areaSelectMode: this.areaSelectMode,
      };
    },
    visiblePageRange() {
      const visiblePageStart = this.pagesVisibility.findIndex(x => x);
      const remainingVisibilities = this.pagesVisibility.slice(visiblePageStart);
      const firstInvisibleIndex = remainingVisibilities.findIndex(x => !x);
      const visibleLength = firstInvisibleIndex == -1 ? remainingVisibilities.length : firstInvisibleIndex;
      return [visiblePageStart, visiblePageStart + visibleLength];
    },
    pageCount() {
      return this.expectedPagesSize.length;
    },
  },
  mounted() {
    this.$once('hook:beforeDestroy', () => {
      this.intersectionObserver?.disconnect();
    });
  },
  methods: {
    // 付箋を追加する
    addStickyNote(stickyNote) {
      // PdfPageEditor.vue の addStickyNoteメソッドを呼び出す
      this.$refs.editors[0].addStickyNote(stickyNote);
    },
    // 付箋編集
    editStickyNote(edit_id) {
      this.$refs.editors[0].callAddStickNoteDialog(edit_id)
    },
    showStickyNote() {
      this.$refs.editors[0].showStickyNotePreview();
    },
    intersectionObserve() {
      this.pagesVisibility = new Array(this.pageCount).fill(false);

      this.intersectionObserver?.disconnect();

      // PdfPageEditor それぞれで IntersectionObserver を作り監視させると
      // 非表示になっても callback が呼ばれないことがあった (スクロール時の負荷による？)
      // ※ Chrome, Firefox で確認

      // init
      this.intersectionObserver = new IntersectionObserver((changedEntries) => {
        changedEntries.forEach(entry => {
          const index = entry.target.dataset.index; // :data-index
          const isVisible = entry.isIntersecting;

          if (this.pagesVisibility[index] != isVisible) {
            this.$set(this.pagesVisibility, index, isVisible);
          }
        });
      });

      this.$nextTick(() => {
        // observe
        const elements = this.$refs.pages ?? [];
        for (const el of elements) {
          this.intersectionObserver.observe(el);
        }
      });
    },
    addStampsConfirmation: function(pageIndex) {
      const editor = this.$refs.editors.find(x => x.$el.dataset.index == pageIndex); // :data-index
      if (editor) {
        editor.AddStampsConfirmation();
      } else {
        // 何もしない
      }
    },
    jumpTo(pageIndex) {
      // scroll
      const pageElement = this.$refs.pages[pageIndex];
      this.$refs.container.scrollTop = pageElement.offsetTop;
    },
    jumpToTop() {
      const container = this.$refs.container;
      if (container) {
        container.scrollTop = 0;
      }
    }
  },
  watch: {
    expectedPagesSize: {
      handler() {
        this.intersectionObserve();
        this.jumpToTop();
      },
      immediate: true
    },
    visiblePageRange(newVal) {
      this.$emit("visible-page-changed", newVal);
    },
  }
}

</script>
