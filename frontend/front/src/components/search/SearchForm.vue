<template>
  <div class="box" style="padding-bottom:0.1rem;margin-bottom:0.7rem">
    <div class="tabs is-boxed">
      <ul>
        <li v-bind:class="{'is-active': (selectedTab=='normal')}">
          <a @click.prevent.stop="selectedTab='normal'">{{ $ml.get('search') }}</a>
        </li>
        <!--<li v-bind:class="{'is-active': (selectedTab=='simple')}">
          <a @click.prevent.stop="selectedTab='simple'">{{ $ml.get('simplesearch') }}</a>
        </li>-->
        <li v-bind:class="{'is-active': (selectedTab=='advanced')}">
          <a @click.prevent.stop="selectedTab='advanced'">{{ $ml.get('advancedsearch') }}</a>
        </li>
      </ul>
    </div>
    <div class="content" v-bind:class="{'is-hidden': (selectedTab!='normal')}">
        <NormalSearch ref="normal" :lang="$ml.current"></NormalSearch>
    </div>
    <div class="content" v-bind:class="{'is-hidden': (selectedTab!='simple')}">
        <SimpleSearch ref="simple"></SimpleSearch>
    </div>
    <div class="content" v-bind:class="{'is-hidden': (selectedTab!='advanced')}">
        <AdvancedSearch ref="advanced"></AdvancedSearch>
    </div>
    <div class="level">
      <div class="level-left">
        <a href="/#/citation" v-html="$ml.get('howtocite')"></a>
      </div>
      <div class="level-right">
        <div class="level-item">
          <button class="button is-primary" :class="{'is-loading':getSearchStatus}" @click="search()" >{{ $ml.get('search') }}</button>
          <button v-if="getHits>-1" class="button is-secondary" style="margin-left:10px" @click="clear()" >{{ $ml.get('clearsearch') }}</button>
        </div>
      </div>
    </div>
    <Dialog ref="dialog"></Dialog>
  </div>
</template>
<script>
import SimpleSearch from './SimpleSearch.vue'
import NormalSearch from './NormalSearch.vue'
import AdvancedSearch from './AdvancedSearch.vue'
import Dialog from '../helpers/Dialog.vue'
import axios from 'axios'
import { mapActions, mapGetters } from 'vuex';
export default {
  data() {
    return {
      selectedTab: "normal",
    };
  },
  components: {
      SimpleSearch,
      NormalSearch,
      AdvancedSearch,
      Dialog
  },
  methods: {
      ...mapActions(['setQuery','setHits','setUser']),
      search() {
        var queryObj = this.$refs[this.selectedTab].queryObj();
        this.setQuery(queryObj)
        this.$parent.mode='result'
      },
      clear() {
        this.$refs[this.selectedTab].clear();
        this.setHits(-1)
        this.$parent.mode='result'
      },
      saveQuery() {
        axios
          .post(this.getApiProfileUrl + '/query', this.getSearchRequest)
          .then(res => { 
            this.setUser(res.data);
            this.$refs.dialog.alert(this.$ml.get('info'),this.$ml.get('searchsaved'))
          })
          .catch(error => console.log(error));
      }
  },
  computed: {...mapGetters(['getSearchStatus','getHits','getApiProfileUrl','getSearchRequest','getUser','getDatasets']), 
    labels() {
      this.lang; // forces the labels list to be recomputed (and re-sorted !) on UI-language-change (weird I know)

      let tmp_labels = []
      for (var i=0; i<this.getDatasets.length; i++) {
        for (var j=0; j<this.getDatasets[i].labels.length; j++) {
          tmp_labels.push(this.getDatasets[i].labels[j]);
        }
      }
      tmp_labels =  [... new Map(tmp_labels.map(item => [item['id'], item])).values()]; // unique labels by id 
      for( i=0; i<tmp_labels.length; i++) {
        tmp_labels[i].name = tmp_labels[i]['name_'+this.$ml.current];
      }

      return tmp_labels.sort(
        function(a,b) {
          if (a.name < b.name) return -1;
          if (a.name > b.name) return 1;
          return 0
        }
        );

    }, 
    languages() {
      this.lang; // forces the language list to be recomputed (and re-sorted !) on UI-language-change (weird I know)
      let tmp_langs = []
        for (var i=0; i<this.getDatasets.length; i++) {
          for (var j=0; j<this.getDatasets[i].languages.length; j++) {
          tmp_langs.push(this.getDatasets[i].languages[j]);
        }
      }

      tmp_langs =  [... new Map(tmp_langs.map(item => [item['id'], item])).values()]; // unique languages by id 
      for( i=0; i<tmp_langs.length; i++) {
        tmp_langs[i].name = tmp_langs[i]['name_'+this.$ml.current];
      }

      return tmp_langs.sort(
        function(a,b) {
          //if (a.name < b.name) return -1;
          //if (a.name > b.name) return 1;
          return (a.id-b.id)
        }
        );

    }, 
    datasets() {
      let tmp_datasets = []
      // for (var i=0; i<this.getDatasets.length; i++) {
      //   if (this.$route.path == '/search/media' && this.getDatasets[i].ismedia == 1) {
      //     tmp_datasets.push(this.getDatasets[i]);
      //   }
      //   if (this.$route.path == '/search/collection' ) {
      //     tmp_datasets.push(this.getDatasets[i]);
      //   }

      // }
      for (var i=0; i<this.getDatasets.length; i++) {
          tmp_datasets.push(this.getDatasets[i]);
      }

      return tmp_datasets.sort(
        function(a,b) {
          if (a.name < b.name) return -1;
          if (a.name > b.name) return 1;
          return 0
        }
      );
    }
  }
};
</script>