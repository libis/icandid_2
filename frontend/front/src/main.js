import Vue from 'vue'
import Vuex from 'vuex'
import VueRouter from 'vue-router'
import App from './App.vue'

import Home from './components/Home.vue'
import Search from './components/Search.vue'
import Statistics from './components/Statistics.vue'
import About from './components/about/About.vue'
import AboutDatasets from './components/about/Datasets.vue'
import EULA from './components/about/EULA.vue'
import Help from './components/about/Help.vue'
import Records from './components/Records.vue'
import Searches from './components/Searches.vue'
import SearchHistory from './components/SearchHistory.vue'
import Collections from './components/Collections.vue'

import AccessRequest from './components/requests/Access.vue'
import NewDatasetRequest from './components/requests/Newdataset.vue'
import DatasetRequest from './components/requests/Dataset.vue'

import Users from './components/admin/Users.vue'
import Datasets from './components/admin/Datasets.vue'
import Groups from './components/admin/Groups.vue'
import Labels from './components/admin/Labels.vue'
import Status from './components/admin/Status.vue'
import Content from './components/admin/Content.vue'

import Record from './components/Record.vue' 
import User from './components/User.vue'
import VueMomentLib from 'vue-moment-lib'
import infiniteScroll from 'vue-infinite-scroll'
import './ml'
import VTooltip from 'v-tooltip'

import linkify from 'vue-linkify'
Vue.directive('linkified', linkify)

import VueGtag from "vue-gtag"

import VueCookies from 'vue-cookies'
Vue.use(VueCookies)

Vue.config.productionTip = false

Vue.use(Vuex);
Vue.use(VueMomentLib);
Vue.use(VueRouter);
Vue.use(infiniteScroll);

Vue.use(VTooltip)

Vue.filter('stripHTML', function (value) {
  const div = document.createElement('div')
  div.innerHTML = value
  const text = div.textContent || div.innerText || ''
  return text
});

const store = new Vuex.Store(
  {
    state: {
      user: {
        firstname: "",
        lastname: "",
        authenticated:3.14,
        eppn:"",
        email:"",
        permissions:{
          resources:[],
          databases:[]
        }
      },
      eshelves:[],
      api_uri:{
        baseurl:"http://localhost:88/api",
        paths: {
          search:"/search",
          query:"/query",
          collection:"/collection",
          user:"/profile/user",
          shelf:"/profile/shelf",
          shelves:"/profile/shelves",
          profile:"/profile",
          ddlists:"/ddlists",
          stats:"/stats",
          form:"/form",
          admin:"/admin",
          status:"/status",
          content:"/content",
          record:"/json"
        }
      },
      search:{
        queryObj:{
          q:'',
          s:'relevance',
          f:[],
          nav:'first'
        },
        resultset:[],
        activeResultIdx:0,
        selectedResults:[],
        loading:false,
        resultType:false,
        hits:-1,
        aggregations:[],
        history: []
      },
      landingpad : window.location.href.match(/\/#\/(record)\/(.+)/),
      elasticquery:'',
      selectedDatasets:[]
    },
    getters: {
      getAuthenticated: (state) => state.user.authenticated,
      getUser: (state) => {
        var tmp = JSON.parse(JSON.stringify(state.user));
        delete tmp.eshelves
        delete tmp.permissions
        delete tmp.queries
        delete tmp.history
        return tmp
      },
      getUsername: (state) => {
        if (state.user.lastname != "") {
          return state.user.firstname + ' ' + state.user.lastname
        } else {
          return state.user.eppn
        }
      },
      getPermissions: (state) => {
        return state.user.permissions.resources.map(function(val) {
          return val.reference
        })
      },
      getDatasets: (state) => state.user.permissions.datasets,
      getEditions: (state) => state.user.permissions.editions,
      getApiUrl: (state) => state.api_uri.baseurl,
      getApiQueryUrl: (state) => state.api_uri.baseurl + state.api_uri.paths.query,
      getApiSearchUrl: (state) => state.api_uri.baseurl + state.api_uri.paths.search,
      getApiFormUrl: (state) => state.api_uri.baseurl + state.api_uri.paths.form,
      getApiUserUrl: (state) => state.api_uri.baseurl + state.api_uri.paths.user,
      getApiShelfUrl: (state) => state.api_uri.baseurl + state.api_uri.paths.shelf,
      getApiShelvesUrl: (state) => state.api_uri.baseurl + state.api_uri.paths.shelves,
      getApiProfileUrl: (state) => state.api_uri.baseurl + state.api_uri.paths.profile,
      getApiDdlistsUrl: (state) => state.api_uri.baseurl + state.api_uri.paths.ddlists,
      getApiStatsUrl: (state) => state.api_uri.baseurl + state.api_uri.paths.stats,
      getApiAdminUrl: (state) => state.api_uri.baseurl + state.api_uri.paths.admin,
      getApiStatusUrl: (state) => state.api_uri.baseurl + state.api_uri.paths.status,
      getApiContentUrl: (state) => state.api_uri.baseurl + state.api_uri.paths.content,
      getApiCollectionUrl: (state) => state.api_uri.baseurl + state.api_uri.paths.collection,
      getApiRecordUrl: (state) => state.api_uri.baseurl + state.api_uri.paths.record,
      getResultset:(state) => state.search.resultset,
      getActiveResult: (state) => state.search.resultset[state.search.activeResultIdx],
      getActiveResultIdx: (state) => state.search.activeResultIdx,
      getSearchRequest: (state) => {
        return state.search.queryObj
      },
      getSearchStatus: (state) => state.search.loading,
      getSorting: (state) => state.search.queryObj.s,
      getHits: (state) => state.search.hits,
      getAggregations: (state) => state.search.aggregations,
      getFilters: (state) => state.search.queryObj.f,
      getSavedQueries: (state) => state.user.queries,
      getSavedSets: (state) => {
        return state.eshelves.map(function(val) {
          return val.name
        })
      },
      getSavedShelves: (state) => state.eshelves,
      getHistory: (state) => state.search.history,
      getSelectedResults: (state) => state.search.selectedResults,
      getLandingpad: (state) => state.landingpad,
      getElasticQuery: (state) => state.elasticquery,
      getSelectedDatasets: (state) => state.selectedDatasets
    },
    actions: {
      setAuthentication( { commit }, s) {
        commit('setAuthentication',s)
      },
      setUsername( { commit }, name) {
        commit('setUsername', name)
      },
      setQuery( { commit }, q) {
        commit ('clearResultset')
        commit ('clearFilters')
        commit ('setQuery', q)
      },
      setSearchStatus( {commit} , s) {
        commit ('setSearchStatus', s)
      },
      clearResultset( {commit}) {
        commit('clearResultset')
      },
      addResults( {commit}, data) {
        for (var i=0; i<data.length; i++) {
          if (data[i]._source.name == undefined) {
            data[i]._source.name = {"@value":""}
          }
        }
        commit('addResults', data)
      },
      setActiveResultIdx( {commit}, idx) {
        commit('setActiveResultIdx', idx);
      },
      setSorting( {commit}, s) {
        commit('clearResultset')
        commit('setSorting', s)
      },
      setNav( {commit}, n) {
        commit ('setNav', n)
      },
      setHits( {commit}, num) {
        commit('setHits', num)
      },
      setAggregations( {commit} , aggs) {
        commit('setAggregations', aggs)
      },
      setFilters( {commit}, filters) {
        commit('setFilters', filters)
      },
      saveQuery( {commit}, msg ) {
        commit('saveQuery', msg)
      },
      setUser( { commit }, user) {
        delete user.history;
        user.validData = true
        commit('setUser', user)
      },
      setHistory(  { commit }, history) {
        commit('setHistory', history);
      },
      setSelectedResults( { commit }, sel) {
        commit('setSelectedResults', sel)
      },
      clearLandingpad( { commit} ) {
        commit('clearLandingpad')
      },
      clearAggregations( { commit} ) {
        commit('clearAggregations')
      },
      setElasticQuery( { commit }, q ) {
        commit('setElasticQuery', q)
      },
      setSelectedDatasets( { commit } , ds) {
        commit('setSelectedDatasets', ds)
      },
      setSavedShelves( { commit }, s) {
        commit('setSavedShelves', s)
      },
      addSavedShelf( { commit }, data) {
        commit('addSavedShelf', data);
      }

    },
    mutations: {
      setAuthentication: (state, s) => {state.user.authenticated = s},
      setUsername: (state, name) => {state.user.name = name},
      clearResultset: (state) => {
        state.search.resultset = [];
        state.search.activeResultIdx = 0;
        state.search.hits = -1;
        state.search.queryObj.nav = 'first';
        state.search.selectedResults = []
      },
      clearFilters: (state) => { state.search.queryObj.f = [] },
      addResults: (state, data) => {
        data.forEach(
          function (value) {
            state.search.resultset.push(value);
          }
        )
      },
      setQuery: (state, q) => {
        state.search.queryObj = q
      },
      setSearchStatus: (state, s) => {state.search.loading = s},
      setActiveResultIdx: (state, idx) => { state.search.activeResultIdx = idx},
      setSorting: (state, s) => { 
        state.search.queryObj.s = s 
      },
      setNav: (state, n) => { state.search.queryObj.nav = n },
      setHits: (state, num) => { state.search.hits = num },
      setAggregations: (state, aggs) => { 
        if (state.search.aggregations.length == 0) {
          state.search.aggregations = aggs 
        } 
      },
      setFilters: (state, filters) => { state.search.queryObj.f = filters },
      setUser: (state, user) => {
        if (user.authenticated) {
          state.user = user
        } 
      },
      setHistory: (state, history) => {
        state.search.history = history
      },
      setSelectedResults: (state, selected) => { state.search.selectedResults = selected },
      clearLandingpad: (state) => { state.landingpad = null },
      clearAggregations: (state) => { state.search.aggregations = [] },
      setElasticQuery: (state, q) => { state.elasticquery = q },
      setSelectedDatasets: (state, ds) => { state.selectedDatasets = ds},
      setSavedShelves: (state, s) => { state.eshelves = s },
      addSavedShelf: (state, data) => { 
        if (state.eshelves.filter(e => e.id == data.shelfid)[0].items == undefined) {
          state.eshelves.filter(e => e.id == data.shelfid)[0].items = []
        }
        state.eshelves.filter(e => e.id == data.shelfid)[0].items = state.eshelves.filter(e => e.id == data.shelfid)[0].items.concat(data.items)
      }
    }
  }
);


function checkUserResource(ref) {
  return (store.state.user.permissions.resources.map(function(val) { return val.reference }).indexOf(ref) >= 0)
}

const router = new VueRouter(
  {
    //mode: 'history',
    routes: [
      {
        path: '/',
        name: 'Home',
        component: Home
      }, 
      {
        path: '/search',
        name: 'Search',
        component: Search,
        beforeEnter: (to, from, next) => ( checkUserResource('search')?next():next(false) )
      },

      {
        path: '/record/:id',
        name: 'Record',
        component: Record,
        beforeEnter: (to, from, next) => ( checkUserResource('search')?next():next(false) )
      },
      {
        path: '/record/:id/json',
        name: 'JSON',
        component: Record,
        beforeEnter(to) {
          console.log(to.params.id)
          window.location.href = store.state.api_uri.baseurl + store.state.api_uri.paths.record + "/" + to.params.id;
        }
      },
      {
        path: '/stats',
        name: 'Statistics',
        component: Statistics,
      },      
      {
        path: '/about',
        name: 'About',
        component: About,
      },
      {
        path: '/datasets',
        name: 'AboutDatasets',
        component: AboutDatasets,
      },
      {
        path: '/eula',
        name: 'EULA',
        component: EULA,
      },
      {
        path: '/help',
        name: 'Help',
        component: Help,
      },
      {
        path: '/profile/records',
        name: 'Records',
        component: Records,
        beforeEnter: (to, from, next) => ( checkUserResource('search')?next():next(false) )
      },
      {
        path: '/profile/searches',
        name: 'Searches',
        component: Searches,
        beforeEnter: (to, from, next) => ( checkUserResource('search')?next():next(false) )
      },
      {
        path: '/profile/history',
        name: 'SearchHistory',
        component: SearchHistory,
        beforeEnter: (to, from, next) => ( checkUserResource('search')?next():next(false) )
      },
      {
        path: '/profile/user',
        name: 'User',
        component: User,
        beforeEnter: (to, from, next) => ( checkUserResource('search')?next():next(false) )
      }, 
      {
        path: '/request/access',
        name: 'Access',
        component: AccessRequest
      },
      {
        path: '/request/dataset',
        name: 'Dataset',
        component: DatasetRequest
      },
      {
        path: '/request/newdataset',
        name: 'Newdataset',
        component: NewDatasetRequest,
        beforeEnter: (to, from, next) => ( checkUserResource('search')?next():next(false) )        
      },
      {
        path: '/admin/users',
        name: 'Users',
        component: Users,
        beforeEnter: (to, from, next) => ( checkUserResource('admin')?next():next(false) )
      },
      {
        path: '/admin/datasets',
        name: 'Datasets',
        component: Datasets,
        beforeEnter: (to, from, next) => ( checkUserResource('admin')?next():next(false) )
      },
      {
        path: '/admin/groups',
        name: 'Groups',
        component: Groups,
        beforeEnter: (to, from, next) => ( checkUserResource('admin')?next():next(false) )
      },
      {
        path: '/admin/labels',
        name: 'Labels',
        component: Labels,
        beforeEnter: (to, from, next) => ( checkUserResource('admin')?next():next(false) )
      },
      {
        path: '/admin/message',
        name: 'Status',
        component: Status,
        beforeEnter: (to, from, next) => ( checkUserResource('content')?next():next(false) )
      },
      {
        path: '/admin/content',
        name: 'Content',
        component: Content,
        beforeEnter: (to, from, next) => ( checkUserResource('content')?next():next(false) )
      },
      {
        path: '/collections',
        name: 'Collections',
        component: Collections
      }
    ]
  }
);
Vue.use(VueGtag, {
  config: { id: "UA-203016416-1" }
}, router);

new Vue({
  store,
  router,
  render: h => h(App),
}).$mount('#app')