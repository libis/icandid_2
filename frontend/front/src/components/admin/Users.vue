<template>
    <div>
        <h1 class="title">{{ $ml.get('admin')}} - {{ $ml.get('users') }}</h1>
        <div v-if="activeuseridx<0">
            <div class="box">
                <input type="text" style="width:100%" class="input" v-model="searchterm" :placeholder="$ml.get('search')+'...'" @keyup="update()">
                <div class="columns">
                    <div class="column is-10">
                        <div class="tabs is-toggle is-small" style="margin:0px;margin-top:10px">
                        <ul>
                            <li v-bind:class="{'is-active': (selectedType=='all')}">
                            <a @click.prevent.stop="selectedType='all';update()">{{ $ml.get('allusers') }}</a>
                            </li>
                            <li v-bind:class="{'is-active': (selectedType=='active')}">
                            <a @click.prevent.stop="selectedType='active';update()">{{ $ml.get('activeusers') }}</a>
                            </li>
                            <li v-bind:class="{'is-active': (selectedType=='inactive')}">
                            <a @click.prevent.stop="selectedType='inactive';update()">{{ $ml.get('inactiveusers') }}</a>
                            </li>
                        </ul>
                        </div>
                    </div>
                    <div class="column is-2" style="text-align:right">
                        <button class="button is-rounded" style="margin-top:5px" :title="$ml.get('exportusers')" @click="exportUsers()"><i class="fa fa-file-o" aria-hidden="true" style="margin-right:3px"></i>.xlsx</button>
                    </div>
                </div>
            </div>
            <table class="table is-hoverable is-fullwidth" v-if="userlist.length > 0">
                <tr>
                    <th width="5"></th>
                    <th>{{ $ml.get("fullname") }}</th>
                    <th>{{ $ml.get("email") }}</th>
                    <th>{{ $ml.get("institution") }}</th>
                    <th>{{ $ml.get("researchgroup") }}</th>
                    <th>{{ $ml.get("groups") }}</th>
                    <th></th>
                </tr>
                <tr v-for="(v,k) in userlist" :key="k">
                    <td><i class="fa fa-user" :class="is_active(v.active)"></i></td>
                    <td>{{ v.firstname }} {{ v.lastname }} </td>
                    <td>{{ v.email }}</td>
                    <td>{{ v.institution }} </td>
                    <td>{{ v.researchgroup }} </td>
                    <td>{{ v.roles.map(function(val,idx) { return val.name }).sort().join(', ') }} </td>
                    <td><button class="button" @click="edit(k)">{{ $ml.get('edit')}}</button></td>
                </tr>
            </table>
            <button class="button" style="float:right" @click="edit(userlist.length)">{{ $ml.get('newuser') }}</button>
            <br/><br/>
        </div>
        <div class="box" v-if="activeuseridx >= 0">
            <div class="columns">
                <div class="column is-half">
                    <div class="field">
                        <label class="label">{{ $ml.get("name") }}</label>
                        <div class="control">
                            <input class="input" type="text" v-model="activeuser.firstname" maxlength="100">
                        </div>
                        <p class="help is-danger is-hidden" ref="firstnamewarn">{{ $ml.get('fieldrequired') }}</p>
                    </div>
                    <div class="field">
                        <label class="label">{{ $ml.get("lastname") }}</label>
                        <div class="control">
                            <input class="input" type="text" v-model="activeuser.lastname" maxlength="100">
                        </div>
                        <p class="help is-danger is-hidden" ref="lastnamewarn">{{ $ml.get('fieldrequired') }}</p>
                    </div>
                    <div class="field">
                        <label class="label">{{ $ml.get("eppn") }}</label>
                        <div class="control">
                            <input class="input" type="text" v-model="activeuser.eppn" @keyup="checkExists()" maxlength="255">
                        </div>
                        <p class="help is-danger is-hidden" ref="eppnwarn">{{ $ml.get('fieldrequired') }}</p>
                    </div> 
                    <div class="field">
                        <label class="label">{{ $ml.get("email") }}</label>
                        <div class="control">
                            <input class="input" type="email" v-model="activeuser.email" maxlength="150">
                        </div>
                        <p class="help is-danger is-hidden" ref="emailwarn">{{ $ml.get('fieldrequired') }}</p>
                    </div>            
                    <div class="field">
                        <label class="label">{{ $ml.get("institution") }}</label>
                        <div class="control">
                            <input class="input" type="text" v-model="activeuser.institution" list="institutions" maxlength="45">
                            <datalist id="institutions">
                                <option v-for="(v,i) in options.institutions" :value="v.institution" :key="i" />
                            </datalist>                    
                        </div>
                        <p class="help is-danger is-hidden" ref="institutionwarn">{{ $ml.get('fieldrequired') }}</p>
                    </div>    
                    <div class="field">
                        <label class="label">{{ $ml.get("researchgroup") }}</label>
                        <div class="control">
                            <input class="input" type="text" v-model="activeuser.researchgroup" maxlength="150">
                        </div>
                        <p class="help is-danger is-hidden" ref="researchgroupwarn">{{ $ml.get('fieldrequired') }}</p>
                    </div>
                    <div class="field">
                        <label class="label">{{ $ml.get("validityperiod") }}</label>
                        <div class="control">
                        <span class="vertalign">{{ $ml.get('from') }} : </span><input type="date" class="input" style="width:170px" v-model="activeuser.startdate" name="dp_fromdate" />
                        <span class="vertalign" style="margin-left:5px">{{ $ml.get('toandincluding') }} : </span><input type="date" class="input" style="width:170px" v-model="activeuser.enddate" name="dp_untildate" />
                        </div>
                        <p class="help is-danger is-hidden" ref="validityperiodwarn">{{ $ml.get('fieldrequired') }}</p>
                    </div>
                    <div class="field">
                        <label class="label">{{ $ml.get("description") }}</label>
                        <div class="control">
                            <textarea class="textarea" v-model="activeuser.description" maxlength="450"></textarea>
                        </div>
                    </div>                    
                    <div class="field">
                        <label class="label" for="checkbox">{{ $ml.get("active") }} <input type="checkbox" id="checkbox" v-model="activeuser.active"></label>
                    </div>
                    <div class="field">
                        <label class="label" for="checkbox">{{ $ml.get("newsletter") }} <input type="checkbox" id="checkbox" v-model="activeuser.newsletter"></label>
                    </div>

                    <div class="field">
                        <label class="label">{{ $ml.get("apikey") }}</label>
                        <div class="control">
                            <input class="input" style="width:350px" readonly type="text" v-model="activeuser.apikey" :placeholder="$ml.get('newapikeyph')">
                        </div><button class="button" @click="activeuser.apikey = ''">{{ $ml.get('newapikey')}}</button>
                    </div>
                </div>
                <div class="column is-half">
                    <label class="label">{{ $ml.get("accessrights") }}</label>
                    <div class="field"><CheckboxList label="groups" :options="options.roles" :selected="activeuser.roles" @select="getRoles"></CheckboxList></div>
                    <div class="field"><CheckboxList label="collections_" :options="options.datasets" :selected="activeuser.datasets" @select="getDatasets" :key="triggerD"></CheckboxList></div>
                    <div class="field"><CheckboxList label="functions" :options="options.resources" :selected="activeuser.resources" @select="getResources" :key="triggerR"></CheckboxList></div>
                    <label class="label">{{ $ml.get("twitter") }}</label>
                    <div class="field">
                        <label class="lowlabel">{{ $ml.get('apikey') }}</label>
                        <div class="control">
                                <input class="input" type="text" v-model="activeuser.twitter_api_key" maxlength="25">
                        </div>
                    </div>
                    <div class="field">
                        <label class="lowlabel">{{ $ml.get('apikeysecret') }}</label>
                        <div class="control">
                                <input class="input" type="text" v-model="activeuser.twitter_api_key_secret" maxlength="50">
                        </div>
                    </div>
                    <div class="field">
                        <label class="lowlabel">{{ $ml.get('bearertoken') }}</label>
                        <div class="control">
                            <input class="input" type="text" v-model="activeuser.twitter_bearer_token" maxlength="127">
                        </div>
                    </div>
                </div>
            </div>

            <div class="columns">
                <div class="column is-half">
                    
                </div>
                <div class="column is-one-quarter">
                    <a class="button is-primary" style="color:White" @click="save()">{{ $ml.get('save') }}</a>&nbsp;
                    <a class="button" @click="edit(-1)">{{ $ml.get('cancel') }}</a>
                </div>
                <div class="column is-one-quarter">
                    <a class="button is-danger" v-if="activeuser.id > 0" @click="del()">{{ $ml.get('delete') }}</a>
                </div>
            </div>            

        </div>
        <br/>
        <Dialog ref="dialog"></Dialog>
    </div>    
</template>
<script>
import CheckboxList from '../helpers/CheckboxList.vue'
import Dialog from '../helpers/Dialog.vue'
import axios from "axios";
import { mapGetters } from "vuex";
axios.defaults.withCredentials = true;
export default {
    components: {
        CheckboxList, Dialog
    },
    data() {
        return {
            searchterm:"",
            selectedType:"active",
            activeuseridx:-1,
            userlist:[],
            activeuser:[],
            selectedidx:-1,
            selectedroleidx:-1,
            options:[],
            triggerD:0,
            triggerR:0
        }
    },
    methods: {
        update() {
            if (this.searchterm.length >= 3) {
                axios
                    .post(this.getApiAdminUrl + '/users/' + this.selectedType, this.searchterm)
                    .then(res => {
                        this.userlist = res.data;
                    })
                    .catch(error => console.log(error));
            } else {
                this.userlist = []
            }
        },
        edit(idx) {
            if (idx == -1) this.update();
            this.activeuseridx = idx
            if(this.userlist[this.activeuseridx] != null) {
                this.activeuser = JSON.parse(JSON.stringify(this.userlist[this.activeuseridx]));
                this.updatevia()
            } else {
                this.activeuser = Object()
                this.activeuser.id = 0;
                this.activeuser.roles = [];
                this.activeuser.resources = [];
                this.activeuser.datasets = [];
                this.updatevia()
            }
            axios
                .get(this.getApiAdminUrl + '/options')
                .then(res => {
                    this.options = res.data;
                    this.updatevia()
                })
                .catch(error => console.log(error));            
        },
        save() {
            var error = 0;
            if (this.activeuser.firstname == undefined || this.activeuser.firstname.trim() == "") {
                this.$refs.firstnamewarn.classList.remove('is-hidden')
                error++
            } else {
                this.$refs.firstnamewarn.classList.add('is-hidden')
            }
            if (this.activeuser.lastname == undefined || this.activeuser.lastname.trim() == "") {
                this.$refs.lastnamewarn.classList.remove('is-hidden')
                error++
            } else {
                this.$refs.lastnamewarn.classList.add('is-hidden')
            }
            if (this.activeuser.eppn == undefined || this.activeuser.eppn.trim() == "") {
                this.$refs.eppnwarn.classList.remove('is-hidden')
                error++
            } else {
                this.$refs.eppnwarn.classList.add('is-hidden')
            }
            if (this.activeuser.email == undefined || this.activeuser.email.trim() == "") {
                this.$refs.emailwarn.classList.remove('is-hidden')
                error++
            } else {
                this.$refs.emailwarn.classList.add('is-hidden')
            }
            if (this.activeuser.institution == undefined || this.activeuser.institution.trim() == "") {
                this.$refs.institutionwarn.classList.remove('is-hidden')
                error++
            } else {
                this.$refs.institutionwarn.classList.add('is-hidden')
            }
            if (this.activeuser.researchgroup == undefined || this.activeuser.researchgroup.trim() == "") {
                this.$refs.researchgroupwarn.classList.remove('is-hidden')
                error++
            } else {
                this.$refs.researchgroupwarn.classList.add('is-hidden')
            }
            if (this.activeuser.startdate == undefined || this.activeuser.startdate == "" || this.activeuser.enddate == undefined || this.activeuser.enddate == "") {
                this.$refs.validityperiodwarn.classList.remove('is-hidden')
                error++
            } else {
                this.$refs.validityperiodwarn.classList.add('is-hidden')
            }



            if (error > 0) return false;
            axios
                .post(this.getApiAdminUrl + '/user', this.activeuser)
                .then(res => {
                    this.activeuser = res.data;
                    this.update();
                    this.edit(-1);
                    this.$root.$children[0].getUserInfo()
                })
                .catch(error => console.log(error));
        },
        checkExists() {
            if (this.activeuseridx == 0) {
                if (this.activeuser.eppn.length >= 7) {
                    axios
                        .post(this.getApiAdminUrl + '/users/all', this.activeuser.eppn)
                        .then(res => {
                            if (res.data.length > 0) {
                                this.$refs.dialog.alert(this.$ml.get('newuser'), this.$ml.get('userexists'));
                            }
                        })
                        //.catch(error => console.log(error));
                }
            }
        },
        del() {
            if (confirm(this.$ml.get('reallydeluser'))) {
                axios
                    .delete(this.getApiAdminUrl + '/user/' + this.activeuser.id)
                    .then(res => {
                        this.activeuser = res.data;
                        this.update();
                        this.edit(-1);
                    })
                    .catch(error => console.log(error));
            }
        },
        getRoles(value) {
            this.activeuser.roles = value
            this.updatevia()
        },
        getDatasets(value) {
            this.activeuser.datasets = value
        },
        getResources(value) {
            this.activeuser.resources = value
        },
        is_active(type){
            return ["has-text-danger","has-text-success"][type];
        },
        updatevia() {
            if (this.options.resources != undefined) {
                for (let i = 0; i<this.options.resources.length; i++) {
                    this.options.resources[i].via = []
                }
                for (let i = 0; i<this.options.resources.length; i++) {
                    for (let j=0; j<this.activeuser.roles.length; j++) {
                        if(this.activeuser.roles[j].resources != undefined) {
                            if (this.activeuser.roles[j].resources.filter(x => x.id == this.options.resources[i].id).length > 0){
                                this.options.resources[i].via.push(this.activeuser.roles[j].name)
                            }
                        }
                    }
                }
                this.triggerR += 1
            }

            if (this.options.datasets != undefined) {
                for (let i = 0; i<this.options.datasets.length; i++) {
                    this.options.datasets[i].via = []
                }
                for (let i = 0; i<this.options.datasets.length; i++) {
                    for (let j=0; j<this.activeuser.roles.length; j++) {
                        if (this.activeuser.roles[j].datasets != undefined) {
                            if (this.activeuser.roles[j].datasets.filter(x => x.id == this.options.datasets[i].id).length > 0){
                                this.options.datasets[i].via.push(this.activeuser.roles[j].name)
                            }
                        }
                    }
                }
                this.triggerD += 1
            }
        },
        exportUsers() {
          var url = this.getApiAdminUrl + '/user/export'
          axios
            .get(url, {responseType: 'blob'})
            .then(response => {
              var fileURL = window.URL.createObjectURL(new Blob([response.data]));
              var fileLink = document.createElement('a');
         
              fileLink.href = fileURL;
              fileLink.setAttribute('download', 'users.xlsx');
              document.body.appendChild(fileLink);
         
              fileLink.click();
          })
            .catch(error => {
              console.log(error);
          });
      }              
    },
    computed: mapGetters(['getApiAdminUrl']),
    
}
</script>
<style scoped>
input[type=text] {
    width:400px
}
input[type=email] {
    width:400px
}
input[type=radio] {
    margin-left:10px
}
textarea {
    width:400px;
}
.field {
    margin-bottom:20px
}
.hidden {
    display:none;
}
.lowlabel {
    color:Black;
    display:block
}
</style>