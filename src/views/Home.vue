<template>
  <v-main>
    <v-card tile flat>
      <Appbar />
      <Drawer />
      <v-container fluid>
        <v-row>
          <v-col cols="12" md="8">
            <v-card flat color="transparent" class="py-8 rounded-xl">
              <v-row no-gutters>
                <v-col cols="12" md="6">
                  <div class="text-h4">
                    Welcome
                    <span class="teal--text">{{ ComputeFirstName }}</span
                    >,
                  </div>
                  <div class="text-subtitle-2">
                    Here are some quick statistics for you.
                  </div>
                </v-col>
                <v-col
                  cols="12"
                  md="6"
                  align="end"
                  class="pr-10 hidden-md-and-down"
                >
                  <div class="text-body-1">Total Products</div>
                  <div class="text-h4 orange--text text-undeline">
                    <number
                      ref="number1"
                      :from="0"
                      :to="statistics.products"
                      :duration="1"
                      :format="FormatWithCommas"
                      :delay="0"
                      easing="Power1.easeOut"
                    />
                  </div>
                </v-col>
              </v-row>
            </v-card>
            <v-row no-gutters>
              <v-col
                v-for="(entity, i) in entities"
                :key="i"
                cols="12"
                md="4"
                align="center"
              >
                <v-card
                  dark
                  flat
                  width="80%"
                  class="mb-6 pa-6 rounded-xl elevation-20 text-left"
                  :class="entity.class"
                >
                  <v-row no-gutters>
                    <v-col cols="6">
                      <!-- <v-img
                        height="50"
                        width="50"
                        :src="`${$store.getters.server}img/${entity.image}`"
                      /> -->
                      <v-icon size="50">{{ entity.icon }}</v-icon>
                    </v-col>
                    <v-col cols="6" align="end">
                      <v-icon size="20">mdi-refresh</v-icon>
                    </v-col>
                  </v-row>

                  <div
                    style="
                      margin: 10px;
                      position: absolute;
                      background-color: #fff;
                      border-radius: 30px;
                      height: 100px;
                      top: 0;
                      left: 0;
                      width: 160px;
                      opacity: 0;
                    "
                  />
                  <div class="text-subtitle-2 mt-2">
                    {{ entity.name }}
                  </div>
                  <div class="text-h6 font-weight-thin mb-2">
                    <v-row no-gutters>
                      <v-col cols="7">
                        <span class="font-weight-bold">{{ entity.count }}</span>
                      </v-col>
                      <v-col cols="5" align="end">
                        <span class="text-caption yellow--text font-italic"
                          >as of now</span
                        >
                      </v-col>
                    </v-row>
                  </div>
                  <v-progress-linear
                    stream
                    buffer-value="50"
                    rounded
                    color="white"
                    value="15"
                  />
                </v-card>
              </v-col>
            </v-row>
            <v-row v-if="$vuetify.breakpoint.smAndUp">
              <v-col cols="12">
                <div class="text-h6 text-center font-weight-thin">
                  Stock control
                </div>
                <div class="text-subtitle-2 text-center">
                  We are currently keeping stock in the following branches
                </div>
                <v-row
                  v-show="loading"
                  class="justify-center align-center"
                  style="width: 100%"
                  :style="
                    $vuetify.breakpoint.mdAndDown
                      ? 'height: 300px'
                      : 'height: 500px'
                  "
                >
                  <v-progress-circular indeterminate color="primary" />
                </v-row>
                <div
                  id="chartdiv"
                  ref="StoresChart"
                  class="chart"
                  style="width: 100%"
                  :style="
                    $vuetify.breakpoint.mdAndDown
                      ? 'height: 300px'
                      : 'height: 500px'
                  "
                />
              </v-col>
            </v-row>
          </v-col>
          <v-col cols="12" md="4" class="px-8">
            <v-card flat class="rounded-xl mt-16">
              <div class="text-h6">Statistics</div>
              <div class="text-subtitle-2 mb-5">Verification rate</div>
              <div id="VerificationRate" style="width: 100%" />
              <v-row
                v-show="loading"
                class="justify-center align-center"
                style="width: 100%; height: 150px"
              >
                <v-progress-circular indeterminate color="orange" />
              </v-row>
            </v-card>
            <div class="text-h6 my-1">Top Scan Operators</div>
            <div class="text-subtitle-2 mb-3">Leading in number of scans</div>
            <v-row
              v-show="loading"
              class="justify-center align-center mt-5"
              style="height: 150px"
            >
              <v-col cols="4">
                <v-skeleton-loader type="avatar" /><v-skeleton-loader
                  type="text"
                  class="mt-5"
                  max-width="60px"
              /></v-col>
              <v-col cols="4">
                <v-skeleton-loader type="avatar" /><v-skeleton-loader
                  type="text"
                  class="mt-5"
                  max-width="60px"
              /></v-col>
              <v-col cols="4">
                <v-skeleton-loader type="avatar" /><v-skeleton-loader
                  type="text"
                  class="mt-5"
                  max-width="60px"
              /></v-col>
            </v-row>

            <v-row justify="space-between">
              <v-col
                v-for="(scanner, i) in statistics.scanners"
                :key="i"
                cols="3"
                align="center"
              >
                <img
                  width="60"
                  :src="scanner.ProfilePicture"
                  style="border-radius: 100px"
                />
                <div class="text-subtitle-2 text-center font-weight-thin">
                  {{ `${scanner.FirstName} ${scanner.LastName}` }}
                </div>
                <div class="text-caption text-center">
                  {{ scanner.count }} scans
                </div>
              </v-col>
            </v-row>

            <v-card flat max-width="500" class="pa-8 rounded-xl text-center">
              <div class="text-h6 font-weight-light">Total Discrepancies</div>
              <div class="text-subtitle-2">
                Something that needs your attention
              </div>
              <v-row
                v-show="loading"
                class="justify-center align-center"
                style="width: 100%; height: 350px"
              >
                <v-progress-circular indeterminate color="primary" />
              </v-row>
              <div id="DiscrepanciesChart" style="height: 300px" />
            </v-card>
          </v-col>
        </v-row>
      </v-container>
    </v-card>
    <Snackbar ref="Snackbar" />
    <Alert ref="Alert" />
  </v-main>
</template>

<script>
import * as am4core from "@amcharts/amcharts4/core";
import * as am4charts from "@amcharts/amcharts4/charts";
import am4themes_animated from "@amcharts/amcharts4/themes/animated";
import * as am4maps from "@amcharts/amcharts4/maps";
import worldLow from "@amcharts/amcharts4-geodata/worldLow";
import kenyaLow from "@amcharts/amcharts4-geodata/kenyaLow";
am4core.useTheme(am4themes_animated);

import Appbar from "@/components/Appbar";
import Drawer from "@/components/Drawer";
import Snackbar from "@/components/Snackbar";

export default {
  components: {
    Appbar,
    Drawer,
    Snackbar,
  },

  data() {
    return {
      entities: [
        {
          name: "Stores",
          image: "store.png",
          icon: "mdi-store",
          count: 0,
          class: "StoresCard",
        },
        {
          name: "Scan Operators",
          image: "section.png",
          icon: "mdi-account-multiple",
          count: 0,
          class: "ScanOperatorsCard",
        },
        {
          name: "Locations",
          image: "location.png",
          icon: "mdi-target-variant",
          count: 0,
          class: "LocationsCard",
        },
      ],
      products: 0,
      loading: false,
      statistics: {},
      map: {},
      chart: {},
      pie: {},
      line: {},
    };
  },
  mounted() {
    this.GetStats();
  },
  methods: {
    GetStats() {
      this.loading = true;

      this.promiseFetch(this.$store.getters.fetchTimeout)(
        fetch(`${this.$store.getters.endpoint}Dashboard/`, {
          headers: { Authorization: `Bearer ${this.$store.getters.token}` },
        })
      )
        .then((response) =>
          response.json().then((res) => {
            this.statistics = res;
            this.entities[0].count = this.statistics.stores;
            this.entities[1].count = this.statistics.scanOperators;
            this.entities[2].count = this.statistics.locations;

            this.$vuetify.breakpoint.smAndUp &&
              this.StoresChart(res.scansPerStore);

            this.DiscrepanciesChart([
              {
                type: "Physical Count",
                count: Number(this.statistics.discrepancies.physicalCount),
              },
              {
                type: "System Count",
                count: Number(this.statistics.discrepancies.systemCount),
              },
            ]);

            this.ScansChart(
              "VerificationRate",
              "orange",
              "count",
              this.statistics.scans
            );
          })
        )
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
          this.$Progress.finish();
          this.loading = false;
        });
    },
    WorldMap() {
      this.map = am4core.create(this.$refs.WorldMap, am4maps.MapChart);
      this.map.logo.disabled = true;
      this.map.padding(20, 0, 0, 0);
      this.map.fontSize = 10;

      /* this.map.titles.template.fontSize = 10;
      this.map.titles.template.textAlign = "left";
      this.map.titles.template.isMeasured = true;
      this.map.titles.create().text = "map"; */

      // Set map definition
      this.map.geodata = worldLow;

      // Set projection
      this.map.projection = new am4maps.projections.Miller();
      //this.chart.projection = new am4maps.projections.AlbersUsa();

      // Series for World map
      var worldSeries = this.map.series.push(new am4maps.MapPolygonSeries());
      worldSeries.exclude = ["AQ"];
      worldSeries.useGeodata = true;

      var polygonTemplate = worldSeries.mapPolygons.template;
      polygonTemplate.tooltipText = "{name}";
      polygonTemplate.fill = this.map.colors.getIndex(1);
      polygonTemplate.nonScalingStroke = true;

      // Hover state
      var hs = polygonTemplate.states.create("hover");
      hs.properties.fill = am4core.color("#367B25");

      // Series for United States map
      var usaSeries = this.map.series.push(new am4maps.MapPolygonSeries());
      usaSeries.geodata = kenyaLow;

      var usPolygonTemplate = usaSeries.mapPolygons.template;
      usPolygonTemplate.tooltipText = "{name}";
      usPolygonTemplate.fill = this.map.colors.getIndex(1);
      usPolygonTemplate.nonScalingStroke = true;

      // Hover state
      var hss = usPolygonTemplate.states.create("hover");
      hss.properties.fill = am4core.color("#367B25");

      // Create active state
      var activeState = polygonTemplate.states.create("active");
      activeState.properties.fill = this.map.colors.getIndex(4);

      // Create an event to toggle "active" state
      //var that = this;
      polygonTemplate.events.on("hit", function (ev) {
        //console.log(ev.target.tooltipText);
        /* that.$refs.Alert.Alertify({
          title: "Update Error!",
          content: "The last change you made could not be saved. Kindly retry.",
          visible: true,
          icon: "mdi-wifi-strength-1-alert",
          color: "error",
        }); */
        ev.target.isActive = !ev.target.isActive;
      });

      this.map.series.push(new am4maps.GraticuleSeries());

      // Small map
      this.map.smallMap = new am4maps.SmallMap();
      // Re-position to top right (it defaults to bottom left)
      this.map.smallMap.align = "right";
      this.map.smallMap.valign = "middle";
      this.map.smallMap.series.push(worldSeries);

      // Export
      this.map.exporting.menu = new am4core.ExportMenu();

      // Zoom control
      this.map.zoomControl = new am4maps.ZoomControl();

      var homeButton = new am4core.Button();
      homeButton.events.on("hit", function () {
        this.map.goHome();
      });

      homeButton.icon = new am4core.Sprite();
      homeButton.padding(7, 5, 7, 5);
      homeButton.width = 30;
      homeButton.icon.path =
        "M16,8 L14,8 L14,16 L10,16 L10,10 L6,10 L6,16 L2,16 L2,8 L0,8 L8,0 L16,8 Z M16,8";
      homeButton.marginBottom = 10;
      homeButton.parent = this.map.zoomControl;
      homeButton.insertBefore(this.map.zoomControl.plusButton);

      // Add image series
      var imageSeries = this.map.series.push(new am4maps.MapImageSeries());
      imageSeries.mapImages.template.propertyFields.longitude = "longitude";
      imageSeries.mapImages.template.propertyFields.latitude = "latitude";
      imageSeries.mapImages.template.tooltipText = "{title}";
      imageSeries.mapImages.template.propertyFields.url = "url";

      var circle = imageSeries.mapImages.template.createChild(am4core.Circle);
      circle.radius = 7;
      circle.propertyFields.fill = "color";
      circle.nonScaling = true;

      var circle2 = imageSeries.mapImages.template.createChild(am4core.Circle);
      circle2.radius = 7;
      circle2.propertyFields.fill = "color";

      circle2.events.on("inited", function (event) {
        animateBullet(event.target);
      });
      let that = this;
      function animateBullet(circle) {
        var animation = circle.animate(
          [
            {
              property: "scale",
              from: 1 / that.map.zoomLevel,
              to: 5 / that.map.zoomLevel,
            },
            { property: "opacity", from: 1, to: 0 },
          ],
          1000,
          am4core.ease.circleOut
        );
        animation.events.on("animationended", function (event) {
          animateBullet(event.target.object);
        });
      }

      var colorSet = new am4core.ColorSet();

      imageSeries.data = [
        {
          title: "London",
          latitude: 51.5002,
          longitude: -0.1262,
          color: colorSet.next(),
        },
        {
          title: "Peking",
          latitude: 39.9056,
          longitude: 116.3958,
          color: colorSet.next(),
        },
        {
          title: "New Delhi",
          latitude: 28.6353,
          longitude: 77.225,
          color: colorSet.next(),
        },
        {
          title: "Nairobi",
          latitude: 1.2921,
          longitude: 36.8219,
          url: "http://www.google.co.jp",
          color: colorSet.next(),
        },
        {
          title: "Brasilia",
          latitude: -15.7801,
          longitude: -47.9292,
          color: colorSet.next(),
        },
        {
          title: "Washington",
          latitude: 38.8921,
          longitude: -77.0241,
          color: colorSet.next(),
        },
        {
          title: "Kinshasa",
          latitude: -4.3369,
          longitude: 15.3271,
          color: colorSet.next(),
        },
        {
          title: "Cairo",
          latitude: 30.0571,
          longitude: 31.2272,
          color: colorSet.next(),
        },
        {
          title: "Pretoria",
          latitude: -25.7463,
          longitude: 28.1876,
          color: colorSet.next(),
        },
      ];

      /*  // Create map polygon series
      var polygonSeries = chart.series.push(new am4maps.MapPolygonSeries());

      //Set min/max fill color for each area
      polygonSeries.heatRules.push({
        property: "fill",
        target: polygonSeries.mapPolygons.template,
        min: chart.colors.getIndex(1).brighten(1),
        max: chart.colors.getIndex(1).brighten(-0.3),
      });

      // Make map load polygon data (state shapes and names) from GeoJSON
      polygonSeries.useGeodata = true;

      // Set heatmap values for each state
      polygonSeries.data = [
        {
          id: "KE-28",
          value: 28,
          title: "Nairobi",
          latitude: 1.2921,
          longitude: 36.8219,
        },
        { id: "KE-19", value: 19 },
        { id: "KE-39", value: 39 },
        { id: "KE-14", value: 14 },
        { id: "KE-21", value: 21 },
        { id: "KE-23", value: 23 },
        { id: "KE-30", value: 30 },
        { id: "KE-10", value: 10 },
        { id: "KE-22", value: 22 },
        { id: "KE-13", value: 13 },
        { id: "KE-27", value: 27 },
        { id: "KE-29", value: 29 },
        { id: "KE-16", value: 16 },
        { id: "KE-33", value: 33 },
        { id: "KE-34", value: 34 },
        { id: "KE-02", value: 2 },
        { id: "KE-08", value: 8 },
        { id: "KE-06", value: 6 },
        { id: "KE-15", value: 15 },
        { id: "KE-18", value: 18 },
        { id: "KE-40", value: 40 },
        { id: "KE-36", value: 36 },
        { id: "KE-17", value: 17 },
        { id: "KE-12", value: 12 },
        { id: "KE-41", value: 41 },
        { id: "KE-35", value: 35 },
        { id: "KE-45", value: 45 },
        { id: "KE-31", value: 31 },
        { id: "KE-38", value: 38 },
        { id: "KE-32", value: 32 },
        { id: "KE-26", value: 26 },
        { id: "KE-04", value: 4 },
        { id: "KE-20", value: 20 },
        { id: "KE-11", value: 11 },
        { id: "KE-44", value: 44 },
        { id: "KE-07", value: 7 },
        { id: "KE-03", value: 3 },
        { id: "KE-42", value: 42 },
        { id: "KE-05", value: 5 },
        { id: "KE-01", value: 1 },
        { id: "KE-09", value: 9 },
        { id: "KE-37", value: 37 },
        { id: "KE-47", value: 47 },
        { id: "KE-46", value: 46 },
        { id: "KE-24", value: 24 },
        { id: "KE-25", value: 25 },
        { id: "KE-43", value: 43 },
      ];

      // Set up heat legend
      let heatLegend = chart.createChild(am4maps.HeatLegend);
      heatLegend.series = polygonSeries;
      heatLegend.align = "left";
      heatLegend.valign = "bottom";
      heatLegend.width = am4core.percent(20);
      heatLegend.marginRight = am4core.percent(4);
      heatLegend.minValue = 0;
      heatLegend.maxValue = 40000000;

      // Set up custom heat map legend labels using axis ranges
      var minRange = heatLegend.valueAxis.axisRanges.create();
      minRange.value = heatLegend.minValue;
      minRange.label.text = minRange.value;
      var maxRange = heatLegend.valueAxis.axisRanges.create();
      maxRange.value = heatLegend.maxValue;
      maxRange.label.text = maxRange.value;

      // Blank out internal heat legend value axis labels
      heatLegend.valueAxis.renderer.labels.template.adapter.add(
        "text",
        function () {
          return "";
        }
      );

      // Configure series tooltip
      var polygonTemplate = polygonSeries.mapPolygons.template;
      polygonTemplate.tooltipText = "{name}: {value}";
      polygonTemplate.nonScalingStroke = true;
      polygonTemplate.strokeWidth = 0.5;

      // Create hover state and set alternative fill color
      var hs = polygonTemplate.states.create("hover");
      hs.properties.fill = am4core.color("#3c5bdc");

       */
    },
    StoresChart(data) {
      const that = this;

      this.chart = am4core.create(this.$refs.StoresChart, am4charts.XYChart);
      this.chart.logo.disabled = true;
      this.chart.colors.step = 3;

      // Legend
      this.chart.legend = new am4charts.Legend();
      this.chart.legend.position = "top";
      this.chart.legend.paddingBottom = 20;
      this.chart.legend.labels.template.maxWidth = 95;

      let xAxis = this.chart.xAxes.push(new am4charts.CategoryAxis());
      xAxis.dataFields.category = "store";
      xAxis.renderer.cellStartLocation = 0.1;
      xAxis.renderer.cellEndLocation = 0.9;
      xAxis.renderer.grid.template.location = 0;

      let yAxis = this.chart.yAxes.push(new am4charts.ValueAxis());
      yAxis.min = 0;

      this.chart.data = data;

      function createSeries(value, name) {
        var series = that.chart.series.push(new am4charts.ColumnSeries());
        series.dataFields.valueY = value;
        series.dataFields.categoryX = "name";
        series.name = name;

        series.events.on("hidden", arrangeColumns);
        series.events.on("shown", arrangeColumns);

        var bullet = series.bullets.push(new am4charts.LabelBullet());
        bullet.interactionsEnabled = true;
        bullet.dy = 30;
        bullet.label.text =
          "[font-size: 10px]{name}[/]\n[bold text-align: center]{valueY}[/]";
        bullet.label.fill = am4core.color("#ffffff");

        return series;
      }

      function arrangeColumns() {
        var series = that.chart.series.getIndex(0);

        var w =
          1 -
          xAxis.renderer.cellStartLocation -
          (1 - xAxis.renderer.cellEndLocation);
        if (series.dataItems.length > 1) {
          var x0 = xAxis.getX(series.dataItems.getIndex(0), "categoryX");
          var x1 = xAxis.getX(series.dataItems.getIndex(1), "categoryX");
          var delta = ((x1 - x0) / that.chart.series.length) * w;
          if (am4core.isNumber(delta)) {
            var middle = that.chart.series.length / 2;

            var newIndex = 0;
            that.chart.series.each(function (series) {
              if (!series.isHidden && !series.isHiding) {
                series.dummyData = newIndex;
                newIndex++;
              } else {
                series.dummyData = that.chart.series.indexOf(series);
              }
            });
            var visibleCount = newIndex;
            var newMiddle = visibleCount / 2;

            that.chart.series.each(function (series) {
              var trueIndex = that.chart.series.indexOf(series);
              var newIndex = series.dummyData;

              var dx = (newIndex - trueIndex + middle - newMiddle) * delta;

              series.animate(
                { property: "dx", to: dx },
                series.interpolationDuration,
                series.interpolationEasing
              );
              series.bulletsContainer.animate(
                { property: "dx", to: dx },
                series.interpolationDuration,
                series.interpolationEasing
              );
            });
          }
        }
      }

      createSeries("physicalCount", "Physical Count");
      createSeries("systemCount", "System Count");
    },
    ScansChart(reference, color, category, data) {
      var container = am4core.create(reference, am4core.Container);
      container.logo.disabled = true;
      container.layout = "grid";
      container.fixedWidthGrid = false;
      container.width = am4core.percent(100);
      container.height = am4core.percent(100);

      this.line = container.createChild(am4charts.XYChart);
      this.line.width = am4core.percent(75);
      this.line.height = 120;
      this.line.fontSize = 12;

      this.line.data = data;

      /* chart.titles.template.fontSize = 10;
      chart.titles.template.textAlign = "left";
      chart.titles.template.isMeasured = true;
      chart.titles.create().text = title; */

      this.line.padding(0, 0, 0, 0);

      var dateAxis = this.line.xAxes.push(new am4charts.DateAxis());
      dateAxis.renderer.grid.template.disabled = false;
      dateAxis.renderer.labels.template.disabled = false;
      dateAxis.startLocation = 0.5;
      dateAxis.endLocation = 0.7;
      dateAxis.cursorTooltipEnabled = false;

      var valueAxis = this.line.yAxes.push(new am4charts.ValueAxis());
      valueAxis.min = 0;
      valueAxis.renderer.grid.template.disabled = true;
      valueAxis.renderer.baseGrid.disabled = true;
      valueAxis.renderer.labels.template.disabled = true;
      valueAxis.cursorTooltipEnabled = false;

      this.line.cursor = new am4charts.XYCursor();
      this.line.cursor.lineY.disabled = true;
      this.line.cursor.behavior = "none";

      var series = this.line.series.push(new am4charts.LineSeries());
      series.tooltipText = "{date}: [bold]{" + category + "}";
      series.dataFields.dateX = "date";
      series.dataFields.valueY = category;
      series.tensionX = 0.8;
      series.strokeWidth = 2;
      series.stroke = color;

      // render data points as bullets
      var bullet = series.bullets.push(new am4charts.CircleBullet());
      bullet.circle.opacity = 0;
      bullet.circle.fill = color;
      bullet.circle.propertyFields.opacity = "opacity";
      bullet.circle.radius = 7;
    },
    DiscrepanciesChart(data) {
      this.pie = am4core.create("DiscrepanciesChart", am4charts.PieChart);
      this.pie.hiddenState.properties.opacity = 0; // this creates initial fade-in
      this.pie.logo.disabled = true;
      this.pie.padding(10, 0, 0, 0);
      this.pie.fontSize = 11;

      this.pie.data = data;

      this.pie.innerRadius = 50;
      this.pie.legend = new am4charts.Legend();

      // Add and configure Series
      var pieSeries = this.pie.series.push(new am4charts.PieSeries());
      pieSeries.dataFields.value = "count";
      pieSeries.dataFields.category = "type";
      pieSeries.slices.template.stroke = am4core.color("#fff");
      pieSeries.slices.template.strokeWidth = 2;
      pieSeries.slices.template.strokeOpacity = 1;

      // This creates initial animation
      pieSeries.hiddenState.properties.opacity = 1;
      pieSeries.hiddenState.properties.endAngle = -45;
      pieSeries.hiddenState.properties.startAngle = -45;
      //pieSeries.labels.template.fill = am4core.color("#fff"); - change label colors
    },
  },
  beforeDestroy() {
    this.chart.dispose();
    this.line.dispose();
    this.pie.dispose();
  },
};
</script>

<style>
.GroupedAvatars {
  _display: inline-block;
}

.GroupedAvatars:not(:first-child) {
  margin-left: -20px;
  border: 2px solid #c79f56;
  /*   -webkit-mask: radial-gradient(
    circle 15px at 5px 50%,
    transparent 99%,
    #fff 100%
  ); */
  mask: radial-gradient(circle 25px at -5px 50%, transparent 100%, #fff 100%);
}

.map-marker {
  /* adjusting for the marker dimensions
    so that it is centered on coordinates */
  margin-left: -8px;
  margin-top: -8px;
  box-sizing: border-box;
}
.map-marker.map-clickable {
  cursor: pointer;
}

.StoresCard {
  _background-image: linear-gradient(to top, #e8198b 0%, #c7eafd 100%);
  background-image: linear-gradient(to right, #ed6ea0 0%, #ec8c69 100%);
}
.ScanOperatorsCard {
  background-image: linear-gradient(-20deg, #b721ff 0%, #21d4fd 100%);
}
.LocationsCard {
  background-image: linear-gradient(to top, #37ecba 0%, #72afd3 100%);
}
</style>
