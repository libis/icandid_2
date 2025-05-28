<template>
  <div>
    <textarea v-model="searchterm" class="textarea" rows="3" :placeholder="$ml.get('search')+'...'"></textarea>
    <button class="button is-rounded is-pulled-right" style="margin-top:10px" :title="$ml.get('savequery')"  @click="$parent.saveQuery()" v-if="getHits>0"><i class="fa fa-floppy-o" aria-hidden="true"></i></button>
    <PeriodDateSelector :selectedPeriod="selectedPeriod" :lang="lang" @change="(v) => this.selectedPeriod = v"></PeriodDateSelector> 
    <LanguageSelector :selectedLanguages="selectedLanguages" :lang="lang" @change="(v) => this.selectedLanguages = v"></LanguageSelector>
    <div class="columns" style="padding-left:2em">
      <div class="column is-2 with-scrollbar" >
        <LabelSelector :selectedLabels="selectedLabels" :lang="lang" @change="(v) => this.selectedLabels = v"></LabelSelector>
      </div>
      <div class="column is-9 with-scrollbar" style="border-left:1px solid Lightgrey;margin-right:20px">
        <div class="field is-grouped is-grouped-multiline">
          <DatasetSelector :selectedDatasets="selectedDatasets" :lang="lang" @change="(v) => this.selectedDatasets = v"></DatasetSelector>        
        </div>
      </div>
      <div class="column is-1"></div>
    </div>
  </div>
</template>
<script>
import axios from "axios";
import PeriodDateSelector from './PeriodDateSelector.vue';
import LanguageSelector from "./LanguageSelector.vue";
import LabelSelector from "./LabelSelector.vue";
import DatasetSelector from "./DatasetSelector.vue";
axios.defaults.withCredentials = true;
import { mapGetters, mapActions } from 'vuex';
export default {
  props:['lang'],
  data() {
    return {
      searchterm: "",
      selectedPeriod: "",
      selectedLabels:[],
      selectedLanguages:[],
      selectedDatasets:[],
      publications: [],      
      es_query_pub: {
        size: "0",
        aggs: {
          publications: {
            terms: {
              field: "publisher.name.keyword",
              size: "300",
              order: { _key: "asc" }
            }
          }
        }
      },
      menuitems:[
        {label:"Save query for later use",name:"save"},
        {label:"Export result as CSV", name:"exportcsv"},
        {label:"Export result as JSON-LD", name:"exportjsonld"}
      ]
    };
  },
  components: {
    PeriodDateSelector,
    LanguageSelector,
    LabelSelector,
    DatasetSelector
  },
  methods: {
    ...mapActions(["clearResultset","clearAggregations","saveQuery","exportResultCsv","exportResultJsonLd"]),
    queryObj() {

      var datasets_full = this.$parent.datasets.filter(d => this.selectedDatasets.indexOf(d.internalident) > -1 ).map(e=>e.name)

      return { 
        searchtype:"extended", 
        q: this.searchterm, 
        period:this.selectedPeriod, 
        datasets:this.selectedDatasets,
        datasets_fullname: datasets_full,
        name: this.searchterm };
    },
    keywords(){
      return this.searchterm
    },
    clear() {
      this.searchterm = "";
      this.selectedPeriod = "";
      this.selectedDatasets = this.$parent.datasets.map (x => x.internalident);
      this.selectedLanguages = this.$parent.languages.map(x => x.id);
      this.selectedLabels = this.$parent.labels.map(x => x.id);
      this.clearResultset();
      this.clearAggregations()
    },
    selectDatasets() {
      this.selectedDatasets = [];
      this.$parent.datasets.forEach(
        function(ds) {
          let conditionA = ds.languages.map(x => x.id).filter(x => this.selectedLanguages.includes(x)).length > 0;  // intersection of dataset languages and selected languages is not empty
          let conditionB = ds.labels.map(x => x.id).filter(x => this.selectedLabels.includes(x)).length > 0;  // intersection of dataset labels and selected labesl is not empty
          if (conditionA && conditionB) {
            this.selectedDatasets.push(ds.internalident);
          }
        }.bind(this)
      );

      // make unique, just to make sure it does not cause any problems down the line
      this.selectedDatasets = this.selectedDatasets.filter(
        function(value,index, self)  {
          return (self.indexOf(value) === index)
        }
      )
    }    
  },
  created() {
    
    this.unsubscribe ==
      this.$store.subscribe((mutation) => {
        if (mutation.type === "setQuery" && mutation.payload.searchtype === "extended") {
          this.searchterm = mutation.payload.q;
          this.selectedPeriod = mutation.payload.period;
          this.selectedDatasets = mutation.payload.datasets;
        }
      }); 
    this.clear();  
  },
  beforeDestroy() {
    this.unsubscribe();
  },
  computed: {...mapGetters(['getApiQueryUrl','getHits','getApiDdlistsUrl']),
    
  },
  watch: {
    selectedLanguages:
    {
            handler: 'selectDatasets'  
    },
    selectedLabels:
    {
            handler: 'selectDatasets'  
    },
    allSelectedCb: {
            handler: 'selectDatasets'  
    },
    searchterm: function(v){
      this.searchterm = v.replaceAll(" or ", " OR ").replaceAll(" and ", " AND ").replaceAll(" not ", " NOT ")
    }
  }
}
</script>
<style>
.with-scrollbar {
  max-height:150px; 
  overflow-x:hidden;
  overflow-y:auto;
  padding-top:0px
}
</style>