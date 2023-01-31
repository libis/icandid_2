<template>
  <div class="group">
    <Doughnut :chart-data="chartData" :options="options" v-if="!loading"></Doughnut>
    <Loader v-if="loading" style="margin:100px"></Loader>
  </div>
</template>
<script>
import Doughnut from "./DoughnutChart.js";
import Loader from "../helpers/Loader.vue"
import { mapGetters } from "vuex";
import axios from "axios";
axios.defaults.withCredentials = true;

export default {
  name: "TypePie",
  props: ["lang"],
  components: {
    Doughnut,
    Loader
  },
  data() {
    return {
      data: [],
      loading: false,
      options: {
        legend: {
          display: true,
          position: "bottom"
        },
        title: {
          text: this.$ml.get('numberofarticlespertype'),
          display: true,
          fontSize: 24
        },
        elements: {
          arc: {
            borderWidth: 0
          }
        },
        responsive: true,
        maintainAspectRatio: true
      }
    };
  },
  created() {
    this.update();
  },
  watch:{
    lang: function() { this.options.title.text = this.$ml.get('numberofarticlespertype'); this.update(); }
  },
  methods: {
    update() {
      this.loading = true;
      axios
        .post(this.getApiStatsUrl+'/typepie')
        .then(res => {
          this.data = res.data.aggregations.two.buckets;
          this.loading = false;
        })
        .catch(error => console.log(error));
    }
  },
  computed: {
    ...mapGetters(['getApiStatsUrl']),
    chartData() {
      var o = { labels: [], datasets: [] };
      o.datasets.push({
        data: [],
        backgroundColor: [
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
        ]
      });

      this.data.forEach(element => {
        o.labels.push(element.key);
        o.datasets[0].data.push(element.doc_count);
      });
      return o;
    }
  }
};
</script>
<style scoped>
</style>