import Vue from 'vue';
import App from './App';
import { MonthPicker } from 'vue-month-picker'
import { MonthPickerInput } from 'vue-month-picker'

Vue.use(MonthPicker)
Vue.use(MonthPickerInput)
/**
 * Mount the Vue instance on the DOM element "#wp-vue-app" in the Custom Page Template
 */

new Vue({
	el: '#wp-vue-app',
	render: h => h( App )
});
