<template>
  <v-main>
    <v-navigation-drawer
      v-model="drawer.visible"
      :mini-variant="MiniVariant"
      :expand-on-hover="false"
      :permanent="permanent"
      app
      clipped
      fixed
      color="white"
      width="200px"
      style="height: calc(100vh - 60px)"
    >
      <v-list-item>
        <v-list-item-content>
          <v-row no-gutters class="py-3">
            <v-col>
              <v-icon size="24" @click="MiniVariant = !MiniVariant"
                >mdi-menu</v-icon
              >
              <div
                v-if="!MiniVariant"
                class="text-h5 grey--text font-weight-bold mb-1 mt-4"
              >
                4C-Tech
              </div>
            </v-col>
          </v-row>
        </v-list-item-content>
      </v-list-item>
      <v-list nav shaped>
        <v-list-item-group v-model="currentRoute" active-class="DrawerActive">
          <v-list-item
            v-for="parent in ComputeAccessMatrix"
            :key="parent.text"
            @click="$router.push('/' + parent.route)"
          >
            <v-list-item-action>
              <v-icon>{{ parent.icon }}</v-icon>
            </v-list-item-action>
            <v-list-item-content>
              <v-list-item-title class="text-subtitle-1">{{
                parent.text
              }}</v-list-item-title>
            </v-list-item-content>
            <v-chip v-if="parent.new" color="primary" x-small>new</v-chip>
          </v-list-item>
        </v-list-item-group>
      </v-list>
    </v-navigation-drawer>
  </v-main>
</template>

<script>
export default {
  data() {
    return {
      currentRoute: this.$router.name,
      MiniVariant: this.$vuetify.breakpoint.smAndDown ? true : false,
      permanent: true,
    };
  },
  watch: {
    "$vuetify.breakpoint.smAndDown"(n) {
      n
        ? ((this.MiniVariant = true), (this.permanent = true))
        : (this.MiniVariant = false);
    },
  },
  /* mounted() {
    this.drawer.loading = true;
    this.promiseFetch(this.$store.getters.fetchTimeout)(
      fetch(this.$store.getters.endpoint + "NavigationDrawer/", {
        method: "post",
        //body: localStorage.token,
      })
    ).then((response) => {
      response.text().then((res) => {
        this.drawer.loading = false;
        this.drawer.parents = JSON.parse(res);
        localStorage.drawerize = btoa(res);
      });
    });
  }, */
};
</script>

<style>
.v-navigation-drawer__border {
  background-color: #fff !important;
}
.DrawerActive {
  background-image: linear-gradient(to right, #f83600 0%, #f9d423 100%);
  color: white !important;
}
</style>
