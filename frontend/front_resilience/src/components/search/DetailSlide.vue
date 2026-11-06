<template>
  <transition name="slide-panel">
    <div v-if="visible" class="overlay" @click="$emit('close')">  
      <div class="popup" @click.stop>
        <div class="header"><span @click="$emit('close')"><Icon icon="cross" width="14" color="#93A4B5"></Icon></span></div>
        <div class="body">
            <span class="type" v-html="flatten(data['@type'])"></span>
            <div class="title" v-html="nl2br(flatten(data['name']))"></div>
            <div class="author" v-if="data['author'] != undefined" v-html="flatten(data['author'],'name')"></div>
            <div class="publisher" v-if="data['publisher'] != undefined" v-html="flatten(data['publisher'],'name')"></div>
            <div class="collection" v-if="data['isBasedOn']['isPartOf']['name'] != undefined" v-html="flatten(data['isBasedOn']['isPartOf']['name'])">
            </div>
            <div style="display:flex;padding-top:15px;padding-bottom:15px;width:auto">
                <div class="actionbutton" style="padding-top:3px" @click="showShare = !showShare">
                    <Icon icon="share" color="#001A72" width="15"></Icon>
                </div>
                <div v-if="showShare" class="contextmenu">
                    <SocialShare :title="flatten(data['name'])" :url="flatten(data['url'])" :description="flatten(data['description'])"></SocialShare> 
                </div>

                <div class="actionbutton" @click="toggleQuote()">
                    <Icon icon="quote" color="#001A72" width="15"></Icon>
                </div>

                <div class="actionbutton" style="background-color: inherit;border:1px solid #ADBDCC" @click="toggleShowMenu()"  v-if="this.getPermissions.indexOf('save') >= 0">
                    <Icon icon="star" color="#001A72" width="15"></Icon>
                </div>
                <div v-if="showMenu" class="contextmenu">
                    <div style="color:Gray;padding:8px 12px;">{{$ml.get('savetocollection')}}</div>
                    <div v-for="shelf, key in shelves" :key="'shelf_'+key" class="menuitem" @click="addToFavourite(shelf.name)">{{shelf.name}}</div>
                </div>
                <div class="detailbutton" @click="reset();visible=false;$emit('close');setActiveRecord(data);"><span style="margin-right:4px">{{ $ml.get('seealldetails') }}</span><Icon icon="arrow-right" width="19" color="White" style="padding-top:4px"></Icon></div>
            </div>
            <div v-if="showQuote" class="citation">
                <Citation :data="data" :longnames="true"></Citation>
            </div>

            <div v-if="data['description'] != undefined">
                <div class="propertylabel">{{ $ml.get('description') }}</div>
                <div class="propertydata" v-html="flatten(data['description'])"></div>
            </div>
            <div v-if="data['locationCreated'] != undefined">
                <div class="propertylabel">{{ $ml.get('publicationplace') }}</div>
                <div class="propertydata" v-html="flatten(data['locationCreated'],'name')"></div>
            </div>
            <div v-if="data['datePublished'] != undefined">
                <div class="propertylabel">{{ $ml.get('publicationdate') }}</div>
                <div class="propertydata" v-html="flatten(data['datePublished'])"></div>
            </div>
            <div v-if="data['inLanguage'] != undefined">
                <div class="propertylabel">{{ $ml.get('language') }}</div>
                <div class="propertydata" v-html="flatten(data['inLanguage'],'name')"></div>
            </div>

        </div>
     </div>

    </div>
  </transition>
</template>

<script>
import Icon from '../helpers/Icon.vue'
import SocialShare from '../helpers/SocialShare.vue'
import Citation from '../helpers/Citation.vue'
import axios from 'axios';
import { mapActions, mapGetters } from "vuex";
export default {
  name: "DetailSlide",
  data() {
    return {
        showMenu:false,
        showShare:false,
        showQuote:false,
        shelves:null
    }
  },
  props: {
    visible: {
      type: Boolean,
      default: false
    },
    value: {
        type:[]
    }
  },
  components: {
    Icon,
    SocialShare,
    Citation
  },
  computed:{
    ...mapGetters(["getApiShelvesUrl","getApiProfileUrl","getPermissions"]),
    data: function () {
        return this.value._source
    }
  },
  methods:{
    ...mapActions(["setActiveRecord"]),
    flatten(obj,subfield) {
        var l = []
        if (obj instanceof Array) {
            if (subfield == '' || subfield == null || subfield == undefined) {  
              l = obj.map(function (el) { 
                    if (el['@value'] != undefined) return el['@value'] 
                    else return el
                }.bind(subfield))
            } else {
              l = obj.map(function (el) { 
                    if (el[subfield]['@value'] == undefined) {
                        return el[subfield]
                    } else {
                        return el[subfield]['@value'] 
                    }
                }.bind(subfield)
            )}
            return l.join("<br/>")
        } else {
            if (subfield == '' || subfield == null || subfield == undefined) {
                if (obj["@value"] == undefined) {
                    return obj
                } else {
                    return obj["@value"]
                }
            } else {
                if (obj[subfield]["@value"] == undefined) {
                    return obj[subfield]
                } else {
                    return obj[subfield]["@value"]
                }
            }
        }
    },
    reset() {
        this.showQuote = false;
        this.showMenu = false;
        this.showShare = false;
    },
    nl2br(str) {
        return str.replace(/\r?\n/g, "<br />");
    },
    toggleShowMenu() {
        this.showMenu = !this.showMenu
    },
    toggleQuote() {
        this.showQuote = !this.showQuote
    },
    addToFavourite(shelf) {
        var obj = {shelf: shelf, values: [this.data["@id"]]}
        axios
        .post(this.getApiProfileUrl + '/item', obj)
        .then(
            this.showMenu = false
        )
        .catch(error => {
            console.log(error);
        });
    },
  },
  watch:{
    value: function () {
        axios.get(this.getApiShelvesUrl)
        .then(res => {
            this.shelves = res.data
        })
    }
  }  
};
</script>

<style scoped>
.overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.4);
  z-index: 1000;
}

.popup {
  position: fixed;
  top: 0;
  right: 0;
  width: 480px;
  height: 100vh;
  background: white;
  overflow-y: auto;
  box-shadow: -4px 0 12px rgba(0, 0, 0, 0.2);
}

.header {
    border-bottom: 1px solid #E6EBEF;
    text-align: right;
    padding:6px;
    padding-right:12p;

}
.body {
    margin:14px
}
.type {
    color:#001A72;
    background: #CEE0FF;
    border-radius:6px;
    padding: 4px 8px 4px 8px;
    font-size:13px

}
.title {
    color:#202F3E;
    font-weight: 700;
    font-style: Bold;
    font-size: 22px;
    line-height: 100%;
    letter-spacing: 0px;
    margin-top:10px;
    margin-bottom:10px;
}
.author {
    color:#202F3E;
    font-weight: 400;
    font-style: Regular;
    font-size: 13px;
    line-height: 15px;
    letter-spacing: 0px;
}

.publisher {
    color:#202F3E;
    font-weight: 400;
    font-style: Italic;
    font-size: 13px;
    line-height: 15px;
    letter-spacing: 0px;
}
.collection {
    font-weight: 400;
    font-style: Regular;
    font-size: 13px;
    line-height: 15px;
    letter-spacing: 0px;
}
.propertylabel {
    font-weight: 700;
    font-style: Bold;
    font-size: 13px;
    line-height: 18px;
    letter-spacing: 0%;
    margin-top:8px;
    margin-bottom:8px
}

.propertydata {
    font-weight: 400;
    font-style: Regular;
    font-size: 16px;
    line-height: 22px;
    letter-spacing: 0%;
    margin-bottom:8px
}

.actionbutton {
  width:32px;
  height:32px;
  background-color:#E5EEFF;
  border-radius:5px;
  text-align:center;
  cursor:pointer;
  margin-right:12px;
  vertical-align: middle;
  padding-top:1px;
  margin-right:8px
}
.detailbutton {
    display:flex;
    height:32px;
    background-color: #455B71; 
    color:#FFFFFF;
    margin-left:auto;
    border-radius:5px;
    text-align:center;
    cursor:pointer;
    vertical-align:middle;
    font-weight: 600;
    font-style: Semi Bold;
    font-size: 13px;
    line-height: 18px;
    letter-spacing: 0%;
    padding-left:8px;
    padding-right:8px;
    padding-top:6px
}

.slide-panel-enter-active,
.slide-panel-leave-active {
  transition: all 0.3s ease;
}

.slide-panel-enter,
.slide-panel-leave-to {
  opacity: 0;
}

.slide-panel-enter .popup,
.slide-panel-leave-to .popup {
  transform: translateX(100%);
}

.slide-panel-enter-active .popup,
.slide-panel-leave-active .popup {
  transition: transform 0.3s ease;
}

.contextmenu {
  position:absolute;
  margin-top:15px;
  margin-left:30px;
  background:white;
  border:1px solid #ccc;
  border-radius:4px;
  box-shadow:0 2px 8px rgba(0,0,0,.2);
  min-width:150px;
  font-size:13px;
  font-weight: 400;
  font-style: Regular;  
}

.menuitem {
  padding:8px 12px;
  cursor:pointer;
}

.menuitem:hover {
  background:#f5f5f5;
}

.citation {
    border-top: 1px solid #E6EBEF;
    border-bottom: 1px solid #E6EBEF;
    padding-bottom:10px;
}
</style>
