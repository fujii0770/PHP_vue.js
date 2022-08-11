<template>
    <div class="skeleton" :style="sizeStyle">
      <p :style="{ 'line-height' : sizeStyle.height }">
        <template v-if="loading">
          {{ stateText }}
        </template>
      </p>
    </div>
</template>

<script>
import { mapState } from "vuex";

export default {
  name: 'pdf-page-skeleton',
  props: {
    expectedSize        : { type: Object, required: true },
    imageScale          : { type: Number, required: true },
    loading             : { type: Boolean },
  },
  data() {
    return {
      stateText : "Loading"
    };
  },
  computed: {
    ...mapState({
      zoom: state => state.home.fileSelected.zoom,
    }),
    zoomScale() {
      return this.zoom / 100;
    },
    noZoomSize() {
      // zoom 反映前サイズ
      const size = this.expectedSize;
      const pageScale = this.imageScale;
      return {
        width: pageScale * size.width,
        height: pageScale * size.height
      };
    },
    shownSize() {
      // zoom を反映したサイズ
      return {
        width: this.noZoomSize.width * this.zoomScale,
        height: this.noZoomSize.height * this.zoomScale,
      };
    },
    sizeStyle() {
      return {
        width: `${this.shownSize.width}px`, 
        height: `${this.shownSize.height}px`
      };
    },
  },
}

</script>
