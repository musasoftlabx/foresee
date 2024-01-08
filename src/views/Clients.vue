<template>
  <v-main>
    <Appbar />
    <v-container>
      <v-row no-gutters align="center" class="py-3">
        <v-col>
          <div class="text-h6">{{ $route.name }}</div>
          <v-btn
            small
            color="primary"
            outlined
            @click="$refs.AddClient.visible = true"
            class="mt-3"
          >
            <v-icon left>mdi-plus</v-icon>ADD CLIENT</v-btn
          >
        </v-col>
      </v-row>
      <v-row no-gutters class="mt-3">
        <v-col cols="12">
          <v-card id="FancyTable" flat />
        </v-col>
      </v-row>
    </v-container>

    <Drawer />
    <Alert ref="Alert" />
    <Snackbar ref="Snackbar" />
    <AddClient ref="AddClient" @NewUser="NewClient" />
    <EditPassword ref="EditPassword" @Edited="Edited" />
    <Confirm ref="Confirm" @confirm="Confirm" @cancel="Confirm" />
  </v-main>
</template>

<script>
import Appbar from "@/components/Appbar";
import Fancy from "fancygrid";
import "fancygrid/client/fancy.min.css";
Fancy.stylesLoaded = true;
import Snackbar from "@/components/Snackbar";
import Drawer from "@/components/Drawer";
import AddClient from "@/components/Dialogs/AddClient";
import EditPassword from "@/components/Dialogs/EditPassword";

export default {
  components: {
    Appbar,
    Snackbar,
    AddClient,
    EditPassword,
    Drawer,
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
      },

      visible: false,
      selected: null,
    };
  },
  mounted() {
    if (this.$vuetify.breakpoint.mdAndUp) this.FancyGridDefaults["flex"] = 1;

    new Promise((resolve) => {
      resolve(this.FancyGridDefaults["flex"] === 1);
    }).then(() => {
      this.FancyGrid = new Fancy.Grid({
        renderTo: "FancyTable",
        theme: "material",
        tbar: [
          {
            text: "Export to CSV",
            handler: function () {
              this.exportToCSV({
                fileName: "Clients",
                header: true,
              });
            },
          },
        ],
        width: "fit",
        height: "fit",
        selModel: "rows",
        stripped: true,
        exporter: true,
        defaults: this.FancyGridDefaults,
        trackOver: true,
        clicksToEdit: 1,
        cellHeight: 60,
        align: "center",
        cellAlign: "center",
        columns: [
          {
            index: "id",
            title: "#",
            width: 50,
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
          /* {
            type: "action",
            title: "Actions",
            align: "center",
            width: 75,
            filter: false,
            rightLocked: true,
            resizable: false,
            items: [
              {
                text: "<v-btn>DELETE</v-btn>",
                cls: "delete-user",
                handler: (grid, o) => {
                  this.Confirm({
                    task: "show",
                    operation: "delete",
                    color: "error",
                    metadata: {
                      key: o.id,
                      object: o,
                    },
                    icon: "mdi-delete-variant",
                    content: `Proceed to remove ${o.data.username}?`,
                    title: "Delete user",
                  });
                },
              },
            ],
          }, */
        ],
        data: {
          proxy: {
            autoLoad: true,
            autoSave: false,
            url: `${this.$store.getters.endpoint}Clients/`,
            beforeRequest: (o) => {
              this.loading = true;
              o.headers[
                "Authorization"
              ] = `Bearer ${this.$store.getters.token}`;
              return o;
            },
          },
        },
        events: [
          {
            change: (grid, o) => {
              this.$Progress.start();
              this.promiseFetch(this.$store.getters.fetchTimeout)(
                fetch(`${this.$store.getters.endpoint}Clients/`, {
                  method: "PUT",
                  headers: {
                    Accept: "application/json",
                    Authorization: `Bearer ${this.$store.getters.token}`,
                    "Content-Type": "application/json",
                  },
                  body: JSON.stringify({
                    username: o.data.username,
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
          //let key = metadata.key;

          this.promiseFetch(this.$store.getters.fetchTimeout)(
            fetch(`${this.$store.getters.endpoint}Users/`, {
              method: "DELETE",
              headers: {
                Accept: "application/json",
                Authorization: `Bearer ${this.$store.getters.token}`,
                "Content-Type": "application/json",
              },
              body: JSON.stringify({ username: object.data.username }),
            })
          )
            .then((response) => {
              response.text().then(() => {
                if (response.status > 200) {
                  this.$refs.Alert.Alertify({
                    visible: true,
                    content: "Error while deleting section. Kindly try again",
                    title: "Error",
                    icon: "mdi-progress-alert",
                    color: "error",
                  });
                } else {
                  this.FancyGrid.remove(object);

                  this.$refs.Snackbar.Snackify({
                    visible: true,
                    content: "Section removed!",
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
    ShowAddClient() {
      this.$refs.AddSection.visible = true;
      setTimeout(() => {
        this.$refs.AddSection.$refs.SectionCode.focus();
      }, 100);
    },
    NewClient(data) {
      this.FancyGrid.insert(data);

      this.$refs.Snackbar.Snackify({
        visible: true,
        content: `Added client successfully.`,
        color: "success",
        timeout: 3000,
      });
    },
    Edited(data) {
      this.FancyGrid.set(data.index, "password", data.password);

      this.$refs.Snackbar.Snackify({
        visible: true,
        content: `Password edited.`,
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
  color: rgb(119, 50, 255);
  font-weight: bold;
}
.change-photo {
  cursor: pointer;
  padding: 5px !important;
  position: relative;
  top: -5px;
  color: rgb(119, 50, 255);
  font-weight: bold;
}
.view-password {
  cursor: pointer;
  padding: 5px !important;
  position: relative;
  top: -5px;
  color: rgb(255, 135, 50);
  font-weight: bold;
}
.delete-user {
  cursor: pointer;
  padding: 5px !important;
  position: relative;
  top: -5px;
  color: rgb(255, 38, 38);
  font-weight: bold;
}
.id-column-cls .fancy-grid-cell {
  background: #ffb798 !important;
  border-color: #ffb798;
}
.photo img {
  border-radius: 100px;
  width: 40px;
  max-width: none;
  margin-top: -5px;
  margin-left: -10px;
}
</style>
