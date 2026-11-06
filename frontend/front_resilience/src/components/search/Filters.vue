<template>
    <div>
      <FilterList ref="type"       type="type"       :bucket="buckets('type')"       :value="selectedFilters.type"       @change="msChanged('type', $event)"></FilterList>
      <FilterList ref="collection" type="collection" :bucket="buckets('collection')" :value="selectedFilters.collection" @change="msChanged('collection', $event)" ></FilterList>
      <FilterList ref="inlanguage" type="inlanguage" :bucket="buckets('inlanguage')" :value="selectedFilters.inlanguage" @change="msChanged('inlanguage', $event)"></FilterList>

      <div class="columns">
        <div class="column" style="padding:0.7rem">
          <div style="font-size:15px">{{ $ml.get('range_from') }}</div>
          <input class="input range" type="number" ref="datePublished_from" v-model="selectedFilters.datePublished_from" :min="min_datePublished" :max="max_datePublished" @change="msChanged('datePublished_from', $event.target.value)">
        </div>
        <div class="column">
          <div style="font-size:15px">{{ $ml.get('range_to') }}</div>
          <input class="input range" type="number" ref="datePublished_until" v-model="selectedFilters.datePublished_until" :min="min_datePublished" :max="max_datePublished" @change="msChanged('datePublished_until', $event.target.value)">
        </div>
      </div>

      <div style="font-size:15px">{{ $ml.get('author') }}</div>
      <Multiselect
        v-model="getSelectedFilters.author"
        :options="options('author')"
        mode="tags"
        :close-on-select="false"
        :searchable=true
        :allow-empty="false"
        @change="msChanged('author', $event)"
      />

      <div style="font-size:15px">{{ $ml.get('contributor') }}</div>
      <Multiselect
        v-model="getSelectedFilters.contributor"
        :options="options('contributor')"
        mode="tags"
        :close-on-select="false"
        :searchable=true
        :allow-empty="false"
        @change="msChanged('contributor', $event)"
      />
      <div style="font-size:15px">{{ $ml.get('publisher') }}</div>
      <Multiselect
        v-model="getSelectedFilters.publisher"
        :options="options('publisher')"
        mode="tags"
        :close-on-select="false"
        :searchable=true
        :allow-empty="false"
        @change="msChanged('publisher', $event)"
      />
      <div style="font-size:15px">{{ $ml.get('locationcreated') }}</div>
      <Multiselect
        v-model="getSelectedFilters.locationcreated"
        :options="options('locationcreated')"
        mode="tags"
        :close-on-select="false"
        :searchable=true
        :allow-empty="false"
        @change="msChanged('locationcreated', $event)"
      />
       </div>
      
</template>
<script>
import { mapActions, mapGetters } from 'vuex';
import FilterList from './FilterList.vue';

import Multiselect from '@vueform/multiselect/dist/multiselect.vue2.js'
import '@vueform/multiselect/themes/default.css'
export default {
    components: {
      FilterList,
      Multiselect
    },
    data() {
      return {
        nbspace:"&nbsp;",
        min_datePublished:0,
        max_datePublished:0,
        selectedFilters:[]
      }
    },
  
  computed: {
    ...mapGetters(["getAggregations","getFilters","getSelectedFilters","getSearchRequest"])
  },
  methods: {
      ...mapActions(['setFilters','setFilterFromHome','setSelectedFilters']),
      buckets(type) {
        var aggs = this.getAggregations
        if ( aggs[type] != undefined) {
            return  aggs[type].buckets
        }
        return null
      },
      isPresent(type) {
        var aggs = this.getAggregations
        return ( aggs[type] != undefined && aggs[type].buckets.length > 0)
      },
      setMinMaxDatePublished(min,max) {
        if (min == "9999-01-01 00:00:00") {
          this.min_datePublished = ""
          this.datePublished_from= ""
        } else {
          this.min_datePublished = min.substr(0,4);
          this.datePublished_from= min.substr(0,4);
        }
        if (max == "0001-01-01 00:00:00") {
          this.max_datePublished = ""
          this.datePublished_until = ""

        } else {
          this.max_datePublished = max.substr(0,4)
          this.datePublished_until = max.substr(0,4);
        }
      },
      splitWritten(t){
        var arr = t.split(/(?=[A-Z])/)
        for (var i=1;i<arr.length;i++) arr[i] = arr[i].toLowerCase()
        return arr.join(" ")
      },
      filterlistClicked(arg){
        if (arg.checked) {
          this.add(arg.value)
        } else {
          this.remove(arg.value)
        }
      },
      clearFilters() {
        this.setSelectedFilters({
            type:[],
            collection:[],
            inlanguage:[],
            datePublished_from:"",
            datePublished_until:"",
            author:[],
            contributor:[],
            publisher:[],
            locationcreated:[]
        }) 
      },
      options(name) {
        if (this.buckets(name) == null) {
          return null
        }
        return this.buckets(name).map(v => (v.key))
      },
      msChanged(filterName, value) {
        if (value == undefined) {
          value = []
        }
        const newFilters = {
          ...this.selectedFilters,
          [filterName]: value
        }
        this.setSelectedFilters(newFilters)
      }

  },
  mounted() {

  },
  created() {
      this.unsubscribe ==
          this.$store.subscribe((mutation) => {
              if (mutation.type == "setSearchStatus") {
                  this.selectedFilters = JSON.parse(JSON.stringify(this.getSearchRequest.sf))
          }
      });
  }  
}
</script>
<style scoped>
.tablerow {
    display:table-row;
}
.tablecell {
    display:table-cell;
}
.tablecell_align {
    display:table-cell;
    vertical-align:top;
    width:15px;
}
.hidden {
  display:none
}
.range {
  border: 1px solid #ADBDCC;
  border-radius: 5px;
}
</style>
<style>
.multiselect {
  border: 1px solid #ADBDCC;
  border-radius: 5px;
  margin-bottom:15px
}
.multiselect-clear {
  display:none
}
</style>