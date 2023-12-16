<template>
  <section>
    <v-dialog v-model="alert.visible" persistent max-width="300">
      <v-card flat color="white" class="rounded-lg" style="overflow: hidden">
        <v-card
          dark
          color="transparent"
          flat
          class="text-center"
          width="250"
          style="
            position: absolute;
            z-index: 1;
            left: 50%;
            transform: translate(-50%, 20px);
          "
        >
          <v-icon x-large>{{ alert.icon }}</v-icon>
          <div class="text-h6 mt-1">{{ alert.title }}</div>
        </v-card>
        <div id="Alert-divider-top">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 100 100"
            preserveAspectRatio="none"
          >
            <defs>
              <linearGradient id="MyGradient">
                <stop offset="0%" :stop-color="alert.gradient.start"></stop>
                <stop offset="80%" :stop-color="alert.gradient.stop"></stop>
              </linearGradient>
            </defs>
            <path
              class="divider-fill"
              d="M0,33.886L13.281,0L62.225,67.779L88.288,61.436L100,33.886L100,100L0,100L0,33.886z"
              style="fill-opacity: 0.2"
            />
            <path
              class="divider-fill"
              d="M0,48.8L10.938,44.6L35.196,65.112L62.225,47L89.063,68.844L100,64.8L100,100L0,100L0,64.8z"
            />
          </svg>
        </div>
        <v-card flat color="transparent" style="z-index: 0" class="px-3">
          <v-card-text
            v-html="alert.content"
            class="black--text"
            style="padding-top: 170px"
          />
          <v-row no-gutters justify="end" class="mt-n3 mb-2">
            <v-btn text @click="alert.visible = false">OK</v-btn>
          </v-row>
        </v-card>
      </v-card>
    </v-dialog>
  </section>
</template>
<script>
export default {
  data() {
    return {
      alert: {
        visible: false,
        icon: "mdi-check-decagram",
        title: "Title goes here.",
        color: undefined,
        gradient: {
          start: "#00dbde",
          stop: "#fc00ff",
        },
        content: "Content goes here. ",
      },
    };
  },
  watch: {
    "alert.color"(v) {
      switch (v) {
        case "success":
          this.alert.gradient.start = "#43e97b";
          this.alert.gradient.stop = "#38f9d7";
          break;
        case "info":
          this.alert.gradient.start = "#4facfe";
          this.alert.gradient.stop = "#00f2fe";
          break;
        case "warning":
          this.alert.gradient.start = "#f9d423";
          this.alert.gradient.stop = "#ff4e50";
          break;
        case "error":
          this.alert.gradient.start = "#ff0844";
          this.alert.gradient.stop = "#ffb199";
          break;
      }
    },
  },
  methods: {
    Alertify(props) {
      this.alert = {
        ...this.alert,
        ...props,
      };
    },
  },
};
</script>

<style>
#Alert-divider-top {
  background-color: white;
  overflow: hidden;
  position: absolute;
  top: 0;
  width: 100%;
  height: 300px;
  line-height: 0;
  left: 0;
  transform: scale(-1, -1);
}
#Alert-divider-top svg {
  display: block;
  width: 100%;
  height: 100%;
  position: relative;
  left: 50%;
  transform: translateX(-50%);
}
#Alert-divider-top .divider-fill {
  transform-origin: bottom;
  transform: rotateY(0deg);
  fill: url("#MyGradient");
}
</style>
