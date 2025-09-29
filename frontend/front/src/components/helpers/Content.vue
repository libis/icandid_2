<template>
    <div v-show="content != null">
    <h3 class="title is-2" v-if="show_title" v-html="title"></h3>
    <MarkDown v-if="!isHTML" id="markdown" :markdown="content"></MarkDown>
    <div v-if="isHTML" id="html" v-html="content"></div>
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
      },
      isHTML() {
        return (this.data['content_'+this.lang].trim().substring(0,1) == "<")
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
#html li {
    padding-top:5px;
    padding-bottom:-2px
}
#html ul {
    list-style-type: disc;
    margin-top:0px;
    margin-left:67px;
    margin-bottom:0px
}
#html ol {
    margin-top:0px;
    margin-bottom:30px;
    margin-left:67px
}
#html img {
    border: 1px solid Black;
    padding:12px
}
#html p {
    padding-bottom:0.5rem
}

</style>