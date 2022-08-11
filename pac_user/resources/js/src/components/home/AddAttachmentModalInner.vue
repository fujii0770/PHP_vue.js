<template>
    <div>
        <header class="vs-popup--header">
            <div class="vs-popup--title"><h3>添付ファイル</h3></div>
        </header>
        <vs-row class="mb-2 pb-4 pt-4 " >
            <vs-list class="vx-list mb-2 pb-6 " style="padding-left: 15%">
                <div class="" v-for="(file, index) in attachmentUploads" v-bind:key="file.file_name + index">
                    <vs-row class="mb-3">
                        <vs-col>
                            {{index+1}}.
                            <a  href="#" class="link" style="text-decoration:none;color:#000000;word-wrap:break-word;" @click="emitFileClick(index)">
                                &nbsp;{{ file.file_name }}&nbsp;&nbsp;&nbsp;
                            </a>
                        </vs-col>
                    </vs-row>
                </div>
            </vs-list>
        </vs-row>
        <vs-row>
            <vs-col><hr style='border:1.5px inset #fff;'></vs-col>
        </vs-row>
        <vs-row class="mt-6" vs-type="flex" vs-justify="flex-end" vs-align="center" style="padding-right: 2%;padding-bottom: 3%;">
            <vs-button class="square" style="color:#000;border:1px solid #dcdcdc;" color="#fff" type="filled" @click="emitClose()">
                <i class="fa fa-times" aria-hidden="true" style="color:#107fcd;width: 1.875em;"></i>閉じる
            </vs-button>
        </vs-row>
    </div>
</template>

<script>
export default {
    name:"AddAttachmentModalInner",
    props: ["attachmentUploads"],
    methods:{
        emitFileClick(index){
            this.$emit("file-click",index)
        },

        emitClose(){
            this.$emit("close-click")
        }
    }
}
</script>