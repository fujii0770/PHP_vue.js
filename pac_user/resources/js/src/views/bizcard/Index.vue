<template>
    <div id="saves-list-page">
        <vs-row>
            <vs-col :vs-w="showMyBizcard ? 9 : 11.5" vs-xs="12" :vs-sm="showMyBizcard ? 7 : 11.5" style="transition: width .2s;">
                <vs-card style="margin-bottom: 0">
                    <vs-row>
                        <vs-col vs-type="flex" vs-lg="12" vs-sm="12" class="mb-12">
                            <vs-input class="inputx w-full" placeholder="名前、会社名、メールアドレス、部署、役職" v-model="filter"/>
                        </vs-col>
                    </vs-row>
                    <vs-row class="mt-3">
                        <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end" vs-w="12">
                            <vs-button class="square" color="primary" v-on:click="onSearch(true)"><i class="fas fa-search"></i> 検索</vs-button>
                        </vs-col>
                    </vs-row>
                </vs-card>

                <vs-card>
                    <vs-button class="square" color="primary" @click="updateMode = false; myBizcardOperation = false; onOpenInputBizcardModal()"><i class="far fa-address-card"></i> 登録</vs-button>
                    <vs-button class="square" color="success" @click="onOpenZipUploadModal()"><i class="fas fa-upload"></i> データ取込</vs-button>
                    <vs-button class="square" color="danger" @click="confirmDelete = true; myBizcardOperation = false; deleteBizcardList = selected"
                        v-bind:disabled="selected.length == 0"  ><i class="far fa-trash-alt"></i> 削除</vs-button>

                    <vs-table class="mt-3" noDataText="データがありません。" :data="listBizcard" @sort="handleSort" stripe sst>
                        <template slot="thead">
                            <vs-th class="width-50"><vs-checkbox :value="selectAll" @click="onSelectAll" /></vs-th>
                            <vs-th>名刺ID</vs-th>
                            <vs-th>名刺画像</vs-th>
                            <vs-th>名前</vs-th>
                            <vs-th>会社名</vs-th>
                            <vs-th>電話番号</vs-th>
                            <vs-th>住所</vs-th>
                            <vs-th>メールアドレス</vs-th>
                            <vs-th>部署</vs-th>
                            <vs-th>役職</vs-th>
                            <vs-th></vs-th>
                            <vs-th></vs-th>
                        </template>

                        <template slot-scope="{data}">
                            <vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data" >
                                <vs-td><vs-checkbox :value="tr.selected" @click="onRowCheckboxClick(tr)"/></vs-td>
                                <td> {{tr.bizcard_id}}</td>
                                <td> <img :src="tr.bizcard" class="bizcard_img"></td>
                                <td>{{tr.name}}</td>
                                <td>{{tr.company_name}}</td>
                                <td>{{tr.phone_number}}</td>
                                <td>{{tr.address}}</td>
                                <td>{{tr.email}}</td>
                                <td>{{tr.department}}</td>
                                <td>{{tr.position}}</td>
                                <td><vs-button class="square" color="primary"
                                    @click="onClickUpdateButton(tr)">更新</vs-button></td>
                                <td><vs-button class="square" color="primary" icon-pack="fas" icon="fa-link"
                                    @click="onClickShowURLButton(tr.bizcard_id)"></vs-button></td>
                            </vs-tr>
                        </template>
                    </vs-table>
                    <div>
                        <div class="mt-3">
                            {{ pagination.totalItem }} 件中 {{ pagination.from }} 件から {{ pagination.to }} 件までを表示
                        </div>
                    </div>
                    <vs-pagination :total="pagination.totalPage" v-model="pagination.currentPage"></vs-pagination>
                </vs-card>
            </vs-col>
            <vs-col :vs-w="showMyBizcard ? 3 : 0.5" :vs-xs="showMyBizcard ? 12 : 0" :vs-sm="showMyBizcard ? 5 : 0.5" class="my_bizcard_area">
                <div  @click="showMyBizcard = true">
                    <div class="button" v-if="!showMyBizcard" style="text-align: center; cursor: pointer;">
                        <i class="fas fa-caret-left" style="font-size: 40px; color: rgba(var(--vs-primary),1);"></i>
                        <div class="text" style="margin: 0 auto;line-height: 17px;"><p>自<br>分<br>の<br>名<br>刺<br>を<br>表<br>示<br>す<br>る</p></div>
                    </div>
                </div>
                <div v-if="showMyBizcard" class="show-flex my_bizcard_panel">
                    <div class="button2 ml-3 flex-item" @click="showMyBizcard=false" style="cursor: pointer; position: relative;">
                        <i class="fas fa-caret-right" style="font-size: 40px; color: rgba(var(--vs-primary),1);"></i>
                        <div class="text" style="position: absolute; top: 10px; left: 20px;">閉じる</div>
                    </div>
                    <div style="height: 90%; overflow-y: scroll">
                        <vs-row>
                            <vs-col vs-type="flex" vs-lg="12" vs-sm="12" vs-xs="12" class="mb-12" style="justify-content: center;">
                                <span v-if="myBizcard == null" class="vs-input--label">登録されていません。</span>
                                <img v-else :src="myBizcard.bizcard" class="my_bizcard_img">
                            </vs-col>
                        </vs-row>
                        <div v-if="myBizcard != null" class="detail">
                            <vs-row class="mt-3">
                                <vs-col vs-w="4" class="label">名前</vs-col>
                                <vs-col vs-w="8" class="info max-width-360">{{ myBizcard.name }}</vs-col>
                            </vs-row>
                            <vs-row>
                                <vs-col vs-w="4" class="label">会社名</vs-col>
                                <vs-col vs-w="8" class="info">{{ myBizcard.company_name }}</vs-col>
                            </vs-row>
                            <vs-row>                                
                                <vs-col vs-w="4" class="label">電話番号</vs-col>
                                <vs-col vs-w="8" class="info">{{ myBizcard.phone_number }}</vs-col>
                            </vs-row>
                            <vs-row>
                                <vs-col vs-w="4" class="label">住所</vs-col>
                                <vs-col vs-w="8" class="info">{{ myBizcard.address }}</vs-col>
                            </vs-row>
                            <vs-row>
                                <vs-col vs-w="4" class="label">メールアドレス</vs-col>
                                <vs-col vs-w="8" class="info">{{ myBizcard.email }}</vs-col>
                            </vs-row>
                            <vs-row>
                                <vs-col vs-w="4" class="label">部署</vs-col>
                                <vs-col vs-w="8" class="info">{{ myBizcard.department }}</vs-col>
                            </vs-row>
                            <vs-row>
                                <vs-col vs-w="4" class="label">役職</vs-col>
                                <vs-col vs-w="8" class="info">{{ myBizcard.position }}</vs-col>
                            </vs-row>
                        </div>
                        <vs-row class="mt-3">
                            <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end"  vs-w="12">
                                <vs-button v-if="myBizcard == null" class="square" color="primary"
                                @click="updateMode = false; myBizcardOperation = true; onOpenInputBizcardModal()">
                                    <i class="far fa-address-card"></i> 登録
                                </vs-button>
                                <vs-button v-else class="square" color="primary" @click="onClickUpdateButton(myBizcard)">
                                    <i class="far fa-address-card"></i> 更新
                                </vs-button>
                                <vs-button class="square" color="danger" :disabled="myBizcard == null" @click="confirmDelete=true; myBizcardOperation = true; deleteBizcardList = [myBizcard]">
                                    <i class="far fa-trash-alt"></i> 削除
                                </vs-button>
                            </vs-col>
                        </vs-row>
                    </div>
                </div>
            </vs-col>
        </vs-row>

        <modal name="input-bizcard-modal"
               :classes="['v--modal', 'input-bizcard-modal', 'p-4']"
               :height="'auto'"
               :scrollable="true">
            <vs-row>
                <vs-col vs-w="12" vs-type="block">
                    <p v-if="updateMode">名刺更新</p>
                    <p v-else>名刺登録</p>
                </vs-col>
            </vs-row>
            <div class="mt-5 input_area">
                <form>
                    <vs-row class="mt-3">
                        <vs-col vs-w="4" class="text-right pr-3 pt-3"></vs-col>
                        <vs-col vs-type="" vs-w="8" class="uploaded_image_row">
                            <img v-show="base64Image" :src="base64Image" class="uploaded_image">
                            <vs-button radius v-show="base64Image" type="filled" color="primary" icon="rotate_right" @click="rotateBase64()" class="rotate_button"></vs-button>
                        </vs-col>
                        <vs-col vs-w="4" class="text-right pr-3 pt-3">名刺画像  <span class="text-red">*</span></vs-col>
                        <vs-col vs-type="" vs-w="8">
                            <input ref="file" type="file" accept="image/jpeg, image/png, image/gif"
                             @change="upload" class="w-full">
                            <span class="text-danger text-sm" v-show="imageFileError">{{ imageFileError }}</span>
                        </vs-col>
                        <vs-col vs-w="4" class="text-right pr-3 pt-3">名前　</vs-col>
                        <vs-col vs-type="" vs-w="8">
                            <vs-input placeholder="名前" name="name" v-validate="'max:128'" data-vv-as="名前"
                             v-model="parameters.name" class="w-full" />
                             <span class="text-danger text-sm" v-show="errors.has('name')">{{ errors.first('name') }}</span>
                        </vs-col>
                        <vs-col vs-w="4" class="text-right pr-3 pt-3">会社名</vs-col>
                        <vs-col vs-type="" vs-w="8">
                            <vs-input placeholder="会社名" name="company_name" v-validate="'max:256'" data-vv-as="会社名"
                             v-model="parameters.company_name" class="w-full" />
                             <span class="text-danger text-sm" v-show="errors.has('company_name')">{{ errors.first('company_name') }}</span>
                        </vs-col>
                        <vs-col vs-w="4" class="text-right pr-3 pt-3">電話番号</vs-col>
                        <vs-col vs-type="" vs-w="8">
                            <vs-input placeholder="電話番号"  name="phone_number" v-validate="'max:128'" data-vv-as="電話番号"
                             v-model="parameters.phone_number" class="w-full" />
                            <span class="text-danger text-sm" v-show="errors.has('phone_number')">{{ errors.first('phone_number') }}</span>
                        </vs-col>
                        <vs-col vs-w="4" class="text-right pr-3 pt-3">住所　</vs-col>
                        <vs-col vs-type="" vs-w="8">
                            <vs-input placeholder="住所" name="address" v-validate="'max:256'" data-vv-as="住所"
                             v-model="parameters.address" class="w-full" />
                            <span class="text-danger text-sm" v-show="errors.has('address')">{{ errors.first('address') }}</span>
                        </vs-col>
                    </vs-row>
                    <vs-row class="mt-3">
                        <vs-col vs-w="4" class="text-right pr-3 pt-3">メールアドレス</vs-col>
                        <vs-col vs-type="" vs-w="8">
                            <vs-input type="email" name="email" v-validate="'email|max:256'" data-vv-as="メールアドレス"
                                placeholder="メールアドレス" v-model="parameters.email" class="w-full" />
                            <span class="text-danger text-sm" v-show="errors.has('email')">{{ errors.first('email') }}</span>
                        </vs-col>
                        <vs-col vs-w="4" class="text-right pr-3 pt-3">部署　</vs-col>
                        <vs-col vs-type="" vs-w="8">
                            <vs-input placeholder="部署" name="department" v-validate="'max:128'" data-vv-as="部署"
                             v-model="parameters.department" class="w-full" />
                            <span class="text-danger text-sm" v-show="errors.has('department')">{{ errors.first('department') }}</span>
                        </vs-col>
                        <vs-col vs-w="4" class="text-right pr-3 pt-3">役職　</vs-col>
                        <vs-col vs-type="" vs-w="8">
                            <vs-input placeholder="役職" name="position" v-validate="'max:128'" data-vv-as="役職"
                             v-model="parameters.position" class="w-full" />
                            <span class="text-danger text-sm" v-show="errors.has('position')">{{ errors.first('position') }}</span>
                        </vs-col>
                    </vs-row>
                </form>
            </div>
            <vs-row class="pt-3 pb-0" vs-type="flex" vs-justify="flex-end" vs-align="center">
                <vs-button v-if="updateMode" color="primary" @click="onUpdate()">更新</vs-button>
                <vs-button v-else color="primary" @click="onRegister()">登録</vs-button>
                <vs-button class="square mr-2 " color="dark" type="border" @click="$modal.hide('input-bizcard-modal')" > キャンセル</vs-button>
            </vs-row>
        </modal>

        <vs-popup title="データ取込(名刺登録)" :active.sync="zipUploadPopup" :class="zipUploadPopupClass">
            <div>
                <div>追加する名刺画像を含むZIPファイル（必須）および名刺情報を記載したCSVファイル（任意）を指定してください。</div>
                <br>
                <div>ZIPファイル：</div>
                <div>最大2MB</div>
                <div>JPEGまたはPNG形式の名刺画像を含め、フォルダを含めないでください。フォルダ内に画像を配置した場合は登録できません。</div>
                <br>
                <div>CSV形式：</div>
                <div>名前、名前（かな・カナ）、名前（ローマ字）、会社名、会社名（かな・カナ）、電話番号（市外局番から入力）、住所、施設名称、郵便番号（000-0000形式）、住所（英語表記）、メールアドレス（xxx@domain.co.jp形式）、部署、役職、職種・資格・その他肩書等、URL、名刺画像ファイル名（必須）</div>
                <br>
                <div>※データに不備がある名刺は登録を行うことはできません。</div>
            </div>
            <input id="zipUpload" type="file" accept=".zip" @change="onUploadZip" style="display: none">
            <input id="csvUpload" type="file" accept=".csv" @change="onUploadCsv" style="display: none">
            <vs-row v-if="csvData" vs-type="flex" vs-w="12" class="items-center">
                <div style="width: 100%;">
                    <div class="csv_result_title">
                        CSV取込結果
                    </div>
                    <vs-table class="mt-3" noDataText="データがありません。" :data="csvData" stripe sst maxHeight="200px">
                        <template slot="thead">
                            <vs-th>名前</vs-th>
                            <vs-th>名前（かな・カナ）</vs-th>
                            <vs-th>名前（ローマ字）</vs-th>
                            <vs-th>会社名</vs-th>
                            <vs-th>会社名（かな・カナ）</vs-th>
                            <vs-th>電話番号</vs-th>
                            <vs-th>住所</vs-th>
                            <vs-th>施設名称</vs-th>
                            <vs-th>郵便番号</vs-th>
                            <vs-th>住所（英語表記）</vs-th>
                            <vs-th>メールアドレス</vs-th>
                            <vs-th>部署</vs-th>
                            <vs-th>役職</vs-th>
                            <vs-th>職種・資格・その他肩書等</vs-th>
                            <vs-th>URL</vs-th>
                            <vs-th>名刺画像ファイル名</vs-th>
                            <vs-th></vs-th>
                        </template>

                        <template slot-scope="{data}">
                            <vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data" >
                                <td :class="tr[csvDataColumn.name].class">{{tr[csvDataColumn.name].data}}</td>
                                <td :class="tr[csvDataColumn.name_kana].class">{{tr[csvDataColumn.name_kana].data}}</td>
                                <td :class="tr[csvDataColumn.name_romaji].class">{{tr[csvDataColumn.name_romaji].data}}</td>
                                <td :class="tr[csvDataColumn.company_name].class">{{tr[csvDataColumn.company_name].data}}</td>
                                <td :class="tr[csvDataColumn.company_kana].class">{{tr[csvDataColumn.company_kana].data}}</td>
                                <td :class="tr[csvDataColumn.phone_number].class">{{tr[csvDataColumn.phone_number].data}}</td>
                                <td :class="tr[csvDataColumn.address].class">{{tr[csvDataColumn.address].data}}</td>
                                <td :class="tr[csvDataColumn.address_name].class">{{tr[csvDataColumn.address_name].data}}</td>
                                <td :class="tr[csvDataColumn.postal_code].class">{{tr[csvDataColumn.postal_code].data}}</td>
                                <td :class="tr[csvDataColumn.address_en].class">{{tr[csvDataColumn.address_en].data}}</td>
                                <td :class="tr[csvDataColumn.email].class">{{tr[csvDataColumn.email].data}}</td>
                                <td :class="tr[csvDataColumn.department].class">{{tr[csvDataColumn.department].data}}</td>
                                <td :class="tr[csvDataColumn.position].class">{{tr[csvDataColumn.position].data}}</td>
                                <td :class="tr[csvDataColumn.person_title].class">{{tr[csvDataColumn.person_title].data}}</td>
                                <td :class="tr[csvDataColumn.url].class">{{tr[csvDataColumn.url].data}}</td>
                                <td :class="tr[csvDataColumn.file_name].class">{{tr[csvDataColumn.file_name].data}}</td>
                                <td><vs-button @click="onClickModify(tr, indextr)" class="square" color="primary">修正</vs-button></td>
                            </vs-tr>
                        </template>
                    </vs-table>
                    <vs-alert :active="0 < displayCsvErrorMessage.size" color="danger" class="mt-3">
                        <div v-for="(message, index) in displayCsvErrorMessage" :key="index">{{message}}</div>
                    </vs-alert>
                </div>
            </vs-row>
            <vs-row vs-align="flex-start" vs-type="flex" vs-justify="center" class="p-4" style="border-bottom: lightgray 1px solid;">
                <vs-col vs-w="7" vs-type="flex" vs-justify="center" vs-align="center">
                    <vs-button v-if="zipUploadTime == null" @click="triggerClickEvent('zipUpload')" color="success" type="filled" style="margin-left: 0.5rem;"><i class="fas fa-file-import"></i>画像取込</vs-button>
                    <vs-button v-else @click="onClickDeleteZip()" color="success" type="filled" style="margin-left: 0.5rem;"><i class="fas fa-times-circle"></i>画像クリア</vs-button>
                    <vs-button v-if="csvData == null" @click="triggerClickEvent('csvUpload')" color="success" type="filled"><i class="fas fa-file-import"></i>CSV取込</vs-button>
                    <vs-button v-else @click="csvData = null" color="success" type="filled"><i class="fas fa-times-circle"></i>CSVクリア</vs-button>
                </vs-col>
            </vs-row>
            <vs-row class="pt-3 pb-0" vs-type="flex" vs-justify="flex-end" vs-align="center">
                <vs-button @click="onMultipleRegister()" color="primary" :disabled="zipUploadTime == null"><i class="far fa-address-card"></i>登録</vs-button>
                <vs-button class="square mr-2" color="lightgray" text-color="black" type="filled" @click="zipUploadPopup = false;" ><i class="fas fa-times-circle"></i>閉じる</vs-button>
            </vs-row>
            <vs-popup title="データ修正" :active.sync="dataModifyPopup" class="csv-modify">
                <vs-row class="mt-3">
                    <vs-col vs-w="4" class="text-right pr-3">名前　</vs-col>
                    <vs-col vs-w="8">
                        <vs-input placeholder="名前" name="name" v-validate="'max:128'" data-vv-as="名前"
                        v-model="modifyData[csvDataColumn.name]" class="w-full" />
                    </vs-col>
                </vs-row>
                <vs-row v-show="errors.has('name')">
                    <vs-col vs-w="4"></vs-col>
                    <vs-col vs-w="8">
                        <span class="text-danger text-sm">{{ errors.first('name') }}</span>
                    </vs-col>
                </vs-row>
                <vs-row class="mt-3">
                    <vs-col vs-w="4" class="text-right pr-3">名前（かな・カナ）</vs-col>
                    <vs-col vs-w="8">
                        <vs-input placeholder="名前（かな・カナ）" name="name_kana" v-validate="'max:128'" data-vv-as="名前（かな・カナ）"
                        v-model="modifyData[csvDataColumn.name_kana]" class="w-full" />
                    </vs-col>
                </vs-row>
                <vs-row v-show="errors.has('name_kana')">
                    <vs-col vs-w="4"></vs-col>
                    <vs-col vs-w="8">
                        <span class="text-danger text-sm">{{ errors.first('name_kana') }}</span>
                    </vs-col>
                </vs-row>
                <vs-row class="mt-3">
                    <vs-col vs-w="4" class="text-right pr-3">名前（ローマ字）</vs-col>
                    <vs-col vs-w="8">
                        <vs-input placeholder="名前（ローマ字）" name="name_romaji" v-validate="'max:128'" data-vv-as="名前（ローマ字）"
                        v-model="modifyData[csvDataColumn.name_romaji]" class="w-full" />
                    </vs-col>
                </vs-row>
                <vs-row v-show="errors.has('name_romaji')">
                    <vs-col vs-w="4"></vs-col>
                    <vs-col vs-w="8">
                        <span class="text-danger text-sm">{{ errors.first('name_romaji') }}</span>
                    </vs-col>
                </vs-row>
                <vs-row class="mt-3">
                    <vs-col vs-w="4" class="text-right pr-3">会社名</vs-col>
                    <vs-col vs-w="8">
                        <vs-input placeholder="会社名" name="company_name" v-validate="'max:256'" data-vv-as="会社名"
                        v-model="modifyData[csvDataColumn.company_name]" class="w-full" />
                    </vs-col>
                </vs-row>
                <vs-row v-show="errors.has('company_name')">
                    <vs-col vs-w="4"></vs-col>
                    <vs-col vs-w="8">
                        <span class="text-danger text-sm">{{ errors.first('company_name') }}</span>
                    </vs-col>
                </vs-row>
                <vs-row class="mt-3">
                    <vs-col vs-w="4" class="text-right pr-3">会社名（かな・カナ）</vs-col>
                    <vs-col vs-w="8">
                        <vs-input placeholder="会社名（かな・カナ）" name="company_kana" v-validate="'max:256'" data-vv-as="会社名（かな・カナ）"
                        v-model="modifyData[csvDataColumn.company_kana]" class="w-full" />
                    </vs-col>
                </vs-row>
                <vs-row v-show="errors.has('company_kana')">
                    <vs-col vs-w="4"></vs-col>
                    <vs-col vs-w="8">
                        <span class="text-danger text-sm">{{ errors.first('company_kana') }}</span>
                    </vs-col>
                </vs-row>
                <vs-row class="mt-3">
                    <vs-col vs-w="4" class="text-right pr-3">電話番号</vs-col>
                    <vs-col vs-w="8">
                        <vs-input placeholder="電話番号" name="phone_number" v-validate="'phone'" data-vv-as="電話番号"
                        v-model="modifyData[csvDataColumn.phone_number]" class="w-full" />
                    </vs-col>
                </vs-row>
                <vs-row v-show="errors.has('phone_number')">
                    <vs-col vs-w="4"></vs-col>
                    <vs-col vs-w="8">
                        <span class="text-danger text-sm">{{ csvErrorMessages.phoneInvalid }}</span>
                    </vs-col>
                </vs-row>
                <vs-row class="mt-3">
                    <vs-col vs-w="4" class="text-right pr-3">住所　</vs-col>
                    <vs-col vs-w="8">
                        <vs-input placeholder="住所" name="address" v-validate="'max:256'" data-vv-as="住所"
                        v-model="modifyData[csvDataColumn.address]" class="w-full" />
                    </vs-col>
                </vs-row>
                <vs-row v-show="errors.has('address')">
                    <vs-col vs-w="4"></vs-col>
                    <vs-col vs-w="8">
                        <span class="text-danger text-sm">{{ errors.first('address') }}</span>
                    </vs-col>
                </vs-row>
                <vs-row class="mt-3">
                    <vs-col vs-w="4" class="text-right pr-3">施設名称</vs-col>
                    <vs-col vs-w="8">
                        <vs-input placeholder="施設名称" name="address_name" v-validate="'max:256'" data-vv-as="施設名称"
                        v-model="modifyData[csvDataColumn.address_name]" class="w-full" />
                    </vs-col>
                </vs-row>
                <vs-row v-show="errors.has('address_name')">
                    <vs-col vs-w="4"></vs-col>
                    <vs-col vs-w="8">
                        <span class="text-danger text-sm">{{ errors.first('address_name') }}</span>
                    </vs-col>
                </vs-row>
                <vs-row class="mt-3">
                    <vs-col vs-w="4" class="text-right pr-3">郵便番号</vs-col>
                    <vs-col vs-w="8">
                        <vs-input placeholder="郵便番号" name="postal_code" v-validate="{regex: /^[0-9]{3}-[0-9]{4}$/}" data-vv-as="郵便番号"
                        v-model="modifyData[csvDataColumn.postal_code]" class="w-full" />
                    </vs-col>
                </vs-row>
                <vs-row v-show="errors.has('postal_code')">
                    <vs-col vs-w="4"></vs-col>
                    <vs-col vs-w="8">
                        <span class="text-danger text-sm">{{ csvErrorMessages.postalCodeInvalid }}</span>
                    </vs-col>
                </vs-row>
                <vs-row class="mt-3">
                    <vs-col vs-w="4" class="text-right pr-3">住所（英語表記）</vs-col>
                    <vs-col vs-w="8">
                        <vs-input placeholder="住所（英語表記）" name="address_en" v-validate="'max:256'" data-vv-as="住所（英語表記）"
                        v-model="modifyData[csvDataColumn.address_en]" class="w-full" />
                    </vs-col>
                </vs-row>
                <vs-row v-show="errors.has('address_en')">
                    <vs-col vs-w="4"></vs-col>
                    <vs-col vs-w="8">
                        <span class="text-danger text-sm">{{ errors.first('address_en') }}</span>
                    </vs-col>
                </vs-row>
                <vs-row class="mt-3">
                    <vs-col vs-w="4" class="text-right pr-3">メールアドレス</vs-col>
                    <vs-col vs-w="8">
                        <vs-input type="email" name="email" v-validate="'email|max:256'" data-vv-as="メールアドレス"
                            placeholder="メールアドレス" v-model="modifyData[csvDataColumn.email]" class="w-full" />
                    </vs-col>
                </vs-row>
                <vs-row v-show="errors.has('email')">
                    <vs-col vs-w="4"></vs-col>
                    <vs-col vs-w="8">
                        <span class="text-danger text-sm">{{ csvErrorMessages.emailInvalid }}</span>
                    </vs-col>
                </vs-row>
                <vs-row class="mt-3">
                    <vs-col vs-w="4" class="text-right pr-3">部署　</vs-col>
                    <vs-col vs-w="8">
                        <vs-input placeholder="部署" name="department" v-validate="'max:128'" data-vv-as="部署"
                        v-model="modifyData[csvDataColumn.department]" class="w-full" />
                    </vs-col>
                </vs-row>
                <vs-row v-show="errors.has('department')">
                    <vs-col vs-w="4"></vs-col>
                    <vs-col vs-w="8">
                        <span class="text-danger text-sm">{{ errors.first('department') }}</span>
                    </vs-col>
                </vs-row>
                <vs-row class="mt-3">
                    <vs-col vs-w="4" class="text-right pr-3">役職　</vs-col>
                    <vs-col vs-w="8">
                        <vs-input placeholder="役職" name="position" v-validate="'max:128'" data-vv-as="役職"
                        v-model="modifyData[csvDataColumn.position]" class="w-full" />
                    </vs-col>
                </vs-row>
                <vs-row v-show="errors.has('position')">
                    <vs-col vs-w="4"></vs-col>
                    <vs-col vs-w="8">
                        <span class="text-danger text-sm">{{ errors.first('position') }}</span>
                    </vs-col>
                </vs-row>
                <vs-row class="mt-3">
                    <vs-col vs-w="4" class="text-right pr-3">職種・資格・その他肩書等</vs-col>
                    <vs-col vs-w="8">
                        <vs-input placeholder="職種・資格・その他肩書等" name="person_title" v-validate="'max:256'" data-vv-as="職種・資格・その他肩書等"
                        v-model="modifyData[csvDataColumn.person_title]" class="w-full" />
                    </vs-col>
                </vs-row>
                <vs-row v-show="errors.has('person_title')">
                    <vs-col vs-w="4"></vs-col>
                    <vs-col vs-w="8">
                        <span class="text-danger text-sm">{{ errors.first('person_title') }}</span>
                    </vs-col>
                </vs-row>
                <vs-row class="mt-3">
                    <vs-col vs-w="4" class="text-right pr-3">URL</vs-col>
                    <vs-col vs-w="8">
                        <vs-input placeholder="URL" name="url" v-validate="'max:256'" data-vv-as="URL"
                        v-model="modifyData[csvDataColumn.url]" class="w-full" />
                    </vs-col>
                </vs-row>
                <vs-row v-show="errors.has('url')">
                    <vs-col vs-w="4"></vs-col>
                    <vs-col vs-w="8">
                        <span class="text-danger text-sm">{{ errors.first('url') }}</span>
                    </vs-col>
                </vs-row>
                <vs-row class="mt-3">
                    <vs-col vs-w="4" class="text-right pr-3">名刺画像ファイル名 <span class="text-red">*</span></vs-col>
                    <vs-col vs-type="" vs-w="8">
                        <vs-input placeholder="名刺画像ファイル名" name="file_name"
                        v-model="modifyData[csvDataColumn.file_name]" class="w-full" />
                    </vs-col>
                </vs-row>
                <vs-row v-show="modifyData[csvDataColumn.file_name] == ''">
                    <vs-col vs-w="4"></vs-col>
                    <vs-col vs-w="8">
                        <span class="text-danger text-sm">{{ csvErrorMessages.fileNameEmpty }}</span>
                    </vs-col>
                </vs-row>
                <vs-row class="pt-3 pb-0" vs-type="flex" vs-justify="flex-end" vs-align="center">
                    <vs-button color="primary" @click="onModify()">修正</vs-button>
                    <vs-button class="square mr-2 " color="dark" type="border" @click="dataModifyPopup = false"> キャンセル</vs-button>
                </vs-row>
            </vs-popup>
        </vs-popup>

        <vs-popup classContent="popup-example"  title="名刺の削除" :active.sync="confirmDelete">
            <div v-if="deleteBizcardList.length > 1">{{ deleteBizcardList.length }}件の名刺を削除します。</div>
            <div v-if="deleteBizcardList.length == 1">
                <vs-row>
                    <vs-col vs-w="4"></vs-col>
                    <vs-col vs-type="flex" vs-w="8">
                        <img :src="deleteBizcardList[0].bizcard" class="uploaded_image">
                    </vs-col>
                </vs-row>
                <vs-row class="mt-3">
                    <vs-col vs-type="flex" vs-w="3">名刺ID</vs-col>
                    <vs-col vs-type="flex" vs-w="1">:</vs-col>
                    <vs-col vs-w="8" class="bizcard_info_detail">{{ deleteBizcardList[0].bizcard_id }}</vs-col>
                </vs-row>
                <vs-row class="mt-3">
                    <vs-col vs-type="flex" vs-w="3">名前</vs-col>
                    <vs-col vs-type="flex" vs-w="1">:</vs-col>
                    <vs-col vs-w="8" class="bizcard_info_detail">{{ deleteBizcardList[0].name }}</vs-col>
                </vs-row>
                <vs-row class="mt-3">
                    <vs-col vs-type="flex" vs-w="3">会社名</vs-col>
                    <vs-col vs-type="flex" vs-w="1">:</vs-col>
                    <vs-col vs-w="8" class="bizcard_info_detail">{{ deleteBizcardList[0].company_name }}</vs-col>
                </vs-row>
                <vs-row class="mt-3">
                    <vs-col vs-type="flex" vs-w="3">電話番号</vs-col>
                    <vs-col vs-type="flex" vs-w="1">:</vs-col>
                    <vs-col vs-w="8" class="bizcard_info_detail">{{ deleteBizcardList[0].phone_number }}</vs-col>
                </vs-row>
                <vs-row class="mt-3">
                    <vs-col vs-type="flex" vs-w="3">住所</vs-col>
                    <vs-col vs-type="flex" vs-w="1">:</vs-col>
                    <vs-col vs-w="8" class="bizcard_info_detail">{{ deleteBizcardList[0].address }}</vs-col>
                </vs-row>
                <vs-row class="mt-3">
                    <vs-col vs-type="flex" vs-w="3">メールアドレス</vs-col>
                    <vs-col vs-type="flex" vs-w="1">:</vs-col>
                    <vs-col vs-w="8" class="bizcard_info_detail">{{ deleteBizcardList[0].email }}</vs-col>
                </vs-row>
                <vs-row class="mt-3">
                    <vs-col vs-type="flex" vs-w="3">部署</vs-col>
                    <vs-col vs-type="flex" vs-w="1">:</vs-col>
                    <vs-col vs-w="8" class="bizcard_info_detail">{{ deleteBizcardList[0].department }}</vs-col>
                </vs-row>
                <vs-row class="mt-3">
                    <vs-col vs-type="flex" vs-w="3">役職</vs-col>
                    <vs-col vs-type="flex" vs-w="1">:</vs-col>
                    <vs-col vs-w="8" class="bizcard_info_detail">{{ deleteBizcardList[0].position }}</vs-col>
                </vs-row>
                <vs-row v-if="myBizcardOperation" class="mt-3"><vs-col vs-type="flex" vs-w="12">自分の名刺を削除します。</vs-col></vs-row>
                <vs-row v-else class="mt-3"><vs-col vs-type="flex" vs-w="12">この名刺を削除します。</vs-col></vs-row>
            </div>
            <vs-row class="mt-3">
                <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end"  vs-w="12">
                    <vs-button @click="onDelete" color="danger">削除</vs-button>
                    <vs-button @click="confirmDelete=false" color="dark" type="border">キャンセル</vs-button>
                </vs-col>
            </vs-row>
        </vs-popup>
        
        <vs-popup classContent="popup-example" title="名刺情報ページURL" :active.sync="showLinkPageURLPopup">
            <vs-row class="mt-3">
                <vs-col vs-type="flex" vs-align="center" vs-justify="center" vs-w="12">
                    <div @click="downLoadQrCode()">
                        <vue-qr id="QrCode" :text="linkPageURL" :size="200"></vue-qr>
                    </div>
                </vs-col>
            </vs-row>
            <vs-row class="mt-3">
                <vs-col vs-w="12" style="word-wrap: break-word; overflow-wrap: break-word;">{{linkPageURL}}</vs-col>
            </vs-row>
            <vs-row class="mt-3">
                <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end" vs-w="12">
                    <vs-button @click="downLoadQrCode()">QRCode</vs-button>
                    <vs-button color="primary" @click="copyURLToClipboard()">クリップボードにコピー</vs-button>
                    <vs-button @click="showLinkPageURLPopup=false" color="dark" type="border">閉じる</vs-button>
                </vs-col>
            </vs-row>
        </vs-popup>
    </div>
</template>

<script>
import { mapState, mapActions } from "vuex";
import InfiniteLoading from 'vue-infinite-loading';

import flatPickr from 'vue-flatpickr-component'
import 'flatpickr/dist/flatpickr.min.css';
import {Japanese} from 'flatpickr/dist/l10n/ja.js';

import ja from 'vee-validate/dist/locale/ja'
import { Validator } from 'vee-validate'
import { PhoneNumberUtil, PhoneNumberFormat } from 'google-libphonenumber';
import config from "../../app.config.js";
import VueQr from 'vue-qr';

const phoneUtil = PhoneNumberUtil.getInstance();

export default {
    components: {
        InfiniteLoading,
        flatPickr,
        VueQr,
    },
    data() {
        return {
            filter: "",
            selectAll: false,
            listBizcard:[],
            pagination:{ totalPage:0, currentPage:1, limit: 10, totalItem:0, from: 1, to: 10 },
            bizcardNumPerPage: 10,  // 1ページ当たりの名刺表示件数
            orderBy: "bizcard_id",
            orderDir: "desc",
            configDate: {
                locale: Japanese,
                wrap: true,
                defaultHour: 0
            },
            confirmDelete: false,
            base64_prefix: {
                jpeg : "data:image/jpeg;base64,",
                png : "data:image/png;base64,",
                gif : "data:image/gif;base64,"
            },
            base64Image: "",
            selectedPrefix: "",
            parameters: {
                biz_card_image: null,
                name: "",
                company_name: "",
                phone_number: "",
                address: "",
                email: "",
                department: "",
                position: "",
                my_bizcard: false,
            },
            error: {
                biz_card_image: {
                    required: '必須項目です',
                    large: 'アップロードできるファイルサイズは5MB以下です',
                    invalid: 'アップロードできるファイルはJPEG, PNG, GIF画像です'
                },
                email: {
                    email: "メールアドレスが正しくありません"
                }
            },
            imageFileError: "",
            updateMode: false,       // 名刺入力モーダルを開く時にupdateMode == trueなら名刺更新、updateMode == falseなら名刺登録
            myBizcard: null,
            showMyBizcard: false,
            myBizcardOperation: false,  // 自分の名刺に対し処理を行う場合はtrue
            deleteBizcardList: [],
            showLinkPageURLPopup: false,
            linkPageURL: "",
            updateBizcardId: null,      // 名刺更新時の対象の名刺管理ID
            zipUploadPopup: false,
            zipUploadPopupClass: "zip-upload-popup",
            zipUploadTime: null,
            csvData: null,
            dataModifyPopup: false,
            modifyData: Array(8),
            modifyIndex: null,
            displayCsvErrorMessage: new Set(),  // csvデータ下部のエラーメッセージ表示内容
        }
    },
    created: function(){
        Validator.localize('ja', ja);
        Validator.extend('phone', {
            message: '電話番号が正しくありません',
            validate (value) {
                try {
                    let number = phoneUtil.parse(String(value), 'JP');
                    return phoneUtil.isValidNumberForRegion(number, 'JP');
                } catch (error) {
                    // parseに失敗した場合は不正な電話番号と判定する
                    return false;
                }
            }
        })
    },
    computed: {
        selected() {
            return this.listBizcard.filter(item => item.selected);
        },
        csvDataColumn() {
            return {
                name: 0,
                name_kana: 1,
                name_romaji: 2,
                company_name: 3,
                company_kana: 4,
                phone_number: 5,
                address: 6,
                address_name: 7,
                postal_code: 8,
                address_en: 9,
                email: 10,
                department: 11,
                position: 12,
                person_title: 13,
                url: 14,
                file_name: 15,
                has_error: 16,
            }
        },
        csvErrorMessages() {
            return {
                nameMaxOver: "名前は128文字以内にしてください",
                nameKanaMaxOver: "名前（かな・カナ）は128文字以内にしてください",
                nameRomajiMaxOver: "名前（ローマ字）は128文字以内にしてください",
                companyMaxOver: "会社名は256文字以内にしてください",
                companyKanaMaxOver: "会社名（かな・カナ）は256文字以内にしてください",
                phoneInvalid: "電話番号が正しくありません",
                addressMaxOver: "住所は256文字以内にしてください",
                addressNameMaxOver: "施設名称は256文字以内にしてください",
                postalCodeInvalid: "郵便番号が正しくありません",
                addressEnMaxOver: "住所（英語表記）は256文字以内にしてください",
                emailMaxOver: "メールアドレスは256文字以内にしてください",
                emailInvalid: "メールアドレスが正しくありません",
                departmentMaxOver: "部署は128文字以内にしてください",
                positionMaxOver: "役職は128文字以内にしてください",
                personTitleMaxOver: "職種・資格・その他肩書等は256文字以内にしてください",
                urlMaxOver: "URLは256文字以内にしてください",
                fileNameEmpty: "名刺画像ファイル名は必須項目です",
                fileNameDuplicate: "同一のファイル名のデータが含まれています",
            }
        }
    },
    methods: {
        ...mapActions({
            search: "bizcard/getListBizcard",
            register: "bizcard/registerBizcard",
            delete: "bizcard/deleteBizcard",
            update: "bizcard/updateBizcard",
            getMyBizcard: "bizcard/getMyBizcard",
            getURL: "bizcard/getURL",
            uploadZip: "bizcard/uploadZip",
            deleteZip: "bizcard/deleteZip",
            uploadCsv: "bizcard/uploadCsv",
            multipleRegister: "bizcard/multipleRegister",
        }),
        onSearch: async function (resetPaging) {
            this.selectAll = false;

            if (resetPaging) {
                this.pagination.from = 1;
                this.pagination.currentPage = 1;
            } else {
                // 表示するページ数から、何件目の名刺から取得するかを計算
                this.pagination.from = (this.pagination.currentPage - 1) * this.bizcardNumPerPage + 1;
            }

            let info = { filter : this.filter,
                         offset : this.pagination.from - 1,
                         limit : this.bizcardNumPerPage,
                        };

            try {
                this.$vs.loading({
                    type: 'sound',
                });
                let data = await this.search(info);

                for (let i = 0; i < data.bizcardArray.length; i++) {
                    data.bizcardArray[i].selected = false;
                    switch (data.bizcardArray[i].bizcard.charAt(0)) {
                        case "/":
                            data.bizcardArray[i].bizcard = this.base64_prefix.jpeg + data.bizcardArray[i].bizcard;
                            break;
                        case "i":
                            data.bizcardArray[i].bizcard = this.base64_prefix.png + data.bizcardArray[i].bizcard;
                            break;
                        case "R":
                            data.bizcardArray[i].bizcard = this.base64_prefix.gif + data.bizcardArray[i].bizcard;
                            break;
                    }
                }

                // 自分の名刺を取得
                let myBizcardData = await this.getMyBizcard();
                if (myBizcardData.bizcard != null) {
                    switch (myBizcardData.bizcard.bizcard.charAt(0)) {
                        case "/":
                            myBizcardData.bizcard.bizcard = this.base64_prefix.jpeg + myBizcardData.bizcard.bizcard;
                            break;
                        case "i":
                            myBizcardData.bizcard.bizcard = this.base64_prefix.png + myBizcardData.bizcard.bizcard;
                            break;
                        case "R":
                            myBizcardData.bizcard.bizcard = this.base64_prefix.gif + myBizcardData.bizcard.bizcard;
                            break;
                    }
                }
                this.myBizcard = myBizcardData.bizcard;

                this.listBizcard = data.bizcardArray;
                this.pagination.totalItem = data.total_bizcard_num;
                this.pagination.totalPage = Math.floor(data.total_bizcard_num / this.bizcardNumPerPage);
                // 「総件数/1ページ辺りの表示数」が割り切れない場合、ページ数を1増やす
                if (data.total_bizcard_num % this.bizcardNumPerPage > 0) {
                    this.pagination.totalPage++;
                }
                // 表示する名刺が0件の場合は表示開始位置を0に設定
                if (data.total_bizcard_num == 0) {
                    this.pagination.from = 0;
                }
                this.pagination.to = Math.min(data.total_bizcard_num, this.pagination.from + this.bizcardNumPerPage - 1);
            } catch (error) {
                this.listBizcard = [];
            } finally {
                this.$vs.loading.close();
            }
        },
        onSelectAll() {
            this.selectAll = !this.selectAll;
			this.listBizcard.map(item => {item.selected = this.selectAll; return item});
        },
        onClickUpdateButton: function(bizcard) {
            // 更新の場合は各パラメータを元の値にセット
            this.updateMode = true;
            Object.keys(this.parameters).forEach(key => {
                this.parameters[key] = bizcard[key];
            });
            this.updateBizcardId = bizcard.bizcard_id;
            this.base64Image = bizcard.bizcard;
            this.onOpenInputBizcardModal();
        },
        onOpenInputBizcardModal: function() {
            this.$modal.show('input-bizcard-modal');
            this.imageFileError = "";
            this.$validator.reset();

            // 登録の場合は各パラメータを空にする
            if (!this.updateMode) {
                this.updateBizcardId = null;
                this.base64Image = "";
                Object.keys(this.parameters).forEach (key => {
                    this.parameters[key] = "";
                });
            }
            // 自分の名刺に対する操作かそうでないかを設定する
            this.parameters.my_bizcard = this.myBizcardOperation;
            this.parameters.biz_card_image = null;
        },
        upload: async function(event) {
            const files = event.target.files || event.dataTransfer.files;
            const file = files[0];

            // ファイルのバリデーション
            if (this.validateFile(file)) {
                await this.getBase64(file);
            }
        },
        validateFile: function(file) {
            const SIZE_LIMIT = 5000000 // 5MB

            // ローカルマシンからの読み込みをキャンセルしたら処理中断
            if (!file) {
                this.imageFileError = "";
                return false;
            }
            // jpeg, png, gif 関連ファイル以外は受付けない
            switch (file.type) {
                case "image/jpeg":
                    this.selectedPrefix = this.base64_prefix.jpeg;
                    break;
                case "image/png":
                    this.selectedPrefix = this.base64_prefix.png;
                    break;
                case "image/gif":
                    this.selectedPrefix = this.base64_prefix.gif;
                    break;
                default:
                    this.imageFileError = this.error.biz_card_image.invalid;
                    this.base64Image = "";
                    this.parameters.biz_card_image = null;
                    return false;
            }
            // 上限サイズより大きければ受付けない
            if (file.size > SIZE_LIMIT) {
                this.imageFileError = this.error.biz_card_image.large;
                this.base64Image = "";
                this.parameters.biz_card_image = null;
                return false;
            }
            this.imageFileError = "";
            return true;
        },
        getBase64: function(file) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onload = (e) => {
                    this.base64Image = e.target.result;
                    this.parameters.biz_card_image = this.base64Image.slice(this.selectedPrefix.length);
                    resolve(reader.result);
                }
                reader.onerror = error => reject(error);
            })
        },
        rotateBase64: function() {
            // 元画像の情報を取得
            let imgType = this.base64Image.substring(5, this.base64Image.indexOf(";"));
            let img = new Image();
            img.src = this.base64Image;

            // 画像を右に90度回転
            let canvas = document.createElement('canvas');
            let ctx = canvas.getContext('2d');
            canvas.height = img.width;
            canvas.width = img.height;
            ctx.rotate(90 * Math.PI / 180);
            ctx.translate(0, -canvas.width);
            ctx.drawImage(img, 0, 0);
            this.base64Image = canvas.toDataURL(imgType, 100);
            this.parameters.biz_card_image = this.base64Image.replace(/^data:\w+\/\w+;base64,/, '');
        },
        onRegister: async function() {
            // 画像が選択済かチェック
            if (this.base64Image == "") {
                this.imageFileError = this.error.biz_card_image.required;
            }
            // 画像選択で何らかのエラーがある場合は登録処理をしない
            if (this.imageFileError !== "") {
                return;
            }

            // 画像以外の項目のバリデート
            this.$validator.validateAll().then(async result => {
                if (result) {
                    try {
                        await this.register(this.parameters);
                    } catch (error) {
                        console.log(error);
                    } finally {
                        this.$modal.hide('input-bizcard-modal');
                        this.onSearch(false);
                    }
                }
            });
        },
        onUpdate: async function() {
            // 画像が選択済かチェック
            if (this.base64Image == "") {
                this.imageFileError = this.error.biz_card_image.required;
            }
            // 画像選択で何らかのエラーがある場合は更新処理をしない
            if (this.imageFileError !== "") {
                return;
            }

            // 画像以外の項目のバリデート
            this.$validator.validateAll().then(async result => {
                if (result) {
                    try {
                        await this.update({bizcard_id: this.updateBizcardId, param: this.parameters});
                    } catch (error) {
                        console.log(error);
                    } finally {
                        this.$modal.hide('input-bizcard-modal');
                        this.onSearch(false);
                    }
                }
            });
        },
        onOpenZipUploadModal: function() {
            this.zipUploadPopup = true;
        },
        triggerClickEvent: function(id) {
            $('#' + id).trigger("click");
        },
        onUploadZip: async function (event) {
            const files = event.target.files || event.dataTransfer.files;
            try {
                let uploadZipResult = await this.uploadZip(files[0]);
                if (uploadZipResult && uploadZipResult.zip_upload_time != null) {
                    this.zipUploadTime = uploadZipResult.zip_upload_time;
                }
            } catch (error) {
                console.error(error);
            } finally {
                $('#zipUpload').val("");
            }
        },
        onClickDeleteZip: async function() {
            let deleteResult = await this.deleteUploadZip();
            if (deleteResult.success) {
                this.$store.dispatch("alertSuccess", deleteResult.message, { root: true });
            } else {
                this.$store.dispatch("alertError", deleteResult.message, { root: true });
            }
        },
        deleteUploadZip: async function () {
            // 画像クリアボタン押下時や一括登録用ダイアログを閉じた時などに、アップロード済みのzipを削除する
            if (this.zipUploadTime == null) {
                return;
            }
            let deleteZipResult = await this.deleteZip(this.zipUploadTime);
            if (deleteZipResult.result_code === 0) {
                this.zipUploadTime = null;
                return {success: true, message: deleteZipResult.message};
            }
            return {success: false, message: deleteZipResult.result_message};
        },
        onUploadCsv: async function (event) {
            const files = event.target.files || event.dataTransfer.files;
            try {
                let uploadCsvResult = await this.uploadCsv(files[0]);
                if (uploadCsvResult && uploadCsvResult.csv_data != null) {
                    // APIから取得したデータにクラス格納用の要素を追加
                    this.csvData = uploadCsvResult.csv_data.map(data => {
                        return data.map(item => {
                            return {
                                "data" : item,
                                "class" : "",
                            }
                        })
                    })
                    this.csvValidation();
                }
            } catch (error) {
                console.error(error);
            } finally {
                $('#csvUpload').val("");
            }
        },
        onClickModify: function (data, index) {
            // CSV取込結果の修正ボタン押下時に、データ修正用ダイアログを表示する
            this.modifyData = data.slice().map(item => {
                return item.data;
            });
            this.modifyIndex = index;
            this.$validator.reset();
            this.dataModifyPopup = true;
        },
        onModify: function() {
            // データ修正用ダイアログの修正ボタン押下時に、CSVデータの修正を実行する
            // VeeValidateによるバリデーション + ファイル名が入力済かチェック
            this.$validator.validateAll().then(result => {
                if (result && this.modifyData[this.csvDataColumn.file_name] != '') {
                    this.csvData[this.modifyIndex] = this.modifyData.slice().map(item => {
                        return {
                                "data" : item,
                                "class" : "",
                            }
                    });
                    this.dataModifyPopup = false;
                    this.csvValidation();
                }
            });
        },
        csvValidation: function() {
            // CSVアップデート時・データ修正時のバリデーション
            this.displayCsvErrorMessage.clear();
            // ファイル名だけを抜き出し、重複しているものをSetに保持しておく
            let fileNames = this.csvData.map(data => {
                return data[this.csvDataColumn.file_name].data;
            });
            let duplicateFileNames = new Set(
                fileNames.filter((val, index, orgArray) => {
                    // 「最初に見つかる位置 == 最後に見つかる位置」でない場合は重複データ
                    return orgArray.indexOf(val) !== orgArray.lastIndexOf(val);
                })
            );
            this.csvData.forEach((data, index, orgData) => {
                orgData[index][this.csvDataColumn.has_error] = false;
                // 名前
                if (!this.validateCsvColumnLength(128, this.csvDataColumn.name, data, orgData[index])) {
                    this.displayCsvErrorMessage.add(this.csvErrorMessages.nameMaxOver);
                }
                // 名前（かな・カナ）
                if (!this.validateCsvColumnLength(128, this.csvDataColumn.name_kana, data, orgData[index])) {
                    this.displayCsvErrorMessage.add(this.csvErrorMessages.nameKanaMaxOver);
                }
                // 名前（ローマ字）
                if (!this.validateCsvColumnLength(128, this.csvDataColumn.name_romaji, data, orgData[index])) {
                    this.displayCsvErrorMessage.add(this.csvErrorMessages.nameRomajiMaxOver);
                }
                // 会社名
                if (!this.validateCsvColumnLength(256, this.csvDataColumn.company_name, data, orgData[index])) {
                    this.displayCsvErrorMessage.add(this.csvErrorMessages.companyMaxOver);
                }
                // 会社名（かな・カナ）
                if (!this.validateCsvColumnLength(256, this.csvDataColumn.company_kana, data, orgData[index])) {
                    this.displayCsvErrorMessage.add(this.csvErrorMessages.companyKanaMaxOver);
                }
                // 電話番号
                try {
                    let number = phoneUtil.parse(String(data[this.csvDataColumn.phone_number].data), 'JP');
                    if (!phoneUtil.isValidNumberForRegion(number, 'JP')) {
                        orgData[index][this.csvDataColumn.phone_number].class="csv-error";
                        this.displayCsvErrorMessage.add(this.csvErrorMessages.phoneInvalid);
                        orgData[index][this.csvDataColumn.has_error] = true;
                    } else {
                        orgData[index][this.csvDataColumn.phone_number].class="";
                        // 電話番号を成形
                        orgData[index][this.csvDataColumn.phone_number].data = phoneUtil.format(number, PhoneNumberFormat.NATIONAL);
                    }
                } catch (error) {
                    // parseに失敗した場合は不正な電話番号と判定する
                    orgData[index][this.csvDataColumn.phone_number].class="csv-error";
                    this.displayCsvErrorMessage.add(this.csvErrorMessages.phoneInvalid);
                    orgData[index][this.csvDataColumn.has_error] = true;
                }
                // 住所
                if (!this.validateCsvColumnLength(256, this.csvDataColumn.address, data, orgData[index])) {
                    this.displayCsvErrorMessage.add(this.csvErrorMessages.addressMaxOver);
                }
                // 施設名称
                if (!this.validateCsvColumnLength(256, this.csvDataColumn.address_name, data, orgData[index])) {
                    this.displayCsvErrorMessage.add(this.csvErrorMessages.addressNameMaxOver);
                }
                // 郵便番号
                let postal_code = data[this.csvDataColumn.postal_code].data;
                let postalCodeRegExp = /^[0-9]{3}-[0-9]{4}$/;
                if(!postal_code.match(postalCodeRegExp) && 0 < postal_code.length) {
                    orgData[index][this.csvDataColumn.postal_code].class="csv-error";
                    this.displayCsvErrorMessage.add(this.csvErrorMessages.postalCodeInvalid);
                    orgData[index][this.csvDataColumn.has_error] = true;
                } else {
                    orgData[index][this.csvDataColumn.postal_code].class="";
                }
                // 住所（英語表記）
                if (!this.validateCsvColumnLength(256, this.csvDataColumn.address_en, data, orgData[index])) {
                    this.displayCsvErrorMessage.add(this.csvErrorMessages.addressEnMaxOver);
                }
                // メールアドレス
                let email = data[this.csvDataColumn.email].data;
                let emailRegExp = /^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)+$/;
                if((!email.match(emailRegExp) && 0 < email.length) || 256 < email.length) {
                    orgData[index][this.csvDataColumn.email].class="csv-error";
                    orgData[index][this.csvDataColumn.has_error] = true;
                    if (!email.match(emailRegExp) && 0 < email.length) {
                        // 形式が間違っている場合
                        this.displayCsvErrorMessage.add(this.csvErrorMessages.emailInvalid);
                    }
                    if (256 < email.length) {
                        // 最大文字数を超えている場合
                        this.displayCsvErrorMessage.add(this.csvErrorMessages.emailMaxOver);
                    }
                } else {
                    orgData[index][this.csvDataColumn.email].class="";
                }
                // 部署
                if (!this.validateCsvColumnLength(128, this.csvDataColumn.department, data, orgData[index])) {
                    this.displayCsvErrorMessage.add(this.csvErrorMessages.departmentMaxOver);
                }
                // 役職
                if (!this.validateCsvColumnLength(128, this.csvDataColumn.position, data, orgData[index])) {
                    this.displayCsvErrorMessage.add(this.csvErrorMessages.positionMaxOver);
                }
                // 職種・資格・その他肩書等
                if (!this.validateCsvColumnLength(256, this.csvDataColumn.person_title, data, orgData[index])) {
                    this.displayCsvErrorMessage.add(this.csvErrorMessages.personTitleMaxOver);
                }
                // URL
                if (!this.validateCsvColumnLength(256, this.csvDataColumn.url, data, orgData[index])) {
                    this.displayCsvErrorMessage.add(this.csvErrorMessages.urlMaxOver);
                }
                // ファイル名
                if (data[this.csvDataColumn.file_name].data == "" || data[this.csvDataColumn.file_name].data == null) {
                    // ファイル名が空の時
                    orgData[index][this.csvDataColumn.file_name].class="csv-error";
                    this.displayCsvErrorMessage.add(this.csvErrorMessages.fileNameEmpty);
                    orgData[index][this.csvDataColumn.has_error] = true;
                } else if(duplicateFileNames.has(data[this.csvDataColumn.file_name].data)) {
                    // ファイル名が重複している時
                    orgData[index][this.csvDataColumn.file_name].class="csv-error";
                    this.displayCsvErrorMessage.add(this.csvErrorMessages.fileNameDuplicate);
                    orgData[index][this.csvDataColumn.has_error] = true;
                } else {
                    orgData[index][this.csvDataColumn.file_name].class="";
                }
            }, this);
        },
        // CSVデータの長さチェック
        validateCsvColumnLength: function (maxLength, csvColumn, data, orgData) {
            if (maxLength < data[csvColumn].data.length) {
                orgData[csvColumn].class = "csv-error";
                orgData[this.csvDataColumn.has_error] = true;
                return false;
            }
            orgData[csvColumn].class = "";
            return true;
        },
        onMultipleRegister: async function() {
            let validCsvData = [];
            if (this.csvData != null) {
                // CSVデータアップロード済の場合、エラーのないものだけ絞り込む
                validCsvData = this.csvData.filter(data => {
                    return !data[this.csvDataColumn.has_error]
                });
                // クラス指定用の要素を取り除く
                validCsvData = validCsvData.map(data => {
                    return data.map(item => {
                        return item.data;
                    })
                });
                validCsvData = validCsvData.map(data => {
                    return {
                        'name' : data[this.csvDataColumn.name],
                        'name_kana' : data[this.csvDataColumn.name_kana],
                        'name_romaji' : data[this.csvDataColumn.name_romaji],
                        'company_name' : data[this.csvDataColumn.company_name],
                        'company_kana' : data[this.csvDataColumn.company_kana],
                        'phone_number' : data[this.csvDataColumn.phone_number],
                        'address' : data[this.csvDataColumn.address],
                        'address_name' : data[this.csvDataColumn.address_name],
                        'postal_code' : data[this.csvDataColumn.postal_code],
                        'address_en' : data[this.csvDataColumn.address_en],
                        'email' : data[this.csvDataColumn.email],
                        'department' : data[this.csvDataColumn.department],
                        'position' : data[this.csvDataColumn.position],
                        'person_title' : data[this.csvDataColumn.person_title],
                        'url' : data[this.csvDataColumn.url],
                        'file_name' : data[this.csvDataColumn.file_name],
                    }
                }, this);
            }
            try {
                await this.multipleRegister({
                    'zip_upload_time' : this.zipUploadTime,
                    'bizcard_data' : validCsvData,
                });
            } catch (error) {
                console.log(error);
            } finally {
                this.zipUploadPopup = false;
                this.onSearch(false);
            }
        },
        zipDeleteOnLeavePage: function () {
            // 画面更新時やブラウザ(タブ)を閉じる時にzipアップロード済なら削除する
            if (this.zipUploadTime == null) {
                return;
            }
            fetch(`${config.BASE_API_URL}/multipleBizcard/deleteZipContents/${this.zipUploadTime}`, {
                method: "POST",
                keepalive: true,
                headers: {
                    "Authorization": `Bearer ${sessionStorage.getItem('token')}`
                }
            });
        },
        onDelete: async function () {
            try {
                this.$vs.loading({
                    type: 'sound',
                });
                let info = { bizcardIds : this.getSelectedID(),
                             myBizcard : this.myBizcardOperation,
                           };
                await this.delete(info);
            } catch (error) {
                console.log(error);
            } finally {
                this.$vs.loading.close();
                this.confirmDelete = false;
                this.onSearch(false);
            }
        },
        onClickShowURLButton: async function(bizcard_id) {
            this.showLinkPageURLPopup = true;
            let data = await this.getURL(bizcard_id);
            if (data.result_code == 0) {
                this.showLinkPageURLPopup = true;
                this.linkPageURL = data.link_page_url;
            }
        },
        copyURLToClipboard: function() {
            this.$copyText(this.linkPageURL).then(() => {
                this.$store.dispatch("alertSuccess", "URLをコピーしました。", { root: true });
            }, e => {
                this.$store.dispatch("alertError", "URLをコピーできませんでした。", { root: true });
                console.log(e);
            })
        },
        downLoadQrCode: function() {
            let QrImage = document.getElementById('QrCode');

            const QrCanvas = document.createElement('canvas');
            QrCanvas.width = QrImage.width;
            QrCanvas.height = QrImage.height;
            QrCanvas.getContext('2d').drawImage(QrImage, 0, 0);

            let link = document.createElement("a");
            link.href = QrCanvas.toDataURL("image/png");
            link.download = "qrcode.png";
            link.click();            
        },
        getSelectedID(){
            let bizcardIds = [];
            this.deleteBizcardList.forEach((item, stt) => {
                bizcardIds.push(item.bizcard_id)
            });
            return bizcardIds;
        },
        handleSort(key, active) {
            this.orderBy = key;
            this.orderDir = active?"DESC":"ASC";
            this.onSearch(false);
        },
        onRowCheckboxClick: function (tr) {
          tr.selected = !tr.selected
          this.selectAll = this.listBizcard.every(item => item.selected);
        }
    },
    watch:{
        'pagination.currentPage': function (val) {
            this.onSearch(false);
        },
        zipUploadPopup: async function (val) {
            if (!val) {
                // 一括登録用ダイアログを閉じた時にzip、csvアップロード済みなら消去する
                await this.deleteUploadZip();
                this.csvData = null;
            }
        },
        csvData: function(val) {
            this.zipUploadPopupClass = val ? "csv-uploaded" : "zip-upload-popup";
        }
    },
    mounted() {
        this.onSearch(false);
        $(window).on('beforeunload.bizcard', () => {
            this.zipDeleteOnLeavePage();
        });
    },
    destroyed() {
        $(window).off('beforeunload.bizcard');
    }
}

</script>

<style lang="stylus" scoped>
.bizcard_img {
    width: 100px;
}
.my_bizcard_img {
    max-width: 90%;
}
.detail{
    margin-bottom: 20px;
    margin-left: 0.75rem;
    .label{ background: #b3e5fb; padding: 3px; }
    .info{  padding: 3px 3px 3px 5px; word-wrap:break-word; overflow-wrap:break-word; }
}
.uploaded_image {
    width: 200px;
    vertical-align: bottom;
}
.uploaded_image_row {
    margin-bottom: 10px;
    position: relative;
}
.input_area {
    height: 65vh;
    overflow-y: scroll;
}
.rotate_button {
    position: absolute;
    right: 170px;
    bottom: 10px;
}
.my_bizcard_panel{
    height: calc(100vh - 60px);
    margin-left: 25px;
    background: #fff;
    flex-direction: column;
}
.my_bizcard_area {
    position: fixed;
    right: 0px;
    top: 60px;
}
.bizcard_info_detail {
    word-wrap: break-word;
    overflow-wrap: break-word;
    width: 100%;
}
.zip-upload-popup /deep/ .vs-popup {
    width: 800px !important;
}
.csv-uploaded /deep/ .vs-popup {
    width: 1500px !important;
}
.csv-modify /deep/ .vs-row {
    align-items: center;
}
.csv_result_title {
    background: #f2f2f2;
    border-left: 4px solid #8a8a8a;
    text-align: left;
    padding: 3px 10px;
    font-weight: 600;
    margin-top: 10px;
}
.csv-error {
    color: red;
    background-color: rgba(234, 84, 85, .15);
}
</style>
