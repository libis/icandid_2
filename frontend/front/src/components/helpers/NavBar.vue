<template>
  <nav class="navbar" role="navigation" aria-label="main navigation">
    <div class="navbar-brand">
      <a class="navbar-item" href="/#/">
        <img src="/img/iCANDID_logo.png" height="28" />
      </a>

      <a
        role="button"
        class="navbar-burger burger"
        aria-label="menu"
        aria-expanded="false"
        data-target="navbarBasicExample"
        @click="navbarburgerToggle()"
        ref="navbarburger"
      >
        <span aria-hidden="true"></span>
        <span aria-hidden="true"></span>
        <span aria-hidden="true"></span>
      </a>
    </div>
    <div id="navbarBasicExample" ref="navbarBasicExample" class="navbar-menu">
      <div class="navbar-start"></div>

      <div class="navbar-end">
        <div v-for="m in menu" :key="m.id">
          <a v-if="m.route" class="navbar-item is-hidden" :href="m.route" ref="mi" :id="m.vis" :target="((m.route.substring(0, 4) == 'http')?'_blank':'')">{{ m.name }}</a>
          <div v-if="m.submenu" class="navbar-item has-dropdown is-hoverable is-hidden" ref="mi" :id="m.vis">
            <a class="navbar-link">{{ m.name }}</a>
            <div class="navbar-dropdown">
              <div v-for="n in m.submenu" :key="n.id">
                <a
                  v-if="n.route"
                  class="navbar-item is-hidden"
                  :href="n.route"
                  ref="mi"
                  :id="n.vis"
                  @click="closeMenu"
                >{{ n.name }}</a>
                <hr v-else class="navbar-divider" />
              </div>
            </div>
          </div>
        </div>
        <div>

          <div class="navbar-item has-dropdown is-hoverable">
            <a class="navbar-link" >{{ (this.$ml.current.toUpperCase()) }}</a>
            <div class="navbar-dropdown">
              <div v-for="l in $ml.list" :key="l">
                <a v-if="l != $ml.current" class="navbar-item" @click="setLang(l)">{{ ($ml.get(l)) }}</a> 
              </div>
            </div>
          </div>
          
        </div>
      </div>
    </div>
  </nav>
</template>
<script>
import { mapGetters } from "vuex";
export default {
    props:['auth','menu','lang'],
    data() {
        return {
            burgerstatus: false
        }
    },    
    watch: {
        auth: {
            handler: 'updateMenu'  
        },
        lang: {
            handler: 'updateMenu'  
        }

    },
    computed: {
      ...mapGetters(["getAuthenticated","getPermissions"]),
    },
    methods: {
        navbarburgerToggle(){
          if (this.burgerstatus) {
            this.$refs.navbarburger.classList.remove('is-active')
            this.$refs.navbarBasicExample.classList.remove('is-active')
          } else {
            this.$refs.navbarburger.classList.add('is-active')
            this.$refs.navbarBasicExample.classList.add('is-active')
          }
          this.burgerstatus=!this.burgerstatus
        },
        updateMenu() {
            this.$parent.updateMenu();
            for (var i in this.$refs.mi) {
                var item = this.$refs.mi[i];
                if (this.auth == undefined) {
                  this.$refs.mi[i].classList.add('is-hidden');
                } else {
                  if (this.getAuthenticated) {
                      if (this.checkPermissions(item.id)) {
                          this.$refs.mi[i].classList.remove('is-hidden')
                      } else {
                          this.$refs.mi[i].classList.add('is-hidden')
                      }
                      if (item.id == "unauth") {
                          this.$refs.mi[i].classList.add('is-hidden')
                      }
                      if (item.id == "noauth" || item.id == "" || item.id == undefined) {
                          this.$refs.mi[i].classList.remove('is-hidden')
                      }
                  } else {
                      //if (item.id == "auth") {
                          this.$refs.mi[i].classList.add('is-hidden')
                      //}
                      if (item.id == "unauth") {
                          this.$refs.mi[i].classList.remove('is-hidden')
                      }
                      if (item.id == "noauth" || item.id == "" || item.id == undefined) {
                          this.$refs.mi[i].classList.remove('is-hidden')
                      }
                  }
                }
            }
        },
        closeMenu(e) {
            let menu = e.currentTarget.parentElement.parentElement;
            if(e.target.parentElement.parentElement.classList.contains("navbar-dropdown")) menu.style.display = "none";
            setTimeout(()=>{ 
                menu.style.display = "";
                e.target.blur();
            },100);
        },
        setLang(l) {
          this.$ml.change(l)
          this.$cookies.set('lang',this.$ml.current)
        },
        checkPermissions(p) {
          var pp = p.split("||");
          for (var i=0; i<pp.length; i++) {
            if (this.getPermissions.indexOf(pp[i]) >= 0) return true
          }
          return false
        }
    },
    created() {
        this.updateMenu();
    }
}    

</script>
<style scoped>
.is-clickable {
  cursor: pointer;
}
</style>