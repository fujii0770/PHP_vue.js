<template>
  <div>
    <vs-row>
      <vs-col vs-w="12" vs-type="block">
        <img style="height: 40px" :src="cloudLogo" alt="Box" />
        <p>
          <strong>{{ cloudName }}にファイル保存</strong>
        </p>
      </vs-col>
    </vs-row>
    <vs-row class="mb-3 pt-3">
      <vs-col
        vs-w="12"
        vs-type="flex"
        vs-justify="flex-start"
        vs-align="center"
        class="breadcrumb-container"
      >
        <vs-breadcrumb>
          <li
            v-for="(breadcrumbItem, index) in breadcrumbItems"
            :key="breadcrumbItem.id + index"
          >
            <a
              href="#"
              v-if="!breadcrumbItem.active"
              @click="emitBreadcrumbClick(breadcrumbItem.id)"
            >
              {{ breadcrumbItem.title }}
              <span v-if="!breadcrumbItem.active" class="vs-breadcrum--separator">/</span>
            </a>
            <p v-if="breadcrumbItem.active">{{ breadcrumbItem.title }}</p>
          </li>
        </vs-breadcrumb>
      </vs-col>
      <vs-col
        vs-w="12"
        class="files pt-3 pb-3 cloudItems"
      >
        <vs-list>
          <vs-list-item
            v-for="(file, index) in cloudFileItems"
            :key="file.id + index"
          >
            <img
              @click="emitCloudClick(file)"
              v-if="file.type === 'folder'"
              style="height: 25px"
              :src="require('@assets/images/folder.svg')"/>
            <img
              v-if="file.type === 'pdf'"
              style="height: 25px"
              :src="require('@assets/images/pdf.png')"/>
            <a @click="emitCloudClick(file)" v-if="file.type === 'folder'" href="#">{{ file.filename }}</a>
            <p v-if="file.type === 'pdf'" href="#">{{ file.filename }}</p>
          </vs-list-item>
        </vs-list>
      </vs-col>
    </vs-row>
    <vs-row
      class="mt-3 pt-6"
      vs-type="flex"
      style="border-top: 1px solid #cdcdcd">
      <vs-col
        vs-w="3"
        vs-type="flex"
        vs-justify="flex-end"
        vs-align="center"
        class="pr-6">
        <label>
          <strong>ファイル名:</strong>
        </label>
      </vs-col>
      <vs-col vs-w="9" vs-type="flex" vs-justify="flex-start" vs-align="center">
        <vs-input
          class="inputx w-full"
          placeholder="ファイル名"
          :value="filenameUpload"
          @input="$emit('update:filenameUpload', $event)"
        />
       </vs-col>
    </vs-row>
    <vs-row class="pt-6" vs-type="flex" vs-justify="flex-end" vs-align="center">
      <vs-button
        class="square mr-2"
        color="success"
        type="filled"
        @click="emitStoreClick()"
        :disabled="!filenameUpload">
        ファイル保存
      </vs-button>
      <vs-button
        class="square mr-0"
        color="#bdc3c7"
        type="filled"
        @click="emitCancelClick()">
        キャンセル
      </vs-button>
    </vs-row>
  </div>
</template>

<script>
export default {
  name: "CloudUploadModalInner",
  props: [
    "cloudLogo",
    "cloudName",
    "breadcrumbItems",
    "cloudFileItems",
    "filenameUpload",
  ],
  methods:{
      emitBreadcrumbClick(breadcrumbItemId){
          this.$emit("breadcrumb-item-click",breadcrumbItemId)
      },
      emitCloudClick(file){
          this.$emit("cloud-item-click",file)
      },
      emitStoreClick(){
          this.$emit("store-click")
      },
      emitCancelClick(){
          this.$emit("cancel-click")
      },
  },
};
</script>
