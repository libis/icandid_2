<template>
  <div class="group">
    <LineGraph :chart-data="chartData" :options="options" v-if="!loading"></LineGraph>
    <Loader v-if="loading" style="margin:100px"></Loader>
  </div>
</template>
<script>
import LineGraph from "./LineChart.js";
import Loader from '../helpers/Loader.vue'
import { mapGetters } from "vuex";
import axios from "axios";
axios.defaults.withCredentials = true;

export default {
  name: "ArticleLine",
  props: ["lang"],
  components: {
    LineGraph,
    Loader
  },
  data() {
    return {
      data: {},
      loading:false,
      options: {
        responsive: true,
        maintainAspectRatio: false,
        legend: { display: false },
        title: {
          text: this.$ml.get('articlesperweek'),
          display: true,
          fontSize: 24
        },
        scales: {
          xAxes: [
            {
              stacked: true,
              type: "time",
              time: {
                unit: "week",
                tooltipFormat: "MMM DD YYYY"
              },
              offset: true
            }
          ],
          yAxes: [
            {
              stacked: true
            }
          ]
        }
      },
      lastdate: "",
      chartData: {}
    };
  },
  created() {
    this.update();
  },
  watch:{
    lang: function() { this.options.title.text = this.$ml.get('articlesperweek'); this.update(); }
  },
  computed: mapGetters(['getApiStatsUrl']),
  methods: {
    update() {
      this.loading = true
      axios
        .post(this.getApiStatsUrl+'/articleline')
        .then(res => {
            this.data = res.data.aggregations.date.buckets;
            this.calculateChartData();
            this.loading = false
        })
        /*.catch(error => console.log(error))*/;
    },
    calculateChartData() {
      var o = { labels: [], datasets: [{data:[], fill: false, borderColor:'#3366CC'}] };
      for (var i = 0; i < this.data.length-1; i++) {
        o.labels.push(this.data[i].key_as_string);
        o.datasets[0].data.push(this.data[i].doc_count);
      }
      this.chartData = o;
    }
  }
};
</script>
<style scoped>
</style>