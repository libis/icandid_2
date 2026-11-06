<template>
    <div>
        <span class="citation">{{ $ml.get('citation') }}</span>
        <div style="display:flex; vertical-align:middle"> 
            <select class="selectinput option" :style="(longnames?'width:430px':'width:150px')" v-model="activeCitationStyle" >
                <option class="option" v-for="(type,key) in citation_styles" :value="key" :key="key">
                    {{ longnames?type.name:key }}
                </option>
            </select>
            <div style="padding-top:8px;margin-left:8px;cursor:pointer" @click="cite()">
                <Icon icon="clipboard" color="#455B71" width="16"></Icon>
            </div>
            
        </div>
        <transition name="fade">
            <div style="padding-top:3px;color:#B5BABF;font-size: 13px;" v-if="isCopied">Citation is copied to clipboard</div>
        </transition>
    </div>
</template>
<script>
import axios from "axios";
axios.defaults.withCredentials = true;
import { mapGetters } from "vuex";
import Icon from "./Icon.vue"
export default {
    data() {
        return {
            isCopied:false,
            activeCitationStyle:"APA",
            citation_styles: {"MLA7": {csl:"modern-language-association-7th-edition", name:"Modern Language Assocation, 7th edition (MLA 7)"},
                            "MLA8": {csl:"modern-language-association", name:"Modern Language Assocation, 8th edition (MLA 8)"},
                            "APA": {csl:"apa-6th-edition", name:"American Psychological Association (APA)"},
                            "CT16": {csl:"chicago-author-date-16th-edition", name:"Chicago/Turabian, 16th edition"},
                            "HAR1": {csl:"elsevier-harvard", name:"Harvard 1"}},
        }
    },
    components:{
        Icon
    },
    props:{
        data:{
            type:Object
        },
        longnames:{
            type: Boolean,
            default:false
        }
    },
    methods:{
        cite() {
            var citeurl = this.getApiCitationUrl+"/"+this.citation_styles[this.activeCitationStyle].csl+"/text/bibliography";
            axios.post(citeurl, this.data, {headers: {'Content-Type': 'application/ld+json'}})
            .then(response => {
                navigator.clipboard.writeText(response.data)
                this.isCopied = true
                window.setTimeout(() => { this.isCopied = false; }, 4000); 
            });
        }
    },
    computed: { ...mapGetters(['getApiCitationUrl']) }
}
</script>
<style scoped>
.citation {
  font-weight: 600;
  font-size: 13px;
  line-height: 15px;
  letter-spacing: 0px;
  vertical-align: middle;
  color:#455B71
}
.option {
font-weight: 400;
font-size: 16px;
line-height: 22px;
letter-spacing: 0%;
vertical-align: middle;
color:#202F3E
}
.fade-enter-active, 
.fade-leave-active {
  transition: opacity 0.5s ease;
}

.fade-enter, 
.fade-leave-to {
  opacity: 0;
}
</style>