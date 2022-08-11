<template>
    <div>
        <vs-row>
            <vs-col vs-w="12"  vs-type="flex" vs-align="center">
                <h2 class="mb-2 pb-2" style="font-size: 18px;">保存先の確認</h2>
            </vs-col>
        </vs-row>
        <vs-row class="mb-3 pt-3" style="border-top: 1px solid #cdcdcd">
            <p class="mb-4">同名のファイルが存在します。<br>
            <p>上書き保存しますか？</p>
        </vs-row>
        <vs-row class="pt-3" vs-type="flex" vs-justify="flex-end" vs-align="center" style="border-top: 1px solid #cdcdcd">
            <vs-button class="square mr-2" color="success" type="filled" @click="emitAccept"> 上書き保存</vs-button>
            <vs-button class="square mr-0" color="#bdc3c7" type="filled" @click="emitCancel"> キャンセル</vs-button>
        </vs-row>
    </div>
</template>
<script>
export default {
  name: "UpdateCheckDocModalInner",
  methods:{
      emitCancel(){
          this.$emit("cancel")
      },
      emitAccept(){
          this.$emit("accept")
      },
  }
};
</script>
