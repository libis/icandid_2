<template>
    <div>
        <br />
        <div class="has-text-weight-semibold">{{ $ml.get('filters')}}</div>

        <div class="content">
            <span>{{ $ml.get('publicationdate') }}</span><br/>
            <div class="content is-small" style="margin:0px">{{ $ml.get('from') }} :</div><input class="input" type="number" ref="datePublished_from" v-model="datePublished_from" :min="min_datePublished" :max="max_datePublished" @change="setrange();">
            <div class="content is-small" style="margin:0px">{{ $ml.get('until') }} :</div><input class="input" type="number" ref="datePublished_until" v-model="datePublished_until" :min="min_datePublished" :max="max_datePublished" @change="setrange();">
        </div>

        <div class="content" v-for="(type,l) in filterOrder" :key="type">
            <span v-if="isPresent(type)">{{ $ml.get(type) }}</span>
            <ul class="content is-small" style="list-style-type: none; margin: 8px">
                <li v-for="(v,k) in buckets(type)" :key="k" :class="(k>=filterSize && !filterMore[l])?'hidden':''">
                    <div class="field">
                    <input
                        type="checkbox"
                        class="is-checkradio is-small"
                        :id="'f_'+type+'_'+v.key"
                        v-model="theFilters"
                        :value="type+':'+v.key"
                        style="padding-bottom:30px"
                    />
                    <label class="tablecell" :for="'f_'+type+'_'+v.key" v-html="makelabel(v,type)"></label>
                    </div>
                </li>
            </ul>
            <div v-if="(isPresent(type) && buckets(type).length > filterSize)" style="width:100%; font-weight: Bold; font-size:10px; text-align: center;">
              <a @click="toggle(l)">{{ (filterMore[l]?$ml.get('showless'):$ml.get('showmore')) }}</a>
            </div>
        </div>
        <div class="content">
            <span>{{ $ml.get('online') }}</span>
            <ul class="content is-small" style="list-style-type: none; margin: 8px">
                <li>
                    <div class="field">
                    <input
                        type="checkbox"
                        class="is-checkradio is-small"
                        id="f_onlineonly"
                        v-model="theFilters"
                        value="onlineonly:true"
                        style="padding-bottom:30px"
                    />
                    <label class="tablecell" for="f_onlineonly">{{ $ml.get('onlineonly') }}</label>
                    </div>
                </li>
            </ul>
        </div>

        <div class="content">
            <span>{{ $ml.get('metadatapublicationdate') }}</span>
            <ul class="content is-small" style="list-style-type: none; margin: 8px">
              <li v-for="(v,k) in sdDatePublishList" :key="k">
                    <div class="field">
                    <input
                        type="checkbox"
                        class="is-checkradio is-small"
                        :id="'f_sdDatePublished_'+k"
                        v-model="theFilters"
                        :value="'sdDatePublished:'+v"
                        style="padding-bottom:30px"
                    />
                    <label class="tablecell" :for="'f_sdDatePublished_'+k">{{ $ml.get(v) }}</label>
                    </div>
                </li>
            </ul>
        </div>



      </div>
</template>
<script>
import { mapActions, mapGetters } from 'vuex';
export default {
    data() {
      return {
        nbspace:"&nbsp;",
        filterOrder:["author","subject","inlanguage","type","contributor","provider","locationcreated","publisher","dataset","digitalrepresentation"],
        filterMore:[false,false,false,false,false,false,false,false,false,false,false,false,false,false,false,false,false,false,false,false,false,false,false,false,false,false,false,false,false,false,false,false,false],
        filterSize:6,
        sdDatePublishList:['lastweek','last2week','lastmonth','last3month','last6month','lastyear'],
        min_datePublished:0,
        max_datePublished:0,
        datePublished_from:'',
        datePublished_until:''
      }
    },
  computed: {
    ...mapGetters(["getAggregations","getFilters"]),
    theFilters: {
        get() { return this.getFilters },
        set(val) { this.setFilters(val) }
    }
  },
  methods: {
      ...mapActions(['setFilters']),
      makelabel(v,type) {
          if (type == "retweet") {
            return this.$ml.get(v.key) + '&nbsp;('+ v.doc_count + ')'
          } else {
            if (type == "type") {
              return this.splitWritten(v.key) + '&nbsp;('+ v.doc_count + ')'
            } else {
              return v.key + '&nbsp;('+ v.doc_count + ')'
            }
          }
      },
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
      toggle(i) {
        this.filterMore[i] = !this.filterMore[i]
        this.$forceUpdate();
      },
      resetFilterMore() {
        this.filterMore.fill(false);
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
      setrange() {        
        if (this.datePublished_from != '' && this.datePublished_until != '') {
          var idx = this.theFilters.indexOf(this.theFilters.filter(o => o.startsWith('publicationdaterange'))[0])
          if (idx > -1) {
            this.theFilters[idx] = 'publicationdaterange:' + this.datePublished_from + '-01-01 ' + this.datePublished_until + '-12-31'
          } else {
            this.theFilters.push('publicationdaterange:' + this.datePublished_from + '-01-01 ' + this.datePublished_until + '-12-31')
          }
          this.setFilters(this.theFilters)
        }
      },
      splitWritten(t){
        var arr = t.split(/(?=[A-Z])/)
        for (var i=1;i<arr.length;i++) arr[i] = arr[i].toLowerCase()
        return arr.join(" ")
      }
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
</style>