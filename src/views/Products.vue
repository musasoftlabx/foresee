<template>
  <v-main>
    <v-card tile flat>
      <Appbar />
      <Drawer />
      <v-container fluid>
        <v-row no-gutters align="center" class="py-3">
          <v-col>
            <div class="ml-4">
              <div class="text-h6">
                <number
                  :from="0"
                  :to="count"
                  :duration="1"
                  :format="FormatWithCommas"
                  :delay="0"
                  easing="Power1.easeIn"
                  class="mr-1"
                />{{ $route.name }}
              </div>
              <div class="text-caption mb-2">Products table</div>
              <v-btn
                color="primary"
                small
                @click="ShowUploadProductList"
                class="mr-3"
                >UPLOAD PRODUCT LIST</v-btn
              >
              <v-btn color="primary" small @click="ShowAddProduct"
                >ADD PRODUCT</v-btn
              >
            </div>
          </v-col>
        </v-row>
        <v-row no-gutters class="mt-1 px-4">
          <v-col cols="12">
            <v-card id="FancyTable" outlined />
          </v-col>
        </v-row>
      </v-container>
    </v-card>
    <Alert ref="Alert" />
    <Snackbar ref="Snackbar" />
    <AddProduct ref="AddProduct" @NewProduct="NewProduct" />
    <UploadProductList ref="UploadProductList" @Uploaded="Uploaded" />
  </v-main>
</template>

<script>
import Appbar from "@/components/Appbar";
import Fancy from "fancygrid";
import "fancygrid/client/fancy.min.css";
Fancy.stylesLoaded = true;
import Drawer from "@/components/Drawer";
import Snackbar from "@/components/Snackbar";
import AddProduct from "@/components/Dialogs/AddProduct";
import UploadProductList from "@/components/Dialogs/UploadProductList";

export default {
  components: {
    Appbar,
    Drawer,
    Snackbar,
    AddProduct,
    UploadProductList,
  },
  data() {
    return {
      loading: false,
      FancyGrid: {},
      count: 0,

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
  mounted() {
    if (this.$vuetify.breakpoint.mdAndUp) this.FancyGridDefaults["flex"] = 1;

    new Promise((resolve) => {
      resolve(this.FancyGridDefaults["flex"] === 1);
    }).then(() => {
      this.FancyGrid = new Fancy.Grid({
        renderTo: "FancyTable",
        theme: {
          name: "blue",
          /* config: {
          cellHeight: 92,
          titleHeight: 65,
        }, */
        },
        tbar: [
          {
            text: "Export to CSV",
            handler: () => {
              this.exportToCSV({
                fileName: "Products",
                header: true,
              });
            },
          },
          {
            text: "Refresh",
            handler: () => {
              this.FancyGrid.load();
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
            width: 100,
            editable: true,
          },
          {
            index: "SpecialCode",
            title: "Special Code",
            width: 100,
          },
          {
            index: "section",
            title: "Section",
          },
          {
            index: "location",
            title: "Location",
          },
          {
            index: "name",
            title: "Name",
          },
          {
            index: "quantity",
            title: "Quantity",
            editable: true,
          },
          {
            index: "size",
            title: "Size",
          },
          {
            index: "length",
            title: "Length",
          },
          {
            index: "ColorCode",
            title: "Color Code",
          },
          {
            type: "combo",
            index: "color",
            title: "Color",
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
          remotePage: true,
          remoteFilter: true,
          proxy: {
            autoLoad: true,
            autoSave: false,
            url: `${this.$store.getters.endpoint}Products/Get/`,
            params: {
              token: this.$store.getters.token,
            },
            writer: {
              type: "json",
            },
            beforeRequest: (o) => {
              this.$Progress.start();
              return o;
            },
            afterRequest: (o) => {
              this.$Progress.finish();
              this.count = o.response.totalCount;
              return o;
            },
          },
        },
        paging: {
          pageSize: 20,
          pageSizeData: [5, 10, 15, 20, 50],
          inputWidth: 100,
          refreshButton: true,
        },
        events: [
          {
            change: (grid, o) => {
              this.$Progress.start();
              this.promiseFetch(this.$store.getters.fetchTimeout)(
                fetch(`${this.$store.getters.endpoint}Products/Edit/`, {
                  method: "post",
                  body: JSON.stringify({
                    token: this.$store.getters.token,
                    key: o.id,
                    entity: o.key,
                    value: o.value,
                  }),
                })
              )
                .then((response) => {
                  response.text().then(() => {
                    if (response.status > 200) {
                      this.$Progress.fail();
                      this.$refs.Alert.Alertify({
                        title: "Update Error!",
                        content:
                          "The last change you made could not be saved. Kindly retry.",
                        visible: true,
                        icon: "mdi-wifi-strength-1-alert",
                        color: "error",
                      });
                    } else {
                      this.$refs.Snackbar.Snackify({
                        visible: true,
                        content: "Changes saved!",
                        color: "success",
                        timeout: 2000,
                      });
                      this.FancyGrid.save(o);
                    }
                  });
                })
                .catch(() => {
                  this.$Progress.fail();
                  this.$refs.Alert.Alertify({
                    title: "Connection Timeout",
                    content: this.$store.getters.fetchTimeoutError,
                    visible: true,
                    icon: "mdi-wifi-strength-1-alert",
                    color: "error",
                  });
                })
                .finally(() => {
                  this.$Progress.finish();
                });
            },
          },
        ],
      });
    });
  },
  methods: {
    ShowAddProduct() {
      this.$refs.AddProduct.visible = true;
      setTimeout(() => {
        this.$refs.AddProduct.$refs.section.focus();
      }, 100);
    },
    ShowUploadProductList() {
      this.$refs.UploadProductList.Show();
    },
    NewProduct(data) {
      this.FancyGrid.add(data);

      this.$refs.Snackbar.Snackify({
        visible: true,
        content: `Added ${data.name} to products.`,
        color: "success",
        timeout: 3000,
      });
    },
    Uploaded(count) {
      this.FancyGrid.load();

      this.$refs.Snackbar.Snackify({
        visible: true,
        content: `${count} products have been added to the product list.`,
        color: "success",
        timeout: 3000,
      });
    },
  },
};
</script>
