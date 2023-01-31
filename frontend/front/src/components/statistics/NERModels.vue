<template>
  <div class="group" >
    <Bar :chart-data="chartData" :options="options" :height="120" v-if="!loading"></Bar>
    <input type="Button" :value="$ml.get('refresh')" @click="update()" v-if="!loading">
    <br/><br/>
    <Loader v-if="loading" style="margin:100px"></Loader>
  </div>
</template>
<script>
import Bar from "./HorizontalBarChart.js";
import Loader from '../helpers/Loader.vue'
import { mapGetters } from "vuex";
import axios from "axios";
axios.defaults.withCredentials = true;

export default {
  name: "NERModelsBar",
  props: ["lang"],
  components: {
    Bar,
    Loader
  },
  data() {
    return {
      count:0,
      nonecount:0,
      loading:false,
      options: {
        responsive: true,
        maintainAspectRatio: false,
        legend: { position: "right" },
/*        title: {
          text: this.$ml.get('NERModelsinuse'),
          display: true,
          fontSize: 24
        },*/
        scales: {
          xAxes: [
            {
              stacked: true,
              offset: true
            }
          ],
          yAxes: [
            {
              stacked: true
            }
          ]
        }
      }
    }
  },
  created() {
    this.update();
  },
  computed: {
    ...mapGetters(['getApiStatsUrl'])
  },
  watch:{
    lang: function() { this.options.title.text = this.$ml.get('NERModelsinuse'); this.update(); }
  },
  methods: {
    update() {
      this.loading = true
      axios
        .post(this.getApiStatsUrl+'/ner')
        .then(res => {
          this.count = res.data.count;
          this.nonecount = res.data.nonecount;
          this.calculateChartData();
          this.loading = false
        })
        .catch(error => console.log(error));
    },
    calculateChartData() {
      var backgroundColors = [
        "#3366CC",
        "#DC3912",
        "#FF9900",
        "#109618",
        "#990099",
        "#3B3EAC",
        "#0099C6",
        "#DD4477",
        "#66AA00",
        "#B82E2E",
        "#316395",
        "#994499",
        "#22AA99",
        "#AAAA11",
        "#6633CC",
        "#E67300",
        "#8B0707",
        "#329262",
        "#5574A6",
        "#651067"
      ];
      var count = this.count;
      var nonecount = this.nonecount;
      var o = { labels: [], datasets: [] };
      o.labels = ["Status"];

      o.datasets.push({
          label: "Parsed" + ' (' + ((100*count/(count+nonecount)).toFixed(2)) + '%)',
          data: [count],
          backgroundColor: backgroundColors[3],
          borderColor: backgroundColors[3],
          borderWidth: 1
      });
    
      if (nonecount > 0) {
        o.datasets.push({
              label: "Not parsed (" + ((100*nonecount/(count+nonecount)).toFixed(2)) + '%)',
              data: [nonecount],
              backgroundColor: backgroundColors[1],
              borderColor: backgroundColors[1],
              borderWidth: 1
            });
      }
      this.chartData = o;
    }
  }
};
</script>
<style scoped>
</style>