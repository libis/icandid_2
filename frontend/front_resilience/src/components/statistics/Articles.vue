<template>
  <div id="app">
    <div class="level" >
      <div class="level-left">
          <input class="input" style="margin-right:5px" type="text" v-model="searchterm[0]" @change="update()" ref="search">-
          <input class="input" style="margin-left:5px"  type="text" v-model="searchterm[1]" @change="update()" ref="search">
      </div>
      <div class="level-right">
        
      </div>
    </div>
    <Loader v-if="loading" style="margin:100px"></Loader>
      <table class="table is-striped is-hoverable fullwidth" width=100%>
          <tr v-for="(art, idx) in data" :key="idx">
              <td><a :href="art['_source']['url']">{{ art["_source"]["name"]["@value"] }}</a></td>
          </tr>
      </table>
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
            this.data = res.data["hits"]["hits"]
            
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
              "bool": {
                  "must": []
              }
          },

          "size": 10000,
          "_source": [
              "headline",
              "url",
              "publisher.name",
              "name.@value"
          ]
      },
      searchterm:["Leuven","Gent"],
      loading:false,
      data:[]
    }
  }
}
</script>
<style scoped>
.pointer {cursor: pointer;}
</style>