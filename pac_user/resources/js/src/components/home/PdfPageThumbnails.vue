<template>
    <!-- position: relative; は 子要素の offsetTop 取得のため -->
    <div class="preview-list" ref="container" style="position:relative;">
        <div v-for="(thumbnail, index) in thumbnails"
            ref="pages" class="page" :class="{ selected: index == selectedIndex }"
            :key="index" :data-index="index"
            @click="onClick(index)">
            <pdf-page-thumbnail :size="thumbnailImagesSize[index]"
                                :thumbnail="thumbnail">
            </pdf-page-thumbnail>
        </div>
    </div>
</template>

<script>

import PdfPageThumbnail from "../../components/home/PdfPageThumbnail";

export default {
  name: 'pdf-page-thumbnails',
  components: {
    PdfPageThumbnail,
  },
  props: {
    thumbnailImagesSize : { type: Array, required: true },
    thumbnails          : { type: Array, required: true },
    selectedIndex       : { type: Number },
  },
  data() {
    return {
      pagesVisibility: [],
      intersectionObserver: null,
    };
  },
  computed: {
    visiblePageRange() {
      const visiblePageStart = this.pagesVisibility.findIndex(x => x);

      const remainingVisibilities = this.pagesVisibility.slice(visiblePageStart);
      const firstInvisibleIndex = remainingVisibilities.findIndex(x => !x);
      const visibleLength = firstInvisibleIndex == -1 ? remainingVisibilities.length : firstInvisibleIndex;

      return [visiblePageStart, visiblePageStart + visibleLength];
    },
    pageCount() {
      return this.thumbnailImagesSize.length;
    },
  },
  mounted() {
    this.$once('hook:beforeDestroy', () => {
      this.intersectionObserver?.disconnect();
    });
  },
  methods: {
    intersectionObserve() {
      this.pagesVisibility = new Array(this.pageCount).fill(false);

      this.intersectionObserver?.disconnect();

      // 子要素それぞれで IntersectionObserver を作り監視させると
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
    jumpTo(pageIndex) {
      const pageElement = this.$refs.pages[pageIndex];
      // + 1 は 一つ上のサムネイルが表示扱いになるのを防ぐため
      this.$refs.container.scrollTop = pageElement.offsetTop + 1;
    },
    jumpToTop() {
      const container = this.$refs.container;
      if (container) {
        container.scrollTop = 0;
      }
    },
    onClick(index) {
      this.$emit("click", index);
    }
  },
  watch: {
    thumbnailImagesSize: {
      handler() {
        // file changed
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
