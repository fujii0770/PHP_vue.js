<template>
  <div style="width: 100%; padding-top: 20px">
    <vs-col cols="12" vs-w="2" style="margin-top:40px;">
      <input type="file" ref="fileInput" class="hidden" multiple  id="uploadFile"
             v-on:change="onUploadFile" name="files"/>
      <vs-button id="button9" class="square fileselectbtn" color="#fff" type="filled"
                 @click.stop="$refs.fileInput.click()"><i class="fa fa-paperclip" aria-hidden="true"
                                                          style="color:#107fcd;width: 1.875em;"></i>添付ファイル
      </vs-button>
    </vs-col>
    <vs-col cols="12" vs-w="10">
      <p v-for="(msg , index) in uploadFileError " class="text-danger text-sm" style="padding-left:12px;" :key="index">{{ msg }}</p>
      <div class="v-list-file__list">
        <template :data="file" v-for="(file, itemIndex) in dispAttachments">
          <div class="v-list-file__block" :key="itemIndex" v-if="file.type!='del'">
            <div class="v-list-file__name" v-tooltip.top-center="file.name">{{ file.name ? file.name : '' }}</div>
            <button-icon icon-name="close" class="btnicon_fc" color="white"
                         @clickButton="deletefile(file,itemIndex)"></button-icon>
          </div>
        </template>
      </div>
    </vs-col>
  </div>
</template>

<script>
import config from "../../../app.config";
import ButtonIcon from "./ButtonIcon";
import Axios from "axios";

export default {
  name: "Attachemnt",
  components: {ButtonIcon},
  model: {
    prop: 'dispAttachments',
    event: 'change'
  },
  props: {
    dispAttachments: {
      type: Array,
      default: () => []
    },
    initFiles: {
      type: Array,
      default: () => []
    }
  },
  data() {
    return {
      uploadFileError: [],
      addAttachments: [],
      error: {
        uploadFile: {
          large: 'アップロードできるファイルサイズは5MB以下です',
          sameFileName: '同一ファイル名でアップロードできません。',
          count: '指定した添付ファイル数を超えることはできません。',
          total_size: '指定した添付ファイル合計容量を超えることはできません。'
        },
      },
      companyInfo: {},
      errorMsgHandler :null
    }
  },
  methods: {
    deletefile(file,index) {
      if (file.type == 'history'){
        file.type='del'
      }else{
        this.dispAttachments.splice(index,1)
      }
      this.$forceUpdate()
    },
    onUploadFile: async function (event) {
      const files = Array.from(event.target.files);
      const attachment = files;
      // ファイルのバリデーション
      for (const file of attachment) {
        let base64 = await this.fileToBase64(file)
        if (this.validateFile(file)) {
          this.dispAttachments.push({
            name: file.name,
            size: file.size,
            type: 'add',
            file: base64,
          })
          this.$forceUpdate()
        }else {
          event.target.value = '';
        }
      }
      event.target.value = '';
      this.$emit('change', this.dispAttachments)
    },
    validateFile: function (file) {
      // ローカルマシンからの読み込みをキャンセルしたら処理中断
      if (!file) {
        this.uploadFileError = "";
        return false;
      }
      const delFiles = this.dispAttachments.filter(value => {
        return value.type == 'del'
      });
      let oldInitFiles = this.initFiles.filter(_ => {
        if (delFiles.some(value => value.name == _)){
          return false
        }else {
          return true
        }
      })
      const target = this.dispAttachments.some(value => value.name === file.name && value.type == 'add');
      if (target) {
        this.uploadFileError.push(this.error.uploadFile.sameFileName);
        setTimeout(()=>{
          this.uploadFileError.shift();
        },3000)
        return false;
      }
      if (file.size == 0) {
        return false
      }
      // 上限サイズより大きければ受付けない
      // if (file.size > (this.companyInfo.bbs_max_attachment_size * 1024 * 1024)) {
      //   this.uploadFileError.push(`アップロードできるファイルサイズは${this.companyInfo.bbs_max_attachment_size}MB以下です`);
      //   setTimeout(()=>{
      //     this.uploadFileError.shift();
      //   },3000)
      //   return false;
      // }
      // let totalAttachmentSize = 0;
      // this.dispAttachments.map(value => {
      //   if (value.type == 'add' || value.type == 'history') {
      //     totalAttachmentSize += value.size
      //   }
      // })
      //
      // if ((parseInt(totalAttachmentSize) + file.size + parseInt(this.companyInfo.countAttachmentSize)) > (this.companyInfo.bbs_max_total_attachment_size * 1024 * 1024 * 1024)) {
      //   this.uploadFileError.push(this.error.uploadFile.total_size);
      //   setTimeout(()=>{
      //     this.uploadFileError.shift();
      //   },3000)
      //   return false;
      // }
      // // this.uploadFileError = "";
      return true;
    },
    async fileToBase64(file) {
      return await new Promise(resolve => {
        let reader = new FileReader()
        reader.onloadend = () => {
          let res = reader.result.split(',')
          if (res.length > 1) {
            resolve(res[1])
          } else {
            resolve('')
          }
        }
        reader.readAsDataURL(file)
      })
    },
  },
  async mounted() {
    // this.companyInfo = await Axios.get(`${config.BASE_API_URL}/getBbsSetting`)
    //     .then(response => {
    //       if (response.data) {
    //         let obj = response.data.info.companySetting
    //         obj.countAttachmentSize = response.data.info.countAttachmentSize
    //         return obj
    //       }
    //       return {
    //         bbs_max_attachment_size: 0,
    //         bbs_max_total_attachment_size: 0,
    //         bbs_max_attachment_count: 0,
    //         countAttachmentSize: 0
    //       };
    //     })
  },
  watch:{

  }
}
</script>

<style scoped>

</style>