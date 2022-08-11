import contactsService from "../../services/contacts.service";
import fileDownload from "js-file-download";

const state = {
  changePhoneBooks: false,
};

const actions = {
  getListContact({ dispatch, commit }, info) { 
      return contactsService.getListContact(info).then(
          response => {
            return Promise.resolve(response.data);
          },
          error => {
            dispatch("alertError", error, { root: true });
            return Promise.reject(false);
          }
      );
    },  
    getContact({ dispatch, commit }, id) { 
      return contactsService.getContact(id).then(
          response => {
            return Promise.resolve(response.data);
          },
          error => {
            dispatch("alertError", error, { root: true });
            return Promise.reject(false);
          }
      );
    },

    updateContact({ dispatch, commit }, info) { 
      return contactsService.updateContact(info).then(
          response => {
            commit("notifyChangedPhoneBook");
            dispatch("alertSuccess", response.message, { root: true });
            return Promise.resolve(response.data);
          },
          error => {
            dispatch("alertError", error, { root: true });
            return Promise.reject(false);
          }
      );
    },

    addNewContact({ dispatch, commit }, info) { 
      return contactsService.addNewContact(info).then(
          response => {
            commit("notifyChangedPhoneBook");
            dispatch("alertSuccess", response.message, { root: true });
            return Promise.resolve(response.data);
          },
          error => {
            dispatch("alertError", error, { root: true });
            return Promise.reject(false);
          }
      );
    },

    deleteContact({ dispatch, commit }, id) { 
      return contactsService.deleteContact(id).then(
          response => {
            commit("notifyChangedPhoneBook");
            dispatch("alertSuccess", response.message, { root: true });
            return Promise.resolve(response.data);
          },
          error => {
            dispatch("alertError", error, { root: true });
            return Promise.reject(false);
          }
      );
    },
     
};

const mutations = {

  notifyChangedPhoneBook(state, data) {
    state.changePhoneBooks = !state.changePhoneBooks;
  }
    
};

export const contacts = {
    namespaced: true,
    state,
    actions,
    mutations
};
