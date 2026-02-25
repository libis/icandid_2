<template>
  <div>
    <button class="button is-rounded is-pulled-right" style="margin-top:10px" :title="$ml.get('savequery')"  @click="$parent.saveQuery()" v-if="getHits>0"><i class="fa fa-floppy-o" aria-hidden="true"></i></button>
    
    <SuperSearchGroup :group="query" @change="queryChanged" :first="true"></SuperSearchGroup>
  </div>
</template>
<script>
import { mapGetters, mapActions } from 'vuex';
import axios from "axios";
import SuperSearchGroup from './SuperSearchGroup.vue';
export default {
  data() {
    return {
      query: [],
      ddlists: [],
    };
  },
  components: {
    SuperSearchGroup
  },
  methods: {
    ...mapActions(["clearResultset","clearAggregations","saveQuery","exportResultCsv","exportResultJsonLd"]),
    clear() {
      this.query = {
          operator: "and",
          rules: [{field: "any", operator: "contains", value: ""}]
        }
      this.clearResultset();
      this.clearAggregations()     
    },
    fillddlists() {
      var tmp_providers = []
      this.ddlists.publishers = []
      this.ddlists.providers = []
      this.$parent.datasets.forEach(element => {
        this.ddlists.publishers.push(element.name);
        tmp_providers.push(element.provider)
      });
      this.ddlists.providers = tmp_providers.filter((v, i, a) => a.indexOf(v) === i).sort(); // unique and sorted
      this.ddlists.datasets = this.getDatasets.map(ds => { return {id: ds.id, internalident: ds.internalident, name: ds.name} }) // only id, internalident and name are needed for the dropdowns   
      this.ddlists.labels = this.$parent.labels // only id and name are needed for the dropdowns
    },
    queryChanged(value) {
      this.query = value;
    },
    queryObj() {
      var qObj = { q: this.query, searchtype: "super", name: this.keywords() }
      return qObj;
     },
    keywords(){
      var keywords = []
      function extractKeywords(rules) {
        rules.forEach(rule => {
          if (rule.rules != undefined) {
            extractKeywords(rule.rules)
          } else {
            if (rule.value != "") {
              keywords.push(rule.value)
            }
          }
        });
      }
      extractKeywords(this.query.rules)
      return keywords.join(" ")
    }
  },
  computed: {...mapGetters(['getHits','getApiDdlistsUrl','getUser','getDatasets']), 
  },
  created() {
    this.clear();
    this.unsubscribe ==
      this.$store.subscribe((mutation) => {
        if (mutation.type === "setQuery" && mutation.payload.searchtype === "super") {
          this.query = mutation.payload.q;
        }
      });
    axios.get(this.getApiDdlistsUrl).then(function(response) { 
      this.ddlists = response.data
      this.fillddlists();
    }.bind(this) )    
    
  },
  mounted() {
    
    
  },
  beforeDestroy() {
    this.unsubscribe();
  },  
};
</script>
<style>
input.noborder { border: 0px; width:116px }
input.noborder:focus { outline:none; }
option {
  /* wrap text in compatible browsers */
  -moz-white-space: pre-wrap;
  -o-white-space: pre-wrap;
  white-space: pre-wrap;
  /* hide text that can't wrap with an ellipsis */
  overflow: hidden;
  text-overflow: ellipsis;
  /* add border after every option */
  border-bottom: 1px solid #DDD;
}
</style>