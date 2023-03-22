<template>
  <div>
    <button class="button is-rounded is-pulled-right" style="margin-top:10px" :title="$ml.get('savequery')"  @click="$parent.saveQuery()" v-if="getHits>0"><i class="fa fa-floppy-o" aria-hidden="true"></i></button>
    <div class="columns">
      <div class="column">
        <table class="table is-narrow" xstyle="table-layout: auto;">
          <tr v-for="(advancedQ,index) in query" :key="index">
            <td>
              <div class="select" v-if="advancedQ.operator!='blank'">
                <select v-model="advancedQ.operator">
                  <option value="AND">{{ $ml.get('and').toUpperCase() }}</option>
                  <option value="OR">{{ $ml.get('or').toUpperCase() }}</option>
                  <option value="NOT">{{ $ml.get('not').toUpperCase() }}</option>
                </select>
              </div>
            </td>
            <td>
              <div class="select">
                <select v-model="advancedQ.field" @change="advancedQ.query=''">
                  <option value="any">{{ $ml.get('any') }}</option>
                  <option value="title">{{ $ml.get('title') }}</option>
                  <option value="author">{{ $ml.get('author') }}</option>
                  <option value="sender">{{ $ml.get('sender') }}</option>
                  <option value="text">{{ $ml.get('text') }}</option>
                  <option value="publicationdate">{{ $ml.get('publicationdate') }}</option>
                  <option value="period">{{ $ml.get('period') }}</option>
                  <!--<option value="publisher">{{ $ml.get('publisher') }}</option> -->
                  <option value="provider">{{ $ml.get('provider') }}</option>
                  <option value="dataset">{{ $ml.get('dataset') }}</option>
                  <option value="edition">{{ $ml.get('edition') }}</option>
                  <option value="retweet">{{ $ml.get('retweet') }}</option>
                  <option value="language">{{ $ml.get('language') }}</option>
                  <option value="label">{{ $ml.get('label') }}</option>
                  <option value="legislationType">{{ $ml.get('legislationType') }}</option>
                  
                </select>
              </div>
            </td>
            <td>
              <div class="select" v-if="!['period', 'publicationdate','provider','publisher','dataset','edition','language','label','retweet','legislationType'].includes(advancedQ.field) ">
                <select v-model="advancedQ.condition">
                  <option value="contains">{{ $ml.get('containsthewords') }}</option>
                  <option value="phrase">{{ $ml.get('containsexactphrase') }}</option>
                  <option value="starts">{{ $ml.get('startswith') }}</option>
                </select>
              </div>
              &nbsp;
              <input
                class="input"
                type="text"
                v-model="advancedQ.query"
                v-on:keyup.enter="search()"
                style="width:57%"
                v-if="!['period', 'publicationdate','provider','publisher','dataset','edition','language','label','retweet','legislationType'].includes(advancedQ.field)" />
              <!-- <datepicker class="input" style="width:130px" :format="dateformat" input-class="noborder" :language="nl" v-model="advancedQ.query" name="dp_pubdate" v-if="advancedQ.field == 'publicationdate'"></datepicker>                -->
              <input type="date" class="input" style="width:150px" v-model="advancedQ.query" v-if="advancedQ.field == 'publicationdate'" name="dp_pubdate" />
<!--              <div v-if="advancedQ.field == 'period'" >
                <span class="vertalign">{{ $ml.get('from') }} : </span><datepicker class="input" style="width:130px" :format="dateformat" input-class="noborder" :language="nl" v-model="advancedQ.queryfrom" name="dp_fromdate" ></datepicker>&nbsp;
                <span class="vertalign">{{ $ml.get('toandincluding') }} : </span><datepicker class="input" style="width:130px" :format="dateformat" input-class="noborder" :language="nl" v-model="advancedQ.queryto" name="dp_todate" ></datepicker>
              </div> -->
              <div v-if="advancedQ.field == 'period'" >
                <span class="vertalign">{{ $ml.get('from') }} : </span><input type="date" class="input" style="width:150px" v-model="advancedQ.queryfrom" name="dp_fromdate" />&nbsp;
                <span class="vertalign">{{ $ml.get('toandincluding') }} : </span><input type="date" class="input" style="width:150px" v-model="advancedQ.queryto" name="dp_todate" />
              </div>

              <div class="select" v-if="advancedQ.field == 'provider'">
              <select style="width:315px" v-model="advancedQ.query" >
                  <option v-for="item in ddlists.providers" :value="item" :key="item">{{ item }}</option>
              </select>
              </div>

              <div class="select" v-if="advancedQ.field == 'publisher'">
              <select style="width:315px" v-model="advancedQ.query" >
                  <option v-for="item in ddlists.publishers" :value="item" :key="item">{{ item }}</option>
              </select>
              </div>

              <div class="select" v-if="advancedQ.field == 'dataset'">
              <select style="width:315px" v-model="advancedQ.query" >
                  <option v-for="item in $parent.datasets" :value="item.internalident" :key="'ds_' + item.id">{{ item.name }}</option>
              </select>
              </div>
              <div class="select" v-if="advancedQ.field == 'edition'" style="width: 315px; max-width: 100%;">
              <select style="width:315px" v-model="advancedQ.query" >
                  <option v-for="item in ddlists.editions" :value="item" :key="item">{{ item }}</option>
              </select>
              </div>
              
<!-- 
              <div class="select" v-if="advancedQ.field == 'retweet'">
              <select style="width:315px" v-model="advancedQ.query" >
                  <option value="only">{{ $ml.get('onlyretweet') }}</option>
                  <option value="no">{{ $ml.get('noretweet') }}</option>
              </select>
              </div>
 -->
              <div class="select" v-if="advancedQ.field == 'language'">
              <select style="width:315px" v-model="advancedQ.query" >
                  <!-- <option v-for="item in $parent.languages" :value="item.id" :key="'lang_' + item.id">{{ item['name_'+$ml.current] }}</option> -->
                  <option v-for="item in ddlists.languages" :value="item" :key="'lang_' + item">{{ item }}</option>

              </select>
              </div>

              <div class="select" v-if="advancedQ.field == 'label'">
              <select style="width:315px" v-model="advancedQ.query" >
                  <option v-for="item in $parent.labels" :value="item.id" :key="'lbl_' + item.id">{{ item['name_'+$ml.current] }}</option>
              </select>
              </div>
              
              <div class="select" v-if="advancedQ.field == 'legislationType'">
              <select style="width:315px" v-model="advancedQ.query" >
                  <option v-for="item in ddlists.legislationTypes" :value="item" :key="item">{{ item }}</option>
              </select>
              </div>

            </td>
            <td v-if="query.length > 1">
              <button class="button" @click="removeLine(index)">-</button>
            </td>
          </tr>
          <tr>
            <td colspan="4"></td>
            <td>
              <button class="button" @click="addNewLine">+</button>
            </td>
          </tr>
        </table>
      </div>
      
    </div>
  </div>
</template>
<script>
import { mapGetters, mapActions } from 'vuex';
import axios from "axios";
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
              name += line.field+':'+lq
          }
          if (line.condition == "starts") {
              name += line.field+':'+lq+'*'
          }
          if (line.condition == "phrase") {
              name += line.field+':"'+lq+'"'
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
    }    
  },
  computed: {...mapGetters(['getHits','getApiDdlistsUrl','getUser','getDatasets']), 
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
    
    axios.get(this.getApiDdlistsUrl).then(function(response) { this.ddlists = response.data}.bind(this) )     
  },
  beforeDestroy() {
    this.unsubscribe();
  },  
};
</script>
<style>
input.noborder { border: 0px; width:116px }
input.noborder:focus { outline:none; }
.vertalign {
  display: inline-block;
  vertical-align: middle;
  line-height: normal;  
  padding-top:9px;
  padding-right:3px
}
option {
  /* wrap text in compatible browsers */
  -moz-white-space: pre-wrap;
  -o-white-space: pre-wrap;
  white-space: pre-wrap;
  /* hide text that can't wrap with an ellipsis */
  overflow: hidden;
  text-overflow: ellipsis;
  /* add border after every option */
  border-bottom: 1px solid #DDD;
}
</style>