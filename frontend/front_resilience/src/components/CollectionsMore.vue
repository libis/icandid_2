<template>
    <div>
        <h1 class="title">{{ $ml.get('aboutcollections') }}</h1>
        <p style="margin-bottom:20px" >{{ $ml.get('aboutcollectionsintro') }}</p>
        <!-- <input class="input input_intro" type="text" v-model="searchTerm" > -->
        <div class="grid-container" v-for="collection in matchingCollections" :key="collection.id">
            <div class="grid-item" style="height:180px;overflow:hidden"><img :src="collection.imageurl"></div>
            <div class="grid-item info">
                <h3 class="title is-4">{{ collection.name }}</h3>
                <h4 class="title is-5">{{ collection.provider }}</h4>
                <p v-html="collection.description.replace('\n','<br/>') + ' ' + moreinfo(collection.dataseturl)"></p>
                <button class="button is-primary" style="background-color: #E5EEFF; border-color: #E5EEFF;color:#001A72;font-size:14px;height:30px;width:18%" @click="searchFilter('collection',collection.name)" ><i class="fa fa-search" style="margin-right:5px"></i>{{ $ml.get('explorecollection') }}</button>
            </div>
        </div>
    </div>
</template>
<script>
import axios from "axios";
import { mapGetters, mapActions } from "vuex";
axios.defaults.withCredentials = true;

export default {
    data() {
        return {
            collections:[],
            searchTerm: ""
        }
    },
    components:{
    },    
    methods: {
        ...mapActions(['setQuery','resetQuery','setSelectedFilters']),
        validURL(str) {
            try {
                const url = new URL(str);
                // Restrict to http and https only
                return url.protocol === "http:" || url.protocol === "https:";
            } catch (err) {
                return false;
            }
        },
        moreinfo(url) {
            if (this.validURL(url)) {
                return "<a style='white-space: nowrap;' href='" + url + "' target='_blank'>" + this.$ml.get('moreinfo') + "</a>"
            } else { return "" }
        },
        searchFilter(filterName, value) {
            var newFilters = {
                type:[],
                collection:[],
                inlanguage:[],
                datePublished_from:"",
                datePublished_until:"",
                author:[],
                contributor:[],
                publisher:[],
                locationcreated:[]
            }
            this.$router.push("/search")
            this.resetQuery({ searchtype: "simple", q: "", f: [], sf: newFilters})
            window.setTimeout(function() {
                newFilters[filterName] = [value]
                this.setSelectedFilters(newFilters)
            }.bind(this), 500)
        }
    },
    computed: {...mapGetters(['getApiCollectionUrl','getDatasets']), 
        matchingCollections() {
            if (this.searchTerm.trim() == "") {
                return this.collections
            }
            let searchTermLower = this.searchTerm.toLowerCase().trim()
            let matching = this.collections.filter((collection) => 
                ( collection.description.toLowerCase().includes(searchTermLower) 
                    || collection.name.toLowerCase().includes(searchTermLower) 
                    || collection.provider.toLowerCase().includes(searchTermLower) 
                    || collection.providerurl.toLowerCase().includes(searchTermLower)
                    || collection.dataseturl.toLowerCase().includes(searchTermLower) 
                    || collection.license.toLowerCase().includes(searchTermLower) 
                )
            )
            return matching
        }
    },
    created() {
        axios
            .get(this.getApiCollectionUrl + '/all')
            .then(res => {
                this.collections = res.data;
            });
    },
    watch : {
    }
    
}
</script>
<style scoped>
.grid-container {
  display: grid;
  grid-template-columns: auto auto;
  margin-bottom: 40px;
}


.grid-item img {
    width:320px
}

.info {
    padding-left:20px;
    width:976px
}

.info p {
    margin-bottom: 15px
}
</style>