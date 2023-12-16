<template>
  <v-main>
    <Appbar />
    <NoContent v-if="NoContent.visible" :NoContent="NoContent" />
    <v-container
      v-else
      fluid
      :class="$vuetify.breakpoint.smAndUp && 'px-12 pt-6'"
    >
      <v-row justify="center">
        <v-col cols="12" sm="12">
          <v-data-iterator
            :items="dataset"
            :items-per-page.sync="itemsPerPage"
            :page.sync="page"
            item-key="barcode"
            :search="search"
            hide-default-footer
            no-data-text=""
            :server-items-length="totalCount"
          >
            <template v-slot:header>
              <div v-if="dataset.length >= 6">
                <v-row>
                  <v-col cols="12" sm="4">
                    <v-text-field
                      ref="Search"
                      v-model="filter"
                      clearable
                      rounded
                      flat
                      filled
                      hide-details
                      prepend-inner-icon="mdi-table-search"
                      label="Search"
                      class="my-3 ml-n2"
                    >
                      <template v-slot:append>
                        <v-btn
                          color="primary"
                          small
                          rounded
                          outlined
                          style="margin-top: -2px"
                          @click="$refs.Search.focus()"
                        >
                          <v-icon size="20" left>mdi-barcode</v-icon> SEARCH
                        </v-btn>
                      </template>
                    </v-text-field>
                  </v-col>
                  <v-spacer />
                  <v-col align="end" cols="12" sm="4">
                    <div style="position: absolute; right: 50px; top: 100px">
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
              </div>
              <h3 class="my-2">
                All Locations<v-chip
                  small
                  text-color="white"
                  color="primary"
                  class="ml-2 subtitle-1"
                  ><span>{{ totalCount }}</span></v-chip
                >
              </h3>

              <v-btn
                outlined
                text
                class="mt-3 mr-6"
                @click="$refs.CreateLocation.visible = true"
              >
                <v-icon left>mdi-shape-polygon-plus</v-icon>ADD LOCATION
              </v-btn>
              <v-btn
                outlined
                text
                class="mt-3 mr-6"
                @click="
                  $refs.TabularLocationView.locations = dataset;
                  $refs.TabularLocationView.visible = true;
                "
              >
                <v-icon left>mdi-file-table-outline</v-icon>VIEW &amp; EXPORT
                LOCATIONS
              </v-btn>
              <v-btn to="/PrintLocations" outlined text class="mt-3">
                <v-icon left>mdi-printer</v-icon>PRINT LOCATIONS
              </v-btn>

              <v-row class="mt-4 mb-8">
                <v-pagination
                  v-model="page"
                  :length="pageCount"
                  :total-visible="11"
                  color="primary"
                  circle
                />
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
                    color="primary"
                    small
                    outlined
                    class="mx-2 GradientButton"
                    style="margin-top: -2px"
                    @click="$refs.CreateLocation.visible = true"
                    >ADD LOCATION
                  </v-btn>
                  to add a new location.
                </v-alert>
              </v-row>
            </template>

            <template v-slot:default="props">
              <section v-if="skeleton">
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
                    <v-skeleton-loader type="image, article" />
                  </v-col>
                </v-row>
              </section>
              <section>
                <v-row v-masonry>
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
                      class="rounded-b-xl elevation-3 px-5 pb-5 pt-6"
                      style="border-top: 3px solid #7b68ee"
                      :style="
                        item.discrepancy > 0
                          ? item.PhysicalCount > item.SystemCount
                            ? 'background: linear-gradient(#ffffff, #fff9f9); border-top: 3px solid rgba(200, 120, 0, 1)'
                            : 'background: linear-gradient(#ffffff, #fff3f3); border-top: 3px solid rgba(255, 0, 0, 1)'
                          : item.edited === 1
                          ? 'background: linear-gradient(#ffffff, #f9fffc); border-top: 3px solid rgba(0, 100, 0, 1)'
                          : 'border-top: 3px dashed rgba(0, 0, 0, 0.6)'
                      "
                    >
                      <img
                        v-if="item.DaysElapsed < 2"
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
                      <v-row
                        :id="`loc-${item.barcode}`"
                        no-gutters
                        justify="center"
                      >
                        <barcode
                          :value="item.barcode"
                          text-margin="5"
                          width="1"
                          height="50"
                          text-position="top"
                        />
                      </v-row>

                      <div class="text-caption" style="margin-bottom: 10px">
                        Located in {{ item.country }} at {{ item.store }} under
                        the {{ item.section }} section
                      </div>

                      <v-row class="mt-3 mb-n6">
                        <v-col cols="6">
                          <v-text-field
                            v-model="item.PhysicalCount"
                            v-mask="'####'"
                            :rules="[validators.NumericsRegex]"
                            label="Physical cnt"
                            outlined
                            clearable
                            :hint="
                              item.PhysicalCount > item.SystemCount
                                ? 'Greater -->'
                                : ''
                            "
                            persistent-hint
                            @focus="item.PhysicalCount = ''"
                            :disabled="item.edited > 0"
                            :readonly="item.edited > 0"
                            @change="
                              EditPhysicalCount(
                                i,
                                item.id,
                                Number(item.PhysicalCount)
                              )
                            "
                          />
                        </v-col>
                        <v-col cols="6" align="right">
                          <v-text-field
                            v-model="item.SystemCount"
                            :rules="[validators.NumericsRegex]"
                            label="System cnt"
                            :hint="
                              item.SystemCount > item.PhysicalCount
                                ? '<-- Greater'
                                : ''
                            "
                            outlined
                            persistent-hint
                            disabled
                            readonly
                          />
                        </v-col>
                      </v-row>

                      <v-chip
                        v-if="item.discrepancy > 0"
                        :color="
                          item.PhysicalCount > item.SystemCount
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
                        <span class="font-weight-bold">Modified On:&nbsp;</span>
                        <span>{{ item.ModifiedOn }}</span>
                      </div>
                      <div class="text-center caption mb-2">
                        <span class="font-weight-bold">Modified By:&nbsp;</span>
                        <span class="text-decoration-underline">{{
                          item.ModifiedBy
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
                                @click="Print(item.barcode)"
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
                                @click="ShowProductsInLocation(item.barcode)"
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
                                      LocationBarcode: item.barcode,
                                      PhysicalCount: item.PhysicalCount,
                                      SystemCount: item.SystemCount,
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
              </section>
            </template>
          </v-data-iterator>
        </v-col>
      </v-row>
    </v-container>
    <Drawer />
    <Alert ref="Alert" />
    <Snackbar ref="Snackbar" />
    <Confirm ref="Confirm" @confirm="Confirm" @cancel="Confirm" />
    <CreateLocation ref="CreateLocation" @NewLocation="NewLocation" />
    <Scanner ref="Scanner" />
    <ProductsInLocation ref="ProductsInLocation" />
    <TabularLocationView ref="TabularLocationView" />
  </v-main>
</template>

<script>
import VueBarcode from "vue-barcode";
import Appbar from "@/components/Appbar";
import Drawer from "@/components/Drawer";
import Snackbar from "@/components/Snackbar";
import NoContent from "@/components/NoContent";
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
    NoContent,
    CreateLocation,
    Scanner,
    ProductsInLocation,
    TabularLocationView,
  },
  data() {
    return {
      NoContent: {
        visible: true,
        loading: true,
      },
      apps: true,
      skeleton: false,
      headers: [],
      dataset: [],
      totalCount: 0,
      itemsPerPageArray: [12, 24, 36],
      search: "",
      SearchResults: [],
      filter: "",
      sortDesc: false,
      page: 0,
      pageCount: 0,
      offset: 0,
      itemsPerPage: 20,
      sortBy: "id",
    };
  },
  mounted() {
    this.GetLocations();
  },
  watch: {
    filter() {
      this.SearchLocations(
        JSON.stringify([
          { operator: "like", value: this.filter, property: "barcode" },
        ])
      );
    },
  },
  methods: {
    GetLocations() {
      this.$Progress.start();
      this.skeleton = true;

      const sort = localStorage.LocationsOrder
        ? JSON.parse(localStorage.LocationsOrder).value
        : this.sortBy;

      const query = `${this.$store.getters.endpoint}Locations/?page=${this.page}&limit=${this.itemsPerPage}&start=${this.offset}&sort=${sort}`;

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
            setTimeout(() => (this.skeleton = false), 1000);
            this.NoContent.visible = false;

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
    },
    SearchLocations(filtrate) {
      this.$Progress.start();
      this.skeleton = true;

      this.promiseFetch(this.$store.getters.fetchTimeout)(
        fetch(
          `${this.$store.getters.endpoint}Locations/?page=${this.page}&limit=${this.itemsPerPage}&start=${this.offset}&sort=${this.sortBy}&filter=${filtrate}`,
          {
            headers: {
              Accept: "application/json",
              Authorization: `Bearer ${this.$store.getters.token}`,
              "Content-Type": "application/json",
            },
          }
        )
      )
        .then((response) =>
          response.json().then((res) => {
            this.dataset = res.data;
            this.totalCount = res.totalCount;
            setTimeout(() => (this.skeleton = false), 1000);
            this.NoContent.visible = false;

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
    },
    ShowScanner(LocationBarcode, LocationPK) {
      this.$refs.Scanner.LocationPK = LocationPK;
      this.$refs.Scanner.LocationBarcode = LocationBarcode;
      this.$refs.Scanner.visible = true;
      setTimeout(() => this.$refs.Scanner.$refs.ProductBarcode.focus(), 100);
    },
    ShowProductsInLocation(location) {
      this.$refs.ProductsInLocation.location = location;
      this.$refs.ProductsInLocation.visible = true;
    },
    NewLocation(dataset) {
      dataset.forEach((item) => this.dataset.unshift(item));
    },
    EditPhysicalCount(index, key, value) {
      if (value && value > 0) {
        if (this.dataset[index].edited === 0) {
          this.Confirm({
            task: "show",
            operation: "edit",
            color: "info",
            metadata: {
              index,
              key,
              value,
            },
            icon: "mdi-playlist-edit",
            content: `Are you sure you want to set <b class="info--text">${value}</b> as the physical count? Kindly note that once edited, you won't able to edit again.`,
            title: "Edit Physical Count",
          });
        } else {
          this.$refs.Alert.Alertify({
            visible: true,
            content:
              "This location has already been counted. Changes cannot be made.",
            title: "Error",
            icon: "mdi-progress-alert",
            color: "error",
          });
        }
      }
    },
    nextPage() {
      if (this.page + 1 <= this.numberOfPages) this.page += 1;
      this.offset = this.page++ * this.itemsPerPage;
      this.GetLocations();
    },
    formerPage() {
      if (this.page - 1 >= 1) this.page -= 1;
      this.offset = this.page-- * this.itemsPerPage;
      this.GetLocations();
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
        let value = metadata.value;

        if (this.$refs.Confirm.confirm.operation === "edit") {
          this.promiseFetch(this.$store.getters.fetchTimeout)(
            fetch(`${this.$store.getters.endpoint}Locations/`, {
              method: "PUT",
              headers: {
                Accept: "application/json",
                Authorization: `Bearer ${this.$store.getters.token}`,
                "Content-Type": "application/json",
              },
              body: JSON.stringify({
                key,
                value,
              }),
            })
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
        } else if (this.$refs.Confirm.confirm.operation === "delete") {
          this.promiseFetch(this.$store.getters.fetchTimeout)(
            fetch(`${this.$store.getters.endpoint}Locations/`, {
              method: "DELETE",
              headers: {
                Accept: "application/json",
                Authorization: `Bearer ${this.$store.getters.token}`,
                "Content-Type": "application/json",
              },
              body: JSON.stringify({
                key,
                LocationBarcode: metadata.LocationBarcode,
                PhysicalCount: metadata.PhysicalCount,
                SystemCount: metadata.SystemCount,
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
      return Math.ceil(this.dataset.length / this.itemsPerPage);
    },
    /* filteredKeys() {
      return this.headers.filter((key) => key !== `location`);
    }, */
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
</style>
