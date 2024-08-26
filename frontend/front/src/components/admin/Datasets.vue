<template>
    <div>
        <h1 class="title">{{ $ml.get('admin')}} - {{ $ml.get('collections') }}</h1>
        <div v-if="activeidx<0">
            <div class="box">
                <input type="text" style="width:100%" class="input" v-model="searchterm" :placeholder="$ml.get('search')+'...'" @keyup="update()">
            </div>
            <table class="table is-hoverable is-fullwidth" v-if="setlist.length > 0">
                <tr>
                    <th>{{ $ml.get("fullname") }}</th>
                    <th>{{ $ml.get("provider") }}</th>
                    <th>{{ $ml.get("labels") }}</th>
                    <th></th>
                    <th></th>
                </tr>
                <tr v-for="(v,k) in setlist" :key="k">
                    <td>{{ v.name }} </td>
                    <td>{{ v.provider }} </td>
                    <td>{{ v['labels_'+$ml.current]}} </td>
                    <td><button class="button" @click="edit(k,false)">{{ $ml.get('edit')}}</button></td>
                    <td><button class="button" @click="edit(k,true)">{{ $ml.get('users')}}</button></td>
                </tr>
            </table>
            <button class="button" style="float:right" @click="edit(setlist.length)">{{ $ml.get('newdataset') }}</button>
            <br/><br/>
        </div>
        <div class="box" v-if="activeidx >= 0 && !showUsers">
            <div class="columns">
                <div class="column is-half">
                    <div class="field">
                        <label class="label">{{ $ml.get("fullname") }}</label>
                        <div class="control">
                            <input class="input" type="text" v-model="activeset.name" maxlength="100">
                        </div>
                        <p class="help is-danger is-hidden" ref="namewarn">{{ $ml.get('fieldrequired') }}</p>
                    </div>
                    <div class="field">
                        <label class="label">{{ $ml.get("internalident") }}</label>
                        <div class="control">
                            <input class="input" type="text" v-model="activeset.internalident" maxlength="45">
                        </div>
                        <p class="help is-danger is-hidden" ref="internalidentwarn">{{ $ml.get('fieldrequired') }}</p>
                    </div>
                    <div class="field">
                        <label class="label">{{ $ml.get("description") }}</label>
                        <div class="control">
                            <textarea class="textarea" v-model="activeset.description" maxlength="255"></textarea>
                        </div>
                    </div>

                    <div class="columns">
                        <div class="column is-4">
                            <div class="field"><CheckboxList label="talen" :options="options.languages" :selected="activeset.languages" @select="getLanguages"></CheckboxList></div>
                        </div>
                        <div class="column is-4">
                            <div class="field"><CheckboxList label="labels" :options="options.labels" :selected="activeset.labels" @select="getLabels"></CheckboxList></div>
                        </div>
                    </div>

                </div>
                <div class="column is-half">
                    <div class="field">
                        <label class="label">{{ $ml.get("requestor") }}</label>
                        <div class="control">
                            <input class="input" type="text" v-model="activeset.requestor" maxlength="100">
                        </div>
                    </div>
                    <div class="field">
                        <label class="label">{{ $ml.get("requestoremail") }}</label>
                        <div class="control">
                            <input class="input" type="email" v-model="activeset.requestoremail" maxlength="200">
                        </div>
                    </div>
                    <div class="field">
                        <label class="label">{{ $ml.get("query") }}</label>
                        <div class="control">
                            <textarea class="textarea" v-model="activeset.query" maxlength="1000"></textarea>
                        </div>
                    </div>
                    <div class="field">
                        <label class="label">{{ $ml.get("provider") }}</label>
                        <div class="control">
                            <input class="input" type="text" v-model="activeset.provider" list="providers" maxlength="45">
                            <datalist id="providers">
                                <option v-for="(v,i) in options.providers" :value="v.provider" :key="i" />
                            </datalist>
                        </div>
                    </div>
                    <div class="field">
                        <label class="label">{{ $ml.get("license") }}</label>
                        <div class="control">
                            <input class="input" type="text" v-model="activeset.license" maxlength="255">
                        </div>
                        <p class="help is-danger is-hidden" ref="licensewarn">{{ $ml.get('fieldrequired') }}</p>
                    </div>

                    <div class="field">
                        <label class="label">{{ $ml.get('period') }}</label>
                        <div class="control" style="flex-direction:inherit">
                            <span class="vertalign">{{ $ml.get('from') }} : </span><input type="date" class="input" style="width:170px" v-model="activeset.from" name="dp_fromdate" />
                            <span class="vertalign" style="margin-left:5px">{{ $ml.get('toandincluding') }} : </span><input type="date" class="input" style="width:170px" v-model="activeset.until" name="dp_untildate" />
                        </div>
                    </div>

                    <div class="field">
                        <div class="columns">
                        <div class="column is-4"><input type="checkbox" id="checkbox1" v-model="activeset.available"><label class="label" style="display:inline" for="checkbox1">&nbsp;{{ $ml.get("available") }}</label></div>
                        <div class="column is-8"><input type="checkbox" id="checkbox2" v-model="activeset.hidden"><label class="label" style="display:inline" for="checkbox2">&nbsp;{{ $ml.get("hidden") }} </label> <br/>{{ $ml.get("sensitivecollection") }}</div>
                        </div>
                    </div>
                    <!-- <div class="field">
                        <label class="label" for="checkbox2">{{ $ml.get("media") }} <input type="checkbox" id="checkbox2" v-model="activeset.ismedia"></label>
                    </div> -->

                </div>
            </div>

            <div class="columns">
                <div class="column is-half">
                    
                </div>
                <div class="column is-one-quarter">
                    <a class="button is-primary" style="color:White" @click="save()">{{ $ml.get('save') }}</a>&nbsp;
                    <a class="button" @click="edit(-1,false)">{{ $ml.get('cancel') }}</a>
                </div>
                <div class="column is-one-quarter">
                    
                </div>
            </div>            
            
        </div>
        <div class="box" v-if="activeidx >=0 && showUsers">
            
            <span>
                <button class="delete" style="float:right" @click="edit(-1,false)"></button>
                <h4 class="title is-4">{{ $ml.get('users') }} : {{ activeset.name }}</h4>
                
            </span>

            <table class="table">
                <thead>
                    <th>{{ $ml.get("fullname") }}</th>
                    <th>{{ $ml.get("group") }}</th>
                    <th>{{ $ml.get("institution") }} - {{ $ml.get("researchgroup") }}</th>
                </thead>
                <tbody>
                    <tr v-for="(user) in activeset.access" :key="user.id">
                        <td>
                            {{user.firstname}} {{user.lastname}}
                        </td>
                        <td>
                            {{user.via}}
                        </td>
                        <td>
                            {{user.institution}} - {{user.researchgroup}}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <br/>
    </div>    
</template>
<script>
import axios from "axios";
import { mapGetters } from "vuex";
axios.defaults.withCredentials = true;
import CheckboxList from '../helpers/CheckboxList.vue'
export default {
    data() {
        return {
            searchterm:"",
            activeidx:-1,
            setlist:[],
            activeset:{
                languages:[],
                labels:[]
            },
            options:[],
            selectedLangidx:-1,
            selectedLabelidx:-1,
            showUsers:false
        }
    },
    components:{
        CheckboxList
    },
    methods: {
        update() {
            if (this.searchterm.length >= 3) {
                axios
                    .post(this.getApiAdminUrl + '/datasets', this.searchterm)
                    .then(res => {
                        this.setlist = res.data;
                    })
                    .catch(error => console.log(error));
            } else {
                this.setlist = []
            }
        },
        edit(idx,su) {
            
            if (idx == -1) this.update();
            this.activeidx = idx
            if(this.setlist[this.activeidx] != null) {
                this.activeset = JSON.parse(JSON.stringify(this.setlist[this.activeidx]));
            } else {
                this.activeset = Object()
                this.activeset.id = 0;
                this.activeset.languages = [];
                this.activeset.labels = [];
            }
            this.showUsers = su
            axios
                .get(this.getApiAdminUrl + '/options')
                .then(res => {
                    this.options = res.data;
                })
                .catch(error => console.log(error));
        },
        save() {
            var error = 0;
            if (this.activeset.name == undefined || this.activeset.name.trim() == "") {
                this.$refs.namewarn.classList.remove('is-hidden')
                error++
            } else {
                this.$refs.namewarn.classList.add('is-hidden')
            }
            if (this.activeset.internalident == undefined || this.activeset.internalident.trim() == "") {
                this.$refs.internalidentwarn.classList.remove('is-hidden')
                error++
            } else {
                this.$refs.internalidentwarn.classList.add('is-hidden')
            } 
            if (this.activeset.license == undefined || this.activeset.license.trim() == "") {
                this.$refs.licensewarn.classList.remove('is-hidden')
                error++
            } else {
                this.$refs.licensewarn.classList.add('is-hidden')
            }

            if (error > 0) return false;                       
            axios
                .post(this.getApiAdminUrl + '/dataset', this.activeset)
                .then(res => {
                    this.activeset = res.data;
                    this.update();
                    this.edit(-1);
                    this.$root.$children[0].getUserInfo()
                })
                .catch(error => console.log(error));
        },
        getLanguages(value) {
            this.activeset.languages = value
        },
        getLabels(value) {
            this.activeset.labels = value
        }
    },
    computed: mapGetters(['getApiAdminUrl']),

    
}
</script>
<style scoped>
input[type=text] {
    width:400px;
    border-radius: 4px;
}
input[type=email] {
    width:400px;
    border-radius: 4px;
}
input[type=radio] {
    margin-left:10px
}
input[type=date] {
    border-radius: 4px;
}
textarea {
    width:400px
}
.field {
    margin-bottom:20px;
    display:block
}
.hidden {
    display:none;
}
</style>