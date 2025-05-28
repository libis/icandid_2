<template>
  <div class="content">
    <h1 class="title">{{ $ml.get('savedrecords') }}</h1>
    <ActionBar :class="{hidden:!(activeShelfId > 0 && this.getPermissions.indexOf('export') >= 0)}" :shelfId="activeShelfId"></ActionBar>
    <div class="columns">

      <div class="column is-2">
          <ShelfsList :shelves="sortedShelves" :activeShelfId="activeShelfId" @click="setActiveShelf"></ShelfsList>
      </div>
              
      <div class="is-divider-vertical" style="padding:0"></div>
      <div class="column is-5">
          <ItemsList v-if="activeShelfId > 0" :items="activeShelf" :activeItemId="activeItemId" @click="setActiveItem"></ItemsList>
      </div>
      
      <div v-if="activeShelfId > 0" class="is-divider-vertical" style="padding:0"></div>
      <div class="column is-5">
          <Detail v-if="activeItemId > 0" :activeResult="activeItem" :highlights="false"></Detail>
      </div>
      
    </div>
  </div>
</template>
<script>
import ShelfsList from './profile/ShelfsList.vue';
import ItemsList from './profile/ItemsList.vue';
import ActionBar from './profile/ActionBar.vue';
import Detail from './search/Detail.vue';
import { mapGetters, mapActions } from "vuex";
import axios from 'axios';
export default {
    components: {
        ShelfsList,
        ItemsList,
        Detail,
        ActionBar
    },
    data() {
        return {
            activeShelfId:0,
            activeItemId:0,
            refresh:0
        }
    },
    computed: {
        ...mapGetters(["getSavedShelves",'getPermissions','getApiShelvesUrl','getApiShelfUrl']),
        sortedShelves() {
            return [...this.getSavedShelves].sort((a,b) => (a.name > b.name) ? 1 : -1)
        },
        activeShelf() {
            this.refresh
            var r = [];
            try {
                if (this.activeShelfId != 0) {
                    r = this.getSavedShelves.filter(
                        function(shlf) { 
                            return shlf.id == this.activeShelfId ;
                        }.bind(this)
                    )[0].items.sort((a,b) => (a.value._source.headline > b.value._source.headline) ? 1 : -1);
                }
                for(var i=0; i<r.length; i++) {
                    if (r[i].value._source.name == undefined) {
                        r[i].value._source.name = {"@value":""}
                    }

                }
            }
            catch(err) {
                return []
            }
            return r;
        },
        activeItem() {
            try {
                if (this.activeItemId == 0) return [];
                return this.activeShelf.filter(function(itm) { return itm.id == this.activeItemId }.bind(this))[0].value;
            } 
            catch (err) {
                return []
            }
        }
    },
    methods: {
        ...mapActions(['setSavedShelves','addSavedShelf']),
        setActiveShelf(event) {
            if (event == 0) {
                this.activeShelfId = event
                return
            }
            var start = 0 
            var tmp = this.getSavedShelves.filter(s => s.id == event)
           
            if (tmp[0].items != undefined) {
                start = tmp[0].items.length
            }
            axios
                .get(this.getApiShelfUrl + '/' + event + '/' + start)
                .then(res => {
                    var data = {}
                    data.shelfid = event;
                    data.items = res.data;
                    this.addSavedShelf(data);
                    this.activeShelfId = event;
                    this.activeItemId = 0;
                    this.refresh += 1;
                })
                .catch(error => {
                    console.log(error);
                });      
            
        },
        loadMore() {
            var start = 0 
            var tmp = this.getSavedShelves.filter(s => s.id == this.activeShelfId)
           
            if (tmp[0].items != undefined) {
                start = tmp[0].items.length
            }
            axios
                .get(this.getApiShelfUrl + '/' + this.activeShelfId + '/' + start)
                .then(res => {
                    var data = {}
                    data.shelfid = this.activeShelfId;
                    data.items = res.data;
                    this.addSavedShelf(data);
                    this.refresh += 1;
                })
                .catch(error => {
                    console.log(error);
                });      
            
        },
        setActiveItem(event) {
            this.activeItemId = event;
        },
        getShelves() {
            axios
                .get(this.getApiShelvesUrl)
                .then(res => {
                    this.setSavedShelves(res.data);
                    window.setTimeout(function(t){
                        var tmp = t.activeShelfId;
                        t.setActiveShelf(0);
                        t.setActiveShelf(tmp);
                    }, 3000, this)
                })
                .catch(error => {
                    console.log(error);
                });      
        },
        getEnrichments() {
            var enrichments = []
            for (var idx in this.activeShelf) {

                if (this.activeShelf[idx]["value"]["_source"]["prov:wasAttributedTo"] != undefined) {
                    for (var jdx in this.activeShelf[idx]["value"]["_source"]["prov:wasAttributedTo"]) {
                        if (this.activeShelf[idx]["value"]["_source"]["prov:wasAttributedTo"][jdx]["prov:wasAssociatedFor"] != undefined) {
                            for (var kdx in this.activeShelf[idx]["value"]["_source"]["prov:wasAttributedTo"][jdx]["prov:wasAssociatedFor"]) {
                                if (this.activeShelf[idx]["value"]["_source"]["prov:wasAttributedTo"][jdx]["prov:wasAssociatedFor"][kdx].name != undefined) {
                                    enrichments.push(this.activeShelf[idx]["value"]["_source"]["prov:wasAttributedTo"][jdx]["prov:wasAssociatedFor"][kdx].name)
                                }
                            }
                        }
                    }
                } 
            }
            var enrichments_sorted = [...new Set(enrichments.sort())];
            var enrichments_obj = []
            for (idx in enrichments_sorted){
                enrichments_obj.push({id:enrichments_sorted[idx], name:enrichments_sorted[idx]})
            }
            return enrichments_obj
        }
    },
    created() {
        //this.setActiveShelf(this.sortedShelves[0].id);
        //this.setActiveItem(this.sortedShelves[0].items[0].id);
        //this.$parent.getUserInfo();
        this.getShelves();
    },
    watch:{
    $route (to){
        if (to.name == "Records") {
            //this.$parent.getUserInfo();
            this.getShelves();
        }
    }
} 
};
</script>
<style scoped>
.hidden {
  visibility: hidden
}
</style>