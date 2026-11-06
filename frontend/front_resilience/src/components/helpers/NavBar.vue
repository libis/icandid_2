<template>
  <nav class="navbar" role="navigation" aria-label="main navigation">
    <div class="navbar-brand">
      <a class="navbar-item" href="/#/">
        <img src="/img/Logo_ReIReSearch.png" height="40" />
      </a>

      <a role="button" class="navbar-burger burger" aria-label="menu" aria-expanded="false"
        data-target="navbarBasicExample" @click="navbarburgerToggle()" ref="navbarburger">
        <span aria-hidden="true"></span>
        <span aria-hidden="true"></span>
        <span aria-hidden="true"></span>
      </a>
    </div>
    <div id="navbarBasicExample" ref="navbarBasicExample" class="navbar-menu">
      <div class="navbar-start"></div>

      <div class="navbar-end" style="width:100%;justify-content:left;vertical-align:center;padding-top:45px">
        <div v-for="m in menu" :key="m.id">
          <a v-if="m.route" class="navbar-item" :class="{ ispath: isPath(m.name, m.route) }" :href="m.route" ref="mi"
            :id="m.vis" :target="((m.route.substring(0, 4) == 'http') ? '_blank' : '')" @click="click(m.route)">{{
              m.name
            }}</a>
          <div v-if="m.submenu" class="navbar-item has-dropdown is-hoverable is-hidden" style="padding:0px;margin:0px"
            ref="mi" :id="m.vis">
            <a class="navbar-item" :class="{ ispath: isPath(m.name, '/#/admin') }">{{ m.name }}</a>
            <div class="navbar-dropdown">
              <div v-for="n in m.submenu" :key="n.id">
                <a v-if="n.route" class="navbar-item is-hidden" :href="n.route" ref="mi" :id="n.vis"
                  @click="closeMenu">{{ n.name }}</a>
                <hr v-else ref="mi" :id="n.vis" class="navbar-divider" />
              </div>
            </div>
          </div>
        </div>
        <div>

        </div>
      </div>
      <div style="white-space:nowrap;width:auto;vertical-align:center;padding-top:45px;">
        <div style="display:flex;" v-if="(this.getPermissions.indexOf('save')>-1)">
          <div v-if="getHistory.length > 0">
            <a class="menuitem" href="/#/profile/history" :class="{ ispath: isPath('History', '/#/profile/history') }">
              <Icon width="16" icon="history" color="#202F3E"></Icon>
              <span>{{ $ml.get('history') }}</span>
            </a>
          </div>
          <div>
            <a class="menuitem" href="/#/profile/favourites" :class="{ ispath: isPath('History', '/#/profile/favourites') }">
              <Icon width="16" icon="star" color="#202F3E"></Icon>
              <span>{{ $ml.get('favourits') }}</span>
            </a>
          </div>
          <a href="/#/search" class="menubutton">
            <Icon icon="search" color="#202F3E" width="18"></Icon>
          </a>
          <a href="/#/" class="avatar">
            <Icon icon="avatar" color="#455B71" width="15"></Icon>
          </a>
          
        </div>
        <div style="display:flex;" v-if="!(this.getPermissions.indexOf('save')>-1)">
          <a href="/#/search" class="menubutton">
            <Icon icon="search" color="#202F3E" width="18"></Icon>
          </a>
          <a href="/login" class="menubutton dark">
            <Icon icon="access" color="#FFFFFF" width="19"></Icon><span>{{ $ml.get('access') }}</span>
          </a>
        </div>
      </div>
    </div>
  </nav>
</template>
<script>
import { mapGetters, mapActions } from "vuex";
import Icon from './Icon.vue'
export default {
  props: ['auth', 'menu', 'lang'],
  data() {
    return {
      burgerstatus: false
    }
  },
  components: {
    Icon
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
    ...mapGetters(["getAuthenticated", "getPermissions", "getUsername","getHistory"]),
  },
  methods: {
    ...mapActions(["setActiveRecord"]),
    navbarburgerToggle() {
      if (this.burgerstatus) {
        this.$refs.navbarburger.classList.remove('is-active')
        this.$refs.navbarBasicExample.classList.remove('is-active')
      } else {
        this.$refs.navbarburger.classList.add('is-active')
        this.$refs.navbarBasicExample.classList.add('is-active')
      }
      this.burgerstatus = !this.burgerstatus
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
      if (e.target.parentElement.parentElement.classList.contains("navbar-dropdown")) menu.style.display = "none";
      setTimeout(() => {
        menu.style.display = "";
        e.target.blur();
      }, 100);
    },
    setLang(l) {
      this.$ml.change(l)
      this.$cookies.set('lang', this.$ml.current)
    },
    checkPermissions(p) {
      var pp = p.split("||");
      for (var i = 0; i < pp.length; i++) {
        if (this.getPermissions.indexOf(pp[i]) >= 0) return true
      }
      return false
    },
    click(r) {
      if (r == "/#/search") {
        this.setActiveRecord(null)
      }
    },
    isPath(n, r) {
      if (r == undefined) {
        return false
      }
      if (r == "/#/") {
        return (this.$route.path == "/")
      } else {
        return (('/#' + this.$route.path).startsWith(r))
      }

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

.navbar-item {
  padding: 4px;
  margin: 5px;
  margin-left: 15px;
  white-space: nowrap;
  text-decoration: none;
  font-family: Inter;
  font-weight: 700;
  font-size: 15px;
  line-height: 19px;
  letter-spacing: 0%;
  vertical-align: middle;
  color: #202F3E;
  
  text-decoration: none
}

.navbar-item:focus {
  color: #202F3E;
  background-color: #FFFFFF;
  text-decoration: none;
}

.navbar-item:hover {
  background-color: #BADEFE;
  border-radius: 4px;
  color: #0625AC;
  text-decoration: none
}
.navbar-item.ispath {
  color: #0625AC;
  /*text-decoration: underline; */
  border-bottom:2px solid #0625AC; 
  cursor:default
}
.navbar-item.ispath:hover {
  color: #0625AC;
  border-radius: 0px;
  background-color: #FFFFFF;
  text-decoration: none;
  cursor:default
}
.menuitem {
  display:flex;
  border-radius: 4px;
  color:#202F3E;
  padding:4px;
  text-decoration: none;
  margin-left:15px
}

.menuitem span {
  padding-left:6px;
  padding-top:6px;
  font-weight: 600;
  font-size: 12px;
  line-height: 16px;
  letter-spacing: 0%;
  vertical-align: middle;
}

.menuitem:hover {
  background-color: #BADEFE;
  color: #0625AC;
  text-decoration: none
}

.menubutton {
  width:40px;
  height:40px;
  border-radius:6px;
  background-color: #EBEFF3;
  text-align:center;
  vertical-align: middle;
  margin-left:15px;
  padding-top:9px;
  text-decoration:none;
}
.menubutton.dark span{
  padding-left:6px;
  padding-top:4px;
  font-weight: 600;
  font-size: 12px;
  line-height: 16px;
  letter-spacing: 0%;
  vertical-align: middle;
  color:#FFFFFF
}
.menubutton:hover {
  background-color:#D3DCE4
}

.menubutton.dark {
  display:flex;
  width:auto;
  padding-left:9px;
  padding-right:9px;
  background-color:#455B71
}

.menubutton.dark:hover {
  background-color:#2B3E52
}


.avatar {
  width:32px;
  height:32px;
  border-radius:16px;
  background-color: #EBEFF3;
  padding-top:3px;
  text-align:center;
  vertical-align: middle;
  margin-left:15px;
  margin-top:4px;
}
</style>