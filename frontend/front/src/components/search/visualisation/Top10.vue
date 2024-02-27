<template>
  <div id="app">
    <Loader v-if="loading" style="margin:100px"></Loader>
    <div class="columns">
        <div class="column" v-if="data['newssources']">
            <b>Top {{ data['newssources']['buckets'].length }} nieuwsbronnen</b>
            <table class="table is-striped is-hoverable fullwidth" width=100%>
                <tr v-for="(ns, idx) in data['newssources']['buckets']" :key="idx">
                    <td>{{ ns["key"] }}</td>
                    <td style="text-align:right">{{ ns["doc_count"] }}</td>
                </tr>
            </table>
        </div>
        <div class="column" v-if="data['named_entities']">
            <b>Top 10 named entities</b>
            <table class="table is-striped is-hoverable fullwidth" width=100%>
                <tr v-for="(ne, idx) in data['named_entities']['buckets']" :key="idx">
                    <td>{{ ne["key"] }}</td>
                    <td style="text-align:right">{{ ne["doc_count"] }}</td>
                </tr>
            </table>
        </div>
    </div>
      <p></p><p v-html="$ml.get('top10_info')"></p>
  </div>
</template>
<script>
import Loader from '../../helpers/Loader.vue'
import { mapGetters } from "vuex";
import axios from "axios";
axios.defaults.withCredentials = true;

export default {
  name: 'app',
  components: {
    Loader
  },
  methods: {
    clear() {
      this.data = []
    },
    update() {
      this.data = []
      this.loading = true
      var es_query = JSON.parse(JSON.stringify(this.getElasticQuery))
      if (es_query == "") return false
      es_query.size = 0;
      es_query.from = 0;
      es_query.aggs = this.aggs;    
      
      axios
        .post(this.getApiQueryUrl, es_query)
        .then(res => {
            
            this.data["newssources"] = res.data["aggregations"]["newssources"]
            this.data["named_entities"] = res.data["aggregations"]["filtered"]["buckets"]["entities"]["ENRICHMENTS"]["ALL"]
            this.loading = false
        })
        .catch(error => console.log(error));
    }
  },
  created() {
    this.update();
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
                    "size": 10
                  }
                }
              }
            }
          }
        },
        "newssources": {
          "terms": {
            "field": "publisher.name.keyword",
            "size": 10
          }
        }
      },
/*

      aggs:{
          "named_entities": {
              "filter": {
                  "terms": {
                      "_named_entities.entities.all.label": [
                          "GPE",
                          "PERSON",
                          "ORG",
                          "NORP",
                          "FAC",
                          "WORK_OF_ART",
                          "LOC",
                          "EVENT",
                          "PRODUCT",
                          "LANGUAGE",
                          "LAW"
                      ]
                  }
              },
              "aggs": {
                  "text": {
                      "terms": {
                          "field": "_named_entities.entities.all.value.keyword",
                          "size": 10
                      }
                  }
                }
            },
            "newssources": {
                "terms": {
                    "field": "publisher.name.keyword",
                    "size": 10
                }
            }
        }, */
      loading:false,
      data:[]
    }
  },
  computed:{
        ...mapGetters(['getApiQueryUrl','getElasticQuery']),
  },
  watch:{
    getElasticQuery: function() { this.update() ;}
  }
}
</script>
<style scoped>
.pointer {cursor: pointer;}
</style>