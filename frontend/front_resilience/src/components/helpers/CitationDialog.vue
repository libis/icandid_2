<template>
    <div class="modal" :class="(visible?'is-active':'')" >
      <div class="modal-background" @click="close()"></div>
      <div class="modal-card">
        <header class="modal-card-head">
          <div class="modal-card-title" v-html="$ml.get('citation')"></div>
          <button class="delete" aria-label="close" @click="close()"></button>
        </header>
        <section class="modal-card-body spaced">
            <div class="columns">
            <div class="column is-narrow">
              <aside class="menu">
                <ul class="menu-list">
                    <li class="citationstyles" v-for="(value,key) in this.citation_styles" :key="key"><a :ref="key" v-on:click.prevent.stop="activeCitationStyle=key">{{ value.name }}</a></li>
                </ul>
              </aside>
            </div>
            <div class="column ">
                <div class="box" style="font-size:12px" ref="citationbox" v-html="this.activeCitation"></div>
            </div>
          </div>

        </section>
        <footer class="modal-card-foot" style="justify-content: flex-end;">
          <button class="button is-success" @click="copy()">{{ $ml.get('copytoclipboard') }}</button>
          <button class="button is-danger" @click="close()">{{ $ml.get('cancel') }}</button>
        </footer>
      </div>
      <Dialog ref="dialog"></Dialog>
    </div>    
    </template>
    <script>
    import axios from "axios";
    axios.defaults.withCredentials = true;
    import { mapGetters } from "vuex";
    import Dialog from '../helpers/Dialog.vue' 

    export default {
        data() {
            return {
                visible:false,
                currentrecord:[],
                activeCitation:"",
                activeCitationStyle:"MLA7",
                citation_styles: {"MLA7": {csl:"modern-language-association-7th-edition", name:"Modern Language Assocation, 7th edition (MLA 7)"},
                                "MLA8": {csl:"modern-language-association", name:"Modern Language Assocation, 8th edition (MLA 8)"},
                                "APA": {csl:"apa-6th-edition", name:"American Psychological Association (APA)"},
                                "CT16": {csl:"chicago-author-date-16th-edition", name:"Chicago/Turabian, 16th edition"},
                                "HAR1": {csl:"elsevier-harvard", name:"Harvard 1"}},
            }
        },
        components: {
            Dialog
        },
        methods:{
            close() {
                this.visible = false;
            },
            open(current) {
                this.currentrecord=current;
//                this.activeCitationStyle = "MLA7"
                this.cite()
                this.visible = true;
            },
            copy() {
                navigator.clipboard.writeText(this.activeCitation)
                    .then(() => { 
                        this.$refs.dialog.alert(this.$ml.get('info'),this.$ml.get('citationcopied')); 
                        })
                    .catch((error) => { this.$refs.dialog.alert(this.$ml.get('info'),`${error}`) });                
               
            },
            cite(){
            for (var key in this.citation_styles) {
                    this.$refs[key][0].classList.remove("is-active");
                }
                if (this.activeCitationStyle != "") {
                    this.$refs[this.activeCitationStyle][0].classList.add("is-active");
                    var citeurl = this.getApiCitationUrl+"/"+this.citation_styles[this.activeCitationStyle].csl+"/text/bibliography";
                    axios.post(citeurl, this.currentrecord._source, {headers: {'Content-Type': 'application/ld+json'}})
                        .then(response => {this.activeCitation = response.data });
                        //.catch(error => console.log(error)); 
                }
            }
        },
        watch:{
            activeCitationStyle:{
                handler: function() {
                    this.cite()
                }
            }
        },
        computed: { ...mapGetters(['getApiCitationUrl']) }
    }
    </script>
    <style scoped>
    .spaced {
        padding-top:40px;
        padding-bottom:50px
    }
    .citationstyles{
        font-size:12px
    }
    </style>