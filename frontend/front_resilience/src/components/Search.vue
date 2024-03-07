<template>
  <div class="block">
    <SearchForm ref="searchForm"></SearchForm>
    <ActionBar></ActionBar>
    <!-- <div class="content" v-bind:class="{'is-hidden': (!(getHits>0 && mode=='visualisation'))}">
      <Visualisations ref="visualisations"></Visualisations>
    </div> -->

    <div class="columns">
      <div class="column is-2" v-bind:class="{'is-hidden': (!(visiblefilters && mode=='result'))}">
        <div class="has-text-weight-semibold">{{ $ml.get('sortby') }}</div>
        <div class="select" style="font-size:1rem">
          <select v-model="sorting">
            <option value="relevance">{{ $ml.get('relevance')}} </option>
            <option value="title">{{  $ml.get('title') }}</option>
            <option value="datePublishedDesc">{{ $ml.get('datePublishedDesc') }}</option>
            <option value="datePublishedAsc">{{ $ml.get('datePublishedAsc') }}</option>
            <!-- <option value="datePublished">{{ $ml.get('publicationdate') }}</option> -->
            <!-- <option value="author">{{ $ml.get('author') }}</option> -->
          </select>
        </div>
        <Filters ref="filters"></Filters>
      </div>
      <div class="is-divider-vertical" style="padding:0" v-bind:class="{'is-hidden': (!(visiblefilters && mode=='result'))}"></div>
      <div class="column is-5" v-bind:class="{'is-hidden': (!(getHits>0 && mode=='result'))}">
        <ResultList ref="resultlist"></ResultList>
      </div>
      <div class="is-divider-vertical" style="padding:0" v-bind:class="{'is-hidden': (!(getHits>0 && mode=='result'))}"></div>
      <div class="column is-5" v-bind:class="{'is-hidden': (!(getHits>0 && mode=='result'))}">
        <Detail :activeResult="getActiveResult" :currentLang="$ml.current" :highlights="true"></Detail>
      </div>

      <div class="column is-8" v-bind:class="{'is-hidden': (!(getHits==0 && mode=='result'))}">
        <article class="message is-warning" style="margin-left:150px">
          <div class="message-header">
            <p>{{ $ml.get('notice') }}</p>
          </div>
          <div class="message-body">
            {{ $ml.get('noresultsfound') }}
          </div>
        </article>
      </div>



    </div>
</div>
</template>
<script>
import SearchForm from "./search/SearchForm.vue";
import Filters from "./search/Filters.vue";
import ResultList from "./search/ResultList.vue";
import Detail from "./search/Detail.vue";
import ActionBar from "./search/ActionBar.vue";
//import Visualisations from "./search/Visualisations.vue";
import axios from "axios";
import { mapGetters, mapActions } from "vuex";

axios.defaults.withCredentials = true;

export default {
  components: {
    SearchForm,
    Filters,
    ResultList,
    Detail,
    ActionBar,
//    Visualisations
  },
  data() {
        return {
          mode:'result'
        }
  },
  methods: {
    ...mapActions(["setSearchStatus","addResults","setSorting","clearResultset","setNav","setHits","setAggregations","clearAggregations","setHistory","setElasticQuery"]),
    extendResultset() {
      if (this.getResultset.length < this.getHits) {
        this.setNav('next');
        this.setSearchStatus(true);
        axios
          .post(this.getApiSearchUrl, this.$store.state.search.queryObj)
          .then(res => {
            this.processData(res)
          })
          .catch(error => {

            if (error.response.status === 401) {
              axios
                .post(this.getApiSearchUrl+"/public", this.$store.state.search.queryObj)
                .then(res => {
                  this.processData(res)
                })
                .catch(error => {
                  this.setSearchStatus(false);
                  console.log(error);
                })
            } else {
              this.setSearchStatus(false);
              //console.log(error);
            }
          });      
      }
    },
    selectall() {
      this.$refs.resultlist.selectall();
    },
    setMinMaxDatePublished(buckets) {
      var min = ''
      var max = ''
      for (var i in buckets) {
        if (buckets[i].key == 'min_datePublished') {
          min = buckets[i].doc_count
        }
        if (buckets[i].key == 'max_datePublished') {
          max = buckets[i].doc_count
        }
      }
      this.$refs.filters.setMinMaxDatePublished(min,max);
    },
    processData(res) {
      this.addResults(res.data.hits.hits)
      this.setAggregations(res.data.aggregations)
      this.setMinMaxDatePublished(res.data.aggregations.minmax_datePublished.buckets)
      this.setHistory(res.data.history)
      this.setSearchStatus(false);
      this.setHits(res.data.hits.total.value)
    }
  },
  computed: {
    ...mapGetters(["getApiSearchUrl","getResultset","getSorting","getHits","getAggregations","getActiveResult","getElasticQuery"]),
    sorting: {
      get() {
        return this.getSorting
      },
      set(value) {
        this.setSorting(value)
      }
    },
    visiblefilters() {
      var som = 0
      for (const value of Object.values(this.getAggregations)){

        som += value.buckets.length
        
      }

      return (som != 0)
    }
  },
  created() {
    this.unsubscribe ==
      this.$store.subscribe((mutation, state) => {
        if (mutation.type === 'clearResultset') {
          if (this.$refs.visualisations != undefined) { this.$refs.visualisations.clear(); }
        }
        if (mutation.type === "setQuery"){
          this.clearAggregations()
        }
        if (mutation.type === "setQuery" || mutation.type === "setSorting"  || mutation.type === "setFilters") {
          this.setSearchStatus(true);
          this.setNav('first');
          this.$refs.searchForm.selectedTab = this.$store.state.search.queryObj.searchtype;
          //this.$gtag.event('search', {'event_category' : 'engagement' , 'event_label': JSON.stringify(state.search.queryObj) })
          axios
            .post(this.getApiSearchUrl, state.search.queryObj)
            .then(res => {
              this.clearResultset()
              this.processData(res)
              this.setElasticQuery(res.data.elastic_query)      
            })
            .catch(error => {
              if (error.response.status === 401) {
                axios
                  .post(this.getApiSearchUrl+"/public", state.search.queryObj)
                  .then(res => {
                    this.clearResultset()
                    this.processData(res)
                    this.setElasticQuery(res.data.elastic_query)      
                  })
                  .catch(error => {
                    this.setSearchStatus(false);
                    console.log(error);
                  })
              } else {
                this.setSearchStatus(false);
                //console.log(error);
              }
            });      
        }
      });
  },
  beforeDestroy() {
    this.unsubscribe();
  }

};
</script>