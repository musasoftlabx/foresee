<template>
  <v-row
    v-if="NoContent.visible"
    align="center"
    justify="center"
    style="height: 75vh"
  >
    <v-col v-if="NoContent.loading" justify="center" align="center">
      <v-progress-circular :size="30" indeterminate color="primary" />
      <v-sheet class="body-2 mt-3" width="400"
        >Retrieving app information...</v-sheet
      >
    </v-col>
    <v-col v-else align="center">
      <v-icon size="60">mdi-lan-disconnect</v-icon>
      <v-sheet class="body-2 mt-3" width="400">
        Could not retieve data. Click
        <v-btn
          @click="
            $router.push('/Reloader');
            $router.back();
          "
          small
          outlined
          color="primary"
          class="ml-1 mr-2"
          >HERE</v-btn
        >to reload. If this persists, kindly send us a message via the webchat
      </v-sheet>
    </v-col>
  </v-row>
</template>

<script>
export default {
  props: ["NoContent"],
};
</script>
