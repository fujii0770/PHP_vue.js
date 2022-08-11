
import Vue from 'vue'
import Vuex from 'vuex'
import createPersistedState from 'vuex-persistedstate'

import state from "./state"
import getters from "./getters"
import mutations from "./mutations"
import actions from "./actions"
import { home } from "./modules/home.module"
import { hr } from "./modules/hr.module"
import { circulars } from "./modules/circulars.module"
import { special } from "./modules/special.module"
import { contacts } from "./modules/contacts.module"
import { application } from "./modules/application.module"
import { fileMail } from "./modules/fileMail.module"
import { user } from "./modules/user.module"
import { favorite } from "./modules/favorite.module"
import { setting } from "./modules/setting.module"
import { cloud } from "./modules/cloud.module"
import { logOperation } from "./modules/logOperation.module"
import { viewingUser } from "./modules/viewingUser.module"
import { portal } from "./modules/portal.module"
import { notice } from "./modules/notice.module"
import { advertise } from "./modules/advertise.module"
import { movie } from "./modules/movie.module"
import { customizeArea } from "./modules/customizeArea.module"
import { bizcard } from "./modules/bizcard.module"
import { template } from "./modules/template.module"
import { browserSession } from "./modules/browserSession.module"
import { groupware } from "./modules/groupware.module"
import { templateRoute } from "./modules/templateRoute.module"
import { workList } from "./modules/workList.module"
import { dailyReport } from "./modules/dailyReport.module"
import { timeCardDetail } from "./modules/timeCardDetail.module"
import { pageBreaks } from "./modules/pageBreaks.module"
import { formIssuance } from "./modules/formIssuance.module"
import { expenseSettlement } from "./modules/expenseSettlement.module"
import { expense } from "./modules/expense.module"

import SecureLS from "secure-ls";

const ls = new SecureLS({isCompression: false});

Vue.use(Vuex)

const store = new Vuex.Store({
    modules: {
        home,
        hr,
        circulars,
        special,
        application,
        fileMail,
        user,
        contacts,
        favorite,
        setting,
        cloud,
        logOperation,
        viewingUser,
        bizcard,
        browserSession,
        template,
        portal,
        notice,
        advertise,
        movie,
        customizeArea,
        groupware,
        templateRoute,
        workList,
        dailyReport,
        timeCardDetail,
        pageBreaks,
        formIssuance,
        expense,
        expenseSettlement,
    },
    //TODO: to encode local Storage
    plugins: [createPersistedState({
        storage: {
            getItem: key => ls.get(key),
            setItem: (key, value) => ls.set(key, value),
            removeItem: key => ls.remove(key)
        }
    })],
  //  plugins: [createPersistedState()],
    getters,
    mutations,
    state,
    actions,
    strict: process.env.NODE_ENV !== 'production'
});

store.dispatch("browserSession/init");

export default store;
