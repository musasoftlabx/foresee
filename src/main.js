import Vue from "vue";
import App from "./App.vue";
import "./registerServiceWorker";
import router from "./router";
import store from "./store";
import vuetify from "./plugins/vuetify";
import "roboto-fontface/css/roboto/roboto-fontface.css";
import "@mdi/font/css/materialdesignicons.css";
import "@babel/polyfill";
import Mixins from "@/mixins/";
import Alert from "@/components/Dialogs/Alert";
import Confirm from "@/components/Dialogs/Confirm";
import vuescroll from "vuescroll";
import VueTheMask from "vue-the-mask";
import VueProgressBar from "vue-progressbar";
import VuePageTransition from "vue-page-transition";
import VueNumberAnimation from "vue-number-animation";
import TelephoneInput from "vue-tel-input-vuetify/lib";
import VueHtmlToPaper from "vue-html-to-paper";
import { VueMasonryPlugin } from "vue-masonry";

Vue.mixin(Mixins);
Vue.component("Alert", Alert);
Vue.component("Confirm", Confirm);
Vue.use(vuescroll);
Vue.use(VueTheMask);
Vue.use(VueProgressBar, {
  color: "#b33bf5",
  failedColor: "red",
  height: "20px",
});
Vue.use(VuePageTransition);
Vue.use(TelephoneInput, { vuetify });
Vue.use(VueNumberAnimation);
Vue.use(VueHtmlToPaper, {
  name: "_blank",
  specs: ["fullscreen=no", "titlebar=yes", "scrollbars=yes"],
  autoClose: true, // if false, the window will not close after printing
  windowTitle: window.document.title, // override the window title
  styles: [
    //"https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css",
    "https://foresee-technologies.com/api/libraries/index.css",
    //"@/assets/css/index.css",
  ],
});
Vue.use(VueMasonryPlugin);

Vue.config.productionTip = false;

new Vue({
  router,
  store,
  vuetify,
  render: (h) => h(App),
}).$mount("#app");
