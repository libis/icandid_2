<template>
    <div>
        <d3-network :net-nodes="nodes" :net-links="links" :options="options" v-if="!loading" />  
        <Loader v-if="loading"></Loader>
        <p></p><p v-html="$ml.get('network_info')"></p>
    </div>
</template>
<script>
import D3Network from 'vue-d3-network'
import Loader from '../../helpers/Loader.vue'
import axios from "axios";
import { mapGetters } from "vuex";

  
export default {
  components: {
    D3Network,
    Loader
  },
  data() {
      return {
          nodes:[],
          links: [],
          canvas:false,
          loading:false,
          colors : [
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
          query:{
            "track_total_hits": true,
            "_source": [
                "creator",
                "author",
                "sender"
            ],
            "query": {
                "bool": {
                "must": [
                    {
                    "term": {
                        "isBasedOn.provider.alternateName.keyword": {
                        "value": "Twitter"
                        }
                    }
                    },
                    {
                    "script": {
                        "script": "doc['author.@id'].value != doc['sender.@id'].value"
                    }
                    }
                ],
                "filter": {
                    "exists": {
                    "field": "author.@id"
                    }
                }
                }
            },
            "aggs": {
                "provider": {
                "terms": {
                    "field": "isBasedOn.provider.name.keyword",
                    "size": 10
                }
                },
                "author": {
                "terms": {
                    "field": "author.@id",
                    "size": 10
                },
                "aggs": {
                    "sender": {
                    "terms": {
                        "field": "sender.@id",
                        "size": 1000
                    }
                    }
                }
                }
            }
        },
        person_query:{
          "_source": [
            "author",
            "sender"
          ],
          "query": {
            "bool": {
              "must" :[],
              "should": [ ]
            }
          },
          size:10000
        }    
      }
  },
  watch:{
    //getElasticQuery: function() { this.update() ;}
  },
  computed:{
    ...mapGetters(['getApiQueryUrl','getElasticQuery']),
    options(){
      return{
        force: 475,
        size:{ w:1100, h:600},
        nodeLabels: true,
        linkWidth:1,
        fontSize:10,
        nodeSize:5
      }
    }
  },
  created() {
    this.update();
  }, 
  methods: {
    clear() {
      this.nodes = [];
      this.links = [];
    },
    async update() {
      if (this.nodes.length == 0) {
        var es_query = JSON.parse(JSON.stringify(this.getElasticQuery))
        if (es_query.query == undefined) {
          return false
        }
        var tmp = JSON.parse(JSON.stringify(es_query.query)) 
        var query = JSON.parse(JSON.stringify(this.query))
        query.query.bool.must.push(tmp);
        this.loading=true
        await axios
              .post(this.getApiQueryUrl, query)
              .then(res => {
                var data = res.data.aggregations;
                this.calculateChartData(data);
                
              })
            /*.catch(error => console.log(error))*/;
        this.loading=false
      }
    },
    calculateChartData(d) {
        var nodes = [];
        var links = [];
        var l_idx = 1;
        var maxcount = 0;
        this.nodes = [];
        this.links = [];
        for (var i = 0; i < d.author.buckets.length; i++) {
            nodes.push({id:d.author.buckets[i].key,_size:d.author.buckets[i].doc_count,_color:this.colors[i]});
            if (d.author.buckets[i].doc_count > maxcount) { maxcount = d.author.buckets[i].doc_count; }
        }

        for (i = 0; i < d.author.buckets.length; i++) {
            for (var j = 0; j < d.author.buckets[i].sender.buckets.length; j++) {
                if ( nodes.find(x => x.id == d.author.buckets[i].sender.buckets[j].key) == undefined) {
                  nodes.push({id:d.author.buckets[i].sender.buckets[j].key,_size:0});
                }
                links.push({id:l_idx,sid:d.author.buckets[i].key,tid:d.author.buckets[i].sender.buckets[j].key});
                l_idx++;
            }
        }

        for (i=0; i< nodes.length; i++) {
          nodes[i]._size = Math.ceil((50 * nodes[i]._size) / maxcount) + 8
        }

        var k,l, chunk = 256;
        var subsets = [];
        for (k = 0,l = nodes.length; k < l; k += chunk) {
            subsets.push(nodes.slice(k, k + chunk));
            // do whatever
        }

        for (l=0; l<subsets.length; l++) {
          var person_query = JSON.parse(JSON.stringify(this.person_query));
          for (i=0; i< subsets[l].length; i++) {
            var part = {
                    "query_string": {
                      "fields": [
                        "author.@id",
                        "sender.@id"
                      ],
                      "query": ""
                    }
                  }
            part.query_string.query = subsets[l][i].id 
            person_query.query.bool.should.push(part)

          }
          var es_query = JSON.parse(JSON.stringify(this.getElasticQuery))
          var tmp = JSON.parse(JSON.stringify(es_query.query)) 
          person_query.query.bool.must.push(tmp)

          axios
            .post(this.getApiQueryUrl, person_query)
            .then(res => {
              for(i=0; i<res.data.hits.hits.length; i++){
                if (res.data.hits.hits[i]._source.sender != undefined) {
                  var idx = nodes.findIndex(x => x.id == res.data.hits.hits[i]._source.sender['@id'])
                  if (idx >= 0) {
                    nodes[idx].name = res.data.hits.hits[i]._source.sender.name
                  }
                }
                if (res.data.hits.hits[i]._source.author != undefined) {
                  idx = nodes.findIndex(x => x.id == res.data.hits.hits[i]._source.author['@id'])
                  if (idx >= 0) {
                    nodes[idx].name = res.data.hits.hits[i]._source.author.name 
                  }
                } 
              }
            })
          /*.catch(error => console.log(error))*/;
        }





        this.nodes = nodes;
        this.links = links
    }
  }     
}
</script>
<style src="vue-d3-network/dist/vue-d3-network.css"></style>
