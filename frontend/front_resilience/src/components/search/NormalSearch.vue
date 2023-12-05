<template>
  <div>
    <textarea v-model="searchterm" class="textarea" rows="1" :placeholder="$ml.get('search')+'...'"></textarea>
    <button class="button is-rounded is-pulled-right" style="margin-top:10px" :title="$ml.get('savequery')"  @click="$parent.saveQuery()" v-if="getHits>0 && this.getPermissions.indexOf('save')>=0"><i class="fa fa-floppy-o" aria-hidden="true"></i></button>
    <div class="tabs is-toggle is-small" style="margin:0px">
      <ul>
        <li v-bind:class="{'is-active': (selectedType=='all')}">
          <a @click.prevent.stop="selectedType='all'">{{ $ml.get('allthewords') }}</a>
        </li>
        <li v-bind:class="{'is-active': (selectedType=='one')}">
          <a @click.prevent.stop="selectedType='one'">{{ $ml.get('oneofwords') }}</a>
        </li>
        <li v-bind:class="{'is-active': (selectedType=='phrase')}">
          <a @click.prevent.stop="selectedType='phrase'">{{ $ml.get('exactsentence') }}</a>
        </li>
      </ul>
    </div>
    <!-- <div class="tabs is-toggle is-small">
      <ul>
        <li v-bind:class="{'is-active': (selectedPeriod=='yesterday')}">
          <a @click.prevent.stop="selectedPeriod='yesterday'">{{ $ml.get('yesterday') }}</a>
        </li>
        <li v-bind:class="{'is-active': (selectedPeriod=='week')}">
          <a @click.prevent.stop="selectedPeriod='week'">{{ $ml.get('lastweek') }}</a>
        </li>
        <li v-bind:class="{'is-active': (selectedPeriod=='month')}">
          <a @click.prevent.stop="selectedPeriod='month'">{{ $ml.get('lastmonth') }}</a>
        </li>
        <li v-bind:class="{'is-active': (selectedPeriod=='year')}">
          <a @click.prevent.stop="selectedPeriod='year'">{{ $ml.get('oneyear') }}</a>
        </li>
        <li v-bind:class="{'is-active': (selectedPeriod=='entire')}">
          <a @click.prevent.stop="selectedPeriod='entire'">{{ $ml.get('entirearchive') }}</a>
        </li>
        <li>
          <button style="margin-bottom:20px;margin-left:5px;border:0px;background-color:transparent;font-size:16px" v-tooltip="info"><i class="fa fa-info"></i></button>
        </li>
      </ul>
      
    </div> -->

    <div class="tabs is-toggle is-small">
      <ul>
        <li v-for="t in options.types" :key="t" v-bind:class="{'is-active': (selectedTypes.includes(t))}">
          <a @click.prevent.stop="toggleTypes(t)">{{ splitWritten(t) }}</a>
        </li>
        <li>
          <button style="margin-bottom:20px;margin-left:5px;border:0px;background-color:transparent;font-size:16px" v-tooltip="info"><i class="fa fa-info"></i></button>
        </li>
      </ul>
      
    </div>
    

<!--    <div class="columns" style="padding-left:2em" v-if="$parent.languages.length > 1">
      <div class="column">
        <p class="control" v-for="language in $parent.languages" :key="'lang_'+language.id" style="margin:0px">
            <input
              class="is-checkradio is-small"
              :id="'lang_'+language.id"
              type="checkbox"
              :name="'lang_'+language.id"
              :value="language.id"
              v-model="selectedLanguages"
            />
            <label :for="'lang_'+language.id" style="white-space: nowrap">{{ language['name_'+$ml.current]}}</label>
          </p>
      </div>
    </div> -->
    <div class="columns" style="padding-left:2em">
      <div class="column">

          <input class="is-checkradio is-small" type="checkbox" v-model="onlineonly" id="onlineonly" name="onlineonly"/><label for="onlineonly">{{ $ml.get('onlineonly') }}</label>
          <button style="margin-bottom:20px;margin-left:5px;border:0px;background-color:transparent;font-size:16px" v-tooltip="infoonlineonly"><i class="fa fa-info"></i></button>
      </div>
    </div>
    <div class="columns" style="padding-left:2em">
      <div class="column is-2 with-scrollbar" >
        <!-- <div class="field" v-if="$parent.datasets.length>0" style="padding-top:4px">
          <input
            class="is-checkradio is-small"
            id="cbPublAll"
            type="checkbox"
            name="cbPublAll"
            v-model="allSelectedCb"
          />
          <label for="cbPublAll">{{ $ml.get('all') }}</label>
        </div> -->

        <p class="control" v-for="label in $parent.labels" :key="'lbl_'+label.id" style="margin:0px">
          <input
            class="is-checkradio is-small"
            :id="'lbl'+label.id"
            type="checkbox"
            :name="'lbl_'+label.id"
            :value="label.id"
            v-model="selectedLabels"
          />
          <label :for="'lbl'+label.id" style="white-space: nowrap">{{ label['name_'+$ml.current]}}</label>
        </p>  

      </div>
      <div class="column is-9 with-scrollbar" style="border-left:1px solid Lightgrey;margin-right:20px">
        <div class="field is-grouped is-grouped-multiline">
          <!--
          <p class="control" v-for="pub in publications" :key="pub" style="width:160px;margin:0px">
            <input
              class="is-checkradio is-small"
              :id="pub"
              type="checkbox"
              :name="pub"
              :value="pub"
              v-model="selectedPublications"
            />
            <label :for="pub" style="white-space: nowrap">{{pub}}</label>
          </p> -->
          <p class="control" v-for="dataset in $parent.datasets" :key="'ds_'+dataset.id" style="margin:0px">
            <input
              class="is-checkradio is-small"
              :id="'ds_'+dataset.id"
              type="checkbox"
              :name="'ds_'+dataset.id"
              :value="dataset.internalident"
              v-model="selectedDatasets"
              :alt="dataset.description"
            />
            <label :for="'ds_'+dataset.id" style="white-space: nowrap">{{ dataset.name }}</label>
          </p>          
        </div>
      </div>
      <div class="column is-1"></div>
    </div>
  </div>
</template>
<script>
import axios from "axios";
axios.defaults.withCredentials = true;
import { mapGetters, mapActions } from 'vuex';
export default {
  props:['lang'],
  data() {
    return {
      searchterm: "",
      selectedType: "all",
      selectedTypes:["Book","Article"],
//      selectedPeriod: "year",
//      selectedPublications: [],
      selectedLabels:[],
      selectedLanguages:[],
      selectedDatasets:[],
      publications: [],
      onlineonly:false,      
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
        content:this.$ml.get('typeinfotooltip'),
        html:true,
        trigger:'hover',
        autoHide:false,
      },
      infoonlineonly: {
        content:this.$ml.get('infoonlineonly'),
        html:true,
        trigger:'hover',
        autoHide:false,
      },
      options:[]
    };
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
        types:this.selectedTypes,
        onlineonly:this.onlineonly,
        //period:this.selectedPeriod, 
        //publications:this.selectedPublications,
        datasets:this.selectedDatasets,
        datasets_fullname: datasets_full,
        name: this.searchterm };
    },
    clear() {
      this.searchterm = "";
      this.selectedType = "all";
      this.selectedTypes = ["Book","Article"],
      this.onlineonly = false;
      //this.selectedPeriod = "year";
//      this.selectedPublications = [];
      this.selectedDatasets = this.$parent.datasets.map (x => x.internalident);
      //this.selectedLanguages = this.$parent.languages.map(x => x.id);
      //this.selectedLabels = this.$parent.labels.map(x => x.id);
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
    },
    toggleTypes(t) {
      if (this.selectedTypes.includes(t)) {
        if (this.selectedTypes.length > 1) {
          this.selectedTypes.splice(this.selectedTypes.indexOf(t),1)
        }
      } else {
        this.selectedTypes.push(t)
      }
    },
    splitWritten(t){
      var arr = t.split(/(?=[A-Z])/)
      for (var i=1;i<arr.length;i++) arr[i] = arr[i].toLowerCase()
      return arr.join(" ")
    }
  },
  created() {
    axios
      .get(this.getApiDdlistsUrl)
      .then(function(response) { this.options = response.data}.bind(this))
      .catch(error => {
        //console.log(error);
        if (error.response.status === 401) {
          axios
          .get(this.getApiDdlistsUrl + "/public")
          .then(function(response) { this.options = response.data}.bind(this))
        }
      })
    this.unsubscribe ==
      this.$store.subscribe((mutation) => {
        if (mutation.type === "setQuery" && mutation.payload.searchtype === "normal") {
          this.searchterm = mutation.payload.q;
          this.selectedType = mutation.payload.type;
          this.selectedTypes = mutation.payload.types;
          //this.selectedPeriod = mutation.payload.period;
          //this.selectedPublications = mutation.payload.publications;
          this.selectedDatasets = mutation.payload.datasets;
        }
      });
    this.clear();  
  },
  beforeDestroy() {
    this.unsubscribe();
  },
  computed: {...mapGetters(['getApiQueryUrl','getHits','getApiDdlistsUrl','getPermissions']),
    
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
    lang: function() {
        this.info.content = this.$ml.get('periodinfotooltip');
        this.infoonlineonly.content = this.$ml.get('infoonlineonly');
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

.tooltip {
  display: block !important;
  z-index: 10000;
}

.tooltip .tooltip-inner {
  background: black;
  color: white;
  border-radius: 8px;
  padding: 5px 10px 4px;
  font-size:12px;
  line-height: 120%;
  width:200px

}

.tooltip .tooltip-arrow {
  width: 0;
  height: 0;
  border-style: solid;
  position: absolute;
  margin: 5px;
  border-color: black;
  z-index: 1;
}

.tooltip[x-placement^="top"] {
  margin-bottom: 5px;
}

.tooltip[x-placement^="top"] .tooltip-arrow {
  border-width: 5px 5px 0 5px;
  border-left-color: transparent !important;
  border-right-color: transparent !important;
  border-bottom-color: transparent !important;
  bottom: -5px;
  left: calc(50% - 5px);
  margin-top: 0;
  margin-bottom: 0;
}

.tooltip[x-placement^="bottom"] {
  margin-top: 5px;
}

.tooltip[x-placement^="bottom"] .tooltip-arrow {
  border-width: 0 5px 5px 5px;
  border-left-color: transparent !important;
  border-right-color: transparent !important;
  border-top-color: transparent !important;
  top: -5px;
  left: calc(50% - 5px);
  margin-top: 0;
  margin-bottom: 0;
}

.tooltip[x-placement^="right"] {
  margin-left: 5px;
}

.tooltip[x-placement^="right"] .tooltip-arrow {
  border-width: 5px 5px 5px 0;
  border-left-color: transparent !important;
  border-top-color: transparent !important;
  border-bottom-color: transparent !important;
  left: -5px;
  top: calc(50% - 5px);
  margin-left: 0;
  margin-right: 0;
}

.tooltip[x-placement^="left"] {
  margin-right: 5px;
}

.tooltip[x-placement^="left"] .tooltip-arrow {
  border-width: 5px 0 5px 5px;
  border-top-color: transparent !important;
  border-right-color: transparent !important;
  border-bottom-color: transparent !important;
  right: -5px;
  top: calc(50% - 5px);
  margin-left: 0;
  margin-right: 0;
}

.tooltip.popover .popover-inner {
  background: #f9f9f9;
  color: black;
  padding: 24px;
  border-radius: 5px;
  box-shadow: 0 5px 30px rgba(black, .1);
}

.tooltip.popover .popover-arrow {
  border-color: #f9f9f9;
}

.tooltip[aria-hidden='true'] {
  visibility: hidden;
  opacity: 0;
  transition: opacity .15s, visibility .15s;
}

.tooltip[aria-hidden='false'] {
  visibility: visible;
  opacity: 1;
  transition: opacity .15s;
}
</style>