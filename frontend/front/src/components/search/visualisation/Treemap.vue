<template>
  <div class="group">
    <Loader v-if="loading" style="margin:100px"></Loader>
    <treemap ref="treemap" :width="width" v-if="!loading" id="treemap" :dataSource="dataSource" :weightValuePath="weightValuePath" :leafItemSettings="leafItemSettings" :equalColorValuePath="equalColorValuePath" :legendSettings="legendSettings" :tooltipSettings='tooltipSettings' ></treemap>
    <p></p><p v-html="$ml.get('treemap_info')"></p>
  </div>
</template>
<script>
import Loader from '../../helpers/Loader.vue'
import { TreeMapComponent, TreeMapTooltip } from "../../../../node_modules/@syncfusion/ej2-vue-treemap";
import axios from "../../../../node_modules/axios";
import { mapGetters } from "../../../../node_modules/vuex/dist/vuex.mjs";
axios.defaults.withCredentials = true;

export default {
  name: "Treemap",
  components: {
    Loader,
    treemap: TreeMapComponent
  },
  props:['activetab'],
  data() {
    return {
      lang:"nl",
      data:[],
      loading:false,
      width:"100%",
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
          }
        }
      },
      dataSource: [
        { Publication: "", Count: 0 }
      ],
      weightValuePath: 'Count',
      equalColorValuePath: "Count",
      tooltipSettings: {
        visible: true,
        format:'${Publication} : ${Count}'
      },      
      leafItemSettings: {
        labelPath: "Publication",
        labelFormat: '${Publication}<br>${Count}',
        gap:2,
        colorMapping: [
          { value: "25", color: "#634D6F" }
        ]
      },
      legendSettings: {
        visible: true
      }
    };
  },
  provide:{
    treemap:[TreeMapTooltip]
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
      this.dataSource = [];
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
            this.data = res.data.aggregations;
            console.log(this.data)
            this.calculateChartData();
            this.loading = false
          })
        .catch(error => console.log(error));
    },
    calculateChartData() {
      var values = [];
      this.dataSource = [];
      for(var i=0;i<this.data.publ.buckets.length;i++) {
        var bucket = this.data.publ.buckets[i];
        values.push(bucket.doc_count);
        this.dataSource.push({Publication:bucket.key, Count:bucket.doc_count});
      }
      let unique = [...new Set(values)]
      unique.sort(function(a, b) { return a - b; });  // numeriek sorteren
      unique.reverse();
      this.leafItemSettings.colorMapping = [];
      for(i=0;i<unique.length;i++) {
        this.leafItemSettings.colorMapping.push({ value: unique[i]+"", color: this.backgroundColors[i] });
      }
    }
  }
};
</script>
<style scoped>
</style>