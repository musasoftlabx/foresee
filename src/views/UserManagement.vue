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
            @click="$refs.AddUser.visible = true"
            class="mt-3"
          >
            <v-icon left>mdi-plus</v-icon>ADD USER</v-btn
          >
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
    <AddUser ref="AddUser" @NewUser="NewUser" />
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
import AddUser from "@/components/Dialogs/AddUser";
import EditPassword from "@/components/Dialogs/EditPassword";

import * as FilePond from "filepond";
import "filepond/dist/filepond.min.css";
import FilePondPluginFileValidateSize from "filepond-plugin-file-validate-size";
import FilePondPluginFileValidateType from "filepond-plugin-file-validate-type";
import FilePondPluginFileMetadata from "filepond-plugin-file-metadata";
import FilePondPluginImageTransform from "filepond-plugin-image-transform";
import FilePondPluginImagePreview from "filepond-plugin-image-preview";
import FilePondPluginFileEncode from "filepond-plugin-file-encode";
import "filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css";

export default {
  components: {
    Appbar,
    Snackbar,
    AddUser,
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
            type: "action",
            title: "Change Photo",
            width: 100,
            filter: false,
            resizable: false,
            items: [
              {
                text: "<v-btn>CHANGE</v-btn>",
                cls: "change-photo",
                handler: (grid, o) => {
                  this.visible = true;

                  new Promise((resolve) => {
                    resolve((this.visible = true));
                  }).then(() => {
                    let UUID = null;
                    let Base64Image = null;
                    let Base64ImageMetadata = null;
                    let FormData = null;

                    FilePond.registerPlugin(
                      FilePondPluginFileValidateSize,
                      FilePondPluginFileValidateType,
                      FilePondPluginFileMetadata,
                      FilePondPluginImageTransform,
                      FilePondPluginImagePreview,
                      FilePondPluginFileEncode
                    );
                    FilePond.setOptions({
                      server: {
                        url: this.$store.getters.endpoint + "FileUploader/",
                        process: {
                          method: "post",
                          ondata: (formData) => {
                            formData.forEach((value) => {
                              JSON.stringify(value).indexOf("token") > -1 &&
                                (FormData = JSON.parse(value));
                            });
                            return JSON.stringify({
                              UUID,
                              Base64Image,
                              Base64ImageMetadata,
                              FormData,
                              username: o.data.username,
                            });
                          },
                          onload: (response) => {
                            console.log(o.rowIndex);
                            this.visible = false;
                            this.FancyGrid.set(
                              o.rowIndex,
                              "profilePicture",
                              null
                            );
                            setTimeout(() => {
                              this.FancyGrid.set(
                                o.rowIndex,
                                "profilePicture",
                                JSON.parse(response).URL
                              );
                            }, 1000);
                          },
                          onerror: (response) => {
                            this.$refs.Alert.Alertify({
                              visible: true,
                              content: response,
                              title: "Upload error",
                              icon: "mdi-progress-alert",
                              color: "error",
                            });
                          },
                        },
                      },
                      maxFileSize: "5MB",
                      labelMaxFileSizeExceeded: "File is too large",
                      labelMaxFileSize: "Maximum file size is {filesize}",
                      acceptedFileTypes: [
                        "image/png",
                        "image/jpeg",
                        "image/jpg",
                      ],
                      labelFileTypeNotAllowed: "File of invalid type",
                      fileValidateTypeLabelExpectedTypes:
                        "Expects .PNG .JPG .JPEG",
                      allowImagePreview: true,
                      imagePreviewMinHeight: 44,
                      imagePreviewMaxHeight: 256,
                      imagePreviewMaxFileSize: "5MB",
                      imagePreviewTransparencyIndicator: "#39b54a",
                      imagePreviewMaxInstantPreviewFileSize: 1000000,
                      imageTransformOutputMimeType: "image/jpeg",
                      imageTransformOutputQuality: 70,
                      imagePreviewHeight: 250,
                      imageCropAspectRatio: "1:1",
                      imageResizeTargetWidth: 250,
                      imageResizeTargetHeight: 250,
                      stylePanelLayout: "compact circle",
                      styleLoadIndicatorPosition: "center bottom",
                      styleProgressIndicatorPosition: "right bottom",
                      styleButtonRemoveItemPosition: "left bottom",
                      styleButtonProcessItemPosition: "right bottom",
                      dropOnPage: true,
                      onaddfile: (error, file) => {
                        UUID = file.id;
                      },
                      onremovefile: (error, file) => {
                        this.reupload.uploads = this.reupload.uploads.filter(
                          (i) => i !== file.id
                        );
                      },
                      onpreparefile: (file, output) => {
                        Base64Image = file.getFileEncodeBase64String();
                        Base64ImageMetadata = {
                          name: output.name,
                          size: output.size,
                          type: output.type,
                        };
                      },
                      labelIdle: `<div style="cursor:pointer; z-index: 1; background: transparent">
                                  <img style="font-size:40px;color:#39b54a;width:30px;height:30px" src="${this.$store.getters.server}img/attachment.png"/>
                                  <div class="primary--text" style="font-size:15px; font-weight:400;">Upload avatar here</div>
                                  <div class="my-1" style="font-size: 9px; opacity:.7">Only JPG, PNG, GIF allowed</div>
                                  <div class="text-body-2">Size less than 5 MB</div>
                                  </div>`,
                    });
                    this.pond = FilePond.create(
                      document.querySelector("[name*=attachment]"),
                      {
                        fileMetadataObject: {
                          token: this.$store.getters.token,
                        },
                      }
                    );
                  });
                },
              },
            ],
          },
          /* {
            index: "profilePicture",
            title: "Photo",
            render: function (o) {
              o.value = `
                <div class="photo">
                  <img src="${o.value}" />
                  <button onclick="this.visible = true">dvgrer</button>
                </div>`;

              return o;
            },
          }, */
          {
            index: "firstName",
            title: "FirstName",
          },
          {
            index: "lastName",
            title: "LastName",
          },
          {
            index: "username",
            title: "Username",
          },
          {
            type: "action",
            title: "Password",
            align: "left",
            width: 100,
            filter: false,
            resizable: false,
            items: [
              {
                text: "<v-btn>CHECK</v-btn>",
                cls: "view-password",
                handler: (grid, o) => {
                  this.$refs.EditPassword.visible = true;
                  this.$refs.EditPassword.index = o.rowIndex;
                  this.$refs.EditPassword.username = o.data.username;
                  this.$refs.EditPassword.password = o.data.password;
                },
              },
            ],
          },
          {
            index: "role",
            title: "Role",
            type: "combo",
            data: ["Super Admin", "Supervisor", "Scanner"],
            editable: true,
          },
          {
            index: "addedBy",
            title: "Added By",
          },
          {
            index: "addedOn",
            title: "Added On",
          },
          {
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
          },
        ],
        data: {
          proxy: {
            autoLoad: true,
            autoSave: false,
            url: `${this.$store.getters.endpoint}Users/`,
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
                fetch(`${this.$store.getters.endpoint}Users/`, {
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
    ShowAddStore() {
      this.$refs.AddSection.visible = true;
      setTimeout(() => {
        this.$refs.AddSection.$refs.SectionCode.focus();
      }, 100);
    },
    NewUser(data) {
      this.FancyGrid.insert(data);

      this.$refs.Snackbar.Snackify({
        visible: true,
        content: `Added ${data.username} to sections.`,
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
