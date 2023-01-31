<template>
    <div>
        <br />
        <div class="has-text-weight-semibold">{{ $ml.get('filters')}}</div>
        <div class="content is-capitalized" v-for="type in filterOrder" :key="type">
            <span v-if="isPresent(type)">{{ $ml.get(type) }}</span>
            <ul class="content is-small" style="list-style-type: none; margin: 8px">
                <li v-for="(v,k) in buckets(type)" :key="k">
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
        </div>
    </div>
</template>
<script>
import { mapActions, mapGetters } from 'vuex';
export default {
    data() {
      return {
        nbspace:"&nbsp;",
        filterOrder:["provider","author","publisher","edition","retweets"]
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
</style>