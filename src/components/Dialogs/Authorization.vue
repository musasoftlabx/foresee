<template>
  <section>
    <v-dialog
      v-model="login.visible"
      max-width="350"
      transition="fab-transition"
    >
      <v-card flat tile>
        <v-toolbar
          height="35"
          dark
          :src="
            $store.getters.server + 'img/backdrops/' + $store.getters.background
          "
        >
          <template v-slot:img="{ props }">
            <v-img
              v-bind="props"
              gradient="to top right, rgba(57,181,74,.9), rgba(25,32,72,.6)"
            />
          </template>
          <v-icon>mdi-account-circle</v-icon>
          <span class="body-2 ml-3">Login</span>
          <v-spacer />
          <v-icon size="18" class="ml-5" @click="login.visible = false"
            >mdi-close</v-icon
          >
        </v-toolbar>
        <div
          class="pt-2 px-5 pb-0 CustomFormModifier"
          style="max-height: 75vh; overflow-y: auto; overflow-x: hidden"
        >
          <v-row>
            <v-col class="text-center">
              <img
                width="80"
                :src="this.$store.getters.server + 'img/boat.png'"
                class="mr-2 mt-2"
              />
              <div class="headline">Login</div>
              <div class="mb-2 caption">
                Please enter your email and password
              </div>
            </v-col>
          </v-row>
          <v-form ref="Login" v-model="login.valid" class="px-5">
            <v-text-field
              ref="LoginUsername"
              v-model="login.username"
              :rules="[validators.required]"
              name="username"
              label="Username *"
              outlined
              dense
              clearable
              autofocus
              prepend-inner-icon="mdi-account-circle"
              @keyup.enter="Login"
            />
            <v-text-field
              v-model="login.password"
              name="password"
              label="Password *"
              outlined
              dense
              clearable
              counter
              prepend-inner-icon="mdi-shield-key"
              :append-icon="textpass ? 'mdi-eye' : 'mdi-eye-off'"
              :type="textpass ? 'text' : 'password'"
              :rules="[validators.required]"
              @click:append="TextPass"
              @keyup.enter="Login"
            />
            <v-btn-toggle>
              <v-btn
                color="primary"
                style="color: white !important"
                width="100"
                height="35"
                small
                :disabled="!login.valid || login.loading"
                :loading="login.loading"
                @click="Login"
                >LOGIN</v-btn
              >
              <v-btn
                color="primary"
                width="140"
                height="35"
                outlined
                @click="GoToSignUp"
                >GO TO SIGN UP</v-btn
              >
            </v-btn-toggle>
            <div
              class="caption font-weight-bold mt-3 underline"
              @click="$refs.Reset.visible = true"
            >
              Forgot username or password?
            </div>
          </v-form>
          <img width="100%" :src="$store.getters.server + 'img/_logo.png'" />
        </div>
      </v-card>
    </v-dialog>

    <v-dialog
      v-model="signup.visible"
      max-width="600"
      transition="dialog-bottom-transition"
    >
      <v-card flat tile>
        <v-toolbar
          height="35"
          dark
          :src="
            $store.getters.server + 'img/backdrops/' + $store.getters.background
          "
        >
          <template v-slot:img="{ props }">
            <v-img
              v-bind="props"
              gradient="to top right, rgba(57,181,74,.9), rgba(25,32,72,.6)"
            />
          </template>
          <v-icon>mdi-account-circle</v-icon>
          <span class="body-2 ml-3">Sign Up</span>
          <v-spacer />
          <v-icon size="18" class="ml-5" @click="signup.visible = false"
            >mdi-close</v-icon
          >
        </v-toolbar>
        <div
          class="pt-2 px-5 pb-0 CustomFormModifier"
          style="max-height: 75vh; overflow-y: auto; overflow-x: hidden"
        >
          <v-row>
            <v-col class="text-center">
              <img
                width="80"
                :src="this.$store.getters.server + 'img/boat.png'"
                class="mr-2 mt-2"
              />
              <div class="headline">Sign Up</div>
              <div class="mb-4 caption">Enter details to sign up</div>
            </v-col>
          </v-row>
          <v-form
            ref="SignUp"
            v-model="signup.valid"
            class="CustomFormModifier px-5"
          >
            <v-row>
              <v-col cols="12" lg="6" class="px-2">
                <v-text-field
                  v-model.trim="signup.FirstName"
                  :rules="[validators.required]"
                  label="First Name *"
                  outlined
                  dense
                  name="FirstName"
                  prepend-inner-icon="mdi-account-circle"
                />
              </v-col>
              <v-col cols="12" lg="6" class="px-2">
                <v-text-field
                  v-model.trim="signup.LastName"
                  :rules="[validators.required]"
                  label="Last Name *"
                  outlined
                  dense
                  name="LastName"
                  prepend-inner-icon="mdi-account-circle"
                />
              </v-col>
              <v-col cols="12" lg="6" class="px-2">
                <v-text-field
                  v-model.trim="signup.EmailAddress"
                  :rules="[validators.required, validators.email]"
                  label="Email Address *"
                  outlined
                  dense
                  name="EmailAddress"
                  prepend-inner-icon="mdi-email-variant"
                />
              </v-col>
              <v-col cols="12" lg="6" class="px-2">
                <v-text-field
                  v-model.trim="signup.username.value"
                  :rules="[validators.required]"
                  :error="signup.username.error"
                  :error-messages="signup.username.ErrorMessage"
                  :loading="signup.username.loading"
                  label="Username *"
                  outlined
                  dense
                  name="Username"
                  prepend-inner-icon="mdi-account-circle"
                  @blur="CheckUsername"
                />
              </v-col>
              <v-col cols="12" lg="7" class="px-2">
                <vue-tel-input-vuetify
                  v-model="signup.PhoneNumber"
                  :defaultCountry="'KE'"
                  :preferredCountries="['KE', 'UG', 'TZ']"
                  :mode="'international'"
                  :inputOptions="{ showDialCode: true }"
                  selectLabel="Country *"
                  label="Enter Phone Number *"
                  outlined
                  dense
                  @country-changed="GetCountry"
                />
              </v-col>
              <v-col cols="12" lg="5" class="px-2">
                <v-select
                  v-model.trim="signup.AccountType"
                  :rules="[validators.required]"
                  :items="['Company', 'Individual']"
                  label="Account Type *"
                  outlined
                  dense
                  prepend-inner-icon="mdi-account-box"
                />
              </v-col>
              <v-col
                v-if="signup.AccountType === 'Company'"
                cols="12"
                class="px-2"
              >
                <v-text-field
                  v-model.trim="signup.CompanyName"
                  :rules="[validators.required]"
                  label="Company Name *"
                  outlined
                  dense
                  name="CompanyName"
                  prepend-inner-icon="mdi-domain"
                />
              </v-col>
              <TermsAndConditions ref="TermsAndConditions" />
              <v-col cols="12" class="px-2 pt-1">
                <v-btn-toggle>
                  <v-btn
                    color="primary"
                    style="color: white !important"
                    width="100"
                    height="35"
                    :loading="signup.loading"
                    :disabled="!signup.valid || signup.loading"
                    @click="SignUp"
                    >SIGN UP</v-btn
                  >
                  <v-btn
                    ref="GoToLogin"
                    color="primary"
                    width="120"
                    height="35"
                    small
                    outlined
                    @click="GoToLogin"
                    >GO TO LOGIN</v-btn
                  >
                </v-btn-toggle>
              </v-col>
            </v-row>
          </v-form>
          <img
            class="mt-n3 ml-4"
            width="95%"
            :src="$store.getters.server + 'img/_logo.png'"
          />
        </div>
      </v-card>
    </v-dialog>
    <Alert ref="Alert" />
    <Reset ref="Reset" />
  </section>
</template>

<script>
import TermsAndConditions from "@/components/Dialogs/TermsAndConditions";
import Reset from "@/components/Dialogs/Reset";
export default {
  components: {
    TermsAndConditions,
    Reset,
  },
  data() {
    return {
      login: {
        visible: false,
        valid: false,
        loading: false,
        username: null,
        password: null,
        phone: {
          visible: true,
          value: "",
        },
        OTP: {
          visible: false,
          value: "",
        },
      },
      signup: {
        visible: false,
        valid: false,
        loading: false,
        FirstName: null,
        LastName: null,
        EmailAddress: null,
        username: {
          value: null,
          error: false,
          ErrorMessage: null,
          loading: false,
        },
        PhoneNumber: null,
        country: null,
        AccountType: null,
        CompanyName: null,
        password: null,
        phone: {
          visible: true,
          value: "",
        },
        OTP: {
          visible: false,
          value: "",
        },
      },
    };
  },
  methods: {
    GoToSignUp() {
      this.$refs.Login.reset();
      this.login.visible = false;
      this.signup.visible = true;
    },
    GoToLogin() {
      this.signup.visible = false;
      this.login.visible = true;
    },
    Login() {
      this.loading = true;
      this.promiseFetch(this.$store.getters.fetchTimeout)(
        fetch(this.$store.getters.endpoint + "Login/", {
          method: "post",
          body: JSON.stringify({
            username: this.login.username,
            password: this.login.password,
          }),
        })
      )
        .then((response) => {
          response.text().then((res) => {
            if (response.status > 200) {
              this.$refs.Alert.Alertify({
                visible: true,
                content: res,
                title: "Invalid Credentials",
                icon: "mdi-progress-alert",
                color: "error",
              });
            } else {
              /* localStorage.daraja = res;
              this.$refs.Login.hideComponent();
                this.$refs.Appbar.showComponent() */
              localStorage.setItem("daraja", res);

              this.$store.dispatch("StoreToken", res);

              if (this.$route.fullPath === "/") {
                this.$router.push("/MyApps");
              } else {
                this.login.visible = false;
                this.$router.push("Reloader");
                this.$router.back();
              }
            }
          });
        })
        .catch(() => {
          this.$refs.Alert.Alertify({
            visible: true,
            content: this.$store.getters.fetchTimeoutError,
            title: "Connection Timeout",
            icon: "mdi-wifi-strength-1-alert",
            color: "error",
          });
        })
        .finally(() => {
          this.loading = false;
        });
    },
    GetCountry(country) {
      this.signup.country = country.name;
    },
    CheckUsername() {
      let username = this.signup.username.value;
      this.signup.username.loading = true;
      this.promiseFetch(this.$store.getters.fetchTimeout)(
        fetch(this.$store.getters.endpoint + "CheckUsername/", {
          method: "post",
          body: JSON.stringify({
            username,
          }),
        })
      ).then((response) => {
        response
          .text()
          .then((res) => {
            if (response.status > 200) {
              this.$refs.Alert.Alertify({
                visible: true,
                content:
                  "Username <b>" +
                  username +
                  "</b> you entered is already taken. Kindly try another.",
                title: "Username taken!",
                icon: "mdi-account-card-details",
                color: "error",
              });
              this.signup.username.ErrorMessage = username + " is taken";
              this.signup.username.error = true;
            } else {
              if (JSON.parse(res) == false) {
                this.signup.username.ErrorMessage = username + " is taken";
                this.signup.username.error = true;
              } else {
                this.signup.username.ErrorMessage = null;
                this.signup.username.error = false;
              }
            }
          })
          .finally(() => {
            this.signup.username.loading = false;
          });
      });
    },
    SignUp() {
      this.signup.loading = true;
      this.promiseFetch(this.$store.getters.fetchTimeout)(
        fetch(this.$store.getters.endpoint + "SignUp/", {
          method: "post",
          body: JSON.stringify({
            FirstName: this.signup.FirstName,
            LastName: this.signup.LastName,
            EmailAddress: this.signup.EmailAddress,
            username: this.signup.username.value,
            PhoneNumber: this.signup.PhoneNumber,
            country: this.signup.country,
            AccountType: this.signup.AccountType,
            CompanyName: this.signup.CompanyName || null,
          }),
        })
      )
        .then((response) => {
          response.text().then((res) => {
            if (response.status > 300) {
              this.$refs.Alert.Alertify({
                visible: true,
                content: res,
                title: "Already exists",
                icon: "mdi-progress-alert",
                color: "error",
              });
            } else if (response.status === 201) {
              this.$refs.Alert.Alertify({
                visible: true,
                content: res,
                title: "Success",
                icon: "mdi-progress-alert",
                color: "error",
              });
            } else {
              this.$refs.SignUp.reset();
              this.$refs.GoToLogin.$el.click();
            }
          });
        })
        .catch(() => {
          this.$refs.Alert.Alertify({
            visible: true,
            content: this.$store.getters.fetchTimeoutError,
            title: "Connection Timeout",
            icon: "mdi-wifi-strength-1-alert",
            color: "error",
          });
        })
        .finally(() => {
          this.signup.loading = false;
        });
    },
    Validate() {
      this.loading.validate = true;
      this.promiseFetch(this.$store.getters.fetchTimeout)(
        fetch(this.$store.getters.endpoint + "OTP/Validate/", {
          method: "post",
          body: JSON.stringify({
            OTP: this.OTP.value,
            phone: this.phone.value,
          }),
        })
      )
        .then((response) => {
          response.text().then((res) => {
            if (response.status > 200) {
              this.$refs.Alert.Alertify({
                visible: true,
                content: res,
                title: "Invalid OTP",
                icon: "mdi-progress-alert",
                color: "error",
              });
            } else {
              this.$router.push("/Applications");
            }
          });
        })
        .catch(() => {
          this.$refs.Alert.Alertify({
            visible: true,
            content: this.$store.getters.fetchTimeoutError,
            title: "Connection Timeout",
            icon: "mdi-wifi-strength-1-alert",
            color: "error",
          });
        })
        .finally(() => {
          this.loading.validate = false;
        });
    },
    Generate() {
      this.loading.generate = true;
      this.promiseFetch(this.$store.getters.fetchTimeout)(
        fetch(this.$store.getters.endpoint + "OTP/Generate/", {
          method: "post",
          body: JSON.stringify({
            phone: this.phone.value,
          }),
        })
      )
        .then((response) => {
          response.text().then((res) => {
            if (response.status > 200) {
              this.$refs.Alert.Alertify({
                visible: true,
                content: res,
                title: "Generate Error",
                icon: "mdi-alert",
                color: "error",
              });
            } else {
              this.OTP.value = "";
            }
          });
        })
        .catch(() => {
          this.$refs.Alert.Alertify({
            visible: true,
            content: this.$store.getters.fetchTimeoutError,
            title: "Connection Timeout",
            icon: "mdi-wifi-strength-1-alert",
            color: "error",
          });
        })
        .finally(() => {
          this.loading.generate = false;
        });
    },
  },
};
</script>

<style>
.underline {
  text-decoration: underline;
  cursor: pointer;
}
.country-code {
  margin-right: 10px !important;
}
</style>
