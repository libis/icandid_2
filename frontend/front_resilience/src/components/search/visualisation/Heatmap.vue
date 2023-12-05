<template>
  <div class="group">
    <Loader v-if="loading" style="margin:100px"></Loader>
    <heatmap v-if="!loading" id="heatmap" :dataSource='dataSource' :xAxis='xAxis' :yAxis='yAxis' :titleSettings='titleSettings' :legendSettings='legendSettings' :cellSettings='cellSettings'  :showTooltip='showTooltip'></heatmap>
    <input class="input" style="width:100%" type="text" v-model="input" @change="newinput()" ref="search" />
    <p></p><p v-html="$ml.get('heatmap_info')"></p>
  </div>
</template>
<script>
import Loader from '../../helpers/Loader.vue'
import { HeatMapComponent, Tooltip, Legend } from "@syncfusion/ej2-vue-heatmap";
import axios from "axios";
import { mapGetters } from "vuex";
axios.defaults.withCredentials = true;

export default {
  name: "Heatmap",
  components: {
    Loader,
    heatmap: HeatMapComponent
  },
  data() {
    return {
      lang:"nl",
      data:[],
      loading:false,
      backgroundColors : [
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
      ],
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
      input:"",
      xAxis: {
          labels: [],
      },
      yAxis:{
        labels: [],
      },
      cellSettings: {
        showLabel: true,
      },
      titleSettings: {
        text: '',
        textStyle: {
            size: '15px',
            fontWeight: '500',
            fontStyle: 'Normal',
            fontFamily: 'Segoe UI'
        }
      },
      dataSource: [],
        legendSettings: {
            visible:true,
            position: 'Right',
            showLabel: true,
            height: "150"
        },
        showTooltip:true
    }
  },
  provide:{
    heatmap:[Tooltip,Legend]
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
      this.xAxis.labels = [];
      this.yAxis.labels = [];
      this.dataSource = [];
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
        /*.catch(error => console.log(error))*/;
    },
    calculateChartData() {
        this.xAxis.labels = [];
        this.yAxis.labels = [];
        this.dataSource = [];

        var startdate = this.$moment('2032-01-01');
        var enddate = this.$moment('1970-01-01');

        for (var i = 0; i<this.data.publ.buckets.length; i++) {
          for (var j= 0 ; j < this.data.publ.buckets[i].date.buckets.length; j++) {
            var t = this.$moment(this.data.publ.buckets[i].date.buckets[j].key_as_string);
            if (t < startdate) startdate = t
            if (t > enddate) enddate = t
          }
        }

        if (startdate == enddate) {
          this.yAxis.labels.push(startdate.format("YYYY-MM-DD"));
        } else {
          for (var dat = startdate; dat <= enddate; dat = dat.add(1, "day")) {
              this.yAxis.labels.push(dat.format("YYYY-MM-DD"));
          }
        }

        var len = this.yAxis.labels.length;
        for ( i = 0; i<this.data.publ.buckets.length; i++) {
            this.xAxis.labels.push(this.data.publ.buckets[i].key);
            this.dataSource.push(new Array(len).fill(0));

            for ( j= 0 ; j < this.data.publ.buckets[i].date.buckets.length; j++) {
                var date = this.data.publ.buckets[i].date.buckets[j];
                var idx = this.yAxis.labels.indexOf(date.key_as_string)
                this.dataSource[i][idx] = date.doc_count;
            }

        }
    }
  }
};
</script>
<style scoped>

</style>