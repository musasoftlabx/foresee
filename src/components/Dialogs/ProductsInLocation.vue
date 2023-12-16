<template>
  <section>
    <v-dialog
      v-model="visible"
      persistent
      max-width="80vw"
      transition="scale-transition"
    >
      <v-card flat color="primary" class="rounded-lg">
        <v-card dark flat color="transparent" class="px-8 py-6">
          <v-row no-gutters>
            <v-col cols="10">
              <div class="text-h5 font-weight-thin">Products</div>
              <div style="color: #bdffff" class="text-caption mt-0">
                Location:
                <span class="yellow--text">{{ location }}</span>
              </div>
            </v-col>
            <v-col cols="2" align="end"
              ><v-icon size="24" @click="visible = false"
                >mdi-close</v-icon
              ></v-col
            >
          </v-row>
        </v-card>
        <div
          style="
            overflow-y: auto;
            overflow-x: hidden;
            max-height: calc(100vh - 200px);
          "
        >
          <v-card id="ProductsInLocationContainer" outlined tile />
        </div>
      </v-card>
    </v-dialog>
  </section>
</template>

<script>
import Fancy from "fancygrid";
import "fancygrid/client/fancy.min.css";
Fancy.stylesLoaded = true;
export default {
  data() {
    return {
      visible: false,
      loading: false,
      valid: false,
      location: null,
      FancyGrid: {},
      FancyGridDefaults: {
        sortable: true,
        editable: false,
        resizable: true,
        ellipsis: false,
        filter: {
          header: true,
          emptyText: "Search",
        },
      },
    };
  },
  watch: {
    visible(v) {
      v ? this.GetProducts() : this.FancyGrid.destroy();
    },
  },
  methods: {
    GetProducts() {
      if (this.$vuetify.breakpoint.mdAndUp) this.FancyGridDefaults["flex"] = 1;

      new Promise((resolve) => {
        resolve(this.FancyGridDefaults["flex"] === 1);
      }).then(() => {
        this.FancyGrid = new Fancy.Grid({
          renderTo: "ProductsInLocationContainer",
          theme: "material",
          tbar: [
            {
              text: "Export to CSV",
              handler: function () {
                this.exportToCSV({
                  fileName: "Products In Location",
                  header: true,
                });
              },
            },
          ],
          height: "fit",
          selModel: "rows",
          stripped: true,
          exporter: true,
          defaults: this.FancyGridDefaults,
          trackOver: true,
          clicksToEdit: 1,
          columns: [
            {
              type: "order",
              locked: true,
            },
            {
              index: "id",
              title: "PK",
              hidden: true,
            },
            {
              index: "barcode",
              locked: true,
              title: "Barcode",
            },
            {
              index: "SpecialCode",
              title: "Special Code",
            },
            {
              index: "section",
              title: "Section",
              filter: false,
            },
            {
              index: "location",
              title: "Location",
              filter: false,
            },
            {
              index: "name",
              title: "Name",
              editable: true,
            },
            {
              index: "quantity",
              title: "Quantity",
              filter: false,
            },
            {
              index: "size",
              title: "Size",
              editable: true,
            },
            {
              index: "length",
              title: "Length",
              editable: true,
            },
            {
              index: "ColorCode",
              title: "Color Code",
              editable: true,
            },
            {
              type: "combo",
              index: "color",
              title: "Color",
              editable: true,
              data: ["Brown", "Khaki", "Caramel", "Black"],
            },

            {
              index: "LastScannedBy",
              title: "Last Scanned By",
            },
            {
              index: "LastScannedOn",
              title: "Last Scanned On",
            },
          ],
          data: {
            proxy: {
              autoLoad: true,
              url: `${this.$store.getters.endpoint}Locations/`,
              params: {
                products: true,
                location: this.location,
              },
              beforeRequest: (o) => {
                o.headers[
                  "Authorization"
                ] = `Bearer ${this.$store.getters.token}`;
                return o;
              },
              afterRequest: (o) => o,
            },
          },
          paging: {
            pageSize: 20,
            pageSizeData: [5, 10, 15, 20, 50],
            inputWidth: 100,
          },
        });
      });
    },
  },
};
</script>
