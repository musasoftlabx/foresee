<template>
  <section>
    <v-dialog
      v-model="visible"
      persistent
      max-width="350"
      transition="dialog-top-transition"
    >
      <v-card color="primary" dark class="rounded-lg pa-8 pb-2">
        <v-row no-gutters>
          <v-col cols="10">
            <div class="text-h5 font-weight-thin">
              <v-icon size="26" class="mr-3">mdi-barcode</v-icon>
              Scan Barcode
            </div>
            <div style="color: #bdffff" class="text-caption mt-1 ml-1">
              The barcode will appear below. It will be validated.
            </div>
          </v-col>
          <v-col cols="2" align="end"
            ><v-icon size="24" @click="visible = false"
              >mdi-close</v-icon
            ></v-col
          >
        </v-row>
        <v-row no-gutters>
          <v-col cols="12">
            <v-form ref="Scanner">
              <v-text-field
                ref="ProductBarcode"
                v-model="ProductBarcode"
                v-mask="'#############'"
                solo-inverted
                :disabled="loading"
                rounded
                class="mt-6"
              >
                <template v-slot:append>
                  <v-progress-circular
                    v-if="loading"
                    color="white"
                    indeterminate
                    class="mr-n3"
                  />
                </template>
              </v-text-field>
            </v-form>
          </v-col>
        </v-row>
      </v-card>
    </v-dialog>
    <Snackbar ref="Snackbar" />
  </section>
</template>

<script>
import Snackbar from "@/components/Snackbar";
export default {
  components: {
    Snackbar,
  },
  data() {
    return {
      visible: false,
      LocationPK: null,
      LocationBarcode: null,
      ProductBarcode: null,
      loading: false,
    };
  },
  watch: {
    ProductBarcode(v) {
      if (v.length === 13) {
        this.ValidateScannedBarcode();
      }
    },
  },
  methods: {
    ValidateScannedBarcode() {
      this.loading = true;
      this.promiseFetch(this.$store.getters.fetchTimeout)(
        fetch(this.$store.getters.endpoint + "ValidateScannedBarcode/", {
          method: "post",
          body: JSON.stringify({
            token: this.$store.getters.token,
            LocationPK: this.LocationPK,
            LocationBarcode: this.LocationBarcode,
            ProductBarcode: this.ProductBarcode,
          }),
        })
      )
        .then((response) => {
          response.text().then((res) => {
            if (response.status > 200) {
              this.$refs.Snackbar.Snackify({
                visible: true,
                content: res,
                color: "error",
                timeout: 2000,
              });
            } else {
              this.$refs.Snackbar.Snackify({
                visible: true,
                content: res,
                color: "success",
                timeout: 2000,
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
          this.$refs.Scanner.reset();
          setTimeout(() => {
            this.$refs.ProductBarcode.focus();
          }, 100);

          this.loading = false;
        });
    },
  },
};
</script>
