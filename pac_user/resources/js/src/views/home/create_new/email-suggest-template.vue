<template>
    <div>
        <span>{{item.name}}</span>
        <b>{{ item.email }}</b>
    </div>
</template>

<script>
  export default {
    props: {
      item: { required: true },
    }
  }
</script>