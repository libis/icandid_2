<template>
  <div id="app" class="container" >
    <div class="block">
      <NavBar :auth="getAuthenticated" :menu="navmenu" :lang="$ml.current" ref="navbar"></NavBar>
    </div>
    <Message ref="popupwarnmessage"></Message>
    <transition name="fade" mode="out-in" >
      <keep-alive>
        <router-view />
      </keep-alive>
    </transition>
    <footer class="footer">
      
      <div class="columns" >        
        <div class="column is-half">
          <img src="https://assets.libis.be/reiresearch/footer_logo.png" width="209">
          <p>{{ $ml.get('footer_text') }}</p>
          <div class="columns" style="padding-top:20px">
            <div class="column is-3"><a class="link" href="/#/page/tos">{{ $ml.get('tos') }}</a></div>
            <div class="column is-3"><a class="link" href="/#/page/privacy">{{ $ml.get('privacy') }}</a></div>
            <div class="column is-3"><a class="link" href="/#/page/cookies">{{ $ml.get('cookies') }}</a></div>
            <div class="column is-3"></div>
          </div>

        </div>
        <div class="column is-half" style="display: grid; align-items: center; justify-items: center;">
          <div style="display:flex;gap:40px">
            <img src="https://assets.libis.be/reiresearch/eu_funded_logo_white.png" style="height:32px" >
            <img src="https://assets.libis.be/Logos/KU-Leuven-LIBIS-white-small.webp" style="height:32px" >
            <img src="https://assets.libis.be/reiresearch/resilience_logo_white.png" style="height:32px" >
          </div>
        </div>
      </div>
      
    </footer>    
    <div class="subfooter">
      <p>{{ $ml.get('subfooter') }}</p>
    </div>
    <TermsConsent
      @accepted="onTermsAccepted"
    />
  </div>
</template>
<script>
import NavBar from './components/helpers/NavBar.vue'
import Message from './components/helpers/Message.vue'
import TermsConsent from './components/TermsConsent.vue'
import { mapGetters, mapActions } from "vuex";
import axios from 'axios';
export default {
  name: 'App',
  components: {
    NavBar,
    Message,
    TermsConsent
  },
  data() {
    return {
      navmenu:[],
      
    }
  },
  computed: mapGetters(["getAuthenticated","getApiUserUrl","getApiUserPublicUrl","getUsername","getLandingpad","getUser","getPermissions","getDatasets"]),
  methods: {
    ...mapActions(["setUser","setHistory","setSearchStatus","addResults","clearResultset","setNav","setAggregations","clearLandingpad","setAuthentication","checkUserResource"]),
    updateMenu() {
      this.navmenu = [];
      this.navmenu = [
        /*{id:"2", name:this.$ml.get('search'), vis:"novis", route:"/#/search"}, */
        {id:"3", name:this.$ml.get('home'), vis:"noauth", route:"/#/"},
        {id:"1", name:this.$ml.get('collections'), route:"/#/collections", vis:"noauth"},
        {id:"4", name:this.$ml.get('about'), route:"/#/page/about", vis:"noauth"},
        {id:"5", name:this.$ml.get('help'), route:"https://reiresearch.helpdocs.com/", vis:"noauth"},
        {id:"6", name:this.$ml.get('contact'), route:"/#/contact", vis:"noauth"},
        {id:"7", name:this.$ml.get('admin'), submenu:[
          {id:"71", name:this.$ml.get('users'), route:"/#/admin/users", vis:"admin"},
          {id:"72", name:this.$ml.get('groups'), route:"/#/admin/groups", vis:"admin"},
          {id:"73", name:this.$ml.get('collections'), route:"/#/admin/datasets", vis:"admin"},
          {id:"74", name:this.$ml.get('labels'), route:"/#/admin/labels", vis:"admin"},
          {id:"75", name:this.$ml.get('message'), route:"/#/admin/message", vis:"content"},
          {id:"76", name:this.$ml.get('texts'), route:"/#/admin/content", vis:"content"},
        ]},
/*        {id:"6", name:this.$ml.get('profile'), submenu: [
          {id:"61", name:this.$ml.get('savedsearches'), route:"/#/profile/searches", vis:"save"},
          {id:"62", name:this.$ml.get('savedrecords'), route:"/#/profile/records", vis:"save"},
          {id:"63", name:this.$ml.get('searchhistory'), route:"/#/profile/history", vis:"search"},
          {id:"64", name:"Divider", vis:"save"},
          {id:"60", name:this.getUsername, route:"/#/profile/user", vis:"save"},
        ]}, */
        //{id:"8", name:this.$ml.get('logout'), "route":"/logout", vis:"logout"},
        //{id:"9", name:this.$ml.get('login'), "route":"/login", vis:"login"}
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
            .get(this.getApiUserPublicUrl)
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
    }, 
    onTermsAccepted() {
      console.log('Terms accepted');
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
    this.unsubscribe ==
      this.$store.subscribe((mutation) => {
        if (mutation.type == "setSelectedResults")
          console.log("Mutation", mutation)
      });
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
.subfooter {
  background-color:#000E52;
  color:White;
  padding: 10px 50px 10px 50px;
  font-size: 12px;
  
}
a.link {
  color:White;
  text-decoration: underline;

}
a:hover.link {
  color:White;
  text-decoration: underline;

}

</style>
