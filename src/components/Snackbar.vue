<template>
  <section>
    <v-snackbar
      v-model="snackbar.visible"
      :color="snackbar.color"
      :timeout="snackbar.timeout"
      :bottom="snackbar.bottom"
      :left="snackbar.left"
      :right="snackbar.right"
      :vertical="snackbar.vertical"
      :multi-line="snackbar.multiline"
    >
      <span :class="snackbar.textcolor + '--text'">{{ snackbar.content }}</span>
      <v-btn
        v-if="snackbar.action"
        text
        color="yellow"
        @click="snackbar.visible = false"
        >VIEW</v-btn
      >
      <v-progress-circular
        v-if="snackbar.loader"
        :size="20"
        indeterminate
        color="primary"
      />
    </v-snackbar>
  </section>
</template>

<script>
export default {
  data() {
    return {
      snackbar: {
        visible: false,
        content: null,
        route: null,
        color: undefined,
        textcolor: "white",
        loader: false,
        timeout: -1,
        action: false,
        right: true,
        left: false,
        bottom: true,
        vertical: false,
        multiline: false,
      },
    };
  },
  methods: {
    Snackify(props) {
      this.snackbar = {
        ...this.snackbar,
        ...props,
      };
    },
  },
};
</script>
