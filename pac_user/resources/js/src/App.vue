

<template>
	<div id="app">
		<router-view></router-view>
	</div>
</template>

<script>
import themeConfig from '@/../themeConfig.js';
import config from "./app.config";

export default {
  watch: {
    '$store.state.theme'(val) {
      this.toggleClassInBody(val)
    },

  },
  methods: {
    toggleClassInBody(className) {
      if (className == 'dark') {
        if (document.body.className.match('theme-semi-dark')) document.body.classList.remove('theme-semi-dark')
        document.body.classList.add('theme-dark')
      }
      else if (className == 'semi-dark') {
        if (document.body.className.match('theme-dark')) document.body.classList.remove('theme-dark')
        document.body.classList.add('theme-semi-dark')
      }
      else {
        if (document.body.className.match('theme-dark'))      document.body.classList.remove('theme-dark')
        if (document.body.className.match('theme-semi-dark')) document.body.classList.remove('theme-semi-dark')
      }
    },
    handleWindowResize() {
      this.$store.commit('UPDATE_WINDOW_WIDTH', window.innerWidth)
    },
    handleScroll() {
      this.$store.commit('UPDATE_WINDOW_SCROLL_Y', window.scrollY)
    }
  },
  mounted() {
    this.toggleClassInBody(themeConfig.theme)
    this.$store.commit('UPDATE_WINDOW_WIDTH', window.innerWidth)
  },
  async created() {
    window.addEventListener('resize', this.handleWindowResize);
    window.addEventListener('scroll', this.handleScroll);

    this.$ls.on('logout', (value)=> {
	  const return_url = localStorage.getItem('return_url');
	  window.location.href = return_url;
	});
    this.$ls.on('login', (value)=> {
        if (this.$root && this.$root.$el && this.$root.$el.parentNode){
			if (resultLogin && resultLogin.user && resultLogin.user.id != value){
                this.$root.$el.parentNode.removeChild(this.$root.$el);
                window.location.href = localStorage.getItem('return_url');
			}
		}
	});
  },
  destroyed() {
    window.removeEventListener('resize', this.handleWindowResize)
    window.removeEventListener('scroll', this.handleScroll)
  },
}

</script>
