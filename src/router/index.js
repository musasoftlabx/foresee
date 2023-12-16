import Vue from "vue";
import Router from "vue-router";
import store from "@/store/";

Vue.use(Router);

let router = new Router({
  mode: "history",
  base: process.env.BASE_URL,
  routes: [
    {
      name: "Home",
      path: "/",
      component: () => import("@/views/Home.vue"),
    },
    {
      name: "Login",
      path: "/Login",
      component: () => import("@/views/Login.vue"),
    },
    {
      name: "Stores",
      path: "/Stores",
      component: () => import("@/views/Stores.vue"),
    },
    {
      path: "/Stores/:id",
      component: () => import("@/views/Store.vue"),
      children: [
        {
          name: "Store",
          path: "",
          component: () => import("@/views/Locations.vue"),
        },
        {
          path: "PrintLocations",
          component: () => import("@/views/PrintLocations.vue"),
        },
        {
          path: "Scans",
          component: () => import("@/views/Scans.vue"),
        },
      ],
    },
    {
      name: "Users",
      path: "/Users",
      component: () => import("@/views/Users.vue"),
    },
    {
      name: "Products",
      path: "/Products",
      component: () => import("@/views/Products.vue"),
    },
    {
      name: "User Management",
      path: "/UserManagement",
      component: () => import("@/views/UserManagement.vue"),
    },
    {
      name: "Scan Operators",
      path: "/ScanOperators",
      component: () => import("@/views/ScanOperators.vue"),
    },
    {
      name: "MasterResetBarcode",
      path: "/MasterResetBarcode",
      component: () => import("@/views/MasterResetBarcode.vue"),
    },
  ],
});

router.beforeEach((to, from, next) => {
  if (to.path !== "/Login") {
    if (localStorage._4c_token_) {
      fetch(store.getters.endpoint + "Tokenify/", {
        method: "post",
        body: localStorage._4c_token_,
      }).then((response) => {
        response.text().then((res) => {
          if (response.status == 401) {
            router.push("/Login");
            localStorage.removeItem("_4c_token_");
          } else {
            localStorage._4c_token_ = res;
            localStorage.lastRoute = to.path;
            clearInterval(window.tokenify);
            window.tokenify = setInterval(() => {
              fetch(store.getters.endpoint + "Tokenceptor/", {
                method: "post",
                body: localStorage._4c_token_,
              }).then((response) => {
                response.text().then(() => {
                  if (response.status == 401) {
                    clearInterval(window.tokenify);
                    router.push("/Login");
                  }
                });
              });
            }, 10000);
            next();
          }
        });
      });
    } else {
      next("/Login");
    }
  } else {
    next();
  }
});

export default router;
