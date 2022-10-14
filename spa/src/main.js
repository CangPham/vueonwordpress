import Vue from 'vue';
import App from './App';

/**
 * Mount the Vue instance on the DOM element "#wp-vue-app" in the Custom Page Template
 */

new Vue({
	el: '#wp-vue-app',
	render: h => h( App )
});
