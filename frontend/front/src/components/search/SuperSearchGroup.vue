<template>
    <div style="max-width:94%">
        <table>
            <tr>
                <td class="topcorner"></td><td><i class="fa fa-plus-circle" aria-hidden="true" @click="addRule" :title="$ml.get('addrule')"></i>&nbsp;<i class="fa fa-plus-square" aria-hidden="true" @click="addGroup" :title="$ml.get('addgroup')"></i>&nbsp;<i v-if="!first" class="fa fa-times" aria-hidden="true" @click="removeGroup()" :title="$ml.get('removegroup')"></i>
                <a href="https://icandid.helpdocsite.com/sasearch/advanced-search" target="_blank"><i class="fa fa-info is-pulled-right" style="margin-left:10px;font-size:16px"></i></a>
                </td>
            </tr>
            <tr>
                <td class="vertical" style="vertical-align: middle;"><span class="operator" v-if="group.rules != undefined && group.rules.length > 1" @click="toggleOperator">{{ $ml.get(group.operator) }}</span></td>
                <td>
                    <div v-for="(rule, idx) in group.rules" :key="idx">
                        <table v-if="rule.field != undefined">
                            <tr>
                                <td>
                                    <div class="select">
                                        <select v-model="rule.field" @change="$emit('change',group)">
                                            <option value="any">{{ $ml.get('any') }}</option>
                                            <option value="title">{{ $ml.get('title') }}</option>
                                            <option value="author">{{ $ml.get('author') }}</option>
                                            <option value="originalidentifier">{{ $ml.get('originalidentifier') }}</option>
                                            <option value="sender">{{ $ml.get('sender') }}</option>
                                            <option value="text">{{ $ml.get('text') }}</option>
                                            <option value="publicationdate">{{ $ml.get('publicationdate') }}</option>
                                            <option value="period">{{ $ml.get('period') }}</option>
                                            <!--<option v-if="ddlists.publishers != undefined && ddlists.publishers.length > 0" value="publisher">{{ $ml.get('publisher') }}</option> -->
                                            <option v-if="ddlists.providers != undefined && ddlists.providers.length > 0" value="provider">{{ $ml.get('provider') }}</option>
                                            <option value="dataset">{{ $ml.get('dataset') }}</option>
                                            <option v-if="ddlists.editions != undefined && ddlists.editions.length > 0" value="edition">{{ $ml.get('edition') }}</option>
                                            <option value="volume">{{ $ml.get('volume') }}</option>
                                            <option value="issue">{{ $ml.get('issue') }}</option>
                                            <option v-if="ddlists.genres != undefined && ddlists.genres.length > 0" value="genre">{{ $ml.get('genre') }}</option>
                                            <option value="retweet">{{ $ml.get('retweet') }}</option>
                                            <option v-if="ddlists.languages != undefined && ddlists.languages.length > 0" value="language">{{ $ml.get('language') }}</option>
                                            <option value="label">{{ $ml.get('label') }}</option>
                                            <option v-if="ddlists.legislationTypes != undefined && ddlists.legislationTypes.length > 0" value="legislationType">{{ $ml.get('legislationType') }}</option>
                                        </select>
                                    </div>                                
                                </td>
                                <td v-if="rule.field != undefined" nowrap >
                                    <div class="select" v-if="!['period', 'publicationdate','provider','publisher','dataset','edition','language','label','genre','retweet','legislationType','originalidentifier'].includes(rule.field) ">
                                        <select v-model="rule.operator" @change="$emit('change',group)">
                                        <option value="contains">{{ $ml.get('containsthewords') }}</option>
                                        <option value="containsnot">{{ $ml.get('containsthewordsnot') }}</option>
                                        <option value="phrase">{{ $ml.get('containsexactphrase') }}</option>
                                        <option value="phrasenot">{{ $ml.get('containsexactphrasenot') }}</option>
                                        <option value="starts">{{ $ml.get('startswith') }}</option>
                                        <option value="startsnot">{{ $ml.get('startswithnot') }}</option>
                                        </select>
                                    </div>
                                    <input type="date" class="input" style="width:170px" v-model="rule.value" v-if="rule.field == 'publicationdate'" name="dp_pubdate" />
                                    <div v-if="rule.field == 'period'" >
                                        <span class="vertalign">{{ $ml.get('from') }} : </span><input type="date" class="input" style="width:170px" v-model="rule.valuefrom" name="dp_fromdate" />&nbsp;
                                        <span class="vertalign">{{ $ml.get('toandincluding') }} : </span><input type="date" class="input" style="width:170px" v-model="rule.valueto" name="dp_todate" />
                                    </div>

                                    <div class="select" v-if="rule.field == 'provider'">
                                    <select style="width:315px" v-model="rule.value" >
                                        <option v-for="item in ddlists.providers" :value="item" :key="item">{{ item }}</option>
                                    </select>
                                    </div>

                                    <div class="select" v-if="rule.field == 'publisher'">
                                    <select style="width:315px" v-model="rule.value" >
                                        <option v-for="item in ddlists.publishers" :value="item" :key="item">{{ item }}</option>
                                    </select>
                                    </div>

                                    <div class="select" v-if="rule.field == 'dataset'">
                                    <select style="width:315px" v-model="rule.value" >
                                        <option v-for="item in ddlists.datasets" :value="item.internalident" :key="'ds_' + item.id">{{ item.name }}</option>
                                    </select>
                                    </div>
                                    <div class="select" v-if="rule.field == 'edition'" style="width: 315px; max-width: 100%;">
                                    <select style="width:315px" v-model="rule.value" >
                                        <option v-for="item in ddlists.editions" :value="item" :key="item">{{ item }}</option>
                                    </select>
                                    </div>

                                    <div class="select" v-if="rule.field == 'genre'">
                                    <select style="width:315px" v-model="rule.value" >
                                        <!-- <option v-for="item in $parent.languages" :value="item.id" :key="'lang_' + item.id">{{ item['name_'+$ml.current] }}</option> -->
                                        <option v-for="item in ddlists.genres" :value="item" :key="'genre_' + item">{{ item }}</option>
                                    </select>
                                    </div>


                                    <div class="select" v-if="rule.field == 'language'">
                                    <select style="width:315px" v-model="rule.value" >
                                        <!-- <option v-for="item in $parent.languages" :value="item.id" :key="'lang_' + item.id">{{ item['name_'+$ml.current] }}</option> -->
                                        <option v-for="item in ddlists.languages" :value="item" :key="'lang_' + item">{{ item }}</option>

                                    </select>
                                    </div>

                                    <div class="select" v-if="rule.field == 'label'">
                                    <select style="width:315px" v-model="rule.value" >
                                        <option v-for="item in ddlists.labels" :value="item.id" :key="'lbl_' + item.id">{{ item['name_'+$ml.current] }}</option>
                                    </select>
                                    </div>
                                    
                                    <div class="select" v-if="rule.field == 'legislationType'">
                                    <select style="width:315px" v-model="rule.value" >
                                        <option v-for="item in ddlists.legislationTypes" :value="item" :key="item">{{ item }}</option>
                                    </select>
                                    </div>





                                </td>
                                <td v-if="rule.field != undefined">
                                    <input style="width:400px" v-if="!['period', 'publicationdate','provider','publisher','dataset','edition','language','label','genre','retweet','legislationType'].includes(rule.field)" type="text" v-model="rule.value" @input="$emit('change',group)" />
                                </td>
                                <td v-if="rule.field != undefined" width="100%" style="vertical-align: middle;">
                                    <i class="fa fa-times" aria-hidden="true" @click="removeRule(idx)" :title="$ml.get('removerule')"></i>
                                </td>
                            </tr>
                        </table>
                        <SuperSearchGroup v-else :group="rule" @change="$emit('change',group)" @remove="removeRule(idx)"></SuperSearchGroup>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="bottomcorner"></td><td>&nbsp;</td>
            </tr>
        </table>
    </div>
</template>
<script>
import SuperSearchGroup from './SuperSearchGroup.vue';

export default {
  name: "SuperSearchGroup",
  props: ['group','first'],
  data() {
    return {
        ddlists: []
    };
  },
  components: {
    SuperSearchGroup
  },  
  methods: {
    toggleOperator() {
      this.group.operator = this.group.operator === "and" ? "or" : "and";
      this.$emit("change",this.group);
    },
    addRule() {
      if (this.group.rules == undefined) {
        this.$set(this.group, "rules", []);
      }
      this.group.rules.push({field: "any", operator: "contains", value: ""});
      this.$emit("change",this.group);
    },
    insertRule(newidx) {
      if (this.group.rules == undefined) {
        this.$set(this.group, "rules", []);
      }
      this.group.rules.splice(newidx, 0, {field: "any", operator: "contains", value: ""});
      this.$emit("change",this.group);
    },
    removeRule(idx) {
      this.group.rules.splice(idx, 1);
      this.$emit("change",this.group);
    },
    addGroup() {
      if (this.group.rules == undefined) {
        this.$set(this.group, "rules", []);
      }
      this.group.rules.push({operator: "and", rules: [{field: "any", operator: "contains", value: ""}]});
      this.$emit("change",this.group);
    },
    removeGroup() {
      this.$emit("remove");
    }
  },
  created() {
    this.ddlists = this.$parent.ddlists;
  },
  watch: {
    '$parent.ddlists': function(newval) {
      this.ddlists = newval;
    },
    'group.rules': {
      handler() {
        if (this.group.rules.length == 0) {
            if (this.first) {
                this.$parent.clear()
            } else{
                this.removeGroup();
            }
        }
     }
    }
  }
}
</script>
<style>
.vertical
{
    background: url('/img/line.png') repeat-y;
    width: 40px;
    vertical-align: middle;
}
.topcorner
{
    background: url('/img/topcorner.png') no-repeat;
    width: 16px;
    
}
.bottomcorner 
{
    background: url('/img/bottomcorner.png') no-repeat;
    width: 16px;
    height: 16px;
}
.operator {
    display: inline-block;
    background-color: white;
    cursor: pointer;
    color: #00407a;
}
i {
    color: #00407a;
    cursor: pointer;
}
.content table td
{
    padding: 0.3rem;
    border:0px
}
</style>