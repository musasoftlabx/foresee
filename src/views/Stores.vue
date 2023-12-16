<template>
  <v-main>
    <Appbar />
    <Drawer />
    <v-container fluid>
      <div class="ml-4 py-3">
        <div class="text-h6">
          Stores<v-chip
            small
            text-color="white"
            color="primary"
            class="ml-2 mt-n1 subtitle-1"
            ><span>{{ dataset.length }}</span></v-chip
          >
        </div>
        <v-btn color="grey" text @click="ShowAddStore" class="mt-3 ml-n2">
          <v-icon left>mdi-plus</v-icon>ADD STORE</v-btn
        >
        <v-btn
          color="primary"
          text
          :disabled="!dataset.length"
          @click="$refs.SetWorkingStore.visible = true"
          class="mt-3"
        >
          <v-icon left>mdi-domain</v-icon>SET WORKING STORE</v-btn
        >
        <v-row>
          <v-col cols="12" md="4">
            <v-text-field
              ref="Search"
              v-model="search"
              clearable
              rounded
              flat
              dense
              filled
              hide-details
              prepend-inner-icon="mdi-table-search"
              label="Search"
              class="mt-5 ml-n2"
            />
          </v-col>
        </v-row>
      </div>
      <v-row no-gutters class="mt-3 px-4">
        <v-col cols="12">
          <v-data-iterator
            :items="dataset"
            :items-per-page.sync="itemsPerPage"
            :page.sync="page"
            :search="search"
            :loading="loading"
            hide-default-footer
          >
            <template v-slot:header>
              <v-row>
                <v-col align="end" cols="12" sm="4">
                  <div style="position: absolute; right: 50px; top: 115px">
                    <span class="mr-4 grey--text"
                      >Page {{ page }} of {{ numberOfPages }}</span
                    >
                    <v-btn
                      fab
                      dark
                      small
                      color="primary"
                      class="mr-1"
                      @click="formerPage"
                    >
                      <v-icon>mdi-chevron-left</v-icon>
                    </v-btn>
                    <v-btn
                      fab
                      dark
                      small
                      color="primary"
                      class="ml-1"
                      @click="nextPage"
                    >
                      <v-icon>mdi-chevron-right</v-icon>
                    </v-btn>
                  </div>
                </v-col>
              </v-row>
            </template>

            <template v-slot:no-data
              ><v-row no-gutters justify="center" class="mt-10">
                <v-alert
                  text
                  outlined
                  color="deep-orange"
                  icon="mdi-progress-alert"
                >
                  Mmmmh...looks like you don't have any stores currently. Click
                  <v-btn
                    color="white"
                    small
                    outlined
                    class="mx-2 GradientButton"
                    style="margin-top: -2px"
                    @click="ShowAddStore"
                    >ADD STORE
                  </v-btn>
                  to add a new store.
                </v-alert>
              </v-row>
            </template>

            <template v-slot:loading>
              <v-row>
                <v-col
                  v-for="i in 12"
                  :key="i"
                  cols="12"
                  sm="6"
                  md="4"
                  lg="3"
                  xl="2"
                >
                  <v-skeleton-loader type="image" />
                </v-col>
              </v-row>
            </template>

            <template v-slot:default="props">
              <v-row>
                <v-col
                  v-for="(item, i) in props.items"
                  :key="i"
                  cols="12"
                  sm="6"
                  md="4"
                  lg="3"
                  xl="2"
                >
                  <v-card
                    max-width="300"
                    class="rounded-xl elevation-3 px-5 pb-5 pt-6"
                    :style="
                      item.code === workingStore.code
                        ? 'background-image: linear-gradient(43deg, rgb(132 255 236) 0%, rgb(197 255 211) 100%)'
                        : 'background: linear-gradient(#ffffff, #fafafa)'
                    "
                  >
                    <v-chip
                      v-if="item.code === workingStore.code"
                      color="#99f8cb"
                      pill
                      small
                      style="position: absolute; right: 20px; top: -10px"
                      >Working store</v-chip
                    >
                    <v-row no-gutters>
                      <v-col align="center">
                        <div class="text-h6">
                          {{ item.code }}
                        </div>
                        <div class="text-subtitle-2 text-ellipsis">
                          {{ item.name }} ({{ item.client }})
                        </div>
                        <v-chip class="mt-1" outlined small>
                          {{ item.country }}
                        </v-chip>
                      </v-col>
                    </v-row>

                    <v-row justify="space-around">
                      <v-btn
                        :to="{
                          name: 'Store',
                          params: { id: item.id, code: item.code },
                        }"
                        color="primary"
                        rounded
                        dense
                        text
                        >VIEW</v-btn
                      >
                      <v-btn
                        color="error"
                        rounded
                        dense
                        text
                        @click="
                          Confirm({
                            task: 'show',
                            operation: 'delete',
                            color: 'warning',
                            metadata: { key: item.id, object: item },
                            icon: 'mdi-delete-variant',
                            content: `Proceed to remove ${item.name} store?`,
                            title: 'Remove store',
                          })
                        "
                      >
                        DELETE
                      </v-btn>
                    </v-row>
                  </v-card>
                </v-col>
              </v-row>
            </template>
          </v-data-iterator>
        </v-col>
      </v-row>
    </v-container>
    <Alert ref="Alert" />
    <Snackbar ref="Snackbar" />
    <AddStore ref="AddStore" @NewStore="NewStore" />
    <SetWorkingStore
      :dataset="dataset"
      @NewWorkingStore="NewWorkingStore"
      ref="SetWorkingStore"
    />
    <Confirm ref="Confirm" @confirm="Confirm" @cancel="Confirm" />
  </v-main>
</template>

<script>
import Appbar from "@/components/Appbar";
import Drawer from "@/components/Drawer";
import Snackbar from "@/components/Snackbar";
import AddStore from "@/components/Dialogs/AddStore";
import SetWorkingStore from "@/components/Dialogs/SetWorkingStore";

export default {
  components: {
    Appbar,
    Drawer,
    Snackbar,
    AddStore,
    SetWorkingStore,
  },
  data() {
    return {
      loading: false,
      dataset: [],
      workingStore: null,
      search: "",
      page: 1,
      itemsPerPage: 20,
      itemsPerPageArray: [20, 40, 60],
    };
  },
  created() {
    this.$Progress.start();
    this.loading = true;

    this.promiseFetch(this.$store.getters.fetchTimeout)(
      fetch(`${this.$store.getters.endpoint}Stores/`, {
        headers: {
          Accept: "application/json",
          Authorization: `Bearer ${this.$store.getters.token}`,
          "Content-Type": "application/json",
        },
      })
    )
      .then((response) => {
        response.json().then((data) => {
          this.dataset = data.stores;
          this.workingStore = this.$refs.SetWorkingStore.store = JSON.parse(
            data.workingStore.workingStore
          );
          this.$refs.SetWorkingStore.date.scanDate =
            data.workingStore.workingDate;
          this.$refs.SetWorkingStore.scanCharsLimit =
            data.workingStore.scanCharsLimit;
          this.$refs.SetWorkingStore.strict =
            data.workingStore.strict === 1 ? true : false;
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
        this.loading = false;
        this.$Progress.finish();
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
                code: object.code,
                name: object.name,
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
                  this.dataset.splice(this.dataset.indexOf(object), 1);

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
      this.dataset.unshift(data);

      this.$refs.Snackbar.Snackify({
        visible: true,
        content: `Added ${data.name} to list of stores.`,
        color: "success",
        timeout: 3000,
      });
    },
    NewWorkingStore(data) {
      this.workingStore = data;
    },
    nextPage() {
      if (this.page + 1 <= this.numberOfPages) this.page += 1;
    },
    formerPage() {
      if (this.page - 1 >= 1) this.page -= 1;
    },
  },
  computed: {
    numberOfPages() {
      return Math.ceil(this.dataset.length / this.itemsPerPage);
    },
  },
};
</script>
