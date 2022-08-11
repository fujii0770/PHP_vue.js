
<template>
    <div class="thumbnail">
        <img v-if="loaded"
          :src="thumbnail.imageUrl" alt="a4"
          :style="sizeStyle">
        <div v-else
          class="skeleton" :style="sizeStyle">
          <p :style="{ 'line-height' : sizeStyle.height }">
            <template v-if="isLoading">
              {{ stateText }}
            </template>
          </p>
        </div>
        <p>{{thumbnail.no}}</p>
    </div>
</template>

<script>
export default {
  name: 'pdf-page-thumbnail',
  data() {
    return {
      stateText : "Loading"
    };
  },
  props: {
    size              : { type: Object },
    thumbnail         : { type: Object },
  },
  computed: {
    loaded() {
      return !!this.thumbnail.imageUrl;
    },
    sizeStyle() {
      return {
        width: `${this.size.width}px`, 
        height: `${this.size.height}px`,
      };
    },
    isLoading() {
      const item = this.thumbnail;
      return item.loading
    },
  },
}

</script>

