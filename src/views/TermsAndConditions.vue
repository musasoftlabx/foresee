<template>
  <v-main>
    <v-card tile flat>
      <Appbar />
      <Drawer />
      <v-container fluid>
        <affix
          class="sidebar-menu"
          relative-element-selector="#example-content"
          :offset="{ top: 40, bottom: 40 }"
        >
          <v-row align="center">
            <v-col>
              <div class="ml-4">
                <div class="text-h6 mb-n1">{{ $route.name }}</div>
                <small class="ml-1"
                  >Feel free to use CTRL + S to quick save</small
                >
              </div>
            </v-col>
            <v-col align="end" class="mr-5">
              <v-btn
                color="primary"
                outlined
                large
                :loading="loading"
                :disabled="loading"
                @click="EditTerms"
                >SAVE CHANGES</v-btn
              >
            </v-col>
          </v-row>
          <v-divider />
        </affix>

        <v-row id="relative" justify="center">
          <v-col cols="10">
            <jodit-editor
              v-model="content"
              :config="TextEditor.ConfigTopicEditor"
            />
          </v-col>
        </v-row>
      </v-container>
    </v-card>
    <Alert ref="Alert" />
    <Snackbar ref="Snackbar" />
  </v-main>
</template>

<script>
import "jodit/build/jodit.min.css";
import { JoditEditor } from "jodit-vue";
import Appbar from "@/components/Appbar";
import Drawer from "@/components/Drawer";
import Snackbar from "@/components/Snackbar";
export default {
  components: {
    Appbar,
    JoditEditor,
    Drawer,
    Snackbar,
  },
  data() {
    return {
      darkUI: false,
      loading: false,
      content: "",
      TextEditor: {
        ConfigTopicEditor: {
          theme: "light",
          toolbarButtonSize: "large",
          placeholder: "",
        },
      },
    };
  },
  created() {
    document.addEventListener(
      "keydown",
      (e) => {
        if (
          (window.navigator.platform.match("Mac") ? e.metaKey : e.ctrlKey) &&
          e.keyCode == 83
        ) {
          e.preventDefault();
          this.EditTerms();
        }
      },
      false
    );
  },
  mounted() {
    //this.$Progress.start();
    this.promiseFetch(this.$store.getters.fetchTimeout)(
      fetch(this.$store.getters.endpoint + "TermsAndConditions/", {
        method: "post",
        body: JSON.stringify({
          token: "",
        }),
      })
    )
      .then((response) => {
        if (response.status > 200) {
          //this.$Progress.fail();
          this.$refs.Snackbar.Snackify({
            visible: true,
            content:
              "We were unable to retrieve some information. This might affect your application. Kindly refresh to retry.",
            color: "error",
            timeout: 10000,
          });
        } else {
          response.text().then((res) => {
            this.content = JSON.parse(res);
          });
        }
      })
      .catch(() => {
        //this.$Progress.fail();
        this.$refs.Alert.Alertify({
          visible: true,
          content: this.$store.getters.fetchTimeoutError,
          title: "Connection Timeout",
          icon: "mdi-wifi-strength-1-alert",
          color: "error",
        });
      })
      .finally(() => {
        //this.$Progress.finish();
      });
  },
  methods: {
    EditTerms() {
      this.loading = true;
      this.promiseFetch(this.$store.getters.fetchTimeout)(
        fetch(this.$store.getters.endpoint + "TermsAndConditions/Edit/", {
          method: "post",
          body: JSON.stringify({
            //token: this.$store.getters.token,
            content: this.content,
          }),
        })
      )
        .then((response) => {
          response.text().then(() => {
            if (response.status > 200) {
              this.$refs.Alert.Alertify({
                visible: true,
                content: "Error while updating terms. Kindly try again",
                title: "Error",
                icon: "mdi-progress-alert",
                color: "error",
              });
            } else {
              this.$refs.Snackbar.Snackify({
                visible: true,
                content: "Terns and conditions have been edited succesfully",
                color: "primary",
                timeout: 5000,
              });
            }
          });
        })
        .catch(() => {
          this.$refs.Alert.Alertify({
            visible: true,
            content: this.$store.getters.fetchTimeoutError,
            title: "Connection Timeout",
            icon: "mdi-wifi-strength-1-alert",
            color: "error",
          });
        })
        .finally(() => {
          this.loading = false;
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
