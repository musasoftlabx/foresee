<template>
  <v-main>
    <v-container fluid>
      <v-row justify="center" align="center" style="height: 100vh">
        <barcode
          :value="24537892844"
          text="Master reset Barcode"
          text-margin="5"
          :width="1"
          height="50"
          text-position="top"
        />
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
  },
};
</script>
