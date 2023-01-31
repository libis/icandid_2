<template>
  <div id="app">
    <wordcloud
      v-if="!loading && searchterm != ''"
      :data="defaultWords"
      nameKey="name"
      valueKey="value"
      :showTooltip="false"
      :wordClick="wordClickHandler"
      class="pointer"
      :rotate="{from:0,to:0,numOfOrientation:1}"
    ></wordcloud>
    <Loader v-if="loading" style="margin:100px"></Loader>
    <div v-if="searchterm == ''">Gelieve minstens 1 zoekterm in te voeren.</div>
    <div class="level" >
      <div class="level-left">
        <span class="tag is-primary" style="margin-right:5px;min-height:2em;height:auto;white-space:normal" v-for="(st, idx) in searchterm" :key="idx">
                {{ st }}
                <button class="delete is-small" @click="remove(idx)"></button>
        </span> 
      </div>
      <div class="level-right">
        <input class="input" type="text" v-model="input" @change="newinput()" ref="search">
      </div>
      </div>
  </div>
</template>
<script>
import wordcloud from 'vue-wordcloud'
import Loader from '../helpers/Loader.vue'
import axios from "axios";
axios.defaults.withCredentials = true;

export default {
  name: 'app',
  components: {
    wordcloud,
    Loader
  },
  props: ["url"],
  methods: {
    update() {
      if (this.searchterm.length > 0) {
        this.loading = true
        this.defaultWords = []
        this.es_query["query"]["bool"]["must"] = []
        this.searchterm.forEach(function(el) {
          var section = {
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
                                      "query": ""
                                  }
                              }
                          ]
                      }
                  }
              }
          }

          section["nested"]["query"]["bool"]["must"][0]["query_string"]["query"] = el
          this.es_query["query"]["bool"]["must"].push(section)
        }.bind(this))


        axios
          .post(this.url, this.es_query)
          .then(res => {
              this.data = res.data["aggregations"]["named_entities"]["f_labels"]["text"]["buckets"]
              this.calculateChartData()
              this.loading = false
          })
          .catch(error => console.log(error));
      }
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
    },
    wordClickHandler(word) {
        this.searchterm.push(word)
        this.update()
    },
    remove(idx) {
      this.searchterm.splice(idx,1)
      this.update()
    },
    newinput() {
      this.searchterm.push(this.input)
      this.input = ""
      this.update()
    }
  },
  created() {
    this.update();
  },
  data() {
    return {
      es_query:{
          "query": {
              "bool": {
                  "must": []
              }
          },
    "size":0 ,
    "aggs": {
        "named_entities": {
            "nested": {
                "path": "_named_entities.entities"
            },
            "aggs": {
                "f_labels": {
                    "filter": {
                        "terms": {
                            "_named_entities.entities.label.keyword": ["GPE","PERSON","ORG","NORP","FAC","WORK_OF_ART","LOC","EVENT","PRODUCT","LANGUAGE","LAW"]
                        }
                    },
                    "aggs": {
                        "text": {
                            "terms": {
                                "field": "_named_entities.entities.text.keyword",
                                "size": 100
                            }
                        }
                    }
                }
            }
        }
    }
},
      defaultWords:[],
      searchterm:["Leuven"],
      input:"",
      loading:false,
    }
  }
}
</script>
<style scoped>
.pointer {cursor: pointer;}
</style>