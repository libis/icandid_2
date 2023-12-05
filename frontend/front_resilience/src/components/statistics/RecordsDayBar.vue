<template>
  <div class="group">
    <Bar :chart-data="chartData" :options="options" :height="600" v-if="!loading"></Bar>
    <div class="level" v-if="!loading">
      <div class="level-left"></div>
      <div class="level-right">
        <div class="level-item">{{ $ml.get('last') }}</div>
        <div class="level-item select" v-bind:class="{'is-loading': loading}">
          <select v-model="period" @change="update()" ref="sel">
            <option value="7">7 {{ $ml.get('days') }}</option>
            <option value="15">15 {{ $ml.get('days') }}</option>
            <option value="30">30 {{ $ml.get('days') }}</option>
            <option value="60">60 {{ $ml.get('days') }}</option>
            <option value="90">90 {{ $ml.get('days') }}</option>
            <option value="120">120 {{ $ml.get('days') }}</option>
            <option value="150">150 {{ $ml.get('days') }}</option>
            <option value="180">180 {{ $ml.get('days') }}</option>
          </select>
        </div>
      </div>
    </div>
    <Loader v-if="loading" style="margin:100px"></Loader>
  </div>
</template>
<script>
import Bar from "./BarChart.js";
import Loader from '../helpers/Loader.vue'
import { mapGetters } from "vuex";
import axios from "axios";
axios.defaults.withCredentials = true;

export default {
  name: "RecordsDayBar",
  props: ["lang"],
  components: {
    Bar,
    Loader
  },
  data() {
    return {
      data: {},
      loading:false,
      options: {
        responsive: true,
        maintainAspectRatio: false,
        legend: { 
          position: "bottom",
          align: "start"
          
        },
        title: {
          text: this.$ml.get('articlespernewssourceperday'),
          display: true,
          fontSize: 24
        },
        scales: {
          xAxes: [
            {
              stacked: true,
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
              stacked: true
            }
          ]
        }
      },
      period: 7,
      lastdate: "",
      chartData: {}
    };
  },
  created() {
    this.update();
  },
  watch:{
    lang: function() { this.options.title.text = this.$ml.get('articlespernewssourceperday'); this.update(); }
  },
  computed: mapGetters(['getApiStatsUrl']),
  methods: {
    update() {
      this.loading = true
      var params = {}
      params.period = this.period;
      axios
        .post(this.getApiStatsUrl+'/recordsdaybar', params)
        .then(res => {
          this.data = res.data.aggregations;
          this.calculateChartData();
          this.loading = false
        })
        /*.catch(error => console.log(error))*/;
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
      var o = { labels: [], datasets: [] };
      if (this.data.publ != undefined) {
        for (var i = 0; i < this.data.publ.buckets.length; i++) {
          var d = [];
          for (
            var j = 0;
            j < this.data.publ.buckets[i].date.buckets.length;
            j++
          ) {
            var datum = new Date(this.data.publ.buckets[i].date.buckets[j].key);
            //var key = this.$moment(datum).format('YYYY-MM-DD');
            var value = this.data.publ.buckets[i].date.buckets[j].doc_count;
            d.push({ x: datum, y: value });
          }
          o.datasets.push({
            label: this.data.publ.buckets[i].key,
            data: d,
            backgroundColor: backgroundColors[i]
          });
        }
      }

      // adding zero values for missing datapoints

      var startdate = this.$moment('2032-01-01');
      var enddate = this.$moment('1970-01-01');

      for ( i = 0; i<this.data.publ.buckets.length; i++) {
        for ( j= 0 ; j < this.data.publ.buckets[i].date.buckets.length; j++) {
          var t = this.$moment(this.data.publ.buckets[i].date.buckets[j].key_as_string);
          if (t < startdate) startdate = t
          if (t > enddate) enddate = t
        }
      }

      for (var dat = startdate; dat <= enddate; dat = dat.add(1, "day")) {
        var datestr = dat.format("YYYY-MM-DD");
        o.datasets.forEach(np => {
          if (
            np.data.filter(
              f => this.$moment(f.x).format("YYYY-MM-DD") == datestr
            ).length == 0
          ) {
            np.data.push({ x: this.$moment(datestr).toDate(), y: 0 });
          }
        });
      }

      // sorting datasets so that filled-in zero values end up in sequence
      o.datasets.forEach(np => {
        np.data.sort((a, b) => a.x - b.x);
      });

      this.chartData = o;
    }
  }
};
</script>
<style scoped>
</style>