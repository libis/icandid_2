<template>
  <div>
      <div style="display:flex;border-bottom:1px solid #F0F0F0;padding-bottom:40px">
        <div style="width:160px;height:213px">
          <div v-if="thumbnailUrl() == undefined" class="iconcontainer" :style="'background:'+iconBgColors[data['@type'].toLowerCase()]">
            <Icon :color="iconColors[data['@type'].toLowerCase()]" :icon="data['@type']" width="45"></Icon> 
          </div>
          <div v-else class="iconcontainer" >
            <img :src="thumbnailUrl()" width="140">
          </div>
        </div>
        <div style="padding-left:15px">
          <span class="type">{{ unpackByIdx("@type") }}</span> 
          <div class="title" v-html="nl2br(unpackByIdx('name'))"></div>
          <div class="author" v-if="data['author'] != undefined" v-html="flatten(data['author'],'name')"></div>
          <div class="publisher" v-if="data['publisher'] != undefined" v-html="flatten(data['publisher'],'name')"></div>
          <div class="collection" v-if="data['isBasedOn']['isPartOf']['name'] != undefined">{{ data['isBasedOn']['isPartOf']['name'] }}</div>
        </div>
     </div>
     <div style="padding-top:40px;padding-bottom:200px">
      <table class="table is-striped" width="100%">
        <tbody>
        <tr>
          <td style="white-space: nowrap;"> {{ $ml.get('type_additionalType') }}</td>
          <td width="100%" style="font-weight: 400;">
           {{ unpackByIdx("@type") }}, {{ unpackByIdx('$.additionalType') }} 
          </td>
        </tr>
        <tr v-for="(field,idx) in fields" :key="idx">
          <td v-if="unpackByIdx(idx)" style="white-space: nowrap;"> {{ field }}</td>
          <td v-if="unpackByIdx(idx)" width="100%" v-html="nl2br(unpackByIdx(idx))" style="font-weight: 400;" v-linkified></td>
        </tr>
        </tbody>
      </table>
     </div>
  </div>
</template>
<script>

import Icon from '../helpers/Icon.vue'
import { JSONPath } from 'jsonpath-plus';
import { iconColors, iconBgColors } from '../../../constants.js';
export default {
  data() {
    return {
      fields: {
        "$.name": this.$ml.get('fullname'),
        "$.description": this.$ml.get('description'),
        "$.author": this.$ml.get('author'),
        "$.contributor": this.$ml.get('contributor'),
        "$.url": this.$ml.get('permalink'),
        "$.sameAs": this.$ml.get('originalsource'),
        "$.associatedMedia..embedUrl": this.$ml.get('viewonline'),
        "$.associatedMedia..contentUrl": this.$ml.get('openfile'),
        "$.isBasedOn.isPartOf.license": this.$ml.get('license'),
        "$.isBasedOn.isPartOf": this.$ml.get('dataset'),
        "$.dateCreated": this.$ml.get('dateCreated'),
        "$.locationCreated": this.$ml.get('locationCreated'),
        "$.datePublished":  this.$ml.get('publicationdate'),
        "$.publisher": this.$ml.get('publication'),
        "$.inLanguage":this.$ml.get('language'),
        "$.keywords": this.$ml.get('keywords'),
        "$.sdDatePublished": this.$ml.get('sdDatePublished'),
        "$.sdPublisher": this.$ml.get('sdPublisher'),
        "$.bookEdition": this.$ml.get('bookEdition'),
        "@id":  this.$ml.get('identifier')

      },
      iconColors:{},
      iconBgColors:{},
    };
  },
  props:['data'],
  components:{
    Icon
  },
  methods: {
    flatten(obj,subfield) {
        var l = []
        if (obj instanceof Array) {
            if (subfield == '' || subfield == null || subfield == undefined) {  
              l = obj.map(function (el) { return el['@value'] }.bind(subfield))
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
    isType(value) {
      if (Array.isArray(value)) {
          return 'array'
      } else if (typeof value === "string") {
          return 'string'
      } else if (value !== null && typeof value === "object") {
          return 'object'
      }
    },
    isDate(value){
      const regex = /(^([0-9]){4}-([0-9]){2}-([0-9]){2}T([0-9]){2}:([0-9]){2}:([0-9]){2}.([0-9])+Z)/g;
      return value.match(regex);      
    },
    formatDate(value) {
      const date = new Date(value);
      return date.toUTCString();
    },
    unpackByIdx(path) {
      if (path.charAt(0) == "@") {
        return this.unpack(this.data[path])
      }
      const values = JSONPath({path: path, json: this.data })
      return this.unpack(values)
    },
    unpack(value) {
      if (value == undefined) {
        return null
      }
      if (this.isType(value) == 'string') {
        if (this.isDate(value)) {
          return this.formatDate(value)
        }
        return value
      }
      if (this.isType(value) == 'object') {
        return this.unpack_object(value)
      }
      if (this.isType(value) == 'array') {
        return this.unpack_array(value).join("<br/>")
      }
    },
    unpack_array(value) {
      return value.map((v) => this.unpack(v))
    },
    unpack_object(value) {
      if (value["@value"] != undefined) {
        return value["@value"]
      }
      if (value["@type"] != undefined && value["name"] != undefined) {
        return this.unpack(value["name"])
      }
      return value   // just in case there is a case I didn't handle, at least it's clear that something is going on :-)
    },
    thumbnailUrl() {
      if (this.data.associatedMedia == undefined) {
        return undefined
      } else {
        if (Array.isArray(this.data.associatedMedia)) {
          return this.data.associatedMedia[0].thumbnailUrl
        } else {
          return this.data.associatedMedia.thumbnailUrl
        }
      }
    },
    nl2br(str) {
        return str.replace(/\r?\n/g, "<br />");
    }
  },
  mounted() {
    this.iconColors = iconColors
    this.iconBgColors = iconBgColors
  }
};
</script>
<style scoped>

.header {
    border-bottom: 1px solid #E6EBEF;
    text-align: right;
    padding:6px;
    padding-right:12px

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
.iconcontainer {
  width:140px;height:213px; display: flex;  justify-content: center; align-items: center;
}

.table td {
  border:0px;
  font-weight: 600;
  font-size: 16px;
  line-height: 20px;
  letter-spacing: 0%;

}
</style>