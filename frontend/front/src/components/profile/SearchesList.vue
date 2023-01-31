<template>
  <div ref="list">
    <table class="table is-striped is-hoverable fullwidth" v-if="getSavedQueries.length > 0">
      <tbody>
        <tr class="is-clickable" v-for="(rec, idx) in sortedSavedQueries" :key="idx">
          <td @click="setQ(idx)"><i class="fa fa-play is-size-5"></i></td>
          <td
            style="white-space: nowrap"
            @click="setQ(idx)"
          >{{ $moment(rec.created_at).format("YYYY-MM-DD HH:mm:ss") }}</td>
          <td @click="setQ(idx)">{{ rec.query.name }}</td>
          <td  @click="setQ(idx)" width="100%" style="font-size:12px;vertical-align: middle">{{ JSON.stringify(nonav(rec.query)).replace(/,/g, ", ") }}</td>
          <td>
            <i class="fa fa-copy is-size-5" @click="copySavedQuery(rec.query)"></i>
          </td>
          <td>
            <i class="fa fa-trash is-size-5" @click="removeSavedQuery(idx)"></i>
          </td>
        </tr>
      </tbody>
    </table>
    <Dialog ref="dialog" @answer="onAnswer"></Dialog>
  </div>
</template>
<script>
import { mapActions, mapGetters } from "vuex";
import axios from "axios";
import Dialog from '../helpers/Dialog.vue'
export default {
  data() {
    return {
      queryToDelete:-1
    }
  },
  components: {
    Dialog
  },
  methods: {
    ...mapActions(["setQuery","setUser"]),
    setQ(idx) {
      this.$router.push("/search");
      window.setTimeout(function() {
        this.setQuery(this.sortedSavedQueries[idx].query);
      }.bind(this)
      , 1000)
    },
    removeSavedQuery(idx) {
      this.queryToDelete = idx
      this.$refs.dialog.confirm(this.$ml.get('confirm'),this.$ml.get('suredeletesearchfromsaved'))
    },
    onAnswer(e) {
      if (e) {
        axios
          .delete(this.getApiProfileUrl + "/query/" + this.sortedSavedQueries[this.queryToDelete].id)
          .then(res => {
            this.setUser(res.data);
          })
          .catch(error => {
            console.log(error);
          });        
      }
      this.queryToDelete = -1
    },
    nonav(p) {
      var r = JSON.parse(JSON.stringify(p))
      delete r.nav 
      delete r.name 
      return r
    },
    copySavedQuery(query) {
      navigator.clipboard.writeText(JSON.stringify(this.nonav(query)));
    }
  },
  computed: {
    ...mapGetters(["getSavedQueries","getApiProfileUrl"]),
    sortedSavedQueries() {
      return [...this.getSavedQueries].sort((a,b) => (a.created_at > b.created_at) ? -1 : 1);
    }
  }
};
</script>
<style scoped>
.is-clickable {
  cursor: pointer;
}
</style>