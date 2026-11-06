<template>
  <div style="margin-bottom:20px">
    <div v-if="getActiveRecord != null" class="breadcrumbs">
      <div @click="setActiveRecord(null)" style="cursor:pointer;padding-top:2px">
        <Icon icon="arrow-left" color="#0625AC" width="14"></Icon>
      </div><a href="/#/" @click="setActiveRecord(null)">{{ $ml.get('home') }}</a> / <a
        @click="setActiveRecord(null)">{{ $ml.get('searchresult') }}</a>
      / <span>{{ getActiveRecord["name"]["@value"] }}</span>
    </div>

    <div v-if="getActiveRecord != null" style="border-top:1px solid #F0F0F0;display:flex">
      <div style="padding-top:40px;padding-right:20px">
        <Detail :data="getActiveRecord"></Detail>
      </div>
      <div style="border-left:1px solid #F0F0F0;padding-top:25px;padding-left:18px">
        <div style="display:flex;padding-top:15px;padding-bottom:15px;width:auto;border-bottom:1px solid #F0F0F0">
          <div class="actionbutton" @click="showShare = !showShare">
            <Icon icon="share" color="#001A72" width="15"></Icon><span style="margin-left:5px;color:#001A72">{{ $ml.get('share') }}</span>
          </div>
          <div v-if="showShare" class="contextmenu">
            <SocialShare :title="unpack(getActiveRecord.name)" :url="unpack(getActiveRecord.url)" :description="unpack(getActiveRecord.description)"></SocialShare> 
          </div>


          <div class="actionbutton" style="background-color: inherit;border:1px solid #ADBDCC" v-if="this.getPermissions.indexOf('save') >= 0" @click="toggleShowMenu()">
            <Icon icon="star" color="#001A72" width="15"></Icon><span style="margin-left:5px;color:#455B71">{{
              $ml.get('favourite') }}</span>
          </div>
          <div v-if="showMenu" class="contextmenu">
            <div style="color:Gray;padding:8px 12px;">{{$ml.get('savetocollection')}}</div>
            <div v-for="shelf, key in shelves" :key="'shelf_'+key" class="menuitem" @click="addToFavourite(shelf.name)">{{shelf.name}}</div>
          </div>


        </div>
        <Citation :data="getActiveRecord"></Citation>
      </div>
    </div>
    <div :style="(getActiveRecord == null ? 'display:block' : 'display:none')" class="block">
      <SearchForm ref="searchForm"></SearchForm>
      <div v-bind:class="{ 'is-hidden': (!(visiblefilters && mode == 'result')) }"
        style="font-size:15px;border-bottom:1px solid #dbdbdb;padding-bottom:4px">
        <FilterBar></FilterBar>
      </div>
      
      <div class="columns" style="margin-top:0px">
        <div class="column is-3" v-bind:class="{ 'is-hidden': (!(visiblefilters && mode == 'result')) }">
          <Filters ref="filters"></Filters>
        </div>
        <div class="column is-9" style="padding-left:0px;border-left:1px solid #DBDBDB"
          v-bind:class="{ 'is-hidden': (!(getHits > 0 && mode == 'result')) }">

          <ResultList ref="resultlist"></ResultList>
        </div>
        <div class="column is-12" style="padding-left:30%;padding-top:10%"
          v-bind:class="{ 'is-hidden': (!(getHits == 0 && mode == 'result')) }">
          {{ $ml.get('noresultsfound') }}
        </div>



      </div>
    </div>
  </div>
</template>
<script>
import SearchForm from "./search/SearchForm.vue";
import Filters from "./search/Filters.vue";
import ResultList from "./search/ResultList.vue";
import Detail from "./search/Detail.vue";
import Icon from "./helpers/Icon.vue"
import Citation from "./helpers/Citation.vue"
import FilterBar from "./helpers/FilterBar.vue"
import SocialShare from './helpers/SocialShare.vue'
import axios from "axios";
import { mapGetters, mapActions } from "vuex";

axios.defaults.withCredentials = true;

export default {
  components: {
    SearchForm,
    Filters,
    ResultList,
    Detail,
    Icon,
    Citation,
    FilterBar,
    SocialShare
  },
  data() {
    return {
      mode: 'result',
      showMenu:false,
      showShare:false,
      shelves:null
    }
  },
  methods: {
    ...mapActions(["setSearchStatus", "addResults", "setSorting", "clearResultset", "setNav", "setHits", "setAggregations", "clearAggregations", "clearFilters", "setHistory", "setElasticQuery", "clearPage", "setActiveRecord"]),
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
      this.$refs.filters.setMinMaxDatePublished(min, max);
    },
    processData(res) {
      this.addResults(res.data.hits.hits)
      this.setAggregations(this.aggsMerge(res.data.aggregations, res.data.allAggregations))
      this.setMinMaxDatePublished(res.data.aggregations.minmax_datePublished.buckets)
      this.setHistory(res.data.history)
      this.setSearchStatus(false);
      this.setHits(res.data.hits.total.value)
    },
    aggsMerge(aggs, fullFilters) {
      // await this.fullFilters
      let merged = JSON.parse(JSON.stringify(aggs))
      const allowed = ["type", "additionaltype", "collection", "dataset", "provider"]
      allowed.forEach(function (key) {
        if (fullFilters[key].buckets != undefined && merged[key].buckets != undefined) {
          fullFilters[key].buckets.forEach(function (item) {
            if (merged[key].buckets.filter((e) => e.key == item.key).length == 0) {
              merged[key].buckets.push(item)
            }
          })
        }
      });
      return merged
    },
    searchDescription() {
      if (this.getSearchRequest.name != undefined) {
        return this.getSearchRequest.name
      }
      return ""
    },
    setActive(rec) {
      this.setActiveRecord(rec)
    },
    clearActive() {
      this.setActiveRecord(null)
    },
    toggleShowMenu() {
        this.showMenu = !this.showMenu
        axios.get(this.getApiShelvesUrl)
        .then(res => {
          console.log(res.data)
            this.shelves = res.data
        })
    },
    addToFavourite(shelf) {
        var obj = {shelf: shelf, values: [this.getActiveRecord["@id"]]}
        axios
        .post(this.getApiProfileUrl + '/item', obj)
        .then(
            this.showMenu = false
        )
        .catch(error => {
            console.log(error);
        });
    },
    stopLoading() {
        this.setSearchStatus(false)
    },
    unpack(value) {
      if (value == undefined) {
        return null
      }
      if (this.isType(value) == 'string') {
        if (this.isDate(value)) {
          return this.formatDate(value)
        }
        return value
      }
      if (this.isType(value) == 'object') {
        return this.unpack_object(value)
      }
      if (this.isType(value) == 'array') {
        return this.unpack_array(value).join("\n")
      }
    },
    unpack_array(value) {
      return value.map((v) => this.unpack(v))
    },
    unpack_object(value) {
      if (value["@value"] != undefined) {
        return value["@value"]
      }
      if (value["@type"] != undefined && value["name"] != undefined) {
        return this.unpack(value["name"])
      }
      return value   // just in case there is a case I didn't handle, at least it's clear that something is going on :-)
    },
    isType(value) {
      if (Array.isArray(value)) {
          return 'array'
      } else if (typeof value === "string") {
          return 'string'
      } else if (value !== null && typeof value === "object") {
          return 'object'
      }
    },
    isDate(value){
      const regex = /(^([0-9]){4}-([0-9]){2}-([0-9]){2}T([0-9]){2}:([0-9]){2}:([0-9]){2}.([0-9])+Z)/g;
      return value.match(regex);      
    }
  },
  computed: {
    ...mapGetters(["getApiSearchUrl", "getResultset", "getSorting", "getHits", "getAggregations", "getActiveResult", "getElasticQuery", "getSearchRequest", "getPage", "getPagesize", "getFilters", "getActiveRecord","getPermissions","getFilters","getSelectedFilters","getApiShelvesUrl","getApiProfileUrl","getSearchStatus"]),
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
      for (const value of Object.values(this.getAggregations)) {
        if (value.buckets != undefined) {
          som += value.buckets.length
        }
      }

      return (som != 0)
    }
  },
  mounted() {

  },
  created() {
    this.unsubscribe ==
      this.$store.subscribe((mutation, state) => {
        if (mutation.type === 'clearResultset') {
          if (this.$refs.visualisations != undefined) { this.$refs.visualisations.clear(); }
        }
        if (mutation.type === "setQuery") {
          this.clearAggregations()
          this.$refs.filters.clearFilters()
          this.clearPage()
        }
        if (mutation.type === "setSorting" || mutation.type === "setFilters" || mutation.type === "setSelectedFilters") {
          this.clearPage()
        }
        if (mutation.type === "setQuery" || 
            mutation.type === "setSorting" || 
            mutation.type === "setFilters" || 
            mutation.type === "setSelectedFilters" || 
            mutation.type == "setPage") {
          if (this.getSearchStatus) {  return  } // prevent double search-requests
          this.setSearchStatus(true);
          const timeoutHandle = window.setTimeout(this.stopLoading, 6000)  // if search takes too long release UI
          
          this.setNav('first');
          this.$refs.searchForm.selectedTab = this.$store.state.search.queryObj.searchtype;
          //this.$gtag.event('search', {'event_category' : 'engagement' , 'event_label': JSON.stringify(state.search.queryObj) })
          var queryObj = JSON.parse(JSON.stringify(state.search.queryObj));
          queryObj = Object.assign(queryObj, { ['from']: (this.getPage - 1) * this.getPagesize });
          queryObj = Object.assign(queryObj, { ['pagesize']: this.getPagesize });

          if (queryObj.searchtype == 'simple') {
            this.$refs['searchForm'].$refs['simple'].searchterm = queryObj.q
          }
          if ((queryObj.q == "" || queryObj.q.length==0) && (queryObj.f == "" || queryObj.f.length == 0)) {
            this.setSearchStatus(false);
            window.clearTimeout(timeoutHandle);
            return ""
          }          
          
          axios
            .post(this.getApiSearchUrl, queryObj)
            .then(res => {
              this.clearResultset()
              this.processData(res)
              this.setElasticQuery(res.data.elastic_query)

              this.$refs.filters.$refs.collection.setChecked(this.getFilters)


            })
            .catch(error => {
              if (error.response != undefined && error.response.status === 401) {
                axios
                  .post(this.getApiSearchUrl + "/public", queryObj)
                  .then(res => {
                    this.clearResultset()
                    this.processData(res)
                    this.setElasticQuery(res.data.elastic_query)
                  })
                  .catch(error => {
                    this.setSearchStatus(false);
                    window.clearTimeout(timeoutHandle);
                    console.log(error);
                  })
              } else {
                this.setSearchStatus(false);
                window.clearTimeout(timeoutHandle);
              }
            });
        }
      });
  },
  beforeDestroy() {
    this.unsubscribe();
  },
  watch: {
    getActiveRecord: function () {
      window.scrollTo(0, 0);
    },
    '$route': function () { 
      if (this.$route.name != 'Search') {
        this.setActiveRecord(null) ;
        this.$refs.resultlist.clearSelected();
      }
    }
  }
}
</script>
<style scoped>
.actionbutton {
  width: auto;
  height: 32px;
  background-color: #E5EEFF;
  border-radius: 5px;
  text-align: center;
  cursor: pointer;
  margin-right: 12px;
  vertical-align: middle;
  padding-top: 1px;
  margin-right: 8px;
  display: flex;
  padding-left: 5px;
  padding-right: 5px;
  padding-top: 8px;
  font-weight: 600;
  font-size: 12px;
  line-height: 16px;
  letter-spacing: 0%;
}

.breadcrumbs {
  font-weight: 400;
  font-size: 12px;
  line-height: 13px;
  letter-spacing: 0px;
  margin-bottom: 16px;
  display: flex
}

.breadcrumbs a {
  margin-left: 10px;
  margin-right: 10px
}

.breadcrumbs span {
  margin-left: 10px;
}

.contextmenu {
  position:absolute;
  margin-top:15px;
  margin-left:30px;
  background:white;
  border:1px solid #ccc;
  border-radius:4px;
  box-shadow:0 2px 8px rgba(0,0,0,.2);
  min-width:150px;
  font-size:13px;
  font-weight: 400;
  font-style: Regular;  
}

.menuitem {
  padding:8px 12px;
  cursor:pointer;
}

.menuitem:hover {
  background:#f5f5f5;
}
</style>