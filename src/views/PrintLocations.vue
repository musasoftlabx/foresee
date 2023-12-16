<template>
  <v-main>
    <v-container fluid :class="$vuetify.breakpoint.smAndUp && 'px-12 pt-6'">
      <v-row justify="center">
        <v-col cols="12" sm="12">
          <v-data-iterator
            :items="dataset"
            :items-per-page.sync="itemsPerPage"
            :page.sync="page"
            :search="search"
            :loading="loading"
            hide-default-footer
            no-data-text=""
          >
            <template v-slot:header>
              <div class="mb-8">
                <span class="text-subheading-1">Print range</span>

                <v-row no-gutters>
                  <v-col cols="12" sm="2">
                    <v-text-field
                      v-model="range.from"
                      clearable
                      dense
                      outlined
                      hide-details
                      prepend-inner-icon="mdi-barcode"
                      label="From"
                      class="my-3 mr-6"
                    />
                  </v-col>
                  <v-col cols="12" sm="2">
                    <v-text-field
                      v-model="range.to"
                      clearable
                      dense
                      outlined
                      hide-details
                      prepend-inner-icon="mdi-barcode"
                      label="To"
                      class="my-3"
                    />
                  </v-col>
                </v-row>
                <v-btn
                  color="primary"
                  class="mt-3"
                  rounded
                  text
                  @click="GetBarcodes"
                  ><v-icon left>mdi-printer</v-icon>GET BARCODES</v-btn
                >

                <v-btn
                  color="primary"
                  class="mt-3"
                  rounded
                  text
                  :disabled="dataset.length === 0"
                  @click="Print"
                  ><v-icon left>mdi-printer</v-icon>PRINT BARCODES</v-btn
                >
              </div>
            </template>

            <template v-slot:loading>
              <v-row>
                <v-col
                  v-for="i in 12"
                  :key="i"
                  cols="12"
                  sm="6"
                  md="4"
                  lg="3"
                  xl="2"
                >
                  <v-skeleton-loader type="table-cell@6" />
                </v-col>
              </v-row>
            </template>

            <template v-slot:default="props">
              <section id="barcodes" class="mt-5">
                <table class="table table-bordered">
                  <tbody>
                    <tr v-for="(item, i) in props.items" :key="i">
                      <barcode
                        :value="item.code.split('-')[0]"
                        :text="item.code.split('-')[0]"
                        text-margin="5"
                        :width="1"
                        height="50"
                        text-position="top"
                      />
                    </tr>
                  </tbody>
                </table>
              </section>
            </template>
          </v-data-iterator>
        </v-col>
      </v-row>
    </v-container>
    <Drawer />
    <Alert ref="Alert" />
  </v-main>
</template>

<script>
import VueBarcode from "vue-barcode";
import Drawer from "@/components/Drawer";

//const role = JSON.parse(atob(localStorage._4c_token_.split(".")[1])).data.role;

export default {
  /* beforeRouteEnter: (to, from, next) =>
    role === "Scanner" ? next("/Locations") : next(), */
  components: {
    barcode: VueBarcode,
    Drawer,
  },
  data() {
    return {
      loading: false,
      headers: [],
      dataset: [],
      itemsPerPageArray: [12, 24, 36],
      search: "",
      SearchResults: [],
      filter: {},
      sortDesc: false,
      page: 1,
      itemsPerPage: 5000,
      sortBy: "no",
      range: {
        from: null,
        to: null,
      },
    };
  },
  methods: {
    async Print() {
      await this.$htmlToPaper("barcodes");
    },
    GetBarcodes() {
      this.$Progress.start();
      this.skeleton = true;

      this.promiseFetch(this.$store.getters.fetchTimeout)(
        fetch(this.$store.getters.endpoint + "Locations/print.php", {
          method: "POST",
          headers: {
            Accept: "application/json",
            Authorization: `Bearer ${this.$store.getters.token}`,
            "Content-Type": "application/json",
          },
          body: JSON.stringify({
            storeId: this.$route.params.id,
            from: this.range.from,
            to: this.range.to,
          }),
        })
      )
        .then((response) => {
          response.json().then((res) => {
            this.dataset = res.data;

            setTimeout(() => {
              this.skeleton = false;
            }, 1000);
            this.NoContent.visible = false;
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
          this.$Progress.finish();
        });
    },
  },
  computed: {
    filteredKeys() {
      return this.headers.filter((key) => key !== `section` || key !== `store`);
    },
  },
};
</script>
