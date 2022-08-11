import protalService from "../../services/portal.service";
import Axios from "axios";
import config from "../../app.config";

const state = {
  addFavoriteInternal: true,
  listFavorite: [],
  listService: [],
  listMyPages: [],
  currentMyPage: null,
  bbsDispList:'topiclist',
  isDispWide:false,
  editStatus: false,
  currentLayout: [],
  selectPage: {},
  currentComponent: [],
  changeTemplateFlg: false,
  hasData: {},
  changePermission: false,
  changePageNameFlg: false,
  /*PAC_5-3156 S*/
  faqBbsUnreadNoticeCount:0, 
  faqBbsUnreadNoticeTimeHandle:null,
  /*PAC_5-3156 E*/
};

const actions = {

    getListFavorite({ dispatch, commit }, mypage_id) {
        return protalService.getListFavorite(mypage_id).then(
            response => {
                commit('updateListFavorite', response.data)
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }
        );
    },

    getListServiceInternal({ dispatch, commit }) {
        return protalService.getListServiceInternal().then(
            response => {
                commit('updateListService', response.data)
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }
        );
    },

    addFavorite({ dispatch, commit }, data) {
        return protalService.addFavorite(data).then(
          response => {
            if(!response) return;
            dispatch("alertSuccess", response.message, { root: true });
            return Promise.resolve(true);
          },
          error => {
            dispatch("alertError", error, { root: true });
            return Promise.resolve(false);
          }
        );
    },

    deleteFavorite({ dispatch, commit }, id) {
        return protalService.deleteFavorite(id).then(
          response => {
            if(!response) return;
            dispatch("alertSuccess", response.message, { root: true });
            return Promise.resolve(true);
          },
          error => {
            dispatch("alertError", error, { root: true });
            return Promise.resolve(false);
          }
        );
    },

    getMyPages({ dispatch, commit }) {
      return protalService.getMyPages().then(
          response => {
              commit('updateMyPages', response.data)
              return Promise.resolve(response.data);
          },
          error => {
              dispatch("alertError", error, { root: true });
              return Promise.reject(false);
          }
      );
    },

    saveMyPage({ dispatch, commit }, data) {
        return protalService.saveMyPage(data).then(
            response => {
                dispatch("alertSuccess", response.message, { root: true });
                return Promise.resolve(true);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },

    updateMyPage({ dispatch, commit }, info) {
        return protalService.updateMyPage(info).then(
            response => {
                dispatch("alertSuccess", response.message, { root: true });
                return Promise.resolve(response);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },

    updateMyPageInBackground({ dispatch, commit }, info) {
        return protalService.updateMyPage(info).then(
            response => {
               // dispatch("alertSuccess", response.message, { root: true });
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },

    deleteMyPage({ dispatch, commit }, id) {
        return protalService.deleteMyPage(id).then(
            response => {
                if(!response) return;
                dispatch("alertSuccess", response.message, { root: true });
                return Promise.resolve(true);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },

    getMyPageLayout({ dispatch, commit }, info) {
        return protalService.getMyPageLayout(info).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },

    getTopicList({ dispatch, commit }, info) {

        return protalService.getTopicList(info).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    getFaqTopicList({ dispatch, commit }, info) {

        return protalService.getFaqTopicList(info).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    getBbsCategories({ dispatch, commit }, info) {

        return protalService.getBbsCategories(info).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    getFaqBbsCategories({ dispatch, commit }, info) {

        return protalService.getFaqBbsCategories(info).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    getBbsAuth({ dispatch, commit }) {

        return protalService.getBbsAuth().then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    getBbsMember({ dispatch, commit }) {

        return protalService.getBbsMember().then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    getBbsMemberForPage({ dispatch, commit }, {page, search}) {
        return protalService.getBbsMemberForPage(page, search).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    getBbsMemberListByIds({ dispatch, commit }, ids) {
        return protalService.getBbsMemberListByIds(ids).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    deleteBbsTopic({ dispatch, commit }, info) {
        return protalService.deleteBbsTopic(info).then(
            response => {
                if(!response) return;
                dispatch("alertSuccess", response.message, { root: true });
                return Promise.resolve(true);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    deleteFaqBbsTopic({ dispatch, commit }, info) {
        return protalService.deleteFaqBbsTopic(info).then(
            response => {
                if(!response) return;
                dispatch("alertSuccess", response.message, { root: true });
                return Promise.resolve(true);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    deleteBbsComment({ dispatch, commit }, info) {
        return protalService.deleteBbsComment(info).then(
            response => {
                if(!response) return;
                dispatch("alertSuccess", response.message, { root: true });
                return Promise.resolve(true);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    deleteFaqBbsComment({ dispatch, commit }, info) {
        return protalService.deleteFaqBbsComment(info).then(
            response => {
                if(!response) return;
                dispatch("alertSuccess", response.message, { root: true });
                return Promise.resolve(true);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    deleteBbsCategory({ dispatch, commit }, info) {
        return protalService.deleteBbsCategory(info).then(
            response => {
                if(!response) return;
                dispatch("alertSuccess", response.message, { root: true });
                return Promise.resolve(true);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },

    updateBbsTopic({ dispatch, commit }, info) {
        return protalService.updateBbsTopic(info).then(
            response => {
                dispatch("alertSuccess", response.message, { root: true });
                return Promise.resolve(response);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    updateFaqBbsTopic({ dispatch, commit }, info) {
        return protalService.updateFaqBbsTopic(info).then(
            response => {
                dispatch("alertSuccess", response.message, { root: true });
                return Promise.resolve(response);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    updateBbsComment({ dispatch, commit }, info) {
        return protalService.updateBbsComment(info).then(
            response => {
                dispatch("alertSuccess", response.message, { root: true });
                return Promise.resolve(response);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    updateFaqBbsComment({ dispatch, commit }, info) {
        return protalService.updateFaqBbsComment(info).then(
            response => {
                dispatch("alertSuccess", response.message, { root: true });
                return Promise.resolve(response);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    updateBbsCategory({ dispatch, commit }, info) {
        return protalService.updateBbsCategory(info).then(
            response => {
                dispatch("alertSuccess", response.message, { root: true });
                return Promise.resolve(response);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    addBbsTopic({ dispatch, commit }, info) {
        return protalService.addBbsTopic(info).then(
            response => {
                if(!response) return;
                dispatch("alertSuccess", response.message, { root: true });
                return Promise.resolve(true);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    addFaqBbsTopic({ dispatch, commit }, info) {
        return protalService.addFaqBbsTopic(info).then(
            response => {
                if(!response) return;
                dispatch("alertSuccess", response.message, { root: true });
                return Promise.resolve(true);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    addBbsComment({ dispatch, commit }, info) {
        return protalService.addBbsComment(info).then(
            response => {
                if(!response) return;
                dispatch("alertSuccess", response.message, { root: true });
                return Promise.resolve(true);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    addFaqBbsComment({ dispatch, commit }, info) {
        return protalService.addFaqBbsComment(info).then(
            response => {
                if(!response) return;
                dispatch("alertSuccess", response.message, { root: true });
                return Promise.resolve(true);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    addBbsCategory({ dispatch, commit }, info) {
        return protalService.addBbsCategory(info).then(
            response => {
                if(!response) return;
                dispatch("alertSuccess", response.message, { root: true });
                return Promise.resolve(true);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    getBbsTopicLikes({ dispatch, commit }, info) {
    
        return protalService.getBbsTopicLikes(info).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    addBbsTopicLike({ dispatch, commit }, info) {
        return protalService.addBbsTopicLike(info).then(
            response => {
                if(!response) return;
                // dispatch("alertSuccess", response.message, { root: true });
                return Promise.resolve(true);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    deleteBbsTopicLike({ dispatch, commit }, info) {
        return protalService.deleteBbsTopicLike(info).then(
            response => {
                if(!response) return;
                // dispatch("alertSuccess", response.message, { root: true });
                return Promise.resolve(true);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    addBbsDraftTopic({ dispatch, commit }, info) {
        return protalService.addBbsDraftTopic(info).then(
            response => {
                if(!response) return;
                dispatch("alertSuccess", response.message, { root: true });
                return Promise.resolve(true);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    updateBbsDraftTopic({ dispatch, commit }, info) {
        return protalService.updateBbsDraftTopic(info).then(
            response => {
                dispatch("alertSuccess", response.message, { root: true });
                return Promise.resolve(response);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    deleteBbsDraftTopic({ dispatch, commit }, info) {
        return protalService.deleteBbsDraftTopic(info).then(
            response => {
                if(!response) return;
                dispatch("alertSuccess", response.message, { root: true });
                return Promise.resolve(true);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    timeCardStore({ dispatch, commit }, info) {
        return protalService.timeCardStore(info).then(
            response => {
                if(!response) return;
                dispatch("alertSuccess", response.message, { root: true });
                return Promise.resolve(response);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    timeCardUpdate({ dispatch, commit }, info) {
        return protalService.timeCardUpdate(info).then(
            response => {
                if(!response) return;
                dispatch("alertSuccess", response.message, { root: true });
                return Promise.resolve(true);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    lastPunched({ dispatch, commit }) {
        return protalService.lastPunched().then(
            response => {
                if(!response) return;
                // dispatch("alertSuccess", response.message, { root: true });
                return Promise.resolve(response);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    timeCardList({ dispatch, commit }, info) {
        return protalService.timeCardList(info).then(
            response => {
                if(!response) return;
                // dispatch("alertSuccess", response.message, { root: true });
                return Promise.resolve(response);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    timeCardDownloadCSV({ dispatch, commit }, info) {
        return protalService.timeCardDownloadCSV(info).then(
            response => {
                if(!response) return;
                dispatch("alertSuccess", response.message, { root: true });
                return Promise.resolve(response);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    reserveBbsAttachment({dispatch,commit},info){
        return protalService.reserveBbsAttachment(info).then(
            response => {
                if(!response) return Promise.reject(false);
                dispatch("alertSuccess", response.message, { root: true });
                return Promise.resolve(true);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.reject(false);
            }
        );
    }, 

    getToDoList({ dispatch, commit }, info) {
        return protalService.getToDoList(info).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    getToDoListDetail({ dispatch, commit }, info) {
        return protalService.getToDoListDetail(info).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    addToDoList({ dispatch, commit }, info) {
        return protalService.addToDoList(info).then(
            response => {
                if(!response) return;
                dispatch("alertSuccess", response.message, { root: true });
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    updateToDoList({ dispatch, commit }, info) {
        return protalService.updateToDoList(info).then(
            response => {
                if(!response) return;
                dispatch("alertSuccess", response.message, { root: true });
                return Promise.resolve(true);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    deleteToDoList({ dispatch, commit }, param) {
        return protalService.deleteToDoList(param).then(
            response => {
                if(!response) return;
                dispatch("alertSuccess", response.message, { root: true });
                return Promise.resolve(true);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    getToDoTask({ dispatch, commit }, info) {
        return protalService.getToDoTask(info).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    getToDoTaskDetail({ dispatch, commit }, info) {
        return protalService.getToDoTaskDetail(info).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    addToDoTask({ dispatch, commit }, info) {
        return protalService.addToDoTask(info).then(
            response => {
                if(!response) return;
                dispatch("alertSuccess", response.message, { root: true });
                return Promise.resolve(true);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    updateToDoTask({ dispatch, commit }, info) {
        return protalService.updateToDoTask(info).then(
            response => {
                if(!response) return;
                dispatch("alertSuccess", response.message, { root: true });
                return Promise.resolve(true);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    deleteToDoTask({ dispatch, commit }, info) {
        return protalService.deleteToDoTask(info).then(
            response => {
                if(!response) return;
                dispatch("alertSuccess", response.message, { root: true });
                return Promise.resolve(true);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    doneToDoTask({ dispatch, commit }, id) {
        return protalService.doneToDoTask(id).then(
            response => {
                if(!response) return;
                dispatch("alertSuccess", response.message, { root: true });
                return Promise.resolve(true);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    revokeToDoTask({ dispatch, commit }, id) {
        return protalService.revokeToDoTask(id).then(
            response => {
                if(!response) return;
                dispatch("alertSuccess", response.message, { root: true });
                return Promise.resolve(true);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    getToDoCircular({ dispatch, commit }, info) {
        return protalService.getToDoCircular(info).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    getToDoCircularDetail({ dispatch, commit }, info) {
        return protalService.getToDoCircularDetail(info).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    updateToDoCircularTask({ dispatch, commit }, info) {
        return protalService.updateToDoCircularTask(info).then(
            response => {
                if(!response) return;
                dispatch("alertSuccess", response.message, { root: true });
                return Promise.resolve(true);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    getToDoPublicSchedulerList({ dispatch, commit }, info) {
        return protalService.getToDoPublicSchedulerList(info).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    getToDoGroup({ dispatch, commit }) {
        return protalService.getToDoGroup().then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    getToDoGroupList({ dispatch, commit }) {
        return protalService.getToDoGroupList().then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    getToDoGroupDetail({ dispatch, commit }, id) {
        return protalService.getToDoGroupDetail(id).then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    getToDoDepartment({ dispatch, commit }) {
        return protalService.getToDoDepartment().then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    getToDoUsers({ dispatch, commit }) {
        return protalService.getToDoUsers().then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    addToDoGroup({ dispatch, commit }, info) {
        return protalService.addToDoGroup(info).then(
            response => {
                if(!response) return;
                dispatch("alertSuccess", response.message, { root: true });
                return Promise.resolve(true);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    updateToDoGroup({ dispatch, commit }, info) {
        return protalService.updateToDoGroup(info).then(
            response => {
                if(!response) return;
                dispatch("alertSuccess", response.message, { root: true });
                return Promise.resolve(true);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    deleteToDoGroup({ dispatch, commit }, id) {
        return protalService.deleteToDoGroup(id).then(
            response => {
                if(!response) return;
                dispatch("alertSuccess", response.message, { root: true });
                return Promise.resolve(true);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    settingToDoNotice({ dispatch, commit }, info) {
        return protalService.settingToDoNotice(info).then(
            response => {
                if(!response) return;
                dispatch("alertSuccess", response.message, { root: true });
                return Promise.resolve(true);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    getToDoNoticeConfig({ dispatch, commit }) {
        return protalService.getToDoNoticeConfig().then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    countUnreadToDoNotice({ dispatch, commit }) {
        return protalService.countUnreadToDoNotice().then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    getUnReadToDoNotice({ dispatch, commit }) {
        return protalService.getUnReadToDoNotice().then(
            response => {
                return Promise.resolve(response.data);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    readToDoNotice({ dispatch, commit }, id) {
        return protalService.readToDoNotice(id).then(
            response => {
                if(!response) return;
                dispatch("alertSuccess", response.message, { root: true });
                return Promise.resolve(true);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    readAllToDoNotice({ dispatch, commit }) {
        return protalService.readAllToDoNotice().then(
            response => {
                if(!response) return;
                dispatch("alertSuccess", response.message, { root: true });
                return Promise.resolve(true);
            },
            error => {
                dispatch("alertError", error, { root: true });
                return Promise.resolve(false);
            }
        );
    },
    /*PAC_5-3156 S*/
    getFaqBbsUnreadNoticeCount({dispatch,commit,rootState}) {
        commit("clearFaqBbsUnreadNoticeTimeHandle")
        var handleFunction = ()=>{
            if (rootState.groupware.checkFaqBulletinBoardApp ){
                Axios.get(`${config.BASE_API_URL}/faq_bbs_unread_cnt`).then(response=>{
                    commit("updateFaqBbsUnreadNoticeCount",response.data)
                    return Promise.resolve(true);
                }).catch(error=>{
                    error = error.response;
                    const message = (error && error.data && error.data.message) || error.statusText;
                    dispatch("alertError", message, { root: true });
                    return Promise.resolve(false);
                })
            }
        }
        handleFunction()
        let handle = setInterval(handleFunction,60*5*1000);
        commit("updateFaqBbsUnreadNoticeTimeHandle",handle)
        return Promise.resolve(true)
    }
    /*PAC_5-3156 E*/
};

const mutations = {
  updateAddFavoriteInternal(state, value) {
    state.addFavoriteInternal = value;
  },

  updateListFavorite(state, value) {
    state.listFavorite = value;
  },

  updateListService(state, value) {
    state.listService = value;
  },

  updateMyPages(state, value) {
    state.listMyPages = value;
    state.currentMyPage = value[0].id;
  },

  updateMyPageLayout(state, value) {
    if (state.listMyPages){
        var page = state.listMyPages.find(item => item.id === parseInt(value.id));
        page.layout = value.layout;
    }
  },

  updateCurrentMyPage(state, value) {
    state.currentMyPage = value;
  },
  setBbsDispList(state, value){
    state.bbsDispList = value;
  },
  setDispWide(state, value){
    state.isDispWide = value;
  },
  setEditStatus(state, value) {
      state.editStatus = value;
  },
  updateCurrentLayout(state, value) {
      state.currentLayout = value;
  },
  updateCurrentComponent(state, value) {
      state.currentComponent = value;
  },
  updateSelectPage(state, value) {
      state.selectPage = value;
  },
  updateChangeTemplateFlg(state, value) {
      state.changeTemplateFlg = value;
  },
  updateListMyPages(state, value) {
      state.listMyPages = value;
  },
  updateHasData(state, value) {
      state.hasData[value.name] = value.hasData;
  },
  updatePermission(state, value) {
      state.changePermission = value;
  },
  updateChangePageNameFlg(state, value) {
      state.changePageNameFlg = value;
  },
  /*PAC_5-3156 S*/
  clearFaqBbsUnreadNoticeTimeHandle(state,value){
      clearInterval(state.faqBbsUnreadNoticeTimeHandle)
      state.faqBbsUnreadNoticeTimeHandle = null
  },  
  updateFaqBbsUnreadNoticeTimeHandle(state,value){
      state.faqBbsUnreadNoticeTimeHandle = value
  },
  updateFaqBbsUnreadNoticeCount(state,value){
      state.faqBbsUnreadNoticeCount = value
  },
  /*PAC_5-3156 E*/
};
export const portal = {
    namespaced: true,
    state,
    actions,
    mutations
};
