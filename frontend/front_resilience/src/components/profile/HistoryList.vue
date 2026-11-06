<template>
  <div ref="list">
    <table class="table fullwidth" v-if="getHistory.length > 0">
      <tbody>
          <tr v-for="(rec, idx) in sortedHistory" :key="'r'+idx">
              <td>
                <span @click="setQ(idx)">
                  <Icon icon="history" color="#202F3E" width="16"></Icon>
                </span>
              </td>
              <td style="white-space: nowrap">{{ $moment(rec.created_at).format("YYYY-MM-DD HH:mm:ss") }}</td>
              <td width="100%">
                <div v-if="rec.query.searchtype == 'simple'" class="historyline">
                  <div v-if="rec.query.q != ''">{{ rec.query.q }}</div>
                  <div v-for="(filter, fidx) in bubbles(rec.query)" :key="'f'+fidx">
                    {{ filter.text }} 
                  </div>
                </div>
                <div v-if="rec.query.searchtype == 'advanced'" class="historyline">
                  <div v-for="(query, qidx) in rec.query.q" :key="'q'+qidx">{{ $ml.get(query.field)}}:{{ query.query }}</div>
                  <div v-for="(filter, fidx) in bubbles(rec.query)" :key="'f'+fidx">
                    {{ filter.text }}
                  </div>
                </div>
              </td>
          </tr>
      </tbody>
    </table>
  </div>
</template>
<script>
import { mapActions, mapGetters } from "vuex";
import Icon from '../helpers/Icon.vue'
export default {
  methods: {
    ...mapActions(["setQuery",'setHistory','setSelectedFilters']),
    setQ(idx) {
      this.$router.push("/search");
      window.setTimeout(function() {
        this.setQuery(this.sortedHistory[idx].query);
        this.setSelectedFilters(this.sortedHistory[idx].query.sf);
      }.bind(this)
      , 1000)
    },
    bubbles(query){
        const l = []
        var range = " - "
        for (const [filtername, values] of Object.entries(query.sf)) {
            if (Array.isArray(values)) {
                values.forEach((value) => {
                l.push({text:this.$ml.get(filtername)+":"+value,filter:filtername,value:value})  
                })
            }
            if (filtername == "datePublished_from"){
                range = values + range
            }
            if (filtername == "datePublished_until"){
                range = range + values
            }
        }
        if (range != " - ") {
            l.push({text:this.$ml.get("publicationdaterange")+":"+range,filter:'publicationdaterange',value:''})
        }
        return l
    }    
  },
  props:{
    count:{
      type:Number,
      default:0
    }
  },
  components :{
    Icon
  },  
  computed: {
    ...mapGetters(["getHistory"]),
    sortedHistory() {
      const history = this.getHistory.filter(hist => (hist.query.f.length > 0 || hist.query.q != ""))
      if (this.count == 0) {
        return [...history].sort((a,b) => (a.created_at > b.created_at) ? -1 : 1)
      } else {
        return [...history].sort((a,b) => (a.created_at > b.created_at) ? -1 : 1).slice(0, this.count)
      }
      
    }
  }
};
</script>
<style scoped>
.is-clickable {
  cursor: pointer;
}
.table td {
  border-bottom: 1px solid #EBEFF3;
  font-weight: 400;
  font-style: Regular;
  font-size: 16px;
  line-height: 22px;
  letter-spacing: 0%;
  padding-top:16px;
  padding-bottom:8px
}
.table td span {
  cursor:pointer;
  
}

.historyline {
  display: flex;
  flex-wrap: wrap;
  gap: 3px; 
}
.historyline div {
  padding: 3px 5px 2px 5px;
  border-radius: 6px;
  border: 1px solid #ADBDCC;
  font-weight: 600;
  font-size: 13px;
  line-height: 15px;
  color: #455B71;
  white-space: nowrap;
}
</style>