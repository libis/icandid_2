<template>
    <div>
        <div class="columns" style="padding-left:5px">
            <div class="column" style="padding:0.4rem">
                <select class="selectinput" v-model="selectedType">
                    <option value="all">{{ $ml.get('allthewords') }}</option>
                    <option value="one">{{ $ml.get('oneofwords') }}</option>
                    <option value="phrase">{{ $ml.get('exactsentence') }}</option>
                </select>
            </div>
            <div class="column" style="padding:0.4rem">
                <input class="textinput" type="text" v-model="searchterm" :placeholder="$ml.get('search')+'...'" @keyup.enter="$parent.clearsearch()">
            </div>
            <div class="column" style="padding:0.4rem">
                <button class="searchbutton"  @click="$parent.clearsearch()" :class="{'is-loading':getSearchStatus}" v-html="buttonAni"></button>
            </div>
        </div>
    </div>
</template>
<script>
import { mapActions, mapGetters } from 'vuex';
export default {
    data() {
        return {
            searchterm: "",
            filter:[],
            selectedType:"all"
        }
    },
    computed: {
        ...mapGetters(['getSearchStatus']) ,
        buttonAni() {
            if (this.getSearchStatus) {
                return '<div class="spinner"></div>'
            }
            return this.$ml.get('search')
        }
    }, 
    methods:{
        ...mapActions(["clearResultset","clearAggregations","getSearchRequest"]),

        queryObj() {
            return {searchtype:"simple", q:this.searchterm, f:this.filter, type:this.selectedType, name:this.selectedType+":"+this.searchterm}
        },
        clear() {
            this.searchterm = "";
            this.selectedType = "all";
            this.clearResultset()
            this.clearAggregations()
        },
        doSearchFromHome() {
            if (this.$store.state.search.queryObj.searchtype === "simple") {
                this.searchterm = this.$store.state.search.queryObj.q;
                this.filter = this.$store.state.search.queryObj.f;
                this.$parent.search();
            }
        }
    },
    
    watch: {
        $route(to, from) {
            if (to.name === "Search" && from.name === "Home") {
                this.doSearchFromHome()
            }
        }
    },
    mounted() {
        this.doSearchFromHome()
    }
}
</script>
<style>
.textinput {
    width:950px;
    height:40px;
    border:1px solid Black;
    font-size: 20px;
    padding-left:10px

}
.selectinput {
    width:200px;
    height:40px;
    border:1px;
    border-color: #ebeff3;
    border-radius: 5px;
    font-size:20px;
    padding-left:10px;
    padding-right:10px;
    color:Black;
    background-color: #ebeff3;

}
.searchbutton {
    height:40px;
    border-radius: 5px;
    border:0px;
    background-color:#0625ac;
    font-size:16px;
    color:White;
    width:100px;
    cursor:pointer
    
}
.spinner {
    width: 16px;
    height: 16px;
    border: 2px solid rgba(255,255,255,0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
    margin-left:36px
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}
</style>