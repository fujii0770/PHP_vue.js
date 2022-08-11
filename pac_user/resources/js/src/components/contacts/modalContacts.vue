
<template>
   
    <div>
        <vs-popup :active.sync="confirmDuplicateEmail" title="既に登録済みのメールアドレスです。このまま登録しますか？">
            <vs-row class="mt-3">
                <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end"  vs-w="12">                     
                    <vs-button @click="onAllowDuplicate" color="warning">はい</vs-button>
                    <vs-button @click="onCancelDuplicate" color="dark" type="border">いいえ</vs-button>
                </vs-col>
            </vs-row>
        </vs-popup>

        <vs-popup :active.sync="confirmDelete" title="アドレス帳から削除します。よろしいですか？">
            <vs-row class="mt-3">
                <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end"  vs-w="12">                     
                    <vs-button @click="onDeleteContact" color="warning">はい</vs-button>
                    <vs-button @click="confirmDelete = false" color="dark" type="border">いいえ</vs-button>
                </vs-col>
            </vs-row>
        </vs-popup>

        <vs-popup classContent="popup-example modal-contacts"  title="アドレス帳編集" :active.sync="activeModal">
             
              <div class="list-contacts">
                <div class="input-group">
                    <vs-input class="w-full" placeholder="グループ or 名前 or メールアドレス" v-model="filter"/>
                    <span class="icon" @click="onSearch">
                      <vs-icon icon="search" size="medium"   />
                    </span>
                </div>
                
                <vs-list style="height: 300px; overflow: auto;" class="mt-3">
                    <vs-collapse>
                        <vs-collapse-item v-for="(contacts, groupName) in groupContacts" :key="groupName">
                            <div slot="header"><vs-list-header :title="groupName || 'グループなし'"  icon="folder"></vs-list-header></div>
                            <div v-for="(contact, index) in contacts" :key="index" @click="onEditContact(contact)">
                                <vs-list-item :class="[{'state-0': contact.state == 0}]"
                                :title="contact.name" :subtitle="contact.email"  :icon="contact.state == 1?'person':'person_add_disabled'"></vs-list-item>
                            </div>
                        </vs-collapse-item>
                    </vs-collapse>
                </vs-list>
              </div>

              <div class="mt-5">
                <form>
                  <vs-row>
                      <vs-col vs-w="4" class="text-right pr-3 pt-3">グループ</vs-col>
                      <vs-col vs-type="" vs-w="8">
                          <vs-input placeholder="グループ" v-model="editContact.group_name" class="w-full" />
                      </vs-col>
                  </vs-row>
                  <vs-row class="mt-3">
                      <vs-col vs-w="4" class="text-right pr-3 pt-3">名前<span class="ml-1 text-red">*</span></vs-col>
                      <vs-col vs-type="" vs-w="8">
                          <vs-input placeholder="名前" v-validate="'required'" v-model="editContact.name" name="name" class="w-full" />
                          <span class="text-danger text-sm" v-show="errors.has('name')">{{ errors.first('name') }}</span>
                      </vs-col>
                  </vs-row>
                  <vs-row class="mt-3">
                      <vs-col vs-w="4" class="text-right pr-3 pt-3">メールアドレス<span class="ml-1 text-red">*</span></vs-col>
                      <vs-col vs-type="" vs-w="8">
                          <vs-input type="email" v-validate="'required|email'" required name="email"
                            placeholder="メールアドレス" v-model="editContact.email" class="w-full" />
                          <span class="text-danger text-sm" v-show="errors.has('email')">{{ errors.first('email') }}</span>
                      </vs-col>
                  </vs-row>
                </form>
            </div>

            <vs-row class="mt-5">
                <vs-col vs-type="flex" vs-align="flex-end" vs-justify="flex-end"  vs-w="12">
                    <vs-button @click="onUpdateContact()" color="success" v-if="editContact.id">変更</vs-button>
                    <vs-button @click="onNewContact()" color="primary">新規登録</vs-button>
                    <vs-button @click="confirmDelete = true" color="danger" v-if="editContact.id">削除</vs-button>
                    <vs-button @click="activeModal = false" color="dark" type="border">キャンセル</vs-button>
                </vs-col>
            </vs-row>
          
        </vs-popup>
        
    </div>
 
</template>

<script>
import { Validator } from 'vee-validate';
import { mapState, mapActions } from "vuex";

const dict = {
      custom: {
        name: {
          required: '* 必須項目です',
        },
        email: {
          required: '* 必須項目です',
          email: "* メールアドレスが正しくありません"
        }
      }
    };
    Validator.localize('ja', dict);

export default {
  name: 'modal-contacts',
  props: {     
    item  : {  },
  },
  data: () => ({
    activeModal: false,
    confirmDuplicateEmail: false,
    action: "",    
    filter: '',
    listContact:[],
    editContact:{},
    confirmEdit:false,
    confirmDelete:false,
  }),
  watch: {
    '$store.state.showModalContacts': function (newVal, oldVal) {
      this.editContact = {};
      if(newVal == true){
        this.onSearch();
        this.activeModal = newVal;
      }
        this.addLogOperation({ action: 'addr-display', result: 0});
    },
    activeModal(newVal){
      if(newVal == false){
        this.$store.commit('SET_DEACTIVATE_STATE', 'showModalContacts');
      }
    },
   
  },
  computed:{ 
    groupContacts: {
      get: function () {
        var groups = {};
        this.listContact.forEach((contact, stt) => {
          contact.group_name = contact.group_name || '';
          if(!groups[contact.group_name]) groups[contact.group_name] = [];
          groups[contact.group_name].push(contact);
        }); 
        return groups;
      }
    }
  },
  methods: {
        ...mapActions({
            getListContact: "contacts/getListContact",
            getContact: "contacts/getContact",
            updateContact: "contacts/updateContact",
            addNewContact: "contacts/addNewContact",
            deleteContact: "contacts/deleteContact",
            addLogOperation: "logOperation/addLog",
        }),

        showModal(){
          this.activeModal  = true;
          this.onSearch();
        },

        async onSearch() {
            this.listContact = await this.getListContact({filter: this.filter});
        },        
        async onEditContact(contact) { // event click row
            this.$validator.reset();
            this.editContact = await this.getContact(contact.id);
        },
        async onUpdateContact(allowDuplicate){ // click btn update; 15 INFR05001 ERRR05002
          this.action = "update";
          this.$validator.validateAll().then(async result => {
              if (result) {
                if(!allowDuplicate && !this.checkEmail(this.editContact.email, this.editContact.id)){
                  this.confirmDuplicateEmail = true;
                  return;
                }
                await this.updateContact(this.editContact, this.editContact.id);
                this.onSearch();
                this.editContact = {};
              }              
          });
        },
        async onNewContact(allowDuplicate){ // click btn new; 12 INFR05001 ERRR05002
          this.action = "new";
          this.$validator.validateAll().then(async result => {
              if (result) {
                if(!allowDuplicate && !this.checkEmail(this.editContact.email)){
                  this.confirmDuplicateEmail = true;
                  return;
                }
                this.editContact.id = 0;
                await this.addNewContact(this.editContact);
                this.onSearch();
                this.editContact = {};
              }              
          });
          
        },
        async onDeleteContact(){ // 13 INFR05002 ERRR05003
            await this.deleteContact(this.editContact.id);
            this.confirmDelete = false;
            this.editContact = {};
            this.onSearch();
        },
        checkEmail(email, id) {
          for(var i in this.listContact){
            var contact  = this.listContact[i];
            if(id){
              if(contact.email == email && id != contact.id) return false;
            }else{
              if(contact.email == email) return false;
            }
          }           
          return true;
        },
        onAllowDuplicate(){ 
          this.confirmDuplicateEmail = false;
          if(this.action == 'new')  this.onNewContact(true);
          if(this.action == 'update')  this.onUpdateContact(true);
        },
        onCancelDuplicate(){ 
          this.confirmDuplicateEmail = false;
        },        
    },
    mounted(){
      this.activeModal = this.$store.state.showModalContacts;
      if(this.activeModal == true){
        this.onSearch();
      }
    }
}

</script>

