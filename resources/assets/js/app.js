/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

import Vue from "vue";

Vue.component('example', require('./components/Example.vue'));

const app = new Vue({
    el: '#app'
});
