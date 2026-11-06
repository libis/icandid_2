<template>
  <div>
    <DetailSlide :visible="viewidx >= 0" v-model="getResultset[viewidx]" @close="viewidx = -1;$refs.detailslide.reset()" ref="detailslide"></DetailSlide>
    <table class="table fullwidth" width="100%" style="border-bottom:1px solid #dbdbdb">
      <tbody>
          <tr width="100%">
            <td style="padding-bottom:12px">
                <div class="field">
                  <input
                    type="checkbox"
                    class="is-checkradio is-small is-rtl"
                    id="allornone"
                    v-model="allornone"
                    @click="selectAllOrNone()"
                  />
                  <label for="allornone"></label> 
                </div>
              </td>
            <td colspan="3">
              <div style="display:flex">
              <div class="actionbutton" v-if="this.getPermissions.indexOf('export') >= 0" @click="exportSelection()">
                <Icon icon="download" width="15" color="#A3A3A3"></Icon>
              </div>
              <div class="actionbutton" v-if="this.getPermissions.indexOf('save') >= 0" @click="toggleShowMenu()">
                <Icon icon="star" width="15" color="#A3A3A3"></Icon>
              </div>
              <div v-if="showMenu" class="contextmenu">
              <div style="color:Gray;padding:8px 12px;">{{$ml.get('savetocollection')}}</div>
                <div v-for="shelf, key in shelves" :key="'shelf_'+key" class="menuitem" @click="addToFavourite(shelf.name)">{{shelf.name}}</div>
              </div>
              <div class="actionbutton" @click="showShare = !showShare"><Icon icon="share" width="15" color="#A3A3A3"></Icon></div>
              <div v-if="showShare" class="contextmenu">
                    <SocialShare v-if="theSelected.length == 1" :title="toShare('name')" :url="toShare('url')" :description="toShare('description')"></SocialShare> <!--  -->
                    <div style="padding:8px" v-else>Please select one result for sharing on social media</div>
                </div>
              </div>
            </td>
            <td style="font-size:13px;font-weight:Bold;vertical-align:middle;padding-bottom:10px;white-space:nowrap">
              {{ $ml.get('sortby') }}
            </td>
            <td colspan="2">
              <select v-model="$parent.sorting" class="relevance">
                <option value="relevance">{{ $ml.get('relevance')}} </option>
                <option value="datePublished">{{ $ml.get('publicationdate') }}</option>
                <option value="author">{{ $ml.get('author') }}</option>
                <option value="title">{{  $ml.get('title') }}</option>
              </select>
            </td>
          </tr>
          <tr width="100%" v-for="(rec, idx) in getResultset" :key="idx" >
              <td style="padding-top:5px">
                <div class="field">
                  <input
                    type="checkbox"
                    class="is-checkradio is-small is-rtl"
                    
                    :id="'res_ch_'+idx"
                    v-model="theSelected"
                    :value="rec._id"
                />
                <label :for="'res_ch_'+idx"></label>
                </div>
              </td>

              <td style="padding:5px;padding-top:10px">
                <div v-if="thumbnailUrl(rec._source) == undefined" class="iconcontainer" :style="'background:'+iconBgColors[rec._source['@type'].toLowerCase()]">
                  <Icon :color="iconColors[rec._source['@type'].toLowerCase()]" :icon="rec._source['@type']" width="23"></Icon> 
                </div>
                <div v-else class="thumbnailcontainer">
                  <img :src="thumbnailUrl(rec._source)" width="48">
                </div>

              </td> 
              <td style="padding-left:10px;padding-top:0px;padding-bottom:5px;padding-right:8px">
                <span class="rec_type" v-html="rec._source['@type']"></span> 
                <p @click="showDetail(idx)" class="rec_name" v-if="rec._source.name !== undefined" v-html="nl2br(parse(rec._source.name))"></p>
                <p class="rec_author" v-if="rec._source.author !== undefined" v-html="parse(rec._source.author)"></p>
              </td>
              <td style="vertical-align:middle;padding-right:8px">
                <p class="rec_publisher" v-if="rec._source.publisher !== undefined" v-html="parse(rec._source.publisher)"></p>
              </td>
              <td style="vertical-align:middle;padding-right:8px">
                 <p class="rec_dataset" v-if="rec._source.isBasedOn.isPartOf !== undefined" v-html="parse(rec._source.datePublished)"></p> 
              </td>
              <td style="vertical-align:middle;padding-right:8px">
                 <p class="rec_dataset" v-if="rec._source.isBasedOn.isPartOf !== undefined" v-html="parse(rec._source.isBasedOn.isPartOf)"></p> 
              </td>
              <td style="vertical-align:middle;padding-right:8px">
                <div class="rec_eye" @click="$refs.detailslide.reset();viewidx = idx"><Icon icon="eye" width="18" color="#607A93"></Icon></div>
              </td>
          </tr>
      </tbody>
    </table>
    <Pagination :listLength="getHits" :pageSize="getPagesize" :scope='4' ></Pagination>  
    <Dialog ref="dialog"></Dialog>
</div>
</template>
<script>

import { mapActions, mapGetters } from "vuex";
import Icon from '../helpers/Icon.vue';
import DetailSlide from './DetailSlide.vue'
import Pagination from '../helpers/Pagination.vue'
import Dialog from '../helpers/Dialog.vue'
import SocialShare from '../helpers/SocialShare.vue'
import axios from "axios";
axios.defaults.withCredentials = true;
import { iconColors, iconBgColors } from '../../../constants.js';

export default {
  components: {
    Icon,
    Pagination,
    DetailSlide,
    Dialog,
    SocialShare
  },  
  data() {
    return {
        allornone: false,
        theSelected:[],
        iconColors:{},
        iconBgColors:{},
        viewidx:-1,
        showMenu:false,
        showShare:false,
        shelves:[]
    }
  },
  methods: {
    ...mapActions(["setActiveResultIdx","setSelectedResults"]),
    loadMore() {
      this.$parent.extendResultset()
    },
    clearSelected() {
      this.theSelected = []
    },
    selectAllOrNone() {
      if (!this.allornone) {
        for(var i =0; i < this.getResultset.length; i++) {
            this.theSelected.push(this.getResultset[i]._source['@id'])
        }
      } else {
        this.theSelected = []
      }
    },
    parse(v) {
      if (Array.isArray(v)) {
        var l = Array()
        for (var i in v) {
          l.push(this.stripHTML(this.parse(v[i])))
        }
        return l.join('<br>')
      } else {
        if ((typeof v === 'string' || v instanceof String)) {
          return this.stripHTML(v)
        } else {
          if (v instanceof Object) {
            if (v.name != undefined) {
              if (v.name instanceof Object) {
                if (v.name["@value"] != undefined) {
                  return this.stripHTML(v.name["@value"])
                } else {
                  if (typeof v.name == 'string') {
                    return this.stripHTML(v.name)
                  }                  
                }
              } else {
                if (typeof v.name == 'string') {
                  return this.stripHTML(v.name)
                }
              }
            }
            if (v["@value"] != undefined) {
              return this.stripHTML(v["@value"])
            }
          }
        }
      }
      return "not defined"
    },
        nl2br(str) {
        return str.replace(/\r?\n/g, "<br />");
    },
    stripHTML(v) {
        if (typeof v != 'string') return v
        return v.replace(/(<([^>]+)>)/gi, "");
    },
    showDetail(idx) {
      this.$parent.setActive(this.getResultset[idx]._source)
    },
    thumbnailUrl(rec) {
      if (rec.associatedMedia == undefined) {
        return undefined
      } else {
        if (Array.isArray(rec.associatedMedia)) {
          return rec.associatedMedia[0].thumbnailUrl
        } else {
          return rec.associatedMedia.thumbnailUrl
        }
      }
     },
     exportSelection() {
      if (this.theSelected.length > 0 || this.allornone) {
        var url = this.getApiProfileUrl + '/queue'
        var format = 'csv'
        var request = {query:this.getSearchRequest, format:format, language: this.$ml.current }
        request.query.sids = (this.allornone?[]:this.theSelected)
        axios
          .post(url, request)
          .then(response => {
              this.$refs.dialog.alert(this.$ml.get('info'),this.$ml.with('email', response.data.email).get('exportinprogress'))
        })
      }
     },
     toggleShowMenu() {
        this.showMenu = !this.showMenu
        axios.get(this.getApiShelvesUrl)
        .then(res => {
          console.log(res.data)
            this.shelves = res.data
        })
    },
    addToFavourite(shelf) {
        var obj = {}
        if (this.allornone) {
          obj = {format:"shelfitem",language:"en",query:this.getSearchRequest,shelf:shelf}  
          axios
          .post(this.getApiProfileUrl + '/queue', obj)
          .then(
            this.showMenu = false
          )
          .catch(error => {
            console.log(error);
          });
        } else {
          if (this.theSelected.length > 0) {
            obj = {shelf: shelf, values:this.theSelected }
            axios
            .post(this.getApiProfileUrl + '/item', obj)
            .then(
              this.showMenu = false
            )
            .catch(error => {
              console.log(error);
            });
          }
        }
    },
    toShare(fld) {
      const found = this.getResultset.find(item => item._source['@id'] === this.theSelected[0])._source;
      return this.flatten(found[fld])
    },
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
            return l.join("\n")
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
  },
  computed: {
    ...mapGetters(["getResultset","getActiveResultIdx","getSearchStatus","getSelectedResults","getHits","getPage","getPagesize","getPermissions","getSearchRequest","getApiProfileUrl","getApiShelvesUrl"]),
    /*theSelected: {
        get() { return this.getSelectedResults },
        set(val) { this.setSelectedResults(val) } 
    }, */
    
  },
  mounted() {
    //window.addEventListener("resize", this.myEventHandler);
    //this.myEventHandler()
    this.iconColors = iconColors
    this.iconBgColors = iconBgColors
  },
  destroyed() {
    //window.removeEventListener("resize", this.myEventHandler);
  }
};
</script>
<style scoped>
.is-clickable {
  cursor: pointer;
}

.rec_type { 
    padding:2px; margin:0px ;
    border-radius: 4px;
    text-transform: capitalize;
    font-size:9px;
    font-weight:Normal;
    border:1px solid #ADBDCC;
    color:#455B71
}
.rec_name { 
    padding:0px; margin:0px ;
    font-weight:Bold;
    list-style-type:none;
    font-size: 16px;
    cursor:pointer;
}
p:hover.rec_name { 
  text-decoration: underline;
}

.iconcontainer {
  width:48px;height:64px; display: flex;  justify-content: center; align-items: center;
}
.thumbnailcontainer {
  width:48px; display: flex;  justify-content: center; align-items: center;
}

.rec_author { 
    padding:0px; margin:0px ;list-style-type:none;list-style-type:none;font-size:12px
}
.rec_publisher { 
    padding:0px; margin:0px ;
    font-style:Italic;
    list-style-type:none;;font-size:13px
}
.rec_dataset { 
    padding:0px; margin:0px ;list-style-type:none;list-style-type:none;font-size:11px
}
.rec_eye {
  cursor:pointer
}
.relevance {
    width:220px;
    height:30px;
    border:1px solid #607E97;
    border-radius: 5px;
    font-size:13px;
    font-weight:Bold;
    padding-left:10px;
    padding-right:10px;
    color:Black;
    background-color: White;
    vertical-align:middle
}

.el {
  padding: 6px;
  display: inline-flex;
  justify-content: center;
  align-content: center;
  min-width: 30px;
  background-color: white;
  transition-duration: .125s;
  cursor: pointer;
}
.actionbutton {
  width:32px;
  height:32px;
  background-color:#E5E5E5;
  border-radius:5px;
  text-align:center;
  cursor:pointer;
  margin-right:12px;
  vertical-align: middle;
  padding-top:1px
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
</style>