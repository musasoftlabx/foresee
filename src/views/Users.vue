<template>
  <v-main>
    <v-card tile flat>
      <!--  <Appbar /> -->
      <Drawer />

      <v-container>
        <v-data-table
          :headers="headers"
          :items="dataset"
          :loading="loading"
          :items-per-page="itemsPerPage"
          :options.sync="options"
          :page.sync="page"
          @input="selected = $event"
          disable-sort
          class="CustomVerticalTable mx-5"
          style="background: transparent"
        >
          <template v-slot:top>
            <v-pagination
              v-model="page"
              :length="pageCount"
              :total-visible="11"
              circle
            >
            </v-pagination>
            <span v-if="searchResults" class="caption font-weight-bold">{{
              searchResults
            }}</span>
            <v-text-field
              v-model="search"
              ref="search"
              prepend-inner-icon="mdi-magnify"
              label="Search by Dealer Code, Dealer Name, Outlet Name, Outlet Town, Till Number, Till MSISDN, Status "
              hide-details
              outlined
              dense
              clearable
              height="40px"
              @keyup.enter="Querify(false)"
              @click:clear="Querify(true)"
              class="my-4"
            >
            </v-text-field>
          </template>
          <template v-slot:item.credentials="{ item }">
            <div class="body-2">
              <span class="font-weight-bold">Apigee Developer ID:</span
              >&nbsp;<span class="font-weight-bold">{{
                item.credentials.ApigeeDeveloperID
              }}</span>
            </div>
            <div class="caption">
              <span class="font-weight-bold">First Name:</span>&nbsp;<span>{{
                item.credentials.FirstName
              }}</span>
            </div>
            <div class="caption">
              <span class="font-weight-bold">Last Name:</span>&nbsp;<span>{{
                item.credentials.LastName
              }}</span>
            </div>
            <div class="caption">
              <span class="font-weight-bold">Email Address:</span>&nbsp;<span>{{
                item.credentials.EmailAddress
              }}</span>
            </div>
            <div class="caption">
              <span class="font-weight-bold">Username:</span>&nbsp;<span>{{
                item.credentials.username
              }}</span>
            </div>
            <div class="caption">
              <span class="font-weight-bold">Phone Number:</span>&nbsp;<span>{{
                item.credentials.PhoneNumber
              }}</span>
            </div>
          </template>

          <template v-slot:item.AccountType="{ item }">
            <span v-if="item.AccountType === 'Individual'">{{
              item.AccountType
            }}</span>
            <div v-else class="body-2">
              <span class="font-weight-bold">Company:</span>&nbsp;<span>{{
                item.CompanyName
              }}</span>
            </div>
          </template>

          <template v-slot:item.OperatingSystem="{ item }">
            <v-tooltip bottom>
              <template v-slot:activator="{ on, attrs }">
                <img
                  v-bind="attrs"
                  v-on="on"
                  :src="
                    item.OperatingSystem.includes('Windows')
                      ? $store.getters.server + 'img/icons/windows.png'
                      : item.OperatingSystem.includes('Mac')
                      ? $store.getters.server + 'img/icons/MacOS.png'
                      : item.OperatingSystem.includes('Linux')
                      ? $store.getters.server + 'img/icons/linux.png'
                      : item.OperatingSystem.includes('Android')
                      ? $store.getters.server + 'img/icons/android.png'
                      : item.OperatingSystem.includes('iOS')
                      ? $store.getters.server + 'img/icons/iOS.png'
                      : $store.getters.server + ''
                  "
                />
              </template>
              <span>{{ item.OperatingSystem }}</span>
            </v-tooltip>
          </template>

          <template v-slot:item.browser="{ item }">
            <v-tooltip bottom>
              <template v-slot:activator="{ on, attrs }">
                <img
                  v-bind="attrs"
                  v-on="on"
                  :src="
                    item.browser.includes('Chrome')
                      ? $store.getters.server + 'img/icons/chrome.png'
                      : item.browser.includes('Firefox')
                      ? $store.getters.server + 'img/icons/firefox.png'
                      : item.browser.includes('Edge')
                      ? $store.getters.server + 'img/icons/edge.png'
                      : item.browser.includes('explorer')
                      ? $store.getters.server + 'img/icons/InternetExplorer.png'
                      : item.browser.includes('Safari')
                      ? $store.getters.server + 'img/icons/safari.png'
                      : $store.getters.server + ''
                  "
                />
              </template>
              <span>{{ item.browser }}</span>
            </v-tooltip>
          </template>

          <!-- <template v-slot:item.operation="{ item }">
            <v-btn
              small
              :loading="item.loading"
              :disabled="item.loading"
              color="primary"
              @click="
                Confirm({
                  task: 'show',
                  operation: 'whitelist',
                  metadata: {
                    index: dataset.indexOf(item),
                    key: item.operation,
                  },
                  content: 'Proceed to whitelist ' + item.StoreName + '?',
                  title: 'Whitelist outlet?',
                })
              "
            >
              WHITELIST
            </v-btn>
          </template> -->
        </v-data-table>
      </v-container>
    </v-card>
  </v-main>
</template>

<script>
import Drawer from "../components/Drawer";

export default {
  components: {
    Drawer,
  },
  data() {
    return {
      darkUI: false,
      loading: false,
      statistics: {
        subAgents: null,
        assistantSubAgents: null,
      },
      headers: [],
      dataset: [],
      itemsPerPage: 20,
      options: {},
      page: 1,
      pageCount: 0,
      offset: 0,
      selected: [],
      search: null,
      searchResults: null,
    };
  },
  watch: {
    options() {
      if (!this.search) this.Datarize();
    },
  },
  /* mounted() {
    setTimeout(() => {
      (this.styleSelectedText = true), (this.cmOption.styleActiveLine = true);
    }, 1800);
  }, */
  methods: {
    Confirm() {},
    Querify(clear) {
      if (clear) this.search = null;
      this.searchResults = null;

      if (this.search) {
        let keyword = this.search;
        this.loading = true;
        let startTime = Date.now();

        this.$Progress.start();
        this.promiseFetch(this.$store.getters.fetchTimeout)(
          fetch(this.$store.getters.endpoint + "GetUsers/", {
            method: "post",
            body: JSON.stringify({
              operation: "querify",
              keyword,
            }),
          })
        )
          .then((response) => {
            if (response.status > 200) {
              this.$Progress.fail();
              this.$refs.Snackbar.Snackify({
                visible: true,
                content:
                  "We were unable to retrieve some information. This might affect your application. Kindly refresh to retry.",
                color: "error",
                timeout: 10000,
              });
            } else {
              response.text().then((res) => {
                let endTime = Date.now();
                let timeDiff = Math.floor((endTime - startTime) / 1000);
                let seconds = timeDiff == 1 ? " second" : " seconds";
                let data = JSON.parse(res).dataset;
                this.dataset = data;
                this.pageCount = Math.ceil(
                  data.length / this.options.itemsPerPage
                );
                this.searchResults =
                  "Found " + data.length + " results in " + timeDiff + seconds;
              });
            }
          })
          .catch(() => {
            this.$Progress.fail();
            this.$refs.Alert.Alertify({
              visible: true,
              content: this.$store.getters.fetchTimeoutError,
              title: "Connection Timeout",
              icon: "mdi-wifi-strength-1-alert",
              color: "error",
            });
          })
          .finally(() => {
            this.$Progress.finish();
          });
      } else {
        this.dataset = [];
        this.options.page = 1;
        this.Datarize();
      }
    },
    Datarize() {
      let limit = this.options.itemsPerPage;
      let offset = (this.options.page - 1) * this.options.itemsPerPage + 1;

      if (this.options.page == 1) {
        limit = this.options.itemsPerPage + 1;
        offset = 0;
      }

      this.$Progress.start();
      this.promiseFetch(this.$store.getters.fetchTimeout)(
        fetch(this.$store.getters.endpoint + "GetUsers/", {
          method: "post",
          body: JSON.stringify({
            operation: "datarize",
            limit,
            offset,
          }),
        })
      )
        .then((response) => {
          if (response.status > 200) {
            this.$Progress.fail();
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
              let str = JSON.stringify(this.dataset);
              JSON.parse(res).dataset.forEach((e) => {
                if (str.indexOf(JSON.stringify(e)) == -1) this.dataset.push(e);
              });
              this.pageCount = Math.ceil(
                JSON.parse(res).count / this.options.itemsPerPage
              );
            });
          }
        })
        .catch(() => {
          this.$Progress.fail();
          this.$refs.Alert.Alertify({
            visible: true,
            content: this.$store.getters.fetchTimeoutError,
            title: "Connection Timeout",
            icon: "mdi-wifi-strength-1-alert",
            color: "error",
          });
        })
        .finally(() => {
          this.$Progress.finish();
        });
    },
  },
};
</script>

<style>
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
