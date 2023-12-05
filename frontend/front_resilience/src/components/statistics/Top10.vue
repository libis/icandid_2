<template>
  <div id="app">
    <div class="level" >
      <div class="level-left">
          <input class="input" type="text" v-model="searchterm" @change="update()" ref="search">
      </div>
      <div class="level-right">
        
      </div>
    </div>
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
                <tr v-for="(ne, idx) in data['named_entities']['f_labels']['text']['buckets']" :key="idx">
                    <td>{{ ne["key"] }}</td>
                    <td style="text-align:right">{{ ne["doc_count"] }}</td>
                </tr>
            </table>
        </div>
    </div>    
  </div>
</template>
<script>
import Loader from '../helpers/Loader.vue'
import axios from "axios";
axios.defaults.withCredentials = true;

export default {
  name: 'app',
  components: {
    Loader
  },
  props: ["url"],
  methods: {
    update() {
      this.data = []
      this.loading = true
      this.es_query["query"]["nested"]["query"]["bool"]["must"][0]["query_string"]["query"] = this.searchterm
      axios
        .post(this.url, this.es_query)
        .then(res => {
            this.data = res.data["aggregations"]
            
            this.loading = false
        })
        /*.catch(error => console.log(error))*/;
    }
  },
  created() {
    this.update();
  },
  data() {
    return {
      es_query:{
          "query": {
              "nested": {
                  "path": "_named_entities.entities",
                  "query": {
                      "bool": {
                          "must": [
                              {
                                  "query_string": {
                                      "fields": [
                                          "_named_entities.entities.text"
                                      ],
                                      "query": "Leuven"
                                  }
                              }
                          ]
                      }
                  }
              }
          },
          "size": 10,
          "aggregations": {
              "named_entities": {
                  "nested": {
                      "path": "_named_entities.entities"
                  },
                  "aggs": {
                      "f_labels": {
                          "filter": {
                              "terms": {
                                  "_named_entities.entities.label.keyword": [
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
                                      "field": "_named_entities.entities.text.keyword",
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
          }
      }
      ,
      searchterm:"Leuven",
      loading:false,
      data:[]
    }
  }
}
</script>
<style scoped>
.pointer {cursor: pointer;}
</style>