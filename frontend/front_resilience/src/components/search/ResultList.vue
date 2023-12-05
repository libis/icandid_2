<template>
  <div style="overflow-y: scroll" ref="list" v-infinite-scroll="loadMore" 
        infinite-scroll-disabled="getSearchStatus" 
        infinite-scroll-distance="10" >
    <table class="table is-striped is-hoverable fullwidth" width="100%">
      <tbody>
          <tr width="100%" class="is-clickable" v-for="(rec, idx) in getResultset" :key="idx" :class="{ 'is-selected' : idx == getActiveResultIdx }">
              <td style="padding-right:0px" v-html="icon(rec._source['@type'])"></td> 
              <td width="90%" @click="setActiveResultIdx(idx)">
                <p class="rec_type" v-html="rec._source['@type']"></p> 
                <p class="rec_name" v-if="rec._source.name !== undefined" v-html="parse(rec._source.name)"></p>
                <p class="rec_author" v-if="rec._source.author !== undefined" v-html="parse(rec._source.author)"></p>
                <p class="rec_publisher" v-if="rec._source.publisher !== undefined" v-html="parse(rec._source.publisher)"></p>
                <p class="rec_publisher" v-if="rec._source.datePublished !== undefined" v-html="parse(rec._source.datePublished)"></p>
                 <p class="rec_dataset" v-if="rec._source.isBasedOn.isPartOf !== undefined" v-html="parse(rec._source.isBasedOn.isPartOf.name)"></p> 
              </td>
              <td width="10%">
                <div class="field">
                  <input
                    type="checkbox"
                    class="is-checkradio is-small is-rtl"
                    :class="{ 'is-white' : idx == getActiveResultIdx }"
                    :id="'res_ch_'+idx"
                    v-model="theSelected"
                    :value="rec._id"
                    @click="movetotop()"
                />
                <label :for="'res_ch_'+idx"></label>
                </div>
              </td>
          </tr>
      </tbody>
    </table>
  </div>
</template>
<script>

import { mapActions, mapGetters } from "vuex";
export default {
  methods: {
    ...mapActions(["setActiveResultIdx","setSelectedResults"]),
    myEventHandler() {
      this.$refs['list'].style.maxHeight = (window.innerHeight*0.9) + 'px';
    },
    loadMore() {
      this.$parent.extendResultset()
    },
    movetotop() {
      window.scrollTo(0,485);
    },
    selectall() {
      for(var i =0; i < this.getResultset.length; i++) {
        if(this.theSelected.indexOf(i) < 0) {
          this.theSelected.push(i)
        }
      }
    },
    parse(v) {
      if (Array.isArray(v)) {
        var l = Array()
        for (var i in v) {
          l.push(this.parse(v[i]))
        }
        return l.join('<br>')
      } else {
        if ((typeof v === 'string' || v instanceof String)) {
          return v
        } else {
          if (v instanceof Object) {
            if (v.name != undefined) {
              if (v.name instanceof Object) {
                if (v.name["@value"] != undefined) {
                  return v.name["@value"]
                } else {
                  if (typeof v.name == 'string') {
                    return v.name
                  }                  
                }
              } else {
                if (typeof v.name == 'string') {
                  return v.name
                }
              }
            }
            if (v["@value"] != undefined) {
              return v["@value"]
            }
          }
        }
      }
      return "not defined"
    },
    icon(v) {
      var f = 'fa ' 
      if (v != "Person" && v!= "Event" && v != "Event") { f += 'fa-book' }
      if (v == "Person") { f += 'fa-user' }
      if (v == "Event") { f += 'fa-calendar' }

      var r = '<i class="' + f + '" style="font-size:35px"></i>'

      return r
    }


  },
  computed: {
    ...mapGetters(["getResultset","getActiveResultIdx","getSearchStatus","getSelectedResults"]),
    theSelected: {
        get() { return this.getSelectedResults },
        set(val) { this.setSelectedResults(val) }
    }
  },
  mounted() {
    window.addEventListener("resize", this.myEventHandler);
    this.myEventHandler()
  },
  destroyed() {
    window.removeEventListener("resize", this.myEventHandler);
  }
};
</script>
<style scoped>
.is-clickable {
  cursor: pointer;
}

.rec_type { 
    padding:0px; margin:0px ;
    text-transform: uppercase;
    font-size:8px;
    font-weight:Bold
}
.rec_name { 
    padding:0px; margin:0px ;
    font-weight:Bold;
    list-style-type:none;
    line-height:1em
}

.rec_author { 
    padding:0px; margin:0px ;list-style-type:none;list-style-type:none;font-size:14px
}
.rec_publisher { 
    padding:0px; margin:0px ;
    font-style:Italic;
    list-style-type:none;;font-size:13px
}
.rec_dataset { 
    padding:0px; margin:0px ;list-style-type:none;list-style-type:none;font-size:11px
}


</style>