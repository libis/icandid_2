<template>
<div style="overflow-y: scroll" ref="list" v-infinite-scroll="loadMore" 
        infinite-scroll-disabled="getSearchStatus" 
        infinite-scroll-distance="10">
    <table class="table is-striped is-hoverable fullwidth">
      <tbody>
          <tr class="is-clickable" v-for="(rec, idx) in items" :key="idx" :class="{ 'is-selected' : rec.id == activeItemId }">
              <td width="100%" class="is-clickable" @click="$emit('click',rec.id)">{{ rec.value._source.name['@value'] }}</td>
              <td>
                <i class="fa fa-trash is-size-5" @click="removeItem(rec.id)"></i>
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
    props:['items','activeItemId'],
    data() {
      return {
        setToDelete:-1
      }
    },
    components:{
      Dialog
    },
    methods: {
      ...mapActions(["setUser"]),
      removeItem(id) {
        this.setToDelete = id;
        this.$refs.dialog.confirm(this.$ml.get('confirm'),this.$ml.get('suredeleteitemfromset'))
      },
      onAnswer(e) {
        if (e) {
          axios
            .delete(this.getApiProfileUrl + "/item/" + this.setToDelete)
            .then(res => {
              this.setUser(res.data);
              this.$parent.getShelves();
              this.$emit('click',0)
            })
            .catch(error => {
              console.log(error);
            });
        }
        this.setToDelete = -1;
      },
      loadMore() {
        this.$parent.loadMore()
      },
      myEventHandler() {
        this.$refs['list'].style.maxHeight = (window.innerHeight*0.9) + 'px';
      }
    },
    computed: {
      ...mapGetters(["getApiProfileUrl"])
    },
    mounted() {
      window.addEventListener("resize", this.myEventHandler);
      this.myEventHandler()
    },
    destroyed() {
      window.removeEventListener("resize", this.myEventHandler);
    }
}
</script>
<style scoped>
.is-clickable {
  cursor: pointer;
}
</style>