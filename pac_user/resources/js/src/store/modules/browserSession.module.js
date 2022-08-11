
const SESSION_STORAGE_KEY = "browserSessionId";

const generateSessionId = function () {
  // このIDは次の2つの(sessionStorageでいう)セッションが同一か判定するためにだけ使用する
  // - ブラウザ内で最後にstateを永続化したセッション
  // - 現在のセッション
  const num = Math.floor(Math.random() * Number.MAX_SAFE_INTEGER);
  return num.toString(36);
};

const setNewIdToSessionStorage = function() {
  const id = generateSessionId();
  sessionStorage.setItem(SESSION_STORAGE_KEY, id);
  return id;
}

// id 取得 なければ生成 & sessionStorage へセット
const currentSessionId = sessionStorage.getItem(SESSION_STORAGE_KEY) || setNewIdToSessionStorage();

const state = {
  // 永続化される/されたセッションのID
  currentSession: null,
}

const actions = {
  init({ dispatch, commit, state }, payload) {
    // 複数タブを開いた場合に別タブのファイルが消される問題に対処する
    // （別タブのファイル情報を破棄する）
    const persistedSessionId = state.currentSession;

    const hasOtherSessionData = currentSessionId != persistedSessionId;
    if (hasOtherSessionData) {
      // 別タブで作業中のファイルが消されるのを防ぐため
      commit("home/homeClearState", null, { root: true });
    }
    
    // 他のstateと共に永続化されるようにする
    commit('setCurrentSession', currentSessionId);
  }
};

const mutations = {
  setCurrentSession(state, id) {
    state.currentSession = id;
  }
};

export const browserSession = {
  namespaced: true,
  state,
  actions,
  mutations
};