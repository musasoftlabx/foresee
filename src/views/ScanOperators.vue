<template>
  <v-main>
    <Appbar />
    <v-container>
      <v-row no-gutters align="center" class="py-3">
        <v-col>
          <div class="text-h6">{{ $route.name }}</div>
        </v-col>
      </v-row>
      <v-row no-gutters class="mt-3">
        <v-col cols="12">
          <v-card id="FancyTable" flat />
        </v-col>
      </v-row>
    </v-container>

    <v-dialog
      v-model="visible"
      persistent
      max-width="265"
      transition="dialog-top-transition"
    >
      <v-card flat class="rounded-lg px-8 py-6">
        <v-row align="center" no-gutters>
          <v-col cols="10">
            <div class="text-h6 font-weight-thin">Upload Photo</div>
          </v-col>
          <v-col cols="2" align="end"
            ><v-icon size="24" @click="visible = false"
              >mdi-close</v-icon
            ></v-col
          >
        </v-row>
        <div class="pt-2 pb-0">
          <v-card
            dark
            flat
            width="200"
            height="200"
            class="pa-1 rounded-circle"
            style="
              background-color: #efefef;
              border: 2px dashed #bdbdbd !important;
              border-radius: 5px;
              overflow: hidden;
            "
          >
            <input type="file" name="attachment" />
          </v-card>
        </div>
      </v-card>
    </v-dialog>

    <Drawer />
    <Alert ref="Alert" />
    <Snackbar ref="Snackbar" />
  </v-main>
</template>

<script>
import Appbar from "@/components/Appbar";
import Fancy from "fancygrid";
import "fancygrid/client/fancy.min.css";
Fancy.stylesLoaded = true;
import Snackbar from "@/components/Snackbar";
import Drawer from "@/components/Drawer";

export default {
  components: {
    Appbar,
    Snackbar,
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
                fileName: "Users",
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
            type: "image",
            title: "Photo",
            index: "profilePicture",
            width: 80,
            cls: "photo",
          },
          {
            index: "firstName",
            title: "FirstName",
          },
          {
            index: "lastName",
            title: "LastName",
          },
          {
            index: "scannedBy",
            title: "Username",
          },
          {
            index: "scans",
            title: "Scans",
          },
          {
            index: "batteryLevel",
            title: "Battery Level",
          },
        ],
        data: {
          proxy: {
            autoLoad: true,
            autoSave: false,
            url: `${this.$store.getters.endpoint}ScanOperators/`,
            beforeRequest: (o) => {
              this.loading = true;
              o.headers[
                "Authorization"
              ] = `Bearer ${this.$store.getters.token}`;
              return o;
            },
          },
        },
      });
    });
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
