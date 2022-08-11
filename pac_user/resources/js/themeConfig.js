
const current_url = window.location.href;
const branding = JSON.parse(getLS('branding'));
let NAVBAR_BACKGROUND = branding && branding.background_color ? branding.background_color : "0984e3";
const NAVBAR_COLOR = branding && branding.color ? branding.color : "FFFFFF";
const NAVBAR_ICON = branding && branding.logo_file_data ? `data:image/png;base64,${branding.logo_file_data}` : '';

if(current_url.includes('site/approval') || current_url.includes('site/destination') || current_url.includes('site/sendback')) {
  NAVBAR_BACKGROUND = "0984e3";
}

// MAIN COLORS - VUESAX THEME COLORS
let colors = {
	primary : '#0984e3',
	success : '#28C76F',
	danger  : '#EA5455',
	warning : '#FF9F43',
	dark    : '#1E1E1E',
}

import Vue from 'vue'
import Vuesax from 'vuesax'
Vue.use(Vuesax, { theme:{ colors } })


// CONFIGS
const themeConfig = {
  disableCustomizer : false,       // options[Boolean] : true, false(default)
  disableThemeTour  : true,        // options[Boolean] : true, false(default)
  footerType        : "static",    // options[String]  : static(default) / sticky / hidden
  hideScrollToTop   : false,       // options[Boolean] : true, false(default)
  mainLayoutType    : "vertical",  // options[String]  : vertical(default) / horizontal
  navbarColor       : `#${NAVBAR_BACKGROUND}`,      // options[String]  : HEX color / rgb / rgba / Valid HTML Color name - (default: #fff)
  navbarFontColor   : `#${NAVBAR_COLOR}`,      // options[String]  : HEX color / rgb / rgba / Valid HTML Color name - (default: #fff)
  navbarIcon        : `${NAVBAR_ICON}`,      // options[String]  : Branding icon base64 string - (default: )
  navbarType        : "sticky",  // options[String]  : floating(default) / static / sticky / hidden
  routerTransition  : "zoom-fade", // options[String]  : zoom-fade / slide-fade / fade-bottom / fade / zoom-out / none(default)
  sidebarCollapsed  : true,       // options[Boolean] : true, false(default)
  theme             : "light",     // options[String]  : "light"(default), "dark", "semi-dark"

  // Not required yet - WIP
  userInfoLocalStorageKey: "userInfo",

}

export default themeConfig
