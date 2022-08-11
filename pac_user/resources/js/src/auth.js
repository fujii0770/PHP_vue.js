/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
import Axios from "axios";

class Auth {
    constructor() {
        this.token = null;
        this.user = null;
        this.expires_time = null;
    }
    
    login (token, user, expires_time) {
        window.sessionStorage.setItem('token', token);
        storeLS('user', JSON.stringify(user));
        window.localStorage.setItem('expires_time', expires_time );

        Axios.defaults.headers.common['Authorization'] = 'Bearer ' + token;      
        this.token = token;
        this.user = user;
        this.expires_time = expires_time;
        
      //  Event.$emit('userLoggedIn');
    }

    logout(){
        window.sessionStorage.removeItem('token');
        window.localStorage.removeItem('user');
        window.localStorage.removeItem('expires_time');
        
        Axios.defaults.headers.common['Authorization'] = '';
        
        this.token = null;
        this.user = null;
        this.expires_time = null;
        
      //  Event.$emit('userLoggedOut');
    }
    
    check () {
        return !! this.token;
    }
    
    
}

export default new Auth();