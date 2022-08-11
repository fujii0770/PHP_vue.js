<template>
  <div :id="inputId" class="search-box">
    <ButtonIcon
      color="white"
      icon-name="search"
      @clickButton="$emit('clickSearch')"
    />
    <input v-model="searchString" @keyup="onKeyUp" />
    <ButtonIcon
      v-show="searchString"
      color="white"
      icon-name="close"
      @clickButton="$emit('clearSearchString')"
    />
  </div>
</template>
<script>
export default {
  components: {
    ButtonIcon: () => import('@/components/portal/bbs/ButtonIcon')
  },
  props: {
    inputId: { type: String, default: '' },
    value: { type: String, default: '' }
  },
  computed: {
    searchString: {
      get() {
        return this.value
      },
      set(value) {
        this.$emit('input', value)
      }
    }
  },
  methods: {
    onKeyUp(event) {
      if (event.keyCode === 13) this.$emit('clickSearch')
    }
  }
}
</script>
