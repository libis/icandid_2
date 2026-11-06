<template>
  <div class="block">
    <div class="columns">
      <div class="column is-1"></div>
      <div class="is-divider-vertical" style="padding:0"></div>
      <div class="column is-10">
        <Detail :data="record._source" ref="detail" v-if="record != ' '"></Detail>
        <div v-if="record == null">
          {{ $ml.get('recordnotfound')}}
        </div>
        <Loader v-if="record == ' '"></Loader>
      </div>
      <div class="is-divider-vertical" style="padding:0" ></div>
      <div class="column is-1"></div>
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
      this.record = " "
      this.$nextTick(() => {
        this.queryObj.q = this.$route.params.id;
        //this.$gtag.event('search', {'event_category' : 'engagement' , 'event_label': JSON.stringify(this.queryObj) })
        axios
          .post(this.getApiSearchUrl, this.queryObj)
          .then(res => {
            console.log(res.data)
            var data = res.data.hits.hits[0]
            if (data != undefined) {
              data.highlight = {}
            }
            console.log("1", data)
            this.record = data
          })
          .catch(error => {
            if (error.response.status === 401) {
              axios
                .post(this.getApiSearchUrl+"/public", this.queryObj)
                .then(res => {
                  var data = res.data.hits.hits[0]
                  if (data != undefined) {
                    data.highlight = {}
                  }
                  console.log("2", data)
                  this.record = data
                })
            }
          });      
        });
    }
  },
  watch: {
		// Call the method again if the route changes
		'$route': function() { if (this.$route.name == 'Record') { this.updateRecord()} }
	}
};
</script>