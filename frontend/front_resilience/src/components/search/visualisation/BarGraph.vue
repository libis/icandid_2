<template>
  <div class="group">
    <div class="columns">
      <div class="column is-9">
        <Bar :chart-data="chartData" :options="options" v-if="!loading"></Bar>
        <Loader v-if="loading" style="margin:100px"></Loader>
      </div>
      <div class="column is-3">
        <div v-if="keywords == ''">{{ $ml.get('onesearchterm') }}</div>
            <div class="tag is-primary" style="margin-right:5px;margin-bottom:10px;min-height:2em;height:auto;white-space:normal" v-bind:style="{ backgroundColor: backgroundColors[idx]}" v-for="(st, idx) in keywords" :key="idx">
                    {{ st }}
                    <button class="delete is-small" @click="remove(idx)"  v-bind:style="{ backgroundColor: backgroundColors[idx]}"></button><br>
            </div> 
            <br>
            <input class="input" style="width:100%" type="text" v-model="input" @change="newinput()" ref="search">
      </div>
    </div>
    <p v-html="$ml.get('bargraph_info')"></p>
  </div>  
</template>
<script>
import Bar from "../../statistics/BarChart.js";
import Loader from '../../helpers/Loader.vue'
import axios from "axios";
import { mapGetters } from "vuex";
axios.defaults.withCredentials = true;

export default {
  name: "BarGraph",
  components: {
    Bar,
    Loader
  },
  data() {
    return {
      lang:"nl",
      data: {},
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
      options: {
        responsive: true,
        maintainAspectRatio: false,
        legend: { position: "right", display: false },
        title: {
          text: "",
          display: false,
          fontSize: 24
        },
        scales: {
          xAxes: [
            {
              stacked: false,
              type: "time",
              time: {
                unit: "day",
                tooltipFormat: "MMM DD YYYY"
              },
              offset: true
            }
          ],
          yAxes: [
            {
              stacked: false,
              ticks: {
                beginAtZero: true,
              }
            }
          ]
        }
      },
      aggs: {
        date: {
          date_histogram: {
            field: "datePublished",
            calendar_interval: "1d",
            time_zone: "Europe/Brussels",
            min_doc_count: 1
          }
        }
      },
      chartData: {},
      tmp_chartData: {},
      keywords:[],
      input:""
    };
  },
  created() {
    this.update();
    this.lang = this.$ml.current;
  },
  watch:{
    lang: function() { this.options.title.text = this.$ml.get('articlespernewssourceperday'); this.update(); },
    getElasticQuery: function() { this.update() ;}
  },
  computed: mapGetters(['getApiQueryUrl','getElasticQuery']),
  methods: {
    clear() {
      this.tmp_chartData = {}
    },
    remove(idx) {
      this.keywords.splice(idx,1)
      this.update()
    },
    newinput() {
      this.keywords.push(this.input)
      this.input = ""
      this.update()
    },
    async update() {
      var es_query = JSON.parse(JSON.stringify(this.getElasticQuery))
      if (es_query == "" || this.keywords.length == 0) return false
      this.loading = true
      this.tmp_chartData = { labels: [], datasets: [] };
        for (var i = 0; i < this.keywords.length; i++) {
          this.idx = i
          es_query = JSON.parse(JSON.stringify(this.getElasticQuery))
          if (es_query == "") return false
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
          extra_keyw.query_string.query = this.keywords[this.idx];
          var tmp = JSON.parse(JSON.stringify(es_query.query))

          es_query.query = {}
          es_query.query.bool = {}
          es_query.query.bool.must = []
          es_query.query.bool.must.push(tmp)
 
          es_query.query.bool.must.push(extra_keyw)  
          es_query.aggs = this.aggs
          es_query.from = 0
          es_query.size = 0

          await axios
            .post(this.getApiQueryUrl, es_query)
            .then(res => {
              this.data = res.data.aggregations;
              this.calculateChartData();
              
            })
          /*.catch(error => console.log(error))*/;
        }
        this.chartData = this.tmp_chartData;

        this.loading = false
    },
    calculateChartData() {
      if (this.data.date != undefined) {
        var d = [];
        for (var i = 0; i < this.data.date.buckets.length; i++) {
            var datum = new Date(this.data.date.buckets[i].key);
            //var key = this.$moment(datum).format('YYYY-MM-DD');
            var value = this.data.date.buckets[i].doc_count;
            d.push({ x: datum, y: value });
        }

        this.tmp_chartData.datasets.push({
            label: this.keywords[this.idx],
            data: d,
            backgroundColor: this.backgroundColors[this.idx]
        });
      }
    }
  }
};
</script>
<style scoped>
</style>