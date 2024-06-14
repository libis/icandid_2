<template>
    <div class="modal" :class="(visible?'is-active':'')" >
      <div class="modal-background" @click="close()"></div>
      <div class="modal-card">
        <header class="modal-card-head">
          <div class="modal-card-title">{{ $ml.get('export') }}</div>
          <button class="delete" aria-label="close" @click="no()"></button>
        </header>
        <section class="modal-card-body spaced">
            {{ $ml.get('exportrecords').replace("#",getHits)  }}<br/><br/>
            <div class="columns">
                <div class="column is-4" style="padding-bottom:0rem">
                    {{ $ml.get('format') }} : 
                </div>
                <div class="column is-8" style="padding-bottom:0rem">
                    <div class="select">
                    <select v-model="format" >
                        <option v-for="item in formats" :value="item" :key="item">{{ item.toUpperCase() }}</option>
                    </select>
                    </div>
                </div>
            </div>
            <div class="columns" >
                <div class="column is-4">
                    <div style="padding-top:2.4rem;padding-bottom:1rem">{{ $ml.get('enrichments') }}* :</div>                    
                    <p class="is-size-7">* {{ $ml.get('onlywithjsonld') }}</p>
                </div>
                <div class="column is-8">
                    <fieldset :disabled="(format!='json-ld')"> 
                    <CheckboxList ref="enrichments" :options="enrichments" :selected="selectedEnrichments" @select="getEnrichments" :height="140" :isDisabled="(format!='json-ld')"></CheckboxList>                    
                    </fieldset>
                </div>
            </div>
                        
        </section>
        <footer class="modal-card-foot" style="justify-content: flex-end;">
          <button  class="button is-success" :disable="(format=='')" @click="yes()">{{ $ml.get('export') }}</button>
          <button  class="button is-danger" @click="no()">{{ $ml.get('cancel') }}</button>
        </footer>
      </div>
    </div>    
    </template>
    <script>
    import CheckboxList from '../helpers/CheckboxList.vue'
    import { mapGetters } from 'vuex';
    export default {
        data() {
            return {
                visible:false,
                format:"",
                formats:["json-ld","txt","csv","xlsx"],
                selectedEnrichments:[]
            }
        },
        components:{
            CheckboxList
        },
        methods:{
            close() {
                this.visible = false;
            },
            open() {
                this.visible = true;
            },
            yes() {
                if(this.format != '') {
                    this.$emit('answer', {"commit":true,"format":this.format,"enrichments":this.selectedEnrichments});
                    this.close();
                }
            },
            no() {
                this.$emit('answer', {"commit":false});
                this.close();
            },
            getEnrichments(e){
                this.selectedEnrichments = e
            }
        },
        computed: {
            ...mapGetters(['getAggregations','getHits']),
            enrichments(){
                if (this.getAggregations.ENRICHMENTS_PER_TYPE == undefined) {
                    return []
                }
                return this.getAggregations.ENRICHMENTS_PER_TYPE.ACTION_NAME.buckets.map(x => {return {"id":x.key,"name":x.key} })
            }
        },
        watch : {
            getAggregations:{
                deep:true,
                handler() {
                    this.$refs.enrichments.deselectAll();
                    this.format = ""
                }
            }
        }
    }
    </script>
    <style scoped>
    .spaced {
        padding-top:40px;
        padding-bottom:50px
    }
    </style>