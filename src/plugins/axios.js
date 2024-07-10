import Vue from 'vue'
import axios from 'axios'
import VueAxios from 'vue-axios'

const instance = axios.create({
    //baseURL: 'http://sh67.school/',
    //withCredentials: true,
    headers: {
        //'X-Requested-With': "XMLHttpRequest",
        //'Accept': "application/json, text/javascript, */*; q=0.01",
        //'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
        //"Content-Type": "application/json"
        //'Access-Control-Allow-Origin': "*",
        //"Access-Control-Allow-Methods": "GET, POST, PATCH, PUT, DELETE, OPTIONS",
        //"Access-Control-Allow-Headers": "origin, x-requested-with, content-type"
    }
});

/*instance.interceptors.response.use(function (response) {
    // Any status code that lie within the range of 2xx cause this function to trigger
    // Do something with response data
    return response.data;
}, function (error) {
    if (error.response.status == 403) {
        alert(error.response.statusText);
    }
    return Promise.reject(error);
});//*/

Vue.use(VueAxios, instance)

export default instance;