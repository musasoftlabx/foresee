const { defineConfig } = require("@vue/cli-service");
module.exports = defineConfig({
  transpileDependencies: true,
  lintOnSave: false,
  productionSourceMap: false,
  //publicPath: process.env.NODE_ENV === "production" ? "" : "/",
});
