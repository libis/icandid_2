<template>
    <div class="filterbar">
        <div class="prefix" v-if="getHits >= 0"><b>{{ getHits }}</b>&nbsp;{{$ml.get('resultsfor')}}</div>
        <div class="bubble" v-for="bubble,k in bubbles()" :key="k">
            {{ bubble.text }}
            <div v-if="bubble.filter != undefined" style="margin-left:4px;cursor:pointer" @click="removeFilter(bubble.filter,bubble.value)"><Icon icon="cross" color="#0625AC" width="8"></Icon></div>
        </div>
    </div>
</template>
<script>
import { mapGetters, mapActions } from "vuex";
import Icon from "../helpers/Icon.vue"
export default {
    data() {
        return {
            selectedFilters:[],
            searchRequest:[]
        }
    },
    components:{
        Icon
    },
    computed: {
        ...mapGetters(['getSearchRequest','getSelectedFilters','getHits']),
    },
    methods:{
        ...mapActions(['setSelectedFilters']),
        removeFilter(filtername, value) {
          if (filtername == "publicationdaterange") {
            this.selectedFilters["datePublished_from"] = ""
            this.selectedFilters["datePublished_until"] = ""
          } else {
            const index = this.selectedFilters[filtername].indexOf(value);
            if (index !== -1) {
                this.selectedFilters[filtername].splice(index, 1);
            }
          }
          this.setSelectedFilters(this.selectedFilters)
        },
        bubbles(){
            const l = []
            if (this.searchRequest.searchtype == 'simple') {
                if (this.searchRequest.q.trim() != "") {
                    l.push({text:this.searchRequest.q})
                }
            }
            if (this.searchRequest.searchtype == 'advanced') {
                this.searchRequest.q.forEach((el) => {
                    if (el.query.trim() != "") {
                        l.push({text:this.$ml.get(el.field)+":"+el.query})
                    }
                })
            } 
            var range = " - "
            for (const [filtername, values] of Object.entries(this.selectedFilters)) {
                if (Array.isArray(values)) {
                    values.forEach((value) => {
                    l.push({text:this.$ml.get(filtername)+":"+value,filter:filtername,value:value})  
                    })
                }
                if (filtername == "datePublished_from"){
                    range = values + range
                }
                if (filtername == "datePublished_until"){
                    range = range + values
                }
            }
            if (range != " - ") {
                l.push({text:this.$ml.get("publicationdaterange")+":"+range,filter:'publicationdaterange',value:''})
            }
            return l
        }
    },
    created() {
        this.unsubscribe ==
            this.$store.subscribe((mutation) => {
                if (mutation.type == "setSearchStatus") {
                    this.selectedFilters = JSON.parse(JSON.stringify(this.getSearchRequest.sf))
                    this.searchRequest = JSON.parse(JSON.stringify(this.getSearchRequest))
            }
        });
    }    
}    
</script>
<style lang="css" scoped>
.filterbar {
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    gap: 4px; /* replaces margin-right */    
}

.bubble {
    display: inline-flex;
    width: fit-content;  /* only as wide as its content */
    border:1px solid #0625AC;
    background-color: #E5EEFF;
    color:#0625AC;
    border-radius: 12px;
    height:24px;
    font-weight: 600;
    font-style: Semi Bold;
    font-size: 13px;
    line-height: 15px;
    letter-spacing: 0px;
    padding-left:5px;
    padding-right:5px;
    padding-top:4px;
    white-space: nowrap;    
}
.prefix {
    display: inline-flex;
    width: fit-content;  /* only as wide as its content */
    word-wrap: none;
    white-space: nowrap;
}
</style>