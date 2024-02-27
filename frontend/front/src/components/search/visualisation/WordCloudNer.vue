<template>
  <div id="app">
    <wordcloud
      v-if="!loading"
      :data="defaultWords"
      nameKey="name"
      valueKey="value"
      :showTooltip="false"
      class="pointer"
      :rotate="{from:0,to:0,numOfOrientation:1}"
    ></wordcloud>
    <Loader v-if="loading" style="margin:100px"></Loader>
    <p></p><p v-html="$ml.get('wordcloudner_info')"></p>
  </div>
</template>
<script>
import wordcloud from 'vue-wordcloud'
import Loader from '../../helpers/Loader.vue'
import { mapGetters } from "vuex";
import axios from "axios";
axios.defaults.withCredentials = true;

export default {
  name: 'app',
  components: {
    wordcloud,
    Loader
  },
  data() {
    return {
      aggs: {
        "filtered": {
          "filters": {
            "filters": {
              "entities": {
                "term": {
                  "prov:wasAttributedTo.name.keyword": "SpaCy"
                }
              }
            }
          },
          "aggs": {
            "ENRICHMENTS": {
              "nested": {
                "path": "prov:wasAttributedTo.prov:wasAssociatedFor"
              },
              "aggs": {
                "ALL": {
                  "terms": {
                    "field": "prov:wasAttributedTo.prov:wasAssociatedFor._generated.ALL.keyword",
                    "size": 100
                  }
                }
              }
            }
          }
        }
      },
/*      aggs: {
        "named_entities": {
          "filter": {
              "terms": {
                  "_named_entities.entities.all.label": ["GPE","PERSON","ORG","NORP","FAC","WORK_OF_ART","LOC","EVENT","PRODUCT","LANGUAGE","LAW"]
              }
          },
          "aggs": {
              "text": {
                  "terms": {
                      "field": "_named_entities.entities.all.value.keyword",
                      "size": 100
                  }
              }
          }
        }
      }, */
      defaultWords:[],
      loading:false,
    }
  },
  methods: {
    clear() {
      this.defaultWords = []
    },
    update() {
      this.loading = true
      this.defaultWords = []
      var es_query = JSON.parse(JSON.stringify(this.getElasticQuery))
      if (es_query == "") return false
      es_query.aggs = this.aggs
      es_query.from = 0
      es_query.size = 0
//      console.log(JSON.stringify(this.aggs))
//      console.log(JSON.stringify(es_query))
      axios
        .post(this.getApiQueryUrl, es_query)
        .then(res => {
//            this.data = res.data["aggregations"]["named_entities"]["text"]["buckets"]
            this.data = res.data["aggregations"]["filtered"]["buckets"]["entities"]["ENRICHMENTS"]["ALL"]["buckets"]
            this.calculateChartData()
            this.loading = false
        })
        .catch(error => console.log(error));
    },      
    calculateChartData() {
      var defWords = [];
      if (this.data.length == 0) {
          return
      }
      for (var i = 0; i < this.data.length; i++) {
          defWords.push({name:this.data[i]["key"], value:this.data[i]["doc_count"]})
      }      
      this.defaultWords = defWords
    }
  },
  created() {
    this.update();
  },
  watch:{
    getElasticQuery: function() { this.update() ;}
  },
  computed: mapGetters(['getApiQueryUrl','getElasticQuery'])

}
</script>
<style scoped>
.pointer {cursor: pointer;}
</style>