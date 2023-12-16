<template>
  <v-dialog
    v-model="visible"
    max-width="400"
    transition="slide-x-reverse-transition"
  >
    <v-card flat tile color="grey lighten-1">
      <v-toolbar
        height="32"
        dark
        color="primary"
        :src="
          $store.getters.server + 'img/backdrops/' + $store.getters.background
        "
      >
        <template v-slot:img="{ props }">
          <v-img
            v-bind="props"
            gradient="to top right, rgba(57,181,74,.95), rgba(109, 149, 111, 0.9)"
          />
        </template>
        <v-icon size="20">mdi-cards-variant</v-icon>
        <span class="subtitle-2 ml-3">Test Credentials</span>
        <v-spacer />
        <v-icon size="20" class="ml-5" @click="visible = false"
          >mdi-close</v-icon
        >
      </v-toolbar>
      <div style="max-height: 75vh; overflow-y: auto; overflow-x: hidden">
        <v-simple-table dense class="CustomVerticalTable">
          <template v-slot:default>
            <thead>
              <tr>
                <th>Key</th>
                <th>Value</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(TestCredential, i) in TestCredentials" :key="i">
                <td class="caption font-weight-bold">{{ i }}</td>
                <td class="caption">{{ TestCredential }}</td>
              </tr>
            </tbody>
          </template>
        </v-simple-table>
      </div>
    </v-card>
  </v-dialog>
</template>

<script>
export default {
  props: ["TestCredentials"],
  data() {
    return {
      visible: false,
    };
  },
};
</script>
