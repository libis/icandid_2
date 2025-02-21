<template>
  <div>
    <textarea v-model="searchterm" class="textarea" rows="1" :placeholder="$ml.get('search')+'...'"></textarea>
    <button class="button is-rounded is-pulled-right" style="margin-top:10px" :title="$ml.get('savequery')"  @click="$parent.saveQuery()" v-if="getHits>0"><i class="fa fa-floppy-o" aria-hidden="true"></i></button>
    <TypeSelector :selectedType="selectedType" :lang="lang" @change="(v) => this.selectedType = v"></TypeSelector>
    <PeriodSelector :selectedPeriod="selectedPeriod" :lang="lang" @change="(v) => this.selectedPeriod = v"></PeriodSelector>
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
import PeriodSelector from './PeriodSelector.vue';
import LanguageSelector from "./LanguageSelector.vue";
import LabelSelector from "./LabelSelector.vue";
import DatasetSelector from "./DatasetSelector.vue";
import TypeSelector from "./TypeSelector.vue";
axios.defaults.withCredentials = true;
import { mapGetters, mapActions } from 'vuex';


export default {
  props:['lang'],
  data() {
    return {
      searchterm: "",
      selectedType: "all",
      selectedPeriod: "entire",
//      selectedPublications: [],
      selectedLabels:[],
      selectedLanguages:[],
      selectedDatasets:[],
      publications: [],      
      // allSelectedCb: false,
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
      ],
      info: {
        content:this.$ml.get('periodinfotooltip'),
        html:true,
        trigger:'hover',
        autoHide:false,
      }
    };
  },
  components: {
    TypeSelector,
    PeriodSelector,
    LanguageSelector,
    LabelSelector,
    DatasetSelector
  },
  methods: {
    ...mapActions(["clearResultset","clearAggregations","saveQuery","exportResultCsv","exportResultJsonLd"]),
    queryObj() {
      // make sure only active datasets are selected (cfr. difference media/collections)
      // var ds_id = this.$parent.datasets.map(x => x.internalident);
      // for (var i=0; i<this.selectedDatasets.length; i++) {
      //   if (ds_id.indexOf(this.selectedDatasets[i]) < 0 ) {
      //     this.selectedDatasets.splice(i,1);
      //     i--;
      //   }
      // }

      var datasets_full = this.$parent.datasets.filter(d => this.selectedDatasets.indexOf(d.internalident) > -1 ).map(e=>e.name)

      return { 
        searchtype:"normal", 
        q: this.searchterm, 
        type:this.selectedType, 
        period:this.selectedPeriod, 
        //publications:this.selectedPublications,
        datasets:this.selectedDatasets,
        datasets_fullname: datasets_full,
        name: this.searchterm };
    },
    clear() {
      this.searchterm = "";
      this.selectedType = "all";
      this.selectedPeriod = "entire";
//      this.selectedPublications = [];
      this.selectedDatasets = this.$parent.datasets.map (x => x.internalident);
      this.selectedLanguages = this.$parent.languages.map(x => x.id);
      this.selectedLabels = this.$parent.labels.map(x => x.id);
      // this.allSelectedCb = false;
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

      // if (this.allSelectedCb) {
      //   for (var i=0; i<this.$parent.datasets.length; i++) {
      //     this.selectedDatasets.push(this.$parent.datasets[i].internalident);
      //   }
      // } else { 
      //   for (i=0; i<this.$parent.datasets.length; i++) {
      //     for(var j=0; j<this.$parent.datasets[i].languages.length; j++) {
      //       if ( this.selectedLanguages.indexOf(this.$parent.datasets[i].languages[j].id) >= 0 ) {
      //         this.selectedDatasets.push(this.$parent.datasets[i].internalident);
      //       }
      //     }
      //   }
      //   for (i=0; i<this.$parent.datasets.length; i++) {
      //     for(j=0; j<this.$parent.datasets[i].labels.length; j++) {
      //       if ( this.selectedLabels.indexOf(this.$parent.datasets[i].labels[j].id) >= 0 ) {
      //         this.selectedDatasets.push(this.$parent.datasets[i].internalident);
      //       }
      //     }
      //   }
      // }

      // make unique, just to make sure it does not cause any problems down the line
      this.selectedDatasets = this.selectedDatasets.filter(
        function(value,index, self)  {
          return (self.indexOf(value) === index)
        }
      )
    }    
  },
  created() {
    
/*    
    axios.get(this.getApiDdlistsUrl).then(function(response) { 
      this.publications = response.data.publishers
    }.bind(this)
    )     
*/

    /*
    axios
      .post(this.getApiQueryUrl, this.es_query_pub)
      .then(res => {
        this.publications = res.data.aggregations.publications.buckets;
      })
      .catch(error => console.log(error));
 */
    this.unsubscribe ==
      this.$store.subscribe((mutation) => {
        if (mutation.type === "setQuery" && mutation.payload.searchtype === "normal") {
          this.searchterm = mutation.payload.q;
          this.selectedType = mutation.payload.type;
          this.selectedPeriod = mutation.payload.period;
          //this.selectedPublications = mutation.payload.publications;
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