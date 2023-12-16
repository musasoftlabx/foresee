<template>
  <section>
    <v-dialog
      v-model="visible"
      max-width="300"
      persistent
      transition="dialog-top-transition"
    >
      <v-card flat class="rounded-lg">
        <div id="Modal-divider-top">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 1000 125"
            preserveAspectRatio="none"
          >
            <path
              class="divider-fill"
              style="opacity: 0.5"
              d="m 0,81.659348 c 81.99918,0 88.739113,19.697032 141.99858,19.697032 46.67953,0 33.12967,-19.551122 85.12915,-19.551122 57.49943,0 75.87924,34.141512 143.87856,34.141512 55.99944,0 98.99901,-36.475982 149.49851,-36.475982 76.49923,0 77.32923,29.482322 131.49868,43.284832 55.99944,14.2694 72.82927,-44.169982 142.82857,-44.169982 41.37959,0 74.35926,28.033012 103.68897,28.033012 C 942.67057,106.61865 1000,75.093678 1000,75.093678 V 24.999998 H 0 Z"
            />
            <path
              class="divider-fill"
              d="m 0,81.988666 c 97.331947,-14.74806 115.33231,19.667594 176.67353,19.667594 53.33107,0 60.14121,-51.965834 122.64246,-51.965834 84.00168,0 116.35232,90.668944 200.004,73.034484 80.0016,-16.85492 86.00172,-63.205964 153.34306,-63.205964 49.661,0 86.65174,33.00405 124.00248,33.00405 33.33067,0 51.27103,-31.16054 78.00156,-31.16054 43.93088,0 51.28103,31.0868 97.90196,28.83245 C 976.99954,89.015066 1000,74.267006 1000,74.267006 V 5.8499091e-6 H 0 Z"
            />
          </svg>
        </div>
        <v-card dark flat color="transparent" class="px-8 pt-6">
          <v-row no-gutters>
            <v-col cols="10">
              <div class="text-h5 font-weight-thin">
                <!-- <v-icon size="26" class="mt-n1">mdi-target-variant</v-icon> -->
                Create Location
              </div>
              <!-- <div style="color: #bdffff" class="text-caption mt-0 ml-1">
                Create a new location by entering the details below
              </div> -->
            </v-col>
            <v-col cols="2" align="end"
              ><v-icon size="24" @click="visible = false"
                >mdi-close</v-icon
              ></v-col
            >
          </v-row>
        </v-card>
        <div
          class="pt-2 px-5 pb-0"
          style="
            overflow-y: auto;
            overflow-x: hidden;
            padding-top: 200px !important;
          "
        >
          <v-form ref="CreateLocation" v-model="valid">
            <v-row no-gutters justify="center">
              <!-- <v-col cols="12" class="px-2">
                <v-autocomplete
                  v-model="store"
                  :rules="[validators.required]"
                  :items="FormData.stores"
                  label="In which store? *"
                  item-text="name"
                  item-value="code"
                  return-object
                  filled
                  rounded
                  prepend-inner-icon="mdi-store"
                >
                  <template v-slot:item="data">
                    <v-list dense two-line>
                      <v-list-item>
                        <v-list-item-avatar>
                          <v-icon size="40" class="primary--text"
                            >mdi-store</v-icon
                          >
                        </v-list-item-avatar>
                        <v-list-item-content>
                          <v-list-item-title class="text-subtitle-1">{{
                            data.item.name
                          }}</v-list-item-title>
                          <v-list-item-subtitle
                            class="text-subtitle-2 grey--text"
                            >{{ data.item.country }}</v-list-item-subtitle
                          >
                        </v-list-item-content>
                      </v-list-item>
                    </v-list>
                  </template>
                </v-autocomplete>
              </v-col> -->

              <v-dialog
                ref="dialog"
                v-model="date.visible"
                :return-value.sync="date.scanDate"
                persistent
                width="290px"
              >
                <template v-slot:activator="{ on, attrs }">
                  <v-col cols="12" class="px-2">
                    <v-text-field
                      v-model="date.scanDate"
                      :rules="[validators.required]"
                      label="Expected Scan Date"
                      prepend-inner-icon="mdi-calendar"
                      readonly
                      filled
                      rounded
                      dense
                      v-bind="attrs"
                      v-on="on"
                    />
                  </v-col>
                </template>
                <v-date-picker v-model="date.scanDate" scrollable>
                  <v-spacer />
                  <v-btn text color="primary" @click="date.visible = false">
                    Cancel
                  </v-btn>
                  <v-btn
                    text
                    color="primary"
                    @click="$refs.dialog.save(date.scanDate)"
                  >
                    OK
                  </v-btn>
                </v-date-picker>
              </v-dialog>

              <v-col cols="7" class="px-2">
                <v-text-field
                  v-model="quantity"
                  v-mask="'####'"
                  :rules="[validators.required]"
                  label="How many? *"
                  filled
                  rounded
                  dense
                  @keyup.enter="CreateLocation"
                />
              </v-col>

              <v-col cols="12" align="end" class="px-2 pb-6">
                <v-btn
                  color="primary"
                  height="35"
                  rounded
                  text
                  :loading="loading"
                  :disabled="!valid || loading"
                  @click="CreateLocation"
                  >CREATE</v-btn
                >
              </v-col>
            </v-row>
          </v-form>
        </div>
      </v-card>
    </v-dialog>
    <Alert ref="Alert" />
  </section>
</template>

<script>
export default {
  props: ["store"],
  data() {
    return {
      visible: false,
      loading: false,
      valid: false,
      date: {
        visible: false,
        scanDate: null,
      },
      quantity: 1,
    };
  },
  methods: {
    CreateLocation() {
      this.loading = true;

      this.promiseFetch(this.$store.getters.fetchTimeout)(
        fetch(`${this.$store.getters.endpoint}Locations/?Create`, {
          method: "POST",
          headers: {
            Accept: "application/json",
            Authorization: `Bearer ${this.$store.getters.token}`,
            "Content-Type": "application/json",
          },
          body: JSON.stringify({
            storeId: this.$route.params.id,
            storeCode: this.store.code,
            quantity: Number(this.quantity),
            scanDate: {
              date: this.date.scanDate,
              formatted: Number(this.date.scanDate.split("-").join("")),
            },
          }),
        })
      )
        .then((response) =>
          response.json().then((res) => {
            if (response.status > 201) {
              this.$refs.Alert.Alertify({
                visible: true,
                content: res.content,
                title: res.title,
                icon: "mdi-progress-alert",
                color: "error",
              });
            } else {
              this.$refs.CreateLocation.reset();
              this.$emit("NewLocation", res);
              this.visible = false;
            }
          })
        )
        .catch(() => {
          this.$refs.Alert.Alertify({
            visible: true,
            content: this.$store.getters.fetchTimeoutError,
            title: "Connection Timeout",
            icon: "mdi-wifi-strength-1-alert",
            color: "error",
          });
        })
        .finally(() => (this.loading = false));
    },
  },
};
</script>

<style>
#Modal-divider-top {
  overflow: hidden;
  position: absolute;
  top: 0;
  width: 100%;
  height: 230px;
  line-height: 0;
  left: 0;
  transform: scaleX(-1);
}
#Modal-divider-top svg {
  display: block;
  width: 100%;
  height: 100%;
  position: relative;
  left: 50%;
  transform: translateX(-50%);
}
#Modal-divider-top .divider-fill {
  fill: #7b68ee;
  transform-origin: bottom;
  transform: rotateY(0deg);
}
</style>
