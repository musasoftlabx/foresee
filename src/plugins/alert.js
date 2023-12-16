import Vue from "vue";
import Alert from "@/components/Dialogs/Alert";
Vue.component("alert", Alert);

/* export default {
  install(Vue) {
    
    Vue.mixin({
      data() {
        return {
          alert: {
            visible: false,
            icon: "mdi-check-decagram",
            title: null,
            color: undefined,
            content: "All fields need to be filled!",
          },
        };
      },
    });

    this.$root = (() => {
      let DialogConstructor = Vue.extend(Alert);
      let node = document.createElement("div");
      document.querySelector("body").appendChild(node);

      let Vm = new DialogConstructor();

      Vm.registeredViews = this.registeredComponents();

      return Vm.$mount(node);
    })();

    Vue.prototype.$Alertify = (props) => {
      this.alert = {
        ...this.alert,
        ...props,
      };
    };
  },
};
 */
