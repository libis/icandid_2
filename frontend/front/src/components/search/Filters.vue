<template>
    <div>
        <br />
        <div class="has-text-weight-semibold">{{ $ml.get('filters')}}</div>
        <div class="content is-capitalized" v-for="(type,l) in filterOrder" :key="type">
            <span v-if="isPresent(type)">{{ $ml.get(type) }}</span>
            <ul class="content is-small" style="list-style-type: none; margin: 8px">
                <li v-for="(v,k) in buckets(type)" :key="k" :class="(k>=filterSize && !filterMore[l])?'hidden':''">
                    <div class="field">
                    <input
                        type="checkbox"
                        class="is-checkradio is-small"
                        :id="'f'+v.key"
                        v-model="theFilters"
                        :value="type+':'+v.key"
                        style="padding-bottom:30px"
                    />
                    <label class="tablecell" :for="'f'+v.key" v-html="makelabel(v,type)"></label>
                    </div>
                </li>
            </ul>
            <div v-if="(isPresent(type) && buckets(type).length > filterSize)" style="width:100%; font-weight: Bold; font-size:10px; text-align: center;">
              <a @click="toggle(l)">{{ (filterMore[l]?$ml.get('showless'):$ml.get('showmore')) }}</a>
            </div>
        </div>
    </div>
</template>
<script>
import { mapActions, mapGetters } from 'vuex';
export default {
    data() {
      return {
        nbspace:"&nbsp;",
        filterOrder:["provider","author","publisher","aggregator","color","edition","retweets"],
        filterMore:[false,false,false,false,false,false,false],
        filterSize:6
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
          } else if (type == "color") {
            return '<div class="color-container"><div title="' + v.key + '" class="colorbox2" style="background-color:' + v.key + '"></div><div>' + this.$func.getColorName(v.key)  + '&nbsp;('+ v.doc_count + ')' + '</div></div>'
          } else {
            return v.key + '&nbsp;('+ v.doc_count + ')'
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
      }
  }
}
</script>
<style>
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
.field {
  display:flex
}
.colorbox2 {
	border:1px solid Black;
	width:10px;
	height:10px;
	margin-right:3px;
	margin-top:3px;
}
</style>