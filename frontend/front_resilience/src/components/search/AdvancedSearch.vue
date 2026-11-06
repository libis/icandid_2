<template>
  <div>
    
        <table>
          <tr v-for="(advancedQ,index) in query" :key="index">
            <td width="230" style="padding: 0px 8px 8px 0px;" >
              <select class="selectinput"  v-model="advancedQ.field" @change="advancedQ.query=''">
                  <option value="any">{{ $ml.get('any') }}</option>
                  <option value="title">{{ $ml.get('title') }}</option>
                  <option value="author">{{ $ml.get('author') }}</option>
                  <option value="subject">{{ $ml.get('subject') }}</option>
                  <option value="isbn">{{ $ml.get('isbn') }}</option>
                  <option value="issn">{{ $ml.get('issn') }}</option>
                  <option value="publicationdate">{{ $ml.get('publicationdate') }}</option>
                  <option value="locationcreated">{{ $ml.get('locationcreated') }}</option>                  
                  <option value="publisher_ft">{{ $ml.get('publisher') }}</option>
<!--                  <option value="provider">{{ $ml.get('provider') }}</option>
                  <option value="dataset">{{ $ml.get('dataset') }}</option>
                  <option value="type">{{ $ml.get('type') }}</option>  -->
                </select>
            </td>
            <td v-if="!['publicationdate','type','provider','dataset'].includes(advancedQ.field) "  style="padding: 0px 8px 8px 0px;">
                <select class="selectinput" v-model="advancedQ.condition">
                  <option value="contains">{{ $ml.get('containsthewords') }}</option>
                  <option value="phrase">{{ $ml.get('containsexactphrase') }}</option>
                  <option value="starts">{{ $ml.get('startswith') }}</option>
                </select>
            </td>
            <td v-if="!['publicationdate','type','provider','dataset'].includes(advancedQ.field) "   style="padding: 0px 8px 8px 0px;">
              <input
                class="textinputshort"
                type="text"
                v-model="advancedQ.query"
                 />              
            </td>
            <td colspan="2" v-if="advancedQ.field == 'publicationdate'"  style="padding: 0px 8px 8px 0px;width:688px">
              <input type="date" class="dateinput" v-model="advancedQ.query"  name="dp_pubdate" />
            </td>
            <td colspan="2" v-if="advancedQ.field == 'type'"  style="padding: 0px 8px 8px 0px;width:688px">
              <select class="selectinput" style="width:560px" v-model="advancedQ.query" >
                  <option v-for="item in ddlists.types" :value="item" :key="item">{{ splitWritten(item) }}</option>
              </select>
            </td>
            <td colspan="2" v-if="advancedQ.field == 'provider'"  style="padding: 0px 8px 8px 0px;width:688px">
              <select class="selectinput" style="width:560px" v-model="advancedQ.query" >
                  <option v-for="item in ddlists.providers" :value="item" :key="item">{{ item }}</option>
              </select>
            </td>
            <td colspan="2" v-if="advancedQ.field == 'dataset'"  style="padding: 0px 8px 8px 0px;width:688px">
              <select class="selectinput" style="width:560px" v-model="advancedQ.query" >
                  <option v-for="item in $parent.datasets" :value="item.internalident" :key="'ds_' + item.id">{{ item.name }}</option>
              </select>
            </td>

            <td style="padding: 0px 8px 8px 0px;">
               <button v-if="index==0 && query.length == 1" class="actionbutton" @click="addNewLine">+</button> 
               <button v-if="query.length > 1" class="actionbutton" @click="removeLine(index)">-</button>
            </td>

            <td v-if="index == 0" style="padding: 0px 0px 8px 0px;">
              <button class="searchbutton"  @click="$parent.clearsearch()" v-html="buttonAni"></button>
            </td>
          </tr>
          <tr>
            <td colspan="3" style="padding: 0px 8px 8px 0px;"></td>
            <td style="padding: 0px 8px 8px 0px;">
              <button v-if="query.length > 1" class="actionbutton" @click="addNewLine">+</button> 
            </td>
          </tr>

        </table>        
  </div>
</template>
<script>
import { mapGetters, mapActions } from 'vuex';
import axios from "axios";
axios.defaults.withCredentials = true;
export default {
  data() {
    return {
      query: [{ operator: "blank", field: "any", condition: "contains"}],
      dateformat: "yyyy-MM-dd",
      ddlists: [],
    };
  },
  methods: {
    ...mapActions(["clearResultset","clearAggregations","saveQuery","exportResultCsv","exportResultJsonLd"]),
    addNewLine: function() {
      this.query.push({
        operator: "AND",
        field: "any",
        condition: "contains",
        query: ""
      });
    },
    removeLine: function(idx) {
      this.query.splice(idx, 1);
      this.query[0].operator = "blank";
    },
    queryObj() {
      var name = "";
      for (var idx in this.query) {
        var line = this.query[idx];
        if (line.query != "") {
          if (line.operator == 'NOT') {
              name += ' AND NOT '
          }
          if (line.operator == 'AND' || line.operator == 'OR') {
              name += ' ' + line.operator + ' '
          }
          var lq = ""

          switch(line.field) {
            case "dataset":
              line.name = this.$parent.datasets.find(d => d.internalident == line.query).name
              lq = line.name
              break;
/*            case "language":
              line.name = this.$parent.languages.find(d => d.id == line.query).name
              lq = line.name
              break; */
            case "label":
              line.name = this.$parent.labels.find(d => d.id == line.query).name
              lq = line.name
              break;
            default:
              lq = line.query
              break;
          }
          if (line.condition == "contains") {
              name += line.field+' contains:'+lq
          }
          if (line.condition == "starts") {
              name += line.field+' starts with:'+lq
          }
          if (line.condition == "phrase") {
              name += line.field+' contains exactly:'+lq
          }
        }
      }
      var qObj = { q: this.query, searchtype: "advanced", name: name }
      return qObj;
    },
    clear() {
      this.query = [{ operator: "blank", field: "any", condition: "contains", q: "" }],
      this.clearResultset();
      this.clearAggregations()     
    },
    fillddlists() {
      var tmp_providers = []
      this.ddlists.publishers = []
      this.ddlists.providers = []
      this.$parent.datasets.forEach(element => {
        this.ddlists.publishers.push(element.name);
        tmp_providers.push(element.provider)
      });
      this.ddlists.providers = tmp_providers.filter((v, i, a) => a.indexOf(v) === i).sort(); // unique and sorted
    },
    splitWritten(t){
      var arr = t.split(/(?=[A-Z])/)
      for (var i=1;i<arr.length;i++) arr[i] = arr[i].toLowerCase()
      return arr.join(" ")
    }    
  },
  computed: {...mapGetters(['getHits','getApiDdlistsUrl','getUser','getDatasets','getPermissions','getSearchStatus']), 
        buttonAni() {
            if (this.getSearchStatus) {
                return '<div class="spinner"></div>'
            }
            return this.$ml.get('search')
        }
  },
  created() {
    this.unsubscribe ==
      this.$store.subscribe((mutation) => {
        if (mutation.type === "setQuery" && mutation.payload.searchtype === "advanced") {
          this.query = mutation.payload.q;
        }
      });
    this.fillddlists();
  },
  mounted() {
    axios
      .get(this.getApiDdlistsUrl)
      .then(function(response) { this.ddlists = response.data}.bind(this))
      .catch(error => {
        //console.log(error);
        if (error.response.status === 401) {
          axios
          .get(this.getApiDdlistsUrl + "/public")
          .then(function(response) { this.ddlists = response.data}.bind(this))
        }
      })
  },
  beforeDestroy() {
    if (this.unsubscribe != undefined) {
      this.unsubscribe();
    }
  },  
};
</script>
<style>

.vertalign {
  display: inline-block;
  vertical-align: middle;
  line-height: normal;  
  padding-top:9px;
  padding-right:3px
}

.textinputshort {
    width:680px;
    height:40px;
    border:1px solid Black;
    font-size: 20px;
    padding-left:10px

}
.dateinput {
    height:40px;
    width:190px;
    border-radius: 5px;
    font-size:20px;
    padding-left:10px;
    padding-right:10px;
    border:1px solid Black;
  
}
.selectinput {
    width:220px;
    height:40px;
    border:1px;
    border-color: #ebeff3;
    border-radius: 5px;
    font-size:20px;
    padding-left:10px;
    padding-right:10px;
    color:Black;
    background-color: #ebeff3;

}
.searchbutton {
    height:40px;
    border-radius: 5px;
    border:0px;
    background-color:#0625ac;
    font-size:16px;
    color:White;
    width:100px;
    cursor:pointer
}

button.actionbutton {
    height:40px;
    width:40px;
    border-radius: 5px;
    border:0px;
    background-color:#ebeff3;
    font-size:24px;
    color:#535F6B;
    cursor:pointer
}

button:hover.actionbutton {
  background-color:#C0C4C7;
}
.spinner {
    width: 16px;
    height: 16px;
    border: 2px solid rgba(255,255,255,0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
    margin-left:36px
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

</style>