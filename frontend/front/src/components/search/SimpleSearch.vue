<template>
    <div>
        <input class="input" type="text" v-model="searchterm" :placeholder="$ml.get('search')+'...'">
    </div>
</template>
<script>
import { mapActions } from 'vuex';
export default {
    data() {
        return {
            searchterm:"",
        }
    },
    methods:{
        ...mapActions(["clearResultset","clearAggregations"]),
        queryObj() {
            return {searchtype:"simple", q:this.searchterm}
        },
        keywords(){
            return this.searchterm
        },
        clear() {
            this.searchterm = "";
            this.clearResultset()
            this.clearAggregations()
        }
    }
}
</script>