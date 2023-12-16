<template>
  <v-main>
    <Appbar />
    <Drawer />
    <v-container fluid :class="$vuetify.breakpoint.smAndUp && 'px-12 pt-6'">
      <div class="ml-4 py-3">
        <div class="text-h6 mb-4">
          Locations in {{ store.name }}
          <v-chip
            small
            text-color="white"
            color="primary"
            class="ml-2 subtitle-1"
            ><span>{{ totalCount }}</span></v-chip
          >
        </div>
        <v-row>
          <v-btn
            rounded
            color="grey"
            text
            class="mr-6"
            @click="$refs.CreateLocation.visible = true"
          >
            <v-icon left>mdi-shape-polygon-plus</v-icon>ADD LOCATION
          </v-btn>
          <v-btn
            rounded
            color="grey"
            text
            class="mr-6"
            @click="
              $refs.TabularLocationView.locations = dataset;
              $refs.TabularLocationView.visible = true;
            "
          >
            <v-icon left>mdi-file-table-outline</v-icon>VIEW &amp; EXPORT
            LOCATIONS
          </v-btn>
          <v-btn
            rounded
            color="grey"
            text
            class="mr-6"
            :to="`/Stores/${$route.params.id}/PrintLocations`"
          >
            <v-icon left>mdi-printer</v-icon>PRINT LOCATIONS
          </v-btn>
          <v-btn
            :to="`/Stores/${$route.params.id}/Scans`"
            rounded
            color="grey"
            text
          >
            <v-icon left>mdi-barcode</v-icon>SCANS
          </v-btn>
          <!-- <v-btn
            rounded
            :color="autorefresh ? 'grey' : 'primary'"
            :text="autorefresh"
            @click="autorefresh = !autorefresh"
          >
            <v-icon left>mdi-refresh</v-icon>AUTO-REFRESH
          </v-btn> -->
        </v-row>
        <v-row>
          <v-col cols="12" md="4" lg="3">
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
              class="mb-2 ml-n2"
              @keyup.enter="SearchLocations(true)"
              @click:clear="SearchLocations(false)"
            />
          </v-col>
        </v-row>
        <v-row no-gutters align="center">
          <span class="grey--text">Locations per page: </span>
          <v-menu offset-y>
            <template v-slot:activator="{ on }">
              <v-btn dark text color="primary" class="ml-2" v-on="on">
                {{ itemsPerPage }}
                <v-icon>mdi-chevron-down</v-icon>
              </v-btn>
            </template>
            <v-list>
              <v-list-item
                v-for="(number, index) in itemsPerPageArray"
                :key="index"
                @click="updateItemsPerPage(number)"
              >
                <v-list-item-title>{{ number }}</v-list-item-title>
              </v-list-item>
            </v-list>
          </v-menu>

          <span class="grey--text">Order by: </span>
          <v-menu offset-y>
            <template v-slot:activator="{ on }">
              <v-btn dark text color="primary" class="ml-2" v-on="on">
                {{
                  LocalStorage.LocationsOrder
                    ? JSON.parse(LocalStorage.LocationsOrder).key
                    : "Latest"
                }}
                <v-icon>mdi-chevron-down</v-icon>
              </v-btn>
            </template>
            <v-list>
              <v-list-item
                v-for="order in [
                  { key: 'Latest', value: 'id' },
                  { key: 'Most Discrepancies', value: 'discrepancy' },
                ]"
                :key="order.value"
                @click="
                  LocalStorage.LocationsOrder = JSON.stringify(order);
                  sortBy = order.value;
                  GetLocations();
                "
              >
                <v-list-item-title>{{ order.key }}</v-list-item-title>
              </v-list-item>
            </v-list>
          </v-menu>
        </v-row>

        <v-row>
          <v-col v-for="(entity, i) in entities" :key="i" cols="12" md="2">
            <v-card
              :dark="entity.dark"
              :outlined="!entity.dark"
              hover
              class="mb-2 px-6 py-1 rounded-xl text-left"
              :class="entity.class"
              @click="entity.onPress"
            >
              <div
                style="
                  margin: 10px;
                  position: absolute;
                  background-color: #fff;
                  border-radius: 30px;
                  height: 100px;
                  top: 0;
                  left: 0;
                  width: 160px;
                  opacity: 0;
                "
              />
              <div class="text-subtitle-2 mt-2">
                {{ entity.name }}
              </div>
              <div class="text-h6 font-weight-thin mt-n1 mb-1">
                <v-row no-gutters>
                  <v-col cols="7">
                    <span class="font-weight-bold">{{ entity.count }}</span>
                  </v-col>
                </v-row>
              </div>
            </v-card>
          </v-col>
        </v-row>
      </div>

      <v-row justify="center">
        <v-col cols="12" sm="12">
          <v-data-iterator
            :items="dataset"
            :items-per-page.sync="itemsPerPage"
            :page.sync="page"
            :loading="loading"
            :options.sync="options"
            :server-items-length="pageCount"
            hide-default-footer
          >
            <template v-slot:header>
              <v-row align="center">
                <v-pagination
                  v-model="page"
                  :length="pageCount"
                  :total-visible="11"
                  color="primary"
                  circle
                  @input="
                    page = $event;
                    GetLocations();
                  "
                  @next="GetLocations"
                  @previous="GetLocations"
                />
                <v-spacer />
                <v-col align="end" cols="12" sm="4">
                  <div>
                    <span class="mr-4 grey--text"
                      >Page {{ page }} of {{ pageCount }}</span
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
                  Mmmmh...looks like you don't have any locations currently.
                  Click
                  <v-btn
                    color="white"
                    small
                    outlined
                    rounded
                    class="mx-2 GradientButton"
                    style="margin-top: -2px"
                    @click="$refs.CreateLocation.visible = true"
                    >ADD LOCATION
                  </v-btn>
                  to add a new location.
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
                    class="rounded-b-xl elevation-3 px-5 pb-5 pt-4"
                    style="border-top: 3px solid #7b68ee"
                    :style="
                      item.discrepancy > 0
                        ? item.physicalCount > item.systemCount
                          ? 'background: linear-gradient(#ffffff, #fff9f9); border-top: 3px solid rgba(200, 120, 0, 1)'
                          : 'background: linear-gradient(#ffffff, #fff3f3); border-top: 3px solid rgba(255, 0, 0, 1)'
                        : item.edited === 1
                        ? 'background: linear-gradient(#ffffff, #f9fffc); border-top: 3px solid rgba(0, 100, 0, 1)'
                        : 'border-top: 3px dashed rgba(0, 0, 0, 0.6)'
                    "
                  >
                    <img
                      v-if="item.daysElapsed < 2"
                      style="
                        position: absolute;
                        left: -12px;
                        top: -12px;
                        z-index: 1;
                      "
                      :src="$store.getters.server + 'img/CornerRibbon.png'"
                      width="100px"
                      height="100px"
                    />
                    <v-row :id="`loc-${item.code}`" no-gutters justify="center">
                      <barcode
                        :value="item.code.split('-')[0]"
                        :text="item.code.split('-')[0]"
                        text-margin="5"
                        :width="1"
                        height="50"
                        text-position="top"
                      />
                    </v-row>

                    <v-row class="mt-n2 mb-n6">
                      <v-col cols="6">
                        <v-text-field
                          v-model="item.physicalCount"
                          v-mask="'####'"
                          :rules="[validators.NumericsRegex]"
                          label="Physical cnt"
                          outlined
                          clearable
                          dense
                          :hint="
                            item.physicalCount > item.systemCount
                              ? 'Greater -->'
                              : ''
                          "
                          persistent-hint
                          @focus="item.physicalCount = ''"
                          @change="
                            EditPhysicalCount(
                              i,
                              item.id,
                              Number(item.physicalCount)
                            )
                          "
                        />
                      </v-col>
                      <v-col cols="6" align="right">
                        <v-text-field
                          v-model="item.systemCount"
                          :rules="[validators.NumericsRegex]"
                          label="System cnt"
                          :hint="
                            item.systemCount > item.physicalCount
                              ? '<-- Greater'
                              : ''
                          "
                          outlined
                          dense
                          persistent-hint
                          disabled
                          readonly
                        />
                      </v-col>
                    </v-row>

                    <v-chip
                      v-if="item.discrepancy > 0"
                      :color="
                        item.physicalCount > item.systemCount
                          ? 'rgba(200, 120, 0, 0.9)'
                          : 'rgba(255, 0, 0, 0.6)'
                      "
                      class="mt-4 ml-3 px-5"
                      label
                      text-color="white"
                    >
                      <v-icon left size="18" style="margin-top: -2px">
                        mdi-close
                      </v-icon>
                      Discrepancy: {{ item.discrepancy }}
                    </v-chip>

                    <v-chip
                      v-if="item.discrepancy === 0 && item.edited === 1"
                      color="rgba(0, 100, 0, 0.6)"
                      class="ml-3 px-5"
                      label
                      text-color="white"
                    >
                      <v-icon left size="18" style="margin-top: -2px">
                        mdi-check
                      </v-icon>
                      Matching counts
                    </v-chip>

                    <div class="text-center caption mt-4">
                      <span class="font-weight-bold"
                        >Last Scanned On:&nbsp;</span
                      >
                      <span>{{ item.lastScannedOn }}</span>
                    </div>
                    <div class="text-center caption mb-2">
                      <span class="font-weight-bold"
                        >Last Scanned By:&nbsp;</span
                      >
                      <span class="text-decoration-underline">{{
                        item.lastScannedBy
                      }}</span>
                    </div>
                    <v-row>
                      <v-col cols="4" align="center">
                        <v-tooltip left>
                          <template v-slot:activator="{ on, attrs }">
                            <v-btn
                              v-bind="attrs"
                              v-on="on"
                              color="primary"
                              fab
                              small
                              text
                              @click="Print(item.code)"
                              ><v-icon>mdi-printer</v-icon></v-btn
                            >
                          </template>
                          <span>Print Barcode</span>
                        </v-tooltip>
                      </v-col>
                      <v-col cols="4" align="center">
                        <v-tooltip right>
                          <template v-slot:activator="{ on, attrs }">
                            <v-btn
                              v-bind="attrs"
                              v-on="on"
                              color="brown"
                              fab
                              small
                              text
                              @click="ShowProductsInLocation(item.code)"
                              ><v-icon>mdi-table-star</v-icon></v-btn
                            >
                          </template>
                          <span>Show Products</span>
                        </v-tooltip>
                      </v-col>
                      <v-col
                        v-if="item.discrepancy > 0 || item.edited === 1"
                        cols="4"
                        align="center"
                      >
                        <v-tooltip bottom>
                          <template v-slot:activator="{ on, attrs }">
                            <v-btn
                              v-bind="attrs"
                              v-on="on"
                              color="error"
                              fab
                              small
                              text
                              @click="
                                Confirm({
                                  task: 'show',
                                  operation: 'delete',
                                  color: 'warning',
                                  metadata: {
                                    index: i,
                                    key: item.id,
                                    code: item.code,
                                  },
                                  icon: 'mdi-history',
                                  content:
                                    'Proceed to reset location? Kindly note that this will reset the physical count and delete scans in this location.',
                                  title: 'Reset location',
                                })
                              "
                              ><v-icon>mdi-history</v-icon></v-btn
                            >
                          </template>
                          <span>Reset location</span>
                        </v-tooltip>
                      </v-col>
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
    <Confirm ref="Confirm" @confirm="Confirm" @cancel="Confirm" />
    <CreateLocation
      ref="CreateLocation"
      :store="store"
      @NewLocation="NewLocation"
    />
    <Scanner ref="Scanner" />
    <ProductsInLocation ref="ProductsInLocation" />
    <TabularLocationView :store="store" ref="TabularLocationView" />
    <router-view />
  </v-main>
</template>

<script>
import VueBarcode from "vue-barcode";
import Appbar from "@/components/Appbar";
import Drawer from "@/components/Drawer";
import Snackbar from "@/components/Snackbar";
import CreateLocation from "@/components/Dialogs/CreateLocation";
import Scanner from "@/components/Dialogs/Scanner";
import ProductsInLocation from "@/components/Dialogs/ProductsInLocation";
import TabularLocationView from "@/components/Dialogs/TabularLocationView";

export default {
  components: {
    barcode: VueBarcode,
    Appbar,
    Drawer,
    Snackbar,
    CreateLocation,
    Scanner,
    ProductsInLocation,
    TabularLocationView,
  },
  data() {
    return {
      loading: false,
      autorefresh: false,
      timer: 0,
      headers: [],
      dataset: [],
      options: {},
      totalCount: 0,
      itemsPerPageArray: [20, 25, 30],
      search: "",
      SearchResults: [],
      filter: "",
      page: 1,
      pageCount: 1,
      itemsPerPage: 20,
      FilterCount: 0,
      sortBy: "id",
      entities: [
        {
          name: "Total",
          image: "store.png",
          icon: "mdi-store",
          count: 0,
          dark: true,
          class: "Total",
          filters: [],
          onPress: () => {
            localStorage.setItem("filterLocationsBy", "Total");
            this.GetLocations([]);
          },
        },
        {
          name: "Counted",
          image: "store.png",
          icon: "mdi-store",
          count: 0,
          dark: true,
          class: "Counted",
          filters: [
            {
              operator: "custom",
              property: "counted",
              value: 1,
            },
          ],
          onPress: () => {
            localStorage.setItem("filterLocationsBy", "Counted");
            this.GetLocations([
              {
                operator: "custom",
                property: "counted",
                value: 1,
              },
            ]);
          },
        },
        {
          name: "Not Counted",
          image: "section.png",
          icon: "mdi-account-multiple",
          count: 0,
          dark: false,
          class: "NotCounted",
          filters: [
            {
              operator: "eq",
              property: "isVerified",
              value: 0,
            },
            {
              operator: "eq",
              property: "systemCount",
              value: 0,
            },
          ],
          onPress: () => {
            localStorage.setItem("filterLocationsBy", "Not Counted");
            this.GetLocations([
              {
                operator: "eq",
                property: "isVerified",
                value: 0,
              },
              {
                operator: "eq",
                property: "systemCount",
                value: 0,
              },
            ]);
          },
        },
        {
          name: "Discrepancies",
          image: "location.png",
          icon: "mdi-target-variant",
          count: 0,
          dark: true,
          class: "Discrepancies",
          filters: [
            {
              operator: "custom",
              property: "discrepancy",
              value: 0,
            },
          ],
          onPress: () => {
            localStorage.setItem("filterLocationsBy", "Discrepancies");
            this.GetLocations([
              {
                operator: "custom",
                property: "discrepancy",
                value: 0,
              },
            ]);
          },
        },
      ],
      store: {},
    };
  },
  mounted() {
    this.GetData();
    this.timer = setInterval(() => {
      this.GetData();
    }, 10000);
  },
  methods: {
    GetLocations(params) {
      this.$Progress.start();
      this.loading = true;

      const sort = localStorage.LocationsOrder
        ? JSON.parse(localStorage.LocationsOrder).value
        : this.sortBy;

      const statics = [
        {
          operator: "eq",
          property: "storeId",
          value: `${this.$route.params.id}`,
        },
      ];

      params && params.forEach((param) => statics.push(param));

      const filter = encodeURI(JSON.stringify(statics));

      const query = `${
        this.$store.getters.endpoint
      }Locations/?filter=${filter}&page=${this.page - 1}&limit=${
        this.itemsPerPage
      }&sort=${sort}&order=${0}`;

      this.promiseFetch(this.$store.getters.fetchTimeout)(
        fetch(query, {
          headers: {
            Accept: "application/json",
            Authorization: `Bearer ${this.$store.getters.token}`,
            "Content-Type": "application/json",
          },
        })
      )
        .then((response) =>
          response.json().then((res) => {
            this.dataset = res.data;
            this.store = res.store;
            this.totalCount = res.totalCount;
            this.pageCount = Math.ceil(this.totalCount / this.itemsPerPage);

            Object.entries(res.cumulativeCount).forEach((entry) => {
              const [key, value] = entry;

              this.entities.forEach((entity) => {
                if (entity.name === key) {
                  entity.count = value;
                }
              });
            });
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
        .finally(() => ((this.loading = false), this.$Progress.finish()));
    },
    SearchLocations(apply) {
      if (apply) {
        this.$Progress.start();
        this.loading = true;

        const sort = localStorage.LocationsOrder
          ? JSON.parse(localStorage.LocationsOrder).value
          : this.sortBy;

        const statics = [
          {
            operator: "rx",
            property: "code",
            value: this.search,
          },
          {
            operator: "eq",
            property: "storeId",
            value: `${this.$route.params.id}`,
          },
        ];

        const filter = encodeURI(JSON.stringify(statics));

        const query = `${
          this.$store.getters.endpoint
        }Locations/?filter=${filter}&page=${this.page - 1}&limit=${
          this.itemsPerPage
        }&sort=${sort}&order=${0}`;

        this.promiseFetch(this.$store.getters.fetchTimeout)(
          fetch(query, {
            headers: {
              Accept: "application/json",
              Authorization: `Bearer ${this.$store.getters.token}`,
              "Content-Type": "application/json",
            },
          })
        )
          .then((response) =>
            response.json().then((res) => {
              this.dataset = res.data;
              this.totalCount = res.totalCount;
              this.pageCount = Math.ceil(this.totalCount / this.itemsPerPage);
              //this.offset = (this.page - 1) * this.itemsPerPage;
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
          .finally(() => this.$Progress.finish());
      } else {
        this.GetLocations();
      }
    },
    ShowProductsInLocation(location) {
      this.$refs.ProductsInLocation.location = location;
      this.$refs.ProductsInLocation.visible = true;
    },
    GetData() {
      const name = localStorage.filterLocationsBy;
      !name
        ? this.GetLocations(this.entities[0].filters)
        : this.GetLocations(
            this.entities[this.entities.findIndex((item) => item.name === name)]
              .filters
          );
    },
    NewLocation(dataset) {
      this.GetLocations(this.entities[0].filters);

      this.$refs.Snackbar.Snackify({
        visible: true,
        content: `Created ${dataset.length} location${
          dataset.length > 1 ? "s" : ""
        }.`,
        color: "success",
        timeout: 3000,
      });
    },
    EditPhysicalCount(index, key, value) {
      if (value && value > 0) {
        this.promiseFetch(this.$store.getters.fetchTimeout)(
            fetch(
              `${this.$store.getters.endpoint}Locations/?ResetPhysicalCount`,
              {
                method: "PUT",
                headers: {
                  Accept: "application/json",
                  Authorization: `Bearer ${this.$store.getters.token}`,
                  "Content-Type": "application/json",
                },
                body: JSON.stringify({ id: key, physicalCount: value }),
              }
            )
          )
            .then((response) =>
              response.text().then(() => {
                if (response.status > 200) {
                  this.$refs.Alert.Alertify({
                    visible: true,
                    content: "Error while editing count. Kindly try again",
                    title: "Error",
                    icon: "mdi-progress-alert",
                    color: "error",
                  });
                } else {
                  this.dataset[index].edited = 1;
                  this.dataset[index].discrepancy = value;

                  this.$refs.Snackbar.Snackify({
                    visible: true,
                    content: "Edited physical count!",
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
            .finally(() => {
              this.loading = false;
            });
      }
    },
    nextPage() {
      if (this.page + 1 <= this.pageCount) {
        this.page++;
        this.GetLocations();
      }
    },
    formerPage() {
      if (this.page - 1 >= 1) {
        this.page--;
        this.GetLocations();
      }
    },
    updateItemsPerPage(number) {
      this.itemsPerPage = number;
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
          color: props.color || "info",
          truth: props.truth || "YES",
          falsy: props.falsy || "NO",
        });
      } else if (props.task) {
        this.$refs.Confirm.Confirmify({ visible: false });

        let metadata = this.$refs.Confirm.confirm.metadata;
        let index = metadata.index;
        let key = metadata.key;

        if (this.$refs.Confirm.confirm.operation === "delete") {
          this.promiseFetch(this.$store.getters.fetchTimeout)(
            fetch(`${this.$store.getters.endpoint}Locations/`, {
              method: "DELETE",
              headers: {
                Accept: "application/json",
                Authorization: `Bearer ${this.$store.getters.token}`,
                "Content-Type": "application/json",
              },
              body: JSON.stringify({
                id: key,
                code: metadata.code,
              }),
            })
          )
            .then((response) =>
              response.text().then(() => {
                if (response.status > 200) {
                  this.$refs.Alert.Alertify({
                    visible: true,
                    content: "Error resetting location. Kindly try again",
                    title: "Error",
                    icon: "mdi-progress-alert",
                    color: "error",
                  });
                } else {
                  this.dataset[index].PhysicalCount = 0;
                  this.dataset[index].SystemCount = 0;
                  this.dataset[index].edited = 0;
                  this.dataset[index].discrepancy = 0;

                  this.$refs.Snackbar.Snackify({
                    visible: true,
                    content: "Location has been reset.",
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
            );
        }
      } else if (!props.task) {
        this.$refs.Confirm.Confirmify({ visible: false });
        this.dataset[this.$refs.Confirm.confirm.metadata.index].PhysicalCount =
          "";
      }
    },
    async Print(barcode) {
      await this.$htmlToPaper(`loc-${barcode}`);
    },
    filterOnlyCapsText(value, search) {
      return (
        value != null &&
        search != null &&
        typeof value === "string" &&
        value.toString().toLocaleUpperCase().indexOf(search) !== -1
      );
    },
  },
  computed: {
    numberOfPages() {
      console.log(this.numberOfPages);
      return Math.ceil(this.dataset.length / this.itemsPerPage);
    },
    /* filteredKeys() {
      return this.headers.filter((key) => key !== `location`);
    }, */
  },
  destroyed() {
    clearInterval(this.timer);
  },
};
</script>

<style>
@media print {
  @page {
    size: auto;
  }
  body {
    margin-left: 3px;
  }
}
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
.PhysicalCount {
  background-image: linear-gradient(to top, #37ecba 0%, #72afd3 100%);
}
.SystemCount {
  background-image: linear-gradient(120deg, #fccb90 0%, #d57eeb 100%);
}
.Total {
  background-image: linear-gradient(to right, #8a5bea 0%, #45e0ff 100%);
}
.Counted {
  background-image: linear-gradient(to right, #79ea5b 0%, #56cda4 100%);
}
.NotCounted {
  background-image: linear-gradient(-20deg, #ffffff 0%, #ffffff 100%);
}
.Discrepancies {
  background-image: linear-gradient(314deg, #fdffaf 0%, #df0028 100%);
}
</style>
