<template>
  <v-main>
    <v-card tile flat>
      <Drawer />

      <v-row align="center">
        <v-col>
          <div class="ml-4">
            <div class="text-h6">{{ $route.name }}</div>
            <div class="text-caption">
              This table is editable. Click on a data item to edit. Pressing
              ENTER key or leaving the data item will automatically save.
            </div>
          </div>
        </v-col>
        <v-col align="end" class="pr-8"
          ><v-btn
            color="primary"
            :loading="loading"
            :disabled="loading"
            @click="$refs.CreateAPI.visible = true"
          >
            <v-icon left>mdi-plus</v-icon>CREATE AN API</v-btn
          ></v-col
        >
      </v-row>
      <v-divider />

      <v-row justify="center">
        <v-col cols="12">
          <v-data-table
            :headers="headers"
            :items="dataset"
            :loading="loading"
            :single-expand="false"
            :expanded.sync="expanded"
            :items-per-page="25"
            item-key="PK"
            @input="selected = $event"
            dense
            class="mx-5 CustomVerticalTable mt-3"
            style="background: transparent"
          >
            <template v-slot:item.name="{ header, item }">
              <v-text-field
                v-model.trim="item.name.value"
                solo
                dense
                flat
                hide-details
                @focus="
                  item.name.focused = true;
                  item.name.memory = item.name.value;
                "
                @keyup.enter="
                  item.name.focused = false;
                  Edit(
                    dataset.indexOf(item),
                    item.PK,
                    header.value,
                    item.name.value,
                    item.name.memory
                  );
                "
                @blur="
                  item.name.focused = false;
                  Edit(
                    dataset.indexOf(item),
                    item.PK,
                    header.value,
                    item.name.value,
                    item.name.memory
                  );
                "
                :loading="item.name.loading"
                :outlined="item.name.focused"
                prepend-inner-icon="mdi-playlist-edit"
                class="text-body-2 font-weight-bold"
              />
            </template>

            <template v-slot:item.alias="{ header, item }">
              <v-text-field
                v-model.trim="item.alias.value"
                solo
                dense
                flat
                hide-details
                @focus="
                  item.alias.focused = true;
                  item.alias.memory = item.alias.value;
                "
                @keyup.enter="
                  item.properties.focused = false;
                  Edit(
                    dataset.indexOf(item),
                    item.PK,
                    header.value,
                    item.alias.value,
                    item.alias.memory
                  );
                "
                @blur="
                  item.alias.focused = false;
                  Edit(
                    dataset.indexOf(item),
                    item.PK,
                    header.value,
                    item.alias.value,
                    item.alias.memory
                  );
                "
                :loading="item.alias.loading"
                :outlined="item.alias.focused"
                prepend-inner-icon="mdi-playlist-edit"
                class="text-body-2"
              />
            </template>

            <template v-slot:item.color="{ header, item }">
              <v-menu
                v-model="item.color.focused"
                :close-on-content-click="false"
                right
                offset-x
              >
                <template v-slot:activator="{ on, attrs }">
                  <v-icon
                    v-bind="attrs"
                    v-on="on"
                    size="30"
                    :color="item.color.value"
                    >mdi-format-color-fill</v-icon
                  >
                </template>
                <v-card color="white">
                  <v-color-picker
                    v-model="item.color.value"
                    mode="hexa"
                    :modes="['hsla', 'rgba', 'hexa']"
                    @focus="item.color.memory = item.color.value"
                  />
                  <v-card-actions>
                    <v-spacer />
                    <v-btn text @click="item.color.focused = false">
                      Cancel
                    </v-btn>
                    <v-btn
                      color="primary"
                      text
                      @click="
                        item.color.focused = false;
                        Edit(
                          dataset.indexOf(item),
                          item.PK,
                          header.value,
                          item.color.value,
                          item.color.memory
                        );
                      "
                    >
                      Save
                    </v-btn>
                  </v-card-actions>
                </v-card>
              </v-menu>
            </template>

            <template v-slot:item.operation="{ header, item }">
              <v-select
                v-model="item.operation.value"
                :items="['POST', 'GET']"
                hide-details
                solo
                flat
                dense
                @focus="
                  item.operation.focused = true;
                  item.operation.memory = item.operation.value;
                "
                @blur="item.operation.focused = false"
                @change="
                  item.operation.focused = false;
                  Edit(
                    dataset.indexOf(item),
                    item.PK,
                    header.value,
                    item.operation.value,
                    item.operation.memory
                  );
                "
                :loading="item.operation.loading"
                :outlined="item.operation.focused"
                prepend-inner-icon="mdi-playlist-edit"
                class="text-subtitle-2 font-weight-bold"
              />
            </template>

            <template v-slot:item.endpoint="{ header, item }">
              <div v-if="item.endpoint.value" class="body-2">
                <v-text-field
                  v-model.trim="item.endpoint.value"
                  solo
                  dense
                  flat
                  success
                  hide-details
                  @focus="
                    item.endpoint.focused = true;
                    item.endpoint.memory = item.endpoint.value;
                  "
                  @keyup.enter="
                    item.endpoint.focused = false;
                    Edit(
                      dataset.indexOf(item),
                      item.PK,
                      header.value,
                      item.endpoint.value,
                      item.endpoint.memory
                    );
                  "
                  @blur="
                    item.endpoint.focused = false;
                    Edit(
                      dataset.indexOf(item),
                      item.PK,
                      header.value,
                      item.endpoint.value,
                      item.endpoint.memory
                    );
                  "
                  :loading="item.endpoint.loading"
                  :outlined="item.endpoint.focused"
                  prefix="Endpoint: "
                  prepend-inner-icon="mdi-playlist-edit"
                  class="text-body-2"
                />
              </div>
              <div class="body-2">
                <v-text-field
                  v-model.trim="item.purpose.value"
                  solo
                  dense
                  flat
                  success
                  hide-details
                  @focus="
                    item.purpose.focused = true;
                    item.purpose.memory = item.purpose.value;
                  "
                  @keyup.enter="
                    item.purpose.focused = false;
                    Edit(
                      dataset.indexOf(item),
                      item.PK,
                      header.value,
                      item.purpose.value,
                      item.purpose.memory
                    );
                  "
                  @blur="
                    item.purpose.focused = false;
                    Edit(
                      dataset.indexOf(item),
                      item.PK,
                      header.value,
                      item.purpose.value,
                      item.purpose.memory
                    );
                  "
                  :loading="item.purpose.loading"
                  :outlined="item.purpose.focused"
                  prefix="Purpose: "
                  prepend-inner-icon="mdi-playlist-edit"
                  class="text-body-2"
                />
              </div>
            </template>

            <template v-slot:item.visible="{ header, item }">
              <v-switch
                v-model="item.visible.value"
                dense
                @focus="item.visible.memory = item.visible.value"
                @change="
                  Edit(
                    dataset.indexOf(item),
                    item.PK,
                    header.value,
                    item.visible.value,
                    item.visible.memory
                  )
                "
              />
            </template>

            <template v-slot:item.delete="{ header, item }">
              <v-btn
                fab
                text
                small
                @click="Delete(dataset.indexOf(item), item.PK)"
              >
                <v-icon color="error">mdi-delete</v-icon>
              </v-btn>
            </template>

            <template v-slot:expanded-item="{ headers, item }">
              <td v-if="item.subsidiaries.length > 0" :colspan="headers.length">
                <v-simple-table dense>
                  <template v-slot:default>
                    <thead>
                      <tr>
                        <th class="text-left" style="width: 16%">Name</th>
                        <th class="text-left" style="width: 15%">Alias</th>
                        <th class="text-left" style="width: 11%">Subsidiary</th>
                        <th class="text-left" style="width: 13%">
                          Parent Alias
                        </th>
                        <th class="text-left" style="width: 36%">
                          Endpoint &amp; API Purpose
                        </th>
                        <th class="text-left" style="width: 5%">Visible?</th>
                        <th class="text-left" style="width: 4%">Delete</th>
                      </tr>
                    </thead>
                    <tbody style="background: #fff">
                      <tr v-for="(subsidiary, s) in item.subsidiaries" :key="s">
                        <td>
                          <v-text-field
                            v-model.trim="subsidiary.name.value"
                            solo
                            dense
                            flat
                            hide-details
                            @focus="
                              subsidiary.name.focused = true;
                              subsidiary.name.memory = subsidiary.name.value;
                            "
                            @keyup.enter="
                              subsidiary.name.focused = false;
                              Edit(
                                s,
                                subsidiary.PK,
                                subsidiary.name.header,
                                subsidiary.name.value,
                                subsidiary.name.memory,
                                dataset.indexOf(item)
                              );
                            "
                            @blur="
                              subsidiary.name.focused = false;
                              Edit(
                                s,
                                subsidiary.PK,
                                subsidiary.name.header,
                                subsidiary.name.value,
                                subsidiary.name.memory,
                                dataset.indexOf(item)
                              );
                            "
                            :loading="subsidiary.name.loading"
                            :outlined="subsidiary.name.focused"
                            prepend-inner-icon="mdi-playlist-edit"
                            class="text-body-2 font-weight-bold"
                          />
                        </td>
                        <td>
                          <v-text-field
                            v-model.trim="subsidiary.alias.value"
                            solo
                            dense
                            flat
                            hide-details
                            @focus="
                              subsidiary.alias.focused = true;
                              subsidiary.alias.memory = subsidiary.alias.value;
                            "
                            @keyup.enter="
                              subsidiary.alias.focused = false;
                              Edit(
                                s,
                                subsidiary.PK,
                                subsidiary.alias.header,
                                subsidiary.alias.value,
                                subsidiary.alias.memory,
                                dataset.indexOf(item)
                              );
                            "
                            @blur="
                              subsidiary.alias.focused = false;
                              Edit(
                                s,
                                subsidiary.PK,
                                subsidiary.alias.header,
                                subsidiary.alias.value,
                                subsidiary.alias.memory,
                                dataset.indexOf(item)
                              );
                            "
                            :loading="subsidiary.alias.loading"
                            :outlined="subsidiary.alias.focused"
                            prepend-inner-icon="mdi-playlist-edit"
                            class="text-body-2"
                          />
                        </td>
                        <td>
                          <v-text-field
                            v-model.trim="subsidiary.subsidiary.value"
                            solo
                            dense
                            flat
                            hide-details
                            @focus="
                              subsidiary.subsidiary.focused = true;
                              subsidiary.subsidiary.memory =
                                subsidiary.subsidiary.value;
                            "
                            @keyup.enter="
                              subsidiary.subsidiary.focused = false;
                              Edit(
                                s,
                                subsidiary.PK,
                                subsidiary.subsidiary.header,
                                subsidiary.subsidiary.value,
                                subsidiary.subsidiary.memory,
                                dataset.indexOf(item)
                              );
                            "
                            @blur="
                              subsidiary.subsidiary.focused = false;
                              Edit(
                                s,
                                subsidiary.PK,
                                subsidiary.subsidiary.header,
                                subsidiary.subsidiary.value,
                                subsidiary.subsidiary.memory,
                                dataset.indexOf(item)
                              );
                            "
                            :loading="subsidiary.subsidiary.loading"
                            :outlined="subsidiary.subsidiary.focused"
                            prepend-inner-icon="mdi-playlist-edit"
                            class="text-body-2"
                          />
                        </td>
                        <td>
                          <v-text-field
                            v-model.trim="subsidiary.parent.value"
                            solo
                            dense
                            flat
                            hide-details
                            @focus="
                              subsidiary.parent.focused = true;
                              subsidiary.parent.memory =
                                subsidiary.parent.value;
                            "
                            @keyup.enter="
                              subsidiary.parent.focused = false;
                              Edit(
                                s,
                                subsidiary.PK,
                                subsidiary.parent.header,
                                subsidiary.parent.value,
                                subsidiary.parent.memory,
                                dataset.indexOf(item)
                              );
                            "
                            @blur="
                              subsidiary.parent.focused = false;
                              Edit(
                                s,
                                subsidiary.PK,
                                subsidiary.parent.header,
                                subsidiary.parent.value,
                                subsidiary.parent.memory,
                                dataset.indexOf(item)
                              );
                            "
                            :loading="subsidiary.parent.loading"
                            :outlined="subsidiary.parent.focused"
                            prepend-inner-icon="mdi-playlist-edit"
                            class="text-body-2"
                          />
                        </td>
                        <td>
                          <div class="body-2">
                            <v-text-field
                              v-model.trim="subsidiary.endpoint.value"
                              solo
                              dense
                              flat
                              success
                              hide-details
                              @focus="
                                subsidiary.endpoint.focused = true;
                                subsidiary.endpoint.memory =
                                  subsidiary.endpoint.value;
                              "
                              @keyup.enter="
                                subsidiary.endpoint.focused = false;
                                Edit(
                                  s,
                                  subsidiary.PK,
                                  subsidiary.endpoint.header,
                                  subsidiary.endpoint.value,
                                  subsidiary.endpoint.memory,
                                  dataset.indexOf(item)
                                );
                              "
                              @blur="
                                subsidiary.endpoint.focused = false;
                                Edit(
                                  s,
                                  subsidiary.PK,
                                  subsidiary.endpoint.header,
                                  subsidiary.endpoint.value,
                                  subsidiary.endpoint.memory,
                                  dataset.indexOf(item)
                                );
                              "
                              :loading="subsidiary.endpoint.loading"
                              :outlined="subsidiary.endpoint.focused"
                              prefix="Endpoint: "
                              prepend-inner-icon="mdi-playlist-edit"
                              class="text-body-2"
                            />
                          </div>
                          <div class="body-2">
                            <v-text-field
                              v-model.trim="subsidiary.purpose.value"
                              solo
                              dense
                              flat
                              success
                              hide-details
                              @focus="
                                subsidiary.purpose.focused = true;
                                subsidiary.purpose.memory =
                                  subsidiary.purpose.value;
                              "
                              @keyup.enter="
                                subsidiary.purpose.focused = false;
                                Edit(
                                  s,
                                  subsidiary.PK,
                                  subsidiary.purpose.header,
                                  subsidiary.purpose.value,
                                  subsidiary.purpose.memory,
                                  dataset.indexOf(item)
                                );
                              "
                              @blur="
                                subsidiary.purpose.focused = false;
                                Edit(
                                  s,
                                  subsidiary.PK,
                                  subsidiary.purpose.header,
                                  subsidiary.purpose.value,
                                  subsidiary.purpose.memory,
                                  dataset.indexOf(item)
                                );
                              "
                              :loading="subsidiary.purpose.loading"
                              :outlined="subsidiary.purpose.focused"
                              prefix="Purpose: "
                              prepend-inner-icon="mdi-playlist-edit"
                              class="text-body-2"
                            />
                          </div>
                        </td>
                        <td>
                          <v-switch
                            v-model="subsidiary.visible.value"
                            dense
                            @focus="
                              subsidiary.visible.memory =
                                subsidiary.visible.value
                            "
                            @change="
                              Edit(
                                s,
                                subsidiary.PK,
                                subsidiary.visible.header,
                                subsidiary.visible.value,
                                subsidiary.visible.memory,
                                dataset.indexOf(item)
                              )
                            "
                          />
                        </td>
                        <td>
                          <v-btn
                            fab
                            text
                            small
                            @click="
                              Delete(s, subsidiary.PK, dataset.indexOf(item))
                            "
                          >
                            <v-icon color="error">mdi-delete</v-icon>
                          </v-btn>
                        </td>
                      </tr>
                    </tbody>
                  </template>
                </v-simple-table>
              </td>
            </template>
          </v-data-table>
        </v-col>
      </v-row>
    </v-card>
    <Alert ref="Alert" />
    <Confirm ref="Confirm" @confirm="Confirm" @cancel="Confirm" />
    <Snackbar ref="Snackbar" />
    <CreateAPI ref="CreateAPI" @GetAPIs="GetAPIs" />
  </v-main>
</template>

<script>
import Drawer from "@/components/Drawer";
import Snackbar from "@/components/Snackbar";
import CreateAPI from "@/components/Dialogs/CreateAPI";
export default {
  components: {
    Drawer,
    Snackbar,
    CreateAPI,
  },
  data() {
    return {
      darkUI: false,
      loading: false,
      hot: null,
      headers: [],
      dataset: [],
      expanded: [],
    };
  },
  mounted() {
    this.GetAPIs();
  },
  methods: {
    GetAPIs() {
      this.loading = true;
      this.promiseFetch(this.$store.getters.fetchTimeout)(
        fetch(this.$store.getters.endpoint + "APIs/GetAPIs.php", {
          method: "post",
          body: JSON.stringify({
            token: "",
          }),
        })
      )
        .then((response) => {
          if (response.status > 200) {
            this.$refs.Snackbar.Snackify({
              visible: true,
              content:
                "We were unable to retrieve some information. This might affect your application. Kindly refresh to retry.",
              color: "error",
              timeout: 10000,
            });
          } else {
            response.text().then((res) => {
              this.headers = JSON.parse(res).headers;
              this.dataset = this.expanded = JSON.parse(res).dataset;
            });
          }
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
    Edit(index, key, col, val, old, parent) {
      if (val !== old || !val.trim()) {
        parent
          ? (this.dataset[parent].subsidiaries[index][col].loading = true)
          : (this.dataset[index][col].loading = true);
        this.promiseFetch(this.$store.getters.fetchTimeout)(
          fetch(this.$store.getters.endpoint + "APIs/Edit.php", {
            method: "post",
            body: JSON.stringify({
              key,
              col,
              val,
            }),
          })
        )
          .then((response) => {
            response.text().then(() => {
              if (response.status > 200) {
                this.$refs.Alert.Alertify({
                  visible: true,
                  content: "Error while updating terms. Kindly try again",
                  title: "Error",
                  icon: "mdi-progress-alert",
                  color: "error",
                });
              } else {
                this.$refs.Snackbar.Snackify({
                  visible: true,
                  content: "Changes saved!",
                  color: "primary",
                  timeout: 1000,
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
            parent
              ? (this.dataset[parent].subsidiaries[index][col].loading = false)
              : (this.dataset[index][col].loading = false);
          });
      }
    },
    Delete(index, key, parent) {
      this.Confirm({
        task: "show",
        operation: "delete",
        color: "error",
        icon: "mdi-delete-variant",
        metadata: {
          index,
          key,
          parent,
        },
        content: "Proceed to delete this API?",
        title: "Delete API",
      });
    },
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
          let index = metadata.index;
          let key = metadata.key;
          let parent = metadata.parent;

          this.promiseFetch(this.$store.getters.fetchTimeout)(
            fetch(this.$store.getters.endpoint + "APIs/Delete.php", {
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
                    content: "Error while deleting API. Kindly try again",
                    title: "Error",
                    icon: "mdi-progress-alert",
                    color: "error",
                  });
                } else {
                  parent
                    ? this.dataset[parent].subsidiaries.splice(index, 1)
                    : this.dataset.splice(index, 1);

                  this.$refs.Snackbar.Snackify({
                    visible: true,
                    content: "API deleted!",
                    color: "primary",
                    timeout: 1000,
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
  },
};
</script>

<style>
.v-data-table
  > .v-data-table__wrapper
  tbody
  tr.v-data-table__expanded__content {
  box-shadow: unset !important;
}
.theme--light.v-data-table
  > .v-data-table__wrapper
  > table
  > tbody
  > tr:not(:last-child)
  > td:last-child,
.theme--light.v-data-table
  > .v-data-table__wrapper
  > table
  > tbody
  > tr:not(:last-child)
  > th:last-child {
  border-bottom: unset !important;
}
/* .theme--light.v-data-table
  > .v-data-table__wrapper
  > table
  > tbody
  > tr:not(:last-child)
  > td:not(.v-data-table__mobile-row),
.theme--light.v-data-table
  > .v-data-table__wrapper
  > table
  > tbody
  > tr:not(:last-child)
  > th:not(.v-data-table__mobile-row) {
  border-bottom: unset !important;
} */

thead {
  background: #e2e2e2;
}
tr:hover {
  background-color: transparent !important;
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
.v-stepper,
.v-stepper__header {
  box-shadow: unset;
}
</style>
