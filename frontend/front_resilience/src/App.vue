<template>
  <div id="app" class="container">
    <div class="block">
      <NavBar :auth="getAuthenticated" :menu="navmenu" :lang="$ml.current" ref="navbar"></NavBar>
    </div>
    <Message ref="popupwarnmessage"></Message>
    <transition name="fade" mode="out-in">
      <keep-alive>
        <router-view />
      </keep-alive>
    </transition>
    <footer class="footer">
      <div class="columns" >
        <div class="column is-2">
          <p><br>
            {{ $ml.get('platformby') }}<br/><a href="http://www.libis.be" target="_blank"><img src="/img/libis.png" width=85 valign="center"></a>
          </p>
        </div>
        <div class="column is-5">
          <a href="https://www.resilience-ri.eu/" target="_blank"><img src="/img/Logo_ReIReSearch.png" valign="center" style="height:100px;max-width:400px;z-index:10"></a>
        </div>
        <div class="column is-5">
          <span>
            <br>
          <img src="/img/EN_FundedbytheEU_RGB_POS.png" style="height:68px;vertical-align:top;margin-right:8px">
          </span>
        </div>
      </div>
    </footer>    
  </div>
</template>
<script>
import NavBar from './components/helpers/NavBar.vue'
import Message from './components/helpers/Message.vue'
import { mapGetters, mapActions } from "vuex";
import axios from 'axios';
export default {
  name: 'App',
  components: {
    NavBar,
    Message
  },
  data() {
    return {
      navmenu:[],
      
    }
  },
  computed: mapGetters(["getAuthenticated","getApiUserUrl","getUsername","getLandingpad","getUser","getPermissions","getDatasets"]),
  methods: {
    ...mapActions(["setUser","setHistory","setSearchStatus","addResults","clearResultset","setNav","setAggregations","clearLandingpad","setAuthentication","checkUserResource"]),
    updateMenu() {
      this.navmenu = [];
      this.navmenu = [
        // {id:"2", name:this.$ml.get('search'), vis:"search", submenu:[
        //   {id:"21", name:this.$ml.get('collections'), route:"/#/search/collection", vis:"search_collections"},
        //   {id:"22", name:this.$ml.get('media'), route:"/#/search/media", vis:"search_media"}
        // ]},
        {id:"2", name:this.$ml.get('search'), vis:"search", route:"/#/search"},
        {id:"1", name:this.$ml.get('collections'), route:"/#/collections", vis:"save"},
        {id:"3", name:this.$ml.get('statistics'), route:"/#/stats", vis:"noauth"},        
        {id:"4", name:this.$ml.get('about'), submenu: [
//          {id:"41", name:this.$ml.get('abouticandid'), route:"/#/about", vis:"noauth"},
//          {id:"42", name:this.$ml.get('aboutdatasets'), route:"/#/datasets", vis:"noauth"},
          {id:"41", name:this.$ml.get('aboutresilience'), route:"/#/page/aboutresilience", vis:"noauth"},
          {id:"42", name:this.$ml.get('aboutdatasets'), route:"/#/page/aboutdatasets", vis:"noauth"},
          {id:"43", name:this.$ml.get('eula'), route:"/#/eula", vis:"noauth"},
//          {id:"44", name:this.$ml.get('aboutcitations'), route:"/#/citation", vis:"noauth"},
//          {id:"44", name:this.$ml.get('aboutcitations'), route:"/#/page/aboutcitation", vis:"noauth"},
        ]},
//        {id:"5", name:this.$ml.get('help'), route:"/#/help", vis:"noauth"},
//        {id:"5", name:this.$ml.get('help'), route:"/#/page/help", vis:"noauth"},
        {id:"5", name:this.$ml.get('help'), route:"https://reiresearch.helpdocs.com/", vis:"noauth"},
/*        {id:"7", name:this.$ml.get('requests'), submenu:[
          {id:"71", name:this.$ml.get('access'), "route":"/#/request/access", vis:"unauth"},
          {id:"72", name:this.$ml.get('dataset_on_demand'), "route":"/#/request/dataset", vis:"auth"}
        ],}, */
        {id:"7", name:this.$ml.get('admin'), submenu:[
          {id:"71", name:this.$ml.get('users'), route:"/#/admin/users", vis:"admin"},
          {id:"72", name:this.$ml.get('groups'), route:"/#/admin/groups", vis:"admin"},
          {id:"73", name:this.$ml.get('collections'), route:"/#/admin/datasets", vis:"admin"},
          {id:"74", name:this.$ml.get('labels'), route:"/#/admin/labels", vis:"admin"},
          {id:"75", name:this.$ml.get('message'), route:"/#/admin/message", vis:"content"},
          {id:"76", name:this.$ml.get('texts'), route:"/#/admin/content", vis:"content"},
        ]},
        {id:"6", name:this.$ml.get('profile'), submenu: [
          {id:"61", name:this.$ml.get('savedsearches'), route:"/#/profile/searches", vis:"save"},
          {id:"62", name:this.$ml.get('savedrecords'), route:"/#/profile/records", vis:"save"},
          {id:"63", name:this.$ml.get('searchhistory'), route:"/#/profile/history", vis:"search"},
          {id:"64", name:"Divider", vis:"save"},
          {id:"60", name:this.getUsername, route:"/#/profile/user", vis:"save"},
        ]},
        {id:"8", name:this.$ml.get('logout'), "route":"/logout", vis:"logout"},
        {id:"9", name:this.$ml.get('login'), "route":"/login", vis:"login"}
      ]

      for (var i=0; i<this.navmenu.length; i++) {
        if (this.navmenu[i].submenu != undefined) {
          var l=[]
          for(var j=0; j<this.navmenu[i].submenu.length; j++) {
            l.push(this.navmenu[i].submenu[j].vis)
          }
          this.navmenu[i].vis = l.filter((v, k, a) => a.indexOf(v) === k).join('||') 
        }
      }
    },
    processData(res) {
      if (res.data.authenticated) {
        this.setHistory(res.data.history);
        this.setUser(res.data);
        this.setAuthentication(true);
        this.$refs.navbar.updateMenu();
      } else {
        this.setAuthentication(false);
        this.$refs.navbar.updateMenu();
      }
    },
    getUserInfo() {
      axios
      .get(this.getApiUserUrl)
      .then(res => {
        this.processData(res)
      })
      .catch(error => {
        //console.log(error);
        if (error.response.status === 401) {
          axios
            .get(this.getApiUserUrl + "/public")
            .then(res => {
              this.processData(res)
            })
            .catch(function() {
              //console.log(error);
              this.setAuthentication(false);
              this.$refs.navbar.updateMenu();
            }); 
        } else {
          this.setAuthentication(false);
          this.$refs.navbar.updateMenu();

        }
      });      
    },
    userCheckResource(ref)  {
        for(var i=0; i<this.getPermissions.length; i++) {
            if (this.getPermissions[i] == ref) {
            return true
            }
        }
        return false            
    },
    getExtraML() {
      axios
        .get('/res/extraml.json')
        .then(res => {
            var d=res.data
            var li = 0;
            this.$ml.list.forEach((l) => {              
              Object.keys(d[l]).forEach((i) =>{
                this.$ml.db[li][i] = d[l][i]
              })
              li++;
            })


        })
        .catch(error => console.log(error));          
    }    
  },
  mounted(){
    /*
    this.$cookies.config(60 * 60 * 24 * 365,'');
    if (this.$cookies.isKey('lang')) {
      this.$ml.change(this.$cookies.get('lang'))
    } else {
      this.$ml.change('nl')
      this.$cookies.set('lang',this.$ml.current)
    } */
    this.$ml.change('en')
    this.setAuthentication(false);
    this.$refs.navbar.updateMenu();
    this.getUserInfo();
    this.getExtraML();   
    document.title = this.$ml.get('projecttitle')
  },
  created() {
    if (this.getLandingpad) {
      window.setTimeout(function(lp,r,clp) {
        var newroute = '/record/' + lp[2];
        r.push(newroute)
        clp()
      }, 1000,this.getLandingpad, this.$router, this.clearLandingpad);
    }
  }
}
</script>
<style>
.fade-enter-active,
.fade-leave-active {
  transition-duration: 0.3s;
  transition-property: opacity;
  transition-timing-function: ease;
}

.fade-enter,
.fade-leave-active {
  opacity: 0
}

</style>
