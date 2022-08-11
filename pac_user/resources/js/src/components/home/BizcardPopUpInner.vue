<template>
    <div>
        <div v-if="bizcardData != null">
            <div style="display: flex; justify-content: center;">
                <img :src="bizcardData.bizcard" class="bizcard_image">
            </div>
            <vs-row class="mt-3">
                <vs-col vs-type="flex" vs-w="3">名刺ID</vs-col>
                <vs-col vs-type="flex" vs-w="1">:</vs-col>
                <vs-col vs-w="8" class="bizcard_info_detail">{{ bizcardData.bizcard_id }}</vs-col>
            </vs-row>
            <vs-row class="mt-3">
                <vs-col vs-type="flex" vs-w="3">名前</vs-col>
                <vs-col vs-type="flex" vs-w="1">:</vs-col>
                <vs-col vs-w="8" class="bizcard_info_detail">{{ bizcardData.name }}</vs-col>
            </vs-row>
            <vs-row class="mt-3">
                <vs-col vs-type="flex" vs-w="3">会社名</vs-col>
                <vs-col vs-type="flex" vs-w="1">:</vs-col>
                <vs-col vs-w="8" class="bizcard_info_detail">{{ bizcardData.company_name }}</vs-col>
            </vs-row>
            <vs-row class="mt-3">
                <vs-col vs-type="flex" vs-w="3">電話番号</vs-col>
                <vs-col vs-type="flex" vs-w="1">:</vs-col>
                <vs-col vs-w="8" class="bizcard_info_detail">{{ bizcardData.phone_number }}</vs-col>
            </vs-row>
            <vs-row class="mt-3">
                <vs-col vs-type="flex" vs-w="3">住所</vs-col>
                <vs-col vs-type="flex" vs-w="1">:</vs-col>
                <vs-col vs-w="8" class="bizcard_info_detail">{{ bizcardData.address }}</vs-col>
            </vs-row>
            <vs-row class="mt-3">
                <vs-col vs-type="flex" vs-w="3">メールアドレス</vs-col>
                <vs-col vs-type="flex" vs-w="1">:</vs-col>
                <vs-col vs-w="8" class="bizcard_info_detail">{{ bizcardData.email }}</vs-col>
            </vs-row>
            <vs-row class="mt-3">
                <vs-col vs-type="flex" vs-w="3">部署</vs-col>
                <vs-col vs-type="flex" vs-w="1">:</vs-col>
                <vs-col vs-w="8" class="bizcard_info_detail">{{ bizcardData.department }}</vs-col>
            </vs-row>
            <vs-row class="mt-3">
                <vs-col vs-type="flex" vs-w="3">役職</vs-col>
                <vs-col vs-type="flex" vs-w="1">:</vs-col>
                <vs-col vs-w="8" class="bizcard_info_detail">{{ bizcardData.position }}</vs-col>
            </vs-row>
        </div>
        <div v-else>
            名刺情報がありません
        </div>
    </div>
</template>

<script>
export default {
  name: "BizcardPopUpInner",
  props: ["bizcardData"],
};
</script>

<style scoped>
.bizcard_image {
    width: 230px;
}
.bizcard_info_detail {
    word-wrap: break-word;
    overflow-wrap: break-word;
    width: 100%;
}
</style>
