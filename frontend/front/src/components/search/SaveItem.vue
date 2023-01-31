<template>
<div class="modal" :class="(visible?'is-active':'')" >
  <div class="modal-background" @click="close()"></div>
  <div class="modal-card">
    <header class="modal-card-head">
      <p class="modal-card-title">{{ $ml.get('saveiteminset') }}</p>
      <button class="delete" aria-label="close" @click="close()"></button>
    </header>
    <section class="modal-card-body">
        <p class="content">{{ $ml.get('enternameofset') }}</p>
        <p class="content">{{ $ml.get('alternativeclick') }}</p>
        <input class="input" style="width:50%" list="sets" name="set" ref="set" v-model="shelf">
        <datalist id="sets">
          <option v-for="(option, key) in this.getSavedSets" :key="key" v-bind:value="option"></option>
        </datalist>
    </section>
    <footer class="modal-card-foot">
      <button class="button is-success" @click="save()">{{ $ml.get('save') }}</button>
      <button class="button" @click="close()">{{ $ml.get('cancel') }}</button>
    </footer>
  </div>
</div>    
</template>
<script>
import { mapGetters, mapActions } from "vuex";
import axios from 'axios';
export default {
    data() {
        return {
            visible:false,
            shelf:""
        }
    },
    methods:{
        ... mapActions(["setUser"]),
        open() {
            this.visible = true
        },
        close() {
            this.visible = false
        },
        save() {
            if (this.shelf !="") {
                if (this.getSelectedResults.length > 0) {
                    var obj = {shelf: this.shelf, values: this.getSelectedResults}
                    axios
                    .post(this.getApiProfileUrl + '/item', obj)
                    .then(res =>{
                        this.setUser(res.data);
                        this.close(); 
                    })
                    .catch(error => {
                        console.log(error);
                    });
                } else {
                    var url = this.getApiProfileUrl + '/queue'
                    var request = {query:this.getSearchRequest, format:'shelfitem', shelf:this.shelf, language: this.$ml.current }
                    axios
                        .post(url, request)
                        .then(res =>{
                            console.log(res.data);
                            this.close(); 
                        })  
                        .catch(error => {
                        console.log(error);
                    });
                }
            }
        }
    },
    computed: {
        ...mapGetters(["getSavedSets","getActiveResult","getApiProfileUrl","getSelectedResults","getSearchRequest"])
    }
    
}
</script>