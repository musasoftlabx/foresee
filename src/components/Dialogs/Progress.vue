<template>
  <section>
    <v-dialog v-model="progress.visible" persistent width="300">
      <v-card :color="progress.backgroundColor" dark class="pt-3">
        <v-card-text>
          {{ progress.content }}
          <v-progress-linear
            indeterminate
            :color="progress.progressColor"
            class="my-2"
          />
        </v-card-text>
      </v-card>
    </v-dialog>
  </section>
</template>

<script>
export default {
  data() {
    return {
      progress: {
        visible: false,
        content: null,
        backgroundColor: "primary",
        progressColor: "yellow",
      },
    };
  },
  methods: {
    Progressify(props) {
      this.progress = {
        ...this.progress,
        ...props,
      };
    },
  },
};
</script>
