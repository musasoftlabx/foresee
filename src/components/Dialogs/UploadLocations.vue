<template>
  <section>
    <v-dialog
      v-model="visible"
      persistent
      max-width="700"
      transition="dialog-top-transition"
    >
      <v-card flat class="rounded-lg">
        <div id="Modal-divider-top">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 1000 125"
            preserveAspectRatio="none"
          >
            <path
              class="divider-fill"
              style="opacity: 0.5"
              d="m 0,81.659348 c 81.99918,0 88.739113,19.697032 141.99858,19.697032 46.67953,0 33.12967,-19.551122 85.12915,-19.551122 57.49943,0 75.87924,34.141512 143.87856,34.141512 55.99944,0 98.99901,-36.475982 149.49851,-36.475982 76.49923,0 77.32923,29.482322 131.49868,43.284832 55.99944,14.2694 72.82927,-44.169982 142.82857,-44.169982 41.37959,0 74.35926,28.033012 103.68897,28.033012 C 942.67057,106.61865 1000,75.093678 1000,75.093678 V 24.999998 H 0 Z"
            />
            <path
              class="divider-fill"
              d="m 0,81.988666 c 97.331947,-14.74806 115.33231,19.667594 176.67353,19.667594 53.33107,0 60.14121,-51.965834 122.64246,-51.965834 84.00168,0 116.35232,90.668944 200.004,73.034484 80.0016,-16.85492 86.00172,-63.205964 153.34306,-63.205964 49.661,0 86.65174,33.00405 124.00248,33.00405 33.33067,0 51.27103,-31.16054 78.00156,-31.16054 43.93088,0 51.28103,31.0868 97.90196,28.83245 C 976.99954,89.015066 1000,74.267006 1000,74.267006 V 5.8499091e-6 H 0 Z"
            />
          </svg>
        </div>
        <v-card dark flat color="transparent" class="px-8 pt-6">
          <v-row no-gutters>
            <v-col cols="10">
              <div class="text-h5 font-weight-thin">Upload Locations</div>
              <v-btn
                small
                color="white"
                outlined
                @click="DownloadLocationsTemplate"
                class="mt-3"
              >
                <v-icon left>mdi-plus</v-icon>
                DOWNLOAD TEMPLATE
              </v-btn>
            </v-col>
            <v-col cols="2" align="end"
              ><v-icon size="24" @click="visible = false"
                >mdi-close</v-icon
              ></v-col
            >
          </v-row>
        </v-card>
        <div
          class="pt-2 px-5 pb-0"
          style="
            overflow-y: auto;
            overflow-x: hidden;
            padding-top: 190px !important;
          "
        >
          <v-form
            ref="UploadLocations"
            v-model="valid"
            class="CustomFormModifier"
          >
            <v-row justify="center" no-gutters>
              <v-col cols="12" class="px-2">
                <v-card
                  dark
                  flat
                  class="pt-5 pb-2"
                  style="
                    background-color: #efefef;
                    border: 2px dashed #bdbdbd !important;
                    border-radius: 25px;
                    overflow: hidden;
                  "
                >
                  <input type="file" name="attachment" />
                </v-card>
              </v-col>

              <v-data-table
                v-if="DBcolumns.length > 0 || excelFields.length > 0"
                :headers="[
                  {
                    text: 'Column  in database',
                    value: 'column',
                  },
                  {
                    text: 'Excel field to match',
                    value: 'field',
                  },
                ]"
                :items="DBcolumns"
                disable-sort
                hide-default-footer
                class="mt-5 CustomVerticalTable"
              >
                <template v-slot:item.column="{ item }">
                  <v-col justify="center">
                    <div class="text-subtitle-2 blue--text">
                      {{ item.column }}
                    </div>
                  </v-col>
                </template>
                <template v-slot:item.field="{ item }">
                  <v-col justify="center">
                    <v-select
                      v-model="item.excelField"
                      :rules="
                        item.column === 'barcode' ||
                        (item.column === 'Barcode' ? [validators.required] : [])
                      "
                      :items="excelFields"
                      :label="
                        item.column === 'barcode' || item.column === 'Barcode'
                          ? 'Excel field (Required) *'
                          : 'Excel field (Optional)'
                      "
                      item-text="field"
                      item-value="field"
                      dense
                      hide-details
                      outlined
                    >
                      <template v-slot:item="data">
                        <v-list dense two-line>
                          <v-list-item>
                            <!-- <v-list-item-avatar>
                          v-model="column.value"
                          <v-icon size="40" class="primary--text"
                            >mdi-store</v-icon
                          >
                        </v-list-item-avatar> -->
                            <v-list-item-content>
                              <v-list-item-title class="text-subtitle-2">{{
                                data.item.field
                              }}</v-list-item-title>
                              <v-list-item-subtitle
                                ><span>Sample:&nbsp;</span
                                ><span class="text-caption grey--text">{{
                                  data.item.sample
                                }}</span></v-list-item-subtitle
                              >
                            </v-list-item-content>
                          </v-list-item>
                        </v-list>
                      </template>
                    </v-select>
                  </v-col>
                </template>
              </v-data-table>
              <v-col cols="12" align="center" class="px-2 pt-8 pb-6">
                <v-btn
                  color="primary"
                  height="35"
                  width="300"
                  rounded
                  :loading="loading"
                  :disabled="
                    !valid ||
                    loading ||
                    DBcolumns.length === 0 ||
                    excelFields.length === 0
                  "
                  @click="Commit"
                  >COMMIT</v-btn
                >
              </v-col>
            </v-row>
          </v-form>
        </div>
      </v-card>
    </v-dialog>
    <Alert ref="Alert" />
  </section>
</template>

<script>
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
  props: ["store"],
  data() {
    return {
      visible: false,
      loading: false,
      valid: false,
      excelFields: [],
      DBcolumns: [],
      filename: null,
    };
  },
  methods: {
    Show() {
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
            url: this.$store.getters.endpoint + "Locations/UploadTemplate/?Upload",
            process: {
              method: "post",
              headers: {
                Authorization: `Bearer ${this.$store.getters.token}`,
                "Content-Type": "application/json",
              },
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
                });
              },
              onload: (response) => {
                const res = JSON.parse(response);
                this.excelFields = res.excelFields;
                this.DBcolumns = res.DBcolumns;
                this.filename = res.filename;
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
            "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
            "application/vnd.ms-excel",
            "text/csv",
          ],
          labelFileTypeNotAllowed: "File of invalid type",
          fileValidateTypeLabelExpectedTypes: "Expects .XLSX .XLS .CSV",
          allowImagePreview: true,
          imagePreviewMinHeight: 44,
          imagePreviewMaxHeight: 200,
          imagePreviewMaxFileSize: "5MB",
          imagePreviewTransparencyIndicator: "#39b54a",
          imagePreviewMaxInstantPreviewFileSize: 1000000,
          imageTransformOutputMimeType: "image/jpeg",
          imageTransformOutputQuality: 70,
          imagePreviewHeight: 200,
          imageCropAspectRatio: "1:1",
          imageResizeTargetWidth: 100,
          imageResizeTargetHeight: 200,
          dropOnPage: true,
          onaddfile: (error, file) => {
            UUID = file.id;
          },
          onremovefile: (error, file) => {
            this.DBcolumns = [];
            this.excelFields = [];
          },
          onpreparefile: (file, output) => {
            Base64Image = file.getFileEncodeBase64String();
            Base64ImageMetadata = {
              name: output.name,
              size: output.size,
              type: output.type,
            };
          },
          labelIdle: `<div style="cursor:pointer; z-index: 1; background: transparent; ">
                    <img style="font-size:40px;color:#39b54a;width:30px;height:30px" src="${this.$store.getters.server}img/attachment.png"/>
                    <div class="primary--text" style="font-size:15px; font-weight:400;">Upload excel file here</div>
                    <div class="my-1" style="font-size: 9px; opacity:.7">Only XLS, XLSX, CSV allowed</div>
                    <div class="text-body-2">Size less than 5 MB</div>
                    </div>`,
        });
        this.pond = FilePond.create(
          document.querySelector("[name*=attachment]")
        );
      });
    },
    Commit() {
      this.loading = true;

      this.promiseFetch(300000)(
        fetch(`${this.$store.getters.endpoint}Locations/UploadTemplate/?Commit`, {
          method: "POST",
          headers: {
            Accept: "application/json",
            Authorization: `Bearer ${this.$store.getters.token}`,
            "Content-Type": "application/json",
          },
          body: JSON.stringify({
            filename: this.filename,
            mappings: this.DBcolumns,
          }),
        })
      )
        .then((response) =>
          response.json().then((res) => {
            if (response.status > 201) {
              this.$refs.Alert.Alertify({
                title: res.title,
                content: res.content,
                visible: true,
                icon: "mdi-progress-close",
                color: "warning",
              });
            } else {
              this.pond.removeFiles();
              this.visible = false;
            }
          })
        )
        .catch((e) =>
          this.$refs.Alert.Alertify({
            visible: true,
            content: e.message || this.$store.getters.fetchTimeoutError,
            title: "Connection Timeout",
            icon: "mdi-wifi-strength-1-alert",
            color: "error",
          })
        )
        .finally(() => (this.loading = false));
    },
    DownloadLocationsTemplate() {
      location.href = `${this.$store.getters.endpoint}Locations/DownloadTemplate/?storeId=${this.$route.params.id}`;
    }
  },
};
</script>

<style>
#Modal-divider-top {
  overflow: hidden;
  position: absolute;
  top: 0;
  width: 100%;
  height: 230px;
  line-height: 0;
  left: 0;
  transform: scaleX(-1);
}
#Modal-divider-top svg {
  display: block;
  width: 100%;
  height: 100%;
  position: relative;
  left: 50%;
  transform: translateX(-50%);
}
#Modal-divider-top .divider-fill {
  fill: #7b68ee;
  transform-origin: bottom;
  transform: rotateY(0deg);
}
.CustomVerticalTable thead th {
  border-top: 1px solid #7a7a7a;
  border-bottom: 1px solid #7a7a7a !important;
}
.CustomVerticalTable tbody td {
  border-bottom: 1px solid #7a7a7a;
}
.CustomVerticalTable td,
.CustomVerticalTable th {
  border-right: 1px solid #7a7a7a;
}
.CustomVerticalTable td:first-child,
.CustomVerticalTable th:first-child {
  border-left: 1px solid #7a7a7a;
}
</style>
