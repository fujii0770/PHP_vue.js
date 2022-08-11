<template>
    <div>
        <b>{{item.name}}</b>
        <span>{{ item.email }}</span>
    </div>
</template>

<script>
  export default {
    props: {
      item: { required: true },
    }
  }
</script>