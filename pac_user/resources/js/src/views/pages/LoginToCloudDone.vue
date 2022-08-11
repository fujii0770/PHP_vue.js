<template>
    <h1>{{message}}</h1>
</template>
<script>
    import config from "../../app.config";
    import Axios from "axios";
    export default {
        data() {
            return {
                token: null,
                message: '',
            }
        },
        methods: {
          getToken() {
            console.log(this.$route);
            const state = this.$route.query.state;
            const code = this.$route.query.code;
            this.message = 'Waiting...';
            Axios.get(`${config.LOCAL_API_URL}/externalCallback?state=${state}&code=${code}`, {data: {nowait: true}})
              .then(response => {
                if(response && response.data && response.data.data.token) {
                  this.$ls.set(`${response.data.data.drive}AccessToken`, true);
                  this.message = 'Done!';
                }else {
                  this.message = 'Error!';
                }
                setTimeout(()=> {
                  window.close();
                },500);
              })
              .catch(error => {
                //this.$router.push('/pages/error-404');
                this.message = 'Error';
                setTimeout(()=> {
                  window.close();
                },500);
              });
          }
        },
        created() {
            this.getToken();
        }
    }
</script>