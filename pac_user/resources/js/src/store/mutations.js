

const mutations = {


  // /////////////////////////////////////////////
  // COMPONENTS
  // /////////////////////////////////////////////

  // Vertical NavMenu

  TOGGLE_IS_VERTICAL_NAV_MENU_ACTIVE(state, value) {
    state.isVerticalNavMenuActive = value
  },
  TOGGLE_REDUCE_BUTTON(state, val) {
    state.reduceButton = val
  },
  UPDATE_MAIN_LAYOUT_TYPE(state, val) {
    state.mainLayoutType = val
  },
  UPDATE_VERTICAL_NAV_MENU_ITEMS_MIN(state, val) {
    state.verticalNavMenuItemsMin = val
  },
  UPDATE_VERTICAL_NAV_MENU_WIDTH(state, width) {
    state.verticalNavMenuWidth = width
  },

  SET_ACTIVATE_STATE(state, name) {
    state[name] = true;
  },
  SET_DEACTIVATE_STATE(state, name) {
    state[name] = false;
  },


  // VxAutoSuggest

  UPDATE_STARRED_PAGE(state, payload) {

    // find item index in search list state
    const index = state.navbarSearchAndPinList.data.findIndex((item) => item.index == payload.index)

    // update the main list
    state.navbarSearchAndPinList.data[index].highlightAction = payload.val

    // if val is true add it to starred else remove
    if (payload.val) {
      state.starredPages.push(state.navbarSearchAndPinList.data[index])
    }
    else {
      // find item index from starred pages
      const index = state.starredPages.findIndex((item) => item.index == payload.index)

      // remove item using index
      state.starredPages.splice(index, 1)
    }
  },

  // Navbar-Vertical

  ARRANGE_STARRED_PAGES_LIMITED(state, list) {
    const starredPagesMore = state.starredPages.slice(10)
    state.starredPages     = list.concat(starredPagesMore)
  },
  ARRANGE_STARRED_PAGES_MORE(state, list) {
    let downToUp                 = false
    let lastItemInStarredLimited = state.starredPages[10]
    const starredPagesLimited    = state.starredPages.slice(0, 10)
    state.starredPages           = starredPagesLimited.concat(list)

    state.starredPages.slice(0, 10).map((i) => {
      if (list.indexOf(i) > -1) downToUp = true
    })

    if (!downToUp) {
      state.starredPages.splice(10, 0, lastItemInStarredLimited)
    }
  },


  // ////////////////////////////////////////////
  // UI
  // ////////////////////////////////////////////

  TOGGLE_CONTENT_OVERLAY(state, val) { state.bodyOverlay       = val },
  UPDATE_PRIMARY_COLOR(state, val)   { state.themePrimaryColor = val },
  UPDATE_THEME(state, val)           { state.theme             = val },
  UPDATE_WINDOW_WIDTH(state, width)  { state.windowWidth       = width },
  UPDATE_WINDOW_SCROLL_Y(state, val) { state.scrollY = val },


  // /////////////////////////////////////////////
  // User/Account
  // /////////////////////////////////////////////

  // Updates user info in state and localstorage
  UPDATE_USER_INFO(state, payload) {

    // Get Data localStorage
    let userInfo = JSON.parse(localStorage.getItem("userInfo")) || state.AppActiveUser

    for (const property of Object.keys(payload)) {

      if (payload[property] != null) {
        // If some of user property is null - user default property defined in state.AppActiveUser
        state.AppActiveUser[property] = payload[property]

        // Update key in localStorage
        userInfo[property] = payload[property]
      }


    }
    // Store data in localStorage
    localStorage.setItem("userInfo", JSON.stringify(userInfo))
  },
    UPDATE_LOADING(state, payload) {
      state.loading = payload;
    },
    ALERT_SUCCESS(state, payload) {
        state.notification.status = true;
        state.notification.type = "success";
        state.notification.message = payload;
    },
    ALERT_ERROR(state, payload) {
        state.notification.status = true;
        state.notification.type = "danger";
        state.notification.message = payload==='Internal Server Error'?'予期せぬエラーが発生しました':payload;
    },
    ALERT_CLEAR(state) {
        state.notification.status = false;
        state.notification.type = null;
        state.notification.message = null;
    }
}

export default mutations

