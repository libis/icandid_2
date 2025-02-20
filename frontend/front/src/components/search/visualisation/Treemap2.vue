<template>
  <div class="group">
    <Loader v-if="loading" style="margin:100px"></Loader>
    <apexchart width="100%" type="treemap" :options="options" :series="series"></apexchart>
    <p></p><p v-html="$ml.get('treemap_info')"></p>
  </div>
</template>
<script>
import Loader from '../../helpers/Loader.vue'
import VueApexCharts from 'vue-apexcharts'
import axios from "../../../../node_modules/axios";
import { mapGetters } from "../../../../node_modules/vuex/dist/vuex.mjs";
axios.defaults.withCredentials = true;

export default {
  name: "Treemap",
  components: {
    Loader,
    apexchart: VueApexCharts
  },
  props:['activetab'],
  data() {
    return {
      lang:"nl",
      data:[],
      series:[],
      options:{
        plotOptions: {
          treemap: {
            distributed: true,
            enableShades :false
          }
        },
        chart:{
          toolbar:{
            tools:{
              download:false
            }
          }
        },
        fill: { colors:  ["#3366CC", "#DC3912", "#FF9900", "#109618", "#990099", "#3B3EAC", "#0099C6", "#DD4477", "#66AA00", "#B82E2E", "#316395", "#994499", "#22AA99", "#AAAA11", "#6633CC", "#E67300", "#8B0707", "#329262", "#5574A6", "#651067"], type:"solid", opacity:1.0 }
      },
      loading:false,
      width:"100%",
      aggs: {
        "publ": {
          "terms": {
            "field": "publisher.name.keyword",
            "order": {
              "_count": "desc"
            },
            "size": 50
          }
        }
      }
    };
  },
  created() {
    this.update();
    this.lang = this.$ml.current;
  },
  watch:{
    //lang: function() { this.options.title.text = this.$ml.get('articlespernewssourceperday'); this.update(); },
    getElasticQuery: function() { this.update() ;},
    activetab:function () { this.calculateChartData() }
  },
  computed: mapGetters(['getApiQueryUrl','getElasticQuery']),
  methods: {
    clear() {
      this.data = [];
    },
    newinput() {
      this.update()
    },
    update() {
      this.loading = true
      var es_query = JSON.parse(JSON.stringify(this.getElasticQuery))
      if (es_query == "") return false
      es_query.aggs = this.aggs
      es_query.from = 0
      es_query.size = 0
      axios
          .post(this.getApiQueryUrl, es_query)
          .then(res => {
            this.data = res.data.aggregations.publ.buckets;
            this.calculateChartData();
            this.loading = false
          })
        .catch(error => console.log(error));
    },
    calculateChartData() {
      this.series = [{data: []}]
      for(var i=0;i<this.data.length;i++) {
        var bucket = this.data[i];
        this.series[0].data.push({x:bucket.key, y:bucket.doc_count})
      }
    }
  }
};
</script>
<style scoped>
</style>