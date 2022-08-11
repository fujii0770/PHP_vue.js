
import navbarSearchAndPinList from "@/layouts/components/navbar/navbarSearchAndPinList"
import themeConfig from "@/../themeConfig.js"
import colors from "@/../themeConfig.js"


// /////////////////////////////////////////////
// State
// /////////////////////////////////////////////

const state = {
    //AppActiveUser           : userDefaults,
    bodyOverlay             : false,
    isVerticalNavMenuActive : true,
    mainLayoutType          : themeConfig.mainLayoutType || "vertical",
    navbarSearchAndPinList  : navbarSearchAndPinList,
    reduceButton            : themeConfig.sidebarCollapsed,
    verticalNavMenuWidth    : "default",
    verticalNavMenuItemsMin : false,
    scrollY                 : 0,
    starredPages            : navbarSearchAndPinList.data.filter((page) => page.highlightAction),
    theme                   : themeConfig.theme || "light",
    themePrimaryColor       : colors.primary,

    windowWidth: null,
    loading: false,
    notification: {
        status: false,
        message: null,
        type: null,
    },
    showModalContacts:false
}

export default state
