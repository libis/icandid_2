<template>
    <div style="margin-bottom:50px">
        <div class="columns">
            <div class="column is-8">
                <h1 class="title">{{ $ml.get("favourits") }}</h1>
            </div>
            <div class="column is-4">
                <div class="action-buttons">
                    <div class="addlist" @click="newlist = !newlist"><Icon icon="plus" color="#001A72" width="17"></Icon>{{$ml.get('addlist')}}</div>
                    <div class="download"><Icon icon="download" color="#202F3E" width="17"></Icon>{{ $ml.get('downloadcsv') }}</div>
                </div>
            </div>
        </div>
        <div v-if="newlist">
            <Shelf :data="newshelf" @shelfsaved="updateFavorites"></Shelf>
        </div>
        <div v-for="favourite, key in favourites" :key="'fav'+key">
            <Shelf :data="favourite" @shelfsaved="updateFavorites"  @deleted="wasDeleted" ></Shelf>
        </div>
        
    </div>
</template>
<script>
import { mapGetters } from "vuex";
import axios from 'axios';
import Icon from './helpers/Icon.vue'
import Shelf from './helpers/Shelf.vue'
export default {
    data() {
        return {
            favourites:[],
            newlist:false,
            newshelf:{id:0,name:"New List"}
        }
    },
    components: {
        Icon,
        Shelf
    },
    methods:{
        getFavourites() {
            
            axios
                .get(this.getApiFavouritesUrl)
                .then(res => {
                    this.favourites = res.data;
                })
        },
        updateFavorites(data) {
            this.favourites = []
            this.favourites = data
            this.newlist = false
            this.newshelf = {id:0,name:"New List"}
        },
        wasDeleted(idToRemove) {
            const index = this.favourites.findIndex(fav => fav.id === idToRemove);
            if (index !== -1) {
                this.favourites.splice(index, 1);
            } 
        }        

    },
    computed: {
        ...mapGetters(['getApiFavouritesUrl']),
        sortedFavourites: function() {
            const sortedFavouritesList = [...this.favourites].sort((a,b) => (a.name > b.name) ? 1 : -1)
            if (this.newlist) {
                return [{id:0,name:"New List"}].concat(sortedFavouritesList)
            } 
            return sortedFavouritesList
        },
    },
    created() {
        this.getFavourites();
    },
    watch:{
        $route (to){
            if (to.name == "Favourites") {
                this.getFavourites();
            }
        }
    }
}

</script>
<style scoped>
.title {
    font-weight: 700;
    font-style: Bold;
    font-size: 40px;
    line-height: 48px;
    letter-spacing: 0px;
    color:#202F3E;
}
.action-buttons {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 12px;

    font-weight: 600;
    font-size: 13px;
    line-height: 18px;
    letter-spacing: 0%;
    text-align: center;
}

.addlist,
.download {
    cursor:pointer;
    display: flex;
    align-items: center;
    gap: 4px;
    height: 40px;
    padding: 0 12px 0 8px;
    border-radius: 6px;
    white-space: nowrap;
    width: fit-content;
}

.addlist {
    color: #001A72;
    background-color: #E5EEFF;
}

.download {
    color: #202F3E;
    background-color: #EBEFF3;
}
</style>