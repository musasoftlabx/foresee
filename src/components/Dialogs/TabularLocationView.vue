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
              <div class="text-h5 font-weight-thin">Locations</div>
              <div style="color: #bdffff" class="text-caption mt-0">
                All locations citing discrepancy count.
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
          <v-skeleton-loader v-show="loading" type="table" class="mt-1" />
          <v-card id="LocationsContainer" outlined tile />
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
  props: ["store"],
  data() {
    return {
      visible: false,
      loading: false,
      locations: [],
      LocationsGrid: {},
      FancyGridDefaults: {
        sortable: true,
        editable: false,
        resizable: true,
        ellipsis: false,
        filter: {
          header: true,
          emptyText: "Search",
        },
        /* filter: [
          {
            operator: "eq",
            property: "storeId",
            value: `${this.$route.params.id}`,
          },
        ], */
      },
    };
  },
  watch: {
    visible(v) {
      v ? this.RenderLocations() : this.LocationsGrid.destroy();
    },
  },
  methods: {
    RenderLocations() {
      if (this.$vuetify.breakpoint.mdAndUp) this.FancyGridDefaults["flex"] = 1;

      new Promise((resolve) => {
        resolve(this.FancyGridDefaults["flex"] === 1);
      }).then(() => {
        const statics = [
          {
            operator: "eq",
            property: "storeId",
            value: `${this.$route.params.id}`,
          },
        ];
        const filter = encodeURI(JSON.stringify(statics));

        this.LocationsGrid = new Fancy.Grid({
          renderTo: "LocationsContainer",
          theme: "blue",
          tbar: [
            {
              text: "Export All Locations",
              handler: () => {
                this.ExportCSV();
              },
              /* handler: () => {
                this.LocationsGrid.exportToCSV({
                  fileName: "All Locations",
                  header: true,
                  ignoreRender: true,
                  all: false,
                });
              }, */
            },
            {
              text: "Export Deleted Locations",
              handler: () => {
                this.LocationsGrid.addFilter("active", 0);
                setTimeout(() => {
                  this.LocationsGrid.exportToCSV({
                    fileName: "Deleted Locations",
                    header: true,
                    ignoreRender: true,
                  });
                  this.LocationsGrid.clearFilter();
                }, 1000);
              },
            },
          ],
          height: "fit",
          selModel: "rows",
          stripped: true,
          exporter: true,
          filter: true,
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
              title: "#",
              hidden: true,
            },
            {
              index: "code",
              locked: true,
              title: "Code",
              width: 200,
            },
            {
              index: "physicalCount",
              title: "Physical Count",
              editable: true,
            },
            {
              index: "systemCount",
              title: "System Count",
              editable: true,
            },
            {
              index: "discrepancy",
              title: "Discrepancy",
              exportFn: (o) => {
                o.value = o.data.discrepancy;
                return o;
              },
              render: (o) => {
                if (o.value > 0) {
                  o.style = {
                    color: "#E46B67",
                    "font-weight": "bold",
                    "font-size": "14px",
                  };
                } else {
                  o.style = {
                    color: "#65AE6E",
                  };
                }
                return o;
              },
            },
            {
              index: "isActive",
              title: "Is Active?",
              editable: true,
              type: "switcher",
              exportFn: (o) => {
                if (o.value == 1) {
                  o.value = "Yes";
                } else {
                  o.value = "No";
                }
                return o;
              },
              render: (o) => {
                if (o.value == 1) {
                  o.value = true;
                } else {
                  o.value = false;
                }
                return o;
              },
            },
            {
              index: "modifiedBy",
              title: "Modified By",
            },
            {
              index: "modifiedOn",
              title: "Modified On",
            },
          ],
          data: {
            remotePage: true,
            remoteFilter: true,
            proxy: {
              autoLoad: true,
              autoSave: false,
              methods: { read: "GET" },
              api: {
                read: `${this.$store.getters.endpoint}Locations/?filter=${filter}`,
              },
              writer: { type: "json" },
              beforeRequest: (o) => {
                this.loading = true;
                o.headers[
                  "Authorization"
                ] = `Bearer ${this.$store.getters.token}`;
                return o;
              },
              afterRequest: (o) => {
                this.loading = false;
                this.visible = true;
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
                  fetch(`${this.$store.getters.endpoint}Locations/Edit/`, {
                    method: "post",
                    body: JSON.stringify({
                      token: this.$store.getters.token,
                      resource: "sections",
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
                        /* this.$router.push("/Reloader");
                        this.$router.push("/Locations"); */
                        location.reload();
                        /* this.$refs.Snackbar.Snackify({
                          visible: true,
                          content: "Changes saved!",
                          color: "success",
                          timeout: 2000,
                        }); */
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
    ExportCSV() {
      location.href = `${this.$store.getters.endpoint}Locations/Export/?storeId=${this.$route.params.id}&store=${this.store.name}&token=${this.$store.getters.token}`;
    },
  },
};
</script>
