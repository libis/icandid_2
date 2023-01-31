<template>
  <div ref="list">
    <table class="table is-striped is-hoverable fullwidth" v-if="getHistory.length > 0">
      <tbody>
          <tr class="is-clickable" v-for="(rec, idx) in sortedHistory" :key="idx">
              <td style="white-space: nowrap" @click="setQ(idx)">{{ $moment(rec.created_at).format("YYYY-MM-DD HH:mm:ss") }}</td>
              <td width="100%" @click="setQ(idx)">{{ rec.query.name }}</td>
          </tr>
      </tbody>
    </table>
  </div>
</template>
<script>
import { mapActions, mapGetters } from "vuex";
export default {
  methods: {
    ...mapActions(["setQuery"]),
    setQ(idx) {
      this.$router.push("/search");
      window.setTimeout(function() {
        this.setQuery(this.sortedHistory[idx].query);
      }.bind(this)
      , 1000)
    }
  },
  computed: {
    ...mapGetters(["getHistory"]),
    sortedHistory() {
      return [...this.getHistory].sort((a,b) => (a.created_at > b.created_at) ? -1 : 1)
    }
  }
};
</script>
<style scoped>
.is-clickable {
  cursor: pointer;
}
</style>