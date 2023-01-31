<template>
  <div style="overflow-y: scroll" ref="list" v-infinite-scroll="loadMore" 
        infinite-scroll-disabled="getSearchStatus" 
        infinite-scroll-distance="10" >
    <table class="table is-striped is-hoverable fullwidth" width="100%">
      <tbody>
          <tr width="100%" class="is-clickable" v-for="(rec, idx) in getResultset" :key="idx" :class="{ 'is-selected' : idx == getActiveResultIdx }">
              <td width="90%" @click="setActiveResultIdx(idx)">
                {{ parse(rec._source.name) | stripHTML }}
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
      if (Array.isArray((v))) {
        return v[0]['@value']
      } else {
        return v['@value']
      }
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
</style>