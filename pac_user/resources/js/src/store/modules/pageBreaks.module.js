import pageBreaksService from "../../services/pageBreaks.service";

const state = {
  // phpサーバーにアップロードしたファイル情報
  uploadFileInfoList: [],
  // 改ページ調整対象ファイルのリスト(現在は1ファイルのみだが、
  // 今後複数ファイルに対応するかも)
  fileInfoList: [],
  // 編集前のドキュメントのID(改ページ調整対象のcircularDocumentIdを指定する)
  circularDocIdBeforeMod: null,
  // 編集後のドキュメントのID(改ページ調整後に捺印画面でタブを選択させるため)
  circularDocIdAfterMod: null,
  // アップロードし、DB登録済みのドキュメント情報(一つの回覧に対するドキュメントリスト)
  registeredDocInfoList: [],
  // 上記のregisteredDocInfoListに対応するcircularId
  circularIdForRegisteredDocs: null,
};

// actionsは外部公開用に設定
const actions = {
  setUploadFileInfoList({ commit }, uploadFileInfoList) {
    commit("setUploadFileInfoList", uploadFileInfoList);
  },
  setFileInfoList({ commit }, fileInfoList) {
    commit("setFileInfoList", fileInfoList);
  },
  changeActiveFileByIndex({ commit }, index) {
    commit("changeActiveFileByIndex", index);
  },
  changeActiveFileMagnification({ commit }, magnification) {
    if(magnification === ""){
      // 空文字ならnullに設定
      magnification = null;
    }
    if(magnification !== null){
      if(magnification < 5){
        magnification = 5
      }
      if(magnification > 200){
        magnification = 200
      }
    }
    commit("changeActiveFileMagnification", magnification);
  },
  changeActiveFileSheetno({ commit }, sheetno) {
    commit("changeActiveFileSheetno", sheetno);
  },
  changeSheetnoByIndex({ commit }, {index, sheetno}) {
    commit("changeSheetnoByIndex", {index: index, sheetno: sheetno});
  },
  setSheetsByIndex({ commit },  {index, sheets}) {
    commit("setSheetsByIndex", {index: index, sheets: sheets});
  },
  setCircularDocIdBeforeMod({ commit },  circularDocIdBeforeMod) {
    commit("setCircularDocIdBeforeMod", circularDocIdBeforeMod);
  },
  setCircularDocIdAfterMod({ commit },  circularDocIdAfterMod) {
    commit("setCircularDocIdAfterMod", circularDocIdAfterMod);
  },
  setCircularIdAndRegisteredDocInfoList({ commit, state },  {circularId, registeredDocInfo}) {
    const circularIdForRegisteredDocs = state.circularIdForRegisteredDocs;
    if (circularId === circularIdForRegisteredDocs) {
      // circularIdが等しい場合、registeredDocInfoListにregisteredDocInfoを追加する
      commit("setRegisteredDocInfoList", [...state.registeredDocInfoList, registeredDocInfo]);
    } else {
      // circularIdが異なる場合、circularIdForRegisteredDocsとregisteredDocInfoListを新しくする
      commit("setCircularIdForRegisteredDocs", circularId);
      commit("setRegisteredDocInfoList", [registeredDocInfo]);
    }
  },
  updateDocUpdateAtByDocId({ commit },  {circularDocumentId, documentDataUpdateAt}) {
    commit("updateDocUpdateAtByDocId", {circularDocumentId: circularDocumentId, documentDataUpdateAt: documentDataUpdateAt});
  },
  removeDocInfoByCircularDocId({ commit }, CircularDocId){
    commit("removeDocInfoByCircularDocId", CircularDocId);
  },
  initCircularIdAndRegisteredDocInfoList({ commit }) {
    commit("setCircularIdForRegisteredDocs", null);
    commit("setRegisteredDocInfoList", []);
  },
  uploadFilesForPageBreak({ dispatch, commit }, targetFiles){
    return pageBreaksService.uploadFilesForPageBreak(targetFiles).then(
      (response) => {
        // responseはeditorType
        const fileInfoList = [
          {
            name: targetFiles[0].name,
            filename: targetFiles[0].originFilename,
            serverFilename: targetFiles[0].serverFilename,
            pdfFilename: targetFiles[0].pdfFilename,
            pdfFilepath: targetFiles[0].pdfFilepath,
            filetype: response,
            loading: false,
            completeInitialLoading: false,
            active: true,
            edited: false,
            sheetno: -1,
            sheets: [],
            magnification: 100,
          },
        ];
        commit('setFileInfoList', fileInfoList);

        return Promise.resolve(response);
      },
      (error) => {
        dispatch("alertError", error, { root: true });
        return Promise.resolve(null);
      }
    );
  },
  rejectPageBreaks({ dispatch }){
    return pageBreaksService.rejectPageBreaks().then(
      (response) => {
        return Promise.resolve(true);
      },
      (error) => {
        dispatch("alertError", error, { root: true });
        return Promise.resolve(false);
      }
    );
  },
  decidePageBreaksBeforeAcceptUpload({ dispatch, commit, state }, breaks){
    // homeのacceptUploadを参考にする
    const uploadFileInfoList = state.uploadFileInfoList;
    return pageBreaksService.decidePageBreaksBeforeAcceptUpload(breaks).then(
      (response) => {
        const responseData = response.data;
        const fileInfo = responseData.fileInfo;

        dispatch('setCircularIdAndRegisteredDocInfoList', {
          circularId: responseData.circular.id,
          registeredDocInfo: {
            circular_document_id: fileInfo.circular_document_id,
            document_data_update_at: fileInfo.document_data_update_at,
            fileAfterUploads: uploadFileInfoList,
          },
        });

        commit("application/updateCommentTitle", "", {root: true});
        commit("application/updateCommentContent", "", {root: true});
        commit("application/updateListUserView", [], {root: true});

        return Promise.resolve(responseData);
      },
      (error) => {
        dispatch("alertError", error, { root: true });
        return Promise.resolve(false);
      }
    );
  },
  decidePageBreaksAfterAcceptUpload({ dispatch, commit, state }, breaks){
    const circular_document_id = state.circularDocIdBeforeMod;
    return pageBreaksService.decidePageBreaksAfterAcceptUpload(breaks).then(
      (response) => {
        const modifiedDocInfo = {
          circularDocumentId: circular_document_id,
          documentDataUpdateAt: response.data.document_data_update_at
        };
        commit('updateDocUpdateAtByDocId', modifiedDocInfo)
        return Promise.resolve(modifiedDocInfo);
      },
      (error) => {
        if (error.isHttpStatus412) {
          // 楽観ロックエラー
          commit('removeDocInfoByCircularDocId', circular_document_id);
        }
        dispatch("alertError", error.message, { root: true });
        return Promise.resolve(false);
      }
    );
  },
  odsPreview({ dispatch }, filename) {
    return pageBreaksService.odsPreview(filename).then(
      (response) => {
        return response.data;
      },
      (error) => {
        dispatch("alertError", error, { root: true });
        return null;
      }
    );
  }
};

// mutationsは外部から変更しないようにする
const mutations = {
  setUploadFileInfoList(state, uploadFileInfoList) {
    state.uploadFileInfoList = uploadFileInfoList;
  },
  setFileInfoList(state, fileInfoList) {
    state.fileInfoList = fileInfoList;
  },
  changeActiveFileByIndex(state, index){
    const fileInfoList = state.fileInfoList.map(fileInfo => {
      fileInfo.active = false;
      return fileInfo;
    })
    fileInfoList[index].active = true;
    state.fileInfoList = fileInfoList;
  },
  changeActiveFileMagnification(state, magnification) {
    state.fileInfoList.find(
      fileInfo => fileInfo.active === true
    ).magnification = magnification;
  },
  changeActiveFileSheetno(state, sheetno){
    state.fileInfoList.find(
      fileInfo => fileInfo.active === true
    ).sheetno = sheetno;
  },
  changeSheetnoByIndex(state, {index, sheetno}) {
    state.fileInfoList[index].sheetno = sheetno;
  },
  setSheetsByIndex(state, {index, sheets}) {
    state.fileInfoList[index].sheets = sheets;
  },
  setCircularDocIdBeforeMod(state, circularDocIdBeforeMod) {
    state.circularDocIdBeforeMod = circularDocIdBeforeMod;
  },
  setCircularDocIdAfterMod(state, circularDocIdAfterMod) {
    state.circularDocIdAfterMod = circularDocIdAfterMod;
  },
  setCircularIdForRegisteredDocs(state, circularIdForRegisteredDocs) {
    state.circularIdForRegisteredDocs = circularIdForRegisteredDocs;
  },
  setRegisteredDocInfoList(state, registeredDocInfoList) {
    state.registeredDocInfoList = registeredDocInfoList;
  },
  updateDocUpdateAtByDocId(state, {circularDocumentId, documentDataUpdateAt}) {
    const registeredDocInfo = state.registeredDocInfoList.find(
      registeredDocInfo => registeredDocInfo.circular_document_id === circularDocumentId
    );
    if (registeredDocInfo) {
      registeredDocInfo.document_data_update_at = documentDataUpdateAt;
    }
  },
  removeDocInfoByCircularDocId(state, CircularDocId){
    const findIndex = state.registeredDocInfoList.findIndex(
      docInfo => docInfo.circular_document_id === CircularDocId);
    state.registeredDocInfoList.splice(findIndex, 1);
  },
};

export const pageBreaks = {
  namespaced: true,
  state,
  actions,
  mutations
};
