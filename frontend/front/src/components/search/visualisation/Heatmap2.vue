<template>
  <div class="group">
    <Loader v-if="loading" style="margin:100px"></Loader>
    <!-- <heatmap v-if="!loading" id="heatmap" :dataSource='dataSource' :xAxis='xAxis' :yAxis='yAxis' :titleSettings='titleSettings' :legendSettings='legendSettings' :cellSettings='cellSettings'  :showTooltip='showTooltip'></heatmap>  -->
    <apexchart v-if="!loading && series.length > 0" width="100%" type="heatmap" :options="options" :series="series"></apexchart>
    <input class="input" style="width:100%" type="text" v-model="input" @change="newinput()" ref="search" />
    <p></p><p v-html="$ml.get('heatmap_info')"></p>
  </div>
</template>
<script>
import Loader from '../../helpers/Loader.vue'
//import { HeatMapComponent, Tooltip, Legend } from "@syncfusion/ej2-vue-heatmap";
import VueApexCharts from 'vue-apexcharts'
import axios from "axios";
import { mapGetters } from "vuex";
axios.defaults.withCredentials = true;

export default {
  name: "Heatmap",
  components: {
    Loader,
    //heatmap: HeatMapComponent
    apexchart: VueApexCharts
  },
  data() {
    return {
      lang:"nl",
      data:[],
      series:[],
      options:{
        legend: {
          show: false,
        },
        plotOptions: {
          heatmap:{
            enableShades: true,
            shadeIntensity: 0.5,
            colorScale: {
              ranges: [{
                from: 0,
                to: 1000000,
                color: "#3366cc",
                foreColor: "#000000",
                name: undefined,
              }],              
              min:0,
              max:5000
            }
          }
        },
        chart:{
          toolbar:{
            tools:{
              download:false
            }
          }
        },
        fill: { colors:  ["#8888FF"], type:"solid", opacity:1.0 }
      },
      loading:false,
      aggs: {
          "publ": {
          "terms": {
              "field": "publisher.name.keyword",
              "order": {
              "_count": "desc"
              },
              "size": 50
          },
          "aggs": {
              "date": {
              "date_histogram": {
                  "field": "datePublished",
                  "calendar_interval": "1d",
                  "time_zone": "Europe/Brussels",
                  "min_doc_count": 1
              }
            }
          }
        }
      },      
      input:""
    }
  },
  created() {
    this.update();
    this.lang = this.$ml.current;
  },
  watch:{
    //lang: function() { this.options.title.text = this.$ml.get('articlespernewssourceperday'); this.update(); },
    getElasticQuery: function() { this.update() ;}
  },
  computed: mapGetters(['getApiQueryUrl','getElasticQuery']),
  methods: {
    clear() {
      this.data = [];
      this.series = [];
    },
    newinput() {
      this.update()
    },
    update() {
      var es_query = JSON.parse(JSON.stringify(this.getElasticQuery))
      if (es_query == "" || this.input == "") return false
      this.loading = true;      
      var extra_keyw= 					{
        "query_string": {
          "fields": [
                "name.@value",
                "headline",
                "articleBody",
                "description",
                "text"
              ],
          "query": ""
        }
      }					
      extra_keyw.query_string.query = this.input

      var tmp = JSON.parse(JSON.stringify(es_query.query))

      es_query.query = {}
      es_query.query.bool = {}
      es_query.query.bool.must = []
      es_query.query.bool.must.push(tmp)

      es_query.query.bool.must.push(extra_keyw);
      es_query.aggs = this.aggs;
      es_query.from = 0
      es_query.size = 0
      axios
          .post(this.getApiQueryUrl, es_query)
          .then(res => {
            this.data = res.data.aggregations;
            this.calculateChartData();
            this.loading = false
            
          })
        .catch(error => console.log(error));
    },
    calculateChartData() {
      this.series = []
      var startdate = this.$moment('2032-01-01');
      var enddate = this.$moment('1970-01-01');
      for (var i = 0; i<this.data.publ.buckets.length; i++) {
        for (var j= 0 ; j < this.data.publ.buckets[i].date.buckets.length; j++) {
          var t = this.$moment(this.data.publ.buckets[i].date.buckets[j].key_as_string);
          if (t < startdate) startdate = t
          if (t > enddate) enddate = t
        }
      }
      var filler = []
      for ( i = 0; i<this.data.publ.buckets.length; i++) {
        filler.push({x:this.data.publ.buckets[i].key,y:0})
      }
      if (startdate == enddate) {
        this.series.push({name:startdate.format("YYYY-MM-DD"),data:JSON.parse(JSON.stringify(filler))});
      } else {
        for (var dat = startdate; dat <= enddate; dat = dat.add(1, "day")) {
          this.series.push({name:dat.format("YYYY-MM-DD"),data:JSON.parse(JSON.stringify(filler))});
        }
      }

      this.options.plotOptions.heatmap.colorScale.max = 0
      for ( i = 0; i<this.data.publ.buckets.length; i++) {
        var xkey = this.data.publ.buckets[i].key
        for ( j = 0; j<this.data.publ.buckets[i].date.buckets.length; j++) {
          var ykey = this.data.publ.buckets[i].date.buckets[j].key_as_string
          var yvalue = this.data.publ.buckets[i].date.buckets[j].doc_count

          if (yvalue > this.options.plotOptions.heatmap.colorScale.max) this.options.plotOptions.heatmap.colorScale.max = yvalue

          let col = this.series.find(o => o.name === ykey)
          let cell = col.data.find(p => p.x === xkey)
          cell.y = yvalue


        }
      }
      this.options.plotOptions.heatmap.colorScale.ranges[0].to = this.options.plotOptions.heatmap.colorScale.max
    }
  }
}
</script>
<style scoped>

</style>