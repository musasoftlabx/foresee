export default {
  data() {
    return {
      LocalStorage: localStorage,
      textpass: false,
      validators: {
        required: (value) => !!value || "Required.",
        password: (value) =>
          (!!value && value.length >= 8) || "Less characters.",
        confirm: (value) =>
          value === this.password.ConfirmPassword.value || "Not matching.",
        phone: (value) => (!!value && value.length === 10) || "Invalid number.",
        email: (value) => {
          const pattern =
            /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
          return pattern.test(value) || "Invalid e-mail.";
        },
        AlphanumericsRegex: (value) => {
          const pattern = /^[a-zA-Z0-9@.]+$/;
          return pattern.test(value) || "Invalid characters.";
        },
        AlphabetsRegex: (value) => {
          const pattern = /^[a-zA-Z]+$/;
          return pattern.test(value) || "Only alphabets allowed";
        },
        NumericsRegex: (value) => {
          const pattern = /^[+0-9]/;
          return pattern.test(value) || "Only numbers allowed";
        },
      },
      drawer: {
        visible: true,
        miniVariant: false, //this.$vuetify.breakpoint.mdAndDown && localStorage.miniVariant ? true : false,
        loading: false,
        parents: [
          {
            icon: "mdi-view-dashboard-outline",
            children: false,
            text: "Home",
            route: "",
            access: ["Super Admin", "Admin"],
          },
          {
            icon: "mdi-domain",
            children: false,
            text: "Clients",
            route: "Clients",
            access: ["Super Admin", "Admin"],
          },
          {
            icon: "mdi-store",
            children: false,
            text: "Stores",
            route: "Stores",
            access: ["Super Admin", "Admin"],
          },
          {
            icon: "mdi-tag-multiple",
            children: false,
            text: "Products",
            route: "Products",
            access: ["Super Admin", "Admin"],
          },
          {
            icon: "mdi-account-circle",
            children: false,
            text: "User Management",
            route: "UserManagement",
            access: ["Super Admin"],
          },
          {
            icon: "mdi-account-group",
            children: false,
            text: "Scan Operators",
            route: "ScanOperators",
            access: ["Super Admin"],
          },
          {
            icon: "mdi-barcode",
            children: false,
            text: "Master Reset Barcode",
            route: "MasterResetBarcode",
            access: ["Super Admin", "Admin"],
          },
        ],
      },
    };
  },
  methods: {
    promiseFetch(msec) {
      return (promise) => {
        const timeout = new Promise((yea, nah) =>
          setTimeout(() => nah(new Error("Timeout expired")), msec)
        );
        return Promise.race([promise, timeout]);
      };
    },
    TextPass() {
      this.textpass = !this.textpass;
    },
    Logout() {
      localStorage.removeItem("_4c_token_");
      this.$store.dispatch("StoreToken", null);
      this.$router.push("/Login");
    },
    FormatToDecimal(n) {
      return n.toFixed(2);
    },
    FormatWithCommas(n) {
      return n.toLocaleString();
    },
  },
  computed: {
    ComputeFirstName() {
      if (this.$store.getters.token) {
        return JSON.parse(atob(this.$store.getters.token.split(".")[1])).data
          .FirstName;
      } else {
        return "Admin";
      }
    },
    ComputeUsername() {
      if (this.$store.getters.token) {
        return JSON.parse(atob(this.$store.getters.token.split(".")[1])).data
          .username;
      }
    },
    ComputeRole() {
      if (this.$store.getters.token) {
        return JSON.parse(atob(this.$store.getters.token.split(".")[1])).data
          .role;
      }
    },
    ComputeSignedIn() {
      if (this.$store.getters.token) {
        return (
          "Signed in as " +
          JSON.parse(atob(this.$store.getters.token.split(".")[1])).data
            .username
        );
      } else {
        return "You are not signed in";
      }
    },
    ComputeEmailAddress() {
      if (this.$store.getters.token) {
        return JSON.parse(atob(this.$store.getters.token.split(".")[1])).data
          .EmailAddress;
      }
    },
    ComputeAccessMatrix() {
      return this.drawer.parents.filter((parent) =>
        parent.access.includes(this.ComputeRole)
      );
    },
  },
};
