<template>
  <v-content>
    <v-card flat>
      <Appbar />
      <Drawer />
      <v-container></v-container>
    </v-card>
  </v-content>
</template>

<script>
import Appbar from "../components/Appbar";
import Drawer from "../components/Drawer";
export default {
  components: {
    Appbar,
    Drawer,
  },
};
</script>
