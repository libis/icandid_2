<template>
<div ref="list">
    <table class="table is-striped is-hoverable fullwidth">
      <tbody>
          <tr class="is-clickable" v-for="(rec, idx) in shelves" :key="idx" :class="{ 'is-selected' : rec.id == activeShelfId }">
              <td width="100%" class="is-clickable" @click="$emit('click',rec.id)">{{ rec.name }} ({{ rec.items_count }})</td>
              <td><i class="fa fa-trash is-size-5" @click="removeShelf(rec.id)"></i></td>
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
    props:['shelves','activeShelfId'],
    data() {
      return {
        shelfToDelete:-1
      }
    },
    components: {
      Dialog
    },
    methods: {
      ...mapActions(["setUser"]),
      removeShelf(id) {
        this.shelfToDelete = id;
        this.$refs.dialog.confirm(this.$ml.get('confirm'),this.$ml.get('suredeleteset'))
      },
      onAnswer(e) {
        if (e) {
          axios
            .delete(this.getApiProfileUrl + "/shelf/" + this.shelfToDelete)
            .then(res => {
              this.setUser(res.data);
              this.$parent.getShelves();
              this.$emit('click',0)
            })
            /*.catch(error => {
              console.log(error);
            })*/;
        }
        this.shelfToDelete = -1;
      },
    },
    computed: {
      ...mapGetters(["getApiProfileUrl"]),
    }
  }
</script>
<style scoped>
.is-clickable {
  cursor: pointer;
}
</style>