<template>
  <v-main>
    <v-card tile flat>
      <Appbar />
      <Drawer />
      <v-container fluid>
        <v-row no-gutters align="center" class="pt-3">
          <v-col>
            <div class="ml-4">
              <div class="text-h6">Scans</div>
              <div class="text-caption">
                Reporting of scans made in this store
              </div>
            </div>
          </v-col>
        </v-row>
        <v-row class="mt-1 px-4">
          <v-col cols="12">
            <v-card id="FancyTable" outlined />
          </v-col>
          <v-col cols="12">
            <v-card id="Failed" outlined />
          </v-col>
          <v-col cols="4">
            <v-card id="Grouped" outlined />
          </v-col>
          <v-spacer />
          <v-col cols="8">
            <v-card id="Discrepancies" outlined />
          </v-col>
        </v-row>
      </v-container>
    </v-card>
    <Alert ref="Alert" />
    <Snackbar ref="Snackbar" />
    <Confirm ref="Confirm" @confirm="Confirm" @cancel="Confirm" />
    <ExportRange ref="ExportRange" />
    <AddBarcode ref="AddBarcode" @Closed="AddBarcodeClosed" />
  </v-main>
</template>

<script>
import Appbar from "@/components/Appbar";
import Fancy from "fancygrid";
import "fancygrid/client/fancy.min.css";
Fancy.stylesLoaded = true;
import Drawer from "@/components/Drawer";
import Snackbar from "@/components/Snackbar";
import ExportRange from "@/components/Dialogs/ExportRange";
import AddBarcode from "@/components/Dialogs/AddBarcode";

export default {
  /* beforeRouteEnter: (to, from, next) =>
    role === "Scanner" ? next("/Locations") : next(), */
  components: {
    Appbar,
    Drawer,
    Snackbar,
    ExportRange,
    AddBarcode,
  },
  data() {
    return {
      loading: false,
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
        selected: null,
      },

      IndividualScansGrid: {},
      FailedScansGrid: {},
      CumulativeScansGrid: {},
      DiscrepanciesReportGrid: {},
    };
  },
  mounted() {
    const statics = [
      {
        operator: "eq",
        property: "storeId",
        value: `${this.$route.params.id}`,
      },
    ];
    const filter = encodeURI(JSON.stringify(statics));

    if (this.$vuetify.breakpoint.mdAndUp) this.FancyGridDefaults["flex"] = 1;
    let that = this;

    new Promise((resolve) => {
      resolve(this.FancyGridDefaults["flex"] === 1);
    }).then(() => {
      this.IndividualScansGrid = new Fancy.Grid({
        renderTo: "FancyTable",
        title: "Individual Product Scan",
        theme: {
          name: "material",
          /* config: {
          cellHeight: 92,
          titleHeight: 65,
        }, */
        },
        tbar: [
          {
            text: "Export All",
            handler: () => {
              this.ExportIndividual();
            },
          },
          {
            text: "Export Range",
            handler: () => (this.$refs.ExportRange.visible = true),
          },
          {
            text: "Add barcode",
            handler: () => (this.$refs.AddBarcode.visible = true),
          },
          {
            text: "Refresh",
            handler: () => {
              this.IndividualScansGrid.load();
            },
          },
          {
            text: "Delete Scans",
            handler: () => {
              this.DeleteScans();
            },
          },
        ],
        height: "fit",
        //selModel: "rows",
        stripped: true,
        exporter: true,
        defaults: this.FancyGridDefaults,
        trackOver: true,
        clicksToEdit: 2,
        selModel: {
          type: "rows",
          memory: true,
        },
        columns: [
          {
            type: "select",
          },
          {
            type: "order",
            locked: true,
          },
          {
            index: "id",
            title: "#",
            //hidden: true,
          },
          {
            index: "code",
            title: "Location Barcode",
          },
          {
            index: "barcode",
            title: "ProductBarcode",
            editable: true,
          },
          {
            index: "name",
            title: "Name",
          },
          {
            index: "quantity",
            title: "Quantity",
          },
          {
            index: "length",
            title: "Length",
          },
          {
            index: "color",
            title: "Color",
          },
          {
            index: "scannedBy",
            title: "Scanned By",
          },
          {
            index: "scannedOn",
            title: "Scanned On",
          },
          {
            index: "batteryLevel",
            title: "Battery Level",
          },
        ],
        events: [
          {
            contextmenu: function (grid, o) {
              that.FancyGridDefaults.selected = o.data;
            },
          },
          {
            endedit: function (grid, o) {
              if (o.data.barcode != o.value) {
                that.FancyGridDefaults.selected = {
                  ...o.data,
                  column: o.column.id,
                };
                that.BarcodeControl("put");
              }
            },
          },
        ],
        contextmenu: [
          {
            type: "duplicate",
            text: "Duplicate (Creates replica)",
            handler: function (grid, o) {
              that.BarcodeControl("post");
            },
          },
          {
            type: "delete",
            text: "Delete (Removes permanently)",
            handler: function (grid, o) {
              that.BarcodeControl(o.type);
            },
          },
        ],
        data: {
          remotePage: true,
          remoteFilter: true,
          proxy: {
            autoLoad: true,
            autoSave: false,
            url: `${this.$store.getters.endpoint}Scans/Get/Individual/?filter=${filter}`,
            params: {
              token: this.$store.getters.token,
              //filter: this.$store.getters.token,
              context: "display",
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
          pageSize: 10,
          pageSizeData: [5, 10, 15, 20],
          inputWidth: 100,
          refreshButton: true,
        },
      });
    });

    new Promise((resolve) => {
      resolve(this.FancyGridDefaults["flex"] === 1);
    }).then(() => {
      this.FailedScansGrid = new Fancy.Grid({
        renderTo: "Failed",
        title: "Failed Scans",
        theme: {
          name: "material",
          /* config: {
          cellHeight: 92,
          titleHeight: 65,
        }, */
        },
        tbar: [
          {
            text: "Export to Excel",
            handler: () => {
              this.ExportFailed();
            },
          },
          {
            text: "Refresh",
            handler: () => {
              this.FailedScansGrid.load();
            },
          },
          {
            text: "Delete Scans",
            handler: () => {
              this.DeleteScans();
            },
          },
        ],
        height: "fit",
        //selModel: "rows",
        stripped: true,
        exporter: true,
        defaults: this.FancyGridDefaults,
        trackOver: true,
        clicksToEdit: 1,
        selModel: {
          type: "rows",
          memory: true,
        },
        columns: [
          {
            type: "select",
          },
          {
            type: "order",
            locked: true,
          },
          {
            index: "id",
            title: "#",
            //hidden: true,
          },
          {
            index: "code",
            title: "Location Barcode",
          },
          {
            index: "barcode",
            title: "ProductBarcode",
          },
          {
            index: "name",
            title: "Name",
          },
          {
            index: "quantity",
            title: "Quantity",
          },
          {
            index: "length",
            title: "Length",
          },
          {
            index: "color",
            title: "Color",
          },
          {
            index: "scannedBy",
            title: "Scanned By",
          },
          {
            index: "scannedOn",
            title: "Scanned On",
          },
          {
            index: "batteryLevel",
            title: "Battery Level",
          },
        ],
        data: {
          remotePage: true,
          remoteFilter: true,
          proxy: {
            autoLoad: true,
            autoSave: false,
            url: `${this.$store.getters.endpoint}Scans/Get/Failed/?filter=${filter}`,
            params: {
              token: this.$store.getters.token,
              context: "display",
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
          pageSize: 5,
          pageSizeData: [5, 10],
          inputWidth: 100,
          refreshButton: true,
        },
      });
    });

    new Promise((resolve) => {
      resolve(this.FancyGridDefaults["flex"] === 1);
    }).then(() => {
      this.CumulativeScansGrid = new Fancy.Grid({
        renderTo: "Grouped",
        title: "Product Scan Count",
        theme: "material",
        tbar: [
          {
            text: "Export to TXT",
            handler: () => {
              this.ExportTXT();
            },
          },
          {
            text: "Refresh",
            handler: () => {
              this.CumulativeScansGrid.load();
            },
          },
        ],
        height: "fit",
        selModel: "rows",
        stripped: true,
        exporter: true,
        defaults: this.FancyGridDefaults,
        trackOver: true,
        columns: [
          {
            type: "order",
            locked: true,
          },
          {
            index: "barcode",
            title: "Product Barcode",
          },
          {
            index: "quantity",
            title: "Quantity",
          },
        ],
        data: {
          proxy: {
            autoLoad: true,
            autoSave: false,
            url: `${this.$store.getters.endpoint}Scans/Get/Cumulative/`,
            params: {
              storeId: this.$route.params.id,
              token: this.$store.getters.token,
              context: "display",
            },
          },
        },
        paging: {
          pageSize: 15,
          pageSizeData: [5, 10, 15, 20, 50],
          inputWidth: 50,
        },
      });
    });

    new Promise((resolve) => {
      resolve(this.FancyGridDefaults["flex"] === 1);
    }).then(() => {
      this.DiscrepanciesReportGrid = new Fancy.Grid({
        renderTo: "Discrepancies",
        title: "Discrepancies Report",
        theme: {
          name: "material",
          /* config: {
          cellHeight: 92,
          titleHeight: 65,
        }, */
        },
        tbar: [
          {
            text: "Export to Excel",
            handler: () => {
              this.ExportDiscrepancies();
            },
          },
          {
            text: "Refresh",
            handler: () => {
              this.DiscrepanciesReportGrid.load();
            },
          },
        ],
        height: "fit",
        //selModel: "rows",
        stripped: true,
        exporter: true,
        defaults: this.FancyGridDefaults,
        trackOver: true,
        clicksToEdit: 1,
        selModel: {
          type: "rows",
          memory: true,
        },
        columns: [
          {
            type: "select",
          },
          {
            type: "order",
            locked: true,
          },
          {
            index: "id",
            title: "#",
          },
          {
            index: "code",
            title: "Location",
          },
          {
            index: "physicalCount",
            title: "Physical Count",
          },
          {
            index: "systemCount",
            title: "System Count",
          },
          {
            index: "discrepancy",
            title: "Discrepancy",
          },
        ],
        data: {
          remotePage: true,
          remoteFilter: true,
          proxy: {
            autoLoad: true,
            autoSave: false,
            url: `${this.$store.getters.endpoint}Scans/Get/Discrepancies/?filter=${filter}`,
            params: {
              token: this.$store.getters.token,
              context: "display",
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
          pageSize: 5,
          pageSizeData: [5, 10],
          inputWidth: 100,
          refreshButton: true,
        },
      });
    });
  },
  methods: {
    Confirm(props) {
      if (props.task === "show") {
        this.$refs.Confirm.Confirmify({
          visible: true,
          task: props.task,
          operation: props.operation,
          metadata: props.metadata,
          content: props.content,
          title: props.title,
          icon: props.icon || "mdi-help-circle",
          color: props.color || "orange",
          truth: props.truth || "YES",
          falsy: props.falsy || "NO",
        });
      } else if (props.task) {
        this.$refs.Confirm.Confirmify({
          visible: false,
        });
        if (this.$refs.Confirm.confirm.operation === "delete") {
          let metadata = this.$refs.Confirm.confirm.metadata;
          let object = metadata.object;
          let key = metadata.key;

          this.promiseFetch(this.$store.getters.fetchTimeout)(
            fetch(`${this.$store.getters.endpoint}Stores/Delete/`, {
              method: "post",
              body: JSON.stringify({
                key,
              }),
            })
          )
            .then((response) => {
              response.text().then(() => {
                if (response.status > 200) {
                  this.$refs.Alert.Alertify({
                    visible: true,
                    content: "Error while deleting store. Kindly try again",
                    title: "Error",
                    icon: "mdi-progress-alert",
                    color: "error",
                  });
                } else {
                  this.FancyGrid.remove(object);

                  this.$refs.Snackbar.Snackify({
                    visible: true,
                    content: "Store removed!",
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
              this.loading = false;
            });
        }
      } else if (!props.task) {
        this.$refs.Confirm.Confirmify({
          visible: false,
        });
      }
    },
    DeleteScans() {
      let keys = this.IndividualScansGrid.getSelection().map((item) => item.id);

      this.promiseFetch(this.$store.getters.fetchTimeout)(
        fetch(`${this.$store.getters.endpoint}Scans/Delete/`, {
          method: "post",
          body: JSON.stringify({
            token: this.$store.getters.token,
            keys,
          }),
        })
      )
        .then((response) => {
          response.text().then(() => {
            if (response.status > 200) {
              this.$refs.Alert.Alertify({
                visible: true,
                content: "Error while deleting store. Kindly try again",
                title: "Error",
                icon: "mdi-progress-alert",
                color: "error",
              });
            } else {
              this.IndividualScansGrid.load();

              this.$refs.Snackbar.Snackify({
                visible: true,
                content: "Items removed!",
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
          this.loading = false;
        });
    },
    BarcodeControl(method) {
      this.FancyGridDefaults.selected = {
        ...this.FancyGridDefaults.selected,
        storeId: Number(this.$route.params.id),
      };

      this.promiseFetch(this.$store.getters.fetchTimeout)(
        fetch(`${this.$store.getters.endpoint}BarcodeControl/`, {
          method,
          headers: {
            Accept: "application/json",
            Authorization: `Bearer ${this.$store.getters.token}`,
            "Content-Type": "application/json",
          },
          body: JSON.stringify(this.FancyGridDefaults.selected),
        })
      )
        .then((response) => {
          response.text().then(() => {
            if (response.status > 201) {
              this.$refs.Alert.Alertify({
                visible: true,
                content: "Error while duplicating store. Kindly try again",
                title: "Error",
                icon: "mdi-progress-alert",
                color: "error",
              });
            } else {
              this.IndividualScansGrid.load();

              this.$refs.Snackbar.Snackify({
                visible: true,
                content: "Items duplicated!",
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
        });
    },
    AddBarcodeClosed() {
      this.IndividualScansGrid.load();
    },
    ExportTXT() {
      location.href = `${this.$store.getters.endpoint}Scans/Get/Cumulative/?storeId=${this.$route.params.id}&token=${this.$store.getters.token}&context=export`;
    },
    ExportIndividual() {
      location.href = `${this.$store.getters.endpoint}Scans/Get/Individual/?storeId=${this.$route.params.id}&token=${this.$store.getters.token}&context=export`;
    },
    ExportFailed() {
      location.href = `${this.$store.getters.endpoint}Scans/Get/Failed/?storeId=${this.$route.params.id}&token=${this.$store.getters.token}&context=export`;
    },
    ExportDiscrepancies() {
      location.href = `${this.$store.getters.endpoint}Scans/Get/Discrepancies/?storeId=${this.$route.params.id}&token=${this.$store.getters.token}&context=export`;
    },
  },
  beforeDestroy() {
    clearInterval(window.ScansUpdate);
  },
};
</script>

<style>
.CustomFormModifier .v-btn.v-btn--has-bg {
  background-image: linear-gradient(
    to right,
    #84fab0 0%,
    #8fd3f4 51%,
    #84fab0 100%
  );
}
.custom-shape-divider-bottom-1632171631 {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  overflow: hidden;
  line-height: 0;
}

.custom-shape-divider-bottom-1632171631 svg {
  position: relative;
  display: block;
  width: calc(100% + 1.3px);
  height: 120px;
  transform: rotateY(180deg);
}

.custom-shape-divider-bottom-1632171631 .shape-fill {
  fill: #fff;
}

.divider-color {
  background-image: linear-gradient(to right, #74ebd5 0%, #9face6 100%);
}

.action-column-remove {
  cursor: pointer;
  padding: 5px !important;
  position: relative;
  top: -5px;
  border-radius: 2px;
  background-color: #e4827d;
  color: #fff;
}
.id-column-cls .fancy-grid-cell {
  background: #ffb798 !important;
  border-color: #ffb798;
}
</style>
