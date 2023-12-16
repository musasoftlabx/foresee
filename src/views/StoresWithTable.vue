<template>
  <v-main>
    <v-card tile flat>
      <Appbar />
      <Drawer />
      <v-container fluid>
        <div class="ml-4 py-3">
          <div class="text-h6">{{ $route.name }}</div>
          <div class="text-caption">
            This table is editable. However, only Name is. Leaving the data item
            will automatically save.
          </div>
          <v-btn color="primary" outlined @click="ShowAddStore" class="mt-3">
            <v-icon left>mdi-plus</v-icon>ADD STORE</v-btn
          >
        </div>
        <v-row no-gutters class="mt-3 px-4">
          <v-col cols="12">
            <v-card id="FancyTable" outlined />
          </v-col>
        </v-row>
      </v-container>
    </v-card>
    <Alert ref="Alert" />
    <Snackbar ref="Snackbar" />
    <AddStore ref="AddStore" @NewStore="NewStore" />
    <Confirm ref="Confirm" @confirm="Confirm" @cancel="Confirm" />
  </v-main>
</template>

<script>
import Appbar from "@/components/Appbar";
import Fancy from "fancygrid";
import "fancygrid/client/fancy.min.css";
Fancy.stylesLoaded = true;
import Drawer from "@/components/Drawer";
import Snackbar from "@/components/Snackbar";
import AddStore from "@/components/Dialogs/AddStore";

export default {
  components: {
    Appbar,
    Drawer,
    Snackbar,
    AddStore,
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
          name: "material",
          /* config: {
          cellHeight: 92,
          titleHeight: 65,
        }, */
        },
        tbar: [
          {
            text: "Export to CSV",
            handler: function () {
              this.exportToCSV({
                fileName: "Stores",
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
        /* footer: {
          status:
            '<span style="position: relative;top: 3px;">*</span> - Devices online per 100 inhabitants in 2015',
          source: {
            text: "OECD",
            link: "oecd.org",
          },
        },
        contextmenu: [
          "copy",
          "copy+",
          "-",
          "delete",
          "-",
          "export",
          "-",
          {
            text: "Custom",
            imageCls: "custom-menu-item-cls",
            sideText: "CUSTOM",
            handler: function () {
              alert("Click on custom item");
            },
          },
        ], */
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
            //cls: "id-column-cls",
            locked: true,
          },
          {
            index: "id",
            title: "PK",
            hidden: true,
          },
          {
            index: "code",
            title: "Code",
          },
          {
            index: "name",
            title: "Name",
            editable: true,
          },
          {
            type: "combo",
            index: "country",
            title: "Country",
            data: this.$store.getters.countries,
          },
          {
            index: "client",
            title: "Client",
          },
          {
            index: "modifiedBy",
            title: "Modified By",
          },
          {
            index: "modifiedOn",
            title: "Modified On",
          },
          {
            type: "action",
            title: "Actions",
            align: "center",
            width: 120,
            filter: false,
            rightLocked: true,
            resizable: false,
            items: [
              {
                text: "<v-btn>GO</v-btn>",
                cls: "action-column-link",
                handler: (grid, o) => {
                  this.$router.push({
                    name: "Store",
                    params: { id: o.id, code: o.value.code },
                  });
                },
              },
              {
                text: "<v-btn>DELETE</v-btn>",
                cls: "action-column-remove",
                handler: (grid, o) => {
                  this.Confirm({
                    task: "show",
                    operation: "delete",
                    color: "warning",
                    metadata: {
                      key: o.id,
                      object: o,
                    },
                    icon: "mdi-delete-variant",
                    content: `Proceed to remove ${o.data.name} store?`,
                    title: "Remove store",
                  });
                },
              },
            ],
          },
        ],
        data: {
          proxy: {
            autoLoad: true,
            autoSave: false,
            methods: {
              read: "GET",
            },
            api: {
              read: `${this.$store.getters.endpoint}Stores/`,
            },
            params: {},
            words: {
              key: "index",
              page: "pageNumber",
              limit: "pageSize",
            },
            writer: {
              type: "json",
            },
            beforeRequest: (o) => {
              this.$Progress.start();
              //console.log(o);
              //o.type - hint of request.
              //Possible values: create/read/update/delete

              //o.params - server request params
              o.headers[
                "Authorization"
              ] = `Bearer ${this.$store.getters.token}`;
              //o.params["token"] = this.$store.getters.token;

              //o.params.page += 1;
              return o;
            },
            afterRequest: (o) => {
              this.$Progress.finish();
              //o.response - server answer
              return o;
            },
          },
        },
        events: [
          {
            change: (grid, o) => {
              this.$Progress.start();
              this.promiseFetch(this.$store.getters.fetchTimeout)(
                fetch(`${this.$store.getters.endpoint}Stores/`, {
                  method: "PUT",
                  headers: {
                    Accept: "application/json",
                    Authorization: `Bearer ${this.$store.getters.token}`,
                    "Content-Type": "application/json",
                  },
                  body: JSON.stringify({
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
                .finally(() => this.$Progress.finish());
            },
          },
        ],
      });
      this.FancyGrid.setTitle("Sum total of 30 stores");
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
            fetch(`${this.$store.getters.endpoint}Stores/`, {
              method: "DELETE",
              headers: {
                Accept: "application/json",
                Authorization: `Bearer ${this.$store.getters.token}`,
                "Content-Type": "application/json",
              },
              body: JSON.stringify({
                code: object.data.code,
                name: object.data.name,
                key,
              }),
            })
          )
            .then((response) =>
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
              })
            )
            .catch(() =>
              this.$refs.Alert.Alertify({
                visible: true,
                content: this.$store.getters.fetchTimeoutError,
                title: "Connection Timeout",
                icon: "mdi-wifi-strength-1-alert",
                color: "error",
              })
            )
            .finally(() => (this.loading = false));
        }
      } else if (!props.task) {
        this.$refs.Confirm.Confirmify({
          visible: false,
        });
      }
    },
    ShowAddStore() {
      this.$refs.AddStore.visible = true;
      setTimeout(() => this.$refs.AddStore.$refs.StoreName.focus(), 100);
    },
    NewStore(data) {
      this.FancyGrid.insert(data);

      this.$refs.Snackbar.Snackify({
        visible: true,
        content: `Added ${data.name} to list of stores.`,
        color: "success",
        timeout: 3000,
      });
    },
  },
};
</script>

<style>
.action-column-remove {
  cursor: pointer;
  padding: 5px !important;
  position: relative;
  top: -5px;
  border-radius: 2px;
  background-color: #e4827d;
  color: #fff;
  margin-left: 10px;
}
.action-column-link {
  cursor: pointer;
  padding: 5px !important;
  position: relative;
  top: -5px;
  border-radius: 2px;
  background-color: #7790fe;
  color: #fff;
}
.id-column-cls .fancy-grid-cell {
  background: #ffb798 !important;
  border-color: #ffb798;
}
</style>
