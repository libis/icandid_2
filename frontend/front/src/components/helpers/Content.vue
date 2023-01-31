<template>
    <div v-show="content != null">
    <h3 class="title is-2" v-if="show_title" v-html="title"></h3>
    <MarkDown id="markdown" :markdown="content"></MarkDown>
    </div>
</template>
<script>
import axios from "axios";
import { mapGetters } from "vuex";
axios.defaults.withCredentials = true;
import MarkDown from '../helpers/MarkDown.vue'
export default {
  components: {
    MarkDown
  },
  props: {contentcode:String,show_title:{type:Boolean,default:true},lang:String},
  data() {
    return {
        data:[]
    }
  },
  computed: {
      ...mapGetters(['getApiContentUrl']),
      content() {
          return this.data['content_'+this.lang];
      },
      title() {
          return this.data['title_'+this.lang];
      }
  },
  created() {
    axios
        .get(this.getApiContentUrl+'/'+this.contentcode)
        .then(res => {
            this.data=res.data
        })
        .catch(error => console.log(error));    
  }
}
</script>
<style>

</style>