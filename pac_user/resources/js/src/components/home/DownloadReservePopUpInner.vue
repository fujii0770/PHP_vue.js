<template>
    <div>
        <vs-row>
            <vs-input 
              class="inputx w-full" 
              label="ファイル名" 
              :value="downloadReserveFilename"
              @input="$emit('update:downloadReserveFilename', $event)"
              :maxlength="inputMaxLength" 
              placeholder="ファイル名(拡張子含め50文字まで。拡張子は自動付与されます。)"
            />
        </vs-row>
        <div class="mt-3 text-red">※ダウンロードが行われていない回覧です。</div>
        <vs-row class="mt-3">
            <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end"  vs-w="12">
                <vs-button @click="emitClickAccept()" color="primary">ダウンロード予約</vs-button>
                <vs-button @click="emitClose()" color="dark" type="border">キャンセル</vs-button>
            </vs-col>
        </vs-row>
    </div>
</template>

<script>
export default {
  name: "DownloadReservePopUpInner",
  props:[
      "downloadReserveFilename",
      "inputMaxLength"
  ],
  methods:{
      emitClickAccept(){
          this.$emit("accept-click")
      },
      emitClose(){
          this.$emit("close-click")
      }
  }
};
</script>