<template>
  <vs-button icon :color="color" @click="clickAction" class="sch-btn">
    <vs-icon class="v-icon">{{ iconName }}</vs-icon>
  </vs-button>
</template>

<script>
export default {
  props: {
    iconName: { type: String, required: true, default: null },
    color: { type: String, default: 'primary' }
  },
  methods: {
    clickAction() {
      this.$emit('clickButton')
    }
  }
}
</script>
