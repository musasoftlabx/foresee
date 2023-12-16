<template>
  <section>
    <v-dialog
      v-model="visible"
      max-width="400"
      persistent
      transition="dialog-bottom-transition"
    >
      <v-card flat tile>
        <v-toolbar
          height="35"
          dark
          color="primary"
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
          <v-icon size="20">mdi-lock-reset</v-icon>
          <span class="body-2 ml-2">Forgot password?</span>
          <v-spacer />
          <v-icon size="18" class="ml-5" @click="Close">mdi-close</v-icon>
        </v-toolbar>
        <v-stepper v-model="step" vertical>
          <v-stepper-step :complete="step > 1" step="1">
            Enter Email Address
            <small class="mt-2 font-weight-bold"
              >A One Time PIN, (OTP) will be sent to your phone</small
            >
          </v-stepper-step>
          <v-stepper-content step="1">
            <v-card flat class="pt-1">
              <v-form
                ref="EmailAddress"
                v-model="EmailAddress.valid"
                @submit.prevent
                class="CustomFormModifier"
              >
                <v-text-field
                  v-model="EmailAddress.value"
                  :rules="[validators.required]"
                  name="EmailAddress"
                  outlined
                  dense
                  hide-details
                  label="Email Address *"
                  prepend-inner-icon="mdi-account-circle"
                  class="mr-7"
                  @keyup.enter="Forget"
                >
                  <template v-slot:append>
                    <v-btn
                      :disabled="!EmailAddress.valid || EmailAddress.loading"
                      :loading="EmailAddress.loading"
                      color="primary"
                      style="margin-top: -3px"
                      small
                      @click="Forget"
                      >SEND OTP</v-btn
                    >
                  </template>
                </v-text-field>
              </v-form>
            </v-card>
          </v-stepper-content>
          <v-stepper-step :complete="step > 2" step="2">
            Enter One Time PIN received
            <small class="mt-2 font-weight-bold"
              >If not received, try to resend</small
            >
          </v-stepper-step>
          <v-stepper-content step="2">
            <v-card flat class="CustomFormModifier pt-1">
              <v-form ref="OTP" v-model="OTP.valid" @submit.prevent>
                <v-text-field
                  v-model="OTP.value"
                  v-mask="'####'"
                  :rules="[validators.required, validators.OTP]"
                  outlined
                  dense
                  label="OTP *"
                  prepend-inner-icon="mdi-powershell"
                  class="mr-7"
                  @keyup.enter="Validate"
                >
                  <template v-slot:append>
                    <v-btn
                      :disabled="OTP.loading.resend"
                      :loading="OTP.loading.resend"
                      color="primary"
                      class="mr-2"
                      style="margin-top: -2px"
                      small
                      outlined
                      @click="Generate"
                      >RESEND</v-btn
                    >
                    <v-btn
                      :disabled="OTP.value.length < 4 || OTP.loading.validate"
                      :loading="OTP.loading.validate"
                      color="primary"
                      style="margin-top: -2px"
                      small
                      @click="Validate"
                      >VALIDATE</v-btn
                    >
                  </template>
                </v-text-field>
              </v-form>
            </v-card>
          </v-stepper-content>
          <v-stepper-step :complete="step > 3" step="3">
            New password
            <small class="mt-2 font-weight-bold"
              >Enter your new password &amp; confirm</small
            >
          </v-stepper-step>
          <v-stepper-content step="3">
            <v-card flat class="CustomFormModifier pt-1">
              <v-form ref="Password" v-model="password.valid" @submit.prevent>
                <v-text-field
                  v-model="password.value"
                  label="Password"
                  prepend-inner-icon="mdi-shield-key"
                  :append-icon="password.type ? 'mdi-eye' : 'mdi-eye-off'"
                  :type="password.type ? 'text' : 'password'"
                  :rules="[validators.required, validators.password]"
                  @click:append="password.type = !password.type"
                  @keyup.enter="Reset"
                  counter
                  outlined
                  dense
                  class="mr-7"
                />
                <v-text-field
                  v-model="confirm.value"
                  label="Confirm"
                  prepend-inner-icon="mdi-shield-key-outline"
                  :append-icon="confirm.type ? 'mdi-eye' : 'mdi-eye-off'"
                  :type="confirm.type ? 'text' : 'password'"
                  :rules="[validators.required, validators.password]"
                  @click:append="confirm.type = !confirm.type"
                  @keyup.enter="Reset"
                  counter
                  outlined
                  dense
                  class="mr-7"
                />
                <v-row no-gutters class="mt-2 mr-7">
                  <v-btn
                    :disabled="
                      !password.valid ||
                      password.loading ||
                      password.value != confirm.value
                    "
                    :loading="password.loading"
                    block
                    color="primary"
                    @click="Reset"
                    >RESET</v-btn
                  >
                </v-row>
              </v-form>
            </v-card>
          </v-stepper-content>
        </v-stepper>
      </v-card>
    </v-dialog>
    <Alert ref="Alert" />
  </section>
</template>

<script>
export default {
  data() {
    return {
      step: 1,
      visible: false,
      EmailAddress: {
        valid: true,
        value: null,
        loading: false,
      },
      OTP: {
        valid: false,
        value: "",
        loading: {
          generate: false,
          validate: false,
        },
      },
      password: {
        valid: false,
        type: false,
        value: null,
        loading: false,
      },
      confirm: {
        type: false,
        value: null,
      },
    };
  },
  methods: {
    Forget() {
      this.EmailAddress.loading = true;
      this.promiseFetch(this.$store.getters.fetchTimeout)(
        fetch(this.$store.getters.endpoint + "OTP/Generate/", {
          method: "post",
          body: JSON.stringify({
            EmailAddress: this.EmailAddress.value,
            local: 1,
          }),
        })
      )
        .then((response) => {
          response.text().then((res) => {
            if (response.status > 200) {
              this.$refs.Alert.Alertify({
                visible: true,
                content: res,
                title: "Invalid Email Address",
                icon: "mdi-shield-account",
                color: "error",
              });
            } else {
              this.step = 2;
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
          this.EmailAddress.loading = false;
        });
    },
    Generate() {
      this.OTP.loading.generate = true;
      this.promiseFetch(this.$store.getters.fetchTimeout)(
        fetch(this.$store.getters.endpoint + "OTP/Generate/", {
          method: "post",
          body: JSON.stringify({
            EmailAddress: this.EmailAddress.value,
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
          this.OTP.loading.generate = false;
        });
    },
    Validate() {
      this.OTP.loading.validate = true;
      this.promiseFetch(this.$store.getters.fetchTimeout)(
        fetch(this.$store.getters.endpoint + "OTP/Validate/", {
          method: "post",
          body: JSON.stringify({
            OTP: this.OTP.value,
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
              this.step = 3;
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
          this.OTP.loading.validate = false;
        });
    },
    Reset() {
      this.password.loading = true;
      let PasswordRegex =
        /^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,}$/.test(
          this.password.value
        );
      if (!PasswordRegex) {
        this.password.loading = false;
        this.$refs.Alert.Alertify({
          visible: true,
          content:
            "Password is meant to be at least 8 characters long, have at least 1 uppercase, 1 lowercase and 1 special character.",
          title: "Invalid password",
          icon: "mdi-shield-account",
          color: "error",
        });
      } else {
        this.promiseFetch(this.$store.getters.fetchTimeout)(
          fetch(this.$store.getters.endpoint + "ChangePassword/", {
            method: "post",
            body: JSON.stringify({
              LoggedIn: false,
              EmailAddress: this.EmailAddress.value,
              password: this.password.value,
            }),
          })
        )
          .then((response) => {
            response.text().then((res) => {
              if (response.status === 401) {
                this.$refs.Alert.Alertify({
                  visible: true,
                  content: res,
                  title: "Invalid Email Address",
                  icon: "mdi-shield-account",
                  color: "error",
                });
              } else {
                this.Close();
                this.$refs.Alert.Alertify({
                  visible: true,
                  content: res,
                  title: "Password Changed",
                  icon: "mdi-check-decagram",
                  color: "primary",
                });
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
            this.password.loading = false;
          });
      }
    },
    Close() {
      this.visible = false;
      this.step = 1;
      this.OTP.value = "";
      this.password.value = "";
      this.confirm.value = "";
    },
  },
};
</script>
