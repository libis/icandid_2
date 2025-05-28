<template>
  <div class="block">
    <div class="columns">
      <div class="column is-2"></div>
      <div class="is-divider-vertical" style="padding:0"></div>
      <div class="column is-8">
        <Detail :activeResult="this.record" ref="detail" :highlights="false"></Detail>
        <div v-if="record == null">
          {{ $ml.get('recordnotfound')}}
        </div>
        <Loader v-if="record == ' '"></Loader>
      </div>
      <div class="is-divider-vertical" style="padding:0" ></div>
      <div class="column is-2"></div>
    </div>
</div>
</template>
<script>
import Detail from "./search/Detail.vue";
import Loader from "./helpers/Loader.vue";
import axios from "axios";
import { mapGetters } from "vuex";

axios.defaults.withCredentials = true;

export default {
  data() {
    return {
      record:" ",
      queryObj:{
        q:"",
        searchtype:"id"
      }
    }
  },
  components: {
    Detail, Loader
  },
  computed: {
    ...mapGetters(["getApiSearchUrl"]),
  },
  created() {
    this.updateRecord();
  },
  methods:{
    updateRecord() {
      this.queryObj.q = this.$route.params.id;
      this.$gtag.event('search', {'event_category' : 'engagement' , 'event_label': JSON.stringify(this.queryObj) })
//      window._paq.push(['trackEvent','Search',"IdSearch"])
      window._paq.push(['trackSiteSearch',this.$route.params.id,"IdSearch"])
      axios
        .post(this.getApiSearchUrl, this.queryObj)
        .then(res => {
          var data = res.data.hits.hits[0]
          if (data != undefined) {
            data.highlight = {}
          }
          this.record = data
        })
        .catch(error => {
          console.log(error);
        });      
    }
  },
  watch: {
		// Call the method again if the route changes
		'$route': function() { if (this.$route.name == 'Record') { this.updateRecord()} }
	}
};
</script>